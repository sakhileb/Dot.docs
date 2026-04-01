<div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Usage Stats -->
        <div class="app-card rounded-[1.5rem] border border-white/65 dark:border-white/8 p-6">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Total Documents</h3>
            <div class="text-4xl font-bold text-sky-600 dark:text-sky-300">{{ $analyticsData['total_documents'] }}</div>
        </div>

        <div class="app-card rounded-[1.5rem] border border-white/65 dark:border-white/8 p-6">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Team Members</h3>
            <div class="text-4xl font-bold text-amber-600 dark:text-amber-300">{{ $analyticsData['total_users'] }}</div>
        </div>

        <div class="app-card rounded-[1.5rem] border border-white/65 dark:border-white/8 p-6">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Activity</h3>
            <div class="space-y-1 text-sm">
                <p><span class="text-slate-600 dark:text-sky-50/62">Comments:</span> <span class="font-semibold">{{ $analyticsData['total_comments'] }}</span></p>
                <p><span class="text-slate-600 dark:text-sky-50/62">Shares:</span> <span class="font-semibold">{{ $analyticsData['total_shares'] }}</span></p>
                <p><span class="text-slate-600 dark:text-sky-50/62">Reviews:</span> <span class="font-semibold">{{ $analyticsData['total_reviews'] }}</span></p>
            </div>
        </div>
    </div>

    <!-- Storage Quota -->
    <div class="app-card rounded-[1.5rem] border border-white/65 dark:border-white/8 p-6 mb-6">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Storage Usage</h3>
        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-slate-600 dark:text-sky-50/62">{{ $analyticsData['storage_used'] }} used of {{ $analyticsData['storage_limit'] }}</span>
                <span class="font-semibold">{{ number_format($analyticsData['storage_used_percentage'], 1) }}%</span>
            </div>
            <div class="w-full bg-sky-100 dark:bg-white/10 rounded-full h-4 overflow-hidden">
                @php $storageWidth = min($analyticsData['storage_used_percentage'], 100); @endphp
                <div 
                    class="h-full bg-gradient-to-r from-sky-500 to-cyan-500 transition-all duration-300"
                    @style("width: {$storageWidth}%;")
                ></div>
            </div>
            <p class="text-xs text-slate-500 dark:text-sky-50/58">{{ $analyticsData['storage_available'] }} available</p>
        </div>
    </div>

    <!-- Activity Trend Chart (simple bar chart) -->
    <div class="app-card rounded-[1.5rem] border border-white/65 dark:border-white/8 p-6">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Activity Trend (Last 30 Days)</h3>
        <div class="flex items-end gap-2 h-32">
            @php
                $maxCount = $activityTrend->max('count') ?: 1;
            @endphp
            @foreach ($activityTrend as $day)
                <div class="flex-1 flex flex-col items-center gap-2">
                    @php $barHeight = ($day->count / $maxCount * 100); @endphp
                    <div class="w-full bg-sky-500 dark:bg-sky-500 rounded-t transition-all hover:bg-sky-600 dark:hover:bg-sky-400" 
                        @style("height: {$barHeight}%;")
                        title="{{ $day->count }} activities on {{ \Carbon\Carbon::parse($day->date)->format('M d') }}"
                    ></div>
                    <span class="text-xs text-slate-500 dark:text-sky-50/58">{{ \Carbon\Carbon::parse($day->date)->format('M d') }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
