<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSlashCommand extends Model
{
    protected $fillable = [
        'user_id',
        'team_id',
        'name',
        'description',
        'prompt_template',
        'share_with_team',
    ];

    protected $casts = [
        'share_with_team' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
