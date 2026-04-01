<?php

namespace Tests\Performance;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoadPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_documents_index_stays_within_reasonable_response_budget_under_repeated_load(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        // Seed enough rows to exercise filtering, sorting and pagination.
        for ($i = 1; $i <= 40; $i++) {
            Document::query()->create([
                'team_id' => $user->currentTeam->id,
                'user_id' => $user->id,
                'title' => "Perf Doc {$i}",
                'content' => '<p>benchmark body</p>',
                'version' => 1,
                'status' => 'draft',
            ]);
        }

        $samples = [];

        for ($i = 0; $i < 20; $i++) {
            $start = microtime(true);

            $response = $this->actingAs($user)->get(route('documents.index'));
            $response->assertOk();

            $samples[] = microtime(true) - $start;
        }

        sort($samples);

        $avg = array_sum($samples) / count($samples);
        $p95 = $samples[(int) floor(count($samples) * 0.95) - 1] ?? end($samples);

        // Conservative guardrails for CI/devcontainer variability.
        $this->assertLessThan(
            0.85,
            $avg,
            'Average response time should stay under 850ms for documents index under repeated load.'
        );

        $this->assertLessThan(
            1.8,
            $p95,
            'P95 response time should stay under 1.8s for documents index under repeated load.'
        );
    }
}
