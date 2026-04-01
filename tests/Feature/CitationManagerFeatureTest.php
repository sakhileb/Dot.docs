<?php

namespace Tests\Feature;

use App\Livewire\Documents\CitationManager;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CitationManagerFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_citation_manager_page(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $document = Document::create([
            'team_id' => $user->currentTeam->id,
            'user_id' => $user->id,
            'title' => 'Citation Doc',
            'content' => '<p>Body</p>',
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);

        $this->actingAs($user)
            ->get(route('documents.citations', $document))
            ->assertOk()
            ->assertSee('Citation Manager')
            ->assertSee('Add Manual Citation');
    }

    public function test_user_can_add_manual_citation(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $document = Document::create([
            'team_id' => $user->currentTeam->id,
            'user_id' => $user->id,
            'title' => 'Manual Citation Doc',
            'content' => '<p>Body</p>',
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);

        Livewire::actingAs($user)
            ->test(CitationManager::class, ['document' => $document])
            ->set('title', 'Deep Work')
            ->set('authors', 'Cal Newport')
            ->set('publicationYear', '2016')
            ->set('sourceUrl', 'https://example.com/deep-work')
            ->set('citationText', 'Newport, C. (2016). Deep Work.')
            ->call('addManualCitation')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('citation_references', [
            'document_id' => $document->id,
            'provider' => 'manual',
            'title' => 'Deep Work',
            'authors' => 'Cal Newport',
            'publication_year' => 2016,
        ]);
    }

    public function test_user_can_import_zotero_json_citations(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $document = Document::create([
            'team_id' => $user->currentTeam->id,
            'user_id' => $user->id,
            'title' => 'Import Citation Doc',
            'content' => '<p>Body</p>',
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);

        $payload = json_encode([
            [
                'key' => 'ABCD1234',
                'title' => 'The Pragmatic Programmer',
                'creators' => [
                    ['firstName' => 'Andrew', 'lastName' => 'Hunt'],
                    ['firstName' => 'David', 'lastName' => 'Thomas'],
                ],
                'date' => '1999-10-20',
                'url' => 'https://example.com/pragmatic-programmer',
            ],
        ], JSON_THROW_ON_ERROR);

        Livewire::actingAs($user)
            ->test(CitationManager::class, ['document' => $document])
            ->set('importProvider', 'zotero')
            ->set('importJson', $payload)
            ->call('importCitations')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('citation_references', [
            'document_id' => $document->id,
            'provider' => 'zotero',
            'external_id' => 'ABCD1234',
            'title' => 'The Pragmatic Programmer',
            'authors' => 'Andrew Hunt, David Thomas',
            'publication_year' => 1999,
        ]);
    }
}
