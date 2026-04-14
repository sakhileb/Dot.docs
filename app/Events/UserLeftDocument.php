<?php

namespace App\Events;

use App\Models\Document;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLeftDocument implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Document $document,
        public readonly User $user,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('document.' . $this->document->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'user.left';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
        ];
    }
}
