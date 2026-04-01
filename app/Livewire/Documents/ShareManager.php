<?php

namespace App\Livewire\Documents;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\DocumentShare;
use App\Models\User;
use App\Notifications\ShareCreated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;

class ShareManager extends Component
{
    public Document $document;

    public ?int $memberUserId = null;

    public string $memberPermission = 'view';

    public string $publicPermission = 'view';

    public ?string $publicExpiresAt = null;

    public ?string $publicPassword = null;

    public ?string $publicDomain = null;

    public function mount(Document $document): void
    {
        $this->authorizeAccess($document);
        $this->document = $document;
    }

    public function shareWithMember(): void
    {
        $validated = $this->validate([
            'memberUserId' => ['required', 'integer', 'exists:users,id'],
            'memberPermission' => ['required', 'in:view,comment,edit'],
        ]);

        $user = User::query()
            ->where('id', $validated['memberUserId'])
            ->whereHas('teams', fn (Builder $query) => $query->where('teams.id', $this->document->team_id))
            ->firstOrFail();

        DocumentShare::query()->updateOrCreate(
            [
                'document_id' => $this->document->id,
                'shared_with_user_id' => $user->id,
                'is_public_link' => false,
            ],
            [
                'team_id' => $this->document->team_id,
                'shared_by_user_id' => Auth::id(),
                'shared_with_email' => $user->email,
                'permission' => $validated['memberPermission'],
                'allowed_domain' => null,
                'access_token' => null,
                'password' => null,
                'expires_at' => null,
                'status' => 'active',
            ]
        );

        ActivityLog::logActivity(
            $this->document,
            $this->currentUser(),
            'share_member',
            $validated['memberPermission'],
            'Shared document with '.$user->email,
            [
                'shared_user_id' => $user->id,
                'permission' => $validated['memberPermission'],
            ]
        );

        $user->notify(new ShareCreated($this->document, $this->currentUser(), $validated['memberPermission'], $user));

        $this->reset('memberUserId');
        $this->memberPermission = 'view';

        $this->dispatch('notify', type: 'success', message: 'Team member share saved.');
    }

    public function createPublicLink(): void
    {
        $validated = $this->validate([
            'publicPermission' => ['required', 'in:view,comment,edit'],
            'publicExpiresAt' => ['nullable', 'date', 'after:now'],
            'publicPassword' => ['nullable', 'string', 'min:6', 'max:100'],
            'publicDomain' => ['nullable', 'string', 'max:255'],
        ]);

        $domain = $this->normalizeDomain($validated['publicDomain'] ?? null);

        $share = DocumentShare::query()->create([
            'document_id' => $this->document->id,
            'team_id' => $this->document->team_id,
            'shared_by_user_id' => Auth::id(),
            'shared_with_user_id' => null,
            'shared_with_email' => null,
            'permission' => $validated['publicPermission'],
            'is_public_link' => true,
            'allowed_domain' => $domain,
            'access_token' => Str::random(48),
            'password' => filled($validated['publicPassword'] ?? null) ? Hash::make($validated['publicPassword']) : null,
            'expires_at' => $validated['publicExpiresAt'] ?: null,
            'status' => 'active',
        ]);

        ActivityLog::logActivity(
            $this->document,
            $this->currentUser(),
            'share_link',
            $validated['publicPermission'],
            'Created public share link',
            [
                'share_id' => $share->id,
                'expires_at' => $share->expires_at,
                'domain' => $share->allowed_domain,
            ]
        );

        $this->publicPermission = 'view';
        $this->publicExpiresAt = null;
        $this->publicPassword = null;
        $this->publicDomain = null;

        $this->dispatch('notify', type: 'success', message: 'Public share link created.');
    }

    public function revokeShare(int $shareId): void
    {
        $share = $this->findShare($shareId);
        $share->update(['status' => 'revoked']);

        ActivityLog::logActivity(
            $this->document,
            $this->currentUser(),
            'share_revoked',
            $share->permission,
            'Revoked share access',
            ['share_id' => $share->id]
        );

        $this->dispatch('notify', type: 'success', message: 'Share revoked.');
    }

    public function activateShare(int $shareId): void
    {
        $share = $this->findShare($shareId);
        $share->update(['status' => 'active']);

        $this->dispatch('notify', type: 'success', message: 'Share reactivated.');
    }

    public function render()
    {
        return view('livewire.documents.share-manager', [
            'teamMembers' => $this->teamMembers(),
            'shares' => $this->shares(),
            'analytics' => $this->shareAnalytics(),
        ]);
    }

    protected function teamMembers()
    {
        return User::query()
            ->where('id', '!=', Auth::id())
            ->whereHas('teams', fn (Builder $query) => $query->where('teams.id', $this->document->team_id))
            ->orderBy('name')
            ->get();
    }

    protected function shares()
    {
        return DocumentShare::query()
            ->where('document_id', $this->document->id)
            ->with(['sharedWithUser', 'sharedByUser'])
            ->latest()
            ->get();
    }

    protected function shareAnalytics(): array
    {
        $shares = DocumentShare::query()->where('document_id', $this->document->id);

        return [
            'total_shares' => (clone $shares)->count(),
            'active_shares' => (clone $shares)->where('status', 'active')->count(),
            'total_views' => (clone $shares)->sum('views_count'),
            'total_edits' => (clone $shares)->sum('edits_count'),
            'link_access' => (clone $shares)->sum('link_access_count'),
        ];
    }

    protected function findShare(int $shareId): DocumentShare
    {
        return DocumentShare::query()
            ->where('document_id', $this->document->id)
            ->findOrFail($shareId);
    }

    protected function normalizeDomain(?string $domain): ?string
    {
        if (! filled($domain)) {
            return null;
        }

        $normalized = strtolower(trim($domain));
        $normalized = preg_replace('/^@/', '', $normalized);

        return $normalized !== '' ? $normalized : null;
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
