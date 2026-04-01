<?php

namespace App\Livewire\Teams;

use App\Models\ActivityLog;
use App\Models\Team;
use Livewire\Component;

class TeamActivityFeed extends Component
{
    public Team $team;

    public int $limit = 20;

    public function mount(Team $team): void
    {
        $this->team = $team;
        $this->authorize('view', $team);
    }

    public function loadMore(): void
    {
        $this->limit += 10;
    }

    public function render()
    {
        $activities = ActivityLog::whereHas('document', function ($q) {
            $q->where('team_id', $this->team->id);
        })
            ->with(['user', 'document'])
            ->latest()
            ->limit($this->limit)
            ->get();

        $groupedActivities = $activities->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        });

        return view('livewire.teams.activity-feed', [
            'groupedActivities' => $groupedActivities,
        ]);
    }
}
