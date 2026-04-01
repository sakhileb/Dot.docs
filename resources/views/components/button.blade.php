<button {{ $attributes->merge(['type' => 'submit', 'class' => 'auth-button inline-flex items-center justify-center px-5 py-3 border border-transparent font-semibold text-xs uppercase tracking-[0.28em] focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2 focus:ring-offset-transparent disabled:opacity-50 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
