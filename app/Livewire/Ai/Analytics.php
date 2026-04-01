<?php

namespace App\Livewire\Ai;

use App\Models\AiSuggestion;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Analytics extends Component
{
    public int $rangeDays = 7;

    public function render()
    {
        $from = now()->subDays($this->rangeDays);

        $base = AiSuggestion::query()->where('created_at', '>=', $from);

        $total = (clone $base)->count();
        $completed = (clone $base)->where('status', 'completed')->count();
        $failed = (clone $base)->where('status', 'failed')->count();
        $cached = (clone $base)->where('is_cached', true)->count();
        $tokenUsage = (int) ((clone $base)->sum('token_usage') ?? 0);
        $avgTokens = $total > 0 ? (int) round($tokenUsage / $total) : 0;

        $byOperation = AiSuggestion::query()
            ->select('operation', DB::raw('count(*) as total'))
            ->where('created_at', '>=', $from)
            ->groupBy('operation')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $dailyUsage = AiSuggestion::query()
            ->selectRaw("date(created_at) as day, count(*) as total, coalesce(sum(token_usage), 0) as tokens")
            ->where('created_at', '>=', $from)
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $recentFailures = AiSuggestion::query()
            ->with('user')
            ->where('status', 'failed')
            ->latest()
            ->limit(8)
            ->get();

        return view('livewire.ai.analytics', [
            'total' => $total,
            'completed' => $completed,
            'failed' => $failed,
            'cached' => $cached,
            'tokenUsage' => $tokenUsage,
            'avgTokens' => $avgTokens,
            'successRate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            'cacheHitRate' => $total > 0 ? round(($cached / $total) * 100, 1) : 0,
            'byOperation' => $byOperation,
            'dailyUsage' => $dailyUsage,
            'recentFailures' => $recentFailures,
        ]);
    }
}
