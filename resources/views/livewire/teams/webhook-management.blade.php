<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                            Automation Webhooks
                        </h3>
                        <button
                            wire:click="openCreateForm"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 dark:bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 dark:hover:bg-blue-600 active:bg-blue-800 dark:active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 transition ease-in-out duration-150"
                        >
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Create Webhook
                        </button>
                    </div>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Set up webhooks to automatically notify external services when documents are created, updated, or deleted.
                    </p>
                </div>

                <!-- Create/Edit Form -->
                @if ($showCreateForm)
                    <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
                        <form wire:submit="@if ($editingWebhookId) update @else create @endif">
                            <div class="space-y-6">
                                <!-- Name -->
                                <div>
                                    <label for="webhookName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Webhook Name
                                    </label>
                                    <input
                                        type="text"
                                        id="webhookName"
                                        wire:model="webhookName"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                        placeholder="e.g., Zapier Document Monitor"
                                    />
                                    @error('webhookName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Provider -->
                                <div>
                                    <label for="webhookProvider" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Provider
                                    </label>
                                    <select
                                        id="webhookProvider"
                                        wire:model="webhookProvider"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    >
                                        @foreach ($availableProviders as $provider)
                                            <option value="{{ $provider }}">
                                                {{ ucfirst($provider) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('webhookProvider') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Webhook URL -->
                                <div>
                                    <label for="webhookUrl" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Webhook URL
                                    </label>
                                    <input
                                        type="url"
                                        id="webhookUrl"
                                        wire:model="webhookUrl"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                        placeholder="https://hooks.zapier.com/hooks/catch/..."
                                    />
                                    @error('webhookUrl') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Secret -->
                                <div>
                                    <label for="webhookSecret" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Secret (Optional)
                                    </label>
                                    <input
                                        type="password"
                                        id="webhookSecret"
                                        wire:model="webhookSecret"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                        placeholder="Your webhook secret for HMAC signature verification"
                                    />
                                    @error('webhookSecret') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Events -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                        Subscribe to Events
                                    </label>
                                    <div class="space-y-3">
                                        @foreach ($availableEvents as $eventKey => $eventLabel)
                                            <div class="flex items-start">
                                                <input
                                                    type="checkbox"
                                                    id="event-{{ $eventKey }}"
                                                    value="{{ $eventKey }}"
                                                    wire:model="selectedEvents"
                                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                />
                                                <label for="event-{{ $eventKey }}" class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ $eventLabel }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('selectedEvents') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Actions -->
                                <div class="flex justify-end space-x-3">
                                    <button
                                        type="button"
                                        wire:click="closeForm"
                                        class="inline-flex items-center px-4 py-2 bg-gray-600 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 transition ease-in-out duration-150"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 dark:bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 dark:hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 transition ease-in-out duration-150"
                                    >
                                        @if ($editingWebhookId) Update Webhook @else Create Webhook @endif
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- Webhooks List -->
                <div class="border-t border-gray-200 dark:border-gray-700">
                    @foreach ($webhooks as $webhook)
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                            {{ $webhook->name }}
                                        </h4>
                                        <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full {{ $webhook->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                                            {{ $webhook->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ ucfirst($webhook->provider) }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 truncate">
                                        {{ $webhook->endpoint_url }}
                                    </p>
                                    <div class="mt-2 space-y-1 text-sm">
                                        <p class="text-gray-700 dark:text-gray-300">
                                            <span class="font-medium">Events subscribed:</span>
                                            {{ implode(', ', array_map(fn($e) => $availableEvents[$e] ?? $e, $webhook->subscribed_events ?? [])) }}
                                        </p>
                                        @if ($webhook->last_triggered_at)
                                            <p class="text-gray-600 dark:text-gray-400">
                                                <span class="font-medium">Last triggered:</span>
                                                {{ $webhook->last_triggered_at->diffForHumans() }}
                                                @if ($webhook->last_response_status)
                                                    <span class="text-xs text-gray-500">({{ $webhook->last_response_status }})</span>
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="ml-4 flex space-x-2">
                                    <button
                                        wire:click="testWebhook({{ $webhook->id }})"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                    >
                                        Test
                                    </button>
                                    <button
                                        wire:click="toggleActive({{ $webhook->id }})"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium {{ $webhook->is_active ? 'text-yellow-600 hover:text-yellow-700 dark:text-yellow-400' : 'text-green-600 hover:text-green-700 dark:text-green-400' }}"
                                    >
                                        {{ $webhook->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                    <button
                                        wire:click="editWebhook({{ $webhook->id }})"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        wire:click="delete({{ $webhook->id }})"
                                        wire:confirm="Are you sure you want to delete this webhook?"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if ($webhooks->isEmpty())
                        <div class="px-4 py-12 sm:px-6 text-center">
                            <p class="text-gray-500 dark:text-gray-400">
                                No webhooks configured yet. Create your first webhook to get started.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Pagination -->
                @if ($webhooks->hasPages())
                    <div class="px-4 py-4 sm:px-6 border-t border-gray-200 dark:border-gray-700">
                        {{ $webhooks->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
