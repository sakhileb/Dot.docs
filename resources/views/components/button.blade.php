<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-6 py-2.5 rounded-lg font-bold text-sm text-slate-900 tracking-wide shadow-md hover:brightness-110 hover:-translate-y-px active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-2 transition-all duration-150']) }} style="background: linear-gradient(135deg, #F5C110, #f59e0b);">
    {{ $slot }}
</button>
