<div class="relative min-h-screen flex flex-col sm:justify-center items-center pt-10 sm:pt-0 px-5 overflow-hidden">
    {{-- Same hero photo as welcome.blade.php ("type type type", hands typing on a laptop, by
    Christin Hume), with a light paper-toned scrim matching this platform's own light auth-card
    theme. --}}
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1515378960530-7c0da6231fb1?q=80&w=2400&auto=format&fit=crop');"></div>
    <div class="absolute inset-0" style="background: radial-gradient(ellipse 70% 65% at 50% 35%, var(--paper) 0%, rgba(251,247,238,0.94) 45%, rgba(251,247,238,0.7) 72%, rgba(251,247,238,0.35) 100%);"></div>

    <div class="relative z-10">
        {{ $logo }}
    </div>

    <div class="relative z-10 w-full sm:max-w-md mt-8 px-6 sm:px-8 py-8 bg-white border border-[var(--line)] shadow-lg overflow-hidden sm:rounded-xl">
        {{ $slot }}
    </div>
</div>
