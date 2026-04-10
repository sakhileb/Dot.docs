<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Manages per-document active-user presence using the cache (Redis in production).
 * Keys are short-lived (5 min TTL) — clients must heartbeat to stay active.
 */
class PresenceService
{
    private const TTL    = 300;  // seconds before a user is considered gone
    private const PREFIX = 'doc_presence:';

    private function key(int $documentId): string
    {
        return self::PREFIX . $documentId;
    }

    /**
     * Mark a user as actively viewing/editing a document.
     */
    public function join(Document $document, User $user): void
    {
        $members = $this->getMembers($document->id);

        $members[$user->id] = [
            'id'         => $user->id,
            'name'       => $user->name,
            'avatar'     => $user->profile_photo_url,
            'joined_at'  => now()->toIso8601String(),
            'last_seen'  => now()->toIso8601String(),
        ];

        Cache::put($this->key($document->id), $members, self::TTL);
    }

    /**
     * Remove a user from the document's active set.
     */
    public function leave(Document $document, User $user): void
    {
        $members = $this->getMembers($document->id);
        unset($members[$user->id]);

        if (empty($members)) {
            Cache::forget($this->key($document->id));
        } else {
            Cache::put($this->key($document->id), $members, self::TTL);
        }
    }

    /**
     * Refresh a user's last-seen timestamp (heartbeat).
     */
    public function heartbeat(Document $document, User $user): void
    {
        $members = $this->getMembers($document->id);

        if (isset($members[$user->id])) {
            $members[$user->id]['last_seen'] = now()->toIso8601String();
            Cache::put($this->key($document->id), $members, self::TTL);
        } else {
            $this->join($document, $user);
        }
    }

    /**
     * Update a user's cursor position inside the document.
     */
    public function updateCursor(Document $document, User $user, array $cursor): void
    {
        $members = $this->getMembers($document->id);

        if (isset($members[$user->id])) {
            $members[$user->id]['cursor']    = $cursor;
            $members[$user->id]['last_seen'] = now()->toIso8601String();
            Cache::put($this->key($document->id), $members, self::TTL);
        }
    }

    /**
     * Get all currently active members for a document (keyed by user ID).
     */
    public function getMembers(int $documentId): array
    {
        return Cache::get($this->key($documentId), []);
    }

    /**
     * Return a simple array of member data for broadcasting.
     */
    public function getMemberList(int $documentId): array
    {
        return array_values($this->getMembers($documentId));
    }
}
