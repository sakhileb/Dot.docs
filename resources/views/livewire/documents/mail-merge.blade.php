<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5 space-y-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Merge Setup</h3>

        <div>
            <x-label for="templateContent" value="Template Content" />
            <textarea id="templateContent" wire:model="templateContent" rows="14" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" placeholder="Hello &#123;&#123;name&#125;&#125;, your renewal date is &#123;&#123;renewal_date&#125;&#125;."></textarea>
            <x-input-error for="templateContent" class="mt-1" />
        </div>

        <div>
            <x-label for="recipientJson" value="Recipients JSON" />
            <textarea id="recipientJson" wire:model="recipientJson" rows="10" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" placeholder='[{"name":"Jane Doe","renewal_date":"2026-05-01"}]'></textarea>
            <x-input-error for="recipientJson" class="mt-1" />
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Use a JSON array of recipients. Nested keys are supported via dot notation such as &#123;&#123;company.name&#125;&#125;.</p>
        </div>

        <div class="flex gap-3">
            <x-button class="flex-1 justify-center" wire:click="previewMerge">Preview Merge</x-button>
            <x-button class="flex-1 justify-center" wire:click="saveMergedDocuments">Save Merged Docs</x-button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Merged Preview</h3>

        <div class="space-y-4 max-h-[800px] overflow-y-auto">
            @forelse($mergedDocuments as $merged)
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Recipient {{ $merged['index'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ json_encode($merged['recipient']) }}</p>
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-200 whitespace-pre-wrap">{{ $merged['content'] }}</div>
                </div>
            @empty
                <div class="h-80 flex items-center justify-center rounded-lg border border-dashed border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400">
                    Generate a preview to inspect merged output.
                </div>
            @endforelse
        </div>
    </div>
</div>
