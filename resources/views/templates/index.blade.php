<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="brand-section-title">Content</p>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-sky-50 leading-tight">
                {{ __('Template Library') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:templates.library />
        </div>
    </div>
</x-app-layout>
