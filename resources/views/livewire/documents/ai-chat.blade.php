<div>
    {{-- Chat toggle button --}}
    <button wire:click="toggle"
            class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-lg flex items-center justify-center text-xl"
            title="AI Chat">
        @if($open) ✕ @else ✨ @endif
    </button>

    {{-- Chat panel --}}
    @if($open)
        <div class="fixed bottom-20 right-6 z-50 w-80 flex flex-col bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700"
             style="height: 420px;">

            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <span class="text-indigo-500">✨</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">AI Assistant</span>
                </div>
                <button wire:click="clearHistory" class="text-xs text-gray-400 hover:text-gray-600">Clear</button>
            </div>

            {{-- Messages --}}
            <div class="flex-1 overflow-y-auto p-3 space-y-3"
                 x-data x-ref="messages"
                 x-init="new MutationObserver(() => { $refs.messages.scrollTop = $refs.messages.scrollHeight; }).observe($refs.messages, { childList: true, subtree: true })">
                @if(empty($history))
                    <div class="text-xs text-gray-400 text-center mt-6">
                        Ask me anything about this document.
                    </div>
                @endif
                @foreach($history as $turn)
                    <div class="flex {{ $turn['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] rounded-lg px-3 py-2 text-xs
                            {{ $turn['role'] === 'user'
                                ? 'bg-indigo-600 text-white'
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200' }}">
                            {!! nl2br(e($turn['content'])) !!}
                        </div>
                    </div>
                @endforeach
                @if($loading)
                    <div class="flex justify-start">
                        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg px-3 py-2">
                            <span class="flex gap-1">
                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Input --}}
            <div class="px-3 py-2 border-t border-gray-200 dark:border-gray-700">
                <div class="flex gap-2">
                    <input wire:model="message"
                           wire:keydown.enter="send"
                           type="text"
                           placeholder="Ask something…"
                           class="flex-1 text-xs border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-1 focus:ring-indigo-500 focus:outline-none" />
                    <button wire:click="send"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-lg text-xs">
                        Send
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
