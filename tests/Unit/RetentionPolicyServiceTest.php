<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentExportJob;
use App\Models\DocumentVersion;
use App\Models\Team;
use App\Models\User;
use App\Services\RetentionPolicyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RetentionPolicyServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_only_records_older_than_retention_windows(): void
    {
        config([
            'data-protection.retention.audit_logs_days' => 365,
            'data-protection.retention.activity_logs_days' => 365,
            'data-protection.retention.autosave_versions_days' => 180,
            'data-protection.retention.export_jobs_days' => 90,
        ]);

        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id]);
        $document = Document::query()->create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'title' => 'Retention Test',
            'content' => '<p>content</p>',
            'version' => 1,
            'status' => 'draft',
        ]);

        $oldAudit = AuditLog::query()->create([
            'user_id' => $user->id,
            'team_id' => $team->id,
            'action' => 'DELETE',
            'route_name' => 'documents.destroy',
            'path' => '/documents/1',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'metadata' => ['source' => 'test'],
        ]);
        $oldAudit->forceFill([
            'created_at' => now()->subDays(400),
            'updated_at' => now()->subDays(400),
        ])->saveQuietly();

        $newAudit = AuditLog::query()->create([
            'user_id' => $user->id,
            'team_id' => $team->id,
            'action' => 'POST',
            'route_name' => 'documents.store',
            'path' => '/documents',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'metadata' => ['source' => 'test'],
        ]);
        $newAudit->forceFill([
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ])->saveQuietly();

        $oldActivity = ActivityLog::query()->create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'team_id' => $team->id,
            'action' => 'edit',
            'action_type' => 'content',
            'description' => 'old',
            'metadata' => ['source' => 'test'],
        ]);
        $oldActivity->forceFill([
            'created_at' => now()->subDays(400),
            'updated_at' => now()->subDays(400),
        ])->saveQuietly();

        $newActivity = ActivityLog::query()->create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'team_id' => $team->id,
            'action' => 'edit',
            'action_type' => 'content',
            'description' => 'new',
            'metadata' => ['source' => 'test'],
        ]);
        $newActivity->forceFill([
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ])->saveQuietly();

        $oldAutosave = DocumentVersion::query()->create([
            'document_id' => $document->id,
            'team_id' => $team->id,
            'user_id' => $user->id,
            'version' => 1,
            'title' => 'old',
            'content' => 'old',
            'is_auto_save' => true,
        ]);
        $oldAutosave->forceFill([
            'created_at' => now()->subDays(200),
            'updated_at' => now()->subDays(200),
        ])->saveQuietly();

        $newAutosave = DocumentVersion::query()->create([
            'document_id' => $document->id,
            'team_id' => $team->id,
            'user_id' => $user->id,
            'version' => 2,
            'title' => 'new',
            'content' => 'new',
            'is_auto_save' => true,
        ]);
        $newAutosave->forceFill([
            'created_at' => now()->subDays(30),
            'updated_at' => now()->subDays(30),
        ])->saveQuietly();

        $oldExport = DocumentExportJob::query()->create([
            'document_id' => $document->id,
            'team_id' => $team->id,
            'user_id' => $user->id,
            'format' => 'pdf',
            'status' => 'completed',
            'requested_at' => now()->subDays(120),
            'completed_at' => now()->subDays(120),
        ]);
        $oldExport->forceFill([
            'created_at' => now()->subDays(120),
            'updated_at' => now()->subDays(120),
        ])->saveQuietly();

        $newExport = DocumentExportJob::query()->create([
            'document_id' => $document->id,
            'team_id' => $team->id,
            'user_id' => $user->id,
            'format' => 'docx',
            'status' => 'completed',
            'requested_at' => now()->subDays(10),
            'completed_at' => now()->subDays(10),
        ]);
        $newExport->forceFill([
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ])->saveQuietly();

        $result = app(RetentionPolicyService::class)->enforce();

        $this->assertSame(1, $result['audit_logs_deleted']);
        $this->assertSame(1, $result['activity_logs_deleted']);
        $this->assertSame(1, $result['autosave_versions_deleted']);
        $this->assertSame(1, $result['export_jobs_deleted']);

        $this->assertDatabaseMissing('audit_logs', ['id' => $oldAudit->id]);
        $this->assertDatabaseHas('audit_logs', ['id' => $newAudit->id]);

        $this->assertDatabaseMissing('activity_logs', ['id' => $oldActivity->id]);
        $this->assertDatabaseHas('activity_logs', ['id' => $newActivity->id]);

        $this->assertDatabaseMissing('document_versions', ['id' => $oldAutosave->id]);
        $this->assertDatabaseHas('document_versions', ['id' => $newAutosave->id]);

        $this->assertDatabaseMissing('document_export_jobs', ['id' => $oldExport->id]);
        $this->assertDatabaseHas('document_export_jobs', ['id' => $newExport->id]);
    }
}
