<div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Documents</h1>
        <div class="flex items-center gap-2">
            <button @click="$dispatch('open-template-gallery')"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                📄 From Template
            </button>
            <button wire:click="$set('showCreateModal', true)"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                + New Document
            </button>
        </div>
    </div>

    {{-- Template Gallery --}}
    @livewire('documents.template-gallery', key('template-gallery'))

    {{-- Search & Filter --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <input wire:model.live.debounce.300ms="search"
               type="search"
               placeholder="Search documents…"
               class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" />

        <div class="flex gap-2">
            @foreach(['all' => 'All', 'mine' => 'Mine', 'shared' => 'Shared', 'team' => 'Team'] as $key => $label)
                <button wire:click="$set('filter', '{{ $key }}')"
                        class="px-3 py-1.5 text-xs font-medium rounded-full transition
                               {{ $filter === $key ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Document Grid --}}
    @if($this->documents->isEmpty())
        <div class="text-center py-20 text-gray-500 dark:text-gray-400">
            <svg class="mx-auto h-12 w-12 mb-4 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm">No documents found. Create your first one!</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($this->documents as $doc)
                <a href="{{ route('documents.edit', $doc->uuid) }}"
                   class="group block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-md hover:border-indigo-400 transition">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                            </svg>
                        </div>
                        @if($doc->is_public)
                            <span class="text-xs text-green-600 dark:text-green-400 font-medium">Public</span>
                        @endif
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm truncate group-hover:text-indigo-600 transition">
                        {{ $doc->title }}
                    </h3>
                    <p class="text-xs text-gray-400 mt-1">{{ $doc->updated_at->diffForHumans() }}</p>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            @if($this->documents->hasMorePages())
                {{-- Infinite scroll sentinel --}}
                <div
                    x-data
                    x-init="
                        const obs = new IntersectionObserver(entries => {
                            if (entries[0].isIntersecting) $wire.loadMore();
                        }, { rootMargin: '300px' });
                        obs.observe($el);
                        $wire.$cleanup(() => obs.disconnect());
                    "
                    class="h-4"
                ></div>
                <div wire:loading wire:target="loadMore"
                     class="py-6 text-center text-sm text-gray-400 animate-pulse">
                    Loading more…
                </div>
            @else
                <p class="text-center text-xs text-gray-400 py-4">All documents loaded</p>
            @endif
        </div>
    @endif

    {{-- Open gallery via window event --}}
    <div x-data @open-template-gallery.window="Livewire.dispatchTo('documents.template-gallery', 'open')" class="hidden"></div>

    {{-- Create Modal --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="$set('showCreateModal', false)">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 w-full max-w-md mx-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">New Document</h2>

                <form wire:submit="createDocument">
                    <input wire:model="newTitle"
                           type="text"
                           placeholder="Document title…"
                           autofocus
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500 mb-4" />
                    @error('newTitle')
                        <p class="text-red-500 text-xs mb-3">{{ $message }}</p>
                    @enderror

                    <div class="flex justify-end gap-3">
                        <button type="button" wire:click="$set('showCreateModal', false)"
                                class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:text-gray-900 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
