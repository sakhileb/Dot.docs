<?php

namespace App\Livewire\Editor;

use App\Models\Document;
use App\Models\DocumentCursor;
use App\Services\ImageOptimizationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class DocumentEditor extends Component
{
    use WithFileUploads;

    public Document $document;
    public string $content = '';
    public string $title = '';
    public bool $editMode = false;
    public array $userPresence = [];
    public ?int $currentCursorPosition = null;

    protected ImageOptimizationService $imageOptimizationService;

    protected $listeners = [
        'document-updated' => 'refreshDocument',
        'cursor-moved' => 'updateCursor',
        'user-joined' => 'notifyUserPresence',
        'user-left' => 'removeUserPresence',
    ];

    public function mount(Document $document)
    {
        $this->document = $document;
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->editMode = $user->can('update', $document);
        $this->imageOptimizationService = app(ImageOptimizationService::class);
    }

    public function render()
    {
        return view('livewire.editor.document-editor', [
            'document' => $this->document,
            'userPresence' => $this->userPresence,
        ]);
    }

    public function updateContent(string $content)
    {
        if (!$this->editMode) {
            return;
        }

        $this->content = $content;
        $this->document->update(['content' => $content]);
        $this->dispatch('content-updated', $this->document->id);
    }

    public function updateTitle(string $title)
    {
        if (!$this->editMode) {
            return;
        }

        $this->title = $title;
        $this->document->update(['title' => $title]);
    }

    public function uploadImage($file)
    {
        if (!$this->editMode) {
            return null;
        }

        $path = $file->store('documents/' . $this->document->id, 'public');
        $fullPath = storage_path('app/public/' . $path);

        // Optimize image
        try {
            $optimizedPath = $this->imageOptimizationService->optimizeImage(
                $fullPath,
                null,
                'medium'
            );
            return asset('storage/' . str_replace('storage/app/public/', '', $optimizedPath));
        } catch (\Exception $e) {
            return asset('storage/' . $path);
        }
    }

    public function updateCursorPosition(int $position)
    {
        $this->currentCursorPosition = $position;

        DocumentCursor::updateOrCreate(
            ['document_id' => $this->document->id, 'user_id' => Auth::id()],
            ['cursor_position' => $position, 'updated_at' => now()]
        );

        $this->dispatch('cursor-position-updated', $this->document->id, Auth::id(), $position);
    }

    public function refreshDocument()
    {
        $this->document->refresh();
        $this->content = $this->document->content;
        $this->title = $this->document->title;
    }

    public function notifyUserPresence(int $userId, string $userName)
    {
        if (!isset($this->userPresence[$userId])) {
            $this->userPresence[$userId] = $userName;
        }
    }

    public function removeUserPresence(int $userId)
    {
        unset($this->userPresence[$userId]);
    }
}
