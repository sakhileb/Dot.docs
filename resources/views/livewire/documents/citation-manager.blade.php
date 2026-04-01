<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 app-card rounded-[2rem] p-5 space-y-4">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-sky-50">Add Manual Citation</h3>

        <div>
            <x-label for="title" value="Title" />
            <x-input id="title" type="text" class="mt-1 block w-full" wire:model="title" />
            <x-input-error for="title" class="mt-1" />
        </div>

        <div>
            <x-label for="authors" value="Authors" />
            <x-input id="authors" type="text" class="mt-1 block w-full" wire:model="authors" placeholder="Doe, Jane; Smith, John" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <x-label for="publicationYear" value="Year" />
                <x-input id="publicationYear" type="text" class="mt-1 block w-full" wire:model="publicationYear" placeholder="2026" />
            </div>
            <div>
                <x-label for="sourceUrl" value="Source URL" />
                <x-input id="sourceUrl" type="text" class="mt-1 block w-full" wire:model="sourceUrl" placeholder="https://..." />
            </div>
        </div>

        <div>
            <x-label for="citationText" value="Citation Text" />
            <textarea id="citationText" wire:model="citationText" rows="4" class="auth-input mt-1 w-full"></textarea>
        </div>

        <x-button class="w-full justify-center" wire:click="addManualCitation">Add Citation</x-button>

        <div class="pt-4 border-t border-sky-100 dark:border-white/10 space-y-3">
            <h4 class="text-sm font-semibold text-slate-800 dark:text-sky-50">Import From Zotero / Mendeley</h4>

            <div>
                <x-label for="importProvider" value="Provider" />
                <select id="importProvider" wire:model="importProvider" class="auth-input mt-1 w-full">
                    <option value="zotero">Zotero</option>
                    <option value="mendeley">Mendeley</option>
                </select>
            </div>

            <div>
                <x-label for="importJson" value="Paste Exported JSON" />
                <textarea id="importJson" wire:model="importJson" rows="8" class="auth-input mt-1 w-full" placeholder='[{"title":"Example"}]'></textarea>
                <x-input-error for="importJson" class="mt-1" />
            </div>

            <x-button class="w-full justify-center" wire:click="importCitations">Import Citations</x-button>
        </div>
    </div>

    <div class="lg:col-span-2 app-card rounded-[2rem] p-5">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-sky-50 mb-4">Document Citations</h3>

        <div class="space-y-3">
            @forelse($citations as $citation)
                <div class="rounded-[1.25rem] border border-sky-100 dark:border-white/10 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-slate-800 dark:text-sky-50">{{ $citation->title }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                {{ $citation->authors ?: 'Unknown author' }}
                                @if($citation->publication_year)
                                    ({{ $citation->publication_year }})
                                @endif
                                · <span class="uppercase tracking-wide">{{ $citation->provider }}</span>
                            </p>
                            @if($citation->citation_text)
                                <p class="text-sm text-slate-700 dark:text-slate-200 mt-2">{{ $citation->citation_text }}</p>
                            @endif
                            @if($citation->source_url)
                                <a href="{{ $citation->source_url }}" target="_blank" class="text-xs text-sky-600 dark:text-sky-400 mt-2 inline-block">Open source</a>
                            @endif
                        </div>
                        <button
                            type="button"
                            wire:click="deleteCitation({{ $citation->id }})"
                            class="px-3 py-1 text-xs rounded-full border border-rose-300 text-rose-700 hover:bg-rose-50 dark:border-rose-700 dark:text-rose-300 dark:hover:bg-rose-900/20"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            @empty
                <div class="h-60 flex items-center justify-center rounded-[1.25rem] border border-dashed border-sky-200 dark:border-sky-500/30 text-slate-500 dark:text-slate-400">
                    No citations added yet.
                </div>
            @endforelse
        </div>
    </div>
</div>
