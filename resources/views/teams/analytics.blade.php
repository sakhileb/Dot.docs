<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="brand-section-title">Team Metrics</p>
                <h2 class="text-3xl font-semibold tracking-[-0.04em] text-slate-900 dark:text-white leading-tight">
                {{ $team->name }} - Analytics
                </h2>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('teams.dashboard', $team) }}" class="app-pill-button px-4 py-2 text-sm font-semibold">
                    Documents
                </a>
                <a href="{{ route('teams.activity', $team) }}" class="app-pill-button px-4 py-2 text-sm font-semibold">
                    Activity Feed
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:teams.team-analytics :team="$team" />
        </div>
    </div>
</x-app-layout>
