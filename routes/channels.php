<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application uses. The required callback is used to authenticate the
| user's access to a particular channel when they attempt to join.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('private-document-{documentId}', function ($user, $documentId) {
    // Check if user has access to the document via team or share
    return $user->allTeams()->whereHas('documents', function ($query) use ($documentId) {
        $query->where('documents.id', $documentId);
    })->exists() || \App\Models\DocumentShare::query()
        ->where('document_id', $documentId)
        ->where('shared_with_user_id', $user->id)
        ->exists();
});

Broadcast::channel('private-user-{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
