<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoiceDictationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_page_exposes_voice_dictation_controls_for_authenticated_user(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $document = Document::create([
            'team_id' => $user->currentTeam->id,
            'user_id' => $user->id,
            'title' => 'Dictation Test Doc',
            'content' => '<p>Initial</p>',
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);

        $this->actingAs($user)
            ->get(route('documents.edit', $document))
            ->assertOk()
            ->assertSee('voice-dictation-btn', escape: false)
            ->assertSee('Start Dictation');
    }
}
