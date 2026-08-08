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
