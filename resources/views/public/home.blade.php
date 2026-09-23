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

    <!-- GSAP & ScrollTrigger for Pro-level Physics Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

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
        .spotlight-card:hover::before {
            opacity: 1;
        }
        .spotlight-card:hover {
            border-color: rgba(251, 191, 36, 0.4);
            transform: translateY(-6px);
            box-shadow: 0 25px 50px -15px rgba(0, 0, 0, 0.75), 0 0 30px -5px rgba(251, 191, 36, 0.2);
        }

        /* Continuous Kinetic Ticker */
        @keyframes tickerLoop {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .ticker-track {
            display: flex;
            width: 200%;
            animation: tickerLoop 26s linear infinite;
        }
        .ticker-track:hover {
            animation-play-state: paused;
        }

        /* Audio wave dynamic bars */
        .bar-anim:nth-child(1) { animation: bounceBar 1.1s infinite ease-in-out; }
        .bar-anim:nth-child(2) { animation: bounceBar 0.85s infinite ease-in-out 0.2s; }
        .bar-anim:nth-child(3) { animation: bounceBar 1.3s infinite ease-in-out 0.4s; }
        .bar-anim:nth-child(4) { animation: bounceBar 1.0s infinite ease-in-out 0.1s; }
        .bar-anim:nth-child(5) { animation: bounceBar 0.75s infinite ease-in-out 0.3s; }
        @keyframes bounceBar {
            0%, 100% { height: 6px; }
            50% { height: 24px; }
        }

        /* Ambient floating orbs */
        @keyframes orbDrift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -20px) scale(1.08); }
        }
        .orb-animate-1 { animation: orbDrift 14s ease-in-out infinite; }
        .orb-animate-2 { animation: orbDrift 18s ease-in-out infinite reverse; }

        /* Interactive 3D Perspective Parent */
        .perspective-container {
            perspective: 1200px;
        }
    </style>
</head>
<body class="bg-ink-950 text-slate-200 font-sans selection:bg-amber-400 selection:text-ink-950 antialiased min-h-screen relative overflow-x-hidden">

    <!-- ── Page Transition & Loader ── -->
    @include('components.page-loader')

    <!-- ── Ambient Floating Glows (Cinematic Depth) ── -->
    <div class="fixed top-[-120px] left-1/2 -translate-x-1/2 w-[950px] h-[500px] bg-gradient-to-b from-amber-500/15 via-indigo-600/8 to-transparent rounded-full blur-[150px] pointer-events-none -z-10 orb-animate-1"></div>
    <div class="fixed bottom-[-100px] right-[-80px] w-[650px] h-[650px] bg-indigo-900/12 rounded-full blur-[160px] pointer-events-none -z-10 orb-animate-2"></div>

    <div id="smooth-page-wrapper">
    <!-- ── Header Navigation (GSAP Nav Entrance) ── -->
    <header id="site-header" class="sticky top-0 z-50 w-full backdrop-blur-xl bg-ink-950/80 border-b border-white/[0.07] transition-all">
        <div class="max-w-7xl mx-auto px-6 h-[72px] flex items-center justify-between">
            
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-ink-950 font-black text-xl shadow-lg shadow-amber-500/25 group-hover:scale-105 group-hover:rotate-3 transition-all duration-300">
                    📖
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-white text-base tracking-tight group-hover:text-amber-400 transition-colors">Kitobxon</span>
                    <span class="font-mono text-[10px] text-slate-400 uppercase tracking-widest">Platforma</span>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-300">
                <a href="#features" class="hover:text-amber-400 transition-colors">Imkoniyatlar</a>
                <a href="{{ route('books.public') }}" class="hover:text-amber-400 transition-colors">Kitoblar</a>
                <a href="{{ route('about') }}" class="hover:text-amber-400 transition-colors">Biz haqimizda</a>
                <a href="{{ route('faq') }}" class="hover:text-amber-400 transition-colors">FAQ</a>
                <a href="{{ route('contact') }}" class="hover:text-amber-400 transition-colors">Aloqa</a>
            </nav>

            <div class="hidden sm:flex items-center gap-3">
                @auth
                    @if(auth()->user()->hasRole('admin'))
                        <a href="{{ route('admin.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-amber-500 text-ink-950 font-bold text-xs uppercase tracking-wider hover:bg-amber-400 active:scale-95 transition-all shadow-md shadow-amber-500/20">
                            Boshqaruv paneli →
                        </a>
                    @elseif(auth()->user()->hasRole('teacher'))
                        <a href="{{ route('teacher.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-amber-500 text-ink-950 font-bold text-xs uppercase tracking-wider hover:bg-amber-400 active:scale-95 transition-all shadow-md shadow-amber-500/20">
                            O'qituvchi paneli →
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-amber-500 text-ink-950 font-bold text-xs uppercase tracking-wider hover:bg-amber-400 active:scale-95 transition-all shadow-md shadow-amber-500/20">
                            Dashboard →
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-slate-300 hover:text-white transition-colors">
                        Kirish
                    </a>
                    <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-xl bg-white text-ink-950 font-bold text-xs uppercase tracking-wider hover:bg-slate-200 active:scale-95 transition-all shadow-md">
                        Ro'yxatdan o'tish
                    </a>
                @endauth
            </div>

            <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-lg text-slate-400 hover:text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <div x-show="mobileMenu" x-cloak @click.away="mobileMenu = false" class="md:hidden border-b border-white/10 bg-ink-900/95 px-6 py-4 space-y-3">
            <a href="#features" @click="mobileMenu = false" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Imkoniyatlar</a>
            <a href="{{ route('books.public') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Kitoblar</a>
            <a href="{{ route('about') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Biz haqimizda</a>
            <a href="{{ route('faq') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">FAQ</a>
            <a href="{{ route('contact') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Aloqa</a>
        </div>
    </header>

    <!-- ── 1. CINEMATIC HERO SECTION (Chiqib keluvchi Pro Animatsiyalar) ── -->
    <section class="relative pt-16 md:pt-24 pb-20 md:pb-28 overflow-hidden noise-bg perspective-container">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                <!-- Left: Focused Copy with Staggered Entrance -->
                <div class="lg:col-span-7 space-y-6">
                    
                    <div class="hero-anim-item inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-amber-400/10 border border-amber-400/25 text-amber-400 font-mono text-xs tracking-wide shadow-sm shadow-amber-500/10">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping"></span>
                        <span>1-Mavsum: Shaxsiy yuksalish & Tafakkur</span>
                    </div>

                    <h1 class="hero-anim-item text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-[1.08]">
                        Har hafta bitta sara kitob, <span class="text-amber-400 italic font-serif">chuqur tahlil</span> va aniq natija.
                    </h1>

                    <p class="hero-anim-item text-base sm:text-lg text-slate-300 max-w-[54ch] leading-relaxed font-normal">
                        Matn, audio va ustozlar video darslari. Har kuni 15 daqiqa mutolaa qilib streak hosil qiling, test topshiring va kitobxonlar reytingida peshqadam bo'ling.
                    </p>

                    <div class="hero-anim-item flex flex-wrap items-center gap-4 pt-2">
                        <a href="{{ route('register') }}" 
                           class="px-7 py-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-sm uppercase tracking-wider transition-all duration-200 active:scale-95 shadow-lg shadow-amber-500/30 hover:shadow-glow-amber flex items-center gap-2 group">
                            <span>Hoziroq boshlash</span>
                            <svg class="w-4 h-4 group-hover:translate-x-1.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        <a href="{{ route('books.public') }}" 
                           class="px-6 py-4 rounded-xl bg-ink-800/90 hover:bg-ink-700 text-slate-200 font-semibold text-sm border border-white/10 hover:border-amber-400/30 transition-all duration-200 active:scale-95 flex items-center gap-2">
                            <span>Kitoblar katalogi</span>
                        </a>
                    </div>

                    <div class="hero-anim-item pt-4 flex items-center gap-4 text-xs text-slate-400">
                        <div class="flex -space-x-2">
                            <span class="w-8 h-8 rounded-full bg-slate-700 border-2 border-ink-950 flex items-center justify-center font-bold text-[11px] text-white">AZ</span>
                            <span class="w-8 h-8 rounded-full bg-indigo-700 border-2 border-ink-950 flex items-center justify-center font-bold text-[11px] text-white">DK</span>
                            <span class="w-8 h-8 rounded-full bg-amber-600 border-2 border-ink-950 flex items-center justify-center font-bold text-[11px] text-white">SH</span>
                        </div>
                        <p><strong class="text-white font-semibold text-sm counter-element" data-target="1200">0</strong>+ faol kitobxonlar safimizda</p>
                    </div>

                </div>

                <!-- Right: Interactive 3D Card (GSAP Entrance + 3D Tilt Physics) -->
                <div class="lg:col-span-5 flex justify-center">
                    <div id="hero-tilt-card" class="hero-anim-item relative w-full max-w-sm cursor-pointer" x-data="{ playing: false }">
                        
                        <div class="absolute inset-0 bg-gradient-to-tr from-amber-500/25 via-indigo-500/20 to-transparent rounded-3xl blur-2xl transform scale-95 pointer-events-none"></div>

                        <div class="relative rounded-3xl bg-ink-900/95 border border-white/15 p-6 shadow-card-depth transition-transform duration-200 hover:border-amber-400/40">
                            
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-3 py-1 rounded-lg bg-amber-400/10 border border-amber-400/25 text-amber-400 text-xs font-mono font-bold">
                                    ✦ Hafta kitobi
                                </span>
                                <span class="flex items-center gap-1.5 text-xs text-emerald-400 font-medium">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                    Faol o'qilmoqda
                                </span>
                            </div>

                            <div class="relative h-64 rounded-2xl overflow-hidden bg-gradient-to-br from-indigo-950 via-ink-900 to-slate-900 border border-white/10 flex flex-col justify-end p-5 group">
                                <div class="absolute top-4 right-4">
                                    <span class="px-2.5 py-1 rounded bg-black/50 backdrop-blur-md text-[11px] font-mono text-amber-300 border border-white/10">
                                        1-Hafta
                                    </span>
                                </div>
                                <div class="relative z-10 space-y-1">
                                    <p class="text-xs font-mono text-amber-400 uppercase tracking-wider">Atom Odatlar</p>
                                    <h3 class="text-xl font-bold text-white leading-tight">James Clear</h3>
                                    <p class="text-xs text-slate-300 line-clamp-2 mt-1">Kichik o'zgarishlar, ulkan natijalar siri.</p>
                                </div>
                            </div>

                            <!-- Interactive Audio Wave preview inside Card -->
                            <div class="mt-4 p-4 rounded-2xl bg-ink-950/80 border border-white/10 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <button @click="playing = !playing" class="w-10 h-10 rounded-xl bg-amber-500 text-ink-950 flex items-center justify-center font-bold text-sm shadow-md shadow-amber-500/20 hover:scale-105 active:scale-95 transition-all">
                                        <span x-text="playing ? '⏸' : '▶'">▶</span>
                                    </button>
                                    <div>
                                        <p class="text-xs font-bold text-white">Audio bob 1</p>
                                        <p class="text-[10px] text-slate-400">12:45 daqiqa • O'zbekcha</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 h-6 px-2">
                                    <span class="w-1 bg-amber-400 rounded-full bar-anim"></span>
                                    <span class="w-1 bg-amber-400 rounded-full bar-anim"></span>
                                    <span class="w-1 bg-amber-400 rounded-full bar-anim"></span>
                                    <span class="w-1 bg-amber-400 rounded-full bar-anim"></span>
                                    <span class="w-1 bg-amber-400 rounded-full bar-anim"></span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ── 2. CONTINUOUS KINETIC TICKER (Ko'zni tortuvchi lentali ritm) ── -->
    <div class="py-4 border-y border-white/[0.08] bg-ink-900/60 overflow-hidden relative">
        <div class="ticker-track font-mono text-xs uppercase tracking-widest text-slate-400 flex items-center gap-8 whitespace-nowrap">
            <span class="flex items-center gap-2"><span class="text-amber-400 font-bold">✦</span> Haftalik yangi kitob</span>
            <span class="flex items-center gap-2"><span class="text-amber-400 font-bold">✦</span> Audio & Video formatlar</span>
            <span class="flex items-center gap-2"><span class="text-amber-400 font-bold">✦</span> Kunlik streak odati</span>
            <span class="flex items-center gap-2"><span class="text-amber-400 font-bold">✦</span> Sun'iy intellekt tahlili</span>
            <span class="flex items-center gap-2"><span class="text-amber-400 font-bold">✦</span> Jonli ekspert efirlari</span>
            <span class="flex items-center gap-2"><span class="text-amber-400 font-bold">✦</span> Haftalik test & reyting</span>
            <span class="flex items-center gap-2"><span class="text-amber-400 font-bold">✦</span> Haftalik yangi kitob</span>
            <span class="flex items-center gap-2"><span class="text-amber-400 font-bold">✦</span> Audio & Video formatlar</span>
            <span class="flex items-center gap-2"><span class="text-amber-400 font-bold">✦</span> Kunlik streak odati</span>
            <span class="flex items-center gap-2"><span class="text-amber-400 font-bold">✦</span> Sun'iy intellekt tahlili</span>
            <span class="flex items-center gap-2"><span class="text-amber-400 font-bold">✦</span> Jonli ekspert efirlari</span>
            <span class="flex items-center gap-2"><span class="text-amber-400 font-bold">✦</span> Haftalik test & reyting</span>
        </div>
    </div>

    <!-- ── 3. ASYMMETRICAL BENTO GRID (ScrollTrigger Staggered Entrance) ── -->
    <section id="features" class="py-24 md:py-32 relative noise-bg">
        <div class="max-w-7xl mx-auto px-6">
            
            <div class="bento-header max-w-2xl mb-14 space-y-3">
                <span class="text-xs font-mono text-amber-400 uppercase tracking-widest block">✦ EKOTIZIM IMKONIYATLARI</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    Shunchaki o'qish emas, balki <span class="text-amber-400 italic font-serif">natijaga aylanish</span>.
                </h2>
                <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
                    Kitobxon platformasi 4 xil qulaylikni yagona mukammal ekotizimga birlashtirgan.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                <!-- Bento 1 (Large - Col 7) -->
                <div class="bento-card md:col-span-7 spotlight-card p-8 sm:p-10 flex flex-col justify-between">
                    <div class="space-y-3 relative z-10">
                        <div class="w-12 h-12 rounded-2xl bg-amber-400/10 border border-amber-400/20 text-amber-400 flex items-center justify-center text-2xl">
                            🎧
                        </div>
                        <h3 class="text-2xl font-bold text-white tracking-tight">3 xil o'qish tajribasi</h3>
                        <p class="text-slate-300 text-sm max-w-md leading-relaxed">
                            Vaqtingizga qarab tanlang: qulay tipografiyali matn rejimida o'qing, yo'lda audio formatini tinglang yoki ustozlarning video tahlil darslarini tomosha qiling.
                        </p>
                    </div>

                    <div class="grid grid-cols-3 gap-3 mt-8 pt-6 border-t border-white/10 relative z-10">
                        <div class="p-3.5 rounded-xl bg-ink-950/70 border border-white/5 text-center group-hover:border-amber-400/20 transition-colors">
                            <span class="text-xl">📖</span>
                            <p class="text-xs font-bold text-white mt-1">Elektron kitob</p>
                            <p class="text-[10px] text-slate-500">Toza shriftlar</p>
                        </div>
                        <div class="p-3.5 rounded-xl bg-ink-950/70 border border-white/5 text-center group-hover:border-amber-400/20 transition-colors">
                            <span class="text-xl">🎙️</span>
                            <p class="text-xs font-bold text-white mt-1">Audio kitob</p>
                            <p class="text-[10px] text-slate-500">1x / 1.5x / 2x</p>
                        </div>
                        <div class="p-3.5 rounded-xl bg-ink-950/70 border border-white/5 text-center group-hover:border-amber-400/20 transition-colors">
                            <span class="text-xl">🎥</span>
                            <p class="text-xs font-bold text-white mt-1">Video dars</p>
                            <p class="text-[10px] text-slate-500">Ustoz tahlili</p>
                        </div>
                    </div>
                </div>

                <!-- Bento 2 (Col 5) -->
                <div class="bento-card md:col-span-5 spotlight-card p-8 sm:p-10 flex flex-col justify-between">
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-500 flex items-center justify-center text-2xl">
                                🔥
                            </span>
                            <span class="font-mono text-xs text-amber-400 bg-amber-400/10 px-2.5 py-1 rounded-lg">
                                Streak odati
                            </span>
                        </div>
                        <h3 class="text-xl font-bold text-white tracking-tight">Kunlik faollik zanjiri</h3>
                        <p class="text-slate-400 text-sm mt-2 leading-relaxed">
                            Har kuni kamida 10 daqiqa mutolaa qiling va faollik olovingizni so'ndirmang. Odat hosil qiling.
                        </p>
                    </div>

                    <div class="mt-6 p-4 rounded-2xl bg-ink-950/80 border border-white/10 flex items-center justify-between relative z-10">
                        <div>
                            <p class="text-[11px] text-slate-400 uppercase font-mono">Faol zanjir</p>
                            <p class="text-2xl font-black text-amber-400"><span class="counter-element" data-target="14">0</span> kun <span class="text-xs font-normal text-slate-400">ketma-ket</span></p>
                        </div>
                        <div class="flex gap-1.5">
                            <div class="w-3 h-8 rounded bg-amber-500"></div>
                            <div class="w-3 h-8 rounded bg-amber-500"></div>
                            <div class="w-3 h-8 rounded bg-amber-500"></div>
                            <div class="w-3 h-8 rounded bg-amber-500"></div>
                            <div class="w-3 h-8 rounded bg-amber-400 animate-pulse"></div>
                        </div>
                    </div>
                </div>

                <!-- Bento 3 (Col 5) -->
                <div class="bento-card md:col-span-5 spotlight-card p-8 sm:p-10 flex flex-col justify-between">
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-2xl mb-4">
                            🧠
                        </div>
                        <h3 class="text-xl font-bold text-white tracking-tight">AI Kitob Tahlilchisi</h3>
                        <p class="text-slate-400 text-sm mt-2 leading-relaxed">
                            Tushunmagan g'oyangiz bo'yicha sun'iy intellektga savol bering. U boblar bo'yicha aniq xulosalar taqdim etadi.
                        </p>
                    </div>

                    <div class="mt-6 space-y-2.5 font-sans text-xs relative z-10">
                        <div class="p-3 rounded-xl bg-ink-950/80 border border-white/5 text-slate-300">
                            "Bu bobdagi eng asosiy g'oya nima?"
                        </div>
                        <div class="p-3 rounded-xl bg-indigo-950/40 border border-indigo-500/20 text-indigo-200 flex items-center gap-2">
                            <span>✨</span>
                            <span>Har kuni 1% o'sish 1 yilda 37 barobar natija beradi.</span>
                        </div>
                    </div>
                </div>

                <!-- Bento 4 (Col 7) -->
                <div class="bento-card md:col-span-7 spotlight-card p-8 sm:p-10 flex flex-col justify-between">
                    <div class="space-y-3 relative z-10">
                        <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center text-2xl">
                            👥
                        </div>
                        <h3 class="text-2xl font-bold text-white tracking-tight">Jonli Muhokamalar & Efirlar</h3>
                        <p class="text-slate-300 text-sm max-w-md leading-relaxed">
                            Hafta yakunida ekspert va o'qituvchilar bilan jonli efirda uchrashing, savol bering va fikrdoshlar bilan tanishing.
                        </p>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-3 relative z-10">
                        <span class="px-3.5 py-1.5 rounded-xl bg-ink-950/80 border border-white/10 text-xs font-medium text-slate-300">
                            💬 Umumiy kitobxonlar chati
                        </span>
                        <span class="px-3.5 py-1.5 rounded-xl bg-ink-950/80 border border-white/10 text-xs font-medium text-slate-300">
                            🏆 Haftalik peshqadamlar ro'yxati
                        </span>
                        <span class="px-3.5 py-1.5 rounded-xl bg-ink-950/80 border border-white/10 text-xs font-medium text-slate-300">
                            🎓 Ustozlar bilan savol-javob
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ── 4. HOW IT WORKS (ScrollTrigger Staggered Steps) ── -->
    <section class="py-24 md:py-32 border-t border-white/[0.07] bg-ink-900/40">
        <div class="max-w-7xl mx-auto px-6">
            
            <div class="step-header text-center max-w-xl mx-auto mb-16 space-y-2">
                <span class="text-xs font-mono text-amber-400 uppercase tracking-widest block">✦ BOSQICHLAR</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">O'qish tartibi qanday?</h2>
                <p class="text-slate-400 text-sm mt-2">Haftasiga atigi 15-20 daqiqa sarflab yangi cho'qqilarni zabt eting</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <div class="step-card p-8 rounded-3xl bg-ink-950/80 border border-white/10 space-y-4 hover:border-amber-400/30 transition-all duration-300 spotlight-card">
                    <span class="font-mono text-3xl font-black text-amber-400">01</span>
                    <h3 class="text-lg font-bold text-white">Haftalik kitob ochiladi</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Har dushanba kuni ekspertlar tomonidan saralangan hafta kitobi taqdim etiladi.
                    </p>
                </div>

                <div class="step-card p-8 rounded-3xl bg-ink-950/80 border border-white/10 space-y-4 hover:border-amber-400/30 transition-all duration-300 spotlight-card">
                    <span class="font-mono text-3xl font-black text-amber-400">02</span>
                    <h3 class="text-lg font-bold text-white">Kunlik boblarni o'rganing</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Kichik boblarni o'qing yoki audio tarzda tinglang, kunlik streak olovini yoqing.
                    </p>
                </div>

                <div class="step-card p-8 rounded-3xl bg-ink-950/80 border border-white/10 space-y-4 hover:border-amber-400/30 transition-all duration-300 spotlight-card">
                    <span class="font-mono text-3xl font-black text-amber-400">03</span>
                    <h3 class="text-lg font-bold text-white">Test & Jonli efir</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Yakshanba kuni test topshiring, reytingingizni oshiring va jonli efirda qatnashing.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- ── 5. CALL TO ACTION (Chiqib keluvchi CTA va Natija) ── -->
    <section class="py-24 md:py-32 border-t border-white/[0.07] relative overflow-hidden">
        <div class="cta-box max-w-5xl mx-auto px-6 text-center space-y-6">
            
            <h2 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight leading-tight">
                Bugun boshlang, bir yildan so'ng <br class="hidden sm:block">
                <span class="text-amber-400 italic font-serif"><span class="counter-element" data-target="52">0</span> ta sara kitob</span> boyligi bilan bo'ling.
            </h2>

            <p class="text-slate-400 text-sm sm:text-base max-w-xl mx-auto leading-relaxed">
                Hech qanday murakkab to'lovlarsiz hoziroq ro'yxatdan o'ting va ilk haftalik kitobni o'qishni boshlang.
            </p>

            <div class="pt-4 flex justify-center">
                <a href="{{ route('register') }}" 
                   class="px-8 py-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-sm uppercase tracking-wider transition-all duration-200 active:scale-95 shadow-xl shadow-amber-500/30 hover:shadow-glow-amber">
                    Mutolaani boshlash →
                </a>
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
                <a href="{{ route('about') }}" class="hover:text-slate-300 transition-colors">Biz haqimizda</a>
                <a href="{{ route('faq') }}" class="hover:text-slate-300 transition-colors">FAQ</a>
                <a href="{{ route('privacy') }}" class="hover:text-slate-300 transition-colors">Maxfiylik</a>
                <a href="{{ route('terms') }}" class="hover:text-slate-300 transition-colors">Foydalanish shartlari</a>
                <a href="{{ route('contact') }}" class="hover:text-slate-300 transition-colors">Aloqa</a>
            </div>
        </div>
    </footer>
    </div>

    <!-- ── Advanced Motion & Physics Script (GSAP + ScrollTrigger + Dynamic Tilt + Counters) ── -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            gsap.registerPlugin(ScrollTrigger);

            // 1. Header Entrance
            gsap.fromTo('#site-header',
                { y: -30, opacity: 0 },
                { y: 0, opacity: 1, duration: 0.8, ease: "power3.out" }
            );

            // 2. Cinematic Hero Staggered Entrance Reveal
            gsap.fromTo('.hero-anim-item', 
                { opacity: 0, y: 40, filter: 'blur(8px)', scale: 0.96 },
                { 
                    opacity: 1, 
                    y: 0, 
                    filter: 'blur(0px)',
                    scale: 1,
                    duration: 1.0, 
                    stagger: 0.12, 
                    ease: "power4.out",
                    clearProps: "transform,scale,filter"
                }
            );

            // 3. Bento Grid Scroll-Triggered Stagger Reveal
            gsap.fromTo('.bento-header',
                { opacity: 0, y: 35 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.8,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: '#features',
                        start: "top 80%",
                    }
                }
            );

            gsap.fromTo('.bento-card', 
                { opacity: 0, y: 50, scale: 0.95 },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.85,
                    stagger: 0.14,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: '#features',
                        start: "top 75%",
                    },
                    clearProps: "transform,scale"
                }
            );

            // 4. Step Cards Stagger Reveal
            gsap.fromTo('.step-card',
                { opacity: 0, y: 45, scale: 0.96 },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.75,
                    stagger: 0.16,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: '.step-card',
                        start: "top 85%",
                    },
                    clearProps: "transform,scale"
                }
            );

            // 5. CTA Box Reveal
            gsap.fromTo('.cta-box',
                { opacity: 0, scale: 0.92, y: 30 },
                {
                    opacity: 1,
                    scale: 1,
                    y: 0,
                    duration: 0.9,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: '.cta-box',
                        start: "top 85%",
                    },
                    clearProps: "all"
                }
            );

            // 6. Dynamic Rolling Number Counters (ScrollTrigger driven)
            document.querySelectorAll('.counter-element').forEach(el => {
                const target = parseInt(el.getAttribute('data-target') || 0, 10);
                ScrollTrigger.create({
                    trigger: el,
                    start: "top 90%",
                    once: true,
                    onEnter: () => {
                        const obj = { count: 0 };
                        gsap.to(obj, {
                            count: target,
                            duration: 1.8,
                            ease: "power2.out",
                            onUpdate: () => {
                                el.textContent = Math.floor(obj.count).toLocaleString('en-US');
                            }
                        });
                    }
                });
            });

            // 7. Dynamic Spotlight Cursor Glow for all spotlight-cards
            document.querySelectorAll('.spotlight-card').forEach(card => {
                card.addEventListener('mousemove', e => {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    card.style.setProperty('--mouse-x', `${x}px`);
                    card.style.setProperty('--mouse-y', `${y}px`);
                });
            });

            // 8. Interactive 3D Card Hover Tilt Physics
            const tiltCard = document.getElementById('hero-tilt-card');
            if (tiltCard) {
                // Subtle floating physics
                gsap.to(tiltCard, {
                    y: -10,
                    duration: 2.8,
                    repeat: -1,
                    yoyo: true,
                    ease: "sine.inOut"
                });

                tiltCard.addEventListener('mousemove', (e) => {
                    const rect = tiltCard.getBoundingClientRect();
                    const x = e.clientX - rect.left - rect.width / 2;
                    const y = e.clientY - rect.top - rect.height / 2;
                    const rotX = -(y / rect.height) * 16;
                    const rotY = (x / rect.width) * 16;
                    tiltCard.style.transform = `perspective(1000px) rotateX(${rotX}deg) rotateY(${rotY}deg) scale3d(1.02, 1.02, 1.02)`;
                });
                tiltCard.addEventListener('mouseleave', () => {
                    tiltCard.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
                });
            }
        });
    </script>

</body>
</html>
