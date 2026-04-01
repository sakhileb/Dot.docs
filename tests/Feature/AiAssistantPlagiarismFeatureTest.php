<?php

namespace Tests\Feature;

use App\Livewire\Documents\AiAssistant;
use App\Models\Document;
use App\Models\User;
use App\Services\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AiAssistantPlagiarismFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_plagiarism_check_uses_mocked_service_and_sets_response(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $document = Document::create([
            'team_id' => $user->currentTeam->id,
            'user_id' => $user->id,
            'title' => 'Plagiarism Test Doc',
            'content' => '<p>Initial</p>',
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);

        $mock = $this->mock(AiService::class);
        $mock->shouldReceive('checkPlagiarism')
            ->once()
            ->andReturn([
                'risk_score' => 28,
                'likely_originality' => 'high',
                'flagged_phrases' => ['industry-leading solution'],
                'recommendations' => ['Add citations for benchmark claims'],
                'summary' => 'Mostly original with minor generic phrasing.',
            ]);

        Livewire::actingAs($user)
            ->test(AiAssistant::class, ['document' => $document])
            ->set('selectedText', 'This industry-leading solution improves outcomes across teams.')
            ->call('checkPlagiarism')
            ->assertSet('currentOperation', 'plagiarism_check')
            ->assertSet('isLoading', false)
            ->assertSet('aiResponse', "Plagiarism Risk Score: 28/100\nLikely Originality: high\nFlagged Phrases: industry-leading solution\nRecommendations: Add citations for benchmark claims\nSummary: Mostly original with minor generic phrasing.");
    }

    public function test_plagiarism_check_requires_selected_text(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $document = Document::create([
            'team_id' => $user->currentTeam->id,
            'user_id' => $user->id,
            'title' => 'Selection Required Doc',
            'content' => '<p>Initial</p>',
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);

        Livewire::actingAs($user)
            ->test(AiAssistant::class, ['document' => $document])
            ->set('selectedText', '')
            ->call('checkPlagiarism')
            ->assertSet('aiResponse', null)
            ->assertDispatched('notify');
    }
}
