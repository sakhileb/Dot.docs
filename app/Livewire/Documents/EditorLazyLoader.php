<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use Livewire\Component;

class EditorLazyLoader extends Component
{
    public Document $document;

    public bool $editorLoaded = false;

    public function mount(Document $document): void
    {
        $this->document = $document;
    }

    public function loadEditor(): void
    {
        $this->editorLoaded = true;
    }

    public function render()
    {
        return view('livewire.documents.editor-lazy-loader');
    }
}
