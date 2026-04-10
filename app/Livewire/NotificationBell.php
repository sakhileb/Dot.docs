<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class NotificationBell extends Component
{
    public int $unreadCount = 0;
    public array $notifications = [];
    public bool $open = false;

    public function mount(): void
    {
        $this->loadNotifications();
    }

    #[On('notification-received')]
    public function loadNotifications(): void
    {
        $user = auth()->user();
        $this->unreadCount = $user->unreadNotifications()->count();
        $this->notifications = $user->notifications()
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($n) => [
                'id'        => $n->id,
                'type'      => $n->data['type'] ?? 'notification',
                'message'   => $this->formatMessage($n->data),
                'url'       => $n->data['url'] ?? null,
                'read'      => !is_null($n->read_at),
                'time'      => $n->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        $this->loadNotifications();
    }

    public function markRead(string $id): void
    {
        auth()->user()->notifications()->where('id', $id)->update(['read_at' => now()]);
        $this->loadNotifications();
    }

    public function toggle(): void
    {
        $this->open = !$this->open;
        if ($this->open) {
            $this->loadNotifications();
        }
    }

    private function formatMessage(array $data): string
    {
        return match($data['type'] ?? '') {
            'comment'   => ($data['commenter'] ?? 'Someone') . ' commented on "' . ($data['document_title'] ?? 'a document') . '"',
            'mention'   => ($data['mentioner'] ?? 'Someone') . ' mentioned you in "' . ($data['document_title'] ?? 'a document') . '"',
            default     => 'You have a new notification',
        };
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}

