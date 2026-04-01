<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Version Timeline</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Document: {{ $document->title }} · Current v{{ $document->version }}</p>
            </div>
            <div class="flex items-center gap-2">
                <x-secondary-button wire:click="pruneAutoSaves">Run Autosave Cleanup</x-secondary-button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Version</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Summary/Notes</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">By</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">At</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($versions as $version)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 font-semibold">v{{ $version->version }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($version->is_milestone)
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">Milestone</span>
                                    @elseif ($version->is_auto_save)
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Autosave</span>
                                    @else
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Manual</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    <div>{{ $version->change_summary ?: ($version->milestone_name ?: '—') }}</div>
                                    @if ($version->version_notes)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $version->version_notes }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $version->user?->name ?: 'System' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ optional($version->created_at)->diffForHumans() }}</td>
                                <td class="px-4 py-3 text-sm text-right">
                                    <div class="inline-flex gap-2">
                                        <button wire:click="$set('leftVersionId', {{ $version->id }})" class="text-indigo-600 dark:text-indigo-400 hover:underline">Set Left</button>
                                        <button wire:click="$set('rightVersionId', {{ $version->id }})" class="text-indigo-600 dark:text-indigo-400 hover:underline">Set Right</button>
                                        <button wire:click="restoreVersion({{ $version->id }})" class="text-emerald-600 dark:text-emerald-400 hover:underline">Restore</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No versions yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $versions->links() }}</div>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5 space-y-4">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Version Comparison</h4>

            <div class="text-sm text-gray-600 dark:text-gray-300">
                Left: {{ $leftVersionId ?: 'not selected' }}<br>
                Right: {{ $rightVersionId ?: 'not selected' }}
            </div>

            <x-button wire:click="compareVersions" class="w-full justify-center">Compare Selected Versions</x-button>

            @if (! is_null($similarity))
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 bg-gray-50 dark:bg-gray-900/40">
                    <p class="text-sm text-gray-700 dark:text-gray-300">Similarity: <span class="font-semibold">{{ $similarity }}%</span></p>
                </div>

                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">{{ $leftTitle }}</p>
                        <textarea readonly rows="8" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-xs">{{ $leftPreview }}</textarea>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">{{ $rightTitle }}</p>
                        <textarea readonly rows="8" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-xs">{{ $rightPreview }}</textarea>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
