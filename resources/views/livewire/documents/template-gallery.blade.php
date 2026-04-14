<div>
    @if($show)
    {{-- Backdrop --}}
    <div class="fixed inset-0 z-40 bg-black/50" wire:click="close"></div>

    {{-- Modal --}}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-4xl max-h-[85vh] flex flex-col">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">📄 Template Gallery</h2>
                <button wire:click="close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Category Tabs --}}
            <div class="flex gap-1 px-6 pt-3 pb-0 flex-wrap">
                @foreach($this->categories as $cat)
                    <button wire:click="$set('activeCategory', '{{ $cat }}')"
                            class="px-3 py-1.5 rounded-full text-sm font-medium capitalize transition
                                {{ $activeCategory === $cat
                                    ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200'
                                    : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        {{ $cat }}
                    </button>
                @endforeach
            </div>

            {{-- Template Grid --}}
            <div class="flex-1 overflow-y-auto px-6 py-4">
                @if($this->templates->isEmpty())
                    <p class="text-center text-gray-500 dark:text-gray-400 py-12">No templates found for this category.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($this->templates as $template)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden hover:border-indigo-400 dark:hover:border-indigo-500 transition group">
                                {{-- Preview --}}
                                <div class="h-28 bg-gray-50 dark:bg-gray-900 overflow-hidden px-4 pt-3 text-xs text-gray-500 dark:text-gray-400 pointer-events-none select-none">
                                    {!! Str::limit(strip_tags($template->content), 250) !!}
                                </div>

                                {{-- Info --}}
                                <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $template->name }}</h3>
                                            @if($template->description)
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2">{{ $template->description }}</p>
                                            @endif
                                            <span class="inline-block mt-1.5 px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 text-xs rounded capitalize">{{ $template->category }}</span>
                                            @if($template->is_global)
                                                <span class="inline-block ml-1 px-2 py-0.5 bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-300 text-xs rounded">Built-in</span>
                                            @endif
                                        </div>
                                    </div>
                                    <button wire:click="useTemplate({{ $template->id }})"
                                            wire:loading.attr="disabled"
                                            class="mt-3 w-full px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition font-medium">
                                        Use Template
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
