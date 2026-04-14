<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="dot.doc — AI-powered document creation with real-time collaboration, smart templates, version history, and offline support.">
    <title>dot.doc — Write Smarter. Collaborate Faster.</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        :root {
            --gold: #F5C110;
            --sky: #3897D3;
            --dark: #080f1e;
        }
        body { font-family: 'Figtree', sans-serif; }

        /* Hero grid-dot background */
        .hero-bg {
            background-color: #080f1e;
            background-image:
                radial-gradient(ellipse 80% 60% at 70% -10%, rgba(56,151,211,0.22) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at -10% 80%, rgba(245,193,16,0.18) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 100% 100%, rgba(139,92,246,0.12) 0%, transparent 60%);
        }
        .dot-grid {
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.07) 1px, transparent 0);
            background-size: 36px 36px;
        }

        /* Glow blobs */
        .blob-gold {
            background: radial-gradient(circle, rgba(245,193,16,0.5) 0%, transparent 70%);
            filter: blur(60px);
        }
        .blob-sky {
            background: radial-gradient(circle, rgba(56,151,211,0.5) 0%, transparent 70%);
            filter: blur(60px);
        }
        .blob-violet {
            background: radial-gradient(circle, rgba(139,92,246,0.35) 0%, transparent 70%);
            filter: blur(80px);
        }

        /* Glass card */
        .glass {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.10);
        }

        /* Gradient text */
        .grad-text {
            background: linear-gradient(135deg, #F5C110 0%, #f97316 40%, #3897D3 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Shimmer badge */
        .badge-glow {
            background: linear-gradient(90deg, rgba(245,193,16,0.15), rgba(56,151,211,0.15));
            border: 1px solid rgba(245,193,16,0.35);
        }

        /* Navbar blur */
        .navbar {
            background: rgba(8,15,30,0.75);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }

        /* Section card hover */
        .feat-card {
            background: rgba(255,255,255,0.98);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .feat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px -12px rgba(56,151,211,0.18);
        }

        /* Step number glow */
        .step-num {
            box-shadow: 0 0 20px rgba(56,151,211,0.5);
        }

        /* CTA gradient */
        .cta-grad {
            background: linear-gradient(135deg, #F5C110 0%, #f59e0b 30%, #3897D3 100%);
        }

        /* Footer */
        .footer-dark { background: #060c18; }

        /* Mockup toolbar icon */
        .toolbar-btn {
            padding: 3px 7px;
            border-radius: 5px;
            cursor: default;
            user-select: none;
            font-size: 12px;
            color: #64748b;
            transition: background 0.15s;
        }
        .toolbar-btn:hover { background: #f1f5f9; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(56,151,211,0.3); border-radius: 6px; }
    </style>
</head>
<body class="antialiased">

<!-- ======================================================== -->
<!-- NAVBAR -->
<!-- ======================================================== -->
<nav class="navbar sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Logo -->
            <a href="/" class="flex items-center gap-2 flex-shrink-0">
                <img src="/dot_doc.png" alt="dot.doc" class="h-9 w-auto">
            </a>

            <!-- Nav links -->
            <div class="hidden md:flex items-center gap-8">
                <a href="#features"    class="text-sm font-medium text-slate-400 hover:text-white transition-colors">Features</a>
                <a href="#how-it-works" class="text-sm font-medium text-slate-400 hover:text-white transition-colors">How It Works</a>
                <a href="#templates"   class="text-sm font-medium text-slate-400 hover:text-white transition-colors">Templates</a>
            </div>

            <!-- Auth CTAs -->
            <div class="flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="text-sm font-semibold px-4 py-2 rounded-lg transition-colors"
                           style="background: rgba(56,151,211,0.15); color: #3897D3; border: 1px solid rgba(56,151,211,0.3);">
                            Dashboard &rarr;
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="text-sm font-medium text-slate-400 hover:text-white transition-colors">
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="text-sm font-semibold px-5 py-2 rounded-lg text-slate-900 transition-all hover:brightness-110 shadow-md"
                               style="background: linear-gradient(135deg, #F5C110, #f59e0b);">
                                Get Started
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </div>
</nav>


<!-- ======================================================== -->
<!-- HERO -->
<!-- ======================================================== -->
<section class="hero-bg relative overflow-hidden min-h-screen flex flex-col justify-center pt-6 pb-24">

    <!-- Dot grid overlay -->
    <div class="dot-grid absolute inset-0 pointer-events-none" aria-hidden="true"></div>

    <!-- Glow blobs -->
    <div class="blob-gold absolute w-96 h-96 -top-24 right-1/4 opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="blob-sky absolute w-[500px] h-[500px] -bottom-32 -left-24 opacity-50 pointer-events-none" aria-hidden="true"></div>
    <div class="blob-violet absolute w-80 h-80 top-1/2 right-0 opacity-40 pointer-events-none" aria-hidden="true"></div>

    <div class="relative max-w-6xl mx-auto px-6 text-center">

        <!-- Badge -->
        <div class="inline-flex items-center gap-2 badge-glow text-amber-300 text-xs font-bold px-5 py-2 rounded-full mb-10 shadow-lg">
            <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            AI-Powered &nbsp;·&nbsp; Real-time &nbsp;·&nbsp; Offline-Ready
        </div>

        <!-- Headline -->
        <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black tracking-tight leading-tight mb-7 text-white">
            Write Smarter.<br>
            <span class="grad-text">Collaborate Faster.</span>
        </h1>

        <!-- Subheadline -->
        <p class="max-w-2xl mx-auto text-lg sm:text-xl leading-relaxed mb-12" style="color: rgba(255,255,255,0.62);">
            dot.doc combines AI-assisted writing, real-time collaboration, smart templates,
            and offline support — all in one beautifully crafted document platform.
        </p>

        <!-- CTAs -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16">
            @if (Route::has('register'))
                <a href="{{ route('register') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 font-bold rounded-xl shadow-xl hover:brightness-110 transition-all text-base text-slate-900"
                   style="background: linear-gradient(135deg, #F5C110, #f59e0b);">
                    Start Writing Free
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            @endif
            <a href="#features"
               class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 font-semibold rounded-xl transition-all text-base text-slate-300 hover:text-white"
               style="border: 1.5px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.04);">
                Explore Features
            </a>
        </div>

        <!-- ── EDITOR CHROME MOCKUP ── -->
        <div class="relative mx-auto max-w-4xl">
            <div class="rounded-2xl overflow-hidden shadow-2xl" style="border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.03);">

                <!-- Window chrome -->
                <div class="flex items-center gap-2 px-4 py-3 border-b" style="background: rgba(15,25,45,0.95); border-color: rgba(255,255,255,0.08);">
                    <div class="w-3 h-3 rounded-full bg-red-500 opacity-80"></div>
                    <div class="w-3 h-3 rounded-full bg-amber-400 opacity-80"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500 opacity-80"></div>
                    <div class="flex-1 mx-4 rounded-md px-3 py-1 text-xs text-left"
                         style="background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.35); border: 1px solid rgba(255,255,255,0.08);">
                        Q4 Project Proposal.doc
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-6 h-6 rounded-full bg-sky-500 text-white text-[10px] flex items-center justify-center font-bold ring-2" style="ring-color: rgba(8,15,30,0.8);">A</div>
                        <div class="w-6 h-6 rounded-full bg-amber-500 text-white text-[10px] flex items-center justify-center font-bold ring-2" style="ring-color: rgba(8,15,30,0.8);">B</div>
                        <div class="w-6 h-6 rounded-full bg-emerald-500 text-white text-[10px] flex items-center justify-center font-bold ring-2" style="ring-color: rgba(8,15,30,0.8);">C</div>
                        <span class="text-xs font-medium ml-1" style="color: rgba(255,255,255,0.4);">3 online</span>
                    </div>
                </div>

                <!-- Toolbar -->
                <div class="flex items-center gap-0.5 px-4 py-2 border-b overflow-x-auto whitespace-nowrap" style="background: rgba(255,255,255,0.97); border-color: #e2e8f0;">
                    <span class="toolbar-btn font-bold">B</span>
                    <span class="toolbar-btn italic">I</span>
                    <span class="toolbar-btn underline">U</span>
                    <span class="w-px h-4 bg-slate-200 mx-1.5 inline-block"></span>
                    <span class="toolbar-btn">H1</span>
                    <span class="toolbar-btn">H2</span>
                    <span class="toolbar-btn">H3</span>
                    <span class="w-px h-4 bg-slate-200 mx-1.5 inline-block"></span>
                    <span class="toolbar-btn">&#8801;</span>
                    <span class="toolbar-btn">&#9783;</span>
                    <span class="toolbar-btn">⊞</span>
                    <span class="ml-auto inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold cursor-default select-none"
                          style="background: linear-gradient(135deg, rgba(245,193,16,0.12), rgba(56,151,211,0.12)); border: 1px solid rgba(245,193,16,0.3); color: #b45309;">
                        ✨ AI Assist
                    </span>
                </div>

                <!-- Document body -->
                <div class="p-8 text-left" style="background: #fff; min-height: 180px;">
                    <h2 class="text-2xl font-bold text-slate-800 mb-3">Project Proposal: Q4 2025 Initiative</h2>
                    <p class="text-slate-600 mb-5 leading-relaxed text-sm">
                        This document outlines the key objectives and milestones for our upcoming product launch.
                        The strategy focuses on three core pillars:
                        <span class="text-sky-600 font-semibold">user experience</span>,
                        <span class="font-semibold" style="color: #d97706;">scalability</span>, and
                        <span class="text-emerald-600 font-semibold">team alignment</span>.
                    </p>
                    <div class="inline-flex items-start gap-2.5 rounded-xl px-4 py-3 text-sm"
                         style="background: rgba(245,193,16,0.08); border: 1px solid rgba(245,193,16,0.25);">
                        <span class="text-base mt-0.5">✨</span>
                        <span style="color: #92400e;"><strong>AI suggestion:</strong> Add a budget breakdown section to strengthen this proposal.</span>
                    </div>
                </div>
            </div>

            <!-- Floating badges -->
            <div class="absolute -right-5 top-1/3 rounded-xl shadow-2xl px-3.5 py-2.5 text-xs hidden lg:block glass"
                 style="color: #34d399;" aria-hidden="true">
                <div class="flex items-center gap-1.5 font-semibold">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
                    3 collaborators online
                </div>
            </div>

            <div class="absolute -left-5 bottom-1/4 rounded-xl shadow-2xl px-3.5 py-2.5 text-xs hidden lg:block glass"
                 style="color: #60a5fa;" aria-hidden="true">
                <div class="flex items-center gap-1.5 font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Auto-saved 2s ago
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ======================================================== -->
<!-- STATS STRIP -->
<!-- ======================================================== -->
<section style="background: #0d1528; border-top: 1px solid rgba(255,255,255,0.07); border-bottom: 1px solid rgba(255,255,255,0.07);" class="py-16">
    <div class="max-w-5xl mx-auto px-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-10 text-center">
            <div>
                <div class="text-4xl font-black text-white mb-2">&infin;</div>
                <div class="text-sm text-slate-500">Documents &amp; revisions</div>
            </div>
            <div>
                <div class="text-4xl font-black grad-text mb-2">AI</div>
                <div class="text-sm text-slate-500">Powered writing assistant</div>
            </div>
            <div>
                <div class="text-4xl font-black text-white mb-2">RT</div>
                <div class="text-sm text-slate-500">Real-time collaboration</div>
            </div>
            <div>
                <div class="text-4xl font-black text-white mb-2">100%</div>
                <div class="text-sm text-slate-500">Offline capable</div>
            </div>
        </div>
    </div>
</section>


<!-- ======================================================== -->
<!-- FEATURES -->
<!-- ======================================================== -->
<section id="features" class="py-28" style="background: #f8fafc;">
    <div class="max-w-6xl mx-auto px-6">
        <div class="text-center mb-16">
            <span class="text-xs font-black uppercase tracking-widest" style="color: #3897D3;">Platform Features</span>
            <h2 class="mt-3 text-4xl font-extrabold text-slate-900">Everything you need to craft great docs</h2>
            <p class="mt-4 text-lg text-slate-500 max-w-2xl mx-auto">Built for writers, teams, and thinkers who demand more from their document tools.</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

            <div class="feat-card rounded-2xl p-7 border border-slate-100 shadow-sm">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl mb-5" style="background: rgba(245,193,16,0.12);" aria-hidden="true">✨</div>
                <h3 class="text-base font-bold text-slate-900 mb-2">AI Writing Assistant</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Slash commands, smart completions, tone adjustments, grammar fixes, and summarisation — your writing gets smarter with every keystroke.</p>
            </div>

            <div class="feat-card rounded-2xl p-7 border border-slate-100 shadow-sm">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl mb-5" style="background: rgba(56,151,211,0.10);" aria-hidden="true">👥</div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Real-time Collaboration</h3>
                <p class="text-slate-500 text-sm leading-relaxed">See who's editing, leave inline comments, and track every change. Your whole team, in perfect sync, in real time.</p>
            </div>

            <div class="feat-card rounded-2xl p-7 border border-slate-100 shadow-sm">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl mb-5" style="background: rgba(16,185,129,0.10);" aria-hidden="true">📄</div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Smart Templates</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Start fast with professional templates for proposals, reports, meeting notes and more — or save your own team template.</p>
            </div>

            <div class="feat-card rounded-2xl p-7 border border-slate-100 shadow-sm">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl mb-5" style="background: rgba(139,92,246,0.10);" aria-hidden="true">🕒</div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Version History</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Every save is a snapshot. Browse, compare with a side-by-side diff view, and restore any earlier version at any time.</p>
            </div>

            <div class="feat-card rounded-2xl p-7 border border-slate-100 shadow-sm">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl mb-5" style="background: rgba(251,146,60,0.12);" aria-hidden="true">📶</div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Offline Mode</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Internet gone? Keep writing. dot.doc caches your work locally and background-syncs automatically when you reconnect.</p>
            </div>

            <div class="feat-card rounded-2xl p-7 border border-slate-100 shadow-sm">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl mb-5" style="background: rgba(239,68,68,0.10);" aria-hidden="true">📤</div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Export &amp; Share</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Export to PDF, Word, HTML, or Markdown in one click. Share via public link with optional password &amp; expiry.</p>
            </div>

        </div>
    </div>
</section>


<!-- ======================================================== -->
<!-- HOW IT WORKS -->
<!-- ======================================================== -->
<section id="how-it-works" class="py-28 bg-white">
    <div class="max-w-4xl mx-auto px-6">
        <div class="text-center mb-16">
            <span class="text-xs font-black uppercase tracking-widest" style="color: #F5C110;">How it works</span>
            <h2 class="mt-3 text-4xl font-extrabold text-slate-900">Up and writing in minutes</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-12">
            <div class="text-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl font-black mx-auto mb-6 text-white step-num"
                     style="background: linear-gradient(135deg, #3897D3, #2563eb);">1</div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Create your workspace</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Sign up, create a team, and invite collaborators in under two minutes.</p>
            </div>
            <div class="text-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl font-black mx-auto mb-6 text-slate-900 step-num"
                     style="background: linear-gradient(135deg, #F5C110, #f59e0b); box-shadow: 0 0 20px rgba(245,193,16,0.5);">2</div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Start from a template</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Pick a template or start blank. Let AI help with the heavy lifting from the first word.</p>
            </div>
            <div class="text-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl font-black mx-auto mb-6 text-white step-num"
                     style="background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 0 20px rgba(16,185,129,0.4);">3</div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Collaborate &amp; ship</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Invite your team, edit together in real time, then export or share with a link.</p>
            </div>
        </div>
    </div>
</section>


<!-- ======================================================== -->
<!-- CTA BANNER -->
<!-- ======================================================== -->
<section class="py-24 cta-grad relative overflow-hidden">
    <div class="absolute inset-0 dot-grid opacity-20 pointer-events-none" aria-hidden="true"></div>
    <div class="relative max-w-3xl mx-auto px-6 text-center">
        <h2 class="text-4xl font-black text-white mb-4">Ready to write smarter?</h2>
        <p class="text-lg mb-10 text-white/80">Join teams already creating better documents with dot.doc.</p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            @if (Route::has('register'))
                <a href="{{ route('register') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-9 py-4 bg-white hover:bg-slate-50 font-bold rounded-xl shadow-xl hover:shadow-2xl transition-all text-base"
                   style="color: #0d1528;">
                    Get Started Free
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            @endif
            @if (Route::has('login'))
                @guest
                    <a href="{{ route('login') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center px-9 py-4 border-2 border-white/40 hover:border-white text-white font-semibold rounded-xl transition-all text-base">
                        Log in to your account
                    </a>
                @endguest
            @endif
        </div>
    </div>
</section>


<!-- ======================================================== -->
<!-- FOOTER -->
<!-- ======================================================== -->
<footer class="footer-dark text-slate-500 py-14">
    <div class="max-w-6xl mx-auto px-6">
        <div class="flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="flex items-center gap-3">
                <img src="/dot_doc.png" alt="dot.doc" class="h-8 w-auto opacity-80">
                <span class="text-sm text-slate-600">Write Smarter. Collaborate Faster.</span>
            </div>
            <nav class="flex flex-wrap items-center justify-center gap-6 text-sm" aria-label="Footer navigation">
                <a href="#features"    class="hover:text-white transition-colors">Features</a>
                <a href="#how-it-works" class="hover:text-white transition-colors">How It Works</a>
                @if (Route::has('login'))
                    <a href="{{ route('login') }}"    class="hover:text-white transition-colors">Sign In</a>
                @endif
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="hover:text-white transition-colors">Register</a>
                @endif
            </nav>
            <p class="text-xs text-slate-700">&copy; {{ date('Y') }} dot.doc. All rights reserved.</p>
        </div>
    </div>
</footer>

</body>
</html>
