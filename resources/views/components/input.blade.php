@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border border-slate-200 bg-slate-50 focus:bg-white focus:border-sky-400 focus:ring-2 focus:ring-sky-100 rounded-lg shadow-sm text-sm text-slate-900 placeholder-slate-400 transition-all px-3.5 py-2.5']) !!}>
