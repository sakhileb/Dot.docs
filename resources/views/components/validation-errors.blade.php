@if ($errors->any())
    <div {{ $attributes }}>
        <div class="rounded-2xl border border-rose-300/60 bg-rose-50/80 px-4 py-3 text-sm text-rose-700 dark:border-rose-400/20 dark:bg-rose-500/10 dark:text-rose-200">
            <div class="font-semibold tracking-wide">{{ __('Whoops! Something went wrong.') }}</div>

            <ul class="mt-3 list-disc list-inside space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
