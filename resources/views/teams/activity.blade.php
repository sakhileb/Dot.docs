<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $team->name }} - Activity Feed
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('teams.dashboard', $team) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                    Documents
                </a>
                <a href="{{ route('teams.analytics', $team) }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    Analytics
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <livewire:teams.team-activity-feed :team="$team" />
            </div>
        </div>
    </div>
</x-app-layout>
