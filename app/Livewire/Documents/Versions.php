<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Versions extends Component
{
    use WithPagination;

    public Document $document;

    public ?int $leftVersionId = null;

    public ?int $rightVersionId = null;

    public ?float $similarity = null;

    public string $leftPreview = '';

    public string $rightPreview = '';

    public string $leftTitle = '';

    public string $rightTitle = '';

    public function mount(Document $document): void
    {
        $this->authorizeAccess($document);
        $this->document = $document;
    }

    public function compareVersions(): void
    {
        if (! $this->leftVersionId || ! $this->rightVersionId) {
            $this->dispatch('notify', type: 'warning', message: 'Select both versions to compare.');

            return;
        }

        $left = $this->findVersion($this->leftVersionId);
        $right = $this->findVersion($this->rightVersionId);

        $leftText = trim(strip_tags((string) $left->content));
        $rightText = trim(strip_tags((string) $right->content));

        $percent = 0.0;
        similar_text($leftText, $rightText, $percent);

        $this->similarity = round($percent, 2);
        $this->leftTitle = 'v' . $left->version . ' - ' . ($left->title ?? 'Untitled');
        $this->rightTitle = 'v' . $right->version . ' - ' . ($right->title ?? 'Untitled');
        $this->leftPreview = mb_substr($leftText, 0, 4000);
        $this->rightPreview = mb_substr($rightText, 0, 4000);
    }

    public function restoreVersion(int $versionId): void
    {
        $version = $this->findVersion($versionId);

        $nextVersion = $this->nextVersionNumber();

        $this->document->update([
            'title' => $version->title ?: $this->document->title,
            'content' => $version->content,
            'version' => $nextVersion,
            'user_id' => Auth::id(),
        ]);

        DocumentVersion::create([
            'document_id' => $this->document->id,
            'team_id' => $this->document->team_id,
            'user_id' => Auth::id(),
            'version' => $nextVersion,
            'title' => $this->document->title,
            'content' => $this->document->content,
            'change_summary' => 'Restored from version ' . $version->version,
            'is_auto_save' => false,
            'is_milestone' => false,
            'version_notes' => 'Restore operation',
            'content_hash' => hash('sha256', (string) $this->document->content),
        ]);

        $this->document->refresh();
        $this->dispatch('notify', type: 'success', message: 'Version restored successfully.');
    }

    public function pruneAutoSaves(): void
    {
        $keep = max((int) env('DOCUMENT_AUTOSAVE_KEEP', 50), 10);

        $idsToKeep = DocumentVersion::query()
            ->where('document_id', $this->document->id)
            ->where('is_auto_save', true)
            ->orderByDesc('created_at')
            ->limit($keep)
            ->pluck('id');

        $deleted = DocumentVersion::query()
            ->where('document_id', $this->document->id)
            ->where('is_auto_save', true)
            ->whereNotIn('id', $idsToKeep)
            ->delete();

        $this->dispatch('notify', type: 'success', message: "Cleanup complete. Removed {$deleted} old autosave version(s).");
    }

    public function render()
    {
        return view('livewire.documents.versions', [
            'versions' => DocumentVersion::query()
                ->where('document_id', $this->document->id)
                ->with('user')
                ->latest('version')
                ->paginate(20),
        ]);
    }

    protected function findVersion(int $versionId): DocumentVersion
    {
        return DocumentVersion::query()
            ->where('document_id', $this->document->id)
            ->findOrFail($versionId);
    }

    protected function nextVersionNumber(): int
    {
        $latest = DocumentVersion::query()
            ->where('document_id', $this->document->id)
            ->max('version');

        return max((int) $latest, (int) $this->document->version) + 1;
    }

    protected function authorizeAccess(Document $document): void
    {
        $teamIds = $this->currentUser()->allTeams()->pluck('id');

        abort_unless($teamIds->contains($document->team_id), 403);
    }

    protected function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
