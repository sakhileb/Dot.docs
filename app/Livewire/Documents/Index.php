<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public string $search = '';
    public string $filter = 'all'; // all | mine | shared | team
    public bool $showCreateModal = false;
    public string $newTitle = '';
    public int $perPage = 12;

    public function updatingSearch(): void
    {
        $this->perPage = 12;
    }

    public function updatingFilter(): void
    {
        $this->perPage = 12;
    }

    public function loadMore(): void
    {
        $this->perPage += 12;
    }

    #[Computed]
    public function documents()
    {
        $user = auth()->user();

        return Document::query()
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('collaborators', fn ($q) => $q->where('user_id', $user->id));

                if ($user->currentTeam) {
                    $q->orWhere('team_id', $user->currentTeam->id);
                }
            })
            ->when($this->search, fn ($q) => $q->where('title', 'like', '%' . $this->search . '%'))
            ->when($this->filter === 'mine', fn ($q) => $q->where('owner_id', $user->id))
            ->when($this->filter === 'shared', fn ($q) => $q->whereHas('collaborators', fn ($q) => $q->where('user_id', $user->id)))
            ->when($this->filter === 'team', fn ($q) => $user->currentTeam ? $q->where('team_id', $user->currentTeam->id) : $q)
            ->latest()
            ->paginate($this->perPage);
    }

    public function createDocument(): void
    {
        $this->validate(['newTitle' => 'required|string|max:255']);

        $document = Document::create([
            'title'    => $this->newTitle,
            'owner_id' => auth()->id(),
            'team_id'  => auth()->user()->currentTeam?->id,
            'version'  => 1,
            'is_public' => false,
        ]);

        $this->showCreateModal = false;
        $this->newTitle = '';

        $this->redirect(route('documents.edit', $document->uuid));
    }

    public function render()
    {
        return view('livewire.documents.index')
            ->layout('layouts.app');
    }
}
