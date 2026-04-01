<div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
    <section class="app-card rounded-[2rem] border border-white/65 p-8 dark:border-white/8 lg:p-10">
        <div class="flex items-center gap-4">
            <x-application-logo class="h-16 w-auto" />
            <div>
                <p class="brand-section-title">Workspace Overview</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-slate-900 dark:text-white lg:text-4xl">Welcome back to Dot.docs</h1>
            </div>
        </div>

        <p class="mt-8 max-w-3xl text-base leading-8 text-slate-600 dark:text-sky-50/74">
            Your workspace now covers the full content lifecycle: drafting with AI, structured reviews, citations, forms, mail merge, analytics, and direct cloud transfer. Use the dashboard as a launchpad, then jump into the document library to move work forward.
        </p>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            <div class="rounded-[1.75rem] bg-sky-50/80 p-5 ring-1 ring-sky-100 dark:bg-sky-500/10 dark:ring-sky-400/10">
                <p class="brand-section-title">Create</p>
                <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white">AI, templates, blank docs</p>
                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-sky-50/68">Open the document library to start from a prompt, a template, or a clean page.</p>
            </div>
            <div class="rounded-[1.75rem] bg-amber-50/85 p-5 ring-1 ring-amber-100 dark:bg-amber-400/10 dark:ring-amber-300/10">
                <p class="brand-section-title">Refine</p>
                <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white">Review, cite, verify</p>
                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-sky-50/68">Track revisions, manage citations, and inspect quality signals inside the doc lifecycle.</p>
            </div>
            <div class="rounded-[1.75rem] bg-white/80 p-5 ring-1 ring-slate-200/80 dark:bg-white/5 dark:ring-white/8">
                <p class="brand-section-title">Deliver</p>
                <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white">Export and sync anywhere</p>
                <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-sky-50/68">Ship work across Google Drive, Dropbox, and OneDrive from the same transfer workflow.</p>
            </div>
        </div>
    </section>

    <section class="space-y-6">
        <div class="hero-card rounded-[2rem] border border-white/10 p-8 text-white">
            <p class="brand-section-title !text-sky-50/65">Quick Start</p>
            <h2 class="mt-3 text-2xl font-semibold tracking-[-0.04em]">Recommended next moves</h2>
            <ul class="mt-6 space-y-4 text-sm leading-7 text-sky-50/75">
                <li class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">Create a new draft from AI or a team template.</li>
                <li class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">Use the transfer hub to pull in an external file or export polished output.</li>
                <li class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">Open analytics to review usage patterns and content performance.</li>
            </ul>
        </div>

        <div class="app-card rounded-[2rem] border border-white/65 p-8 dark:border-white/8">
            <p class="brand-section-title">Useful Links</p>
            <div class="mt-5 grid gap-3 text-sm">
                <a href="{{ route('documents.index') }}" class="app-pill-button px-4 py-3 font-semibold">Go to documents</a>
                <a href="{{ route('documents.transfer') }}" class="app-pill-button px-4 py-3 font-semibold">Open transfer hub</a>
                <a href="{{ route('ai.analytics') }}" class="app-pill-button px-4 py-3 font-semibold">View AI analytics</a>
            </div>
        </div>
    </section>
</div>
