<?php

namespace App\Livewire\Documents;

use App\Models\DocumentShare;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class SharedView extends Component
{
    public string $token;

    public string $password = '';

    public bool $authorized = false;

    public bool $requiresSignIn = false;

    public ?string $accessError = null;

    public ?DocumentShare $share = null;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->loadShare();
    }

    public function submitPassword(): void
    {
        if (! $this->share || ! $this->share->password) {
            return;
        }

        $this->validate([
            'password' => ['required', 'string', 'max:100'],
        ]);

        if (! Hash::check($this->password, $this->share->password)) {
            $this->addError('password', 'Invalid password.');

            return;
        }

        session([$this->passwordSessionKey() => true]);
        $this->password = '';
        $this->authorized = true;

        $this->recordAccess();
    }

    public function render()
    {
        return view('livewire.documents.shared-view');
    }

    protected function loadShare(): void
    {
        $this->share = DocumentShare::query()
            ->where('access_token', $this->token)
            ->where('is_public_link', true)
            ->first();

        if (! $this->share) {
            $this->accessError = 'This share link is invalid.';

            return;
        }

        if (! $this->share->isActive()) {
            $this->accessError = 'This share link has expired or is no longer active.';

            return;
        }

        $domain = $this->share->allowed_domain;
        if ($domain) {
            if (! Auth::check()) {
                $this->requiresSignIn = true;

                return;
            }

            $emailDomain = strtolower((string) substr(strrchr((string) Auth::user()?->email, '@'), 1));
            if ($emailDomain !== strtolower($domain)) {
                $this->accessError = 'Your email domain is not allowed for this share.';

                return;
            }
        }

        if ($this->share->password && ! session($this->passwordSessionKey(), false)) {
            return;
        }

        $this->authorized = true;
        $this->recordAccess();
    }

    protected function recordAccess(): void
    {
        if (! $this->share) {
            return;
        }

        $sessionKey = 'share_access_recorded_'.$this->share->id;
        if (session($sessionKey, false) === true) {
            return;
        }

        $this->share->incrementViewCount();
        $this->share->incrementLinkAccessCount();
        session([$sessionKey => true]);
    }

    protected function passwordSessionKey(): string
    {
        return 'share_password_verified_'.$this->token;
    }

    protected function currentUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }
}
