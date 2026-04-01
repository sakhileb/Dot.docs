<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsDashboardFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_dashboard_requires_authentication(): void
    {
        $this->get(route('analytics.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_analytics_dashboard(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $this->actingAs($user)
            ->get(route('analytics.dashboard'))
            ->assertOk()
            ->assertSee('Product Analytics Dashboard')
            ->assertSee('Integration Status');
    }
}
