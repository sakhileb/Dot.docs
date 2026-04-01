<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\DocumentReview;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;

class ReviewRequested implements ShouldBroadcast
{
    use Queueable;

    public function __construct(
        public DocumentReview $review,
        public Document $document,
        public User $requestedBy,
        public User $recipient,
    ) {}

    public function via(object $notifiable): array
    {
        $prefs = $notifiable->notificationPreferences ?? null;

        $channels = [];
        if ($prefs?->reviews_email) {
            $channels[] = 'mail';
        }
        if ($prefs?->reviews_browser) {
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
            'title' => 'Review Requested',
            'message' => $this->requestedBy->name.' requested a '.$this->review->review_type.' review of '.$this->document->title,
            'document_id' => $this->document->id,
            'review_id' => $this->review->id,
            'review_type' => $this->review->review_type,
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'title' => 'Review Requested',
            'message' => $this->requestedBy->name.' requests your review',
            'document_id' => $this->document->id,
            'review_type' => $this->review->review_type,
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
        return 'review.requested';
    }
}
