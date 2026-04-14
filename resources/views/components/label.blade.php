@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-xs text-slate-600 uppercase tracking-wide mb-1']) }}>
    {{ $value ?? $slot }}
</label>
