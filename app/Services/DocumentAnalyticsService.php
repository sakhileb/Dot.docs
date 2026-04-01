<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Collection;

class DocumentAnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function analyze(Document $document): array
    {
        $activities = $document->activityLogs()
            ->orderBy('created_at')
            ->get(['user_id', 'action', 'created_at']);

        return [
            'total_edits' => $activities->where('action', 'edit')->count(),
            'total_comments' => $activities->where('action', 'comment')->count(),
            'unique_contributors' => $activities->pluck('user_id')->filter()->unique()->count(),
            'estimated_time_spent_minutes' => $this->estimateActiveMinutes($activities),
            'recent_activity_at' => $activities->last()?->created_at,
        ];
    }

    private function estimateActiveMinutes(Collection $activities): int
    {
        if ($activities->isEmpty()) {
            return 0;
        }

        $thresholdMinutes = 10;
        $total = 0;

        $grouped = $activities
            ->filter(fn ($row) => $row->user_id !== null)
            ->groupBy('user_id');

        foreach ($grouped as $userActivities) {
            $sorted = $userActivities->sortBy('created_at')->values();

            if ($sorted->isEmpty()) {
                continue;
            }

            // Count one minute for each active session start.
            $total += 1;

            for ($index = 1; $index < $sorted->count(); $index++) {
                $previous = $sorted[$index - 1]->created_at;
                $current = $sorted[$index]->created_at;

                if (! $previous || ! $current) {
                    continue;
                }

                $delta = $previous->diffInMinutes($current);

                if ($delta <= $thresholdMinutes) {
                    $total += $delta;
                } else {
                    $total += 1;
                }
            }
        }

        return max($total, 0);
    }
}
