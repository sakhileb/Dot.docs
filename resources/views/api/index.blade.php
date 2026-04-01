<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="brand-section-title">Developer Settings</p>
            <h2 class="text-3xl font-semibold tracking-[-0.04em] text-slate-900 dark:text-white leading-tight">
                {{ __('API Tokens') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @livewire('api.api-token-manager')
        </div>
    </div>
</x-app-layout>
