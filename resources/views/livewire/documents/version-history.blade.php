<div class="flex h-screen bg-gray-50 dark:bg-gray-900 overflow-hidden">

    {{-- LEFT: Version list --}}
    <aside class="w-80 flex-shrink-0 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Version History</h2>
                <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $document->title }}</p>
            </div>
            <a href="{{ route('documents.edit', $document->uuid) }}"
               class="text-xs text-indigo-600 hover:underline">← Editor</a>
        </div>

        @if(session('status'))
            <div class="mx-4 mt-3 p-2 bg-green-50 border border-green-200 rounded text-xs text-green-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- Compare bar --}}
        @if(count($compareIds) > 0)
            <div class="px-4 py-2 bg-amber-50 dark:bg-amber-900/30 border-b border-amber-200 dark:border-amber-700 flex items-center gap-2">
                <span class="text-xs text-amber-700 dark:text-amber-300 flex-1">
                    {{ count($compareIds) }}/2 selected for compare
                </span>
                @if(count($compareIds) === 2)
                    <button wire:click="runDiff"
                            class="text-xs bg-amber-500 hover:bg-amber-600 text-white px-2 py-1 rounded">
                        Compare
                    </button>
                @endif
                <button wire:click="$set('compareIds', [])" class="text-xs text-amber-600 hover:underline">Clear</button>
            </div>
        @endif

        <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($versions as $version)
                <div class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer
                    {{ $previewId === $version->id ? 'bg-indigo-50 dark:bg-indigo-900/30' : '' }}
                    {{ in_array($version->id, $compareIds) ? 'bg-amber-50 dark:bg-amber-900/20' : '' }}"
                >
                    <div class="flex items-start gap-2">
                        {{-- Compare checkbox --}}
                        <input type="checkbox"
                               wire:click="toggleCompare({{ $version->id }})"
                               @checked(in_array($version->id, $compareIds))
                               class="mt-1 rounded border-gray-300 text-amber-500 focus:ring-amber-400" />

                        <div class="flex-1 min-w-0" wire:click="preview({{ $version->id }})">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                    v{{ $version->version_number }}
                                </span>
                                @if($version->version_number === $document->version)
                                    <span class="text-[10px] bg-green-100 text-green-700 px-1 rounded">current</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $version->created_at->diffForHumans() }}
                            </p>
                            @if($version->author)
                                <p class="text-xs text-gray-400 truncate">by {{ $version->author->name }}</p>
                            @endif
                        </div>

                        {{-- Restore button (not for current) --}}
                        @if($version->version_number !== $document->version)
                            <button wire:click="restore({{ $version->id }})"
                                    wire:confirm="Restore document to v{{ $version->version_number }}? Current content will be saved as a new version."
                                    class="text-[11px] text-indigo-600 hover:text-indigo-800 hover:underline whitespace-nowrap">
                                Restore
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-sm text-gray-400">No versions yet.</div>
            @endforelse
        </div>

        <div class="p-3 border-t border-gray-200 dark:border-gray-700">
            {{ $versions->links() }}
        </div>
    </aside>

    {{-- RIGHT: Preview or Diff --}}
    <main class="flex-1 overflow-auto">
        @if($showDiff && $diffHtml)
            <div class="p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">
                    Diff comparison
                </h3>
                <div class="diff-wrapper overflow-x-auto rounded border border-gray-200 dark:border-gray-700 text-xs font-mono">
                    {!! $diffHtml !!}
                </div>
            </div>

        @elseif($previewVersion)
            <div class="max-w-3xl mx-auto py-10 px-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <span class="text-xs font-mono font-bold text-indigo-600">v{{ $previewVersion->version_number }}</span>
                        <span class="text-xs text-gray-500 ml-2">
                            {{ $previewVersion->created_at->format('M j, Y H:i') }}
                            @if($previewVersion->author) · {{ $previewVersion->author->name }} @endif
                        </span>
                    </div>
                    @if($previewVersion->version_number !== $document->version)
                        <button wire:click="restore({{ $previewVersion->id }})"
                                wire:confirm="Restore document to v{{ $previewVersion->version_number }}?"
                                class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded">
                            Restore this version
                        </button>
                    @endif
                </div>
                <article class="prose prose-sm dark:prose-invert max-w-none bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                    {!! $previewVersion->content_snapshot !!}
                </article>
            </div>

        @else
            <div class="flex items-center justify-center h-full text-gray-400 text-sm">
                Select a version to preview, or check two versions to compare.
            </div>
        @endif
    </main>

</div>

@push('styles')
<style>
.diff-wrapper table { width: 100%; border-collapse: collapse; }
.diff-wrapper td, .diff-wrapper th { padding: 2px 8px; vertical-align: top; white-space: pre-wrap; word-break: break-word; }
.diff-wrapper .old { background: #fef2f2; }
.diff-wrapper .new { background: #f0fdf4; }
.diff-wrapper .replaced { background: #fffbeb; }
.diff-wrapper .header { background: #f3f4f6; color: #6b7280; font-size: 11px; }
.diff-wrapper ins { background: #bbf7d0; text-decoration: none; }
.diff-wrapper del { background: #fecaca; text-decoration: line-through; }
</style>
@endpush
