<!DOCTYPE html>
<html lang="uz" class="scroll-smooth dark" x-data="{ darkMode: true, mobileMenu: false, openItem: 1 }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kitobxon FAQ — Ko'p beriladigan savollarga aniq va to'liq javoblar.">
    <title>Savol-Javoblar (FAQ) — Kitobxon</title>

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
                        'card-depth': '0 25px 50px -12px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.08)',
                        'glow-amber': '0 0 35px -5px rgba(245, 158, 11, 0.3)',
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

    <!-- Alpine.js Collapse Plugin + Alpine.js -->
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .noise-bg {
            background-image: radial-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 0);
            background-size: 24px 24px;
        }
        .faq-item {
            position: relative;
            background: rgba(17, 23, 38, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.25rem;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .faq-item:hover {
            border-color: rgba(251, 191, 36, 0.35);
        }
        .faq-item.active {
            border-color: rgba(251, 191, 36, 0.45);
            background: rgba(20, 28, 48, 0.85);
            box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.6), 0 0 20px -2px rgba(251, 191, 36, 0.15);
        }

        /* Ambient floating orbs */
        @keyframes orbDrift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(25px, -15px) scale(1.05); }
        }
        .orb-animate-1 { animation: orbDrift 15s ease-in-out infinite; }
        .orb-animate-2 { animation: orbDrift 20s ease-in-out infinite reverse; }
    </style>
</head>
<body class="bg-ink-950 text-slate-200 font-sans selection:bg-amber-400 selection:text-ink-950 antialiased min-h-screen relative overflow-x-hidden">

    <!-- ── Page Transition & Loader ── -->
    @include('components.page-loader')

    <!-- Ambient Glows -->
    <div class="fixed top-[-100px] left-1/2 -translate-x-1/2 w-[1000px] h-[480px] bg-gradient-to-b from-amber-500/12 via-indigo-600/6 to-transparent rounded-full blur-[140px] pointer-events-none -z-10 orb-animate-1"></div>
    <div class="fixed bottom-[-100px] right-[-100px] w-[600px] h-[600px] bg-indigo-900/12 rounded-full blur-[150px] pointer-events-none -z-10 orb-animate-2"></div>

    <!-- ── Header ── -->
    <header id="site-header" class="sticky top-0 z-50 w-full backdrop-blur-xl bg-ink-950/80 border-b border-white/[0.07]">
        <div class="max-w-7xl mx-auto px-6 h-[72px] flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-ink-950 font-black text-xl shadow-lg shadow-amber-500/20 group-hover:scale-105 group-hover:rotate-3 transition-all duration-300">
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
                <a href="{{ route('faq') }}" class="text-amber-400 font-semibold">FAQ</a>
                <a href="{{ route('contact') }}" class="hover:text-amber-400 transition-colors">Aloqa</a>
            </nav>

            <div class="hidden sm:flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-amber-500 text-ink-950 font-bold text-xs uppercase tracking-wider hover:bg-amber-400 active:scale-95 transition-all shadow-md shadow-amber-500/20">
                        Boshqaruv paneli →
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-slate-300 hover:text-white transition-colors">Kirish</a>
                    <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-xl bg-white text-ink-950 font-bold text-xs uppercase tracking-wider hover:bg-slate-200 active:scale-95 transition-all shadow-md">Ro'yxatdan o'tish</a>
                @endauth
            </div>

            <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-lg text-slate-400 hover:text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenu" x-cloak @click.away="mobileMenu = false" class="md:hidden border-b border-white/10 bg-ink-900/95 px-6 py-4 space-y-3">
            <a href="{{ route('home') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Bosh sahifa</a>
            <a href="{{ route('books.public') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Kitoblar</a>
            <a href="{{ route('about') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Biz haqimizda</a>
            <a href="{{ route('faq') }}" class="block text-sm py-2 text-amber-400 font-semibold">FAQ</a>
            <a href="{{ route('contact') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Aloqa</a>
        </div>
    </header>

    <!-- ── Main Content (Chiqib keluvchi Savol-Javoblar) ── -->
    <main class="py-16 md:py-24 noise-bg">
        <div class="max-w-4xl mx-auto px-6">
            
            <div class="faq-header text-center space-y-4 mb-14">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-400/10 border border-amber-400/25 text-amber-400 font-mono text-xs tracking-wider">
                    ✦ KO'P SO'RALADIGAN SAVOLLAR
                </span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-tight">
                    Savollaringizga <span class="text-amber-400 italic font-serif">aniq javoblar</span>.
                </h1>
                <p class="text-slate-400 text-sm sm:text-base max-w-xl mx-auto leading-relaxed">
                    Platformadan foydalanish, haftalik kitoblar tartibi, streak tizimi, AI tahlilchi va jonli efirlar haqida barcha tafsilotlar.
                </p>
            </div>

            <!-- Accordion List (GSAP Staggered Entrance + Alpine Collapse) -->
            <div class="space-y-4">
                
                <!-- FAQ 1 -->
                <div class="faq-card faq-item" :class="{ 'active': openItem === 1 }">
                    <button @click="openItem = (openItem === 1 ? null : 1)" 
                            class="w-full p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-base sm:text-lg font-bold text-white">Platforma qanday ishlaydi?</span>
                        <div class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center text-amber-400 font-bold text-sm shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-amber-400/20 text-amber-300': openItem === 1 }">
                            ↓
                        </div>
                    </button>
                    <div x-show="openItem === 1" x-collapse class="px-6 pb-6 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4">
                        Har dushanba kuni barcha a'zolar uchun haftaning sara kitobi ochiladi. Hafta davomida kitobning boblarini matn yoki audio tarzda mutolaa qilib borasiz. Har kuni 15 daqiqa o'qish orqali streak olovini yoqasiz. Yakshanba kuni esa bilimlarni mustahkamlash uchun qiziqarli test va ustoz bilan jonli efir o'tkaziladi.
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="faq-card faq-item" :class="{ 'active': openItem === 2 }">
                    <button @click="openItem = (openItem === 2 ? null : 2)" 
                            class="w-full p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-base sm:text-lg font-bold text-white">Streak (kunlik ketma-ketlik) nima va u qanday hisoblanadi?</span>
                        <div class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center text-amber-400 font-bold text-sm shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-amber-400/20 text-amber-300': openItem === 2 }">
                            ↓
                        </div>
                    </button>
                    <div x-show="openItem === 2" x-collapse class="px-6 pb-6 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4">
                        Streak — bu sizning har kuni uzluksiz kitob o'qish odatingizni ifodalovchi olov ko'rsatkichi. Har kuni kamida 10 daqiqa kitob o'qisangiz yoki audio eshitsangiz, streak zanjiringiz 1 kunga oshadi. Agar bir kun o'qimay qoldirsangiz, zanjir uziladi va olov so'nadi.
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="faq-card faq-item" :class="{ 'active': openItem === 3 }">
                    <button @click="openItem = (openItem === 3 ? null : 3)" 
                            class="w-full p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-base sm:text-lg font-bold text-white">Kitobxon platformasidan foydalanish bepulmi?</span>
                        <div class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center text-amber-400 font-bold text-sm shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-amber-400/20 text-amber-300': openItem === 3 }">
                            ↓
                        </div>
                    </button>
                    <div x-show="openItem === 3" x-collapse class="px-6 pb-6 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4">
                        Ha, asosiy haftalik kitoblarni o'qish, test topshirish va umumiy hamjamiyat chatida ishtirok etish barcha ro'yxatdan o'tgan kitobxonlar uchun mutlaqo bepul taqdim etiladi.
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="faq-card faq-item" :class="{ 'active': openItem === 4 }">
                    <button @click="openItem = (openItem === 4 ? null : 4)" 
                            class="w-full p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-base sm:text-lg font-bold text-white">AI Kitob Maslahatchisi qanday vazifani bajaradi?</span>
                        <div class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center text-amber-400 font-bold text-sm shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-amber-400/20 text-amber-300': openItem === 4 }">
                            ↓
                        </div>
                    </button>
                    <div x-show="openItem === 4" x-collapse class="px-6 pb-6 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4">
                        Sun'iy intellekt haftalik kitobning barcha boblari bo'yicha to'liq o'qitilgan. Siz kitobdagi tushunarsiz joylar, murakkab atamalar yoki amaliy qo'llash bo'yicha istalgan vaqtda unga savol berib, soniyalar ichida aniq va batafsil xulosa olishingiz mumkin.
                    </div>
                </div>

                <!-- FAQ 5 -->
                <div class="faq-card faq-item" :class="{ 'active': openItem === 5 }">
                    <button @click="openItem = (openItem === 5 ? null : 5)" 
                            class="w-full p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-base sm:text-lg font-bold text-white">Jonli efirlarga qanday qo'shilish mumkin?</span>
                        <div class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center text-amber-400 font-bold text-sm shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-amber-400/20 text-amber-300': openItem === 5 }">
                            ↓
                        </div>
                    </button>
                    <div x-show="openItem === 5" x-collapse class="px-6 pb-6 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4">
                        Hafta yakunida shaxsiy dashboardingizda va "Jonli efirlar" bo'limida qizil "Jonli efir" indikatori paydo bo'ladi. Unga bosish orqali to'g'ridan-to'g'ri efir zaliga ulanishingiz, savollaringizni berishingiz va ekspert fikrlarini tinglashingiz mumkin.
                    </div>
                </div>

            </div>

            <!-- Still Have Questions Banner (ScrollTrigger Reveal) -->
            <div class="faq-cta-card mt-16 p-8 sm:p-10 rounded-3xl bg-ink-900/90 border border-white/10 text-center space-y-4 shadow-card-depth">
                <div class="w-12 h-12 rounded-2xl bg-amber-400/10 border border-amber-400/20 text-amber-400 flex items-center justify-center text-2xl mx-auto">
                    💡
                </div>
                <h3 class="text-2xl font-bold text-white">Boshqa savolingiz bormi?</h3>
                <p class="text-sm text-slate-400 max-w-md mx-auto leading-relaxed">
                    Bizning qo'llab-quvvatlash jamoamiz va ekspertlarimiz sizga har qanday masalada yordam berishga tayyor.
                </p>
                <div class="pt-2">
                    <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider transition-all duration-200 active:scale-95 shadow-md shadow-amber-500/25 hover:shadow-glow-amber">
                        <span>Bizga yozing</span>
                        <span>→</span>
                    </a>
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
                <a href="{{ route('contact') }}" class="hover:text-slate-300 transition-colors">Aloqa</a>
            </div>
        </div>
    </footer>

    <!-- ── Advanced Motion & Physics Script ── -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            gsap.registerPlugin(ScrollTrigger);

            // 1. Header Entrance
            gsap.fromTo('#site-header',
                { y: -30, opacity: 0 },
                { y: 0, opacity: 1, duration: 0.8, ease: "power3.out" }
            );

            // 2. FAQ Header Entrance
            gsap.fromTo('.faq-header',
                { opacity: 0, y: 35, filter: 'blur(8px)' },
                { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.9, ease: "power4.out" }
            );

            // 3. FAQ Accordion Cards Stagger Reveal
            gsap.fromTo('.faq-card',
                { opacity: 0, y: 35, scale: 0.97 },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.7,
                    stagger: 0.1,
                    ease: "power3.out",
                    clearProps: "transform,scale"
                }
            );

            // 4. CTA Card Reveal
            gsap.fromTo('.faq-cta-card',
                { opacity: 0, y: 40, scale: 0.95 },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.8,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: '.faq-cta-card',
                        start: "top 85%",
                    },
                    clearProps: "transform,scale"
                }
            );
        });
    </script>

</body>
</html>
