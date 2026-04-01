@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="px-6 py-4">
        <div class="text-lg font-semibold text-slate-900 dark:text-white tracking-[-0.02em]">
            {{ $title }}
        </div>

        <div class="mt-4 text-sm text-slate-600 dark:text-sky-50/70">
            {{ $content }}
        </div>
    </div>

    <div class="flex flex-row justify-end gap-3 px-6 py-4 bg-white/75 dark:bg-slate-900/80 border-t border-sky-100 dark:border-white/10 text-end">
        {{ $footer }}
    </div>
</x-modal>
