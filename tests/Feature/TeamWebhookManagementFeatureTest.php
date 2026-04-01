<?php

namespace Tests\Feature;

use App\Livewire\Teams\WebhookManagement;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeamWebhookManagementFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_owner_can_open_webhook_management_page(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->currentTeam;

        $this->actingAs($user)
            ->get(route('teams.webhooks', $team))
            ->assertOk()
            ->assertSee('Automation Webhooks');
    }

    public function test_team_owner_can_create_webhook_from_livewire_ui(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->currentTeam;

        Livewire::actingAs($user)
            ->test(WebhookManagement::class, ['team' => $team])
            ->set('webhookName', 'UI Created Webhook')
            ->set('webhookProvider', 'zapier')
            ->set('webhookUrl', 'https://hooks.zapier.com/hooks/catch/ui')
            ->set('selectedEvents', ['document.created'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('automation_webhooks', [
            'team_id' => $team->id,
            'name' => 'UI Created Webhook',
            'provider' => 'zapier',
        ]);
    }
}
