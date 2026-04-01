<div class="space-y-6">
    <div class="app-card border border-white/65 dark:border-white/8 rounded-[2rem] p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="inline-flex rounded-full border border-sky-100 dark:border-white/10 overflow-hidden">
                <button wire:click="$set('tab', 'export')" class="px-4 py-2 text-sm font-semibold {{ $tab === 'export' ? 'bg-sky-600 text-white' : 'bg-white/80 dark:bg-slate-900/70 text-slate-700 dark:text-sky-50/72' }}">Export</button>
                <button wire:click="$set('tab', 'import')" class="px-4 py-2 text-sm font-semibold {{ $tab === 'import' ? 'bg-sky-600 text-white' : 'bg-white/80 dark:bg-slate-900/70 text-slate-700 dark:text-sky-50/72' }}">Import</button>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('documents.index') }}" class="app-pill-button px-4 py-2 text-sm font-semibold">Back to Documents</a>
            </div>
        </div>
    </div>

    @if ($tab === 'export')
        <div class="app-card border border-white/65 dark:border-white/8 rounded-[2rem] p-5 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="md:col-span-2">
                    <x-label for="search" value="Find Document" />
                    <x-input id="search" type="text" class="mt-1 block w-full" wire:model.live.debounce.300ms="search" placeholder="Search document title" />
                </div>
                <div>
                    <x-label for="exportFormat" value="Export Format" />
                    <select id="exportFormat" wire:model="exportFormat" class="auth-input mt-1 w-full">
                        <option value="pdf">PDF</option>
                        <option value="docx">DOCX</option>
                        <option value="html">HTML</option>
                        <option value="md">Markdown</option>
                        <option value="txt">Plain Text</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="w-52">
                        <x-label for="cloudExportProvider" value="Cloud Provider" />
                        <select id="cloudExportProvider" wire:model="cloudExportProvider" class="auth-input mt-1 w-full">
                            <option value="google_drive">Google Drive</option>
                            <option value="dropbox">Dropbox</option>
                            <option value="onedrive">OneDrive</option>
                        </select>
                    </div>

                    <x-button wire:click="exportBatch">Export Selected (Batch ZIP)</x-button>
                    <button wire:click="exportBatchToCloud" type="button" class="inline-flex items-center px-4 py-2 rounded-full font-semibold text-xs uppercase tracking-[0.14em] text-white bg-sky-600 hover:bg-sky-700 transition">
                        Export Selected To Cloud
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto border border-sky-100 dark:border-white/10 rounded-[1.25rem]">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-slate-50/85 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Select</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Title</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Updated</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($documents as $document)
                            <tr>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-sky-50/70">
                                    <input type="checkbox" @checked(in_array($document->id, $selectedDocuments, true)) wire:click="toggleSelection({{ $document->id }})" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">{{ $document->title }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-sky-50/70">{{ optional($document->updated_at)->diffForHumans() }}</td>
                                <td class="px-4 py-3 text-sm text-right">
                                    <button wire:click="exportSingle({{ $document->id }})" class="text-sky-700 dark:text-sky-300 hover:underline">Export</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-sky-50/60">No documents found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($tab === 'import')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="app-card border border-white/65 dark:border-white/8 rounded-[2rem] p-5 space-y-4">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Import From File</h3>

                <div>
                    <x-label for="importTitle" value="Document Title (Optional)" />
                    <x-input id="importTitle" type="text" class="mt-1 block w-full" wire:model="importTitle" placeholder="Defaults to file name" />
                    <x-input-error for="importTitle" class="mt-1" />
                </div>

                <div>
                    <x-label for="importFile" value="Upload File" />
                    <input id="importFile" type="file" wire:model="importFile" class="mt-1 block w-full text-sm text-slate-700 dark:text-sky-50/70" accept=".docx,.md,.markdown,.html,.htm,.txt">
                    <p class="mt-1 text-xs text-slate-500 dark:text-sky-50/60">Supported: DOCX, Markdown, HTML, TXT</p>
                    <x-input-error for="importFile" class="mt-1" />
                </div>

                <x-button wire:click="importFromFile">Import File</x-button>
            </div>

            <div class="app-card border border-white/65 dark:border-white/8 rounded-[2rem] p-5 space-y-4">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Import From Google Docs</h3>

                <div>
                    <x-label for="googleDocsUrl" value="Google Docs URL" />
                    <x-input id="googleDocsUrl" type="url" class="mt-1 block w-full" wire:model="googleDocsUrl" placeholder="https://docs.google.com/document/d/..." />
                    <x-input-error for="googleDocsUrl" class="mt-1" />
                </div>

                <div>
                    <x-label for="importTitleGoogle" value="Document Title (Optional)" />
                    <x-input id="importTitleGoogle" type="text" class="mt-1 block w-full" wire:model="importTitle" placeholder="Defaults to Imported Google Doc" />
                </div>

                <x-button wire:click="importFromGoogleDocs">Import Google Doc</x-button>

                <p class="text-xs text-slate-500 dark:text-sky-50/60">Note: Document must be accessible for export.</p>
            </div>

            <div class="app-card border border-white/65 dark:border-white/8 rounded-[2rem] p-5 space-y-4 lg:col-span-2">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Import From Google Drive, Dropbox, or OneDrive</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-label for="cloudImportProvider" value="Provider" />
                        <select id="cloudImportProvider" wire:model="cloudImportProvider" class="auth-input mt-1 w-full">
                            <option value="google_drive">Google Drive</option>
                            <option value="dropbox">Dropbox</option>
                            <option value="onedrive">OneDrive</option>
                        </select>
                        <x-input-error for="cloudImportProvider" class="mt-1" />
                    </div>

                    <div class="md:col-span-2">
                        <x-label for="cloudReference" value="File Reference" />
                        <x-input id="cloudReference" type="text" class="mt-1 block w-full" wire:model="cloudReference" placeholder="Paste a file ID, shared path, or share URL" />
                        <x-input-error for="cloudReference" class="mt-1" />
                    </div>
                </div>

                <div>
                    <x-label for="importTitleCloud" value="Document Title (Optional)" />
                    <x-input id="importTitleCloud" type="text" class="mt-1 block w-full" wire:model="importTitle" placeholder="Defaults to the remote file name" />
                    <x-input-error for="importTitle" class="mt-1" />
                </div>

                <x-button wire:click="importFromCloud">Import Cloud File</x-button>

                <p class="text-xs text-slate-500 dark:text-sky-50/60">Google Drive imports accept file IDs or document/file URLs. Dropbox accepts shared links or API paths. OneDrive accepts share URLs or item IDs. Provider access tokens are loaded from environment configuration.</p>
            </div>
        </div>
    @endif
</div>
