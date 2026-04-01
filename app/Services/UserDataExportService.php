<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserDataExportService
{
    public function exportUserData(User $user): string
    {
        $disk = Storage::disk(config('data-protection.disk', 'local'));
        $exportPath = trim(config('data-protection.export_path', 'exports'), '/');
        $filename = "user_{$user->id}_".now()->format('Ymd_His').'.json';
        $relativePath = "{$exportPath}/{$filename}";

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'user' => $user->only(['id', 'name', 'email', 'created_at', 'updated_at']),
            'teams' => $user->allTeams()->map(fn ($team) => $team->only(['id', 'name', 'created_at']))->values()->all(),
            'documents' => $user->documents()->get()->toArray(),
            'document_comments' => $user->documentComments()->get()->toArray(),
            'shares_created' => $user->sharesCreated()->get()->toArray(),
            'shares_received' => $user->sharesReceived()->get()->toArray(),
            'favorites' => $user->favorites()->get()->toArray(),
            'folders' => $user->folders()->get()->toArray(),
            'document_export_jobs' => $user->documentExportJobs()->get()->toArray(),
        ];

        $disk->put($relativePath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $relativePath;
    }
}
