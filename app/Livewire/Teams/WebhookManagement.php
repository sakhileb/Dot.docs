<?php

namespace App\Livewire\Teams;

use App\Models\AutomationWebhook;
use App\Models\Team;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Livewire\WithPagination;

class WebhookManagement extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public Team $team;

    public bool $showCreateForm = false;

    public string $webhookName = '';

    public string $webhookProvider = 'zapier';

    public string $webhookUrl = '';

    public string $webhookSecret = '';

    public array $selectedEvents = [];

    public ?int $editingWebhookId = null;

    protected $queryString = ['page'];

    public const AVAILABLE_PROVIDERS = ['zapier', 'make', 'slack', 'discord'];

    public const AVAILABLE_EVENTS = [
        'document.created' => 'Document Created',
        'document.updated' => 'Document Updated',
        'document.deleted' => 'Document Deleted',
        'document.restored' => 'Document Restored',
    ];

    public function mount(Team $team): void
    {
        $this->authorize('update', $team);
        $this->team = $team;
    }

    public function openCreateForm(): void
    {
        $this->resetForm();
        $this->showCreateForm = true;
    }

    public function closeForm(): void
    {
        $this->showCreateForm = false;
        $this->editingWebhookId = null;
        $this->resetForm();
    }

    public function create(): void
    {
        $validated = $this->validate([
            'webhookName' => ['required', 'string', 'max:255'],
            'webhookProvider' => ['required', 'in:' . implode(',', self::AVAILABLE_PROVIDERS)],
            'webhookUrl' => ['required', 'url'],
            'webhookSecret' => ['nullable', 'string', 'max:255'],
            'selectedEvents' => ['required', 'array', 'min:1'],
            'selectedEvents.*' => ['in:' . implode(',', array_keys(self::AVAILABLE_EVENTS))],
        ]);

        AutomationWebhook::create([
            'team_id' => $this->team->id,
            'name' => $validated['webhookName'],
            'provider' => $validated['webhookProvider'],
            'endpoint_url' => $validated['webhookUrl'],
            'secret' => $validated['webhookSecret'] ?: null,
            'subscribed_events' => $validated['selectedEvents'],
            'is_active' => true,
        ]);

        $this->dispatch('notify', type: 'success', message: 'Webhook created successfully.');
        $this->closeForm();
        $this->resetPage();
    }

    public function editWebhook(int $webhookId): void
    {
        $webhook = $this->team->automationWebhooks()->findOrFail($webhookId);

        $this->editingWebhookId = $webhookId;
        $this->webhookName = $webhook->name;
        $this->webhookProvider = $webhook->provider;
        $this->webhookUrl = $webhook->endpoint_url;
        $this->webhookSecret = $webhook->secret ?? '';
        $this->selectedEvents = $webhook->subscribed_events ?? [];
        $this->showCreateForm = true;
    }

    public function update(): void
    {
        $webhook = $this->team->automationWebhooks()->findOrFail($this->editingWebhookId);

        $validated = $this->validate([
            'webhookName' => ['required', 'string', 'max:255'],
            'webhookProvider' => ['required', 'in:' . implode(',', self::AVAILABLE_PROVIDERS)],
            'webhookUrl' => ['required', 'url'],
            'webhookSecret' => ['nullable', 'string', 'max:255'],
            'selectedEvents' => ['required', 'array', 'min:1'],
            'selectedEvents.*' => ['in:' . implode(',', array_keys(self::AVAILABLE_EVENTS))],
        ]);

        $webhook->update([
            'name' => $validated['webhookName'],
            'provider' => $validated['webhookProvider'],
            'endpoint_url' => $validated['webhookUrl'],
            'secret' => $validated['webhookSecret'] ?: null,
            'subscribed_events' => $validated['selectedEvents'],
        ]);

        $this->dispatch('notify', type: 'success', message: 'Webhook updated successfully.');
        $this->closeForm();
    }

    public function delete(int $webhookId): void
    {
        $webhook = $this->team->automationWebhooks()->findOrFail($webhookId);
        $webhook->delete();

        $this->dispatch('notify', type: 'success', message: 'Webhook deleted successfully.');
    }

    public function toggleActive(int $webhookId): void
    {
        $webhook = $this->team->automationWebhooks()->findOrFail($webhookId);
        $webhook->update(['is_active' => ! $webhook->is_active]);

        $this->dispatch('notify', type: 'success', message: 'Webhook status updated.');
    }

    public function testWebhook(int $webhookId): void
    {
        $webhook = $this->team->automationWebhooks()->findOrFail($webhookId);

        $testPayload = [
            'event' => 'test',
            'occurred_at' => now()->toIso8601String(),
            'document' => [
                'id' => 0,
                'team_id' => $this->team->id,
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

            $this->dispatch('notify', type: 'success', message: "Webhook test sent! Response: {$response->status()}");
        } catch (\Throwable $exception) {
            $this->dispatch('notify', type: 'error', message: "Test failed: {$exception->getMessage()}");
        }
    }

    public function render()
    {
        return view('livewire.teams.webhook-management', [
            'webhooks' => $this->team->automationWebhooks()->paginate(10),
            'availableProviders' => self::AVAILABLE_PROVIDERS,
            'availableEvents' => self::AVAILABLE_EVENTS,
        ]);
    }

    private function resetForm(): void
    {
        $this->webhookName = '';
        $this->webhookProvider = 'zapier';
        $this->webhookUrl = '';
        $this->webhookSecret = '';
        $this->selectedEvents = [];
    }
}
