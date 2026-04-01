<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;

class DocumentChanged implements ShouldBroadcast
{
    use Queueable;

    public function __construct(
        public Document $document,
        public User $changedBy,
        public string $changeType,
        public ?string $changeSummary = null,
    ) {}

    public function via(object $notifiable): array
    {
        $prefs = $notifiable->notificationPreferences();

        $channels = [];
        if ($prefs->document_changes_email) {
            $channels[] = 'mail';
        }
        if ($prefs->document_changes_browser) {
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
            'title' => 'Document Updated',
            'message' => $this->changedBy->name.' updated '.$this->document->title,
            'document_id' => $this->document->id,
            'change_type' => $this->changeType,
            'change_summary' => $this->changeSummary,
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'title' => 'Document Updated',
            'message' => $this->changedBy->name.' updated '.$this->document->title,
            'document_id' => $this->document->id,
            'change_type' => $this->changeType,
        ];
    }

    public function broadcastOn(): array
    {
        return [
            'private-document-'.$this->document->id,
        ];
    }

    public function broadcastAs(): string
    {
        return 'document.changed';
    }
}
