<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'document_id',
        'user_id',
        'team_id',
        'action',
        'action_type',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Log an activity for a document
     */
    public static function logActivity(
        Document $document,
        User $user,
        string $action,
        ?string $actionType = null,
        ?string $description = null,
        ?array $metadata = null
    ): self {
        return static::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'team_id' => $document->team_id,
            'action' => $action,
            'action_type' => $actionType,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
