# Dot.docs: Webhook Approval Gate (First Real Level 2 Process)

## Context

Dot.docs' autonomy classification audit (`Dot.Brain/platforms/dot-docs.md`, 2026-08-08) found real Level 1 automation (daily notification digest, queued notification delivery, per-request authorization) but no real Level 2 process — its own gap summary named the exact fix: *"outbound webhook registration (not delivery) requiring approval before a new third-party endpoint starts receiving document data."*

Direct inspection of the real codebase (`~/Dot/Dot.docs`) confirms this precisely. `DocumentWebhook` (`app/Models/DocumentWebhook.php`) has a plain `active` boolean, no review state. `WebhookManager::addWebhook()` (`app/Livewire/Documents/WebhookManager.php`) creates a webhook with `'active' => true` **immediately** — an authorized user (document owner or team admin, gated by `DocumentPolicy::manage()`) can register any arbitrary URL and it starts receiving real document content (`on_save`/`on_export` payloads, including title, version, and extra event data) the moment it's saved. `WebhookService::fire()` (`app/Services/WebhookService.php`) delivers to every webhook `where('active', true)` with no other gate. No test file exists for any of this webhook code today — it is real, shipped, and untested.

## Goal

A newly-registered webhook starts `pending_approval` and receives zero document data until a document owner/admin (the same authorization tier `DocumentPolicy::manage()` already grants — no new role, per this spec's approved design) explicitly approves it. Rejecting a pending webhook requires a reason.

## Changes

### 1. `document_webhooks.status` replaces `active`

Migration: add `status` (string, default `'active'` — protects any hypothetical pre-existing rows from being silently un-delivered), `rejected_reason` (nullable text), `reviewed_by` (nullable FK to `users.id`), `reviewed_at` (nullable timestamp); drop the `active` boolean column entirely — `status` fully replaces it (`active` is referenced nowhere outside `WebhookManager.php` and its Blade view, both changed by this spec). `DocumentWebhook::$fillable` updated to match.

Valid `status` values: `pending_approval` (just created, not yet reviewed), `active` (approved and currently on), `inactive` (approved, but the owner/admin has toggled it off), `rejected` (a reviewer declined it — permanent, never delivers).

### 2. `WebhookManager::addWebhook()` creates as `pending_approval`

Replace `'active' => true` with `'status' => 'pending_approval'` in the `DocumentWebhook::create([...])` call. No other change to the create flow — the same `manage` authorization check, same validation, same secret-generation behavior.

### 3. `WebhookManager::toggleWebhook()` only toggles already-approved webhooks

A webhook in `pending_approval` or `rejected` cannot be toggled — `toggleWebhook()` becomes a no-op (with a flash/notification message, matching this component's existing pattern) unless the webhook's current `status` is `active` or `inactive`, in which case it flips between those two exactly as `active` boolean toggling did before.

### 4. `WebhookManager::approveWebhook($id)` / `::rejectWebhook($id, string $reason)`

New methods, both gated by the existing `$this->authorize('manage', $this->document)` check (same tier as `addWebhook`/`toggleWebhook`/`deleteWebhook` — per this spec's approved design, the same owner/admin who could create a webhook may also approve one; this is a deliberate second look before data flows, not organizational separation of duties):

- `approveWebhook($id)` — refuses (flash error, no state change) unless the webhook's `status === 'pending_approval'`. Sets `status = 'active'`, `reviewed_by = auth()->id()`, `reviewed_at = now()`.
- `rejectWebhook($id, string $reason)` — refuses unless `status === 'pending_approval'`. Refuses an empty/whitespace-only `$reason` (validation error, matching this component's existing `$this->validate()` pattern). Sets `status = 'rejected'`, `rejected_reason = $reason`, `reviewed_by = auth()->id()`, `reviewed_at = now()`.

### 5. `WebhookService::fire()` delivers only to `status = 'active'`

Change the query from `where('active', true)` to `where('status', 'active')`. This is the enforcement point: a `pending_approval` webhook receives nothing, ever, until approved.

### 6. Blade view (`resources/views/livewire/documents/webhook-manager.blade.php`)

Add a pending-webhooks section (above or alongside the existing list) showing each `pending_approval` webhook's URL and requested events, with Approve and Reject actions; Reject requires a reason via a text input (matching the confirm-dialog reason pattern already established for Dot.Mines' and Dot.Charts' Level 2 processes in this ecosystem). Existing active/inactive webhooks keep their current toggle/delete controls unchanged; rejected webhooks display their reason and cannot be re-activated (deleting and re-registering is the only path back to `pending_approval`).

## Testing

New test file (none exists today for this code) — `tests/Feature/WebhookApprovalTest.php` covering: `addWebhook()` creates a `pending_approval` webhook; `WebhookService::fire()` does not call out to a `pending_approval` webhook's URL; `approveWebhook()` flips a pending webhook to `active` and `fire()` then does deliver to it; `rejectWebhook()` without a reason is blocked; `rejectWebhook()` with a reason sets `rejected` and `fire()` never delivers to it; `toggleWebhook()` on a `pending_approval` or `rejected` webhook is a no-op; a non-owner/non-admin cannot approve or reject (matching `DocumentPolicy::manage()`'s existing authorization boundary, already covered for `addWebhook`/`deleteWebhook` conceptually but not with a real test file until now).

## Explicitly out of scope

- Any change to `DocumentPolicy::manage()` itself or who counts as an owner/admin — reused exactly as-is.
- Two-person control (a different admin required to approve than who created the webhook) — explicitly declined per this spec's approved design question.
- Notifying anyone that a webhook is pending — no notification channel is added; an owner/admin finds pending webhooks by opening the document's webhook manager, same as they'd find any other webhook today.
- The daily notification digest, queued notification delivery, or per-request authorization — Dot.docs' three real Level 1 processes, untouched.
- Registering this change in Dot.Brain's `platforms/dot-docs.md` or `platforms/autonomy-signals.json` — a separate, future re-audit pass, not part of building the feature.
