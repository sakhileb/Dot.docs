<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityPenetrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_protected_document_routes(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();

        $document = Document::query()->create([
            'team_id' => $owner->currentTeam->id,
            'user_id' => $owner->id,
            'title' => 'Private Doc',
            'content' => '<p>secret</p>',
            'version' => 1,
            'status' => 'draft',
        ]);

        $this->get(route('documents.edit', $document))->assertRedirect(route('login'));
        $this->get(route('documents.share', $document))->assertRedirect(route('login'));
    }

    public function test_document_content_is_sanitized_against_inline_script_handlers(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $document = Document::query()->create([
            'team_id' => $user->currentTeam->id,
            'user_id' => $user->id,
            'title' => 'XSS Test',
            'content' => '<p onclick="alert(1)">Click me</p><script>alert(2)</script>',
            'version' => 1,
            'status' => 'draft',
        ]);

        $this->assertStringNotContainsString('onclick=', $document->content ?? '');
        $this->assertStringNotContainsString('<script', $document->content ?? '');
        $this->assertStringContainsString('<p>Click me</p>', $document->content ?? '');
    }

    public function test_document_list_search_handles_sqli_like_input_without_error(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        Document::query()->create([
            'team_id' => $user->currentTeam->id,
            'user_id' => $user->id,
            'title' => 'Normal Title',
            'content' => '<p>content</p>',
            'version' => 1,
            'status' => 'draft',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Documents\Index::class)
            ->set('search', "' OR 1=1 --")
            ->assertOk();
    }
}
