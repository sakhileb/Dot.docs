<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\DocumentWebhook;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Component;

class WebhookManager extends Component
{
    use AuthorizesRequests;

    public Document $document;

    // New webhook form
    public string $newUrl = '';
    public array $newEvents = ['on_save', 'on_export'];
    public bool $generateSecret = true;

    public function mount(Document $document): void
    {
        $this->document = $document;
    }

    public function addWebhook(): void
    {
        $this->authorize('manage', $this->document);

        $this->validate([
            'newUrl' => 'required|url|max:500',
        ]);

        DocumentWebhook::create([
            'document_id' => $this->document->id,
            'user_id'     => auth()->id(),
            'url'         => $this->newUrl,
            'events'      => $this->newEvents ?: ['on_save', 'on_export'],
            'secret'      => $this->generateSecret ? Str::random(32) : null,
            'active'      => true,
        ]);

        $this->newUrl = '';
        $this->newEvents = ['on_save', 'on_export'];
        $this->generateSecret = true;
    }

    public function toggleWebhook(int $id): void
    {
        $this->authorize('manage', $this->document);

        $webhook = DocumentWebhook::where('document_id', $this->document->id)->findOrFail($id);
        $webhook->update(['active' => ! $webhook->active]);
    }

    public function deleteWebhook(int $id): void
    {
        $this->authorize('manage', $this->document);

        DocumentWebhook::where('document_id', $this->document->id)->findOrFail($id)->delete();
    }

    public function render()
    {
        $webhooks = DocumentWebhook::where('document_id', $this->document->id)->latest()->get();

        return view('livewire.documents.webhook-manager', compact('webhooks'));
    }
}
