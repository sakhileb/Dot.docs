@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border border-[var(--line)] bg-[var(--paper)] focus:bg-white focus:border-[var(--gold-deep)] focus:ring-2 focus:ring-[var(--gold)]/25 rounded-lg shadow-sm text-sm text-[var(--ink)] placeholder-[var(--ink-soft)]/50 transition-all px-3.5 py-2.5']) !!}>
