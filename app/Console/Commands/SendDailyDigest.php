<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\DailyDigestNotification;
use Illuminate\Console\Command;

class SendDailyDigest extends Command
{
    protected $signature = 'notifications:digest';
    protected $description = 'Send daily email digests for unread notifications';

    public function handle(): int
    {
        $cutoff = now()->subDay();

        User::query()
            ->whereHas('notifications', fn($q) => $q->whereNull('read_at')->where('created_at', '>=', $cutoff))
            ->with(['notifications' => fn($q) => $q->whereNull('read_at')->where('created_at', '>=', $cutoff)->latest()->limit(20)])
            ->each(function (User $user) {
                $unread = $user->notifications->filter(fn($n) => is_null($n->read_at));
                if ($unread->isEmpty()) return;

                $items = $unread->map(fn($n) => [
                    'message' => $this->formatMessage($n->data),
                ])->values()->all();

                $user->notify(new DailyDigestNotification($unread->count(), $items));
            });

        $this->info('Daily digest sent.');
        return self::SUCCESS;
    }

    private function formatMessage(array $data): string
    {
        return match($data['type'] ?? '') {
            'comment' => ($data['commenter'] ?? 'Someone') . ' commented on "' . ($data['document_title'] ?? 'a document') . '"',
            'mention' => ($data['mentioner'] ?? 'Someone') . ' mentioned you in "' . ($data['document_title'] ?? 'a document') . '"',
            default   => 'New notification',
        };
    }
}
