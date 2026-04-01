<?php

namespace App\Jobs;

use App\Models\AiSuggestion;
use App\Services\AiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessAiSuggestionJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function backoff(): array
    {
        return [10, 30, 90];
    }

    /**
     * Create a new job instance.
     */
    public function __construct(public int $suggestionId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $suggestion = AiSuggestion::query()->find($this->suggestionId);

        if (! $suggestion) {
            return;
        }

        try {
            $service = new AiService();
            $service->processQueuedSuggestion($suggestion);
        } catch (\Throwable $e) {
            Log::error('Queued AI suggestion failed', [
                'suggestion_id' => $this->suggestionId,
                'error' => $e->getMessage(),
            ]);

            $suggestion->update([
                'status' => 'failed',
                'progress' => 100,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
                'processed_at' => now(),
            ]);

            throw $e;
        }
    }
}
