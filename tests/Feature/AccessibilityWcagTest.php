<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessibilityWcagTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_semantic_inputs_and_labels(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('type="email"', false);
        $response->assertSee('type="password"', false);
        $response->assertSee('for="email"', false);
        $response->assertSee('for="password"', false);
    }

    public function test_documents_index_has_labeled_controls_for_wcag_forms(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $response = $this->actingAs($user)->get(route('documents.index'));

        $response->assertOk();
        $response->assertSee('for="search"', false);
        $response->assertSee('for="teamFilter"', false);
        $response->assertSee('for="statusFilter"', false);
        $response->assertSee('for="dateFilter"', false);
    }

    public function test_editor_page_exposes_labeled_title_and_status_controls(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $document = Document::query()->create([
            'team_id' => $user->currentTeam->id,
            'user_id' => $user->id,
            'title' => 'Accessibility Doc',
            'content' => '<p>hello</p>',
            'version' => 1,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->get(route('documents.edit', $document));

        $response->assertOk();
        $response->assertSee('for="title"', false);
        $response->assertSee('for="status"', false);
    }
}
