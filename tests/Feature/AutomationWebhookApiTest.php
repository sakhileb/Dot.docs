<?php

namespace Tests\Feature;

use App\Models\AutomationWebhook;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AutomationWebhookApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->withPersonalTeam()->create();
        $this->team = $this->user->currentTeam;
        Sanctum::actingAs($this->user);
    }

    public function test_user_can_create_and_list_team_webhooks_via_api(): void
    {
        $create = $this->postJson('/api/teams/'.$this->team->id.'/webhooks', [
            'name' => 'API Webhook',
            'provider' => 'zapier',
            'endpoint_url' => 'https://hooks.zapier.com/hooks/catch/test',
            'secret' => 'test-secret',
            'subscribed_events' => ['document.created'],
        ]);

        $create->assertCreated();

        $this->getJson('/api/teams/'.$this->team->id.'/webhooks')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'API Webhook');
    }

    public function test_user_can_update_and_delete_team_webhook_via_api(): void
    {
        $webhook = AutomationWebhook::create([
            'team_id' => $this->team->id,
            'name' => 'Original Name',
            'provider' => 'make',
            'endpoint_url' => 'https://hook.make.com/abc123',
            'subscribed_events' => ['document.updated'],
            'is_active' => true,
        ]);

        $this->patchJson('/api/teams/'.$this->team->id.'/webhooks/'.$webhook->id, [
            'name' => 'Updated Name',
            'is_active' => false,
        ])->assertOk()->assertJsonPath('name', 'Updated Name');

        $this->deleteJson('/api/teams/'.$this->team->id.'/webhooks/'.$webhook->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('automation_webhooks', ['id' => $webhook->id]);
    }

    public function test_user_can_test_webhook_via_api(): void
    {
        Http::fake([
            'https://hooks.zapier.com/*' => Http::response(['ok' => true], 200),
        ]);

        $webhook = AutomationWebhook::create([
            'team_id' => $this->team->id,
            'name' => 'Test Hook',
            'provider' => 'zapier',
            'endpoint_url' => 'https://hooks.zapier.com/hooks/catch/test',
            'subscribed_events' => ['document.created'],
            'is_active' => true,
        ]);

        $this->postJson('/api/teams/'.$this->team->id.'/webhooks/'.$webhook->id.'/test')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200);
    }
}
