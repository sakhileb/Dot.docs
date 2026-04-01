<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Phar;
use PharData;
use RuntimeException;

class BackupRestoreService
{
    public function createBackup(?string $label = null): string
    {
        $disk = Storage::disk(config('backup.disk', 'local'));
        $backupPath = trim(config('backup.path', 'backups'), '/');
        $timestamp = now()->format('Ymd_His');
        $suffix = filled($label) ? '_'.Str::slug((string) $label) : '';
        $filenameBase = "backup_{$timestamp}{$suffix}";
        $relativePath = "{$backupPath}/{$filenameBase}.tar.gz";
        $absolutePath = $disk->path($relativePath);

        $absoluteDir = dirname($absolutePath);
        if (!File::exists($absoluteDir)) {
            File::makeDirectory($absoluteDir, 0755, true);
        }

        $tempTarPath = storage_path("app/tmp/{$filenameBase}.tar");
        if (!File::exists(dirname($tempTarPath))) {
            File::makeDirectory(dirname($tempTarPath), 0755, true);
        }

        if (File::exists($tempTarPath)) {
            File::delete($tempTarPath);
        }

        $archive = new PharData($tempTarPath);

        $manifest = [
            'created_at' => now()->toIso8601String(),
            'app_name' => config('app.name'),
            'environment' => config('app.env'),
            'database_connection' => config('database.default'),
        ];
        $archive->addFromString('meta/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

        $this->appendDatabaseToArchive($archive);
        $this->appendStorageToArchive($archive);

        $archive->compress(Phar::GZ);

        $gzTempPath = "{$tempTarPath}.gz";
        if (!File::exists($gzTempPath)) {
            throw new RuntimeException('Unable to create compressed backup archive.');
        }

        File::move($gzTempPath, $absolutePath);
        File::delete($tempTarPath);

        return $relativePath;
    }

    public function listBackups(): array
    {
        $disk = Storage::disk(config('backup.disk', 'local'));
        $backupPath = trim(config('backup.path', 'backups'), '/');

        $files = collect($disk->files($backupPath))
            ->filter(fn (string $file) => str_ends_with($file, '.tar.gz'))
            ->map(function (string $file) use ($disk) {
                return [
                    'file' => $file,
                    'size_kb' => round($disk->size($file) / 1024, 2),
                    'last_modified' => date('Y-m-d H:i:s', $disk->lastModified($file)),
                ];
            })
            ->sortByDesc('last_modified')
            ->values();

        return $files->all();
    }

    public function restoreBackup(string $relativePath): void
    {
        $disk = Storage::disk(config('backup.disk', 'local'));

        if (!$disk->exists($relativePath)) {
            throw new RuntimeException("Backup file not found: {$relativePath}");
        }

        $archivePath = $disk->path($relativePath);
        $tempDir = storage_path('app/tmp/restore_'.Str::random(10));
        File::makeDirectory($tempDir, 0755, true);

        $archive = new PharData($archivePath);
        $archive->extractTo($tempDir, null, true);

        $this->restoreDatabaseFromExtract($tempDir);
        $this->restoreStorageFromExtract($tempDir);

        File::deleteDirectory($tempDir);
    }

    public function cleanupOldBackups(?int $keepDays = null): int
    {
        $days = $keepDays ?? (int) config('backup.keep_days', 14);
        $cutoff = now()->subDays($days)->timestamp;
        $disk = Storage::disk(config('backup.disk', 'local'));
        $backupPath = trim(config('backup.path', 'backups'), '/');

        $deleted = 0;

        foreach ($disk->files($backupPath) as $file) {
            if (!str_ends_with($file, '.tar.gz')) {
                continue;
            }

            if ($disk->lastModified($file) < $cutoff) {
                $disk->delete($file);
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Verify a backup archive's integrity and return a detailed report.
     *
     * @return array{valid: bool, file: string, size_kb: float, sha256: string, file_count: int, manifest: array<string,mixed>}
     *             On failure: array{valid: bool, error: string, sha256: string}
     */
    public function verifyBackup(string $relativePath): array
    {
        $disk = Storage::disk(config('backup.disk', 'local'));

        if (!$disk->exists($relativePath)) {
            throw new RuntimeException("Backup file not found: {$relativePath}");
        }

        $archivePath = $disk->path($relativePath);
        $sha256 = hash_file('sha256', $archivePath);
        $sizeKb = round($disk->size($relativePath) / 1024, 2);
        $tempDir = storage_path('app/tmp/verify_'.Str::random(10));

        File::makeDirectory($tempDir, 0755, true);

        try {
            $archive = new PharData($archivePath);

            // Count all entries in the archive.
            $fileCount = 0;
            /** @var \PharFileInfo $entry */
            foreach (new \RecursiveIteratorIterator($archive) as $entry) {
                $fileCount++;
            }

            // Extract and validate the manifest.
            $archive->extractTo($tempDir, 'meta/manifest.json', true);
            $manifestPath = $tempDir.'/meta/manifest.json';

            if (!File::exists($manifestPath)) {
                return [
                    'valid' => false,
                    'error' => 'Archive is missing meta/manifest.json.',
                    'sha256' => $sha256,
                ];
            }

            /** @var array<string,mixed> $manifest */
            $manifest = json_decode(File::get($manifestPath), true);

            return [
                'valid' => true,
                'file' => $relativePath,
                'size_kb' => $sizeKb,
                'sha256' => $sha256,
                'file_count' => $fileCount,
                'manifest' => $manifest,
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage(),
                'sha256' => $sha256,
            ];
        } finally {
            File::deleteDirectory($tempDir);
        }
    }

    private function appendDatabaseToArchive(PharData $archive): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        $sqlitePath = (string) config('database.connections.sqlite.database');
        if ($sqlitePath !== '' && File::exists($sqlitePath)) {
            $archive->addFile($sqlitePath, 'database/database.sqlite');
        }
    }

    private function appendStorageToArchive(PharData $archive): void
    {
        $directories = [
            storage_path('app/public') => 'storage/app/public',
            storage_path('app/private') => 'storage/app/private',
        ];
        $backupDirectory = storage_path('app/private/'.trim(config('backup.path', 'backups'), '/'));

        foreach ($directories as $source => $target) {
            if (!File::isDirectory($source)) {
                continue;
            }

            $files = File::allFiles($source);
            foreach ($files as $file) {
                $absolutePath = $file->getRealPath();
                if (!$absolutePath) {
                    continue;
                }

                if (str_starts_with($absolutePath, $backupDirectory)) {
                    continue;
                }

                $relative = ltrim(str_replace($source, '', $absolutePath), DIRECTORY_SEPARATOR);
                $archive->addFile($absolutePath, $target.'/'.$relative);
            }
        }
    }

    private function restoreDatabaseFromExtract(string $tempDir): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        $extractedSqlite = $tempDir.'/database/database.sqlite';
        $targetSqlite = (string) config('database.connections.sqlite.database');

        if (!File::exists($extractedSqlite) || $targetSqlite === '') {
            return;
        }

        $targetDir = dirname($targetSqlite);
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        File::copy($extractedSqlite, $targetSqlite);
    }

    private function restoreStorageFromExtract(string $tempDir): void
    {
        $mappings = [
            $tempDir.'/storage/app/public' => storage_path('app/public'),
            $tempDir.'/storage/app/private' => storage_path('app/private'),
        ];

        foreach ($mappings as $from => $to) {
            if (!File::isDirectory($from)) {
                continue;
            }

            if (!File::exists($to)) {
                File::makeDirectory($to, 0755, true);
            }

            File::copyDirectory($from, $to);
        }
    }
}
