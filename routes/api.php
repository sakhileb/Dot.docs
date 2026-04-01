<?php

use App\Http\Controllers\Api\AutomationWebhookController;
use App\Http\Controllers\OfflineSyncController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Offline sync routes
Route::middleware(['auth:sanctum', 'throttle:sync-api', 'audit.sensitive'])->group(function () {
    Route::post('/documents/{documentId}/sync', [OfflineSyncController::class, 'syncDocument']);
    Route::get('/sync/pending', [OfflineSyncController::class, 'getPendingSyncs']);
    Route::get('/documents/{documentId}/sync-status', [OfflineSyncController::class, 'getSyncStatus']);
});

// Automation webhooks API
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/webhooks/documentation', [AutomationWebhookController::class, 'documentation']);
    Route::apiResource('teams.webhooks', AutomationWebhookController::class, [
        'parameters' => ['webhook' => 'webhookId'],
    ]);
    Route::post('/teams/{team}/webhooks/{webhookId}/test', [AutomationWebhookController::class, 'test']);
});
