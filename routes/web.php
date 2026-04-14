<?php

use App\Http\Controllers\DocumentExportController;
use App\Http\Controllers\DocumentImageController;
use App\Http\Controllers\DocumentImportController;
use App\Livewire\Documents\DocumentSettings;
use App\Livewire\Documents\Editor;
use App\Livewire\Documents\Index;
use App\Livewire\Documents\ShareManager;
use App\Livewire\Documents\SlashCommandManager;
use App\Livewire\Documents\VersionHistory;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public shared document view (with optional password & expiry enforcement)
Route::get('/shared/{uuid}', function (string $uuid) {
    $document = \App\Models\Document::where('uuid', $uuid)
        ->where('is_public', true)
        ->firstOrFail();

    // Check expiry
    if ($document->share_expires_at && $document->share_expires_at->isPast()) {
        abort(410, 'This share link has expired.');
    }

    // Check password
    if ($document->share_password) {
        return view('documents.shared-password', compact('document'));
    }

    return view('documents.shared', compact('document'));
})->name('documents.shared');

Route::post('/shared/{uuid}', function (string $uuid, \Illuminate\Http\Request $request) {
    $document = \App\Models\Document::where('uuid', $uuid)
        ->where('is_public', true)
        ->firstOrFail();

    if ($document->share_expires_at && $document->share_expires_at->isPast()) {
        abort(410, 'This share link has expired.');
    }

    $request->validate(['password' => 'required|string']);

    if (! \Illuminate\Support\Facades\Hash::check($request->password, $document->share_password)) {
        return back()->withErrors(['password' => 'Incorrect password.']);
    }

    return view('documents.shared', compact('document'));
})->name('documents.shared.unlock');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Documents
    Route::get('/documents', Index::class)->name('documents.index');
    Route::get('/documents/{uuid}/edit', Editor::class)->name('documents.edit');
    Route::get('/documents/{uuid}/settings', DocumentSettings::class)->name('documents.settings');
    Route::get('/documents/{uuid}/share', ShareManager::class)->name('documents.share');
    Route::get('/documents/{uuid}/history', VersionHistory::class)->name('documents.history');

    // Slash commands (user-level, not per-document)
    Route::get('/settings/slash-commands', SlashCommandManager::class)->name('slash-commands.index');

    // Image uploads inside documents
    Route::post('/documents/{uuid}/images', [DocumentImageController::class, 'store'])
        ->name('documents.images.store');

    // Export
    Route::get('/documents/{uuid}/export/{format}', [DocumentExportController::class, 'export'])
        ->where('format', 'pdf|word|html|markdown')
        ->name('documents.export');

    // Import
    Route::post('/documents/{uuid}/import', [DocumentImportController::class, 'store'])
        ->name('documents.import');
});
