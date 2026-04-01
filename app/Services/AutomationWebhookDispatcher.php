<?php

namespace App\Services;

use App\Models\AutomationWebhook;
use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutomationWebhookDispatcher
{
    public function __construct(
        private readonly SlackWebhookNotifier $slackNotifier,
        private readonly DiscordWebhookNotifier $discordNotifier,
    ) {
    }

    public function dispatch(string $event, Document $document): void
    {
        if (! config('webhooks.enabled', true)) {
            return;
        }

        $webhooks = AutomationWebhook::query()
            ->where('team_id', $document->team_id)
            ->where('is_active', true)
            ->get();

        if ($webhooks->isEmpty()) {
            return;
        }

        foreach ($webhooks as $webhook) {
            if (! $webhook->listensFor($event)) {
                continue;
            }

            match ($webhook->provider) {
                'slack' => $this->slackNotifier->notifyDocumentEvent($event, $document, $webhook),
                'discord' => $this->discordNotifier->notifyDocumentEvent($event, $document, $webhook),
                default => $this->dispatchGenericWebhook($event, $document, $webhook),
            };
        }
    }

    private function dispatchGenericWebhook(string $event, Document $document, AutomationWebhook $webhook): void
    {
        $payload = [
            'event' => $event,
            'occurred_at' => now()->toIso8601String(),
            'document' => [
                'id' => $document->id,
                'team_id' => $document->team_id,
                'user_id' => $document->user_id,
                'title' => $document->title,
                'status' => $document->status,
                'version' => $document->version,
                'updated_at' => optional($document->updated_at)?->toIso8601String(),
                'deleted_at' => optional($document->deleted_at)?->toIso8601String(),
            ],
        ];

        $headers = [
            'X-DotDocs-Event' => $event,
            'X-DotDocs-Provider' => $webhook->provider,
            'User-Agent' => config('app.name', 'Dot.docs').'-Webhook/1.0',
        ];

        if ($webhook->secret) {
            $headers['X-DotDocs-Signature'] = hash_hmac('sha256', json_encode($payload), $webhook->secret);
        }

        try {
            $response = Http::timeout((int) config('webhooks.timeout_seconds', 10))
                ->withHeaders($headers)
                ->acceptJson()
                ->post($webhook->endpoint_url, $payload);

            $webhook->update([
                'last_triggered_at' => now(),
                'last_response_status' => $response->status(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Automation webhook dispatch failed.', [
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
}
