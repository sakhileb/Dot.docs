<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTeamRole extends Model
{
    protected $fillable = [
        'document_id',
        'team_id',
        'role',
        'permissions',
        'description',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public static function getRoleDescriptions(): array
    {
        return [
            'viewer' => 'Can view and download documents',
            'commenter' => 'Can view, download, and add comments',
            'editor' => 'Can edit, view, comment, and manage versions',
            'reviewer' => 'Can view, comment, and perform reviews',
            'admin' => 'Full access including sharing and permissions',
        ];
    }

    public static function getDefaultPermissions(string $role): array
    {
        return match ($role) {
            'viewer' => ['view', 'download'],
            'commenter' => ['view', 'download', 'comment'],
            'editor' => ['view', 'download', 'comment', 'edit', 'version'],
            'reviewer' => ['view', 'download', 'comment', 'review'],
            'admin' => ['view', 'download', 'comment', 'edit', 'version', 'review', 'share', 'manage_permissions'],
            default => [],
        };
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }
}
