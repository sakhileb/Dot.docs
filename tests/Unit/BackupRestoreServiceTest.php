<?php

namespace Tests\Unit;

use App\Services\BackupRestoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupRestoreServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_verifies_created_backups_successfully(): void
    {
        config([
            'backup.disk' => 'local',
            'backup.path' => 'backups/test-suite',
        ]);

        $service = app(BackupRestoreService::class);

        $backupPath = $service->createBackup('unit-test');
        $verification = $service->verifyBackup($backupPath);

        $this->assertTrue($verification['valid']);
        $this->assertSame($backupPath, $verification['file']);
        $this->assertArrayHasKey('sha256', $verification);
        $this->assertArrayHasKey('manifest', $verification);
        $this->assertSame(config('app.env'), $verification['manifest']['environment'] ?? null);

        Storage::disk('local')->delete($backupPath);
    }
}
