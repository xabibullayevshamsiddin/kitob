<!DOCTYPE html>
<html lang="uz" class="scroll-smooth dark" x-data="{ darkMode: true, mobileMenu: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kitobxon haqida — Bizning missiya, qadriyatlar va jamoa.">
    <title>Biz haqimizda — Kitobxon</title>

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
                        'float-soft': 'floatSoft 6s ease-in-out infinite',
                    },
                    keyframes: {
                        pulseGlow: {
                            '0%, 100%': { opacity: '0.3', transform: 'scale(1)' },
                            '50%': { opacity: '0.6', transform: 'scale(1.05)' },
                        },
                        floatSoft: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-6px)' },
                        }
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
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
            transform: translateY(-3px);
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
                <a href="{{ route('about') }}" class="text-amber-400 font-semibold">Biz haqimizda</a>
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
            <a href="{{ route('books.public') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Kitoblar</a>
            <a href="{{ route('about') }}" class="block text-sm py-2 text-amber-400 font-semibold">Biz haqimizda</a>
            <a href="{{ route('faq') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">FAQ</a>
            <a href="{{ route('contact') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Aloqa</a>
        </div>
    </header>

    <!-- ── Hero Section (Editorial Title & Mission) ── -->
    <section class="py-16 md:py-24 noise-bg">
        <div class="max-w-5xl mx-auto px-6 text-center space-y-6">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-400/10 border border-amber-400/20 text-amber-400 font-mono text-xs tracking-wider">
                <span>✦ BIZNING MISSIYAMIZ</span>
            </div>
            <h1 class="text-4xl sm:text-6xl font-extrabold text-white tracking-tight leading-[1.08]">
                Har bir inson uchun <br class="hidden sm:block">
                <span class="text-amber-400 italic font-serif">intellektual o'sish</span> madaniyatini yaratish.
            </h1>
            <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto leading-relaxed">
                Kitobxon — shunchaki kitob o'qish ilovasi emas, balki qisqa vaqt ichida chuqur bilim olish, kunlik odat shakllantirish va fikrdoshlar bilan uchrashish maskani.
            </p>
        </div>
    </section>

    <!-- ── Key Stats Row (High Contrast Tiles) ── -->
    <section class="py-12 border-y border-white/[0.07] bg-ink-900/40">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="p-6 rounded-2xl bg-ink-950/60 border border-white/10">
                    <p class="text-3xl sm:text-4xl font-black text-amber-400 font-mono">52+</p>
                    <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-mono">Yillik kitoblar</p>
                </div>
                <div class="p-6 rounded-2xl bg-ink-950/60 border border-white/10">
                    <p class="text-3xl sm:text-4xl font-black text-white font-mono">1,200+</p>
                    <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-mono">Faol a'zolar</p>
                </div>
                <div class="p-6 rounded-2xl bg-ink-950/60 border border-white/10">
                    <p class="text-3xl sm:text-4xl font-black text-amber-400 font-mono">3 xil</p>
                    <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-mono">Matn, Audio, Video</p>
                </div>
                <div class="p-6 rounded-2xl bg-ink-950/60 border border-white/10">
                    <p class="text-3xl sm:text-4xl font-black text-white font-mono">98%</p>
                    <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-mono">Odat shakllanishi</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Core Values (Asymmetric 3-Card Grid) ── -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-6 space-y-12">
            <div>
                <h2 class="text-3xl font-extrabold text-white tracking-tight">Qadriyatlarimiz</h2>
                <p class="text-sm text-slate-400 mt-1">Biz qaysi tamoyillarga sodiqmiz?</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="spotlight-card rounded-3xl p-8 space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-400/10 border border-amber-400/20 text-amber-400 flex items-center justify-center text-2xl">
                        💎
                    </div>
                    <h3 class="text-xl font-bold text-white">Faqat sara asarlar</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Vaqtingizni behuda sarflamaymiz. Faqat dunyo miqyosida o'z isbotini topgan, amaliy natija beradigan asarlar tanlanadi.
                    </p>
                </div>

                <div class="spotlight-card rounded-3xl p-8 space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-2xl">
                        ⚡️
                    </div>
                    <h3 class="text-xl font-bold text-white">Doimiylik va Streak</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Haftasiga 100 bet o'qib charchagandan ko'ra, har kuni 15 daqiqa mutolaa qilish miyada yangi neyron aloqalarini barpo etadi.
                    </p>
                </div>

                <div class="spotlight-card rounded-3xl p-8 space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center text-2xl">
                        🤝
                    </div>
                    <h3 class="text-xl font-bold text-white">Kuchli hamjamiyat</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Yolg'iz o'qishdan ko'ra birgalikda o'rganish 5 barobar samaraliroq. Har hafta ustozlar va qiziquvchilar bilan jonli fikr almashing.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Team Section (Clean Profile Badges) ── -->
    <section class="py-20 border-t border-white/[0.07] bg-ink-900/30">
        <div class="max-w-7xl mx-auto px-6 space-y-12">
            <div>
                <h2 class="text-3xl font-extrabold text-white tracking-tight">Ustozlar va Ekspertlar</h2>
                <p class="text-sm text-slate-400 mt-1">Platformada tahlillarni olib boruvchi jamoa</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <div class="spotlight-card rounded-3xl p-6 flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-ink-950 font-black text-xl shrink-0">
                        XS
                    </div>
                    <div>
                        <h4 class="font-bold text-white text-base">Xabibullayev Shamsiddin</h4>
                        <p class="text-xs text-amber-400 font-mono">Bosh Arxitektor & Muallif</p>
                        <p class="text-[11px] text-slate-400 mt-1">Platforma asoschisi va rahbar dasturchi</p>
                    </div>
                </div>

                <div class="spotlight-card rounded-3xl p-6 flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-700 flex items-center justify-center text-white font-bold text-xl shrink-0">
                        DK
                    </div>
                    <div>
                        <h4 class="font-bold text-white text-base">Dilshod Karimov</h4>
                        <p class="text-xs text-indigo-300 font-mono">Adabiy Muharrir</p>
                        <p class="text-[11px] text-slate-400 mt-1">Kitoblar tahlilchisi va tarjimon</p>
                    </div>
                </div>

                <div class="spotlight-card rounded-3xl p-6 flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-700 flex items-center justify-center text-white font-bold text-xl shrink-0">
                        NR
                    </div>
                    <div>
                        <h4 class="font-bold text-white text-base">Nodira Rahimova</h4>
                        <p class="text-xs text-emerald-300 font-mono">Audio & Diktant koordinatori</p>
                        <p class="text-[11px] text-slate-400 mt-1">Professional audio kitoblar ijrochisi</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

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
                <a href="{{ route('faq') }}" class="hover:text-slate-300 transition-colors">FAQ</a>
                <a href="{{ route('contact') }}" class="hover:text-slate-300 transition-colors">Aloqa</a>
            </div>
        </div>
    </footer>

</body>
</html>
