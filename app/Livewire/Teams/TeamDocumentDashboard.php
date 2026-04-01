<?php

namespace App\Livewire\Teams;

use App\Models\Document;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TeamDocumentDashboard extends Component
{
    use WithPagination;

    public Team $team;

    public string $search = '';

    public string $sort = 'recent';

    public string $status = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'sort' => ['except' => 'recent'],
        'status' => ['except' => 'all'],
    ];

    public function mount(Team $team): void
    {
        $this->team = $team;
        $this->authorize('view', $team);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = $this->team->documents();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('content', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if (!$this->team->personal_team) {
            $query->where('is_archived', $this->status === 'archived');
        }

        $sortedQuery = match ($this->sort) {
            'oldest' => $query->oldest('created_at'),
            'title' => $query->orderBy('title'),
            'updated' => $query->latest('updated_at'),
            default => $query->latest('created_at'),
        };

        $documents = $sortedQuery->paginate(12);

        $stats = [
            'total' => $this->team->documents()->count(),
            'recent' => $this->team->documents()->where('updated_at', '>=', now()->subDays(7))->count(),
            'archived' => $this->team->documents()->where('is_archived', true)->count(),
            'shared' => $this->team->documents()->whereHas('shares')->count(),
        ];

        return view('livewire.teams.document-dashboard', [
            'documents' => $documents,
            'stats' => $stats,
        ]);
    }
}
