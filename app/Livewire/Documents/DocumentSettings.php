<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use App\Services\TagRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DocumentSettings extends Component
{
    use AuthorizesRequests;

    public Document $document;

    public string $title = '';

    public bool $isPublic = false;

    public bool $showDeleteConfirm = false;

    public string $transferEmail = '';

    public ?int $folderId = null;

    public string $newTagName = '';

    public function mount(string $uuid): void
    {
        $this->document = Document::where('uuid', $uuid)->firstOrFail();
        $this->authorize('update', $this->document);

        $this->title = $this->document->title;
        $this->isPublic = $this->document->is_public;
        $this->folderId = $this->document->folder_id;
    }

    public function save(): void
    {
        $this->authorize('update', $this->document);
        $this->validate(['title' => 'required|string|max:255']);

        $this->document->update([
            'title' => $this->title,
            'is_public' => $this->isPublic,
        ]);

        $this->dispatch('settings-saved');
        session()->flash('status', 'Settings saved.');
    }

    /**
     * Every folder offered here belongs to the document's own scope
     * (its team, or the current user personally if it has none) --
     * a document can never be filed under a folder from a different
     * team/owner's space.
     */
    #[Computed]
    public function availableFolders()
    {
        return Folder::when($this->document->team_id, fn ($q) => $q->where('team_id', $this->document->team_id))
            ->when(! $this->document->team_id, fn ($q) => $q->whereNull('team_id')->where('owner_id', $this->document->owner_id))
            ->orderBy('name')
            ->get();
    }

    public function moveToFolder(): void
    {
        $this->authorize('update', $this->document);

        // The "No folder (root)" <option> submits an empty string, which
        // Livewire casts to 0 for a ?int property, not null -- normalize
        // it here, otherwise `folder_id => 0` hits the FK constraint
        // (folder ids start at 1) instead of clearing the folder.
        $folderId = $this->folderId ?: null;

        if ($folderId) {
            $folder = Folder::find($folderId);
            $inScope = $folder && (
                ($this->document->team_id && $folder->team_id === $this->document->team_id)
                || (! $this->document->team_id && $folder->owner_id === $this->document->owner_id)
            );

            if (! $inScope) {
                $this->addError('folderId', 'That folder is not available for this document.');

                return;
            }
        }

        $this->folderId = $folderId;
        $this->document->update(['folder_id' => $folderId]);
        session()->flash('status', 'Document moved.');
    }

    #[Computed]
    public function tags()
    {
        return $this->document->tags()->orderBy('name')->get();
    }

    public function addTag(): void
    {
        $this->authorize('update', $this->document);

        $name = trim($this->newTagName);
        if ($name === '') {
            return;
        }

        $tag = app(TagRepository::class)->findOrCreate(auth()->user(), $this->document->team_id, $name);
        $this->document->tags()->syncWithoutDetaching([$tag->id]);

        $this->newTagName = '';
    }

    public function removeTag(int $tagId): void
    {
        $this->authorize('update', $this->document);
        $this->document->tags()->detach($tagId);
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
