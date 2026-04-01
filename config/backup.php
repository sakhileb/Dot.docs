<?php

return [
    'disk' => env('BACKUP_DISK', 'local'),
    'path' => env('BACKUP_PATH', 'backups'),
    'keep_days' => (int) env('BACKUP_KEEP_DAYS', 14),
];
