@props(['value'])

<label {{ $attributes->merge(['class' => 'auth-label block']) }}>
    {{ $value ?? $slot }}
</label>
