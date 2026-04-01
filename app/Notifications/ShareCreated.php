<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;

class ShareCreated implements ShouldBroadcast
{
    use Queueable;

    public function __construct(
        public Document $document,
        public User $sharedBy,
        public string $permission,
        public User $recipient,
    ) {}

    public function via(object $notifiable): array
    {
        $prefs = $notifiable->notificationPreferences ?? null;

        $channels = [];
        if ($prefs?->shares_email) {
            $channels[] = 'mail';
        }
        if ($prefs?->shares_browser) {
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
            'title' => 'Document Shared With You',
            'message' => $this->sharedBy->name.' shared '.$this->document->title.' ('.$this->permission.' access)',
            'document_id' => $this->document->id,
            'permission' => $this->permission,
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'title' => 'Document Shared',
            'message' => $this->sharedBy->name.' shared a document with '.$this->permission.' access',
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
        return 'share.created';
    }
}
