<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="brand-section-title">Workspace Settings</p>
            <h2 class="text-3xl font-semibold tracking-[-0.04em] text-slate-900 dark:text-white leading-tight">
                {{ __('Team Settings') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @livewire('teams.update-team-name-form', ['team' => $team])

            @livewire('teams.team-member-manager', ['team' => $team])

            @if (Gate::check('delete', $team) && ! $team->personal_team)
                <x-section-border />

                <div>
                    @livewire('teams.delete-team-form', ['team' => $team])
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
