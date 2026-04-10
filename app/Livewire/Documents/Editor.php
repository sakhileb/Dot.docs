<?php

namespace App\Livewire\Documents;

use App\Events\DocumentUpdated;
use App\Events\UserJoinedDocument;
use App\Events\UserLeftDocument;
use App\Models\Document;
use App\Services\PresenceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Editor extends Component
{
    use AuthorizesRequests;

    public Document $document;
    public string $title = '';
    public string $content = '';
    public bool $saved = false;
    public array $activeUsers = [];

    public function mount(string $uuid): void
    {
        $this->document = Document::where('uuid', $uuid)->firstOrFail();
        $this->authorize('view', $this->document);

        $this->title   = $this->document->title;
        $this->content = $this->document->content ?? '';

        // Mark user as present and broadcast join
        $presence = app(PresenceService::class);
        $presence->join($this->document, auth()->user());
        $this->activeUsers = $presence->getMemberList($this->document->id);

        UserJoinedDocument::dispatch($this->document, auth()->user());
    }

    public function saveContent(string $content): void
    {
        $this->authorize('update', $this->document);

        $this->content = $content;

        $this->document->update([
            'content' => $content,
            'version' => $this->document->version + 1,
        ]);

        $this->saved = true;

        // Broadcast the update to all other presence members
        DocumentUpdated::dispatch(
            $this->document,
            auth()->user(),
            $content,
            $this->document->version
        );

        // Refresh presence heartbeat
        app(PresenceService::class)->heartbeat($this->document, auth()->user());
    }

    public function saveTitle(): void
    {
        $this->authorize('update', $this->document);
        $this->validate(['title' => 'required|string|max:255']);

        $this->document->update(['title' => $this->title]);
        $this->saved = true;
    }

    public function heartbeat(): void
    {
        app(PresenceService::class)->heartbeat($this->document, auth()->user());
        $this->activeUsers = app(PresenceService::class)->getMemberList($this->document->id);
    }

    public function leaving(): void
    {
        $presence = app(PresenceService::class);
        $presence->leave($this->document, auth()->user());
        UserLeftDocument::dispatch($this->document, auth()->user());
    }

    public function render()
    {
        return view('livewire.documents.editor')
            ->layout('layouts.app');
    }
}
