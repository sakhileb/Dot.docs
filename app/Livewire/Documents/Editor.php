<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Editor extends Component
{
    use AuthorizesRequests;

    public Document $document;
    public string $title = '';
    public string $content = '';
    public bool $saved = false;

    public function mount(string $uuid): void
    {
        $this->document = Document::where('uuid', $uuid)->firstOrFail();
        $this->authorize('view', $this->document);

        $this->title   = $this->document->title;
        $this->content = $this->document->content ?? '';
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
        $this->dispatch('content-saved');
    }

    public function saveTitle(): void
    {
        $this->authorize('update', $this->document);
        $this->validate(['title' => 'required|string|max:255']);

        $this->document->update(['title' => $this->title]);
        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.documents.editor')
            ->layout('layouts.app');
    }
}
