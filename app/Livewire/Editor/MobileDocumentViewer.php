<?php

namespace App\Livewire\Editor;

use App\Models\Document;
use App\Traits\DocumentPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MobileDocumentViewer extends Component
{
    use DocumentPagination;

    public Document $document;
    public array $currentPageData = [];
    public bool $viewMode = false; // true for view, false for edit
    public int $fontSize = 16;

    protected $listeners = [
        'document-updated' => 'refreshDocument',
    ];

    public function mount(Document $document)
    {
        $this->document = $document;
        $this->document->load('user', 'team');
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->viewMode = !$user->can('update', $document);
        $this->loadCurrentPage();
    }

    public function render()
    {
        return view('livewire.editor.mobile-document-viewer', [
            'document' => $this->document,
            'currentPageData' => $this->currentPageData,
        ]);
    }

    public function loadCurrentPage()
    {
        $this->currentPageData = $this->paginateContent($this->document->content);
    }

    public function goNextPage()
    {
        if ($this->currentPageData['hasNextPage']) {
            $this->currentPageData = $this->nextPage();
        }
    }

    public function goPreviousPage()
    {
        if ($this->currentPageData['hasPreviousPage']) {
            $this->currentPageData = $this->previousPage();
        }
    }

    public function increaseFontSize()
    {
        $this->fontSize = min($this->fontSize + 2, 32);
    }

    public function decreaseFontSize()
    {
        $this->fontSize = max($this->fontSize - 2, 12);
    }

    public function toggleTheme()
    {
        $this->dispatch('theme-toggled');
    }

    public function refreshDocument()
    {
        $this->document->refresh();
        $this->currentPage = 1;
        $this->loadCurrentPage();
    }
}
