<div class="md:col-span-1 flex justify-between">
    <div class="px-4 sm:px-0">
        <p class="brand-section-title">Settings</p>
        <h3 class="mt-2 text-xl font-semibold tracking-[-0.03em] text-slate-900 dark:text-white">{{ $title }}</h3>

        <p class="mt-2 text-sm leading-7 text-slate-600 dark:text-sky-50/66">
            {{ $description }}
        </p>
    </div>

    <div class="px-4 sm:px-0">
        {{ $aside ?? '' }}
    </div>
</div>
