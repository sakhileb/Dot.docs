<?php

namespace Tests\Feature;

use App\Livewire\Documents\MailMerge;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MailMergeFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_mail_merge_page(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $document = Document::create([
            'team_id' => $user->currentTeam->id,
            'user_id' => $user->id,
            'title' => 'Mail Merge Doc',
            'content' => 'Hello {{name}}',
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);

        $this->actingAs($user)
            ->get(route('documents.mail-merge', $document))
            ->assertOk()
            ->assertSee('Mail Merge')
            ->assertSee('Merge Setup');
    }

    public function test_user_can_preview_mail_merge_output(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $document = Document::create([
            'team_id' => $user->currentTeam->id,
            'user_id' => $user->id,
            'title' => 'Preview Doc',
            'content' => 'Hello {{name}}, your plan is {{plan}}.',
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);

        Livewire::actingAs($user)
            ->test(MailMerge::class, ['document' => $document])
            ->set('templateContent', 'Hello {{name}}, your plan is {{plan}}.')
            ->set('recipientJson', '[{"name":"Jane","plan":"Pro"}]')
            ->call('previewMerge')
            ->assertSet('mergedDocuments.0.content', 'Hello Jane, your plan is Pro.')
            ->assertHasNoErrors();
    }

    public function test_user_can_save_merged_documents(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $document = Document::create([
            'team_id' => $user->currentTeam->id,
            'user_id' => $user->id,
            'title' => 'Save Merge Doc',
            'content' => 'Dear {{name}}',
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);

        Livewire::actingAs($user)
            ->test(MailMerge::class, ['document' => $document])
            ->set('templateContent', 'Dear {{name}}')
            ->set('recipientJson', '[{"name":"Jane"},{"name":"John"}]')
            ->call('previewMerge')
            ->call('saveMergedDocuments')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('documents', [
            'team_id' => $user->currentTeam->id,
            'title' => 'Save Merge Doc - Jane',
        ]);

        $this->assertDatabaseHas('documents', [
            'team_id' => $user->currentTeam->id,
            'title' => 'Save Merge Doc - John',
        ]);
    }
}
