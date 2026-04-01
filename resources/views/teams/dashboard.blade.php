<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $team->name }} - Documents
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('teams.activity', $team) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                    Activity Feed
                </a>
                <a href="{{ route('teams.analytics', $team) }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                    Analytics
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:teams.team-document-dashboard :team="$team" />
        </div>
    </div>
</x-app-layout>
