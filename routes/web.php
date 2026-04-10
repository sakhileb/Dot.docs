<?php

use App\Http\Controllers\DocumentImageController;
use App\Livewire\Documents\DocumentSettings;
use App\Livewire\Documents\Editor;
use App\Livewire\Documents\Index;
use App\Livewire\Documents\ShareManager;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public shared document view
Route::get('/shared/{uuid}', function (string $uuid) {
    $document = \App\Models\Document::where('uuid', $uuid)
        ->where('is_public', true)
        ->firstOrFail();
    return view('documents.shared', compact('document'));
})->name('documents.shared');

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

    // Image uploads inside documents
    Route::post('/documents/{uuid}/images', [DocumentImageController::class, 'store'])
        ->name('documents.images.store');
});
