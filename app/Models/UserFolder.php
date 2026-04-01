<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserFolder extends Model
{
    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'description',
        'color',
        'parent_id',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

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
        return $this->belongsTo(UserFolder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(UserFolder::class, 'parent_id')->orderBy('sort_order');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(FolderDocument::class);
    }

    public function getFullPath(): string
    {
        $path = [$this->name];
        $current = $this;

        while ($current->parent_id) {
            $current = $current->parent;
            $path[] = $current->name;
        }

        return implode(' / ', array_reverse($path));
    }
}
