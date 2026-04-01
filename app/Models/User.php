<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function documentVersions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function sharesCreated(): HasMany
    {
        return $this->hasMany(DocumentShare::class, 'shared_by_user_id');
    }

    public function sharesReceived(): HasMany
    {
        return $this->hasMany(DocumentShare::class, 'shared_with_user_id');
    }

    public function documentComments(): HasMany
    {
        return $this->hasMany(DocumentComment::class);
    }

    public function documentReviewsRequested(): HasMany
    {
        return $this->hasMany(DocumentReview::class, 'requested_by_user_id');
    }

    public function documentReviewsAssigned(): HasMany
    {
        return $this->hasMany(DocumentReview::class, 'reviewer_user_id');
    }

    public function aiSuggestions(): HasMany
    {
        return $this->hasMany(AiSuggestion::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }

    public function notificationPreferences()
    {
        return $this->hasOne(UserNotificationPreference::class)
            ->firstOrCreate([], [
                'document_changes_email' => true,
                'document_changes_browser' => true,
                'comments_email' => true,
                'comments_browser' => true,
                'mentions_email' => true,
                'mentions_browser' => true,
                'shares_email' => true,
                'shares_browser' => true,
                'reviews_email' => true,
                'reviews_browser' => true,
                'push_enabled' => false,
            ]);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(DocumentFavorite::class);
    }

    public function folders(): HasMany
    {
        return $this->hasMany(UserFolder::class);
    }

    public function documentExportJobs(): HasMany
    {
        return $this->hasMany(DocumentExportJob::class);
    }
}
