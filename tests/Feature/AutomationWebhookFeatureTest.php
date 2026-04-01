<?php

namespace Tests\Feature;

use App\Livewire\Documents\Index;
use App\Models\AutomationWebhook;
use App\Models\Document;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AutomationWebhookFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->withPersonalTeam()->create();
        $this->team = $this->user->currentTeam;
    }

    public function test_document_creation_dispatches_configured_webhook(): void
    {
        Http::fake([
            'https://hooks.zapier.com/*' => Http::response(['ok' => true], 200),
        ]);

        $webhook = AutomationWebhook::query()->create([
            'team_id' => $this->team->id,
            'name' => 'Zapier Document Created',
            'provider' => 'zapier',
            'endpoint_url' => 'https://hooks.zapier.com/hooks/catch/test',
            'secret' => 'shared-secret',
            'subscribed_events' => ['document.created'],
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('openCreateWizard', 'blank')
            ->set('newTitle', 'Webhook Test Doc')
            ->call('createDocument');

        Http::assertSent(function (Request $request): bool {
            if ($request->url() !== 'https://hooks.zapier.com/hooks/catch/test') {
                return false;
            }

            $payload = $request->data();

            return ($payload['event'] ?? null) === 'document.created'
                && ($payload['document']['title'] ?? null) === 'Webhook Test Doc'
                && $request->hasHeader('X-DotDocs-Signature');
        });

        $webhook->refresh();
        $this->assertNotNull($webhook->last_triggered_at);
        $this->assertSame(200, $webhook->last_response_status);
    }

    public function test_inactive_webhook_does_not_dispatch(): void
    {
        Http::fake();

        AutomationWebhook::query()->create([
            'team_id' => $this->team->id,
            'name' => 'Inactive Webhook',
            'provider' => 'make',
            'endpoint_url' => 'https://hook.make.com/test',
            'subscribed_events' => ['document.created'],
            'is_active' => false,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('openCreateWizard', 'blank')
            ->set('newTitle', 'No Dispatch')
            ->call('createDocument');

        Http::assertNothingSent();
    }

    public function test_document_delete_dispatches_delete_event_to_matching_webhook(): void
    {
        Http::fake([
            'https://hook.make.com/*' => Http::response([], 202),
        ]);

        $webhook = AutomationWebhook::query()->create([
            'team_id' => $this->team->id,
            'name' => 'Make Document Delete',
            'provider' => 'make',
            'endpoint_url' => 'https://hook.make.com/abc123',
            'subscribed_events' => ['document.deleted'],
            'is_active' => true,
        ]);

        $document = Document::query()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->user->id,
            'title' => 'Delete Event Doc',
            'content' => null,
            'version' => 1,
            'status' => 'draft',
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('deleteDocument', $document->id);

        Http::assertSent(function (Request $request): bool {
            $payload = $request->data();

            return ($payload['event'] ?? null) === 'document.deleted'
                && ($payload['document']['title'] ?? null) === 'Delete Event Doc';
        });

        $webhook->refresh();
        $this->assertSame(202, $webhook->last_response_status);
    }

    public function test_slack_provider_dispatches_slack_payload_format(): void
    {
        Http::fake([
            'https://hooks.slack.com/*' => Http::response(['ok' => true], 200),
        ]);

        AutomationWebhook::query()->create([
            'team_id' => $this->team->id,
            'name' => 'Slack Webhook',
            'provider' => 'slack',
            'endpoint_url' => 'https://hooks.slack.com/services/T/B/X',
            'subscribed_events' => ['document.created'],
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('openCreateWizard', 'blank')
            ->set('newTitle', 'Slack Doc')
            ->call('createDocument');

        Http::assertSent(function (Request $request): bool {
            $payload = $request->data();

            return str_starts_with($request->url(), 'https://hooks.slack.com/')
                && isset($payload['text'])
                && isset($payload['blocks']);
        });
    }

    public function test_discord_provider_dispatches_discord_payload_format(): void
    {
        Http::fake([
            'https://discord.com/*' => Http::response(['ok' => true], 204),
        ]);

        AutomationWebhook::query()->create([
            'team_id' => $this->team->id,
            'name' => 'Discord Webhook',
            'provider' => 'discord',
            'endpoint_url' => 'https://discord.com/api/webhooks/123/abc',
            'subscribed_events' => ['document.created'],
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('openCreateWizard', 'blank')
            ->set('newTitle', 'Discord Doc')
            ->call('createDocument');

        Http::assertSent(function (Request $request): bool {
            $payload = $request->data();

            return str_starts_with($request->url(), 'https://discord.com/')
                && isset($payload['embeds'])
                && isset($payload['content']);
        });
    }
}
