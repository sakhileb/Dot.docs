@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-2xl px-4 py-3 text-start text-base font-semibold text-white bg-sky-600 shadow-[0_12px_24px_rgba(73,163,234,0.22)] transition duration-150 ease-in-out'
            : 'block w-full rounded-2xl px-4 py-3 text-start text-base font-medium text-slate-700 hover:bg-sky-50 hover:text-sky-800 dark:text-sky-50/75 dark:hover:bg-sky-500/10 dark:hover:text-white transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
