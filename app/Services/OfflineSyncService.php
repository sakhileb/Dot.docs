<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OfflineSyncService
{
    protected string $syncQueueKey = 'offline_sync_queue';

    public function queueDocumentForSync(int $documentId, array $changes): void
    {
        $queue = Cache::get($this->syncQueueKey, []);

        $queue[$documentId] = [
            'id' => $documentId,
            'changes' => $changes,
            'timestamp' => now()->toIso8601String(),
            'synced' => false,
        ];

        Cache::put($this->syncQueueKey, $queue, 86400 * 7); // 7 days
    }

    public function getSyncQueue(): array
    {
        return Cache::get($this->syncQueueKey, []);
    }

    public function syncDocument(int $documentId, array $changes): bool
    {
        try {
            $document = Document::find($documentId);

            if (!$document) {
                Log::warning("Tried to sync non-existent document: {$documentId}");
                return false;
            }

            // Apply changes incrementally
            if (isset($changes['content'])) {
                $document->content = $changes['content'];
            }

            if (isset($changes['title'])) {
                $document->title = $changes['title'];
            }

            $document->save();

            $this->markSynced($documentId);

            Log::info("Document {$documentId} synced from offline changes");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to sync document {$documentId}: {$e->getMessage()}");
            return false;
        }
    }

    public function markSynced(int $documentId): void
    {
        $queue = Cache::get($this->syncQueueKey, []);

        if (isset($queue[$documentId])) {
            $queue[$documentId]['synced'] = true;
            Cache::put($this->syncQueueKey, $queue, 86400 * 7);
        }
    }

    public function clearSyncQueue(): void
    {
        Cache::forget($this->syncQueueKey);
    }

    public function getPendingSyncs(): array
    {
        $queue = Cache::get($this->syncQueueKey, []);

        return array_filter($queue, function ($item) {
            return !$item['synced'];
        });
    }
}
