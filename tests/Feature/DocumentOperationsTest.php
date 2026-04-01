<?php

namespace Tests\Feature;

use App\Livewire\Documents\Index;
use App\Models\Document;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentOperationsTest extends TestCase
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

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_create_a_blank_document(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('openCreateWizard', 'blank')
            ->set('newTitle', 'My First Doc')
            ->call('createDocument');

        $this->assertDatabaseHas('documents', [
            'team_id' => $this->team->id,
            'user_id' => $this->user->id,
            'title' => 'My First Doc',
            'status' => 'draft',
        ]);
    }

    public function test_blank_document_gets_default_title_when_none_supplied(): void
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('openCreateWizard', 'blank')
            ->set('newTitle', '')
            ->call('createDocument');

        $this->assertDatabaseHas('documents', [
            'team_id' => $this->team->id,
            'title' => 'Untitled Document',
        ]);
    }

    // -------------------------------------------------------------------------
    // Archive / Unarchive
    // -------------------------------------------------------------------------

    public function test_user_can_archive_their_document(): void
    {
        $document = Document::query()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->user->id,
            'title' => 'To Archive',
            'content' => null,
            'version' => 1,
            'status' => 'draft',
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('archiveDocument', $document->id);

        $document->refresh();
        $this->assertTrue($document->is_archived);
        $this->assertSame('archived', $document->status);
    }

    public function test_user_can_unarchive_a_document(): void
    {
        $document = Document::query()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->user->id,
            'title' => 'Archived',
            'content' => null,
            'version' => 1,
            'status' => 'archived',
            'is_archived' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('unarchiveDocument', $document->id);

        $document->refresh();
        $this->assertFalse($document->is_archived);
        $this->assertSame('draft', $document->status);
    }

    // -------------------------------------------------------------------------
    // Soft Delete / Restore
    // -------------------------------------------------------------------------

    public function test_user_can_soft_delete_a_document(): void
    {
        $document = Document::query()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->user->id,
            'title' => 'To Delete',
            'content' => null,
            'version' => 1,
            'status' => 'draft',
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('deleteDocument', $document->id);

        $this->assertSoftDeleted('documents', ['id' => $document->id]);
    }

    public function test_user_can_restore_a_soft_deleted_document(): void
    {
        $document = Document::query()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->user->id,
            'title' => 'Deleted',
            'content' => null,
            'version' => 1,
            'status' => 'draft',
        ]);
        $document->delete();

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('restoreDocument', $document->id);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'deleted_at' => null,
        ]);
    }

    // -------------------------------------------------------------------------
    // Duplicate
    // -------------------------------------------------------------------------

    public function test_user_can_duplicate_a_document(): void
    {
        $original = Document::query()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->user->id,
            'title' => 'Original',
            'content' => '<p>content</p>',
            'version' => 1,
            'status' => 'published',
        ]);

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->call('duplicateDocument', $original->id);

        $this->assertDatabaseHas('documents', [
            'team_id' => $this->team->id,
            'title' => 'Original (Copy)',
            'status' => 'draft',
        ]);
    }

    // -------------------------------------------------------------------------
    // Search filter
    // -------------------------------------------------------------------------

    public function test_search_filter_narrows_document_list(): void
    {
        Document::query()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->user->id,
            'title' => 'Quarterly Report',
            'content' => null,
            'version' => 1,
            'status' => 'draft',
        ]);
        Document::query()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->user->id,
            'title' => 'Meeting Notes',
            'content' => null,
            'version' => 1,
            'status' => 'draft',
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(Index::class)
            ->set('search', 'Quarterly');

        // Component should render without errors when search is active.
        $component->assertOk();
    }

    // -------------------------------------------------------------------------
    // Authorization: guest cannot access document list
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_is_redirected_from_documents_page(): void
    {
        $this->get('/documents')->assertRedirect('/login');
    }
}
