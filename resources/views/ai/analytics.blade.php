<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="brand-section-title">Intelligence Metrics</p>
            <h2 class="text-3xl font-semibold tracking-[-0.04em] text-slate-900 dark:text-white leading-tight">
                {{ __('AI Analytics') }}
            </h2>
            <p class="max-w-2xl text-sm leading-7 text-slate-600 dark:text-sky-50/68">Monitor model usage, reliability, token volume, and operation trends in one branded dashboard.</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:ai.analytics />
        </div>
    </div>
</x-app-layout>
