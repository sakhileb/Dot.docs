<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\User;
use App\Services\CloudDocumentIntegrationService;
use App\Services\DocumentTransferService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Livewire\WithFileUploads;

class Transfer extends Component
{
    use WithFileUploads;

    public string $tab = 'export';

    public string $search = '';

    public array $selectedDocuments = [];

    public string $exportFormat = 'pdf';

    public string $cloudExportProvider = 'google_drive';

    public $importFile;

    public string $googleDocsUrl = '';

    public string $importTitle = '';

    public string $cloudImportProvider = 'google_drive';

    public string $cloudReference = '';

    public function updatedSearch(): void
    {
        $this->selectedDocuments = [];
    }

    public function toggleSelection(int $documentId): void
    {
        if (in_array($documentId, $this->selectedDocuments, true)) {
            $this->selectedDocuments = array_values(array_filter(
                $this->selectedDocuments,
                fn (int $id): bool => $id !== $documentId
            ));

            return;
        }

        $this->selectedDocuments[] = $documentId;
    }

    public function exportSingle(int $documentId)
    {
        $document = $this->findDocument($documentId);
        $service = new DocumentTransferService();
        $result = $service->exportDocument($document, $this->exportFormat, $this->currentUser());

        return response()->download($result['path'], $result['filename'], [
            'Content-Type' => $result['mime'],
        ])->deleteFileAfterSend(true);
    }

    public function exportBatch()
    {
        if ($this->selectedDocuments === []) {
            $this->dispatch('notify', type: 'warning', message: 'Select at least one document for batch export.');

            return null;
        }

        $documents = Document::query()
            ->whereIn('id', $this->selectedDocuments)
            ->whereIn('team_id', $this->authorizedTeamIds())
            ->get()
            ->all();

        if ($documents === []) {
            $this->dispatch('notify', type: 'error', message: 'No accessible documents selected.');

            return null;
        }

        $service = new DocumentTransferService();
        $result = $service->exportBatch($documents, $this->exportFormat, $this->currentUser());

        return response()->download($result['path'], $result['filename'], [
            'Content-Type' => $result['mime'],
        ])->deleteFileAfterSend(true);
    }

    public function exportBatchToCloud(): void
    {
        if ($this->selectedDocuments === []) {
            $this->dispatch('notify', type: 'warning', message: 'Select at least one document to export to the cloud.');

            return;
        }

        $documents = Document::query()
            ->whereIn('id', $this->selectedDocuments)
            ->whereIn('team_id', $this->authorizedTeamIds())
            ->get()
            ->all();

        if ($documents === []) {
            $this->dispatch('notify', type: 'error', message: 'No accessible documents selected.');

            return;
        }

        try {
            $uploaded = app(CloudDocumentIntegrationService::class)
                ->exportDocuments($documents, $this->cloudExportProvider, $this->exportFormat, $this->currentUser());
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());

            return;
        }

        $provider = $this->providerLabel($this->cloudExportProvider);
        $this->dispatch('notify', type: 'success', message: "Uploaded {$uploaded} document(s) to {$provider}.");
    }

    public function importFromFile(): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'mimes:docx,md,markdown,html,htm,txt', 'max:5120'],
            'importTitle' => ['nullable', 'string', 'max:255'],
        ]);

        $service = new DocumentTransferService();
        $extension = strtolower($this->importFile->getClientOriginalExtension());
        $content = $service->importFromFile($this->importFile->getRealPath(), $extension);

        $title = trim($this->importTitle) !== ''
            ? trim($this->importTitle)
            : pathinfo($this->importFile->getClientOriginalName(), PATHINFO_FILENAME);

        $document = Document::create([
            'team_id' => $this->currentUser()->currentTeam?->id,
            'user_id' => Auth::id(),
            'title' => $title !== '' ? $title : 'Imported Document',
            'content' => $content,
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);

        $this->importFile = null;
        $this->importTitle = '';
        $this->dispatch('notify', type: 'success', message: 'Document imported successfully.');
        $this->redirectRoute('documents.edit', ['document' => $document->id], navigate: true);
    }

    public function importFromGoogleDocs(): void
    {
        $this->validate([
            'googleDocsUrl' => ['required', 'url'],
            'importTitle' => ['nullable', 'string', 'max:255'],
        ]);

        $docId = $this->extractGoogleDocId($this->googleDocsUrl);
        if (! $docId) {
            $this->addError('googleDocsUrl', 'Invalid Google Docs URL format.');

            return;
        }

        $response = Http::timeout(30)
            ->accept('text/plain')
            ->get("https://docs.google.com/document/d/{$docId}/export", ['format' => 'txt']);

        if (! $response->successful()) {
            $this->dispatch('notify', type: 'error', message: 'Unable to import from Google Docs. Ensure the document is accessible.');

            return;
        }

        $raw = (string) $response->body();
        $content = '<p>' . nl2br(e($raw)) . '</p>';

        $title = trim($this->importTitle) !== '' ? trim($this->importTitle) : 'Imported Google Doc';

        $document = Document::create([
            'team_id' => $this->currentUser()->currentTeam?->id,
            'user_id' => Auth::id(),
            'title' => $title,
            'content' => $content,
            'version' => 1,
            'status' => 'draft',
            'is_archived' => false,
        ]);

        $this->googleDocsUrl = '';
        $this->importTitle = '';
        $this->dispatch('notify', type: 'success', message: 'Google Doc imported successfully.');
        $this->redirectRoute('documents.edit', ['document' => $document->id], navigate: true);
    }

    public function importFromCloud(): void
    {
        $validated = $this->validate([
            'cloudImportProvider' => ['required', 'in:google_drive,dropbox,onedrive'],
            'cloudReference' => ['required', 'string', 'min:3'],
            'importTitle' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $document = app(CloudDocumentIntegrationService::class)->importDocument(
                $validated['cloudImportProvider'],
                $validated['cloudReference'],
                $validated['importTitle'] ?? null,
                $this->currentUser(),
            );
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());

            return;
        }

        $provider = $this->providerLabel($validated['cloudImportProvider']);
        $this->cloudReference = '';
        $this->importTitle = '';
        $this->dispatch('notify', type: 'success', message: "{$provider} file imported successfully.");
        $this->redirectRoute('documents.edit', ['document' => $document->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.documents.transfer', [
            'documents' => $this->documents(),
        ]);
    }

    protected function documents(): Collection
    {
        return Document::query()
            ->whereIn('team_id', $this->authorizedTeamIds())
            ->whereNull('deleted_at')
            ->when(trim($this->search) !== '', function (Builder $query): void {
                $term = '%' . trim($this->search) . '%';
                $query->where('title', 'like', $term);
            })
            ->latest('updated_at')
            ->limit(200)
            ->get();
    }

    /**
     * @return Collection<int, int>
     */
    protected function authorizedTeamIds(): Collection
    {
        return $this->currentUser()->allTeams()->pluck('id');
    }

    protected function findDocument(int $documentId): Document
    {
        return Document::query()
            ->whereIn('team_id', $this->authorizedTeamIds())
            ->findOrFail($documentId);
    }

    protected function extractGoogleDocId(string $url): ?string
    {
        if (preg_match('#/document/d/([a-zA-Z0-9_-]+)#', $url, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    protected function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    protected function providerLabel(string $provider): string
    {
        return match ($provider) {
            'google_drive' => 'Google Drive',
            'dropbox' => 'Dropbox',
            'onedrive' => 'OneDrive',
            default => 'Cloud Provider',
        };
    }
}
