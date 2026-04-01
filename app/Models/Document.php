<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Document extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'user_id',
        'title',
        'content',
        'version',
        'status',
        'is_archived',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_archived' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(DocumentShare::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DocumentComment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(DocumentReview::class);
    }

    public function aiSuggestions(): HasMany
    {
        return $this->hasMany(AiSuggestion::class);
    }

    public function exportJobs(): HasMany
    {
        return $this->hasMany(DocumentExportJob::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function cursors(): HasMany
    {
        return $this->hasMany(DocumentCursor::class);
    }

    public function presence(): HasMany
    {
        return $this->hasMany(UserPresence::class);
    }

    public function teamRoles(): HasMany
    {
        return $this->hasMany(DocumentTeamRole::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(DocumentFavorite::class);
    }

    public function folders(): HasMany
    {
        return $this->hasMany(FolderDocument::class);
    }

    public function citations(): HasMany
    {
        return $this->hasMany(CitationReference::class);
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(DocumentFormField::class);
    }

    public function getContentAttribute(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            // Graceful fallback for legacy plaintext content not yet migrated.
            return $value;
        }
    }

    public function setContentAttribute(?string $value): void
    {
        $sanitized = $this->sanitizeContent($value);
        $this->attributes['content'] = $sanitized !== null ? Crypt::encryptString($sanitized) : null;
    }

    public function scopeForTeam(Builder $query, int $teamId): Builder
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->latest('updated_at');
    }

    private function sanitizeContent(?string $content): ?string
    {
        if ($content === null) {
            return null;
        }

        $allowedTags = '<p><br><strong><em><u><s><a><ul><ol><li><blockquote><pre><code><h1><h2><h3><h4><h5><h6><span><div><section><img><table><thead><tbody><tr><th><td><form><label><input><select><option><button><textarea>';
        $clean = strip_tags($content, $allowedTags);

        // Remove inline event handlers and javascript: URLs as a lightweight defense.
        $clean = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? $clean;
        $clean = preg_replace('/(href|src)\s*=\s*("|\')\s*javascript:[^"\']*("|\')/i', '$1="#"', $clean) ?? $clean;

        return $clean;
    }
}
