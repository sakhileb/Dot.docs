<div class="space-y-6">
    <div class="app-card border border-white/65 dark:border-white/8 rounded-[2rem] p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">AI Usage Analytics</h3>
            <div class="flex items-center gap-2">
                <label for="rangeDays" class="text-sm text-slate-600 dark:text-sky-50/70">Range</label>
                <select id="rangeDays" wire:model.live="rangeDays" class="auth-input text-sm">
                    <option value="7">Last 7 days</option>
                    <option value="14">Last 14 days</option>
                    <option value="30">Last 30 days</option>
                </select>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
            <div class="rounded-[1.25rem] border border-sky-100 dark:border-white/10 p-4 bg-sky-50/65 dark:bg-sky-500/8">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-sky-50/60">Total Requests</p>
                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($total) }}</p>
            </div>
            <div class="rounded-[1.25rem] border border-sky-100 dark:border-white/10 p-4 bg-sky-50/65 dark:bg-sky-500/8">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-sky-50/60">Success Rate</p>
                <p class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $successRate }}%</p>
            </div>
            <div class="rounded-[1.25rem] border border-sky-100 dark:border-white/10 p-4 bg-sky-50/65 dark:bg-sky-500/8">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-sky-50/60">Cache Hit Rate</p>
                <p class="mt-2 text-2xl font-bold text-sky-600 dark:text-sky-300">{{ $cacheHitRate }}%</p>
            </div>
            <div class="rounded-[1.25rem] border border-sky-100 dark:border-white/10 p-4 bg-sky-50/65 dark:bg-sky-500/8">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-sky-50/60">Total Tokens</p>
                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($tokenUsage) }}</p>
                <p class="text-xs text-slate-500 dark:text-sky-50/60">Avg {{ number_format($avgTokens) }}/request</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="app-card border border-white/65 dark:border-white/8 rounded-[2rem] p-5">
            <h4 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Usage By Operation</h4>
            <div class="space-y-2">
                @forelse($byOperation as $row)
                    <div class="flex items-center justify-between rounded-xl bg-sky-50/75 dark:bg-sky-500/10 px-3 py-2">
                        <span class="text-sm text-slate-700 dark:text-sky-50/75">{{ $row->operation ?: 'unknown' }}</span>
                        <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $row->total }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-sky-50/60">No usage data yet.</p>
                @endforelse
            </div>
        </div>

        <div class="app-card border border-white/65 dark:border-white/8 rounded-[2rem] p-5">
            <h4 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Daily Volume</h4>
            <div class="space-y-2 max-h-[300px] overflow-y-auto">
                @forelse($dailyUsage as $row)
                    <div class="flex items-center justify-between rounded-xl bg-sky-50/75 dark:bg-sky-500/10 px-3 py-2">
                        <span class="text-sm text-slate-700 dark:text-sky-50/75">{{ $row->day }}</span>
                        <span class="text-sm text-slate-700 dark:text-sky-50/75">{{ $row->total }} requests</span>
                        <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ number_format($row->tokens) }} tokens</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-sky-50/60">No daily usage data yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="app-card border border-white/65 dark:border-white/8 rounded-[2rem] p-5">
        <h4 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Recent Failures</h4>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-sky-50/60 border-b border-sky-100 dark:border-white/10">
                        <th class="py-2 pr-4">When</th>
                        <th class="py-2 pr-4">User</th>
                        <th class="py-2 pr-4">Operation</th>
                        <th class="py-2 pr-4">Error</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentFailures as $failure)
                        <tr class="border-b border-sky-100 dark:border-white/10">
                            <td class="py-2 pr-4 text-slate-700 dark:text-sky-50/75">{{ optional($failure->created_at)->diffForHumans() }}</td>
                            <td class="py-2 pr-4 text-slate-700 dark:text-sky-50/75">{{ $failure->user?->name ?: 'Unknown' }}</td>
                            <td class="py-2 pr-4 text-slate-700 dark:text-sky-50/75">{{ $failure->operation ?: 'unknown' }}</td>
                            <td class="py-2 pr-4 text-red-600 dark:text-red-400">{{ \Illuminate\Support\Str::limit($failure->error_message, 90) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 text-slate-500 dark:text-sky-50/60">No recent failures.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
