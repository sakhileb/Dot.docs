<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\DocumentCollaborator;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ShareManager extends Component
{
    use AuthorizesRequests;

    public Document $document;
    public string $inviteEmail = '';
    public string $inviteRole  = 'viewer';
    public string $publicLink  = '';

    public function mount(string $uuid): void
    {
        $this->document   = Document::with('collaborators.user')->where('uuid', $uuid)->firstOrFail();
        $this->authorize('update', $this->document);
        $this->publicLink = $this->document->is_public
            ? route('documents.shared', $this->document->uuid)
            : '';
    }

    public function invite(): void
    {
        $this->validate([
            'inviteEmail' => 'required|email|exists:users,email',
            'inviteRole'  => 'required|in:viewer,editor,admin',
        ]);

        $user = User::where('email', $this->inviteEmail)->firstOrFail();

        DocumentCollaborator::updateOrCreate(
            ['document_id' => $this->document->id, 'user_id' => $user->id],
            ['role' => $this->inviteRole]
        );

        $this->inviteEmail = '';
        $this->inviteRole  = 'viewer';
        $this->document->load('collaborators.user');
    }

    public function removeCollaborator(int $collaboratorId): void
    {
        DocumentCollaborator::where('id', $collaboratorId)
            ->where('document_id', $this->document->id)
            ->delete();

        $this->document->load('collaborators.user');
    }

    public function togglePublicLink(): void
    {
        $this->document->update(['is_public' => ! $this->document->is_public]);
        $this->document->refresh();
        $this->publicLink = $this->document->is_public
            ? route('documents.shared', $this->document->uuid)
            : '';
    }

    public function render()
    {
        return view('livewire.documents.share-manager')
            ->layout('layouts.app');
    }
}
