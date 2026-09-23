<!DOCTYPE html>
<html lang="uz" class="scroll-smooth" x-data="{ mobileMenu: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kitobxon — @yield('title', 'Boshqaruv paneli')</title>

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
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
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

        /* Pro Spotlight Card with dynamic cursor glow */
        .spotlight-card {
            position: relative;
            background: rgba(14, 20, 33, 0.75);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.5rem;
            overflow: hidden;
            transition: border-color 0.4s ease, box-shadow 0.4s ease, transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: transform;
        }
        .spotlight-card::before {
            content: '';
            position: absolute;
            top: var(--mouse-y, -1000px);
            left: var(--mouse-x, -1000px);
            width: 380px;
            height: 380px;
            background: radial-gradient(circle, rgba(251, 191, 36, 0.15) 0%, transparent 70%);
            transform: translate(-50%, -50%);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }
        .spotlight-card:hover::before { opacity: 1; }
        .spotlight-card:hover {
            border-color: rgba(251, 191, 36, 0.4);
            transform: translateY(-6px);
            box-shadow: 0 25px 50px -15px rgba(0, 0, 0, 0.75), 0 0 30px -5px rgba(251, 191, 36, 0.2);
        }

        /* Ambient floating orbs */
        @keyframes orbDrift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -20px) scale(1.08); }
        }
        .orb-animate-1 { animation: orbDrift 14s ease-in-out infinite; }
        .orb-animate-2 { animation: orbDrift 18s ease-in-out infinite reverse; }
    </style>
</head>
<body class="bg-ink-950 text-slate-200 font-sans selection:bg-amber-400 selection:text-ink-950 antialiased min-h-screen relative overflow-x-hidden">

    <!-- ── Ambient Floating Glows ── -->
    <div class="fixed top-[-120px] left-1/2 -translate-x-1/2 w-[950px] h-[500px] bg-gradient-to-b from-amber-500/15 via-indigo-600/10 to-transparent rounded-full blur-[150px] pointer-events-none -z-10 orb-animate-1"></div>
    <div class="fixed bottom-[-100px] right-[-80px] w-[650px] h-[650px] bg-indigo-900/15 rounded-full blur-[160px] pointer-events-none -z-10 orb-animate-2"></div>

    <!-- ── Header Navigation ── -->
    <header class="sticky top-0 z-50 w-full backdrop-blur-xl bg-ink-950/80 border-b border-white/[0.07]">
        <div class="max-w-7xl mx-auto px-6 h-[72px] flex items-center justify-between">

            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-ink-950 font-black text-xl shadow-lg shadow-amber-500/25 group-hover:scale-105 group-hover:rotate-3 transition-all duration-300">
                    📖
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-white text-base tracking-tight group-hover:text-amber-400 transition-colors">Kitobxon</span>
                    <span class="font-mono text-[10px] text-slate-400 uppercase tracking-widest">Platforma</span>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-7 text-sm font-medium text-slate-300">
                <a href="{{ route('books.catalog') }}" class="hover:text-amber-400 transition-colors">Katalog</a>
                <a href="{{ route('chat') }}" class="hover:text-amber-400 transition-colors">Chat</a>
                <a href="{{ route('groups.index') }}" class="hover:text-amber-400 transition-colors">Guruhlar</a>
                <a href="{{ route('live.index') }}" class="hover:text-amber-400 transition-colors">Live</a>
                <a href="{{ route('leaderboard') }}" class="hover:text-amber-400 transition-colors">Reyting</a>
            </nav>

            <div class="flex items-center gap-3">
                @auth
                    <!-- Streak / Points / Coins -->
                    <div class="hidden lg:flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-400/10 border border-amber-400/25 text-amber-400 font-mono text-xs font-bold" title="Kunlik ketma-ketlik">
                            🔥 {{ auth()->user()->current_streak }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-slate-200 font-mono text-xs font-bold" title="Ballar">
                            ⭐️ {{ number_format(auth()->user()->total_points) }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-slate-200 font-mono text-xs font-bold" title="Tangalar">
                            🪙 {{ number_format(auth()->user()->coin_balance) }}
                        </span>
                    </div>

                    <!-- Notifications -->
                    <a href="{{ route('notifications') }}" class="relative p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 transition-colors" title="Bildirishnomalar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-ink-950"></span>
                        @endif
                    </a>

                    <!-- Avatar Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 focus:outline-none group">
                            <img src="{{ auth()->user()->avatar_url }}" class="w-9 h-9 rounded-xl object-cover ring-2 ring-amber-500/30 group-hover:ring-amber-400/60 transition-all">
                        </button>
                        <div x-show="open" x-cloak @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 rounded-2xl bg-ink-900/95 backdrop-blur-xl border border-white/10 shadow-card-depth py-1.5 z-50 overflow-hidden">
                            <div class="px-4 py-2.5 border-b border-white/10">
                                <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</p>
                                <p class="text-[10px] font-mono text-slate-500 truncate">{{ '@' . auth()->user()->username }}</p>
                            </div>
                            <a href="{{ route('profile.show', auth()->user()->username) }}" class="block px-4 py-2 text-xs text-slate-300 hover:bg-white/5 hover:text-amber-400 transition-colors">Profilim</a>
                            <a href="{{ route('settings') }}" class="block px-4 py-2 text-xs text-slate-300 hover:bg-white/5 hover:text-amber-400 transition-colors">Sozlamalar</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-xs text-rose-400 hover:bg-rose-500/10 transition-colors">Tizimdan chiqish</button>
                            </form>
                        </div>
                    </div>
                @endauth

                <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-lg text-slate-400 hover:text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenu" x-cloak @click.away="mobileMenu = false" class="md:hidden border-b border-white/10 bg-ink-900/95 px-6 py-4 space-y-3">
            <a href="{{ route('books.catalog') }}" @click="mobileMenu = false" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Katalog</a>
            <a href="{{ route('chat') }}" @click="mobileMenu = false" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Chat</a>
            <a href="{{ route('groups.index') }}" @click="mobileMenu = false" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Guruhlar</a>
            <a href="{{ route('live.index') }}" @click="mobileMenu = false" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Live</a>
            <a href="{{ route('leaderboard') }}" @click="mobileMenu = false" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Reyting</a>
        </div>
    </header>

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
    <main class="relative pt-10 pb-20 px-6 noise-bg min-h-[calc(100vh-72px)]">
        <div class="max-w-7xl mx-auto">
            {{ $slot }}
        </div>
    </main>

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
