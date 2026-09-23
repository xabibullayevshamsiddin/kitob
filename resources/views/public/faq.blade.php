<!DOCTYPE html>
<html lang="uz" class="scroll-smooth dark" x-data="{ darkMode: true, mobileMenu: false, activeTab: 'all', searchQuery: '', openItem: 1 }" :class="{ 'dark': darkMode }">
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
        .faq-item {
            background: rgba(17, 23, 38, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.25s ease;
        }
        .faq-item:hover {
            border-color: rgba(251, 191, 36, 0.3);
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
                <a href="{{ route('faq') }}" class="text-amber-400 font-semibold">FAQ</a>
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
            <a href="{{ route('about') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Biz haqimizda</a>
            <a href="{{ route('faq') }}" class="block text-sm py-2 text-amber-400 font-semibold">FAQ</a>
            <a href="{{ route('contact') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Aloqa</a>
        </div>
    </header>

    <!-- ── Content ── -->
    <main class="py-16 md:py-24 noise-bg">
        <div class="max-w-4xl mx-auto px-6">
            
            <div class="text-center space-y-4 mb-12">
                <span class="text-xs font-mono text-amber-400 uppercase tracking-widest">✦ KO'P SO'RALADIGAN SAVOLLAR</span>
                <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    Savollaringizga <span class="text-amber-400 italic font-serif">aniq javoblar</span>.
                </h1>
                <p class="text-slate-400 text-sm sm:text-base max-w-xl mx-auto">
                    Platformadan foydalanish, kitoblar, streak tizimi va jonli efirlar bo'yicha ma'lumotlar.
                </p>
            </div>

            <!-- Accordion List (Interactive Alpine.js) -->
            <div class="space-y-4">
                
                <!-- FAQ 1 -->
                <div class="faq-item rounded-2xl overflow-hidden">
                    <button @click="openItem = (openItem === 1 ? null : 1)" 
                            class="w-full p-6 text-left flex items-center justify-between gap-4 focus:outline-none">
                        <span class="text-base font-bold text-white">Platforma qanday ishlaydi?</span>
                        <span class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-amber-400 font-mono text-sm shrink-0 transition-transform duration-200"
                              :class="{ 'rotate-180': openItem === 1 }">
                            ↓
                        </span>
                    </button>
                    <div x-show="openItem === 1" x-collapse class="px-6 pb-6 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4">
                        Har dushanba kuni barcha a'zolar uchun haftaning sara kitobi ochiladi. Hafta davomida kitobning boblarini matn yoki audio tarzda mutolaa qilib borasiz. Yakshanba kuni esa bilimlarni mustahkamlash uchun qiziqarli test va muallif/ekspert bilan jonli efir o'tkaziladi.
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="faq-item rounded-2xl overflow-hidden">
                    <button @click="openItem = (openItem === 2 ? null : 2)" 
                            class="w-full p-6 text-left flex items-center justify-between gap-4 focus:outline-none">
                        <span class="text-base font-bold text-white">Streak (kunlik ketma-ketlik) nima va u qanday hisoblanadi?</span>
                        <span class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-amber-400 font-mono text-sm shrink-0 transition-transform duration-200"
                              :class="{ 'rotate-180': openItem === 2 }">
                            ↓
                        </span>
                    </button>
                    <div x-show="openItem === 2" x-collapse class="px-6 pb-6 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4">
                        Streak — bu sizning har kuni uzluksiz kitob o'qish odatingizni ifodalovchi olov ko'rsatkichi. Har kuni kamida 10 daqiqa kitob o'qisangiz yoki audio eshitsangiz, streak zanjiringiz 1 kunga oshadi. Agar bir kun o'qimay qoldirsangiz, streak so'nadi.
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="faq-item rounded-2xl overflow-hidden">
                    <button @click="openItem = (openItem === 3 ? null : 3)" 
                            class="w-full p-6 text-left flex items-center justify-between gap-4 focus:outline-none">
                        <span class="text-base font-bold text-white">Kitobxon platformasidan foydalanish bepulmi?</span>
                        <span class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-amber-400 font-mono text-sm shrink-0 transition-transform duration-200"
                              :class="{ 'rotate-180': openItem === 3 }">
                            ↓
                        </span>
                    </button>
                    <div x-show="openItem === 3" x-collapse class="px-6 pb-6 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4">
                        Ha, asosiy haftalik kitoblarni o'qish, test topshirish va umumiy hamjamiyat chatida ishtirok etish barcha ro'yxatdan o'tgan kitobxonlar uchun bepul taqdim etiladi.
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="faq-item rounded-2xl overflow-hidden">
                    <button @click="openItem = (openItem === 4 ? null : 4)" 
                            class="w-full p-6 text-left flex items-center justify-between gap-4 focus:outline-none">
                        <span class="text-base font-bold text-white">AI Kitob Maslahatchisi qanday vazifani bajaradi?</span>
                        <span class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-amber-400 font-mono text-sm shrink-0 transition-transform duration-200"
                              :class="{ 'rotate-180': openItem === 4 }">
                            ↓
                        </span>
                    </button>
                    <div x-show="openItem === 4" x-collapse class="px-6 pb-6 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4">
                        Sun'iy intellekt haftalik kitobning barcha boblari bo'yicha to'liq o'qitilgan. Siz kitobdagi tushunarsiz joylar, murakkab atamalar yoki amaliy qo'llash bo'yicha istalgan vaqtda unga savol berib, tezkor va aniq javob olishingiz mumkin.
                    </div>
                </div>

                <!-- FAQ 5 -->
                <div class="faq-item rounded-2xl overflow-hidden">
                    <button @click="openItem = (openItem === 5 ? null : 5)" 
                            class="w-full p-6 text-left flex items-center justify-between gap-4 focus:outline-none">
                        <span class="text-base font-bold text-white">Jonli efirlarga qanday qo'shilish mumkin?</span>
                        <span class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-amber-400 font-mono text-sm shrink-0 transition-transform duration-200"
                              :class="{ 'rotate-180': openItem === 5 }">
                            ↓
                        </span>
                    </button>
                    <div x-show="openItem === 5" x-collapse class="px-6 pb-6 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4">
                        Hafta yakunida shaxsiy dashboardingizda va "Jonli efirlar" bo'limida qizil "Jonli efir" tugmasi paydo bo'ladi. Unga bosish orqali to'g'ridan-to'g'ri efir zaliga ulanishingiz va chatda savollaringizni berishingiz mumkin.
                    </div>
                </div>

            </div>

            <!-- Still Have Questions Banner -->
            <div class="mt-16 p-8 rounded-3xl bg-ink-900 border border-white/10 text-center space-y-4">
                <h3 class="text-xl font-bold text-white">Boshqa savolingiz bormi?</h3>
                <p class="text-xs text-slate-400 max-w-md mx-auto">Bizning qo'llab-quvvatlash jamoamiz sizga har qanday masalada yordam berishga tayyor.</p>
                <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider transition-all active:scale-[0.98] shadow-md shadow-amber-500/20">
                    Bizga yozing →
                </a>
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

</body>
</html>
