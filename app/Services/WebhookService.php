<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentWebhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Fire all active webhooks for a document and event type.
     *
     * @param  Document  $document
     * @param  string    $event     'on_save' | 'on_export'
     * @param  array     $payload   Extra data merged into the body
     */
    public function fire(Document $document, string $event, array $payload = []): void
    {
        $webhooks = DocumentWebhook::where('document_id', $document->id)
            ->where('active', true)
            ->get();

        foreach ($webhooks as $webhook) {
            if (! in_array($event, $webhook->events ?? [])) {
                continue;
            }

            $body = array_merge([
                'event'        => $event,
                'document_id'  => $document->id,
                'document_uuid'=> $document->uuid,
                'title'        => $document->title,
                'version'      => $document->version,
                'timestamp'    => now()->toIso8601String(),
            ], $payload);

            $headers = ['Content-Type' => 'application/json'];

            if ($webhook->secret) {
                $sig = hash_hmac('sha256', json_encode($body), $webhook->secret);
                $headers['X-Dotdocs-Signature'] = "sha256={$sig}";
            }

            try {
                Http::withHeaders($headers)
                    ->timeout(5)
                    ->post($webhook->url, $body);
            } catch (\Throwable $e) {
                Log::warning("Webhook delivery failed [{$webhook->id}]: " . $e->getMessage());
            }
        }
    }
}
