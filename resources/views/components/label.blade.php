@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-xs text-[var(--ink-soft)] uppercase tracking-wide mb-1']) }}>
    {{ $value ?? $slot }}
</label>
