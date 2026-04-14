<div class="w-full">

    {{-- Logo / heading — only shown on mobile (lg+ already has it in the left branding panel) --}}
    <div class="text-center mb-8 lg:hidden">
        {{ $logo }}
    </div>

    {{-- Form card --}}
    <div class="w-full">
        {{ $slot }}
    </div>

</div>
