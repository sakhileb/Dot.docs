<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TeamAnalytics extends Component
{
    public Team $team;

    public function mount(Team $team): void
    {
        $this->team = $team;
        $this->authorize('view', $team);
    }

    public function render()
    {
        $quota = $this->team->storageQuota;

        $analyticsData = [
            'total_documents' => $this->team->documents()->count(),
            'total_users' => $this->team->users()->count(),
            'total_comments' => DB::table('document_comments')
                ->whereIn('document_id', $this->team->documents()->pluck('id'))
                ->count(),
            'total_shares' => DB::table('document_shares')
                ->whereIn('document_id', $this->team->documents()->pluck('id'))
                ->count(),
            'total_reviews' => DB::table('document_reviews')
                ->whereIn('document_id', $this->team->documents()->pluck('id'))
                ->count(),
            'storage_used_percentage' => $quota->getUsagePercentage(),
            'storage_used' => $quota->formatBytes($quota->storage_used_bytes),
            'storage_limit' => $quota->formatBytes($quota->storage_limit_bytes),
            'storage_available' => $quota->formatBytes($quota->getAvailableBytes()),
        ];

        $activityTrend = DB::table('activity_logs')
            ->whereIn('document_id', $this->team->documents()->pluck('id'))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topUsers = DB::table('activity_logs')
            ->whereIn('document_id', $this->team->documents()->pluck('id'))
            ->select('user_id')
            ->selectRaw('COUNT(*) as activity_count')
            ->groupBy('user_id')
            ->orderByDesc('activity_count')
            ->limit(5)
            ->get();

        return view('livewire.teams.analytics', [
            'analyticsData' => $analyticsData,
            'activityTrend' => $activityTrend,
            'topUsers' => $topUsers,
        ]);
    }
}
