<button {{ $attributes->merge(['type' => 'button', 'class' => 'app-pill-button inline-flex items-center px-4 py-2 font-semibold text-xs uppercase tracking-[0.12em] shadow-sm focus:outline-none disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
