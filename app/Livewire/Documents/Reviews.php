<?php

namespace App\Livewire\Documents;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\DocumentComment;
use App\Models\DocumentReview;
use App\Models\DocumentShare;
use App\Models\User;
use App\Notifications\ReviewRequested;
use App\Notifications\ReviewDecided;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Reviews extends Component
{
    public Document $document;

    public string $accessPermission = 'edit';

    public string $commentBody = '';

    /**
     * @var array<int, string>
     */
    public array $replyBodies = [];

    public ?int $reviewerUserId = null;

    public string $reviewType = 'general';

    public string $reviewRequestNote = '';

    /**
     * @var array<int, string>
     */
    public array $reviewDecisionNotes = [];

    public function mount(Document $document): void
    {
        $this->authorizeAccess($document);
        $this->document = $document;
        $this->accessPermission = $this->resolvePermissionForCurrentUser($document);
    }

    public function addComment(): void
    {
        if (! $this->canComment()) {
            $this->dispatch('notify', type: 'warning', message: 'You do not have permission to comment.');

            return;
        }

        $validated = $this->validate([
            'commentBody' => ['required', 'string', 'max:5000'],
        ]);

        $comment = DocumentComment::create([
            'document_id' => $this->document->id,
            'team_id' => $this->document->team_id,
            'user_id' => Auth::id(),
            'parent_id' => null,
            'body' => $validated['commentBody'],
            'type' => 'comment',
            'is_resolved' => false,
        ]);

        $this->logMentions($comment);

        ActivityLog::logActivity(
            $this->document,
            $this->currentUser(),
            'comment',
            'thread',
            'Created comment thread',
            ['comment_id' => $comment->id]
        );

        $this->commentBody = '';
        $this->dispatch('notify', type: 'success', message: 'Comment posted.');
    }

    public function replyToComment(int $parentCommentId): void
    {
        if (! $this->canComment()) {
            return;
        }

        $body = trim((string) ($this->replyBodies[$parentCommentId] ?? ''));
        if ($body === '') {
            $this->addError('replyBodies.'.$parentCommentId, 'Reply cannot be empty.');

            return;
        }

        $parent = $this->findComment($parentCommentId);

        $reply = DocumentComment::create([
            'document_id' => $this->document->id,
            'team_id' => $this->document->team_id,
            'user_id' => Auth::id(),
            'parent_id' => $parent->id,
            'body' => $body,
            'type' => 'comment',
            'is_resolved' => false,
        ]);

        $this->logMentions($reply);

        ActivityLog::logActivity(
            $this->document,
            $this->currentUser(),
            'comment_reply',
            'thread',
            'Posted reply to comment thread',
            ['comment_id' => $reply->id, 'parent_id' => $parent->id]
        );

        $this->replyBodies[$parentCommentId] = '';
        $this->dispatch('notify', type: 'success', message: 'Reply added.');
    }

    public function toggleResolved(int $commentId): void
    {
        if (! $this->canComment()) {
            return;
        }

        $comment = $this->findComment($commentId);

        $resolved = ! $comment->is_resolved;
        $comment->update([
            'is_resolved' => $resolved,
            'resolved_by_user_id' => $resolved ? Auth::id() : null,
            'resolved_at' => $resolved ? now() : null,
        ]);

        ActivityLog::logActivity(
            $this->document,
            $this->currentUser(),
            $resolved ? 'comment_resolved' : 'comment_reopened',
            'thread',
            $resolved ? 'Resolved comment thread' : 'Reopened comment thread',
            ['comment_id' => $comment->id]
        );
    }

    public function requestReview(): void
    {
        if (! $this->canComment()) {
            $this->dispatch('notify', type: 'warning', message: 'You do not have permission to request review.');

            return;
        }

        $validated = $this->validate([
            'reviewerUserId' => ['required', 'integer', 'exists:users,id'],
            'reviewType' => ['required', 'in:general,legal,technical,editorial'],
            'reviewRequestNote' => ['nullable', 'string', 'max:2000'],
        ]);

        $review = DocumentReview::create([
            'document_id' => $this->document->id,
            'team_id' => $this->document->team_id,
            'requested_by_user_id' => Auth::id(),
            'reviewer_user_id' => $validated['reviewerUserId'],
            'status' => 'pending',
            'review_type' => $validated['reviewType'],
            'request_note' => $validated['reviewRequestNote'],
        ]);

        ActivityLog::logActivity(
            $this->document,
            $this->currentUser(),
            'review_requested',
            $review->review_type,
            'Requested document review',
            ['review_id' => $review->id, 'reviewer_id' => $review->reviewer_user_id]
        );

        $reviewer = User::find($validated['reviewerUserId']);
        if ($reviewer) {
            $reviewer->notify(new ReviewRequested($review, $this->document, $this->currentUser(), $reviewer));
        }

        $this->reviewerUserId = null;
        $this->reviewType = 'general';
        $this->reviewRequestNote = '';

        $this->dispatch('notify', type: 'success', message: 'Review requested.');
    }

    public function decideReview(int $reviewId, string $decision): void
    {
        if (! in_array($decision, ['approved', 'rejected'], true)) {
            return;
        }

        $review = $this->findReview($reviewId);

        if ((int) $review->reviewer_user_id !== (int) Auth::id()) {
            abort(403);
        }

        $review->update([
            'status' => $decision,
            'decision_note' => $this->reviewDecisionNotes[$reviewId] ?? null,
            'reviewed_at' => now(),
        ]);

        ActivityLog::logActivity(
            $this->document,
            $this->currentUser(),
            'review_'.$decision,
            $review->review_type,
            'Review '.$decision,
            ['review_id' => $review->id]
        );

        if ($review->requested_by_user_id) {
            $requester = User::find($review->requested_by_user_id);
            if ($requester) {
                $requester->notify(new ReviewDecided($review, $this->document, $this->currentUser(), $decision, $requester));
            }
        }

        $this->dispatch('notify', type: 'success', message: 'Review marked as '.$decision.'.');
    }

    public function exportComments(string $format = 'markdown')
    {
        $threads = $this->threads();

        if ($format === 'csv') {
            $content = $this->buildCsvExport($threads);

            return response()->streamDownload(function () use ($content): void {
                echo $content;
            }, 'document-comments-'.$this->document->id.'.csv', ['Content-Type' => 'text/csv']);
        }

        $content = $this->buildMarkdownExport($threads);

        return response()->streamDownload(function () use ($content): void {
            echo $content;
        }, 'document-comments-'.$this->document->id.'.md', ['Content-Type' => 'text/markdown']);
    }

    public function render()
    {
        return view('livewire.documents.reviews', [
            'threads' => $this->threads(),
            'reviews' => $this->reviews(),
            'reviewers' => $this->teamMembers(),
            'summary' => $this->reviewSummary(),
        ]);
    }

    protected function threads(): Collection
    {
        return DocumentComment::query()
            ->where('document_id', $this->document->id)
            ->whereNull('parent_id')
            ->with([
                'user',
                'resolver',
                'replies' => fn ($query) => $query->with('user')->orderBy('created_at'),
            ])
            ->orderByDesc('created_at')
            ->get();
    }

    protected function reviews(): Collection
    {
        return DocumentReview::query()
            ->where('document_id', $this->document->id)
            ->with(['reviewer', 'requestedBy'])
            ->latest()
            ->get();
    }

    protected function teamMembers(): Collection
    {
        return User::query()
            ->whereHas('teams', fn ($query) => $query->where('teams.id', $this->document->team_id))
            ->orderBy('name')
            ->get();
    }

    protected function reviewSummary(): array
    {
        $comments = DocumentComment::query()->where('document_id', $this->document->id);
        $reviews = DocumentReview::query()->where('document_id', $this->document->id);
        $mentionCount = ActivityLog::query()
            ->where('document_id', $this->document->id)
            ->where('action', 'mention_notification')
            ->count();

        return [
            'total_comments' => (clone $comments)->count(),
            'resolved_comments' => (clone $comments)->where('is_resolved', true)->count(),
            'open_comments' => (clone $comments)->where('is_resolved', false)->count(),
            'pending_reviews' => (clone $reviews)->where('status', 'pending')->count(),
            'approved_reviews' => (clone $reviews)->where('status', 'approved')->count(),
            'rejected_reviews' => (clone $reviews)->where('status', 'rejected')->count(),
            'mention_notifications' => $mentionCount,
        ];
    }

    protected function logMentions(DocumentComment $comment): void
    {
        $mentions = collect($comment->extractMentions())
            ->map(fn (string $name): string => strtolower($name))
            ->unique()
            ->values();

        if ($mentions->isEmpty()) {
            return;
        }

        $members = $this->teamMembers()->keyBy(fn (User $user) => strtolower($user->name));

        foreach ($mentions as $mention) {
            $mentioned = $members->get($mention);
            if (! $mentioned) {
                continue;
            }

            ActivityLog::logActivity(
                $this->document,
                $this->currentUser(),
                'mention_notification',
                'comment',
                'Mentioned @'.$mentioned->name.' in comment',
                [
                    'comment_id' => $comment->id,
                    'mentioned_user_id' => $mentioned->id,
                ]
            );
        }
    }

    protected function buildMarkdownExport(Collection $threads): string
    {
        $lines = [
            '# Comment Export',
            '',
            'Document: '.$this->document->title,
            'Exported at: '.now()->toDateTimeString(),
            '',
        ];

        foreach ($threads as $thread) {
            $lines[] = '## Thread #'.$thread->id;
            $lines[] = '- Author: '.($thread->user->name ?? 'Unknown');
            $lines[] = '- Created: '.(string) $thread->created_at;
            $lines[] = '- Resolved: '.($thread->is_resolved ? 'Yes' : 'No');
            $lines[] = '- Body: '.trim((string) $thread->body);
            $lines[] = '';

            foreach ($thread->replies as $reply) {
                $lines[] = '- Reply by '.($reply->user->name ?? 'Unknown').': '.trim((string) $reply->body);
            }

            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    protected function buildCsvExport(Collection $threads): string
    {
        $rows = ['thread_id,parent_id,type,author,resolved,created_at,body'];

        foreach ($threads as $thread) {
            $rows[] = $this->csvRow([
                $thread->id,
                '',
                'thread',
                $thread->user->name ?? 'Unknown',
                $thread->is_resolved ? '1' : '0',
                (string) $thread->created_at,
                (string) preg_replace('/\s+/', ' ', trim((string) $thread->body)),
            ]);

            foreach ($thread->replies as $reply) {
                $rows[] = $this->csvRow([
                    $thread->id,
                    $reply->id,
                    'reply',
                    $reply->user->name ?? 'Unknown',
                    $thread->is_resolved ? '1' : '0',
                    (string) $reply->created_at,
                    (string) preg_replace('/\s+/', ' ', trim((string) $reply->body)),
                ]);
            }
        }

        return implode("\n", $rows);
    }

    protected function csvRow(array $values): string
    {
        return collect($values)
            ->map(function ($value): string {
                $safe = str_replace('"', '""', (string) $value);

                return '"'.$safe.'"';
            })
            ->implode(',');
    }

    protected function findComment(int $commentId): DocumentComment
    {
        return DocumentComment::query()
            ->where('document_id', $this->document->id)
            ->findOrFail($commentId);
    }

    protected function findReview(int $reviewId): DocumentReview
    {
        return DocumentReview::query()
            ->where('document_id', $this->document->id)
            ->findOrFail($reviewId);
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

        $share = DocumentShare::query()
            ->where('document_id', $document->id)
            ->where('shared_with_user_id', Auth::id())
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderByRaw("CASE permission WHEN 'edit' THEN 3 WHEN 'comment' THEN 2 WHEN 'view' THEN 1 ELSE 0 END DESC")
            ->first();

        return $share?->permission ?? 'edit';
    }

    protected function canComment(): bool
    {
        return in_array($this->accessPermission, ['comment', 'edit'], true);
    }

    protected function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
