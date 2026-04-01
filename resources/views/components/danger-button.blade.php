<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2 rounded-full font-semibold text-xs text-white uppercase tracking-[0.12em] bg-rose-600 border border-transparent hover:bg-rose-500 active:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
