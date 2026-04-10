<?php

use App\Broadcasting\DocumentPresenceChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Presence channel for collaborative document editing
Broadcast::channel('document.{documentId}', DocumentPresenceChannel::class);
