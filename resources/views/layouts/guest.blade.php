<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'dot.doc') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body { font-family: 'Figtree', sans-serif; }

        /* Left panel dark background */
        .auth-left {
            background-color: #080f1e;
            background-image:
                radial-gradient(ellipse 70% 60% at 80% -5%, rgba(56,151,211,0.28) 0%, transparent 55%),
                radial-gradient(ellipse 60% 55% at -5% 90%, rgba(245,193,16,0.22) 0%, transparent 55%),
                radial-gradient(ellipse 50% 45% at 50% 50%, rgba(139,92,246,0.10) 0%, transparent 60%);
        }

        /* Dot grid */
        .dot-grid {
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.07) 1px, transparent 0);
            background-size: 34px 34px;
        }

        /* Glow blobs */
        .blob-a {
            background: radial-gradient(circle, rgba(245,193,16,0.55) 0%, transparent 70%);
            filter: blur(55px);
        }
        .blob-b {
            background: radial-gradient(circle, rgba(56,151,211,0.50) 0%, transparent 70%);
            filter: blur(65px);
        }

        /* Glass feature card on left panel */
        .glass-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.10);
            backdrop-filter: blur(10px);
        }

        /* Gradient text */
        .grad-text {
            background: linear-gradient(135deg, #F5C110 10%, #f97316 50%, #3897D3 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Right panel - form input focus */
        .form-input:focus {
            border-color: #3897D3 !important;
            box-shadow: 0 0 0 3px rgba(56,151,211,0.15);
            outline: none;
        }

        /* Primary button override */
        .btn-primary {
            background: linear-gradient(135deg, #F5C110, #f59e0b);
            color: #0d1528;
            font-weight: 700;
            border: none;
            transition: filter 0.15s, transform 0.1s;
        }
        .btn-primary:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(56,151,211,0.3); border-radius: 6px; }
    </style>
</head>
<body class="antialiased">
<div class="min-h-screen flex font-sans text-gray-900">

    <!-- ======================================================== -->
    <!-- LEFT PANEL — branding (hidden on mobile)                 -->
    <!-- ======================================================== -->
    <div class="auth-left hidden lg:flex lg:w-[52%] relative flex-col overflow-hidden">

        <!-- Dot grid overlay -->
        <div class="dot-grid absolute inset-0 pointer-events-none" aria-hidden="true"></div>

        <!-- Glow blobs -->
        <div class="blob-a absolute w-80 h-80 -top-16 right-8 opacity-70 pointer-events-none" aria-hidden="true"></div>
        <div class="blob-b absolute w-96 h-96 -bottom-20 -left-12 opacity-60 pointer-events-none" aria-hidden="true"></div>

        <!-- Content -->
        <div class="relative flex flex-col justify-between h-full px-12 py-14">

            <!-- Logo top -->
            <a href="/" class="flex items-center gap-3">
                <img src="/dot_doc.png" alt="dot.doc" class="h-11 w-auto drop-shadow-xl">
            </a>

            <!-- Center copy -->
            <div>
                <h2 class="text-5xl font-black leading-tight text-white mb-5">
                    Your documents,<br>
                    <span class="grad-text">reimagined.</span>
                </h2>
                <p class="text-base leading-relaxed mb-12" style="color: rgba(255,255,255,0.55); max-width: 380px;">
                    AI-powered writing, real-time collaboration, smart version control,
                    and full offline support — all in one place.
                </p>

                <!-- Feature highlights -->
                <div class="space-y-4">
                    <div class="glass-card rounded-xl px-5 py-4 flex items-center gap-4">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 text-lg"
                             style="background: rgba(245,193,16,0.15);">✨</div>
                        <div>
                            <div class="text-sm font-bold text-white">AI Writing Assistant</div>
                            <div class="text-xs mt-0.5" style="color: rgba(255,255,255,0.45);">Grammar, tone, summarisation & slash commands</div>
                        </div>
                    </div>
                    <div class="glass-card rounded-xl px-5 py-4 flex items-center gap-4">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 text-lg"
                             style="background: rgba(56,151,211,0.15);">👥</div>
                        <div>
                            <div class="text-sm font-bold text-white">Real-time Collaboration</div>
                            <div class="text-xs mt-0.5" style="color: rgba(255,255,255,0.45);">Presence channels, live sync &amp; inline comments</div>
                        </div>
                    </div>
                    <div class="glass-card rounded-xl px-5 py-4 flex items-center gap-4">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 text-lg"
                             style="background: rgba(16,185,129,0.15);">📶</div>
                        <div>
                            <div class="text-sm font-bold text-white">Works Offline</div>
                            <div class="text-xs mt-0.5" style="color: rgba(255,255,255,0.45);">Service Worker + IndexedDB background sync</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom tagline -->
            <p class="text-xs" style="color: rgba(255,255,255,0.2);">&copy; {{ date('Y') }} dot.doc — Write Smarter. Collaborate Faster.</p>
        </div>
    </div>


    <!-- ======================================================== -->
    <!-- RIGHT PANEL — form                                        -->
    <!-- ======================================================== -->
    <div class="w-full lg:w-[48%] flex flex-col justify-center bg-white">
        <div class="w-full max-w-sm mx-auto px-8 py-12">
            {{ $slot }}
        </div>
    </div>

</div>

@livewireScripts
</body>
</html>
