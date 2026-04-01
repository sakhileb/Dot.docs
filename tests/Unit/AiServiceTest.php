<?php

namespace Tests\Unit;

use App\Models\AiSuggestion;
use App\Models\Document;
use App\Models\Team;
use App\Models\User;
use App\Services\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AiServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Team $team;
    private Document $document;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->withPersonalTeam()->create();
        $this->team = $this->user->currentTeam;

        $this->document = Document::query()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->user->id,
            'title' => 'AI Test Document',
            'content' => '<p>Hello world</p>',
            'version' => 1,
            'status' => 'draft',
        ]);
    }

    // -------------------------------------------------------------------------
    // queueOperation creates the AiSuggestion record correctly
    // -------------------------------------------------------------------------

    public function test_queue_operation_creates_pending_ai_suggestion(): void
    {
        /** @var AiService $service */
        $service = app(AiService::class);

        $suggestion = $service->queueOperation(
            $this->document,
            $this->user,
            'content_improvement',
            'Improve this text',
        );

        $this->assertInstanceOf(AiSuggestion::class, $suggestion);
        $this->assertSame('pending', $suggestion->status);
        $this->assertSame('Improve this text', $suggestion->prompt);
        $this->assertSame('content_improvement', $suggestion->operation);

        $this->assertDatabaseHas('ai_suggestions', [
            'id' => $suggestion->id,
            'status' => 'pending',
            'document_id' => $this->document->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Cache: repeated requests return cached response without a new suggestion
    // -------------------------------------------------------------------------

    public function test_repeated_ai_call_is_served_from_cache(): void
    {
        /** @var AiService $service */
        $service = app(AiService::class);

        $requestHashMethod = new \ReflectionMethod(AiService::class, 'makeRequestHash');
        $requestHashMethod->setAccessible(true);

        $cacheKeyMethod = new \ReflectionMethod(AiService::class, 'cacheKeyFromHash');
        $cacheKeyMethod->setAccessible(true);

        $prompt = "Based on the following text, continue writing naturally and coherently. Keep the same tone and style.\n\nText: Hello there\n\nContinuation:";
        $requestHash = $requestHashMethod->invoke($service, 'completion', $prompt, []);
        $cacheKey = $cacheKeyMethod->invoke($service, $requestHash);

        Cache::put($cacheKey, [
            'response' => 'Cached response text',
            'token_usage' => 10,
        ], now()->addMinutes(60));

        $result = $service->completeText('Hello there', $this->document, $this->user);

        $this->assertSame('Cached response text', $result);

        $cachedSuggestion = AiSuggestion::query()
            ->where('document_id', $this->document->id)
            ->where('is_cached', true)
            ->first();

        $this->assertNotNull($cachedSuggestion, 'A cached suggestion record should be created');
    }
}
