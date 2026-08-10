<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    protected $fillable = [
        'owner_id',
        'team_id',
        'parent_id',
        'name',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Ancestors from the root down to (but not including) this folder --
     * used to render a breadcrumb trail.
     *
     * @return list<Folder>
     */
    public function breadcrumbs(): array
    {
        $trail = [];
        $node = $this->parent;

        while ($node !== null) {
            array_unshift($trail, $node);
            $node = $node->parent;
        }

        return $trail;
    }

    /**
     * Every descendant id (this folder's children, their children, ...),
     * not including this folder's own id -- used to block moving a folder
     * into its own subtree.
     *
     * @return list<int>
     */
    public function descendantIds(): array
    {
        $ids = [];
        $queue = $this->children()->pluck('id')->all();

        while (! empty($queue)) {
            $id = array_shift($queue);
            $ids[] = $id;
            $queue = array_merge($queue, Folder::where('parent_id', $id)->pluck('id')->all());
        }

        return $ids;
    }
}
