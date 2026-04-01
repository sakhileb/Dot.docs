<?php

return [
    'disk' => env('DATA_PROTECTION_DISK', 'local'),
    'export_path' => env('DATA_EXPORT_PATH', 'exports'),
    'retention' => [
        'audit_logs_days' => (int) env('RETENTION_AUDIT_LOGS_DAYS', 365),
        'activity_logs_days' => (int) env('RETENTION_ACTIVITY_LOGS_DAYS', 365),
        'autosave_versions_days' => (int) env('RETENTION_AUTOSAVE_VERSIONS_DAYS', 180),
        'export_jobs_days' => (int) env('RETENTION_EXPORT_JOBS_DAYS', 90),
    ],
];
