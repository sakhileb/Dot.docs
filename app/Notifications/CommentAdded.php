<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\DocumentComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;

class CommentAdded implements ShouldBroadcast
{
    use Queueable;

    public function __construct(
        public Document $document,
        public DocumentComment $comment,
        public User $author,
        public bool $isReply = false,
    ) {}

    public function via(object $notifiable): array
    {
        $prefs = $notifiable->notificationPreferences();

        $channels = [];
        if ($prefs->comments_email) {
            $channels[] = 'mail';
        }
        if ($prefs->comments_browser) {
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
            'title' => $this->isReply ? 'New Reply' : 'New Comment',
            'message' => $this->author->name.' posted a comment on '.$this->document->title,
            'document_id' => $this->document->id,
            'comment_id' => $this->comment->id,
            'is_reply' => $this->isReply,
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'title' => $this->isReply ? 'New Reply' : 'New Comment',
            'message' => $this->author->name.' posted a comment',
            'document_id' => $this->document->id,
            'comment_id' => $this->comment->id,
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
        return 'comment.added';
    }
}
