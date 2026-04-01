<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutomationWebhook;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;

class AutomationWebhookController extends Controller
{
    public const AVAILABLE_PROVIDERS = ['zapier', 'make', 'slack', 'discord'];

    public const AVAILABLE_EVENTS = [
        'document.created' => 'Document Created',
        'document.updated' => 'Document Updated',
        'document.deleted' => 'Document Deleted',
        'document.restored' => 'Document Restored',
    ];

    public function index(Request $request, Team $team): JsonResponse
    {
        Gate::authorize('update', $team);

        $webhooks = $team->automationWebhooks()
            ->orderByDesc('created_at')
            ->paginate((int) $request->input('per_page', 15));

        return response()->json($webhooks);
    }

    public function store(Request $request, Team $team): JsonResponse
    {
        Gate::authorize('update', $team);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['required', 'in:'.implode(',', self::AVAILABLE_PROVIDERS)],
            'endpoint_url' => ['required', 'url'],
            'secret' => ['nullable', 'string', 'max:255'],
            'subscribed_events' => ['required', 'array', 'min:1'],
            'subscribed_events.*' => ['in:'.implode(',', array_keys(self::AVAILABLE_EVENTS))],
        ]);

        $webhook = AutomationWebhook::create([
            'team_id' => $team->id,
            'name' => $validated['name'],
            'provider' => $validated['provider'],
            'endpoint_url' => $validated['endpoint_url'],
            'secret' => $validated['secret'] ?? null,
            'subscribed_events' => $validated['subscribed_events'],
            'is_active' => true,
        ]);

        return response()->json($webhook, 201);
    }

    public function show(Team $team, int $webhookId): JsonResponse
    {
        Gate::authorize('update', $team);

        $webhook = $team->automationWebhooks()->findOrFail($webhookId);

        return response()->json($webhook);
    }

    public function update(Request $request, Team $team, int $webhookId): JsonResponse
    {
        Gate::authorize('update', $team);

        $webhook = $team->automationWebhooks()->findOrFail($webhookId);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'provider' => ['sometimes', 'in:'.implode(',', self::AVAILABLE_PROVIDERS)],
            'endpoint_url' => ['sometimes', 'url'],
            'secret' => ['nullable', 'string', 'max:255'],
            'subscribed_events' => ['sometimes', 'array', 'min:1'],
            'subscribed_events.*' => ['in:'.implode(',', array_keys(self::AVAILABLE_EVENTS))],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $webhook->update($validated);

        return response()->json($webhook->fresh());
    }

    public function destroy(Team $team, int $webhookId): JsonResponse
    {
        Gate::authorize('update', $team);

        $webhook = $team->automationWebhooks()->findOrFail($webhookId);
        $webhook->delete();

        return response()->json(null, 204);
    }

    public function test(Team $team, int $webhookId): JsonResponse
    {
        Gate::authorize('update', $team);

        $webhook = $team->automationWebhooks()->findOrFail($webhookId);

        $testPayload = [
            'event' => 'test',
            'occurred_at' => now()->toIso8601String(),
            'document' => [
                'id' => 0,
                'team_id' => $team->id,
                'title' => 'Test Document',
                'status' => 'draft',
                'version' => 1,
            ],
        ];

        try {
            $headers = [
                'X-DotDocs-Event' => 'test',
                'X-DotDocs-Provider' => $webhook->provider,
                'User-Agent' => config('app.name', 'Dot.docs').'-Webhook/1.0',
            ];

            if ($webhook->secret) {
                $headers['X-DotDocs-Signature'] = hash_hmac('sha256', json_encode($testPayload), $webhook->secret);
            }

            $response = Http::timeout(5)
                ->withHeaders($headers)
                ->acceptJson()
                ->post($webhook->endpoint_url, $testPayload);

            $webhook->update([
                'last_triggered_at' => now(),
                'last_response_status' => $response->status(),
            ]);

            return response()->json([
                'success' => true,
                'status' => $response->status(),
                'message' => 'Webhook test sent successfully.',
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Test failed: '.$exception->getMessage(),
            ], 400);
        }
    }

    public function documentation(): JsonResponse
    {
        return response()->json([
            'providers' => self::AVAILABLE_PROVIDERS,
            'events' => self::AVAILABLE_EVENTS,
            'documentation' => [
                'zapier' => 'Connect to Zapier using a Zapier webhook URL.',
                'make' => 'Connect to Make (formerly Integromat) using a Make webhook URL.',
                'slack' => 'Connect to Slack using a Slack webhook URL.',
                'discord' => 'Connect to Discord using a Discord webhook URL.',
            ],
        ]);
    }
}
