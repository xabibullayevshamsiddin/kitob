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
    <!-- Vite Bundled Scripts (Pusher, Echo, Axios) -->
    @vite(['resources/js/app.js'])

    @if(class_exists('Livewire\Livewire'))
        @livewireStyles
    @endif
    @stack('styles')

    <style>        /* Number input spinner tugmalari — dark dizaynga mos (oq tugmachalarni yo'qotish) */
        input[type="number"] {
            -moz-appearance: textfield;
            appearance: textfield;
        }
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }


        [x-cloak] { display: none !important; }

        /* Native Select Custom Dark Styling (Taste-Skill) */
        select {
            background-color: #0c101b !important;
            color: #f1f5f9 !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 0.65rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.25em 1.25em !important;
            padding-right: 2.25rem !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
        }
        select:focus {
            border-color: rgba(245, 158, 11, 0.5) !important;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2) !important;
            outline: none !important;
        }
        select option {
            background-color: #0a0e17 !important;
            color: #f1f5f9 !important;
            padding: 10px 14px !important;
        }

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

        @keyframes slideUp {
            0% { opacity: 0; transform: translateY(16px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .animate-slide-up { animation: slideUp 0.35s ease-out forwards; }

        @keyframes scaleIn {
            0% { opacity: 0; transform: scale(0.92); }
            100% { opacity: 1; transform: scale(1); }
        }
        .animate-scale-in { animation: scaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }

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

        /* Brauzer avtofill (autofill) inputlarni oq bo'ya qo'ymasligi uchun */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active,
        textarea:-webkit-autofill,
        textarea:-webkit-autofill:hover,
        textarea:-webkit-autofill:focus,
        select:-webkit-autofill,
        select:-webkit-autofill:hover,
        select:-webkit-autofill:focus {
            -webkit-text-fill-color: #e2e8f0 !important;
            -webkit-box-shadow: 0 0 0 1000px #1e293b inset !important;
            caret-color: #e2e8f0 !important;
            transition: background-color 9999s ease-in-out 0s !important;
        }
        input[type="checkbox"], input[type="radio"] {
            -webkit-box-shadow: none !important;
        }
    </style>
</head>
<body class="bg-ink-950 text-slate-200 font-sans selection:bg-amber-400 selection:text-ink-950 antialiased min-h-screen relative overflow-x-hidden">

    <!-- ── Ambient Floating Glows ── -->
    <div class="fixed top-[-120px] left-1/2 -translate-x-1/2 w-[950px] h-[500px] bg-gradient-to-b from-amber-500/15 via-indigo-600/10 to-transparent rounded-full blur-[150px] pointer-events-none -z-10 orb-animate-1"></div>
    <div class="fixed bottom-[-100px] right-[-80px] w-[650px] h-[650px] bg-indigo-900/15 rounded-full blur-[160px] pointer-events-none -z-10 orb-animate-2"></div>

    <!-- ── Universal Header ── -->
    <x-nav.main-header />

    <!-- ── Flash Messages & Ban Alert ── -->
    <div class="max-w-7xl mx-auto px-6">
        @if (auth()->check() && auth()->user()->isBanned())
            <div class="mt-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 bg-red-950/40 border border-red-500/40 text-red-200 rounded-2xl shadow-lg shadow-red-950/30">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-500/20 text-red-400 flex items-center justify-center flex-shrink-0 text-xl font-bold">
                        ⚠️
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-red-300">Sizning profilingiz cheklangan (Bloklangan)</h4>
                        <p class="text-xs text-red-200/80 mt-0.5">
                            Qoidabuzarlik tufayli chat va jamoaviy bo'limlarda xabar yuborish huquqingiz cheklangan.
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-3 text-xs">
                            <span class="px-2.5 py-0.5 rounded-md bg-red-900/60 border border-red-700/50 font-mono text-red-200 font-semibold">
                                Qolgan muddat: {{ auth()->user()->ban_remaining }}
                            </span>
                            @if(auth()->user()->ban_reason)
                                <span class="text-red-300/90 italic">
                                    Sabab: "{{ auth()->user()->ban_reason }}"
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <a href="{{ route('contact') }}" class="px-3.5 py-2 rounded-xl bg-red-500/20 hover:bg-red-500/30 border border-red-500/40 text-xs font-semibold text-red-200 transition-colors flex-shrink-0 self-end sm:self-center">
                    Bog'lanish
                </a>
            </div>
        @endif
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
        <div class="max-w-7xl mx-auto">
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
