<div class="py-8 max-w-3xl mx-auto px-4">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('documents.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← Documents</a>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Custom Slash Commands</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">

    <div class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Define your own <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">/command</code> prompts that appear in the AI command palette.
                Use <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{content}</code> in the template to inject document text.
            </p>
        </div>
        <button wire:click="openCreate"
                class="shrink-0 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            + New Command
        </button>
    </div>

    {{-- Add / Edit Form --}}
    @if($showForm)
        <div class="rounded-lg border border-indigo-200 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20 p-4 space-y-3">
            <h4 class="text-sm font-medium text-indigo-700 dark:text-indigo-300">
                {{ $editingId ? 'Edit Command' : 'New Command' }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">
                        Command name <span class="text-gray-400">(no spaces, e.g. <em>bullet-list</em>)</span>
                    </label>
                    <div class="flex items-center">
                        <span class="px-2 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-l border border-r-0 border-gray-300 dark:border-gray-600 text-sm">/</span>
                        <input wire:model="name" type="text" placeholder="my-command"
                               class="flex-1 rounded-r-lg rounded-l-none border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Description <span class="text-gray-400">(shown in palette)</span></label>
                    <input wire:model="description" type="text" placeholder="What does this command do?"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500" />
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">
                    Prompt template
                    <span class="text-gray-400 ml-1">— use <code class="bg-gray-200 dark:bg-gray-700 px-0.5 rounded">{content}</code> to include document text</span>
                </label>
                <textarea wire:model="promptTemplate" rows="4"
                          placeholder="Rewrite the following as a numbered list, keeping each point concise:\n\n{content}"
                          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500 font-mono"></textarea>
                @error('promptTemplate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            @if(auth()->user()->currentTeam)
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input wire:model="shareWithTeam" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    Share with {{ auth()->user()->currentTeam->name }}
                </label>
            @endif

            <div class="flex gap-2">
                <button wire:click="save" wire:loading.attr="disabled"
                        class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg font-medium transition disabled:opacity-50">
                    {{ $editingId ? 'Update' : 'Create' }}
                </button>
                <button wire:click="$set('showForm', false)"
                        class="px-4 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">
                    Cancel
                </button>
            </div>
        </div>
    @endif

    {{-- Command List --}}
    @if($commands->isEmpty() && !$showForm)
        <p class="text-sm text-gray-500 dark:text-gray-400 italic py-4 text-center">
            No custom commands yet. Click "+ New Command" to create one.
        </p>
    @else
        <div class="space-y-2">
            @foreach($commands as $cmd)
                <div class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <code class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">/{{ $cmd->name }}</code>
                            @if($cmd->share_with_team)
                                <span class="px-1.5 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 text-xs rounded">Team</span>
                            @endif
                            @if($cmd->user_id !== auth()->id())
                                <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs rounded">Shared</span>
                            @endif
                        </div>
                        @if($cmd->description)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $cmd->description }}</p>
                        @endif
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 font-mono truncate">{{ Str::limit($cmd->prompt_template, 80) }}</p>
                    </div>
                    @if($cmd->user_id === auth()->id())
                        <div class="flex gap-1 shrink-0">
                            <button wire:click="editCommand({{ $cmd->id }})"
                                    class="p-1.5 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition"
                                    title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button wire:click="deleteCommand({{ $cmd->id }})"
                                    wire:confirm="Delete /{{ $cmd->name }}?"
                                    class="p-1.5 text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition"
                                    title="Delete">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>{{-- .space-y-4 --}}
    </div>{{-- card --}}
</div>{{-- page wrapper --}}
