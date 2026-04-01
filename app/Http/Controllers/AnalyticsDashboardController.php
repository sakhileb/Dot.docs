<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;
use App\Models\AiSuggestion;
use Illuminate\View\View;

class AnalyticsDashboardController extends Controller
{
    public function index(): View
    {
        $provider = config('analytics.provider', 'plausible');

        $stats = [
            'documents_total' => Document::query()->count(),
            'documents_last_7_days' => Document::query()->where('created_at', '>=', now()->subDays(7))->count(),
            'users_total' => User::query()->count(),
            'active_users_last_7_days' => User::query()->where('updated_at', '>=', now()->subDays(7))->count(),
            'ai_requests_last_7_days' => AiSuggestion::query()->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        $integrations = [
            'sentry' => filled(config('sentry.dsn')),
            'plausible' => filled(config('analytics.plausible.domain')),
            'google_analytics' => filled(config('analytics.google.measurement_id')),
        ];

        return view('analytics.dashboard', [
            'provider' => $provider,
            'stats' => $stats,
            'integrations' => $integrations,
            'refreshSeconds' => (int) config('analytics.dashboard.refresh_seconds', 60),
        ]);
    }
}
