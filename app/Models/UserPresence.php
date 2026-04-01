<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPresence extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'user_presence';

    protected $fillable = [
        'document_id',
        'user_id',
        'status',
        'last_ping_at',
    ];

    protected $casts = [
        'last_ping_at' => 'datetime',
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
     * Update user presence status in a document
     */
    public static function updateStatus(
        Document $document,
        User $user,
        string $status = 'viewing'
    ): self {
        $presence = static::updateOrCreate(
            ['document_id' => $document->id, 'user_id' => $user->id],
            [
                'status' => $status,
                'last_ping_at' => now(),
            ]
        );

        return $presence;
    }

    /**
     * Get active collaborators in a document (active in last 5 minutes)
     */
    public static function activeCollaborators(Document $document)
    {
        return static::where('document_id', $document->id)
            ->where('last_ping_at', '>', now()->subMinutes(5))
            ->with('user')
            ->get();
    }
}
