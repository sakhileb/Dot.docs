<?php

use App\Models\User;
use App\Services\BackupRestoreService;
use App\Services\RetentionPolicyService;
use App\Services\UserDataExportService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('backup:create {--label=} {--cleanup}', function () {
    /** @var BackupRestoreService $service */
    $service = app(BackupRestoreService::class);

    $file = $service->createBackup($this->option('label'));
    $this->info("Backup created: {$file}");

    if ($this->option('cleanup')) {
        $deleted = $service->cleanupOldBackups();
        $this->info("Old backups removed: {$deleted}");
    }
})->purpose('Create a full application backup archive');

Artisan::command('backup:list', function () {
    /** @var BackupRestoreService $service */
    $service = app(BackupRestoreService::class);
    $backups = $service->listBackups();

    if ($backups === []) {
        $this->warn('No backups found.');

        return;
    }

    $this->table(['File', 'Size (KB)', 'Last Modified'], $backups);
})->purpose('List available backup archives');

Artisan::command('backup:restore {file} {--force}', function (string $file) {
    if (!$this->option('force')) {
        $confirmed = $this->confirm(
            'This will overwrite current database/storage state from backup. Continue?',
            false
        );

        if (!$confirmed) {
            $this->warn('Restore cancelled.');

            return;
        }
    }

    /** @var BackupRestoreService $service */
    $service = app(BackupRestoreService::class);
    $service->restoreBackup($file);

    $this->info('Backup restore completed successfully.');
})->purpose('Restore application state from a backup archive');

Artisan::command('backup:verify {file}', function (string $file) {
    /** @var BackupRestoreService $service */
    $service = app(BackupRestoreService::class);
    $result = $service->verifyBackup($file);

    if (!$result['valid']) {
        $this->error('Backup verification FAILED: '.($result['error'] ?? 'Unknown error'));
        $this->line('SHA-256: '.($result['sha256'] ?? 'n/a'));

        return 1;
    }

    $this->info('Backup verification PASSED');
    $this->table(['Property', 'Value'], [
        ['File', $result['file']],
        ['Size (KB)', $result['size_kb']],
        ['Files in archive', $result['file_count']],
        ['SHA-256', $result['sha256']],
        ['Created at', $result['manifest']['created_at'] ?? 'n/a'],
        ['Environment', $result['manifest']['environment'] ?? 'n/a'],
        ['App name', $result['manifest']['app_name'] ?? 'n/a'],
    ]);
})->purpose('Verify integrity of a backup archive');

Artisan::command('privacy:export-user {userId}', function (int $userId) {
    /** @var User|null $user */
    $user = User::query()->find($userId);

    if (!$user) {
        $this->error("User {$userId} not found.");

        return;
    }

    /** @var UserDataExportService $service */
    $service = app(UserDataExportService::class);
    $file = $service->exportUserData($user);

    $this->info("User data export created: {$file}");
})->purpose('Export user data for privacy/GDPR workflows');

Artisan::command('privacy:enforce-retention', function () {
    /** @var RetentionPolicyService $service */
    $service = app(RetentionPolicyService::class);
    $result = $service->enforce();

    $this->table(['Policy', 'Deleted Rows'], [
        ['audit_logs', $result['audit_logs_deleted']],
        ['activity_logs', $result['activity_logs_deleted']],
        ['autosave_versions', $result['autosave_versions_deleted']],
        ['export_jobs', $result['export_jobs_deleted']],
    ]);
})->purpose('Apply data retention policies and purge old records');

Artisan::command('documents:encrypt-content', function () {
    $total = 0;
    $skipped = 0;

    \App\Models\Document::withTrashed()->chunkById(100, function ($documents) use (&$total, &$skipped) {
        foreach ($documents as $document) {
            $raw = $document->getRawOriginal('content');

            if ($raw === null) {
                $skipped++;
                continue;
            }

            try {
                \Illuminate\Support\Facades\Crypt::decryptString($raw);
                $skipped++; // Already encrypted.
            } catch (\Illuminate\Contracts\Encryption\DecryptException) {
                // Raw value is plaintext – sanitize and encrypt it.
                $document->content = $raw;
                $document->saveQuietly();
                $total++;
            }
        }
    });

    $this->info("Encrypted: {$total} document(s). Already encrypted / null: {$skipped}.");
})->purpose('Encrypt existing plaintext document content at rest');
