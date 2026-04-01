<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    use HasFactory;

    protected $table = 'user_notification_preferences';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'document_changes_email',
        'document_changes_browser',
        'comments_email',
        'comments_browser',
        'mentions_email',
        'mentions_browser',
        'shares_email',
        'shares_browser',
        'reviews_email',
        'reviews_browser',
        'push_enabled',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'document_changes_email' => 'boolean',
            'document_changes_browser' => 'boolean',
            'comments_email' => 'boolean',
            'comments_browser' => 'boolean',
            'mentions_email' => 'boolean',
            'mentions_browser' => 'boolean',
            'shares_email' => 'boolean',
            'shares_browser' => 'boolean',
            'reviews_email' => 'boolean',
            'reviews_browser' => 'boolean',
            'push_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
