<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\DocumentExportJob;
use App\Models\DocumentVersion;

class RetentionPolicyService
{
    public function enforce(): array
    {
        $retention = config('data-protection.retention');

        $auditDeleted = AuditLog::query()
            ->where('created_at', '<', now()->subDays((int) ($retention['audit_logs_days'] ?? 365)))
            ->delete();

        $activityDeleted = ActivityLog::query()
            ->where('created_at', '<', now()->subDays((int) ($retention['activity_logs_days'] ?? 365)))
            ->delete();

        $autosaveVersionsDeleted = DocumentVersion::query()
            ->where('is_auto_save', true)
            ->where('created_at', '<', now()->subDays((int) ($retention['autosave_versions_days'] ?? 180)))
            ->delete();

        $exportJobsDeleted = DocumentExportJob::query()
            ->where('completed_at', '<', now()->subDays((int) ($retention['export_jobs_days'] ?? 90)))
            ->delete();

        return [
            'audit_logs_deleted' => $auditDeleted,
            'activity_logs_deleted' => $activityDeleted,
            'autosave_versions_deleted' => $autosaveVersionsDeleted,
            'export_jobs_deleted' => $exportJobsDeleted,
        ];
    }
}
