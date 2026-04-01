<?php

namespace App\Livewire\Documents;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\DocumentCursor;
use App\Models\DocumentComment;
use App\Models\DocumentShare;
use App\Models\Template;
use App\Models\TemplateVersion;
use App\Models\User;
use App\Models\UserPresence;
use App\Services\DocumentAnalyticsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Editor extends Component
{
    use WithFileUploads;

    public Document $document;

    public string $title = '';

    public ?string $content = null;

    public string $status = 'draft';

    public ?int $wordCount = 0;

    public ?int $characterCount = 0;

    public bool $suggestionMode = false;

    public ?int $cursorPosition = null;

    public ?int $selectionStart = null;

    public ?int $selectionEnd = null;

    public bool $showTemplateForm = false;

    public string $templateName = '';

    public string $templateCategory = 'general';

    public string $templateDescription = '';

    public bool $templateIsPublic = false;

    public bool $showMilestoneForm = false;

    public string $milestoneName = '';

    public string $milestoneNotes = '';

    public string $accessPermission = 'edit';

    #[On('cursor-updated')]
    public function handleCursorUpdate(int $position, ?int $selectionStart = null, ?int $selectionEnd = null): void
    {
        DocumentCursor::updatePosition(
            $this->document,
            $this->currentUser(),
            $position,
            $selectionStart,
            $selectionEnd
        );

        $this->broadcast('cursor-position-updated', [
            'user_id' => Auth::id(),
            'position' => $position,
            'selection_start' => $selectionStart,
            'selection_end' => $selectionEnd,
        ]);
    }

    public function mount(Document $document): void
    {
        $this->authorizeAccess($document);

        $this->document = $document;
        $this->title = $document->title;
        $this->content = $document->content;
        $this->status = $document->status;

        $this->accessPermission = $this->resolvePermissionForCurrentUser($document);

        $share = $this->activeUserShare($document);
        if ($share) {
            $share->incrementViewCount();
        }

        // Register user as active in this document
        UserPresence::updateStatus($document, $this->currentUser(), 'viewing');

        // Log activity: user joined
        ActivityLog::logActivity(
            $document,
            $this->currentUser(),
            'view',
            null,
            'User opened document'
        );
    }

    public function save(): void
    {
        if (! $this->canEdit()) {
            $this->dispatch('notify', type: 'warning', message: 'You have view/comment access only.');

            return;
        }

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $newContent = $validated['content'] !== null && trim($validated['content']) !== ''
            ? $validated['content']
            : null;

        $contentChanged = $this->document->content !== $newContent;
        $titleChanged = $this->document->title !== $validated['title'];

        $newVersionNumber = ($contentChanged || $titleChanged)
            ? $this->nextVersionNumber()
            : $this->document->version;

        $this->document->update([
            'title' => $validated['title'],
            'content' => $newContent,
            'status' => $validated['status'],
            'version' => $newVersionNumber,
            'user_id' => Auth::id(),
        ]);

        $this->document->refresh();

        if ($contentChanged || $titleChanged) {
            $this->createVersionSnapshot(
                changeSummary: $contentChanged && $titleChanged ? 'Manual save: title and content updated' : ($contentChanged ? 'Manual save: content updated' : 'Manual save: title updated'),
                isAutoSave: false,
                isMilestone: false,
                milestoneName: null,
                notes: null,
            );
        }

        // Log activities for changes
        if ($contentChanged) {
            ActivityLog::logActivity(
                $this->document,
                $this->currentUser(),
                'edit',
                'content',
                'Document content updated'
            );
        }

        if ($titleChanged) {
            ActivityLog::logActivity(
                $this->document,
                $this->currentUser(),
                'edit',
                'title',
                'Document title updated'
            );
        }

        // Update user presence to 'editing'
        UserPresence::updateStatus($this->document, $this->currentUser(), 'editing');

        $share = $this->activeUserShare($this->document);
        if ($share) {
            $share->incrementEditCount();
        }

        $this->dispatch('notify', type: 'success', message: 'Document saved.');
    }

    public function autosave(): void
    {
        if (! $this->canEdit()) {
            return;
        }

        $currentContent = $this->content !== null && trim($this->content) !== ''
            ? $this->content
            : null;

        if ($this->document->content === $currentContent) {
            return;
        }

        $nextVersion = $this->nextVersionNumber();

        $this->document->update([
            'content' => $currentContent,
            'version' => $nextVersion,
            'user_id' => Auth::id(),
        ]);

        $this->document->refresh();

        $this->createVersionSnapshot(
            changeSummary: 'Autosave snapshot',
            isAutoSave: true,
            isMilestone: false,
            milestoneName: null,
            notes: null,
        );
    }

    public function toggleMilestoneForm(): void
    {
        $this->showMilestoneForm = ! $this->showMilestoneForm;

        if ($this->showMilestoneForm && $this->milestoneName === '') {
            $this->milestoneName = 'Milestone v' . $this->document->version;
        }
    }

    public function createMilestone(): void
    {
        if (! $this->canEdit()) {
            $this->dispatch('notify', type: 'warning', message: 'Only editors can create milestones.');

            return;
        }

        $validated = $this->validate([
            'milestoneName' => ['required', 'string', 'max:255'],
            'milestoneNotes' => ['nullable', 'string', 'max:1500'],
        ]);

        $this->createVersionSnapshot(
            changeSummary: 'Milestone created',
            isAutoSave: false,
            isMilestone: true,
            milestoneName: $validated['milestoneName'],
            notes: $validated['milestoneNotes'],
        );

        $this->dispatch('notify', type: 'success', message: 'Milestone version created.');
        $this->showMilestoneForm = false;
        $this->milestoneName = '';
        $this->milestoneNotes = '';
    }

    public function uploadImage($file)
    {
        try {
            $this->validate([
                'file' => ['required', 'image', 'max:5120'], // 5MB max
            ], [], [
                'file' => 'image',
            ]);

            $path = Storage::disk('public')->putFile(
                'documents/' . $this->document->id,
                $file,
                'public'
            );

            $url = env('APP_URL') . '/storage/' . $path;

            return [
                'success' => true,
                'url' => $url,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to upload image',
            ];
        }
    }

    public function updateWordCount(): void
    {
        if (! empty($this->content)) {
            $dom = new \DOMDocument();
            @$dom->loadHTML($this->content);
            $text = $dom->textContent;
            $this->wordCount = str_word_count($text);
            $this->characterCount = strlen($text);
        } else {
            $this->wordCount = 0;
            $this->characterCount = 0;
        }
    }

    public function toggleSuggestionMode(): void
    {
        if (! $this->canComment()) {
            $this->dispatch('notify', type: 'warning', message: 'You do not have permission to suggest changes.');

            return;
        }

        $this->suggestionMode = ! $this->suggestionMode;

        if ($this->suggestionMode) {
            UserPresence::updateStatus($this->document, $this->currentUser(), 'suggesting');
        } else {
            UserPresence::updateStatus($this->document, $this->currentUser(), 'editing');
        }

        ActivityLog::logActivity(
            $this->document,
            $this->currentUser(),
            'mode_change',
            null,
            $this->suggestionMode ? 'Entered suggestion mode' : 'Exited suggestion mode'
        );
    }

    public function addComment(string $body, ?int $selectionStart = null, ?int $selectionEnd = null): void
    {
        if (! $this->canComment()) {
            return;
        }

        if (! $this->suggestionMode) {
            DocumentComment::create([
                'document_id' => $this->document->id,
                'team_id' => $this->document->team_id,
                'user_id' => Auth::id(),
                'body' => $body,
                'type' => 'comment',
                'selection_start' => $selectionStart,
                'selection_end' => $selectionEnd,
            ]);

            ActivityLog::logActivity(
                $this->document,
                $this->currentUser(),
                'comment',
                null,
                'Added inline comment'
            );
        }
    }

    public function addSuggestion(
        string $body,
        ?string $suggestedText = null,
        string $suggestionType = 'modification',
        ?int $selectionStart = null,
        ?int $selectionEnd = null
    ): void {
        if (! $this->canComment()) {
            return;
        }

        if ($this->suggestionMode) {
            DocumentComment::create([
                'document_id' => $this->document->id,
                'team_id' => $this->document->team_id,
                'user_id' => Auth::id(),
                'body' => $body,
                'type' => 'suggestion',
                'suggestion_type' => $suggestionType,
                'suggested_text' => $suggestedText,
                'selection_start' => $selectionStart,
                'selection_end' => $selectionEnd,
            ]);

            ActivityLog::logActivity(
                $this->document,
                $this->currentUser(),
                'suggestion',
                $suggestionType,
                'Added suggestion'
            );
        }
    }

    public function acceptSuggestion(DocumentComment $comment): void
    {
        if (! $this->canEdit()) {
            return;
        }

        if ($comment->isSuggestion()) {
            $comment->update([
                'suggestion_accepted' => true,
                'accepted_by_user_id' => Auth::id(),
                'accepted_at' => now(),
            ]);

            ActivityLog::logActivity(
                $this->document,
                $this->currentUser(),
                'suggestion_accepted',
                null,
                'Accepted suggestion'
            );
        }
    }

    public function rejectSuggestion(DocumentComment $comment): void
    {
        if (! $this->canEdit()) {
            return;
        }

        if ($comment->isSuggestion()) {
            $comment->delete();

            ActivityLog::logActivity(
                $this->document,
                $this->currentUser(),
                'suggestion_rejected',
                null,
                'Rejected suggestion'
            );
        }
    }

    public function getActiveCollaborators()
    {
        return UserPresence::activeCollaborators($this->document);
    }

    public function getRecentActivity()
    {
        return $this->document->activityLogs()
            ->with('user')
            ->latest()
            ->limit(20)
            ->get();
    }

    public function toggleTemplateForm(): void
    {
        $this->showTemplateForm = ! $this->showTemplateForm;

        if ($this->showTemplateForm && $this->templateName === '') {
            $this->templateName = $this->title;
            $this->templateDescription = 'Template created from document: ' . $this->title;
        }
    }

    public function saveAsTemplate(): void
    {
        if (! $this->canEdit()) {
            $this->dispatch('notify', type: 'warning', message: 'Only editors can save templates.');

            return;
        }

        $validated = $this->validate([
            'templateName' => ['required', 'string', 'max:255'],
            'templateCategory' => ['required', 'string', 'max:100'],
            'templateDescription' => ['nullable', 'string', 'max:1000'],
            'templateIsPublic' => ['boolean'],
        ]);

        $template = Template::create([
            'team_id' => $this->document->team_id,
            'user_id' => Auth::id(),
            'name' => $validated['templateName'],
            'category' => $validated['templateCategory'],
            'description' => $validated['templateDescription'],
            'content' => $this->content,
            'version' => 1,
            'is_public' => $validated['templateIsPublic'],
        ]);

        TemplateVersion::create([
            'template_id' => $template->id,
            'user_id' => Auth::id(),
            'version' => 1,
            'name' => $template->name,
            'description' => $template->description,
            'content' => $template->content,
        ]);

        ActivityLog::logActivity(
            $this->document,
            $this->currentUser(),
            'template_create',
            null,
            'Saved document as template'
        );

        $this->dispatch('notify', type: 'success', message: 'Template saved successfully.');
        $this->showTemplateForm = false;
    }

    public function render()
    {
        return view('livewire.documents.editor', [
            'analytics' => $this->getDocumentAnalytics(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getDocumentAnalytics(): array
    {
        /** @var DocumentAnalyticsService $service */
        $service = app(DocumentAnalyticsService::class);

        return $service->analyze($this->document);
    }

    protected function createVersionSnapshot(
        string $changeSummary,
        bool $isAutoSave,
        bool $isMilestone,
        ?string $milestoneName,
        ?string $notes
    ): void {
        DocumentVersion::create([
            'document_id' => $this->document->id,
            'team_id' => $this->document->team_id,
            'user_id' => Auth::id(),
            'version' => $this->document->version,
            'title' => $this->document->title,
            'content' => $this->document->content,
            'change_summary' => $changeSummary,
            'is_auto_save' => $isAutoSave,
            'is_milestone' => $isMilestone,
            'milestone_name' => $milestoneName,
            'version_notes' => $notes,
            'content_hash' => hash('sha256', (string) $this->document->content),
        ]);

        $this->applyVersionCleanupPolicy();
    }

    protected function applyVersionCleanupPolicy(): void
    {
        $keep = max((int) env('DOCUMENT_AUTOSAVE_KEEP', 50), 10);

        $keepIds = DocumentVersion::query()
            ->where('document_id', $this->document->id)
            ->where('is_auto_save', true)
            ->orderByDesc('created_at')
            ->limit($keep)
            ->pluck('id');

        DocumentVersion::query()
            ->where('document_id', $this->document->id)
            ->where('is_auto_save', true)
            ->whereNotIn('id', $keepIds)
            ->delete();
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

    protected function resolvePermissionForCurrentUser(Document $document): string
    {
        if ((int) $document->user_id === (int) Auth::id()) {
            return 'edit';
        }

        $share = $this->activeUserShare($document);

        if (! $share) {
            return 'edit';
        }

        return $share->permission;
    }

    protected function activeUserShare(Document $document): ?DocumentShare
    {
        return DocumentShare::query()
            ->where('document_id', $document->id)
            ->where('shared_with_user_id', Auth::id())
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderByRaw("CASE permission WHEN 'edit' THEN 3 WHEN 'comment' THEN 2 WHEN 'view' THEN 1 ELSE 0 END DESC")
            ->first();
    }

    protected function canComment(): bool
    {
        return in_array($this->accessPermission, ['comment', 'edit'], true);
    }

    protected function canEdit(): bool
    {
        return $this->accessPermission === 'edit';
    }

    protected function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
