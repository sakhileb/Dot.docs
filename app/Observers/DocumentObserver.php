<?php

namespace App\Observers;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DocumentObserver
{
    /**
     * Assign a UUID before the document is first saved.
     */
    public function creating(Document $document): void
    {
        $document->uuid = (string) Str::uuid();
    }

    /**
     * Snapshot a new version whenever content changes.
     */
    public function updated(Document $document): void
    {
        // Bust permission + content caches
        $this->bustDocumentCache($document);

        if ($document->wasChanged('content') && $document->content !== null) {
            DocumentVersion::create([
                'document_id'      => $document->id,
                'content_snapshot' => $document->content,
                'version_number'   => $document->version,
                'created_by'       => auth()->id() ?? $document->owner_id,
                'created_at'       => now(),
            ]);

            // Fire on_save webhooks asynchronously (best-effort)
            app(WebhookService::class)->fire($document, 'on_save');
        }
    }

    public function deleted(Document $document): void
    {
        $this->bustDocumentCache($document);
    }

    private function bustDocumentCache(Document $document): void
    {
        // Forget all per-user permission cache entries for this document.
        // We iterate over users who have a relationship with this document.
        $userIds = collect([$document->owner_id])
            ->merge($document->collaborators()->pluck('user_id'))
            ->unique();

        foreach ($userIds as $userId) {
            Cache::forget("doc.view.{$userId}.{$document->id}");
            Cache::forget("doc.update.{$userId}.{$document->id}");
        }

        Cache::forget("doc.content.{$document->uuid}");
    }
}
