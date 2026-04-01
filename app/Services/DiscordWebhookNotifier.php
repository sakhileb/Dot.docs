<?php

namespace App\Services;

use App\Models\AutomationWebhook;
use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordWebhookNotifier
{
    public function notifyDocumentEvent(string $event, Document $document, AutomationWebhook $webhook): void
    {
        try {
            $embed = $this->buildEmbed($event, $document);

            $response = Http::timeout((int) config('webhooks.timeout_seconds', 10))
                ->post($webhook->endpoint_url, [
                    'content' => $this->getContent($event),
                    'embeds' => [$embed],
                ]);

            $webhook->update([
                'last_triggered_at' => now(),
                'last_response_status' => $response->status(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Discord webhook notification failed.', [
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

    private function buildEmbed(string $event, Document $document): array
    {
        $title = match ($event) {
            'document.created' => 'New Document Created',
            'document.updated' => 'Document Updated',
            'document.deleted' => 'Document Deleted',
            'document.restored' => 'Document Restored',
            default => ucfirst(str_replace('document.', '', $event)),
        };

        $color = match ($event) {
            'document.created' => 3447003,
            'document.updated' => 3447003,
            'document.deleted' => 15158332,
            'document.restored' => 15105570,
            default => 9807270,
        };

        return [
            'title' => $title,
            'description' => $document->title,
            'color' => $color,
            'fields' => [
                [
                    'name' => 'Status',
                    'value' => $document->status,
                    'inline' => true,
                ],
                [
                    'name' => 'Author',
                    'value' => $document->user->name,
                    'inline' => true,
                ],
                [
                    'name' => 'Version',
                    'value' => (string) $document->version,
                    'inline' => true,
                ],
                [
                    'name' => 'Last Updated',
                    'value' => optional($document->updated_at)?->format('Y-m-d H:i:s') ?? 'N/A',
                    'inline' => false,
                ],
            ],
            'timestamp' => now()->toIso8601String(),
        ];
    }

    private function getContent(string $event): string
    {
        return match ($event) {
            'document.created' => '✨ New document created!',
            'document.updated' => '✏️ Document updated!',
            'document.deleted' => '🗑️ Document deleted!',
            'document.restored' => '↩️ Document restored!',
            default => ucfirst(str_replace('document.', '', $event)),
        };
    }
}
