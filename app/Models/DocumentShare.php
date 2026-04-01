<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DocumentShare extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_id',
        'team_id',
        'shared_by_user_id',
        'shared_with_user_id',
        'permission',
        'is_public_link',
        'shared_with_email',
        'allowed_domain',
        'access_token',
        'password',
        'expires_at',
        'last_accessed_at',
        'views_count',
        'edits_count',
        'link_access_count',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_public_link' => 'boolean',
            'expires_at' => 'datetime',
            'last_accessed_at' => 'datetime',
            'views_count' => 'integer',
            'edits_count' => 'integer',
            'link_access_count' => 'integer',
        ];
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        return ! $this->isExpired();
    }

    public function isExpired(): bool
    {
        return $this->expires_at instanceof Carbon && $this->expires_at->isPast();
    }

    public function allowsPermission(string $required): bool
    {
        $map = [
            'view' => 1,
            'comment' => 2,
            'edit' => 3,
        ];

        $granted = $map[$this->permission] ?? 0;
        $needed = $map[$required] ?? 0;

        return $granted >= $needed;
    }

    public function incrementViewCount(): void
    {
        $this->increment('views_count');
        $this->update(['last_accessed_at' => now()]);
    }

    public function incrementEditCount(): void
    {
        $this->increment('edits_count');
        $this->update(['last_accessed_at' => now()]);
    }

    public function incrementLinkAccessCount(): void
    {
        $this->increment('link_access_count');
        $this->update(['last_accessed_at' => now()]);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function sharedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by_user_id');
    }

    public function sharedWithUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_with_user_id');
    }
}
