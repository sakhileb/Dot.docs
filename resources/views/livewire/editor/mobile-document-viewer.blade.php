<div class="mobile-viewer bg-white dark:bg-gray-900 min-h-screen flex flex-col">
    <!-- Mobile Header -->
    <div class="sticky top-0 z-40 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-3">
        <div class="flex items-center justify-between gap-2">
            <div class="flex-1">
                <h1 class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ $document->title }}</h1>
                <p class="text-xs text-gray-500">By {{ $document->user->name }}</p>
            </div>
            <button wire:click="toggleTheme" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3v1m0 16v1m9-9h-1m-16 0H1m15.364 1.636l.707.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </button>
        </div>
    </div>

    <!-- Font Size Controls -->
    <div class="sticky top-12 z-30 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-2 flex items-center justify-center gap-3">
        <button wire:click="decreaseFontSize" class="p-2 rounded bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 13H5v-2h14v2z"/></svg>
        </button>
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 w-16 text-center">
            {{ $fontSize }}px
        </span>
        <button wire:click="increaseFontSize" class="p-2 rounded bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        </button>
    </div>

    <!-- Document Content -->
    <div class="flex-1 overflow-y-auto px-4 py-4" wire:ignore>
        <div class="prose dark:prose-invert max-w-none" @style("font-size: {$fontSize}px;")>
            {!! $currentPageData['content'] !!}
        </div>
    </div>

    <!-- Page Navigation -->
    <div class="sticky bottom-0 z-30 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-3">
        <div class="flex items-center justify-between gap-2 mb-2">
            <span class="text-xs text-gray-600 dark:text-gray-400">
                Page {{ $currentPageData['currentPage'] }} of {{ $currentPageData['totalPages'] }}
            </span>
            @if($currentPageData['totalLength'] > 0)
                <span class="text-xs text-gray-600 dark:text-gray-400">
                    {{ $currentPageData['endChar'] }}/{{ $currentPageData['totalLength'] }} chars
                </span>
            @endif
        </div>

        <div class="flex gap-2">
            <button wire:click="goPreviousPage" 
                @disabled(!$currentPageData['hasPreviousPage'])
                class="flex-1 p-3 rounded bg-gray-200 dark:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                <svg class="w-4 h-4 mx-auto" fill="currentColor" viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12l4.58-4.59z"/></svg>
            </button>

            @if($currentPageData['totalPages'] > 1)
                <select wire:change="goToPage($event.target.value)" 
                    class="flex-1 p-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                    @for($i = 1; $i <= $currentPageData['totalPages']; $i++)
                        <option value="{{ $i }}" @selected($i === $currentPageData['currentPage'])>
                            Page {{ $i }}
                        </option>
                    @endfor
                </select>
            @endif

            <button wire:click="goNextPage" 
                @disabled(!$currentPageData['hasNextPage'])
                class="flex-1 p-3 rounded bg-gray-200 dark:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                <svg class="w-4 h-4 mx-auto" fill="currentColor" viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div wire:loading.flex class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
        </div>
    </div>
</div>

<style>
    .touch-target {
        min-width: 44px;
        min-height: 44px;
    }

    @media (max-width: 768px) {
        .prose {
            font-size: 1rem;
        }
    }
</style>
