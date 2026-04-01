@props(['submit'])

<div {{ $attributes->merge(['class' => 'md:grid md:grid-cols-3 md:gap-6']) }}>
    <x-section-title>
        <x-slot name="title">{{ $title }}</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>
    </x-section-title>

    <div class="mt-5 md:mt-0 md:col-span-2">
        <form wire:submit="{{ $submit }}">
            <div class="settings-panel px-4 py-5 sm:p-6 {{ isset($actions) ? 'sm:rounded-tl-[1.5rem] sm:rounded-tr-[1.5rem]' : 'sm:rounded-[1.5rem]' }}">
                <div class="grid grid-cols-6 gap-6">
                    {{ $form }}
                </div>
            </div>

            @if (isset($actions))
                <div class="flex items-center justify-end gap-3 px-4 py-3 bg-white/70 dark:bg-slate-900/70 text-end sm:px-6 shadow sm:rounded-bl-[1.5rem] sm:rounded-br-[1.5rem] border-x border-b border-sky-100 dark:border-white/10">
                    {{ $actions }}
                </div>
            @endif
        </form>
    </div>
</div>
