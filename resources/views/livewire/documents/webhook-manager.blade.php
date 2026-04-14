<div class="space-y-6">

    {{-- Section Header --}}
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Webhooks</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Receive an HTTP POST notification when this document is saved or exported.
        </p>
    </div>

    {{-- Existing Webhooks --}}
    @if($webhooks->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400 italic">No webhooks configured yet.</p>
    @else
        <div class="space-y-3">
            @foreach($webhooks as $webhook)
                <div class="flex items-start gap-4 p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $webhook->url }}</p>
                        <div class="flex flex-wrap gap-1 mt-1">
                            @foreach($webhook->events as $event)
                                <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 text-xs rounded">{{ $event }}</span>
                            @endforeach
                        </div>
                        @if($webhook->secret)
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 font-mono">Secret: {{ Str::limit($webhook->secret, 12) }}…</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        {{-- Toggle active --}}
                        <button wire:click="toggleWebhook({{ $webhook->id }})"
                                title="{{ $webhook->active ? 'Disable' : 'Enable' }}"
                                class="text-xs px-2 py-1 rounded transition {{ $webhook->active ? 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-200 text-gray-600 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-400' }}">
                            {{ $webhook->active ? 'Active' : 'Paused' }}
                        </button>
                        <button wire:click="deleteWebhook({{ $webhook->id }})"
                                wire:confirm="Delete this webhook?"
                                class="text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition p-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Add Webhook Form --}}
    <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 p-4 space-y-3">
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Add Webhook</h4>

        <div>
            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Endpoint URL</label>
            <input wire:model="newUrl"
                   type="url"
                   placeholder="https://example.com/webhook"
                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500" />
            @error('newUrl') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Trigger Events</label>
            <div class="flex gap-3">
                <label class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model="newEvents" value="on_save" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    On Save
                </label>
                <label class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model="newEvents" value="on_export" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    On Export
                </label>
            </div>
        </div>

        <label class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" wire:model="generateSecret" class="rounded border-gray-300 text-indigo-600" />
            Auto-generate HMAC signing secret
        </label>

        <button wire:click="addWebhook"
                wire:loading.attr="disabled"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg font-medium transition disabled:opacity-50">
            Add Webhook
        </button>
    </div>
</div>
