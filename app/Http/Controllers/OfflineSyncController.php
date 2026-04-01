<?php

namespace App\Http\Controllers;

use App\Services\OfflineSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class OfflineSyncController extends Controller
{
    protected OfflineSyncService $syncService;

    public function __construct(OfflineSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function syncDocument(Request $request, int $documentId)
    {
        $document = \App\Models\Document::findOrFail($documentId);

        // Check authorization
        if (!Gate::allows('update', $document)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => 'string',
            'title' => 'string|max:255',
        ]);

        $synced = $this->syncService->syncDocument($documentId, $validated);

        return response()->json([
            'success' => $synced,
            'message' => $synced ? 'Document synced successfully' : 'Failed to sync document',
        ], $synced ? 200 : 500);
    }

    public function getPendingSyncs()
    {
        $pending = $this->syncService->getPendingSyncs();

        return response()->json([
            'pending' => $pending,
            'count' => count($pending),
        ]);
    }

    public function getSyncStatus(int $documentId)
    {
        $queue = $this->syncService->getSyncQueue();

        return response()->json([
            'synced' => isset($queue[$documentId]) ? $queue[$documentId]['synced'] : null,
            'timestamp' => isset($queue[$documentId]) ? $queue[$documentId]['timestamp'] : null,
        ]);
    }
}
