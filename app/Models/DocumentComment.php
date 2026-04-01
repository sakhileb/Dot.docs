<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class DocumentComment extends Model
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
        'user_id',
        'parent_id',
        'body',
        'type',
        'suggestion_type',
        'suggested_text',
        'selection_start',
        'selection_end',
        'suggestion_accepted',
        'accepted_by_user_id',
        'accepted_at',
        'is_resolved',
        'resolved_by_user_id',
        'resolved_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => 'string',
            'selection_start' => 'integer',
            'selection_end' => 'integer',
            'suggestion_accepted' => 'boolean',
            'is_resolved' => 'boolean',
            'resolved_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    public function acceptor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }

    /**
     * Extract @mentions from comment body
     */
    public function extractMentions(): array
    {
        preg_match_all('/@(\w+)/', $this->body, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Check if this is a suggestion
     */
    public function isSuggestion(): bool
    {
        return $this->type === 'suggestion';
    }

    /**
     * Check if this is a mention
     */
    public function isMention(): bool
    {
        return $this->type === 'mention';
    }
}
