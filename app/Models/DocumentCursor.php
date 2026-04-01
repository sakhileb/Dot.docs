<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentCursor extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'user_id',
        'position',
        'selection_start',
        'selection_end',
        'color',
        'name',
        'last_active_at',
    ];

    protected $casts = [
        'position' => 'integer',
        'selection_start' => 'integer',
        'selection_end' => 'integer',
        'last_active_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Update or create cursor position for a user in a document
     */
    public static function updatePosition(
        Document $document,
        User $user,
        int $position,
        ?int $selectionStart = null,
        ?int $selectionEnd = null
    ): self {
        $cursor = static::updateOrCreate(
            ['document_id' => $document->id, 'user_id' => $user->id],
            [
                'position' => $position,
                'selection_start' => $selectionStart,
                'selection_end' => $selectionEnd,
                'color' => $user->cursor_color ?? '#6366f1',
                'name' => $user->name,
                'last_active_at' => now(),
            ]
        );

        return $cursor;
    }
}
