<?php

namespace App\Livewire\Profile;

use App\Models\UserNotificationPreference;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationPreferences extends Component
{
    public bool $document_changes_email = true;

    public bool $document_changes_browser = true;

    public bool $comments_email = true;

    public bool $comments_browser = true;

    public bool $mentions_email = true;

    public bool $mentions_browser = true;

    public bool $shares_email = true;

    public bool $shares_browser = true;

    public bool $reviews_email = true;

    public bool $reviews_browser = true;

    public bool $push_enabled = false;

    public function mount(): void
    {
        $prefs = Auth::user()->notificationPreferences;

        $this->document_changes_email = $prefs->document_changes_email;
        $this->document_changes_browser = $prefs->document_changes_browser;
        $this->comments_email = $prefs->comments_email;
        $this->comments_browser = $prefs->comments_browser;
        $this->mentions_email = $prefs->mentions_email;
        $this->mentions_browser = $prefs->mentions_browser;
        $this->shares_email = $prefs->shares_email;
        $this->shares_browser = $prefs->shares_browser;
        $this->reviews_email = $prefs->reviews_email;
        $this->reviews_browser = $prefs->reviews_browser;
        $this->push_enabled = $prefs->push_enabled;
    }

    public function save(): void
    {
        Auth::user()->notificationPreferences->update([
            'document_changes_email' => $this->document_changes_email,
            'document_changes_browser' => $this->document_changes_browser,
            'comments_email' => $this->comments_email,
            'comments_browser' => $this->comments_browser,
            'mentions_email' => $this->mentions_email,
            'mentions_browser' => $this->mentions_browser,
            'shares_email' => $this->shares_email,
            'shares_browser' => $this->shares_browser,
            'reviews_email' => $this->reviews_email,
            'reviews_browser' => $this->reviews_browser,
            'push_enabled' => $this->push_enabled,
        ]);

        $this->dispatch('notify', type: 'success', message: 'Notification preferences saved.');
    }

    public function render()
    {
        return view('livewire.profile.notification-preferences');
    }
}
