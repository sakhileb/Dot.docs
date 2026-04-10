<div>
    {{-- ===== COMMAND PALETTE MODAL ===== --}}
    @if($showPalette)
        <div class="fixed inset-0 z-50 flex items-start justify-center pt-24 px-4"
             x-data x-init="$el.querySelector('input').focus()">
            <div class="absolute inset-0 bg-black/50" wire:click="closePalette"></div>
            <div class="relative w-full max-w-xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                <div class="flex items-center border-b border-gray-200 dark:border-gray-700 px-4 py-3 gap-2">
                    <span class="text-indigo-500 text-lg">⌘</span>
                    <input wire:model="command"
                           wire:keydown.enter="runCommand"
                           wire:keydown.escape="closePalette"
                           type="text"
                           placeholder="Try /summarize, /grammar, /tone formal, /translate French…"
                           class="flex-1 bg-transparent border-none focus:ring-0 text-sm text-gray-900 dark:text-white placeholder-gray-400" />
                    @if($loading)
                        <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                    @else
                        <button wire:click="runCommand"
                                class="text-xs bg-indigo-600 text-white px-2 py-1 rounded hover:bg-indigo-700">Run</button>
                    @endif
                </div>

                {{-- Command suggestions --}}
                <ul class="divide-y divide-gray-100 dark:divide-gray-700 max-h-64 overflow-y-auto">
                    @foreach($commandSuggestions as $cmd => $desc)
                        <li wire:click="$set('command', '{{ $cmd }}')"
                            class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                            <code class="text-xs font-mono text-indigo-600 dark:text-indigo-400 w-36 shrink-0">{{ $cmd }}</code>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $desc }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- ===== RESULT PANEL (bottom slide-up) ===== --}}
    @if($showResult)
        <div class="fixed bottom-0 left-0 right-0 z-40 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-2xl"
             style="max-height: 40vh;">
            <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">AI Result — {{ ucfirst($action) }}</span>
                <div class="flex items-center gap-2">
                    <button wire:click="applyResult"
                            class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded">
                        Apply to document
                    </button>
                    <button wire:click="dismissResult"
                            class="text-xs text-gray-500 hover:underline">Dismiss</button>
                </div>
            </div>
            <div class="overflow-y-auto p-4 prose prose-sm dark:prose-invert max-w-none" style="max-height: calc(40vh - 44px);">
                {!! $result !!}
            </div>
        </div>
    @endif
</div>
