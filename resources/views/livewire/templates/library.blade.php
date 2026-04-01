<div class="space-y-6">
    <div class="app-card rounded-[2rem] p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 w-full">
                <div class="md:col-span-2">
                    <x-label for="search" value="Search Templates" />
                    <x-input id="search" type="text" class="mt-1 block w-full" wire:model.live.debounce.300ms="search" placeholder="Search by name, description or content" />
                </div>

                <div>
                    <x-label for="categoryFilter" value="Category" />
                    <select id="categoryFilter" wire:model.live="categoryFilter" class="auth-input mt-1 w-full">
                        <option value="all">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-label for="scopeFilter" value="Scope" />
                    <select id="scopeFilter" wire:model.live="scopeFilter" class="auth-input mt-1 w-full">
                        <option value="all">Team + Public</option>
                        <option value="team">Team only</option>
                        <option value="public">Public only</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <label class="app-pill-button cursor-pointer">
                    Import
                    <input type="file" wire:model="importFile" class="hidden" accept=".json">
                </label>
                <x-secondary-button wire:click="importTemplates" @disabled(! $importFile)>Upload</x-secondary-button>
                <x-secondary-button wire:click="exportAllTemplates">Export All</x-secondary-button>
                <button wire:click="openCreateModal" class="rounded-full bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold px-5 py-2 tracking-wide transition">New Template</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse ($templates as $template)
            <div class="app-card rounded-[1.5rem] p-4">
                <div class="flex items-start justify-between gap-2">
                    <div>
                    <h4 class="font-semibold text-slate-800 dark:text-sky-50">{{ $template->name }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ ucfirst($template->category) }} · v{{ $template->version }}</p>
                    </div>
                    @if ($template->is_public)
                        <span class="inline-flex px-2 py-1 rounded-full text-xs bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300">Public</span>
                    @else
                        <span class="inline-flex px-2 py-1 rounded-full text-xs bg-slate-100 text-slate-600 dark:bg-white/8 dark:text-slate-300">Team</span>
                    @endif
                </div>

                <p class="mt-3 text-sm text-slate-600 dark:text-slate-300 line-clamp-3">{{ $template->description ?: 'No description' }}</p>

                <div class="mt-4 flex flex-wrap gap-2 text-sm">
                    <button wire:click="openPreview({{ $template->id }})" class="text-sky-600 dark:text-sky-400 hover:underline">Preview</button>
                    <button wire:click="openEditModal({{ $template->id }})" class="text-sky-600 dark:text-sky-400 hover:underline">Edit</button>
                    <button wire:click="toggleShare({{ $template->id }})" class="text-slate-500 dark:text-slate-400 hover:underline">{{ $template->is_public ? 'Unshare' : 'Share' }}</button>
                    <button wire:click="exportTemplate({{ $template->id }})" class="text-emerald-600 dark:text-emerald-400 hover:underline">Export</button>
                    <button wire:click="deleteTemplate({{ $template->id }})" class="text-rose-600 dark:text-rose-400 hover:underline">Delete</button>
                </div>
            </div>
        @empty
            <div class="sm:col-span-2 xl:col-span-3 app-card rounded-[1.5rem] p-10 text-center text-sm text-slate-500 dark:text-slate-400">
                No templates found.
            </div>
        @endforelse
    </div>

    <div>
        {{ $templates->links() }}
    </div>

    @if ($showCreateModal || $showEditModal)
        <div class="app-card rounded-[2rem] p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-sky-50">{{ $showEditModal ? 'Edit Template' : 'Create Template' }}</h3>
                <button wire:click="{{ $showEditModal ? 'closeEditModal' : 'closeCreateModal' }}" class="app-pill-button text-sm">Close</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-label for="name" value="Name" />
                    <x-input id="name" type="text" class="mt-1 block w-full" wire:model="name" />
                    <x-input-error for="name" class="mt-1" />
                </div>

                <div>
                    <x-label for="category" value="Category" />
                    <x-input id="category" type="text" class="mt-1 block w-full" wire:model="category" />
                    <x-input-error for="category" class="mt-1" />
                </div>

                <div class="md:col-span-2">
                    <x-label for="description" value="Description" />
                    <textarea id="description" wire:model="description" rows="3" class="auth-input mt-1 w-full"></textarea>
                    <x-input-error for="description" class="mt-1" />
                </div>

                <div class="md:col-span-2">
                    <x-label for="content" value="Template Content" />
                    <textarea id="content" wire:model="content" rows="10" class="auth-input mt-1 w-full"></textarea>
                    <x-input-error for="content" class="mt-1" />
                </div>

                <div class="md:col-span-2">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                        <input type="checkbox" wire:model="isPublic" class="rounded border-sky-200 dark:border-white/20 text-sky-600 focus:ring-sky-500">
                        Share publicly across teams
                    </label>
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <x-secondary-button wire:click="{{ $showEditModal ? 'closeEditModal' : 'closeCreateModal' }}">Cancel</x-secondary-button>
                <button wire:click="{{ $showEditModal ? 'updateTemplate' : 'createTemplate' }}" class="rounded-full bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold px-5 py-2 tracking-wide transition">{{ $showEditModal ? 'Save Changes' : 'Create Template' }}</button>
            </div>
        </div>
    @endif

    @if ($showPreviewModal && $previewTemplate)
        <div class="app-card rounded-[2rem] p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-sky-50">Template Preview: {{ $previewTemplate->name }}</h3>
                <button wire:click="closePreview" class="app-pill-button text-sm">Close</button>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">Category: {{ ucfirst($previewTemplate->category) }} · Version: {{ $previewTemplate->version }}</p>
            <div class="prose dark:prose-invert max-w-none rounded-[1.25rem] border border-sky-100 dark:border-white/10 p-4 bg-sky-50/40 dark:bg-white/4">
                {!! nl2br(e((string) $previewTemplate->content)) !!}
            </div>
        </div>
    @endif
</div>
