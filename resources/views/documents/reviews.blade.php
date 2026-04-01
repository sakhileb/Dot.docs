<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="brand-section-title">Collaboration</p>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-sky-50 leading-tight">
                    {{ __('Comments & Reviews') }}
                </h2>
            </div>
            <a href="{{ route('documents.edit', $document) }}" class="app-pill-button">
                &larr; Back to Editor
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:documents.reviews :document="$document" />
        </div>
    </div>
</x-app-layout>
