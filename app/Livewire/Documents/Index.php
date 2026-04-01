<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\Template;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $viewMode = 'list';

    public string $search = '';

    public ?int $teamFilter = null;

    public string $statusFilter = 'all';

    public string $dateFilter = 'all';

    public bool $showCreateWizard = false;

    public string $creationMode = 'blank';

    public string $newTitle = '';

    public ?int $templateId = null;

    public string $aiPrompt = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'teamFilter' => ['except' => null],
        'statusFilter' => ['except' => 'all'],
        'dateFilter' => ['except' => 'all'],
        'viewMode' => ['except' => 'list'],
    ];

    public function mount(): void
    {
        $teamId = $this->currentUser()->currentTeam?->id;

        if ($teamId) {
            $this->teamFilter = $teamId;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTeamFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function setViewMode(string $mode): void
    {
        if (! in_array($mode, ['list', 'grid'], true)) {
            return;
        }

        $this->viewMode = $mode;
    }

    public function openCreateWizard(string $mode = 'blank'): void
    {
        if (! in_array($mode, ['blank', 'template', 'ai'], true)) {
            $mode = 'blank';
        }

        $this->resetCreateWizardState();
        $this->creationMode = $mode;
        $this->showCreateWizard = true;
    }

    public function closeCreateWizard(): void
    {
        $this->showCreateWizard = false;
        $this->resetCreateWizardState();
    }

    public function createDocument(): void
    {
        $teamIds = $this->authorizedTeamIds();

        $validated = $this->validate([
            'creationMode' => ['required', Rule::in(['blank', 'template', 'ai'])],
            'newTitle' => ['nullable', 'string', 'max:255'],
            'templateId' => ['nullable', 'integer', Rule::exists('templates', 'id')],
            'aiPrompt' => ['nullable', 'string', 'max:2000'],
        ]);

        $teamId = $this->teamFilter && $teamIds->contains($this->teamFilter)
            ? $this->teamFilter
            : $this->currentUser()->currentTeam?->id;

        if (! $teamId || ! $teamIds->contains($teamId)) {
            abort(403);
        }

        $title = trim((string) ($validated['newTitle'] ?? ''));
        $content = null;

        if ($validated['creationMode'] === 'template') {
            if (! $validated['templateId']) {
                $this->addError('templateId', 'Please select a template.');

                return;
            }

            $template = Template::query()
                ->where('id', $validated['templateId'])
                ->where(function (Builder $query) use ($teamId): void {
                    $query->where('team_id', $teamId)
                        ->orWhere('is_public', true);
                })
                ->firstOrFail();

            $title = $title !== '' ? $title : $template->name;
            $content = $template->content;
        }

        if ($validated['creationMode'] === 'ai') {
            if (trim((string) ($validated['aiPrompt'] ?? '')) === '') {
                $this->addError('aiPrompt', 'Please enter a prompt for AI generation.');

                return;
            }

            $title = $title !== '' ? $title : 'AI Draft - '.Carbon::now()->format('Y-m-d H:i');
            $content = "AI Draft Request\n\nPrompt:\n".trim($validated['aiPrompt']);
        }

        if ($validated['creationMode'] === 'blank' && $title === '') {
            $title = 'Untitled Document';
        }

        Document::create([
            'team_id' => $teamId,
            'user_id' => Auth::id(),
            'title' => $title,
            'content' => $content,
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);

        $this->closeCreateWizard();
        $this->dispatch('notify', type: 'success', message: 'Document created successfully.');
    }

    public function archiveDocument(int $documentId): void
    {
        $document = $this->findDocumentForUser($documentId);
        $document->update([
            'is_archived' => true,
            'status' => 'archived',
        ]);
    }

    public function unarchiveDocument(int $documentId): void
    {
        $document = $this->findDocumentForUser($documentId);
        $document->update([
            'is_archived' => false,
            'status' => 'draft',
        ]);
    }

    public function deleteDocument(int $documentId): void
    {
        $document = $this->findDocumentForUser($documentId);
        $document->delete();
    }

    public function restoreDocument(int $documentId): void
    {
        $document = $this->findDocumentForUser($documentId, withTrashed: true);

        if ($document->trashed()) {
            $document->restore();
        }
    }

    public function duplicateDocument(int $documentId): void
    {
        $document = $this->findDocumentForUser($documentId, withTrashed: true);

        $copy = $document->replicate([
            'created_at',
            'updated_at',
            'deleted_at',
            'is_archived',
            'status',
            'version',
        ]);

        $copy->title = $document->title.' (Copy)';
        $copy->status = 'draft';
        $copy->is_archived = false;
        $copy->version = 1;
        $copy->deleted_at = null;
        $copy->save();
    }

    public function render()
    {
        return view('livewire.documents.index', [
            'documents' => $this->documents(),
            'teams' => $this->currentUser()->allTeams(),
            'templates' => $this->templates(),
        ]);
    }

    protected function documents(): LengthAwarePaginator
    {
        $teamIds = $this->authorizedTeamIds();

        return Document::query()
            ->with(['team', 'user'])
            ->whereIn('team_id', $teamIds)
            ->when($this->statusFilter === 'deleted', function (Builder $query): void {
                $query->onlyTrashed();
            }, function (Builder $query): void {
                $query->whereNull('deleted_at');
            })
            ->when($this->teamFilter, function (Builder $query): void {
                $query->where('team_id', $this->teamFilter);
            })
            ->when(trim($this->search) !== '', function (Builder $query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(function (Builder $inner) use ($term): void {
                    $inner->where('title', 'like', $term)
                        ->orWhere('content', 'like', $term);
                });
            })
            ->when(in_array($this->statusFilter, ['draft', 'published', 'archived'], true), function (Builder $query): void {
                if ($this->statusFilter === 'archived') {
                    $query->where('is_archived', true);
                } else {
                    $query->where('status', $this->statusFilter);
                }
            })
            ->when($this->dateFilter !== 'all', function (Builder $query): void {
                $now = now();
                $from = match ($this->dateFilter) {
                    'today' => $now->copy()->startOfDay(),
                    '7d' => $now->copy()->subDays(7),
                    '30d' => $now->copy()->subDays(30),
                    default => null,
                };

                if ($from) {
                    $query->where('created_at', '>=', $from);
                }
            })
            ->latest('updated_at')
            ->paginate(10);
    }

    /**
     * @return Collection<int, int>
     */
    protected function authorizedTeamIds(): Collection
    {
        return $this->currentUser()
            ->allTeams()
            ->pluck('id');
    }

    protected function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    protected function templates(): Collection
    {
        $teamIds = $this->authorizedTeamIds();

        return Template::query()
            ->whereIn('team_id', $teamIds)
            ->orWhere('is_public', true)
            ->orderBy('name')
            ->get();
    }

    protected function findDocumentForUser(int $documentId, bool $withTrashed = false): Document
    {
        $teamIds = $this->authorizedTeamIds();

        return Document::query()
            ->when($withTrashed, fn (Builder $query) => $query->withTrashed())
            ->whereIn('team_id', $teamIds)
            ->findOrFail($documentId);
    }

    protected function resetCreateWizardState(): void
    {
        $this->newTitle = '';
        $this->templateId = null;
        $this->aiPrompt = '';
        $this->resetErrorBag();
    }
}
