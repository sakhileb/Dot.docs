<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\User;
use App\Services\DocumentAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_computes_edits_contributors_and_estimated_time_spent(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $secondUser = User::factory()->withPersonalTeam()->create();

        $document = Document::create([
            'team_id' => $owner->currentTeam->id,
            'user_id' => $owner->id,
            'title' => 'Analytics Doc',
            'content' => '<p>Initial</p>',
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);

        $base = now()->subMinutes(30);

        $a1 = ActivityLog::logActivity($document, $owner, 'edit', 'content', 'Edit 1');
        $a1->forceFill(['created_at' => $base->copy(), 'updated_at' => $base->copy()])->saveQuietly();

        $a2 = ActivityLog::logActivity($document, $owner, 'edit', 'title', 'Edit 2');
        $a2->forceFill(['created_at' => $base->copy()->addMinutes(5), 'updated_at' => $base->copy()->addMinutes(5)])->saveQuietly();

        $a3 = ActivityLog::logActivity($document, $owner, 'comment', null, 'Comment');
        $a3->forceFill(['created_at' => $base->copy()->addMinutes(25), 'updated_at' => $base->copy()->addMinutes(25)])->saveQuietly();

        $b1 = ActivityLog::logActivity($document, $secondUser, 'edit', 'content', 'Edit 3');
        $b1->forceFill(['created_at' => $base->copy()->addMinutes(2), 'updated_at' => $base->copy()->addMinutes(2)])->saveQuietly();

        $b2 = ActivityLog::logActivity($document, $secondUser, 'edit', 'content', 'Edit 4');
        $b2->forceFill(['created_at' => $base->copy()->addMinutes(8), 'updated_at' => $base->copy()->addMinutes(8)])->saveQuietly();

        $service = app(DocumentAnalyticsService::class);
        $analytics = $service->analyze($document);

        $this->assertSame(4, $analytics['total_edits']);
        $this->assertSame(1, $analytics['total_comments']);
        $this->assertSame(2, $analytics['unique_contributors']);

        // owner: 1 + 5 + 1 (new session) = 7, second user: 1 + 6 = 7, total = 14
        $this->assertSame(14, $analytics['estimated_time_spent_minutes']);
        $this->assertNotNull($analytics['recent_activity_at']);
    }
}
