<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <x-analytics-tracker />

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="marketing-shell text-gray-900 dark:text-gray-100">
        <div class="marketing-grid min-h-screen font-sans antialiased">
            <div class="relative mx-auto flex min-h-screen w-full max-w-7xl flex-col lg:flex-row lg:items-stretch">
                <section class="relative hidden w-full max-w-2xl flex-col justify-between px-10 py-12 text-white lg:flex xl:px-16">
                    <div class="space-y-10">
                        <a href="{{ url('/') }}" class="inline-flex items-center gap-4">
                            <img src="{{ asset('branding/dot_doc.png') }}" alt="Dot.docs" class="h-16 w-auto drop-shadow-[0_16px_40px_rgba(7,26,47,0.45)]">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.4em] text-sky-100/80">Smart Writing Workspace</p>
                                <p class="brand-wordmark mt-2 font-bold"><span class="gold">dot</span><span class="blue">.docs</span></p>
                            </div>
                        </a>

                        <div class="max-w-xl space-y-6">
                            <span class="hero-chip inline-flex rounded-full px-4 py-2 text-xs font-semibold uppercase tracking-[0.32em] text-sky-50/85">Write, automate, publish</span>
                            <h1 class="max-w-xl text-5xl font-semibold leading-[0.95] tracking-[-0.05em] text-white xl:text-6xl">
                                Documents that feel like products, not files.
                            </h1>
                            <p class="max-w-lg text-base leading-7 text-sky-50/78 xl:text-lg">
                                Draft faster with AI, coordinate reviews, build forms, run mail merges, and sync polished work across your cloud tools from one workspace.
                            </p>
                        </div>
                    </div>

                    <div class="grid max-w-2xl grid-cols-3 gap-4 text-sm text-sky-50/88">
                        <div class="hero-card rounded-3xl border border-white/10 p-5">
                            <p class="text-3xl font-semibold text-white">11+</p>
                            <p class="mt-2 leading-6">Integrated writing workflows across collaboration, AI, export, and transfer.</p>
                        </div>
                        <div class="hero-card rounded-3xl border border-white/10 p-5">
                            <p class="text-3xl font-semibold text-white">Live</p>
                            <p class="mt-2 leading-6">Reviews, citations, forms, and mail merge all stay attached to the document lifecycle.</p>
                        </div>
                        <div class="hero-card rounded-3xl border border-white/10 p-5">
                            <p class="text-3xl font-semibold text-white">Cloud</p>
                            <p class="mt-2 leading-6">Move work between Google Drive, Dropbox, and OneDrive without leaving the app.</p>
                        </div>
                    </div>
                </section>

                <section class="relative flex min-h-screen flex-1 items-center justify-center px-5 py-8 sm:px-8 lg:px-12">
                    {{ $slot }}
                </section>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
