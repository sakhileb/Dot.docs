<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="brand-section-title">Product Intelligence</p>
            <h2 class="text-3xl font-semibold tracking-[-0.04em] text-slate-900 dark:text-white leading-tight">
                Product Analytics Dashboard
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="app-card border border-white/65 dark:border-white/8 rounded-[2rem] p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm text-slate-500 dark:text-sky-50/60">Provider</p>
                        <p class="text-lg font-semibold text-slate-900 dark:text-white uppercase">{{ $provider }}</p>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-sky-50/60">Auto-refresh recommendation: {{ $refreshSeconds }}s</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
                <div class="app-card rounded-[1.25rem] border border-white/65 dark:border-white/8 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-sky-50/60">Total Documents</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['documents_total']) }}</p>
                </div>
                <div class="app-card rounded-[1.25rem] border border-white/65 dark:border-white/8 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-sky-50/60">Docs Last 7 Days</p>
                    <p class="mt-2 text-2xl font-bold text-sky-600 dark:text-sky-300">{{ number_format($stats['documents_last_7_days']) }}</p>
                </div>
                <div class="app-card rounded-[1.25rem] border border-white/65 dark:border-white/8 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-sky-50/60">Total Users</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['users_total']) }}</p>
                </div>
                <div class="app-card rounded-[1.25rem] border border-white/65 dark:border-white/8 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-sky-50/60">Active Users (7d)</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($stats['active_users_last_7_days']) }}</p>
                </div>
                <div class="app-card rounded-[1.25rem] border border-white/65 dark:border-white/8 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-sky-50/60">AI Requests (7d)</p>
                    <p class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($stats['ai_requests_last_7_days']) }}</p>
                </div>
            </div>

            <div class="app-card border border-white/65 dark:border-white/8 shadow-sm rounded-[2rem] p-5">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Integration Status</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="rounded-[1.25rem] border border-sky-100 dark:border-white/10 p-4 bg-sky-50/65 dark:bg-sky-500/8">
                        <p class="text-sm font-medium text-slate-700 dark:text-sky-50/75">Sentry</p>
                        <p class="mt-2 text-sm {{ $integrations['sentry'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $integrations['sentry'] ? 'Configured' : 'Not configured' }}
                        </p>
                    </div>
                    <div class="rounded-[1.25rem] border border-sky-100 dark:border-white/10 p-4 bg-sky-50/65 dark:bg-sky-500/8">
                        <p class="text-sm font-medium text-slate-700 dark:text-sky-50/75">Plausible</p>
                        <p class="mt-2 text-sm {{ $integrations['plausible'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $integrations['plausible'] ? 'Configured' : 'Not configured' }}
                        </p>
                    </div>
                    <div class="rounded-[1.25rem] border border-sky-100 dark:border-white/10 p-4 bg-sky-50/65 dark:bg-sky-500/8">
                        <p class="text-sm font-medium text-slate-700 dark:text-sky-50/75">Google Analytics</p>
                        <p class="mt-2 text-sm {{ $integrations['google_analytics'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $integrations['google_analytics'] ? 'Configured' : 'Not configured' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
