---
title: Dot.docs — Platform Wiki
version: 0.1.0
status: draft
owners: [Docs Platform Lead]
platform-id: dot-docs
last-review: 2026-08-02
---

# Dot.docs

Purpose: this is Dot.docs' own knowledge home — owned and maintained by the Dot.docs team. It describes what this platform actually is, as implemented, and how it connects to the wider Dot Ecosystem. Dot.Brain never edits this file; it only reads what we choose to publish.

> **Related:** [Dot.Brain's ingested view of this platform](https://github.com/sakhilebhayi/Dot.Brain/blob/main/platforms/dot-docs.md) — this file does not exist yet at the time of writing; a separate ingestion process creates it once Dot.docs publishes its first Knowledge Pack.

---

## 1. What Dot.docs Is

Dot.docs is a real-time collaborative document/wiki platform for the InfoDot ecosystem — closer to Notion or Confluence than to a static docs site. Teams and individuals create rich-text documents, edit them together live (presence, active-user avatars, broadcast content updates), track full version history with side-by-side diffs, comment inline with threaded replies and @mentions, and share documents publicly via password- and expiry-protected links. It layers an AI writing assistant (grammar check, summarize, continue writing, tone rewrite, translate, outline) and a slash-command palette (`Ctrl+K`) on top, plus export/import to PDF, Word, HTML, and Markdown.

**Status:** this is a substantially built application — real models, migrations, ~13 Livewire components, a working `EcosystemAuthController`, broadcast events, and a genuine (not faked) OpenAI integration behind `AiService` — not a scaffold. It was previously untouched by the ecosystem-wide engineering effort; this pass is its first. Treat §8 (Roadmap) as what's genuinely unbuilt (Phase 10 in the repo's own `task-list.md` — Pest tests, Dusk browser tests, Telescope/Pulse, error tracking — was left unchecked and still is), and everything else in this document as grounded in the code that exists today.

## 2. Architecture

| Layer | Technology | Notes |
|---|---|---|
| Framework | Laravel 13, PHP 8.4 | Jetstream 5.5 (Livewire stack, Teams), Fortify for auth actions |
| UI | Livewire 3, Alpine.js 3, Tailwind CSS (via Tailwind CDN in the app shell, Vite for the guest/welcome pages) | TipTap rich-text editor wrapped in Alpine, 1.5s debounced autosave |
| Database | PostgreSQL, shared InfoDot instance (`DB_DATABASE=infodot`) | See §4 — this was misconfigured until this pass; see Change Log |
| Auth | Laravel Sanctum + Jetstream Teams + a custom `EcosystemAuthController` | SSO handoff from the InfoDot hub at `/auth/ecosystem` |
| Realtime | Laravel Reverb | Presence channels per document (`document.{id}`), used for live cursors/avatars, content broadcast, and comment broadcast — genuinely wired, not just configured |
| AI | OpenAI (`openai-php/laravel`, default model `gpt-4o`) via `App\Services\AiService` | Not Anthropic — `task-list.md` and an earlier README both said "Anthropic Claude"; the actual code calls `OpenAI::chat()->create()`. Rate-limited to 20 requests/user/hour. |
| Cache / Session / Queue | Database driver | No Redis, Horizon, Scout, or Meilisearch dependency exists in `composer.json` despite `task-list.md` listing them as planned |
| Export / Import | `barryvdh/laravel-dompdf` (PDF), `phpoffice/phpword` (Word), `league/html-to-markdown` (Markdown), `jfcherng/php-diff` (version diff rendering) | All real dependencies, all wired to controllers/components |
| Storage | Local disk (Flysystem) | No S3/AWS credentials or disk config actually wired in beyond the default `.env` placeholders |

Team/user scoping runs through Jetstream's `Team` model, but Dot.docs is **not** exclusively team-scoped — a document can be owned by an individual user (`owner_id`), optionally attached to a team (`team_id`), and independently shared to specific collaborators (`document_collaborators`) or the public internet (`is_public` + optional password/expiry). `DocumentPolicy` is the single source of truth for all three access paths.

## 3. Domain Entities (as implemented)

Source: `database/migrations/*`, `app/Models/`.

| Model | Table | Purpose |
|---|---|---|
| `Document` | `documents` | Core entity — UUID, title, rich-text `content`, owner, optional team, version counter, public-share settings (password hash, expiry) |
| `DocumentVersion` | `document_versions` | Immutable content snapshot, auto-created by `DocumentObserver` on every content change; powers the diff/restore UI |
| `DocumentCollaborator` | `document_collaborators` | Per-user role grant on a document (`viewer`/`editor`/`admin`) independent of team membership |
| `Comment` | `comments` | Threaded (`parent_id`), resolvable, tied to a text selection (`selection_text`) |
| `AiSuggestion` | `ai_suggestions` | A pending AI-proposed edit in "suggestion mode," accepted/rejected by a collaborator |
| `DocumentTemplate` | `document_templates` | Reusable starting content — global, team-owned, or personal |
| `DocumentSlashCommand` | `document_slash_commands` | User- or team-defined `/command` shortcut that expands to a prompt template for the AI assistant |
| `DocumentWebhook` | `document_webhooks` | Outbound webhook definition per document, fired on `on_save` / `on_export` |
| `Team`, `TeamInvitation`, `Membership`, `User` | Jetstream defaults | Standard Jetstream Teams tables, extended with `bio`/`preferences` on `users` |

## 4. SSO Contract Verification

`app/Http/Controllers/Auth/EcosystemAuthController.php` matches the ecosystem-standard pattern used by every other platform:

1. `PersonalAccessToken::findToken($request->query('token'))`
2. `abort_if` on: token missing, token lacks the `ecosystem:read` ability, or token is past `expires_at`
3. Resolves the tokenable `User`, **deletes the token** (one-time use)
4. `Auth::login($user)`
5. Redirects to this app's own `dashboard` route

This is correct and consistent with the contract described in [Dot.Billing's wiki.md](../Dot.Billing/wiki.md) §2.

**Database finding (fixed this pass):** `.env.example` had `DB_DATABASE` commented out as `# DB_DATABASE=laravel`, meaning any environment built from the example file — before this fix — would fall back to `config/database.php`'s hardcoded default of `'laravel'`, a database that does not exist in the shared InfoDot PostgreSQL instance. Every other platform's `.env.example` (Dot.Billing, Dot.Tasks, Dot.Projects checked directly) sets `DB_DATABASE=infodot` uncommented. This has been corrected to `DB_DATABASE=infodot` in this pass — see Change Log. This was not caught by CI or any prior review because this environment cannot run `php artisan migrate` or connect to Postgres at all (see §9); it was found by reading the config, not by running it.

**Recent commit check:** the most recent commit before this pass, `afac9fe "fix: update routes/web.php imports and dashboard query"`, replaced a stub `return view('dashboard')` with real KPI queries (`myDocs`, `sharedDocs`, `publicDocs`, `aiSuggestions`, `recentDocs`, `recentShared`) and passes them to `dashboard.blade.php`, which consumes every one of those variables. Read side-by-side with the view, the fix is complete and internally consistent — nothing was left half-wired.

## 5. Events Emitted

Real, broadcast-over-Reverb domain events exist today (not aspirational):

| Event | Channel | Trigger | Broadcast payload |
|---|---|---|---|
| `DocumentUpdated` | `document.{id}` (presence) | Live content edit propagated to other active collaborators | document id, content, version, editor identity |
| `UserJoinedDocument` / `UserLeftDocument` | `document.{id}` (presence) | Collaborator opens/leaves the editor | user identity / user id |
| `CommentPosted` | `document.{id}` (presence) | New comment or reply | full comment payload including author |

These are genuine `ShouldBroadcast` events wired to real Livewire components (`Editor`, `CommentThread`) and `PresenceService` — not stubs. There is no outbound Dot.Brain-facing Knowledge Pack publisher yet; these events are internal to the collaborative-editing feature only, not (yet) republished as ecosystem-facing domain events.

## 6. Security & Technical-Debt Scan (this pass)

Scope: by-ID record lookups reachable from Livewire public methods, checked against the same IDOR pattern found repeatedly across the ecosystem this session (an unscoped `Model::find($id)` where the ID is a user-controllable Livewire method argument).

**`DocumentPolicy` and most Livewire components are solid** — every document-scoped component (`Editor`, `DocumentSettings`, `ShareManager`, `WebhookManager`, `CommentThread`, `AiAssistant`, `AiChat`, `SaveAsTemplate`, `VersionHistory`) calls `$this->authorize(...)` against `DocumentPolicy` before mutating state, and nested lookups (comments, webhooks, versions used for `restore()`) are correctly scoped with `->where('document_id', $this->document->id)`.

**Two real gaps found and fixed:**

1. **`VersionHistory::runDiff()` and the `previewVersion` computed value** (`app/Livewire/Documents/VersionHistory.php`) called `DocumentVersion::find($id)` on Livewire-supplied version IDs (`compareIds`, `previewId`) with no scoping to the authorized document. A user with legitimate view access to Document A could pass version IDs belonging to Document B — one they have no access to — via `toggleCompare()`/`preview()`, and the diff/preview panel would render Document B's content snapshot. Fixed by scoping both lookups to `->where('document_id', $this->document->id)`.
2. **`TemplateGallery::useTemplate()`** (`app/Livewire/Documents/TemplateGallery.php`) called `DocumentTemplate::findOrFail($templateId)` with no visibility check, while the component's own `templates()` listing correctly restricts to global, own-team, or self-authored templates. Any authenticated user could pass an arbitrary `templateId` and copy another team's private template content into a new document. Fixed by applying the same visibility scope used in `templates()` before the lookup.

Both fixes are narrow (query-scoping only, no schema or authorization-model changes) and match the existing `DocumentPolicy`/`templates()` conventions rather than introducing a new pattern.

**Not fixed, flagged for a dedicated pass:** `Document::findByUuidCached()` and `cachedContent()` cache by UUID/document id but never by user — fine for public documents, but worth a second look if private-document caching is ever added on this code path (currently it is not; private documents always read `$this->content` directly, bypassing the cache — see `Document::cachedContent()`). No action taken; flagging only, per the bounded-pass rule in [02-Engineering-Loop.md](../Dot.Brain/os/02-Engineering-Loop.md) §5.

## 7. Branding

`dot_doc.png` (repo root) was already wired into `application-logo.blade.php`, `application-mark.blade.php`, and `authentication-card-logo.blade.php`, and referenced directly as `/dot_doc.png` for the favicon `<link>` in `welcome.blade.php` and `layouts/guest.blade.php`. `layouts/app.blade.php` (the main authenticated app shell) had no favicon link at all.

This pass added the standard ecosystem favicon set generated via `sips` (`apple-touch-icon.png` 180px, `favicon-32x32.png`, `favicon-16x16.png`), copied the source logo to `public/images/logo.png`, and wired the three-tag favicon block (`32x32`, `16x16`, `apple-touch-icon`, all via `asset()`) into all three head-bearing layouts (`layouts/app.blade.php`, `layouts/guest.blade.php`, `welcome.blade.php`) — matching the exact pattern already used in Dot.Billing's layouts.

## 8. Connecting to Dot.Brain

Dot.docs is not yet registered as publishing Knowledge Packs — there is no outbound publisher, no aggregation-floor configuration, and no DKP manifest in this repo. Dot.Brain's ingested view of this platform is expected to live at [`platforms/dot-docs.md`](https://github.com/sakhilebhayi/Dot.Brain/blob/main/platforms/dot-docs.md); that file does not exist yet as of this writing (it is created by a separate ingestion process, not by this repo).

If/when publishing begins, the natural payload shape follows the same pattern as other platforms:

| Payload type | Would contain |
|---|---|
| `observation` | Aggregated document/collaboration metrics (documents created, active-collaboration sessions, AI-assist usage) — never document content |
| `insight` | Patterns in AI-assist acceptance rates, template usage, collaboration density |
| `outcome` | Verification of any Dot.Brain recommendation (e.g., "suggest templates to teams with low document creation") |
| `incident` | Broadcast/Reverb outages, AI provider failures |

Given that `documents` and `comments` can contain arbitrary user content (including on shared/public documents with real names and text), any aggregation published outward must never include raw content or comment text — only counts and metadata. No aggregation or publishing code exists yet, so this is a design requirement to build in before publishing begins, not an enforced constraint today.

## 9. Environment Constraints on This Pass

This pass was hand-authored and hand-reviewed only. **No PHP, Composer, PostgreSQL, or Docker were available** in the working environment — nothing in this document was verified by running `php artisan`, executing a migration, or hitting a real Postgres instance. All findings (the `DB_DATABASE` misconfiguration, the two IDOR gaps, the dashboard-query commit review) were made by reading the actual source files side-by-side, not by executing them. CI or a genuine PHP/Postgres dev environment remains a mandatory gate before any of this reaches production, per [02-Engineering-Loop.md](../Dot.Brain/os/02-Engineering-Loop.md) §2.

## 10. Roadmap / Open Questions

- [ ] Feature/Pest test suite — `task-list.md` Phase 10.1 is entirely unchecked; no `tests/Feature` coverage for document CRUD, authorization, or the two IDOR fixes made this pass exists yet
- [ ] Browser (Dusk) tests for collaborative editing — unchecked in `task-list.md`
- [ ] Production deployment / queue worker / monitoring (Telescope, Pulse, Sentry) — Phase 10.2–10.3, unchecked
- [ ] No Knowledge Pack publisher or DKP manifest exists — prerequisite for any Dot.Brain integration beyond this static wiki
- [ ] `task-list.md` still describes OpenAI/Gemini as an either/or choice and Redis/Horizon/Scout/Meilisearch as part of the stack; only OpenAI and a database-backed cache/queue actually shipped — `task-list.md` itself was not corrected this pass (it is a historical planning artifact, left as-is; this wiki is the accurate source going forward)
- [ ] Whether `Document::cachedContent()`'s public-only caching should be extended (carefully, with per-user keys) to private documents is an open design question, not a bug — flagged in §6, not fixed

## Change Log

| Version | Date | Author | Change |
|---|---|---|---|
| 0.1.0 | 2026-08-02 | Docs Platform Lead | Initial platform-owned wiki, derived from the actual Laravel codebase. Verified the `EcosystemAuthController` SSO contract (correct) and fixed a real `DB_DATABASE` misconfiguration in `.env.example` (was falling back to a nonexistent `laravel` database instead of the shared `infodot` instance). Fixed two IDOR gaps found in this pass's security scan: unscoped `DocumentVersion::find()` in `VersionHistory` and unscoped `DocumentTemplate::findOrFail()` in `TemplateGallery`. Wired the existing `dot_doc.png` logo into the app-shell favicon (was only wired into `welcome`/`guest` layouts) and generated the standard ecosystem favicon set. Corrected README.md's stack claims (Laravel 12→13, "Anthropic Claude"→OpenAI/gpt-4o, removed unimplemented Redis/Horizon/Scout/Meilisearch claims, corrected the domain-model list). |

## Open Questions

- Should `task-list.md` be deleted/archived now that this wiki is the accurate source, or kept as historical provenance per Manifesto principle 6? Leaning toward keeping it, unedited, as a record of original intent vs. actual build — same reasoning Dot.Billing's wiki applies to its own gaps.
- No Stripe-equivalent sensitive-data concern exists here, but public document sharing (password + expiry) has not been security-reviewed beyond the IDOR scan in §6 — a dedicated pass on the public `/shared/{uuid}` routes (timing attacks on password check, brute-forceability) is worth scheduling.
