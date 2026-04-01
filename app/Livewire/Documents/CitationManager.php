<?php

namespace App\Livewire\Documents;

use App\Models\CitationReference;
use App\Models\Document;
use App\Models\User;
use App\Services\CitationImportService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CitationManager extends Component
{
    public Document $document;

    public string $provider = 'manual';

    public string $title = '';

    public string $authors = '';

    public string $publicationYear = '';

    public string $sourceUrl = '';

    public string $citationText = '';

    public string $importProvider = 'zotero';

    public string $importJson = '';

    public function mount(Document $document): void
    {
        abort_unless($this->currentUser()->allTeams()->pluck('id')->contains($document->team_id), 403);
        $this->document = $document;
    }

    public function addManualCitation(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'authors' => ['nullable', 'string', 'max:255'],
            'publicationYear' => ['nullable', 'digits:4'],
            'sourceUrl' => ['nullable', 'url'],
            'citationText' => ['nullable', 'string', 'max:2000'],
        ]);

        CitationReference::create([
            'document_id' => $this->document->id,
            'team_id' => $this->document->team_id,
            'user_id' => Auth::id(),
            'provider' => 'manual',
            'title' => $validated['title'],
            'authors' => $validated['authors'] ?: null,
            'publication_year' => $validated['publicationYear'] !== '' ? (int) $validated['publicationYear'] : null,
            'source_url' => $validated['sourceUrl'] ?: null,
            'citation_text' => $validated['citationText'] ?: null,
        ]);

        $this->reset(['title', 'authors', 'publicationYear', 'sourceUrl', 'citationText']);
        $this->dispatch('notify', type: 'success', message: 'Citation added.');
    }

    public function importCitations(): void
    {
        $validated = $this->validate([
            'importProvider' => ['required', 'in:zotero,mendeley'],
            'importJson' => ['required', 'string', 'min:2'],
        ]);

        $service = app(CitationImportService::class);
        $items = $service->import($validated['importProvider'], $validated['importJson']);

        $created = 0;

        foreach ($items as $item) {
            CitationReference::create([
                'document_id' => $this->document->id,
                'team_id' => $this->document->team_id,
                'user_id' => Auth::id(),
                'provider' => $item['provider'],
                'external_id' => $item['external_id'] !== '' ? $item['external_id'] : null,
                'title' => $item['title'],
                'authors' => $item['authors'] !== '' ? $item['authors'] : null,
                'publication_year' => $item['publication_year'],
                'source_url' => $item['source_url'] !== '' ? $item['source_url'] : null,
                'citation_text' => $item['citation_text'] !== '' ? $item['citation_text'] : null,
                'metadata' => $item['metadata'],
            ]);
            $created++;
        }

        $this->reset('importJson');
        $this->dispatch('notify', type: 'success', message: "Imported {$created} citation(s).");
    }

    public function deleteCitation(int $citationId): void
    {
        $citation = CitationReference::query()
            ->where('document_id', $this->document->id)
            ->findOrFail($citationId);

        $citation->delete();

        $this->dispatch('notify', type: 'success', message: 'Citation removed.');
    }

    protected function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    public function render()
    {
        return view('livewire.documents.citation-manager', [
            'citations' => $this->document->citations()->latest()->get(),
        ]);
    }
}
