<!DOCTYPE html>
<html lang="uz" class="scroll-smooth dark" x-data="{ darkMode: true, mobileMenu: false, activeGenre: 'all' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kitobxon — Barcha sara kitoblar ro'yxati. Matn, audio va video sharhlar.">
    <title>Kitoblar Katalogi — Kitobxon</title>

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
                            950: '#07090e',
                            900: '#0b0f17',
                            800: '#111726',
                            700: '#1a2236',
                        },
                        amber: {
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                        },
                    },
                    boxShadow: {
                        'card-depth': '0 20px 40px -15px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.07)',
                    },
                    animation: {
                        'pulse-glow': 'pulseGlow 4s ease-in-out infinite',
                    },
                    keyframes: {
                        pulseGlow: {
                            '0%, 100%': { opacity: '0.3', transform: 'scale(1)' },
                            '50%': { opacity: '0.6', transform: 'scale(1.05)' },
                        }
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .noise-bg {
            background-image: radial-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 0);
            background-size: 24px 24px;
        }
        .book-card {
            background: rgba(17, 23, 38, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.25s ease;
        }
        .book-card:hover {
            border-color: rgba(251, 191, 36, 0.35);
            transform: translateY(-4px);
            box-shadow: 0 20px 36px -12px rgba(0, 0, 0, 0.6), 0 0 20px -2px rgba(251, 191, 36, 0.1);
        }
    </style>
</head>
<body class="bg-ink-950 text-slate-200 font-sans selection:bg-amber-400 selection:text-ink-950 antialiased min-h-screen relative overflow-x-hidden">

    <!-- Ambient Glow -->
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[450px] bg-gradient-to-b from-amber-500/10 via-indigo-600/5 to-transparent rounded-full blur-[120px] pointer-events-none -z-10 animate-pulse-glow"></div>

    <!-- ── Header ── -->
    <header class="sticky top-0 z-50 w-full backdrop-blur-xl bg-ink-950/80 border-b border-white/[0.07]">
        <div class="max-w-7xl mx-auto px-6 h-[70px] flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-ink-950 font-black text-xl shadow-lg shadow-amber-500/20 group-hover:scale-105 transition-transform">
                    📖
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-white text-base tracking-tight group-hover:text-amber-400 transition-colors">Kitobxon</span>
                    <span class="font-mono text-[10px] text-slate-400 uppercase tracking-widest">Platforma</span>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-300">
                <a href="{{ route('home') }}" class="hover:text-amber-400 transition-colors">Bosh sahifa</a>
                <a href="{{ route('books.public') }}" class="text-amber-400 font-semibold">Kitoblar</a>
                <a href="{{ route('about') }}" class="hover:text-amber-400 transition-colors">Biz haqimizda</a>
                <a href="{{ route('faq') }}" class="hover:text-amber-400 transition-colors">FAQ</a>
                <a href="{{ route('contact') }}" class="hover:text-amber-400 transition-colors">Aloqa</a>
            </nav>

            <div class="hidden sm:flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-amber-500 text-ink-950 font-bold text-xs uppercase tracking-wider hover:bg-amber-400 active:scale-95 transition-all shadow-md">
                        Boshqaruv paneli →
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-slate-300 hover:text-white transition-colors">Kirish</a>
                    <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-xl bg-white text-ink-950 font-bold text-xs uppercase tracking-wider hover:bg-slate-200 active:scale-95 transition-all shadow-md">Ro'yxatdan o'tish</a>
                @endauth
            </div>

            <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-lg text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenu" x-cloak @click.away="mobileMenu = false" class="md:hidden border-b border-white/10 bg-ink-900/95 px-6 py-4 space-y-3">
            <a href="{{ route('home') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Bosh sahifa</a>
            <a href="{{ route('books.public') }}" class="block text-sm py-2 text-amber-400 font-semibold">Kitoblar</a>
            <a href="{{ route('about') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Biz haqimizda</a>
            <a href="{{ route('faq') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">FAQ</a>
            <a href="{{ route('contact') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Aloqa</a>
        </div>
    </header>

    <!-- ── Content ── -->
    <main class="py-16 md:py-24 noise-bg">
        <div class="max-w-7xl mx-auto px-6 space-y-12">
            
            <div class="max-w-2xl">
                <span class="text-xs font-mono text-amber-400 uppercase tracking-widest block mb-2">✦ HAFTALIK MUTOLAA</span>
                <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    Sara kitoblar <span class="text-amber-400 italic font-serif">katalogi</span>.
                </h1>
                <p class="text-slate-400 text-sm sm:text-base mt-2">
                    Har bir asar ekspertlarimiz tomonidan chuqur tahlil qilingan va audio/video shaklida boyitilgan.
                </p>
            </div>

            <!-- Books Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                
                @forelse($books as $book)
                    <div class="book-card rounded-3xl p-5 flex flex-col justify-between group">
                        <div>
                            <!-- Cover preview -->
                            <div class="w-full h-56 rounded-2xl overflow-hidden bg-gradient-to-br from-indigo-950 via-ink-900 to-slate-900 border border-white/10 flex flex-col justify-between p-4 relative mb-4">
                                <span class="text-xs font-mono text-amber-400 font-bold">{{ $book->week_number }}-hafta</span>
                                <div>
                                    <h3 class="text-lg font-bold text-white leading-snug line-clamp-2">{{ $book->title }}</h3>
                                    <p class="text-xs text-slate-400 mt-1">{{ $book->author }}</p>
                                </div>
                                <div class="flex items-center gap-1.5 text-[10px] font-mono text-slate-400">
                                    <span>📖 Matn</span> • <span>🎧 Audio</span> • <span>🎥 Video</span>
                                </div>
                            </div>

                            <span class="inline-block px-2.5 py-1 rounded-lg bg-amber-400/10 text-amber-400 font-mono text-[11px] mb-2">
                                {{ $book->genre }}
                            </span>

                            <p class="text-xs text-slate-400 line-clamp-3 leading-relaxed">
                                {{ $book->description }}
                            </p>
                        </div>

                        <div class="pt-4 border-t border-white/5 mt-4 flex items-center justify-between">
                            <span class="text-[11px] text-slate-500 font-mono">Sara asar</span>
                            @auth
                                <a href="{{ route('books.show', $book->slug) }}" 
                                   class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider transition-all active:scale-[0.98]">
                                    O'qish →
                                </a>
                            @else
                                <a href="{{ route('login') }}" 
                                   class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs transition-all">
                                    Kirish
                                </a>
                            @endauth
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-12 rounded-3xl bg-ink-900 border border-white/10 text-center">
                        <span class="text-4xl">📚</span>
                        <p class="text-slate-400 text-sm mt-3">Hozirda kitoblar ro'yxati yangilanmoqda.</p>
                    </div>
                @endforelse

            </div>

            @if($books->hasPages())
                <div class="pt-8">
                    {{ $books->links() }}
                </div>
            @endif

        </div>
    </main>

    <!-- ── Footer ── -->
    <footer class="border-t border-white/[0.07] bg-ink-950 py-10 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <span class="font-bold text-white text-sm">Kitobxon</span>
                <span>•</span>
                <span>© {{ date('Y') }} Barcha huquqlar himoyalangan</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}" class="hover:text-slate-300 transition-colors">Bosh sahifa</a>
                <a href="{{ route('about') }}" class="hover:text-slate-300 transition-colors">Biz haqimizda</a>
                <a href="{{ route('faq') }}" class="hover:text-slate-300 transition-colors">FAQ</a>
                <a href="{{ route('contact') }}" class="hover:text-slate-300 transition-colors">Aloqa</a>
            </div>
        </div>
    </footer>

</body>
</html>
