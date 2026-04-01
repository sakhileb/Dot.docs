<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="brand-section-title">Exchange Hub</p>
            <h2 class="text-3xl font-semibold tracking-[-0.04em] text-slate-900 dark:text-white leading-tight">
                {{ __('Document Transfer') }}
            </h2>
            <p class="max-w-2xl text-sm leading-7 text-slate-600 dark:text-sky-50/68">Move content in and out of Dot.docs with local files, Google Docs, and cloud storage providers.</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:documents.transfer />
        </div>
    </div>
</x-app-layout>
