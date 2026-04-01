<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationWebhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'provider',
        'endpoint_url',
        'secret',
        'subscribed_events',
        'is_active',
        'last_response_status',
        'last_triggered_at',
    ];

    protected function casts(): array
    {
        return [
            'subscribed_events' => 'array',
            'is_active' => 'boolean',
            'last_triggered_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function listensFor(string $event): bool
    {
        $events = $this->subscribed_events ?? [];

        return in_array('*', $events, true) || in_array($event, $events, true);
    }
}
