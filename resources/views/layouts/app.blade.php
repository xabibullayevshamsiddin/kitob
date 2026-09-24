<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark scroll-smooth" x-data="{ mobileMenu: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Kitobxon') }} — @yield('title', "Kitob o'qish platformasi")</title>

    <!-- Tailwind CSS Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'ui-sans-serif', 'system-ui'],
                        mono: ['JetBrains Mono', 'ui-monospace'],
                        serif: ['Merriweather', 'Georgia', 'serif'],
                    },
                    colors: {
                        ink: {
                            950: '#06080d',
                            900: '#0a0e17',
                            800: '#111726',
                            700: '#1a2236',
                            600: '#25304c',
                        },
                        amber: {
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                        },
                        primary: {
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc',
                            400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca',
                            800: '#3730a3', 900: '#1e1b4b', 950: '#0f0e2e'
                        },
                        accent: { 400: '#fbbf24', 500: '#f59e0b', 600: '#d97706' },
                        surface: { DEFAULT: '#ffffff', dark: '#0f172a', 'dark-card': '#1e293b' },
                    },
                    boxShadow: {
                        'spotlight': '0 0 50px -10px rgba(245, 158, 11, 0.25)',
                        'card-depth': '0 25px 50px -12px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.08)',
                        'glow-amber': '0 0 35px -5px rgba(245, 158, 11, 0.35)',
                    },
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Merriweather:ital,wght@0,300;0,400;0,700;1,300&display=swap" rel="stylesheet">

    <!-- Alpine.js + Plugins (Collapse must load before Alpine starts) -->
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @if(class_exists('Livewire\Livewire'))
        @livewireStyles
    @endif
    @stack('styles')

    <style>
        [x-cloak] { display: none !important; }

        /* Anti-slop micro texture overlay */
        .noise-bg {
            background-image: radial-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 0);
            background-size: 24px 24px;
        }

        /* One-time content entrance */
        @keyframes contentRise {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .page-enter { animation: contentRise 0.3s ease-out; }

        /* Ambient floating orbs */
        @keyframes orbDrift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -20px) scale(1.08); }
        }
        .orb-animate-1 { animation: orbDrift 14s ease-in-out infinite; }
        .orb-animate-2 { animation: orbDrift 18s ease-in-out infinite reverse; }

        /* Slim custom scrollbar for dark chrome */
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: #06080d; }
        ::-webkit-scrollbar-thumb { background: #1a2236; border-radius: 8px; border: 2px solid #06080d; }
        ::-webkit-scrollbar-thumb:hover { background: #25304c; }
    </style>
</head>
<body class="bg-ink-950 text-slate-200 font-sans selection:bg-amber-400 selection:text-ink-950 antialiased min-h-screen relative overflow-x-hidden">

    <!-- ── Ambient Floating Glows ── -->
    <div class="fixed top-[-120px] left-1/2 -translate-x-1/2 w-[950px] h-[500px] bg-gradient-to-b from-amber-500/15 via-indigo-600/10 to-transparent rounded-full blur-[150px] pointer-events-none -z-10 orb-animate-1"></div>
    <div class="fixed bottom-[-100px] right-[-80px] w-[650px] h-[650px] bg-indigo-900/15 rounded-full blur-[160px] pointer-events-none -z-10 orb-animate-2"></div>

    <!-- ── Universal Header ── -->
    <x-nav.main-header />

    <!-- ── Flash Messages ── -->
    <div class="max-w-7xl mx-auto px-6">
        @if (session()->has('success'))
            <div class="mt-6 flex items-center justify-between p-4 bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 rounded-2xl">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mt-6 flex items-center justify-between p-4 bg-rose-500/10 border border-rose-500/25 text-rose-400 rounded-2xl">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- ── Page Content ── -->
    <main class="relative pt-10 pb-20 px-4 sm:px-6 noise-bg min-h-[calc(100vh-72px)]">
        <div class="max-w-7xl mx-auto page-enter">
            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </main>

    <!-- ── Footer ── -->
    <footer class="border-t border-white/[0.07] py-6">
        <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-slate-500">© {{ date('Y') }} Kitobxon. Barcha huquqlar himoyalangan.</p>
            <div class="flex items-center gap-5 text-xs text-slate-500">
                <a href="{{ route('books.public') }}" class="hover:text-amber-400 transition-colors">Kitoblar</a>
                <a href="{{ route('faq') }}" class="hover:text-amber-400 transition-colors">FAQ</a>
                <a href="{{ route('contact') }}" class="hover:text-amber-400 transition-colors">Aloqa</a>
            </div>
        </div>
    </footer>

    @if(class_exists('Livewire\Livewire'))
        @livewireScripts
    @endif
    @stack('scripts')
</body>
</html>
