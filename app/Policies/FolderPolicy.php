<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;

/**
 * A folder is purely organizational (see the folders migration) -- these
 * checks only govern the folder object itself (rename/delete/move it,
 * see it in the folder tree). They never grant or restrict access to the
 * documents inside it; DocumentPolicy alone decides that, unaffected by
 * which folder a document is filed under.
 */
class FolderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Folder $folder): bool
    {
        return $this->belongsToScope($user, $folder);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Folder $folder): bool
    {
        return $this->belongsToScope($user, $folder);
    }

    public function delete(User $user, Folder $folder): bool
    {
        return $this->belongsToScope($user, $folder);
    }

    private function belongsToScope(User $user, Folder $folder): bool
    {
        if ($folder->owner_id === $user->id) {
            return true;
        }

        return $folder->team_id && $user->belongsToTeam($folder->team);
    }
}
