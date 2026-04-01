<?php

use App\Models\Document;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnalyticsDashboardController;
use App\Http\Controllers\PrivacyController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/shared/{token}', function (string $token) {
    return view('documents.shared', compact('token'));
})->name('shares.public');

// Privacy / GDPR routes
Route::get('/privacy', [PrivacyController::class, 'index'])->name('privacy.index');
Route::middleware(['auth'])->group(function () {
    Route::get('/privacy/rights', [PrivacyController::class, 'rights'])->name('privacy.rights');
    Route::post('/privacy/export', [PrivacyController::class, 'requestExport'])->name('privacy.export');
    Route::get('/privacy/export/download', [PrivacyController::class, 'downloadExport'])->name('privacy.export.download');
});

// PWA routes
Route::get('/manifest.json', [App\Http\Controllers\PWAController::class, 'manifestJson'])->name('pwa.manifest');
Route::get('/service-worker.js', [App\Http\Controllers\PWAController::class, 'serviceWorker'])->name('pwa.service-worker');

// Offline sync routes
Route::post('/api/documents/{document}/sync', [App\Http\Controllers\OfflineSyncController::class, 'syncDocument'])->name('api.documents.sync');
Route::get('/api/offline/pending', [App\Http\Controllers\OfflineSyncController::class, 'getPendingSyncs'])->name('api.offline.pending');
Route::get('/api/documents/{document}/sync-status', [App\Http\Controllers\OfflineSyncController::class, 'getSyncStatus'])->name('api.documents.sync-status');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'throttle:sensitive-actions',
    'audit.sensitive',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/my-dashboard', function () {
        return view('dashboard.personal');
    })->name('dashboard.personal');

    Route::get('/documents', function () {
        return view('documents.index');
    })->name('documents.index');

    Route::get('/documents/search', function () {
        return view('documents.search');
    })->name('documents.search');

    Route::get('/documents/generate', function () {
        return view('documents.generate');
    })->name('documents.generate');

    Route::get('/templates', function () {
        return view('templates.index');
    })->name('templates.index');

    Route::get('/documents/transfer', function () {
        return view('documents.transfer');
    })->name('documents.transfer');

    Route::get('/ai/analytics', function () {
        return view('ai.analytics');
    })->name('ai.analytics');

    Route::get('/analytics/dashboard', [AnalyticsDashboardController::class, 'index'])
        ->name('analytics.dashboard');

    Route::get('/documents/{document}', function (Document $document) {
        return view('documents.edit', compact('document'));
    })->name('documents.edit');

    Route::get('/documents/{document}/versions', function (Document $document) {
        return view('documents.versions', compact('document'));
    })->name('documents.versions');

    Route::get('/documents/{document}/reviews', function (Document $document) {
        return view('documents.reviews', compact('document'));
    })->name('documents.reviews');

    Route::get('/documents/{document}/share', function (Document $document) {
        return view('documents.share', compact('document'));
    })->name('documents.share');

    Route::get('/documents/{document}/citations', function (Document $document) {
        return view('documents.citations', compact('document'));
    })->name('documents.citations');

    Route::get('/documents/{document}/mail-merge', function (Document $document) {
        return view('documents.mail-merge', compact('document'));
    })->name('documents.mail-merge');

    Route::get('/documents/{document}/form-builder', function (Document $document) {
        return view('documents.form-builder', compact('document'));
    })->name('documents.form-builder');

    Route::get('/profile/notifications', function () {
        return view('profile.notifications');
    })->name('profile.notifications');

    Route::middleware('auth')->group(function () {
        Route::get('/teams/{team}/dashboard', function (\App\Models\Team $team) {
            return view('teams.dashboard', compact('team'));
        })->name('teams.dashboard');

        Route::get('/teams/{team}/analytics', function (\App\Models\Team $team) {
            return view('teams.analytics', compact('team'));
        })->name('teams.analytics');

        Route::get('/teams/{team}/activity', function (\App\Models\Team $team) {
            return view('teams.activity', compact('team'));
        })->name('teams.activity');

        Route::get('/teams/{team}/webhooks', function (\App\Models\Team $team) {
            return view('teams.webhooks', compact('team'));
        })->name('teams.webhooks');
    });
});
