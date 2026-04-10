<?php

namespace App\Livewire\Documents;

use App\Events\DocumentUpdated;
use App\Events\UserJoinedDocument;
use App\Events\UserLeftDocument;
use App\Models\AiSuggestion;
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

    /** Suggestion / track-changes mode */
    public bool $suggestionMode = false;

    /** Pending suggestions (not yet accepted/rejected) */
    public array $pendingSuggestions = [];

    /** Whether comment sidebar is open */
    public bool $commentSidebarOpen = false;

    public function mount(string $uuid): void
    {
        $this->document = Document::where('uuid', $uuid)->firstOrFail();
        $this->authorize('view', $this->document);

        $this->title   = $this->document->title;
        $this->content = $this->document->content ?? '';

        $presence = app(PresenceService::class);
        $presence->join($this->document, auth()->user());
        $this->activeUsers = $presence->getMemberList($this->document->id);

        UserJoinedDocument::dispatch($this->document, auth()->user());

        $this->loadPendingSuggestions();
    }

    public function saveContent(string $content): void
    {
        $this->authorize('update', $this->document);

        if ($this->suggestionMode) {
            // Store as a suggestion instead of saving directly
            AiSuggestion::create([
                'document_id'     => $this->document->id,
                'user_id'         => auth()->id(),
                'suggestion_text' => $content,
                'created_at'      => now(),
            ]);
            $this->loadPendingSuggestions();
            $this->saved = true;
            return;
        }

        $this->content = $content;
        $this->document->update([
            'content' => $content,
            'version' => $this->document->version + 1,
        ]);
        $this->saved = true;

        DocumentUpdated::dispatch($this->document, auth()->user(), $content, $this->document->version);
        app(PresenceService::class)->heartbeat($this->document, auth()->user());
    }

    public function toggleSuggestionMode(): void
    {
        $this->suggestionMode = ! $this->suggestionMode;
    }

    public function toggleCommentSidebar(): void
    {
        $this->commentSidebarOpen = ! $this->commentSidebarOpen;
    }

    public function acceptSuggestion(int $suggestionId): void
    {
        $this->authorize('update', $this->document);

        $suggestion = AiSuggestion::where('document_id', $this->document->id)
            ->whereNull('accepted_at')
            ->findOrFail($suggestionId);

        $this->document->update([
            'content' => $suggestion->suggestion_text,
            'version' => $this->document->version + 1,
        ]);
        $this->content = $suggestion->suggestion_text;

        $suggestion->update(['accepted_at' => now()]);
        $this->loadPendingSuggestions();
        $this->saved = true;

        $this->dispatch('suggestion-accepted', content: $suggestion->suggestion_text);
    }

    public function rejectSuggestion(int $suggestionId): void
    {
        $this->authorize('update', $this->document);

        AiSuggestion::where('document_id', $this->document->id)
            ->whereNull('accepted_at')
            ->findOrFail($suggestionId)
            ->delete();

        $this->loadPendingSuggestions();
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

    private function loadPendingSuggestions(): void
    {
        $this->pendingSuggestions = AiSuggestion::where('document_id', $this->document->id)
            ->whereNull('accepted_at')
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($s) => [
                'id'         => $s->id,
                'user'       => $s->user->name,
                'created_at' => $s->created_at->diffForHumans(),
                'excerpt'    => \Illuminate\Support\Str::limit(strip_tags($s->suggestion_text), 80),
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.documents.editor')
            ->layout('layouts.app');
    }
}
