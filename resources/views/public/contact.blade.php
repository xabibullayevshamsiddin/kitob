<!DOCTYPE html>
<html lang="uz" class="scroll-smooth dark" x-data="{ darkMode: true, mobileMenu: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kitobxon bilan bog'laning. Savollar, takliflar va hamkorlik uchun aloqa formasi.">
    <title>Aloqa — Kitobxon</title>

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
        .spotlight-card {
            background: rgba(17, 23, 38, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.25s ease;
        }
        .spotlight-card:hover {
            border-color: rgba(251, 191, 36, 0.3);
            box-shadow: 0 16px 36px -12px rgba(0, 0, 0, 0.6), 0 0 20px -2px rgba(251, 191, 36, 0.1);
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
                <a href="{{ route('books.public') }}" class="hover:text-amber-400 transition-colors">Kitoblar</a>
                <a href="{{ route('about') }}" class="hover:text-amber-400 transition-colors">Biz haqimizda</a>
                <a href="{{ route('faq') }}" class="hover:text-amber-400 transition-colors">FAQ</a>
                <a href="{{ route('contact') }}" class="text-amber-400 font-semibold">Aloqa</a>
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
            <a href="{{ route('books.public') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Kitoblar</a>
            <a href="{{ route('about') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Biz haqimizda</a>
            <a href="{{ route('faq') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">FAQ</a>
            <a href="{{ route('contact') }}" class="block text-sm py-2 text-amber-400 font-semibold">Aloqa</a>
        </div>
    </header>

    <!-- ── Content ── -->
    <main class="py-16 md:py-24 noise-bg">
        <div class="max-w-7xl mx-auto px-6">
            
            <div class="max-w-2xl mb-12">
                <span class="text-xs font-mono text-amber-400 uppercase tracking-widest block mb-2">✦ BIZ BILAN BOG'LANING</span>
                <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    Savol yoki taklifingiz bormi? <br>
                    <span class="text-amber-400 italic font-serif">Biz doim aloqadamiz.</span>
                </h1>
                <p class="text-slate-400 text-sm sm:text-base mt-3">
                    Mutolaa jarayoni, guruhlar yoki hamkorlik masalalarida xabar qoldiring. 24 soat ichida javob beramiz.
                </p>
            </div>

            <!-- Form + Info Split Grid (50/50) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Contact Form (Col 7) -->
                <div class="lg:col-span-7 spotlight-card rounded-3xl p-8 sm:p-10">
                    
                    @if(session('success'))
                        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-3">
                            <span class="text-lg">✓</span>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    <form action="{{ route('contact.send') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 font-mono mb-2">Ismingiz *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required 
                                       placeholder="Ali Valiyev"
                                       class="w-full px-4 py-3 rounded-xl bg-ink-950/80 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-400 transition-colors">
                                @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 font-mono mb-2">Elektron pochta *</label>
                                <input type="email" name="email" value="{{ old('email') }}" required 
                                       placeholder="ali@misol.uz"
                                       class="w-full px-4 py-3 rounded-xl bg-ink-950/80 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-400 transition-colors">
                                @error('email') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 font-mono mb-2">Mavzu *</label>
                            <input type="text" name="subject" value="{{ old('subject') }}" required 
                                   placeholder="Qaysi mavzuda murojaat qilmoqchisiz?"
                                   class="w-full px-4 py-3 rounded-xl bg-ink-950/80 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-400 transition-colors">
                            @error('subject') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 font-mono mb-2">Xabaringiz *</label>
                            <textarea name="message" rows="5" required 
                                      placeholder="Fikringiz yoki savolingizni batafsil yozing..."
                                      class="w-full px-4 py-3 rounded-xl bg-ink-950/80 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-400 transition-colors resize-none">{{ old('message') }}</textarea>
                            @error('message') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" 
                                class="w-full py-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider transition-all duration-200 active:scale-[0.98] shadow-lg shadow-amber-500/25 flex items-center justify-center gap-2">
                            <span>Xabarni yuborish</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>
                </div>

                <!-- Info Cards (Col 5) -->
                <div class="lg:col-span-5 space-y-6">
                    
                    <div class="spotlight-card rounded-3xl p-6 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-400/10 border border-amber-400/20 text-amber-400 flex items-center justify-center text-2xl shrink-0">
                            💬
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Telegram orqali tezkor aloqa</h3>
                            <p class="text-xs text-slate-400 mt-1">Savollaringizga bot va qo'llab-quvvatlash guruhi orqali 10 daqiqada javob oling.</p>
                            <a href="https://t.me/" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-amber-400 font-semibold mt-3 hover:underline">
                                @kitobxon_support →
                            </a>
                        </div>
                    </div>

                    <div class="spotlight-card rounded-3xl p-6 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-2xl shrink-0">
                            ✉️
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Elektron pochta</h3>
                            <p class="text-xs text-slate-400 mt-1">Rasmiy takliflar va hamkorlik loyihalari uchun.</p>
                            <p class="text-xs font-mono text-slate-200 mt-2">info@kitobxon.uz</p>
                        </div>
                    </div>

                    <div class="spotlight-card rounded-3xl p-6 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center text-2xl shrink-0">
                            📍
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Bosh ofis</h3>
                            <p class="text-xs text-slate-400 mt-1">O'zbekiston, Toshkent shahri, IT Park hududi.</p>
                            <p class="text-[11px] text-slate-500 mt-2 font-mono">Dush — Juma: 09:00 — 18:00</p>
                        </div>
                    </div>

                </div>

            </div>

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
                <a href="{{ route('books.public') }}" class="hover:text-slate-300 transition-colors">Kitoblar</a>
                <a href="{{ route('about') }}" class="hover:text-slate-300 transition-colors">Biz haqimizda</a>
                <a href="{{ route('faq') }}" class="hover:text-slate-300 transition-colors">FAQ</a>
            </div>
        </div>
    </footer>

</body>
</html>
