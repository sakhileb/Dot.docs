<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamStorageQuota extends Model
{
    protected $fillable = [
        'team_id',
        'storage_limit_bytes',
        'storage_used_bytes',
        'last_calculated_at',
    ];

    protected $casts = [
        'storage_limit_bytes' => 'integer',
        'storage_used_bytes' => 'integer',
        'last_calculated_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getUsagePercentage(): float
    {
        if ($this->storage_limit_bytes === 0) {
            return 0;
        }

        return ($this->storage_used_bytes / $this->storage_limit_bytes) * 100;
    }

    public function getAvailableBytes(): int
    {
        return max(0, $this->storage_limit_bytes - $this->storage_used_bytes);
    }

    public function isLimitExceeded(): bool
    {
        return $this->storage_used_bytes > $this->storage_limit_bytes;
    }

    public static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
