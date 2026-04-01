@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'auth-input w-full shadow-sm focus:ring-0']) !!}>
