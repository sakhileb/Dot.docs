<div>
    <div class="app-card rounded-[2rem] border border-white/65 p-6 mb-6 dark:border-white/8">
        <div class="mb-5 flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="brand-section-title">Document Control</p>
                <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-slate-900 dark:text-white">Manage drafts, reviews, and delivery</h3>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('documents.transfer') }}" class="app-pill-button px-4 py-2.5 text-sm font-semibold">Transfer</a>
                <a href="{{ route('templates.index') }}" class="app-pill-button px-4 py-2.5 text-sm font-semibold">Templates</a>
                <a href="{{ route('documents.generate') }}" class="app-pill-button px-4 py-2.5 text-sm font-semibold">AI Generator</a>
                <button wire:click="setViewMode('list')" class="px-4 py-2.5 text-sm font-semibold rounded-full border {{ $viewMode === 'list' ? 'bg-sky-600 text-white border-sky-600 shadow-[0_12px_24px_rgba(73,163,234,0.22)]' : 'app-pill-button' }}">List</button>
                <button wire:click="setViewMode('grid')" class="px-4 py-2.5 text-sm font-semibold rounded-full border {{ $viewMode === 'grid' ? 'bg-sky-600 text-white border-sky-600 shadow-[0_12px_24px_rgba(73,163,234,0.22)]' : 'app-pill-button' }}">Grid</button>
                <x-button wire:click="openCreateWizard('blank')">New Document</x-button>
            </div>
        </div>

        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3 w-full">
                <div class="xl:col-span-2">
                    <x-label for="search" value="Search" />
                    <x-input id="search" type="text" class="mt-1 block w-full" placeholder="Search title or content..." wire:model.live.debounce.300ms="search" />
                </div>

                <div>
                    <x-label for="teamFilter" value="Team" />
                    <select id="teamFilter" wire:model.live="teamFilter" class="auth-input mt-1 w-full focus:ring-0">
                        <option value="">All Teams</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-label for="statusFilter" value="Status" />
                    <select id="statusFilter" wire:model.live="statusFilter" class="auth-input mt-1 w-full focus:ring-0">
                        <option value="all">All</option>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </div>

                <div>
                    <x-label for="dateFilter" value="Date" />
                    <select id="dateFilter" wire:model.live="dateFilter" class="auth-input mt-1 w-full focus:ring-0">
                        <option value="all">Any time</option>
                        <option value="today">Today</option>
                        <option value="7d">Last 7 days</option>
                        <option value="30d">Last 30 days</option>
                    </select>
                </div>
            </div>

        </div>
    </div>

    @if ($showCreateWizard)
        <div class="app-card rounded-[2rem] border border-white/65 p-6 mb-6 dark:border-white/8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-slate-900 dark:text-white">Create Document</h3>
                <button wire:click="closeCreateWizard" class="text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-sky-50/60 dark:hover:text-white">Close</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                <button wire:click="openCreateWizard('blank')" class="px-4 py-4 rounded-[1.5rem] border text-left {{ $creationMode === 'blank' ? 'border-sky-300 bg-sky-50 dark:bg-sky-500/10 dark:border-sky-400' : 'border-slate-200/80 dark:border-white/10' }}">
                    <div class="font-semibold text-slate-900 dark:text-white">Blank</div>
                    <div class="text-sm text-slate-500 dark:text-sky-50/68">Start from scratch</div>
                </button>
                <button wire:click="openCreateWizard('template')" class="px-4 py-4 rounded-[1.5rem] border text-left {{ $creationMode === 'template' ? 'border-sky-300 bg-sky-50 dark:bg-sky-500/10 dark:border-sky-400' : 'border-slate-200/80 dark:border-white/10' }}">
                    <div class="font-semibold text-slate-900 dark:text-white">Template</div>
                    <div class="text-sm text-slate-500 dark:text-sky-50/68">Use a saved template</div>
                </button>
                <button wire:click="openCreateWizard('ai')" class="px-4 py-4 rounded-[1.5rem] border text-left {{ $creationMode === 'ai' ? 'border-sky-300 bg-sky-50 dark:bg-sky-500/10 dark:border-sky-400' : 'border-slate-200/80 dark:border-white/10' }}">
                    <div class="font-semibold text-slate-900 dark:text-white">AI</div>
                    <div class="text-sm text-slate-500 dark:text-sky-50/68">Generate from a prompt</div>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <x-label for="newTitle" value="Title" />
                    <x-input id="newTitle" type="text" class="mt-1 block w-full" wire:model="newTitle" placeholder="Optional title" />
                    <x-input-error for="newTitle" class="mt-1" />
                </div>

                @if ($creationMode === 'template')
                    <div>
                        <x-label for="templateId" value="Template" />
                        <select id="templateId" wire:model="templateId" class="auth-input mt-1 w-full focus:ring-0">
                            <option value="">Select a template</option>
                            @foreach ($templates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }} ({{ $template->category }})</option>
                            @endforeach
                        </select>
                        <x-input-error for="templateId" class="mt-1" />
                    </div>
                @endif

                @if ($creationMode === 'ai')
                    <div>
                        <x-label for="aiPrompt" value="AI Prompt" />
                        <textarea id="aiPrompt" wire:model="aiPrompt" rows="4" class="auth-input mt-1 w-full focus:ring-0" placeholder="Describe what you want to generate..."></textarea>
                        <x-input-error for="aiPrompt" class="mt-1" />
                    </div>
                @endif
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <x-secondary-button wire:click="closeCreateWizard">Cancel</x-secondary-button>
                <x-button wire:click="createDocument">Create</x-button>
            </div>
        </div>
    @endif

    @if ($viewMode === 'list')
        <div class="app-card overflow-hidden rounded-[2rem] border border-white/65 dark:border-white/8">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-slate-50/85 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Title</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Team</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Updated</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($documents as $document)
                            <tr>
                                <td class="px-4 py-4 text-sm text-slate-900 dark:text-white">
                                    <div class="font-medium">{{ $document->title }}</div>
                                    <div class="text-xs text-slate-500 dark:text-sky-50/52">v{{ $document->version }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-700 dark:text-sky-50/75">{{ $document->team?->name }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($document->trashed())
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">Deleted</span>
                                    @elseif ($document->is_archived)
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Archived</span>
                                    @else
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">{{ ucfirst($document->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-700 dark:text-sky-50/75">{{ optional($document->updated_at)->diffForHumans() }}</td>
                                <td class="px-4 py-4 text-sm text-right">
                                    <div class="inline-flex gap-2">
                                        @if (! $document->trashed())
                                            <a href="{{ route('documents.edit', $document) }}" class="text-sky-700 dark:text-sky-300 hover:underline">Edit</a>
                                            <a href="{{ route('documents.reviews', $document) }}" class="text-amber-700 dark:text-amber-300 hover:underline">Reviews</a>
                                            <a href="{{ route('documents.share', $document) }}" class="text-cyan-700 dark:text-cyan-300 hover:underline">Share</a>
                                        @endif
                                        @if ($document->trashed())
                                            <button wire:click="restoreDocument({{ $document->id }})" class="text-sky-700 dark:text-sky-300 hover:underline">Restore</button>
                                        @else
                                            @if ($document->is_archived)
                                                <button wire:click="unarchiveDocument({{ $document->id }})" class="text-amber-700 dark:text-amber-300 hover:underline">Unarchive</button>
                                            @else
                                                <button wire:click="archiveDocument({{ $document->id }})" class="text-amber-700 dark:text-amber-300 hover:underline">Archive</button>
                                            @endif
                                            <button wire:click="deleteDocument({{ $document->id }})" class="text-rose-600 dark:text-rose-300 hover:underline">Delete</button>
                                        @endif
                                        <button wire:click="duplicateDocument({{ $document->id }})" class="text-slate-700 dark:text-sky-50/75 hover:underline">Duplicate</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-sky-50/60">No documents found for the current filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($documents as $document)
                <div class="app-card border border-white/65 dark:border-white/8 rounded-[1.75rem] p-5">
                    <div class="flex items-start justify-between">
                        <h4 class="font-semibold text-slate-900 dark:text-white">{{ $document->title }}</h4>
                        @if ($document->trashed())
                            <span class="inline-flex px-2 py-1 rounded-full text-xs bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">Deleted</span>
                        @elseif ($document->is_archived)
                            <span class="inline-flex px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Archived</span>
                        @else
                            <span class="inline-flex px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">{{ ucfirst($document->status) }}</span>
                        @endif
                    </div>

                    <p class="mt-2 text-sm text-slate-500 dark:text-sky-50/60">Team: {{ $document->team?->name }}</p>
                    <p class="text-sm text-slate-500 dark:text-sky-50/60">Updated: {{ optional($document->updated_at)->diffForHumans() }}</p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if (! $document->trashed())
                            <a href="{{ route('documents.edit', $document) }}" class="text-sm text-sky-700 dark:text-sky-300 hover:underline">Edit</a>
                            <a href="{{ route('documents.reviews', $document) }}" class="text-sm text-amber-700 dark:text-amber-300 hover:underline">Reviews</a>
                            <a href="{{ route('documents.share', $document) }}" class="text-sm text-cyan-700 dark:text-cyan-300 hover:underline">Share</a>
                        @endif
                        @if ($document->trashed())
                            <button wire:click="restoreDocument({{ $document->id }})" class="text-sm text-sky-700 dark:text-sky-300 hover:underline">Restore</button>
                        @else
                            @if ($document->is_archived)
                                <button wire:click="unarchiveDocument({{ $document->id }})" class="text-sm text-amber-700 dark:text-amber-300 hover:underline">Unarchive</button>
                            @else
                                <button wire:click="archiveDocument({{ $document->id }})" class="text-sm text-amber-700 dark:text-amber-300 hover:underline">Archive</button>
                            @endif
                            <button wire:click="deleteDocument({{ $document->id }})" class="text-sm text-rose-600 dark:text-rose-300 hover:underline">Delete</button>
                        @endif
                        <button wire:click="duplicateDocument({{ $document->id }})" class="text-sm text-slate-700 dark:text-sky-50/75 hover:underline">Duplicate</button>
                    </div>
                </div>
            @empty
                <div class="sm:col-span-2 lg:col-span-3 app-card border border-white/65 dark:border-white/8 rounded-[2rem] p-10 text-center text-sm text-slate-500 dark:text-sky-50/60">
                    No documents found for the current filters.
                </div>
            @endforelse
        </div>
    @endif

    <div class="mt-6">
        {{ $documents->links() }}
    </div>
</div>
