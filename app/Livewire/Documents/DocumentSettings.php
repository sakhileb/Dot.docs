<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class DocumentSettings extends Component
{
    use AuthorizesRequests;

    public Document $document;
    public string $title = '';
    public bool $isPublic = false;
    public bool $showDeleteConfirm = false;
    public string $transferEmail = '';

    public function mount(string $uuid): void
    {
        $this->document  = Document::where('uuid', $uuid)->firstOrFail();
        $this->authorize('update', $this->document);

        $this->title    = $this->document->title;
        $this->isPublic = $this->document->is_public;
    }

    public function save(): void
    {
        $this->authorize('update', $this->document);
        $this->validate(['title' => 'required|string|max:255']);

        $this->document->update([
            'title'     => $this->title,
            'is_public' => $this->isPublic,
        ]);

        $this->dispatch('settings-saved');
        session()->flash('status', 'Settings saved.');
    }

    public function transferOwnership(): void
    {
        $this->authorize('delete', $this->document);
        $this->validate(['transferEmail' => 'required|email|exists:users,email']);

        $newOwner = User::where('email', $this->transferEmail)->firstOrFail();
        $this->document->update(['owner_id' => $newOwner->id]);

        $this->transferEmail = '';
        session()->flash('status', 'Ownership transferred.');
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->document);
        $this->document->delete();

        $this->redirect(route('documents.index'));
    }

    public function render()
    {
        return view('livewire.documents.document-settings')
            ->layout('layouts.app');
    }
}
