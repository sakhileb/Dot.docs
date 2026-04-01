<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;

class UserMentioned implements ShouldBroadcast
{
    use Queueable;

    public function __construct(
        public Document $document,
        public User $mentionedBy,
        public User $recipient,
        public string $context,
    ) {}

    public function via(object $notifiable): array
    {
        $prefs = $notifiable->notificationPreferences ?? null;

        $channels = [];
        if ($prefs?->mentions_email) {
            $channels[] = 'mail';
        }
        if ($prefs?->mentions_browser) {
            $channels[] = 'database';
        }

        return [
            ...$channels,
            'broadcast',
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'You Were Mentioned',
            'message' => $this->mentionedBy->name.' mentioned you in '.$this->document->title,
            'document_id' => $this->document->id,
            'context' => $this->context,
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'title' => 'You Were Mentioned',
            'message' => $this->mentionedBy->name.' mentioned you',
            'document_id' => $this->document->id,
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new \Illuminate\Broadcasting\PrivateChannel('private-user-'.$this->recipient->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'user.mentioned';
    }
}
