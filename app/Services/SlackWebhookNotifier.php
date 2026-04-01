<?php

namespace App\Services;

use App\Models\AutomationWebhook;
use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackWebhookNotifier
{
    public function notifyDocumentEvent(string $event, Document $document, AutomationWebhook $webhook): void
    {
        try {
            $message = $this->buildMessage($event, $document);

            $response = Http::timeout((int) config('webhooks.timeout_seconds', 10))
                ->post($webhook->endpoint_url, [
                    'text' => $message['text'],
                    'blocks' => $message['blocks'] ?? null,
                ]);

            $webhook->update([
                'last_triggered_at' => now(),
                'last_response_status' => $response->status(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Slack webhook notification failed.', [
                'webhook_id' => $webhook->id,
                'team_id' => $webhook->team_id,
                'event' => $event,
                'error' => $exception->getMessage(),
            ]);

            $webhook->update([
                'last_triggered_at' => now(),
                'last_response_status' => null,
            ]);
        }
    }

    private function buildMessage(string $event, Document $document): array
    {
        $title = match ($event) {
            'document.created' => ':sparkles: New Document Created',
            'document.updated' => ':pencil: Document Updated',
            'document.deleted' => ':wastebasket: Document Deleted',
            'document.restored' => ':arrow_counterclockwise: Document Restored',
            default => ucfirst(str_replace('document.', '', $event)),
        };

        $color = match ($event) {
            'document.created' => '#36a64f',
            'document.updated' => '#0099ff',
            'document.deleted' => '#ff3333',
            'document.restored' => '#ff9900',
            default => '#808080',
        };

        return [
            'text' => "{$title} - {$document->title}",
            'blocks' => [
                [
                    'type' => 'header',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => $title,
                    ],
                ],
                [
                    'type' => 'section',
                    'fields' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Document:*\n{$document->title}",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Status:*\n{$document->status}",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Author:*\n{$document->user->name}",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Version:*\n{$document->version}",
                        ],
                    ],
                ],
            ],
        ];
    }
}
