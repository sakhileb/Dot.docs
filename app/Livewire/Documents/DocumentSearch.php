<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DocumentSearch extends Component
{
    use WithPagination;

    public Team $team;

    public string $query = '';

    public string $searchType = 'all'; // all, title, content, comments

    protected $queryString = [
        'query' => ['except' => ''],
        'searchType' => ['except' => 'all'],
    ];

    public function mount(Team $team): void
    {
        $this->team = $team;
    }

    public function updatingQuery(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        if (strlen($this->query) < 2) {
            return view('livewire.documents.search', ['results' => collect()]);
        }

        $searchTerm = '%' . $this->query . '%';
        $query = Document::where('team_id', $this->team->id);

        if ($this->searchType === 'all') {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('content', 'like', $searchTerm)
                    ->orWhereHas('comments', function ($subQ) use ($searchTerm) {
                        $subQ->where('content', 'like', $searchTerm);
                    });
            });
        } elseif ($this->searchType === 'title') {
            $query->where('title', 'like', $searchTerm);
        } elseif ($this->searchType === 'content') {
            $query->where('content', 'like', $searchTerm);
        } elseif ($this->searchType === 'comments') {
            $query->whereHas('comments', function ($q) use ($searchTerm) {
                $q->where('content', 'like', $searchTerm);
            });
        }

        $results = $query->latest('updated_at')->paginate(12);

        return view('livewire.documents.search', ['results' => $results]);
    }
}
