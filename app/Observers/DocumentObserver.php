<?php

namespace App\Observers;

use App\Models\Document;
use App\Models\DocumentVersion;
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
        if ($document->wasChanged('content') && $document->content !== null) {
            DocumentVersion::create([
                'document_id'      => $document->id,
                'content_snapshot' => $document->content,
                'version_number'   => $document->version,
                'created_by'       => auth()->id() ?? $document->owner_id,
                'created_at'       => now(),
            ]);
        }
    }
}
