<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Dot.docs') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="marketing-shell text-white antialiased">
        <div class="marketing-grid relative min-h-screen overflow-hidden">
            <div class="mx-auto flex min-h-screen max-w-7xl flex-col px-5 py-6 sm:px-8 lg:px-10 xl:px-12">
                <header class="flex items-center justify-between gap-4 py-2">
                    <a href="{{ url('/') }}" class="flex items-center gap-4">
                        <img src="{{ asset('branding/dot_doc.png') }}" alt="Dot.docs" class="h-14 w-auto sm:h-16">
                        <div class="hidden sm:block">
                            <p class="text-xs font-semibold uppercase tracking-[0.4em] text-sky-50/70">Collaborative Writing OS</p>
                            <p class="brand-wordmark mt-1 font-bold"><span class="gold">dot</span><span class="blue">.docs</span></p>
                        </div>
                    </a>

                    @if (Route::has('login'))
                        <nav class="flex items-center gap-3 text-sm">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="hero-chip rounded-full px-5 py-3 font-semibold tracking-wide text-white transition hover:bg-white/16">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="rounded-full px-5 py-3 font-semibold text-sky-50/85 transition hover:text-white">
                                    Log In
                                </a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="auth-button inline-flex items-center justify-center rounded-full px-5 py-3 text-xs font-semibold uppercase tracking-[0.24em]">
                                        Start Free
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    @endif
                </header>

                <main class="flex flex-1 flex-col justify-center py-10 lg:py-16">
                    <div class="grid items-center gap-10 lg:grid-cols-[1.1fr_0.9fr] xl:gap-16">
                        <section class="max-w-3xl">
                            <div class="hero-chip inline-flex rounded-full px-4 py-2 text-xs font-semibold uppercase tracking-[0.32em] text-sky-50/85">
                                AI drafting, review, transfer, and automation
                            </div>

                            <h1 class="mt-8 max-w-4xl text-5xl font-semibold leading-[0.92] tracking-[-0.06em] text-white sm:text-6xl xl:text-7xl">
                                Turn every document into a live, branded workflow.
                            </h1>

                            <p class="mt-8 max-w-2xl text-base leading-8 text-sky-50/76 sm:text-lg">
                                Dot.docs combines collaborative editing, AI generation, citation workflows, form building, mail merge, and cloud delivery in one workspace designed for teams that publish polished work every day.
                            </p>

                            <div class="mt-10 flex flex-col gap-4 sm:flex-row">
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="auth-button inline-flex items-center justify-center rounded-full px-7 py-4 text-xs font-semibold uppercase tracking-[0.28em]">
                                        Create Workspace
                                    </a>
                                @endif

                                @if (Route::has('login'))
                                    <a href="{{ route('login') }}" class="hero-chip inline-flex items-center justify-center rounded-full px-7 py-4 text-sm font-semibold text-white transition hover:bg-white/16">
                                        Explore Secure Access
                                    </a>
                                @endif
                            </div>

                            <dl class="mt-12 grid gap-4 sm:grid-cols-3">
                                <div class="hero-card rounded-3xl border border-white/10 p-5">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-50/60">Draft Faster</dt>
                                    <dd class="mt-3 text-3xl font-semibold text-white">AI+</dd>
                                    <p class="mt-2 text-sm leading-6 text-sky-50/74">Generate, rewrite, summarize, and enhance long-form content without breaking flow.</p>
                                </div>
                                <div class="hero-card rounded-3xl border border-white/10 p-5">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-50/60">Ship Cleaner</dt>
                                    <dd class="mt-3 text-3xl font-semibold text-white">Review</dd>
                                    <p class="mt-2 text-sm leading-6 text-sky-50/74">Comments, versions, citations, plagiarism checks, and analytics stay inside the document lifecycle.</p>
                                </div>
                                <div class="hero-card rounded-3xl border border-white/10 p-5">
                                    <dt class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-50/60">Deliver Anywhere</dt>
                                    <dd class="mt-3 text-3xl font-semibold text-white">Cloud</dd>
                                    <p class="mt-2 text-sm leading-6 text-sky-50/74">Export and import across Google Drive, Dropbox, and OneDrive from the same transfer hub.</p>
                                </div>
                            </dl>
                        </section>

                        <section class="space-y-5">
                            <div class="hero-card rounded-[2rem] border border-white/10 p-6 sm:p-8">
                                <div class="flex items-center gap-4">
                                    <div class="rounded-2xl bg-white/10 p-3">
                                        <img src="{{ asset('branding/dot_doc.png') }}" alt="Dot.docs" class="h-12 w-auto">
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-50/60">Workspace Snapshot</p>
                                        <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-white">One home for writing operations</h2>
                                    </div>
                                </div>

                                <div class="mt-8 space-y-4">
                                    <div class="rounded-3xl bg-white/6 p-5 ring-1 ring-white/8">
                                        <p class="text-sm font-semibold text-white">Form Builder + Mail Merge</p>
                                        <p class="mt-2 text-sm leading-6 text-sky-50/72">Collect structured input, merge it into tailored outputs, and keep the whole flow attached to the source document.</p>
                                    </div>
                                    <div class="rounded-3xl bg-white/6 p-5 ring-1 ring-white/8">
                                        <p class="text-sm font-semibold text-white">Transfer Hub</p>
                                        <p class="mt-2 text-sm leading-6 text-sky-50/72">Move drafts in from cloud providers, convert formats, and publish polished versions back out without juggling tools.</p>
                                    </div>
                                    <div class="rounded-3xl bg-white/6 p-5 ring-1 ring-white/8">
                                        <p class="text-sm font-semibold text-white">Review Intelligence</p>
                                        <p class="mt-2 text-sm leading-6 text-sky-50/72">Use analytics, citations, plagiarism checks, and version history to review content with actual context.</p>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>