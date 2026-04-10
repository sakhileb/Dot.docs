<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Determine whether the user can view any documents.
     * Any authenticated user can see their own documents list.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the document.
     * Owner, team members, collaborators, or public documents.
     */
    public function view(User $user, Document $document): bool
    {
        if ($document->is_public) {
            return true;
        }

        if ($document->owner_id === $user->id) {
            return true;
        }

        if ($document->team_id && $user->belongsToTeam($document->team)) {
            return true;
        }

        return $document->collaborators()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create documents.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the document.
     * Owner, team admins/editors, or collaborators with editor role.
     */
    public function update(User $user, Document $document): bool
    {
        if ($document->owner_id === $user->id) {
            return true;
        }

        if ($document->team_id && $user->belongsToTeam($document->team)) {
            $role = $user->teamRole($document->team);
            return $role && in_array($role->key, ['admin', 'editor']);
        }

        return $document->collaborators()
            ->where('user_id', $user->id)
            ->whereIn('role', ['editor', 'admin'])
            ->exists();
    }

    /**
     * Determine whether the user can delete the document.
     * Only the owner or a team admin may delete.
     */
    public function delete(User $user, Document $document): bool
    {
        if ($document->owner_id === $user->id) {
            return true;
        }

        if ($document->team_id && $user->belongsToTeam($document->team)) {
            $role = $user->teamRole($document->team);
            return $role && $role->key === 'admin';
        }

        return false;
    }

    /**
     * Determine whether the user can restore the document (soft-deleted).
     */
    public function restore(User $user, Document $document): bool
    {
        return $document->owner_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the document.
     */
    public function forceDelete(User $user, Document $document): bool
    {
        return $document->owner_id === $user->id;
    }
}

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Document $document): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Document $document): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Document $document): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Document $document): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Document $document): bool
    {
        return false;
    }
}
