<?php

namespace App\Livewire\Templates;

use App\Models\Template;
use App\Models\TemplateVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Library extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public string $categoryFilter = 'all';

    public string $scopeFilter = 'all';

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showPreviewModal = false;

    public ?int $editingTemplateId = null;

    public ?int $previewTemplateId = null;

    public string $name = '';

    public string $category = 'business';

    public string $description = '';

    public string $content = '';

    public bool $isPublic = false;

    public $importFile;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedScopeFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function createTemplate(): void
    {
        $validated = $this->validateTemplate();

        $template = Template::create([
            'team_id' => $this->currentUser()->currentTeam?->id,
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'content' => $validated['content'],
            'version' => 1,
            'is_public' => $validated['isPublic'],
        ]);

        TemplateVersion::create([
            'template_id' => $template->id,
            'user_id' => Auth::id(),
            'version' => 1,
            'name' => $template->name,
            'description' => $template->description,
            'content' => $template->content,
        ]);

        $this->dispatch('notify', type: 'success', message: 'Template created.');
        $this->closeCreateModal();
    }

    public function openEditModal(int $templateId): void
    {
        $template = $this->findTemplate($templateId);

        $this->editingTemplateId = $template->id;
        $this->name = $template->name;
        $this->category = $template->category;
        $this->description = (string) $template->description;
        $this->content = (string) $template->content;
        $this->isPublic = (bool) $template->is_public;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingTemplateId = null;
        $this->resetForm();
    }

    public function updateTemplate(): void
    {
        if (! $this->editingTemplateId) {
            return;
        }

        $template = $this->findTemplate($this->editingTemplateId);
        $validated = $this->validateTemplate();

        TemplateVersion::create([
            'template_id' => $template->id,
            'user_id' => Auth::id(),
            'version' => $template->version,
            'name' => $template->name,
            'description' => $template->description,
            'content' => $template->content,
        ]);

        $template->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'content' => $validated['content'],
            'is_public' => $validated['isPublic'],
            'version' => $template->version + 1,
        ]);

        $this->dispatch('notify', type: 'success', message: 'Template updated and versioned.');
        $this->closeEditModal();
    }

    public function deleteTemplate(int $templateId): void
    {
        $template = $this->findTemplate($templateId);
        $template->delete();
        $this->dispatch('notify', type: 'success', message: 'Template deleted.');
    }

    public function toggleShare(int $templateId): void
    {
        $template = $this->findTemplate($templateId);
        $template->update(['is_public' => ! $template->is_public]);
        $this->dispatch('notify', type: 'success', message: $template->is_public ? 'Template shared publicly.' : 'Template set to team-only.');
    }

    public function openPreview(int $templateId): void
    {
        $this->previewTemplateId = $templateId;
        $this->showPreviewModal = true;
    }

    public function closePreview(): void
    {
        $this->showPreviewModal = false;
        $this->previewTemplateId = null;
    }

    public function exportTemplate(int $templateId)
    {
        $template = $this->findTemplate($templateId);

        $payload = [
            'name' => $template->name,
            'category' => $template->category,
            'description' => $template->description,
            'content' => $template->content,
            'version' => $template->version,
            'is_public' => $template->is_public,
            'exported_at' => now()->toIso8601String(),
        ];

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, Str::slug($template->name) . '-template.json', [
            'Content-Type' => 'application/json',
        ]);
    }

    public function exportAllTemplates()
    {
        $templates = $this->templatesQuery()->get()->map(function (Template $template): array {
            return [
                'name' => $template->name,
                'category' => $template->category,
                'description' => $template->description,
                'content' => $template->content,
                'version' => $template->version,
                'is_public' => $template->is_public,
            ];
        })->values()->all();

        return response()->streamDownload(function () use ($templates): void {
            echo json_encode([
                'type' => 'dotdocs-template-bundle',
                'exported_at' => now()->toIso8601String(),
                'templates' => $templates,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, 'dotdocs-templates-' . now()->format('Ymd-His') . '.json', [
            'Content-Type' => 'application/json',
        ]);
    }

    public function importTemplates(): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'mimes:json', 'max:2048'],
        ]);

        $raw = file_get_contents($this->importFile->getRealPath());
        $decoded = json_decode((string) $raw, true);

        $templates = isset($decoded['templates']) && is_array($decoded['templates'])
            ? $decoded['templates']
            : [$decoded];

        $created = 0;

        foreach ($templates as $item) {
            if (! is_array($item) || empty($item['name'])) {
                continue;
            }

            $template = Template::create([
                'team_id' => $this->currentUser()->currentTeam?->id,
                'user_id' => Auth::id(),
                'name' => (string) $item['name'],
                'category' => (string) ($item['category'] ?? 'imported'),
                'description' => (string) ($item['description'] ?? ''),
                'content' => (string) ($item['content'] ?? ''),
                'version' => 1,
                'is_public' => false,
            ]);

            TemplateVersion::create([
                'template_id' => $template->id,
                'user_id' => Auth::id(),
                'version' => 1,
                'name' => $template->name,
                'description' => $template->description,
                'content' => $template->content,
            ]);

            $created++;
        }

        $this->importFile = null;
        $this->dispatch('notify', type: 'success', message: "Imported {$created} template(s).");
    }

    public function render()
    {
        return view('livewire.templates.library', [
            'templates' => $this->templatesQuery()->latest('updated_at')->paginate(12),
            'categories' => $this->categories(),
            'previewTemplate' => $this->previewTemplateId ? $this->findTemplate($this->previewTemplateId) : null,
        ]);
    }

    protected function templatesQuery(): Builder
    {
        $teamId = $this->currentUser()->currentTeam?->id;

        return Template::query()
            ->where(function (Builder $query) use ($teamId): void {
                if ($teamId) {
                    $query->where('team_id', $teamId);
                }

                $query->orWhere('is_public', true);
            })
            ->when($this->scopeFilter === 'team', function (Builder $query) use ($teamId): void {
                $query->where('team_id', $teamId);
            })
            ->when($this->scopeFilter === 'public', function (Builder $query): void {
                $query->where('is_public', true);
            })
            ->when($this->categoryFilter !== 'all', function (Builder $query): void {
                $query->where('category', $this->categoryFilter);
            })
            ->when(trim($this->search) !== '', function (Builder $query): void {
                $term = '%' . trim($this->search) . '%';
                $query->where(function (Builder $q) use ($term): void {
                    $q->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhere('content', 'like', $term);
                });
            });
    }

    /**
     * @return Collection<int, string>
     */
    protected function categories(): Collection
    {
        return Template::query()
            ->select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateTemplate(): array
    {
        return $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string'],
            'isPublic' => ['boolean'],
        ]);
    }

    protected function findTemplate(int $templateId): Template
    {
        $teamId = $this->currentUser()->currentTeam?->id;

        return Template::query()
            ->where('id', $templateId)
            ->where(function (Builder $query) use ($teamId): void {
                if ($teamId) {
                    $query->where('team_id', $teamId);
                }

                $query->orWhere('is_public', true);
            })
            ->firstOrFail();
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->category = 'business';
        $this->description = '';
        $this->content = '';
        $this->isPublic = false;
        $this->resetErrorBag();
    }

    protected function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
