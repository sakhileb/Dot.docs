<?php

namespace App\Models;

use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function documentTeamRoles(): HasMany
    {
        return $this->hasMany(DocumentTeamRole::class);
    }

    public function storageQuota(): TeamStorageQuota
    {
        return $this->hasOne(TeamStorageQuota::class)
            ->firstOrCreate([], [
                'storage_limit_bytes' => 10737418240,
                'storage_used_bytes' => 0,
            ]);
    }

    public function userFolders(): HasMany
    {
        return $this->hasMany(UserFolder::class);
    }

    public function documentVersions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function documentShares(): HasMany
    {
        return $this->hasMany(DocumentShare::class);
    }

    public function documentComments(): HasMany
    {
        return $this->hasMany(DocumentComment::class);
    }

    public function aiSuggestions(): HasMany
    {
        return $this->hasMany(AiSuggestion::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }

    public function documentExportJobs(): HasMany
    {
        return $this->hasMany(DocumentExportJob::class);
    }

    public function automationWebhooks(): HasMany
    {
        return $this->hasMany(AutomationWebhook::class);
    }
}
