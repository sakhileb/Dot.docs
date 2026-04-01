<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FolderDocument extends Model
{
    protected $fillable = [
        'user_folder_id',
        'document_id',
        'sort_order',
    ];

    public $timestamps = false;

    public function folder(): BelongsTo
    {
        return $this->belongsTo(UserFolder::class, 'user_folder_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
