@php
    $routeName = request()->route()?->getName();

    $titles = [
        'login' => ['title' => 'Welcome back', 'description' => 'Sign in to continue building documents, workflows, and polished deliverables.'],
        'register' => ['title' => 'Create your workspace', 'description' => 'Launch a branded writing environment for drafting, reviews, and automation.'],
        'password.request' => ['title' => 'Reset your access', 'description' => 'We will send a secure reset link so you can get back into your account quickly.'],
        'password.reset' => ['title' => 'Choose a new password', 'description' => 'Set a strong password to keep your workspace protected.'],
        'verification.notice' => ['title' => 'Verify your email', 'description' => 'Confirm your inbox so collaboration, notifications, and recovery flows stay reliable.'],
        'password.confirm' => ['title' => 'Confirm your password', 'description' => 'We need a quick security check before continuing.'],
        'two-factor.login' => ['title' => 'Two-factor check', 'description' => 'Enter your authentication code or recovery code to finish signing in.'],
    ];

    $copy = $titles[$routeName] ?? ['title' => 'Access Dot.docs', 'description' => 'Secure access to your writing workspace.'];
@endphp

<div class="w-full max-w-xl">
    <div class="auth-panel overflow-hidden rounded-[2rem] border border-white/25 px-6 py-6 text-slate-900 shadow-2xl sm:px-8 sm:py-8 lg:px-10">
        <div class="mb-8 flex flex-col gap-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-sky-700/70 dark:text-sky-100/70">Dot.docs</p>
                    <h2 class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-slate-900 dark:text-white">{{ $copy['title'] }}</h2>
                </div>
                <div class="shrink-0">
                    {{ $logo }}
                </div>
            </div>

            <p class="max-w-lg text-sm leading-7 text-slate-600 dark:text-sky-50/72">
                {{ $copy['description'] }}
            </p>
        </div>

        <div class="space-y-5 text-slate-800 dark:text-sky-50">
            {{ $slot }}
        </div>
    </div>
</div>
