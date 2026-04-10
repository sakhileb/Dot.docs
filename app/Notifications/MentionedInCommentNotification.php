<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentionedInCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Document $document,
        public readonly Comment $comment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->comment->user->name . ' mentioned you in "' . $this->document->title . '"')
            ->line($this->comment->user->name . ' mentioned you in a comment.')
            ->line('"' . \Illuminate\Support\Str::limit($this->comment->content, 120) . '"')
            ->action('View Document', route('documents.edit', $this->document->uuid));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'mention',
            'document_id'   => $this->document->id,
            'document_uuid' => $this->document->uuid,
            'document_title'=> $this->document->title,
            'comment_id'    => $this->comment->id,
            'mentioner'     => $this->comment->user->name,
            'excerpt'       => \Illuminate\Support\Str::limit($this->comment->content, 80),
            'url'           => route('documents.edit', $this->document->uuid),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
