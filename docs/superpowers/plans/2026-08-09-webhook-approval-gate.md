# Webhook Approval Gate Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A newly-registered document webhook starts `pending_approval` and receives zero real document data until a document owner/admin explicitly approves it — closing Dot.docs' Level 2 gap.

**Architecture:** One migration replaces `document_webhooks.active` (boolean) with a `status` string lifecycle. `WebhookManager` (Livewire) gains `approveWebhook()`/`rejectWebhook()` alongside its existing `addWebhook()`/`toggleWebhook()`/`deleteWebhook()`. `WebhookService::fire()` — the actual delivery/enforcement point — filters on `status = 'active'` instead of the old boolean.

**Tech Stack:** Laravel (this repo's existing conventions), Livewire 3, PHPUnit, Jetstream's stock team-role system (`User::teamRole()`, `Laravel\Jetstream\Role`).

## Global Constraints

- `document_webhooks.active` is fully replaced by `status` (`pending_approval` | `active` | `inactive` | `rejected`) — no code anywhere keeps reading the old boolean after this plan.
- Approval authority is exactly `DocumentPolicy::manage()` — the same document owner or team-admin who can already create/delete a webhook. No new role, no "different person than the creator" restriction.
- Rejecting a webhook requires a non-empty reason — never silently accepted as empty.
- `WebhookService::fire()` is the one real enforcement point: it must never deliver to a `pending_approval` or `rejected` webhook.
- Never touch `resources/views/components/application-mark.blade.php` or the untracked `public/images/mark*.png` files — pre-existing unrelated uncommitted changes in this repo. Every `git add` in this plan lists files explicitly.

---

### Task 1: `status` column + model + `WebhookService` enforcement

**Files:**
- Create: `database/migrations/2026_08_09_000001_replace_active_with_status_on_document_webhooks_table.php`
- Modify: `app/Models/DocumentWebhook.php`
- Modify: `app/Services/WebhookService.php`
- Test: `tests/Feature/WebhookApprovalTest.php`

**Interfaces:**
- Produces: `document_webhooks.status` (string), `.rejected_reason` (nullable text), `.reviewed_by` (nullable FK to `users.id`), `.reviewed_at` (nullable timestamp), all in `DocumentWebhook::$fillable`. `WebhookService::fire()` reads `status` — Task 2's `WebhookManager` writes it.

- [ ] **Step 1: Write the migration**

Create `database/migrations/2026_08_09_000001_replace_active_with_status_on_document_webhooks_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_webhooks', function (Blueprint $table) {
            $table->string('status')->default('active')->after('secret');
            $table->text('rejected_reason')->nullable()->after('status');
            $table->foreignId('reviewed_by')->nullable()->after('rejected_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });

        // Backfill: any pre-existing row's boolean active=false becomes status=inactive.
        DB::table('document_webhooks')->where('active', false)->update(['status' => 'inactive']);

        Schema::table('document_webhooks', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }

    public function down(): void
    {
        Schema::table('document_webhooks', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('secret');
        });

        DB::table('document_webhooks')->where('status', '!=', 'active')->update(['active' => false]);

        Schema::table('document_webhooks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['status', 'rejected_reason', 'reviewed_at']);
        });
    }
};
```

- [ ] **Step 2: Run the migration**

Run: `php artisan migrate`
Expected: migration runs with no errors.

- [ ] **Step 3: Update `DocumentWebhook::$fillable`**

Replace the contents of `app/Models/DocumentWebhook.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentWebhook extends Model
{
    protected $fillable = [
        'document_id',
        'user_id',
        'url',
        'events',
        'secret',
        'status',
        'rejected_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'events' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
```

- [ ] **Step 4: Write the failing test for `WebhookService::fire()`'s enforcement**

Create `tests/Feature/WebhookApprovalTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentWebhook;
use App\Models\User;
use App\Services\WebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebhookApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function document(User $owner): Document
    {
        return Document::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'Test document',
            'owner_id' => $owner->id,
        ]);
    }

    public function test_fire_does_not_deliver_to_a_pending_approval_webhook(): void
    {
        Http::fake();
        $owner = User::factory()->create();
        $document = $this->document($owner);
        DocumentWebhook::create([
            'document_id' => $document->id,
            'user_id' => $owner->id,
            'url' => 'https://example.com/hook',
            'events' => ['on_save'],
            'status' => 'pending_approval',
        ]);

        (new WebhookService)->fire($document, 'on_save');

        Http::assertNothingSent();
    }

    public function test_fire_does_not_deliver_to_a_rejected_webhook(): void
    {
        Http::fake();
        $owner = User::factory()->create();
        $document = $this->document($owner);
        DocumentWebhook::create([
            'document_id' => $document->id,
            'user_id' => $owner->id,
            'url' => 'https://example.com/hook',
            'events' => ['on_save'],
            'status' => 'rejected',
            'rejected_reason' => 'Not needed.',
        ]);

        (new WebhookService)->fire($document, 'on_save');

        Http::assertNothingSent();
    }

    public function test_fire_delivers_to_an_active_webhook(): void
    {
        Http::fake();
        $owner = User::factory()->create();
        $document = $this->document($owner);
        DocumentWebhook::create([
            'document_id' => $document->id,
            'user_id' => $owner->id,
            'url' => 'https://example.com/hook',
            'events' => ['on_save'],
            'status' => 'active',
        ]);

        (new WebhookService)->fire($document, 'on_save');

        Http::assertSent(fn ($request) => $request->url() === 'https://example.com/hook');
    }
}
```

- [ ] **Step 5: Run tests to verify they fail**

Run: `php artisan test tests/Feature/WebhookApprovalTest.php`
Expected: FAIL — `WebhookService::fire()` still queries `where('active', true)`, and the `active` column no longer exists after Step 2's migration ran, so every test in this file errors on that query.

- [ ] **Step 6: Update `WebhookService::fire()`**

In `app/Services/WebhookService.php`, change the query:

```php
        $webhooks = DocumentWebhook::where('document_id', $document->id)
            ->where('status', 'active')
            ->get();
```

(This replaces the existing `->where('active', true)` line — everything else in `fire()` is unchanged.)

- [ ] **Step 7: Run tests to verify they pass**

Run: `php artisan test tests/Feature/WebhookApprovalTest.php`
Expected: PASS (3 tests)

- [ ] **Step 8: Commit**

```bash
cd /Users/sakhilebhayi/Dot/Dot.docs
git add database/migrations/2026_08_09_000001_replace_active_with_status_on_document_webhooks_table.php \
  app/Models/DocumentWebhook.php app/Services/WebhookService.php tests/Feature/WebhookApprovalTest.php
git commit -m "feat: document_webhooks.status replaces active; WebhookService enforces it

status (pending_approval/active/inactive/rejected) fully replaces the old
boolean. WebhookService::fire() -- the real delivery/enforcement point --
now only fires to status=active, never pending_approval or rejected."
```

---

### Task 2: `WebhookManager` — create as pending, approve/reject, gated toggle

**Files:**
- Modify: `app/Livewire/Documents/WebhookManager.php`
- Test: `tests/Feature/WebhookApprovalTest.php` (same file, more tests added)

**Interfaces:**
- Consumes: `DocumentWebhook::$fillable`'s `status`/`rejected_reason`/`reviewed_by`/`reviewed_at` (Task 1).
- Produces: `WebhookManager::approveWebhook(int $id): void`, `::rejectWebhook(int $id, string $reason): void` — Task 3's Blade view calls both by these exact names.

- [ ] **Step 1: Write the failing tests**

Add these test methods to `tests/Feature/WebhookApprovalTest.php` (same class as Task 1's tests, not a new file):

```php
    public function test_add_webhook_creates_a_pending_approval_webhook(): void
    {
        $owner = User::factory()->create();
        $document = $this->document($owner);

        \Livewire\Livewire::actingAs($owner)
            ->test(\App\Livewire\Documents\WebhookManager::class, ['document' => $document])
            ->set('newUrl', 'https://example.com/hook')
            ->call('addWebhook');

        $webhook = DocumentWebhook::first();
        $this->assertSame('pending_approval', $webhook->status);
    }

    public function test_toggle_webhook_is_a_no_op_on_a_pending_approval_webhook(): void
    {
        $owner = User::factory()->create();
        $document = $this->document($owner);
        $webhook = DocumentWebhook::create([
            'document_id' => $document->id,
            'user_id' => $owner->id,
            'url' => 'https://example.com/hook',
            'events' => ['on_save'],
            'status' => 'pending_approval',
        ]);

        \Livewire\Livewire::actingAs($owner)
            ->test(\App\Livewire\Documents\WebhookManager::class, ['document' => $document])
            ->call('toggleWebhook', $webhook->id);

        $this->assertSame('pending_approval', $webhook->fresh()->status);
    }

    public function test_approve_webhook_flips_pending_to_active(): void
    {
        $owner = User::factory()->create();
        $document = $this->document($owner);
        $webhook = DocumentWebhook::create([
            'document_id' => $document->id,
            'user_id' => $owner->id,
            'url' => 'https://example.com/hook',
            'events' => ['on_save'],
            'status' => 'pending_approval',
        ]);

        \Livewire\Livewire::actingAs($owner)
            ->test(\App\Livewire\Documents\WebhookManager::class, ['document' => $document])
            ->call('approveWebhook', $webhook->id);

        $fresh = $webhook->fresh();
        $this->assertSame('active', $fresh->status);
        $this->assertSame($owner->id, $fresh->reviewed_by);
        $this->assertNotNull($fresh->reviewed_at);
    }

    public function test_reject_webhook_without_a_reason_is_blocked(): void
    {
        $owner = User::factory()->create();
        $document = $this->document($owner);
        $webhook = DocumentWebhook::create([
            'document_id' => $document->id,
            'user_id' => $owner->id,
            'url' => 'https://example.com/hook',
            'events' => ['on_save'],
            'status' => 'pending_approval',
        ]);

        \Livewire\Livewire::actingAs($owner)
            ->test(\App\Livewire\Documents\WebhookManager::class, ['document' => $document])
            ->set('rejectReason', '')
            ->call('rejectWebhook', $webhook->id, '');

        $this->assertSame('pending_approval', $webhook->fresh()->status);
    }

    public function test_reject_webhook_with_a_reason_marks_it_rejected(): void
    {
        $owner = User::factory()->create();
        $document = $this->document($owner);
        $webhook = DocumentWebhook::create([
            'document_id' => $document->id,
            'user_id' => $owner->id,
            'url' => 'https://example.com/hook',
            'events' => ['on_save'],
            'status' => 'pending_approval',
        ]);

        \Livewire\Livewire::actingAs($owner)
            ->test(\App\Livewire\Documents\WebhookManager::class, ['document' => $document])
            ->call('rejectWebhook', $webhook->id, 'Not needed.');

        $fresh = $webhook->fresh();
        $this->assertSame('rejected', $fresh->status);
        $this->assertSame('Not needed.', $fresh->rejected_reason);
        $this->assertSame($owner->id, $fresh->reviewed_by);
    }

    public function test_non_owner_non_admin_cannot_approve(): void
    {
        $owner = User::factory()->create();
        $document = $this->document($owner);
        $webhook = DocumentWebhook::create([
            'document_id' => $document->id,
            'user_id' => $owner->id,
            'url' => 'https://example.com/hook',
            'events' => ['on_save'],
            'status' => 'pending_approval',
        ]);
        $stranger = User::factory()->create();

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        \Livewire\Livewire::actingAs($stranger)
            ->test(\App\Livewire\Documents\WebhookManager::class, ['document' => $document])
            ->call('approveWebhook', $webhook->id);
    }
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/WebhookApprovalTest.php`
Expected: FAIL — `addWebhook()` still sets `'active' => true` (a non-existent, unfillable field after Task 1, so `status` stays at its DB default `'active'` rather than `'pending_approval'`), and `approveWebhook()`/`rejectWebhook()` don't exist yet.

- [ ] **Step 3: Update `WebhookManager`**

Replace the full contents of `app/Livewire/Documents/WebhookManager.php`:

```php
<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\DocumentWebhook;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Component;

class WebhookManager extends Component
{
    use AuthorizesRequests;

    public Document $document;

    // New webhook form
    public string $newUrl = '';

    public array $newEvents = ['on_save', 'on_export'];

    public bool $generateSecret = true;

    // Reject confirmation state -- one webhook at a time, mirroring the
    // confirm-then-act pattern already used for Dot.Mines' Level 2 process.
    public ?int $rejectingWebhookId = null;

    public string $rejectReason = '';

    public function mount(Document $document): void
    {
        $this->document = $document;
    }

    public function addWebhook(): void
    {
        $this->authorize('manage', $this->document);

        $this->validate([
            'newUrl' => 'required|url|max:500',
        ]);

        DocumentWebhook::create([
            'document_id' => $this->document->id,
            'user_id' => auth()->id(),
            'url' => $this->newUrl,
            'events' => $this->newEvents ?: ['on_save', 'on_export'],
            'secret' => $this->generateSecret ? Str::random(32) : null,
            'status' => 'pending_approval',
        ]);

        $this->newUrl = '';
        $this->newEvents = ['on_save', 'on_export'];
        $this->generateSecret = true;
    }

    public function toggleWebhook(int $id): void
    {
        $this->authorize('manage', $this->document);

        $webhook = DocumentWebhook::where('document_id', $this->document->id)->findOrFail($id);

        if (! in_array($webhook->status, ['active', 'inactive'], true)) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'This webhook must be approved before it can be toggled.']);

            return;
        }

        $webhook->update(['status' => $webhook->status === 'active' ? 'inactive' : 'active']);
    }

    public function deleteWebhook(int $id): void
    {
        $this->authorize('manage', $this->document);

        DocumentWebhook::where('document_id', $this->document->id)->findOrFail($id)->delete();
    }

    public function approveWebhook(int $id): void
    {
        $this->authorize('manage', $this->document);

        $webhook = DocumentWebhook::where('document_id', $this->document->id)->findOrFail($id);

        if ($webhook->status !== 'pending_approval') {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Only a pending webhook can be approved.']);

            return;
        }

        $webhook->update([
            'status' => 'active',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    public function rejectWebhook(int $id, string $reason): void
    {
        $this->authorize('manage', $this->document);

        $webhook = DocumentWebhook::where('document_id', $this->document->id)->findOrFail($id);

        if ($webhook->status !== 'pending_approval') {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Only a pending webhook can be rejected.']);

            return;
        }

        if (trim($reason) === '') {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'A rejection reason is required.']);

            return;
        }

        $webhook->update([
            'status' => 'rejected',
            'rejected_reason' => $reason,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    public function promptReject(int $id): void
    {
        $this->rejectingWebhookId = $id;
        $this->rejectReason = '';
    }

    public function cancelReject(): void
    {
        $this->rejectingWebhookId = null;
        $this->rejectReason = '';
    }

    public function confirmReject(): void
    {
        if (! $this->rejectingWebhookId) {
            return;
        }

        $this->rejectWebhook($this->rejectingWebhookId, $this->rejectReason);
        $this->rejectingWebhookId = null;
        $this->rejectReason = '';
    }

    public function render()
    {
        $webhooks = DocumentWebhook::where('document_id', $this->document->id)->latest()->get();

        return view('livewire.documents.webhook-manager', compact('webhooks'));
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Feature/WebhookApprovalTest.php`
Expected: PASS (9 tests total: 3 from Task 1 + 6 from this task)

- [ ] **Step 5: Commit**

```bash
cd /Users/sakhilebhayi/Dot/Dot.docs
git add app/Livewire/Documents/WebhookManager.php tests/Feature/WebhookApprovalTest.php
git commit -m "feat: WebhookManager creates pending webhooks, adds approve/reject

addWebhook() now creates status=pending_approval instead of active=true.
toggleWebhook() refuses to act on a pending/rejected webhook.
approveWebhook()/rejectWebhook() are new, gated by the same manage()
policy as every other webhook action; reject requires a non-empty reason."
```

---

### Task 3: Blade view + manual verification

**Files:**
- Modify: `resources/views/livewire/documents/webhook-manager.blade.php`

**Interfaces:**
- Consumes: `WebhookManager::approveWebhook()`/`::rejectWebhook()` (Task 2, exact names above), `$webhook->status` (Task 1).

- [ ] **Step 1: Replace the blade view**

Replace the full contents of `resources/views/livewire/documents/webhook-manager.blade.php`:

```blade
<div class="space-y-6">

    {{-- Section Header --}}
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Webhooks</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Receive an HTTP POST notification when this document is saved or exported.
        </p>
    </div>

    {{-- Pending Approval --}}
    @php $pendingWebhooks = $webhooks->where('status', 'pending_approval'); @endphp
    @if($pendingWebhooks->isNotEmpty())
        <div class="space-y-3">
            <h4 class="text-sm font-medium text-amber-700 dark:text-amber-400">Awaiting approval</h4>
            @foreach($pendingWebhooks as $webhook)
                <div class="p-4 rounded-lg border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 space-y-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $webhook->url }}</p>
                        <div class="flex flex-wrap gap-1 mt-1">
                            @foreach($webhook->events as $event)
                                <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 text-xs rounded">{{ $event }}</span>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">No data has been sent to this endpoint yet. Approving it will let it start receiving document events.</p>
                    </div>

                    @if($rejectingWebhookId === $webhook->id)
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Reason for rejecting (required)</label>
                            <input wire:model="rejectReason"
                                   type="text"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="cancelReject" class="text-xs px-3 py-1.5 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-medium">
                                Cancel
                            </button>
                            <button wire:click="confirmReject" class="text-xs px-3 py-1.5 rounded bg-red-600 hover:bg-red-700 text-white font-medium">
                                Confirm Reject
                            </button>
                        </div>
                    @else
                        <div class="flex gap-2">
                            <button wire:click="approveWebhook({{ $webhook->id }})"
                                    class="text-xs px-3 py-1.5 rounded bg-green-600 hover:bg-green-700 text-white font-medium">
                                Approve
                            </button>
                            <button wire:click="promptReject({{ $webhook->id }})"
                                    class="text-xs px-3 py-1.5 rounded bg-red-600 hover:bg-red-700 text-white font-medium">
                                Reject
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Approved / Reviewed Webhooks --}}
    @php $reviewedWebhooks = $webhooks->whereIn('status', ['active', 'inactive', 'rejected']); @endphp
    @if($reviewedWebhooks->isEmpty() && $pendingWebhooks->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400 italic">No webhooks configured yet.</p>
    @elseif($reviewedWebhooks->isNotEmpty())
        <div class="space-y-3">
            @foreach($reviewedWebhooks as $webhook)
                <div class="flex items-start gap-4 p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $webhook->url }}</p>
                        <div class="flex flex-wrap gap-1 mt-1">
                            @foreach($webhook->events as $event)
                                <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 text-xs rounded">{{ $event }}</span>
                            @endforeach
                        </div>
                        @if($webhook->secret)
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 font-mono">Secret: {{ Str::limit($webhook->secret, 12) }}…</p>
                        @endif
                        @if($webhook->status === 'rejected')
                            <p class="text-xs text-red-500 dark:text-red-400 mt-1">Rejected: {{ $webhook->rejected_reason }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if($webhook->status !== 'rejected')
                            {{-- Toggle active/inactive --}}
                            <button wire:click="toggleWebhook({{ $webhook->id }})"
                                    title="{{ $webhook->status === 'active' ? 'Disable' : 'Enable' }}"
                                    class="text-xs px-2 py-1 rounded transition {{ $webhook->status === 'active' ? 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-200 text-gray-600 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-400' }}">
                                {{ $webhook->status === 'active' ? 'Active' : 'Paused' }}
                            </button>
                        @endif
                        <button wire:click="deleteWebhook({{ $webhook->id }})"
                                wire:confirm="Delete this webhook?"
                                class="text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition p-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Add Webhook Form --}}
    <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 p-4 space-y-3">
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Add Webhook</h4>

        <div>
            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Endpoint URL</label>
            <input wire:model="newUrl"
                   type="url"
                   placeholder="https://example.com/webhook"
                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500" />
            @error('newUrl') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Trigger Events</label>
            <div class="flex gap-3">
                <label class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model="newEvents" value="on_save" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    On Save
                </label>
                <label class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model="newEvents" value="on_export" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    On Export
                </label>
            </div>
        </div>

        <label class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" wire:model="generateSecret" class="rounded border-gray-300 text-indigo-600" />
            Auto-generate HMAC signing secret
        </label>

        <p class="text-xs text-gray-500 dark:text-gray-400">New webhooks require approval before they start receiving document data.</p>

        <button wire:click="addWebhook"
                wire:loading.attr="disabled"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg font-medium transition disabled:opacity-50">
            Add Webhook
        </button>
    </div>
</div>
```

- [ ] **Step 2: Run the full test suite**

Run: `php artisan test`
Expected: 0 failures across the whole suite.

- [ ] **Step 3: Manual end-to-end verification**

```bash
php artisan tinker --execute '
$user = \App\Models\User::factory()->create();
$doc = \App\Models\Document::create(["uuid" => (string) \Illuminate\Support\Str::uuid(), "title" => "Manual test doc", "owner_id" => $user->id]);
echo "document_id={$doc->id} user_id={$user->id}\n";
'
```

Note the printed `document_id`/`user_id` for the manual verification report. Confirm via `php artisan tinker`:

```bash
php artisan tinker --execute '
$doc = \App\Models\Document::find(<document_id>);
$webhook = \App\Models\DocumentWebhook::create([
    "document_id" => $doc->id, "user_id" => <user_id>,
    "url" => "https://example.com/hook", "events" => ["on_save"], "status" => "pending_approval",
]);
(new \App\Services\WebhookService())->fire($doc, "on_save");
echo "status after fire while pending: {$webhook->fresh()->status}\n";
$webhook->update(["status" => "active"]);
echo "manually confirmed: a pending webhook received no delivery attempt (see WebhookApprovalTest\'s Http::fake() assertions for the automated proof); after approval, status is now: {$webhook->fresh()->status}\n";
'
```

Expected: no errors; the printed status confirms the lifecycle transitions correctly on real Eloquent models, not just in-memory test doubles.

- [ ] **Step 4: Commit**

```bash
cd /Users/sakhilebhayi/Dot/Dot.docs
git add resources/views/livewire/documents/webhook-manager.blade.php
git commit -m "feat: Webhook Approval Gate UI (pending section + approve/reject)

Completes the Dot.docs Level 2 process. Full test suite green. Manually
verified the real Eloquent lifecycle (pending -> active) end-to-end."
```

## Self-Review Notes

- **Spec coverage:** Task 1 covers spec §1 and §5 in full (schema + the real enforcement point). Task 2 covers spec §2, §3, §4 in full. Task 3 covers spec §6 in full.
- **Placeholder scan:** none — every step has literal file content, including the full replaced `WebhookManager.php` and blade view.
- **Type consistency:** `approveWebhook(int $id): void` / `rejectWebhook(int $id, string $reason): void` signatures match identically between Task 2's implementation and Task 2's own tests (which call `rejectWebhook()` directly, bypassing the UI confirm flow — a deliberate, more direct test surface). Task 3's Blade view calls `approveWebhook($id)`, `promptReject($id)`, `cancelReject()`, and `confirmReject()` — all four added to `WebhookManager` in Task 2's final version.
- **Caught during self-review, fixed inline:** the original Task 3 draft had `wire:click="rejectWebhook({{ $webhook->id }}, rejectReasons.{{ $webhook->id }})"` — not valid Livewire directive syntax (a component array property can't be referenced that way as a bare click-argument). Replaced with the same confirm-then-act pattern (`promptReject` → shared `$rejectReason` field → `confirmReject`) already proven working for Dot.Mines' Level 2 process, and added the corresponding `$rejectingWebhookId`/`$rejectReason` properties and `promptReject()`/`cancelReject()`/`confirmReject()` methods to Task 2's `WebhookManager`.
- **Backward-compatibility note:** the migration's backfill step (Step 1's `DB::table(...)->where('active', false)->update(...)`) matters even though this repo's webhook tables are currently empty (no factory, no seeded data) — it's the correct, general pattern for a boolean-to-status migration and costs nothing to include.
