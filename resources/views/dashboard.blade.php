<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="brand-section-title">Overview</p>
            <h2 class="text-3xl font-semibold tracking-[-0.04em] text-slate-900 dark:text-white leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <p class="max-w-2xl text-sm leading-7 text-slate-600 dark:text-sky-50/68">A branded control room for drafting, reviewing, automating, and shipping your documents.</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden">
                <x-welcome />
            </div>
        </div>
    </div>
</x-app-layout>
