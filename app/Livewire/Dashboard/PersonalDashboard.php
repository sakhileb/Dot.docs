<?php

namespace App\Livewire\Dashboard;

use App\Models\Document;
use App\Models\DocumentFavorite;
use App\Models\UserFolder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PersonalDashboard extends Component
{
    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $currentTeam = $user->currentTeam;

        $recentDocuments = $user->documents()
            ->where('team_id', $currentTeam->id)
            ->latest('updated_at')
            ->limit(6)
            ->get();

        $favoriteDocuments = DocumentFavorite::where('user_id', $user->id)
            ->where('team_id', $currentTeam->id)
            ->with('document')
            ->latest('created_at')
            ->limit(6)
            ->get()
            ->pluck('document');

        $folders = UserFolder::where('user_id', $user->id)
            ->where('team_id', $currentTeam->id)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        $stats = [
            'total_documents' => $user->documents()->where('team_id', $currentTeam->id)->count(),
            'recent_count' => $user->documents()
                ->where('team_id', $currentTeam->id)
                ->where('updated_at', '>=', now()->subDays(7))
                ->count(),
            'favorites_count' => DocumentFavorite::where('user_id', $user->id)
                ->where('team_id', $currentTeam->id)
                ->count(),
            'folders_count' => UserFolder::where('user_id', $user->id)
                ->where('team_id', $currentTeam->id)
                ->whereNull('parent_id')
                ->count(),
        ];

        return view('livewire.dashboard.personal-dashboard', [
            'recentDocuments' => $recentDocuments,
            'favoriteDocuments' => $favoriteDocuments,
            'folders' => $folders,
            'stats' => $stats,
        ]);
    }
}
