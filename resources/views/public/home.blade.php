<!DOCTYPE html>
<html lang="uz" class="scroll-smooth dark" x-data="{ darkMode: true, mobileMenu: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kitobxon — Har hafta bitta sara kitob, chuqur tahlil va gamifikatsiya. O'zbekistondagi eng ilg'or kitobxonlar ekotizimi.">
    <title>Kitobxon — Har hafta bitta kitob, cheksiz bilim</title>

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
                            600: '#26314d',
                        },
                        amber: {
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                        },
                        accent: '#4f46e5',
                    },
                    boxShadow: {
                        'spotlight': '0 0 50px -10px rgba(245, 158, 11, 0.15)',
                        'card-depth': '0 20px 40px -15px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.07)',
                    },
                    animation: {
                        'pulse-glow': 'pulseGlow 4s ease-in-out infinite',
                        'float-soft': 'floatSoft 6s ease-in-out infinite',
                    },
                    keyframes: {
                        pulseGlow: {
                            '0%, 100%': { opacity: '0.4', transform: 'scale(1)' },
                            '50%': { opacity: '0.7', transform: 'scale(1.05)' },
                        },
                        floatSoft: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-8px)' },
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

        /* Anti-slop micro noise texture overlay */
        .noise-bg {
            background-image: radial-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 0);
            background-size: 24px 24px;
        }

        /* 3D card tilt & spotlight effect */
        .spotlight-card {
            position: relative;
            background: rgba(17, 23, 38, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: border-color 0.3s ease, transform 0.25s ease, box-shadow 0.3s ease;
        }
        .spotlight-card:hover {
            border-color: rgba(251, 191, 36, 0.3);
            transform: translateY(-3px);
            box-shadow: 0 16px 36px -12px rgba(0, 0, 0, 0.6), 0 0 20px -2px rgba(251, 191, 36, 0.1);
        }

        /* Subtle audio visualizer bars */
        .bar-anim:nth-child(1) { animation: bounceBar 1.2s infinite ease-in-out; }
        .bar-anim:nth-child(2) { animation: bounceBar 0.9s infinite ease-in-out 0.2s; }
        .bar-anim:nth-child(3) { animation: bounceBar 1.4s infinite ease-in-out 0.4s; }
        .bar-anim:nth-child(4) { animation: bounceBar 1.1s infinite ease-in-out 0.1s; }
        .bar-anim:nth-child(5) { animation: bounceBar 0.8s infinite ease-in-out 0.3s; }
        @keyframes bounceBar {
            0%, 100% { height: 6px; }
            50% { height: 22px; }
        }
    </style>
</head>
<body class="bg-ink-950 text-slate-200 font-sans selection:bg-amber-400 selection:text-ink-950 antialiased min-h-screen relative overflow-x-hidden">

    <!-- ── Ambient Glow Backdrops (Restrained, non-purple) ── -->
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[450px] bg-gradient-to-b from-amber-500/10 via-indigo-600/5 to-transparent rounded-full blur-[120px] pointer-events-none -z-10 animate-pulse-glow"></div>
    <div class="fixed -bottom-40 right-0 w-[600px] h-[600px] bg-indigo-900/10 rounded-full blur-[140px] pointer-events-none -z-10"></div>

    <!-- ── Header / Top Navigation (Strict single line desktop, height 70px) ── -->
    <header class="sticky top-0 z-50 w-full backdrop-blur-xl bg-ink-950/80 border-b border-white/[0.07]">
        <div class="max-w-7xl mx-auto px-6 h-[70px] flex items-center justify-between">
            
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-ink-950 font-black text-xl shadow-lg shadow-amber-500/20 group-hover:scale-105 transition-transform">
                    📖
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-white text-base tracking-tight group-hover:text-amber-400 transition-colors">Kitobxon</span>
                    <span class="font-mono text-[10px] text-slate-400 uppercase tracking-widest">Platforma</span>
                </div>
            </a>

            <!-- Desktop Nav Items -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-300">
                <a href="#features" class="hover:text-amber-400 transition-colors">Imkoniyatlar</a>
                <a href="{{ route('books.public') }}" class="hover:text-amber-400 transition-colors">Kitoblar</a>
                <a href="{{ route('about') }}" class="hover:text-amber-400 transition-colors">Biz haqimizda</a>
                <a href="{{ route('faq') }}" class="hover:text-amber-400 transition-colors">FAQ</a>
                <a href="{{ route('contact') }}" class="hover:text-amber-400 transition-colors">Aloqa</a>
            </nav>

            <!-- Auth Buttons -->
            <div class="hidden sm:flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-amber-500 text-ink-950 font-bold text-xs uppercase tracking-wider hover:bg-amber-400 active:scale-95 transition-all shadow-md shadow-amber-500/20">
                        Boshqaruv paneli →
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-slate-300 hover:text-white transition-colors">
                        Kirish
                    </a>
                    <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-xl bg-white text-ink-950 font-bold text-xs uppercase tracking-wider hover:bg-slate-200 active:scale-95 transition-all shadow-md">
                        Ro'yxatdan o'tish
                    </a>
                @endauth
            </div>

            <!-- Mobile Hamburger -->
            <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-lg text-slate-400 hover:text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    <path x-show="mobileMenu" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div x-show="mobileMenu" x-cloak @click.away="mobileMenu = false" class="md:hidden border-b border-white/10 bg-ink-900/95 px-6 py-4 space-y-3">
            <a href="#features" @click="mobileMenu = false" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Imkoniyatlar</a>
            <a href="{{ route('books.public') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Kitoblar</a>
            <a href="{{ route('about') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Biz haqimizda</a>
            <a href="{{ route('faq') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">FAQ</a>
            <a href="{{ route('contact') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Aloqa</a>
            <div class="pt-3 border-t border-white/10 flex flex-col gap-2">
                <a href="{{ route('login') }}" class="text-center py-2 text-sm text-slate-200">Kirish</a>
                <a href="{{ route('register') }}" class="text-center py-2.5 bg-amber-500 text-ink-950 font-bold rounded-xl text-sm">Ro'yxatdan o'tish</a>
            </div>
        </div>
    </header>

    <!-- ── 1. HERO SECTION (Strict Viewport & Split Composition) ── -->
    <section class="relative pt-12 md:pt-20 pb-16 md:pb-24 overflow-hidden noise-bg">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                <!-- Left: Focused Copy & CTAs (Max 4 elements per taste-skill) -->
                <div class="lg:col-span-7 space-y-6">
                    
                    <!-- 1. Eyebrow badge -->
                    <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-amber-400/10 border border-amber-400/20 text-amber-400 font-mono text-xs tracking-wide">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                        <span>1-Mavsum: Shaxsiy yuksalish</span>
                    </div>

                    <!-- 2. Headline (Max 2 lines, tight leading, punchy) -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-[1.08]">
                        Har hafta bitta sara kitob, <span class="text-amber-400 italic font-serif">chuqur tahlil</span> va natija.
                    </h1>

                    <!-- 3. Subtext (Under 20 words, no fluff) -->
                    <p class="text-base sm:text-lg text-slate-300 max-w-[54ch] leading-relaxed font-normal">
                        Matn, audio va video darslar. Har kuni mutolaa qilib streak hosil qiling, test topshiring va bilimingizni boyiting.
                    </p>

                    <!-- 4. CTAs (1 Primary + 1 Secondary, single intent) -->
                    <div class="flex flex-wrap items-center gap-4 pt-2">
                        <a href="{{ route('register') }}" 
                           class="px-7 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-sm uppercase tracking-wider transition-all duration-200 active:scale-[0.98] shadow-lg shadow-amber-500/25 flex items-center gap-2">
                            <span>Hoziroq boshlash</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        <a href="{{ route('books.public') }}" 
                           class="px-6 py-3.5 rounded-xl bg-ink-800 hover:bg-ink-700 text-slate-200 font-semibold text-sm border border-white/10 hover:border-white/20 transition-all duration-200 active:scale-[0.98] flex items-center gap-2">
                            <span>Kitoblarni ko'rish</span>
                        </a>
                    </div>

                    <!-- Social proof counter pill -->
                    <div class="pt-4 flex items-center gap-4 text-xs text-slate-400">
                        <div class="flex -space-x-2">
                            <span class="w-8 h-8 rounded-full bg-slate-700 border-2 border-ink-950 flex items-center justify-center font-bold text-[11px] text-white">AZ</span>
                            <span class="w-8 h-8 rounded-full bg-indigo-700 border-2 border-ink-950 flex items-center justify-center font-bold text-[11px] text-white">DK</span>
                            <span class="w-8 h-8 rounded-full bg-amber-600 border-2 border-ink-950 flex items-center justify-center font-bold text-[11px] text-white">SH</span>
                        </div>
                        <p><strong class="text-white font-semibold">1,200+</strong> faol kitobxonlar qo'shildi</p>
                    </div>

                </div>

                <!-- Right: Interactive Featured Book Visual (3D Tilt & Audio Preview) -->
                <div class="lg:col-span-5 flex justify-center">
                    <div class="relative w-full max-w-sm" x-data="{ playing: false }">
                        
                        <!-- Glow behind card -->
                        <div class="absolute inset-0 bg-gradient-to-tr from-amber-500/20 via-indigo-500/20 to-transparent rounded-3xl blur-2xl transform scale-95 pointer-events-none"></div>

                        <!-- Main Interactive Card -->
                        <div class="relative rounded-3xl bg-ink-900 border border-white/15 p-6 shadow-card-depth animate-float-soft">
                            
                            <!-- Week Badge -->
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-3 py-1 rounded-lg bg-amber-400/10 border border-amber-400/20 text-amber-400 text-xs font-mono font-semibold">
                                    Hafta kitobi
                                </span>
                                <span class="flex items-center gap-1.5 text-xs text-emerald-400 font-medium">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                    Faol
                                </span>
                            </div>

                            <!-- Book Visual representation -->
                            <div class="relative h-64 rounded-2xl overflow-hidden bg-gradient-to-br from-indigo-950 via-ink-900 to-slate-900 border border-white/10 flex flex-col justify-end p-5 group">
                                <div class="absolute top-4 right-4 text-4xl opacity-40">📖</div>
                                <span class="text-xs font-mono text-amber-400 uppercase tracking-widest mb-1">James Clear</span>
                                <h3 class="text-xl font-bold text-white tracking-tight">Atom Odatlar</h3>
                                <p class="text-xs text-slate-400 mt-1 line-clamp-2">Kichik o'zgarishlar qanday qilib ulkan natijalarga olib kelishi haqida xalqaro bestseller.</p>
                            </div>

                            <!-- Mini Audio Bar -->
                            <div class="mt-4 p-3.5 rounded-2xl bg-ink-800/80 border border-white/10 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <button @click="playing = !playing" class="w-9 h-9 rounded-xl bg-amber-400 hover:bg-amber-300 text-ink-950 flex items-center justify-center font-bold text-xs transition-transform active:scale-90">
                                        <span x-text="playing ? '⏸' : '▶'">▶</span>
                                    </button>
                                    <div>
                                        <p class="text-xs font-semibold text-white">1-bob: Kichik odatlar kuchi</p>
                                        <p class="text-[10px] text-slate-400 font-mono">15 daqiqa • Audio sharh</p>
                                    </div>
                                </div>
                                <!-- Animated sound wave bars -->
                                <div class="flex items-end gap-1 h-6">
                                    <div class="w-1 bg-amber-400 rounded-full" :class="playing ? 'bar-anim' : 'h-1.5'"></div>
                                    <div class="w-1 bg-amber-400 rounded-full" :class="playing ? 'bar-anim' : 'h-3'"></div>
                                    <div class="w-1 bg-amber-400 rounded-full" :class="playing ? 'bar-anim' : 'h-2'"></div>
                                    <div class="w-1 bg-amber-400 rounded-full" :class="playing ? 'bar-anim' : 'h-4'"></div>
                                    <div class="w-1 bg-amber-400 rounded-full" :class="playing ? 'bar-anim' : 'h-2'"></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ── 2. ASYMMETRICAL BENTO GRID (Anti-Default Rhythm) ── -->
    <section id="features" class="py-20 border-t border-white/[0.07] relative">
        <div class="max-w-7xl mx-auto px-6">
            
            <div class="max-w-2xl mb-12">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Shunchaki o'qish emas, balki <span class="text-amber-400">natijaga aylanish</span>.
                </h2>
                <p class="text-slate-400 text-sm sm:text-base mt-2">
                    Kitobxon platformasi 4 xil qulaylikni yagona ekotizimga birlashtirgan.
                </p>
            </div>

            <!-- Bento Grid Structure: 1 Big + 2 Small + 1 Wide -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                <!-- Bento 1 (Large - Col 7): Matn, Audio va Video formatlar -->
                <div class="md:col-span-7 spotlight-card rounded-3xl p-8 flex flex-col justify-between overflow-hidden group">
                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-amber-400/10 border border-amber-400/20 text-amber-400 flex items-center justify-center text-2xl">
                            🎧
                        </div>
                        <h3 class="text-2xl font-bold text-white tracking-tight">3 xil o'qish tajribasi</h3>
                        <p class="text-slate-300 text-sm max-w-md leading-relaxed">
                            Vaqtingizga qarab tanlang: qulay tipografiyali matn rejimida o'qing, yo'lda audio formatini tinglang yoki ekspert video darslarini tomosha qiling.
                        </p>
                    </div>

                    <!-- Mini UI Preview of formats -->
                    <div class="grid grid-cols-3 gap-3 mt-8 pt-6 border-t border-white/10">
                        <div class="p-3.5 rounded-xl bg-ink-950/60 border border-white/5 text-center">
                            <span class="text-lg">📖</span>
                            <p class="text-xs font-bold text-white mt-1">Elektron kitob</p>
                            <p class="text-[10px] text-slate-500">Toza shriftlar</p>
                        </div>
                        <div class="p-3.5 rounded-xl bg-ink-950/60 border border-white/5 text-center">
                            <span class="text-lg">🎙️</span>
                            <p class="text-xs font-bold text-white mt-1">Audio kitob</p>
                            <p class="text-[10px] text-slate-500">1x / 1.5x / 2x tezlik</p>
                        </div>
                        <div class="p-3.5 rounded-xl bg-ink-950/60 border border-white/5 text-center">
                            <span class="text-lg">🎥</span>
                            <p class="text-xs font-bold text-white mt-1">Video dars</p>
                            <p class="text-[10px] text-slate-500">Ustoz tahlili</p>
                        </div>
                    </div>
                </div>

                <!-- Bento 2 (Small - Col 5): Kunlik Streak Gamifikatsiyasi -->
                <div class="md:col-span-5 spotlight-card rounded-3xl p-8 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-500 flex items-center justify-center text-2xl">
                                🔥
                            </span>
                            <span class="font-mono text-xs text-amber-400 bg-amber-400/10 px-2.5 py-1 rounded-lg">
                                Streak tizimi
                            </span>
                        </div>
                        <h3 class="text-xl font-bold text-white tracking-tight">Kunlik ketma-ketlik</h3>
                        <p class="text-slate-400 text-sm mt-2">
                            Har kuni kamida 10 daqiqa mutolaa qiling va faollik olovingizni so'ndirmang. Har bir o'qilgan daqiqa uchun ballar va tangalar beriladi.
                        </p>
                    </div>

                    <!-- Mini Streak Visualization -->
                    <div class="mt-6 p-4 rounded-2xl bg-ink-950/80 border border-white/10 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] text-slate-400 uppercase font-mono">Faol zanjir</p>
                            <p class="text-2xl font-black text-amber-400">14 kun <span class="text-sm font-normal text-slate-400">ketma-ket</span></p>
                        </div>
                        <div class="flex gap-1">
                            <div class="w-3 h-7 rounded bg-amber-500"></div>
                            <div class="w-3 h-7 rounded bg-amber-500"></div>
                            <div class="w-3 h-7 rounded bg-amber-500"></div>
                            <div class="w-3 h-7 rounded bg-amber-500"></div>
                            <div class="w-3 h-7 rounded bg-amber-400 animate-pulse"></div>
                        </div>
                    </div>
                </div>

                <!-- Bento 3 (Small - Col 5): AI Kitobxon Maslahatchisi -->
                <div class="md:col-span-5 spotlight-card rounded-3xl p-8 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-2xl mb-4">
                            🧠
                        </div>
                        <h3 class="text-xl font-bold text-white tracking-tight">AI Kitob Tahlilchisi</h3>
                        <p class="text-slate-400 text-sm mt-2">
                            Tushunmagan g'oyangiz bo'yicha sun'iy intellektga savol bering. U boblar bo'yicha aniq xulosalar va amaliy tavsiyalar taqdim etadi.
                        </p>
                    </div>

                    <!-- Chat snippet -->
                    <div class="mt-6 space-y-2 font-sans text-xs">
                        <div class="p-3 rounded-xl bg-ink-950/70 border border-white/5 text-slate-300">
                            "Bu bobdagi asosiy xulosa nima?"
                        </div>
                        <div class="p-3 rounded-xl bg-indigo-950/40 border border-indigo-500/20 text-indigo-200">
                            ✨ Har kuni 1% o'sish 1 yilda 37 barobar natija beradi.
                        </div>
                    </div>
                </div>

                <!-- Bento 4 (Wide - Col 7): Hamjamiyat va Jonli Efirlar -->
                <div class="md:col-span-7 spotlight-card rounded-3xl p-8 flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center text-2xl">
                            👥
                        </div>
                        <h3 class="text-2xl font-bold text-white tracking-tight">Jonli Muhokamalar & Efirlar</h3>
                        <p class="text-slate-300 text-sm max-w-md">
                            Hafta yakunida ekspert va o'qituvchilar bilan jonli efirda uchrashing, savol bering va boshqa kitobxonlar bilan fikr almashing.
                        </p>
                    </div>

                    <!-- Community Stat tags -->
                    <div class="mt-8 flex flex-wrap gap-3">
                        <span class="px-3.5 py-1.5 rounded-xl bg-ink-950/60 border border-white/10 text-xs font-medium text-slate-300">
                            💬 Umumiy chat
                        </span>
                        <span class="px-3.5 py-1.5 rounded-xl bg-ink-950/60 border border-white/10 text-xs font-medium text-slate-300">
                            🏆 Haftalik peshqadamlar
                        </span>
                        <span class="px-3.5 py-1.5 rounded-xl bg-ink-950/60 border border-white/10 text-xs font-medium text-slate-300">
                            🎓 Ustozlar tahlili
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ── 3. HOW IT WORKS (3 Simple Clear Steps) ── -->
    <section class="py-20 border-t border-white/[0.07] bg-ink-900/40">
        <div class="max-w-7xl mx-auto px-6">
            
            <div class="text-center max-w-xl mx-auto mb-16">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">O'qish tartibi qanday?</h2>
                <p class="text-slate-400 text-sm mt-2">Haftasiga atigi 15-20 daqiqa sarflab bilimliroq bo'ling</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <div class="p-6 rounded-2xl bg-ink-950/60 border border-white/10 space-y-3">
                    <span class="font-mono text-2xl font-black text-amber-400">01</span>
                    <h3 class="text-lg font-bold text-white">Haftalik kitob e'lon qilinadi</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Har dushanba kuni ekspertlar tomonidan saralangan hafta kitobi ochiladi.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-ink-950/60 border border-white/10 space-y-3">
                    <span class="font-mono text-2xl font-black text-amber-400">02</span>
                    <h3 class="text-lg font-bold text-white">Kunlik boblarni o'zlashtiring</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Kichik boblarni o'qing yoki audio tarzda tinglang, kunlik faollik ballarini jamg'aring.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-ink-950/60 border border-white/10 space-y-3">
                    <span class="font-mono text-2xl font-black text-amber-400">03</span>
                    <h3 class="text-lg font-bold text-white">Test topshiring va efirga ulaning</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Yakshanba kuni test topshiring, o'z reytingingizni oshiring va jonli efirda qatnashing.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- ── 4. CALL TO ACTION SECTION (High Contrast, Bold, Restrained) ── -->
    <section class="py-20 border-t border-white/[0.07] relative overflow-hidden">
        <div class="max-w-5xl mx-auto px-6 text-center space-y-6">
            
            <h2 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight">
                Bugun boshlang, bir yildan so'ng <br class="hidden sm:block">
                <span class="text-amber-400 italic font-serif">52 ta sara kitob</span> boyligi bilan bo'ling.
            </h2>

            <p class="text-slate-400 text-sm sm:text-base max-w-xl mx-auto">
                Hech qanday murakkab to'lovlarsiz hoziroq ro'yxatdan o'ting va ilk haftalik kitobni o'qishni boshlang.
            </p>

            <div class="pt-4 flex justify-center">
                <a href="{{ route('register') }}" 
                   class="px-8 py-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-sm uppercase tracking-wider transition-all duration-200 active:scale-[0.98] shadow-xl shadow-amber-500/20">
                    Mutolaani boshlash →
                </a>
            </div>

        </div>
    </section>

    <!-- ── 5. FOOTER (Clean, No-Fluff, Single Registry) ── -->
    <footer class="border-t border-white/[0.07] bg-ink-950 py-10 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <span class="font-bold text-white text-sm">Kitobxon</span>
                <span>•</span>
                <span>© {{ date('Y') }} Barcha huquqlar himoyalangan</span>
            </div>

            <div class="flex items-center gap-6">
                <a href="{{ route('about') }}" class="hover:text-slate-300 transition-colors">Biz haqimizda</a>
                <a href="{{ route('faq') }}" class="hover:text-slate-300 transition-colors">FAQ</a>
                <a href="{{ route('privacy') }}" class="hover:text-slate-300 transition-colors">Maxfiylik</a>
                <a href="{{ route('terms') }}" class="hover:text-slate-300 transition-colors">Foydalanish shartlari</a>
                <a href="{{ route('contact') }}" class="hover:text-slate-300 transition-colors">Aloqa</a>
            </div>
        </div>
    </footer>

</body>
</html>
