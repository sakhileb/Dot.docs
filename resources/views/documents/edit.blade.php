<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="brand-section-title">Draft Studio</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-[-0.04em] text-slate-900 dark:text-white leading-tight">
                {{ __('Editor') }}
                </h2>
            </div>

            <a href="{{ route('documents.index') }}" class="app-pill-button inline-flex items-center px-4 py-2.5 text-sm font-semibold">
                Back to Documents
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:documents.editor :document="$document" />
        </div>
    </div>
</x-app-layout>
