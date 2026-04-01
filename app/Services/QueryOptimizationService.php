<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Team;
use Illuminate\Support\Facades\Cache;

class QueryOptimizationService
{
    public static function getCachedUserDocuments(int $userId, int $teamId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "user_documents_{$userId}_{$teamId}";

        return Cache::remember($cacheKey, 3600, function () use ($userId, $teamId, $limit) {
            return Document::query()
                ->forUser($userId)
                ->forTeam($teamId)
                ->notArchived()
                ->with(['user', 'shares', 'comments'])
                ->recent()
                ->limit($limit)
                ->get();
        });
    }

    public static function getCachedTeamDocuments(int $teamId, int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "team_documents_{$teamId}";

        return Cache::remember($cacheKey, 3600, function () use ($teamId, $limit) {
            return Document::query()
                ->forTeam($teamId)
                ->notArchived()
                ->with(['user', 'teamRoles', 'favorites'])
                ->recent()
                ->limit($limit)
                ->get();
        });
    }

    public static function invalidateUserDocumentsCache(int $userId, int $teamId): void
    {
        Cache::forget("user_documents_{$userId}_{$teamId}");
    }

    public static function invalidateTeamDocumentsCache(int $teamId): void
    {
        Cache::forget("team_documents_{$teamId}");
    }

    public static function getCachedTeamMembers(int $teamId): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "team_members_{$teamId}";

        return Cache::remember($cacheKey, 7200, function () use ($teamId) {
            return Team::find($teamId)
                ->users()
                ->select('users.id', 'users.name', 'users.email', 'users.profile_photo_path')
                ->get();
        });
    }

    public static function invalidateTeamMembersCache(int $teamId): void
    {
        Cache::forget("team_members_{$teamId}");
    }
}
