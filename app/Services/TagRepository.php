<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Tags are scoped the same way Document itself is (team-wide when the
 * document has a team, personal otherwise). Uniqueness within that scope
 * is enforced here rather than a DB constraint -- see the migration for
 * why a composite unique index with a nullable team_id isn't reliable
 * across drivers for this.
 */
class TagRepository
{
    public function findOrCreate(User $user, ?int $teamId, string $name): Tag
    {
        $name = trim($name);

        $existing = Tag::where('name', $name)
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->when(! $teamId, fn ($q) => $q->whereNull('team_id')->where('owner_id', $user->id))
            ->first();

        if ($existing) {
            return $existing;
        }

        return Tag::create([
            'owner_id' => $user->id,
            'team_id' => $teamId,
            'name' => $name,
        ]);
    }

    /**
     * @return Collection<int, Tag>
     */
    public function availableFor(User $user, ?int $teamId): Collection
    {
        return Tag::when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->when(! $teamId, fn ($q) => $q->whereNull('team_id')->where('owner_id', $user->id))
            ->orderBy('name')
            ->get();
    }
}
