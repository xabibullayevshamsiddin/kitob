<!DOCTYPE html>
<html lang="uz" x-data="{ darkMode: false, mobileMenu: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kitobxon — Har hafta bitta kitob, cheksiz bilim. O'zbek tilidagi eng yaxshi onlayn kitob o'qish platformasi.">
    <title>Kitobxon 📚 — Har hafta bitta kitob, cheksiz bilim</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                        serif: ['Merriweather', 'ui-serif', 'Georgia'],
                    },
                    colors: {
                        primary: {
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe',
                            300: '#a5b4fc', 400: '#818cf8', 500: '#6366f1',
                            600: '#4f46e5', 700: '#4338ca', 800: '#3730a3', 900: '#312e81',
                        },
                        accent: {
                            400: '#fbbf24', 500: '#f59e0b', 600: '#d97706',
                        },
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out forwards',
                        'slide-up': 'slideUp 0.7s ease-out forwards',
                        'slide-in-left': 'slideInLeft 0.7s ease-out forwards',
                        'slide-in-right': 'slideInRight 0.7s ease-out forwards',
                        'scale-in': 'scaleIn 0.5s ease-out forwards',
                        'float': 'float 3s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4,0,0.6,1) infinite',
                        'gradient': 'gradient 6s ease infinite',
                        'bounce-slow': 'bounce 2s infinite',
                    },
                    keyframes: {
                        fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                        slideUp: { '0%': { opacity: '0', transform: 'translateY(40px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                        slideInLeft: { '0%': { opacity: '0', transform: 'translateX(-40px)' }, '100%': { opacity: '1', transform: 'translateX(0)' } },
                        slideInRight: { '0%': { opacity: '0', transform: 'translateX(40px)' }, '100%': { opacity: '1', transform: 'translateX(0)' } },
                        scaleIn: { '0%': { opacity: '0', transform: 'scale(0.85)' }, '100%': { opacity: '1', transform: 'scale(1)' } },
                        float: { '0%,100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-12px)' } },
                        gradient: { '0%,100%': { backgroundPosition: '0% 50%' }, '50%': { backgroundPosition: '100% 50%' } },
                    },
                    backgroundSize: { '300%': '300%' },
                    boxShadow: {
                        'soft': '0 4px 24px -4px rgba(79,70,229,0.12)',
                        'glow': '0 0 40px rgba(79,70,229,0.25)',
                        'glow-amber': '0 0 40px rgba(245,158,11,0.3)',
                    },
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Merriweather:wght@400;700;900&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        .gradient-text {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 40%, #f59e0b 100%);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradient 4s ease infinite;
        }

        .hero-bg {
            background: radial-gradient(ellipse 80% 80% at 50% -10%, rgba(79,70,229,0.18) 0%, transparent 70%),
                        radial-gradient(ellipse 60% 60% at 80% 60%, rgba(245,158,11,0.08) 0%, transparent 60%),
                        radial-gradient(ellipse 60% 60% at 10% 80%, rgba(124,58,237,0.08) 0%, transparent 60%);
        }

        .glass {
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.5);
        }

        .dark .glass {
            background: rgba(30,27,75,0.6);
            border: 1px solid rgba(79,70,229,0.2);
        }

        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 60px -10px rgba(79,70,229,0.2);
        }

        .step-line::after {
            content: '';
            position: absolute;
            top: 50%;
            left: calc(100% + 1rem);
            width: calc(100% - 2rem);
            height: 2px;
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
            opacity: 0.3;
        }

        .nav-link {
            position: relative;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #4f46e5, #f59e0b);
            transition: width 0.3s ease;
        }
        .nav-link:hover::after { width: 100%; }

        .particle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes gradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .observe-animate { opacity: 0; transform: translateY(30px); transition: all 0.7s ease-out; }
        .observe-animate.visible { opacity: 1; transform: translateY(0); }
        .observe-delay-1 { transition-delay: 0.1s; }
        .observe-delay-2 { transition-delay: 0.2s; }
        .observe-delay-3 { transition-delay: 0.3s; }
        .observe-delay-4 { transition-delay: 0.4s; }

        .badge-glow { box-shadow: 0 0 20px rgba(245,158,11,0.5); }
    </style>
</head>
<body class="font-sans bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 transition-colors duration-300 overflow-x-hidden">

<!-- ========== HEADER / NAVBAR ========== -->
<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 20">
    <div :class="scrolled ? 'glass shadow-soft' : 'bg-transparent'"
         class="transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-20">

                <!-- Logo -->
                <a href="/" class="flex items-center gap-2 group">
                    <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-violet-600 rounded-xl flex items-center justify-center shadow-glow group-hover:scale-110 transition-transform duration-300">
                        <span class="text-lg">📚</span>
                    </div>
                    <span class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                        Kitob<span class="text-indigo-600">xon</span>
                    </span>
                </a>

                <!-- Desktop Nav -->
                <nav class="hidden lg:flex items-center gap-8">
                    <a href="/" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">Bosh sahifa</a>
                    <a href="/books" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">Kitoblar</a>
                    <a href="/about" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">Haqimizda</a>
                    <a href="/contact" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">Aloqa</a>
                    <a href="/faq" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">FAQ</a>
                </nav>

                <!-- Actions -->
                <div class="hidden lg:flex items-center gap-3">
                    <!-- Dark Mode Toggle -->
                    <button @click="darkMode = !darkMode"
                            class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors duration-200">
                        <span x-show="!darkMode" class="text-base">🌙</span>
                        <span x-show="darkMode" class="text-base">☀️</span>
                    </button>
                    <a href="/login"
                       class="px-4 py-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 transition-all duration-200">
                        Kirish
                    </a>
                    <a href="/register"
                       class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 rounded-xl hover:from-indigo-700 hover:to-violet-700 shadow-glow hover:shadow-none transition-all duration-300 hover:scale-105">
                        Ro'yxatdan o'tish
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenu = !mobileMenu"
                        class="lg:hidden w-10 h-10 flex flex-col items-center justify-center gap-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <span :class="mobileMenu ? 'rotate-45 translate-y-2' : ''" class="w-5 h-0.5 bg-slate-700 dark:bg-slate-300 transition-transform duration-300"></span>
                    <span :class="mobileMenu ? 'opacity-0' : ''" class="w-5 h-0.5 bg-slate-700 dark:bg-slate-300 transition-opacity duration-300"></span>
                    <span :class="mobileMenu ? '-rotate-45 -translate-y-2' : ''" class="w-5 h-0.5 bg-slate-700 dark:bg-slate-300 transition-transform duration-300"></span>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenu" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="lg:hidden glass border-t border-white/20 dark:border-slate-700/50">
            <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col gap-2">
                <a href="/" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">Bosh sahifa</a>
                <a href="/books" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">Kitoblar</a>
                <a href="/about" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">Haqimizda</a>
                <a href="/contact" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">Aloqa</a>
                <a href="/faq" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">FAQ</a>
                <div class="flex gap-3 pt-2 border-t border-slate-200 dark:border-slate-700">
                    <a href="/login" class="flex-1 py-2.5 text-center font-semibold text-indigo-600 border border-indigo-200 dark:border-indigo-800 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 transition-colors">Kirish</a>
                    <a href="/register" class="flex-1 py-2.5 text-center font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 rounded-xl shadow-glow transition-all">Ro'yxatdan o'tish</a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- ========== HERO SECTION ========== -->
<section class="relative min-h-screen flex items-center hero-bg pt-20 overflow-hidden">

    <!-- Background Particles -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="particle w-72 h-72 bg-indigo-200/20 dark:bg-indigo-900/20 top-20 -left-20 blur-3xl" style="animation-delay:0s;"></div>
        <div class="particle w-96 h-96 bg-violet-200/20 dark:bg-violet-900/20 top-40 right-10 blur-3xl" style="animation-delay:1s;"></div>
        <div class="particle w-64 h-64 bg-amber-200/20 dark:bg-amber-900/20 bottom-20 left-1/3 blur-3xl" style="animation-delay:2s;"></div>

        <!-- Floating Book Icons -->
        <div class="absolute top-32 right-[15%] text-5xl opacity-20 dark:opacity-10 animate-float" style="animation-delay:0.5s;">📖</div>
        <div class="absolute top-1/2 right-[8%] text-4xl opacity-15 dark:opacity-10 animate-float" style="animation-delay:1.5s;">🎵</div>
        <div class="absolute bottom-32 right-[25%] text-4xl opacity-15 dark:opacity-10 animate-float" style="animation-delay:2.5s;">🎥</div>
        <div class="absolute top-40 left-[12%] text-3xl opacity-20 dark:opacity-10 animate-float" style="animation-delay:1s;">⭐</div>
        <div class="absolute bottom-40 left-[8%] text-4xl opacity-15 dark:opacity-10 animate-float" style="animation-delay:3s;">🏆</div>

        <!-- Grid Pattern -->
        <div class="absolute inset-0 opacity-[0.03] dark:opacity-[0.05]"
             style="background-image: radial-gradient(circle, #4f46e5 1px, transparent 1px); background-size: 40px 40px;"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
        <div class="max-w-4xl mx-auto text-center">

            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-50 dark:bg-indigo-950 border border-indigo-100 dark:border-indigo-900 text-indigo-700 dark:text-indigo-300 text-sm font-semibold mb-8 animate-fade-in">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse-slow"></span>
                🚀 1000+ o'quvchi bilan birlashing
            </div>

            <!-- Main Headline -->
            <h1 class="text-5xl sm:text-6xl lg:text-7xl xl:text-8xl font-black leading-[1.05] tracking-tight mb-6 animate-slide-up">
                <span class="text-slate-900 dark:text-white">Har hafta</span><br>
                <span class="gradient-text">bitta kitob,</span><br>
                <span class="text-slate-900 dark:text-white">cheksiz bilim</span>
            </h1>

            <!-- Subtitle -->
            <p class="text-lg sm:text-xl lg:text-2xl text-slate-500 dark:text-slate-400 font-light leading-relaxed max-w-2xl mx-auto mb-10 animate-slide-up" style="animation-delay:0.2s;">
                Kitobxon — o'zbek tilidagi birinchi interaktiv kitob o'qish platformasi.
                <strong class="text-slate-700 dark:text-slate-200 font-semibold">Matn, audio va video</strong> formatlarida o'qing, bahsing va o'sing.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 animate-scale-in" style="animation-delay:0.4s;">
                <a href="/register"
                   class="group relative w-full sm:w-auto px-8 py-4 text-lg font-bold text-white bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 rounded-2xl shadow-glow hover:shadow-none transition-all duration-300 hover:scale-105 hover:-translate-y-1 overflow-hidden">
                    <span class="relative z-10 flex items-center gap-2">
                        🚀 Boshlash
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </span>
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-700 via-violet-700 to-indigo-800 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </a>
                <a href="/about"
                   class="group w-full sm:w-auto px-8 py-4 text-lg font-bold text-slate-700 dark:text-slate-200 bg-white/80 dark:bg-slate-800/80 backdrop-blur border border-slate-200 dark:border-slate-700 rounded-2xl hover:border-indigo-300 dark:hover:border-indigo-700 hover:bg-white dark:hover:bg-slate-800 transition-all duration-300 hover:scale-105 hover:-translate-y-1 shadow-soft">
                    <span class="flex items-center gap-2">
                        💡 Ko'proq bilish
                    </span>
                </a>
            </div>

            <!-- Social Proof -->
            <div class="mt-14 flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-10 animate-fade-in" style="animation-delay:0.6s;">
                <div class="flex -space-x-3">
                    @foreach(['🧑‍💻','👩‍🎨','🧑‍🏫','👩‍💼','🧑‍🔬'] as $emoji)
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-violet-500 flex items-center justify-center text-sm border-2 border-white dark:border-slate-900 shadow-md">{{ $emoji }}</div>
                    @endforeach
                </div>
                <div class="text-left">
                    <div class="flex items-center gap-1 text-amber-500">
                        @for($i=0;$i<5;$i++)<svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">1000+ o'quvchi ishonadi</p>
                </div>
                <div class="hidden sm:block w-px h-10 bg-slate-200 dark:bg-slate-700"></div>
                <div class="text-center sm:text-left">
                    <p class="text-2xl font-black text-indigo-600">50+</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Kitob mavjud</p>
                </div>
                <div class="hidden sm:block w-px h-10 bg-slate-200 dark:bg-slate-700"></div>
                <div class="text-center sm:text-left">
                    <p class="text-2xl font-black text-indigo-600">100%</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Bepul boshlash</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 animate-bounce-slow opacity-50">
        <span class="text-xs font-medium text-slate-500 dark:text-slate-400 tracking-widest uppercase">Pastga</span>
        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
</section>

<!-- ========== STATS STRIP ========== -->
<section class="relative bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center text-white">
            <div class="observe-animate">
                <p class="text-4xl lg:text-5xl font-black">1000+</p>
                <p class="text-indigo-200 font-medium mt-1">Faol o'quvchilar</p>
            </div>
            <div class="observe-animate observe-delay-1">
                <p class="text-4xl lg:text-5xl font-black">50+</p>
                <p class="text-indigo-200 font-medium mt-1">Premium kitoblar</p>
            </div>
            <div class="observe-animate observe-delay-2">
                <p class="text-4xl lg:text-5xl font-black">3</p>
                <p class="text-indigo-200 font-medium mt-1">Formatlar (matn/audio/video)</p>
            </div>
            <div class="observe-animate observe-delay-3">
                <p class="text-4xl lg:text-5xl font-black">∞</p>
                <p class="text-indigo-200 font-medium mt-1">Bilim imkoniyatlari</p>
            </div>
        </div>
    </div>
</section>

<!-- ========== FEATURES SECTION ========== -->
<section class="py-24 lg:py-32 bg-white dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto mb-16 observe-animate">
            <span class="inline-block px-4 py-1.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 text-sm font-bold rounded-full mb-4 tracking-wide uppercase">Imkoniyatlar</span>
            <h2 class="text-4xl lg:text-5xl font-black text-slate-900 dark:text-white leading-tight">
                Uch formatda <span class="gradient-text">o'qing</span>
            </h2>
            <p class="text-slate-500 dark:text-slate-400 mt-4 text-lg leading-relaxed">
                Qaysi usul sizga qulay bo'lsa, o'sha formatda kitob o'qing. Hammasi bir joyda.
            </p>
        </div>

        <!-- Feature Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <!-- Card 1: Text -->
            <div class="card-hover observe-animate observe-delay-1 group relative p-8 bg-slate-50 dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 to-transparent dark:from-indigo-950/30 dark:to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                <div class="relative">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-2xl flex items-center justify-center text-3xl shadow-glow mb-6 group-hover:scale-110 transition-transform duration-300">
                        📖
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-3">Matn Formati</h3>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed mb-6">
                        Qulay o'qish rejimi, shrift o'lchamini moslashtirish, so'zlarni belgilash va eslatma qo'shish imkoniyati bilan kitob o'qing.
                    </p>
                    <ul class="space-y-2.5">
                        @foreach(['Shrift sozlamalari', 'O\'qish rejimi', 'Belgilash & eslatma', 'Offline o\'qish'] as $feature)
                        <li class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300">
                            <span class="w-5 h-5 bg-green-100 dark:bg-green-900/30 text-green-600 rounded-full flex items-center justify-center flex-shrink-0 text-xs">✓</span>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Card 2: Audio (Featured) -->
            <div class="card-hover observe-animate observe-delay-2 group relative p-8 bg-gradient-to-br from-indigo-600 to-violet-700 rounded-3xl overflow-hidden shadow-glow md:-mt-4">
                <div class="absolute top-4 right-4 px-3 py-1 bg-amber-400 text-amber-900 text-xs font-black rounded-full shadow-glow-amber">
                    ⭐ MASHHUR
                </div>
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-32 h-32 bg-white rounded-full translate-y-1/2 -translate-x-1/2"></div>
                </div>
                <div class="relative">
                    <div class="w-16 h-16 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform duration-300">
                        🎵
                    </div>
                    <h3 class="text-2xl font-black text-white mb-3">Audio Formati</h3>
                    <p class="text-indigo-200 leading-relaxed mb-6">
                        Professional diktorlar tomonidan o'qilgan audio kitoblarni tinglang. Harakatda bo'lganingizda ham o'qishni davom ettiring.
                    </p>
                    <ul class="space-y-2.5">
                        @foreach(['Professional diktorsiz', 'Tezlikni boshqarish', 'Uxlash taymer', 'Fon rejimi'] as $feature)
                        <li class="flex items-center gap-2.5 text-sm text-indigo-100">
                            <span class="w-5 h-5 bg-white/20 text-white rounded-full flex items-center justify-center flex-shrink-0 text-xs">✓</span>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Card 3: Video -->
            <div class="card-hover observe-animate observe-delay-3 group relative p-8 bg-slate-50 dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-50 to-transparent dark:from-amber-950/20 dark:to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                <div class="relative">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center text-3xl shadow-glow-amber mb-6 group-hover:scale-110 transition-transform duration-300">
                        🎥
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-3">Video Formati</h3>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed mb-6">
                        Kitob bo'yicha tayyorlangan video darslar, muallif intervyulari va muhokama sessiyalarini tomosha qiling.
                    </p>
                    <ul class="space-y-2.5">
                        @foreach(['HD sifat', 'Altyazı', 'Hissalar bo\'yicha', 'Muhokama'] as $feature)
                        <li class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300">
                            <span class="w-5 h-5 bg-amber-100 dark:bg-amber-900/30 text-amber-600 rounded-full flex items-center justify-center flex-shrink-0 text-xs">✓</span>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== HOW IT WORKS ========== -->
<section class="py-24 lg:py-32 bg-slate-50 dark:bg-slate-800/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center max-w-2xl mx-auto mb-16 observe-animate">
            <span class="inline-block px-4 py-1.5 bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 text-sm font-bold rounded-full mb-4 tracking-wide uppercase">Qanday ishlaydi</span>
            <h2 class="text-4xl lg:text-5xl font-black text-slate-900 dark:text-white leading-tight">
                3 qadamda <span class="gradient-text">boshlang</span>
            </h2>
            <p class="text-slate-500 dark:text-slate-400 mt-4 text-lg">Ro'yxatdan o'tishdan kitob o'qishgacha — faqat 3 daqiqa.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">

            <!-- Connecting Lines (hidden on mobile) -->
            <div class="hidden md:block absolute top-16 left-1/3 w-1/3 h-0.5 bg-gradient-to-r from-indigo-300 to-violet-300 dark:from-indigo-700 dark:to-violet-700"></div>
            <div class="hidden md:block absolute top-16 left-2/3 w-1/3 h-0.5 bg-gradient-to-r from-violet-300 to-amber-300 dark:from-violet-700 dark:to-amber-700"></div>

            @php
                $steps = [
                    ['num'=>'01','icon'=>'📝','title'=>'Ro\'yxatdan o\'ting','desc'=>'Bepul hisob yarating. Email va parol yetarli — 30 soniyada tayyor.','color'=>'indigo','delay'=>'observe-delay-1'],
                    ['num'=>'02','icon'=>'📚','title'=>'Kitob tanlang','desc'=>'50+ kitob katalogidan haftalik tanlov qiling yoki o\'zingiz istagan kitobni toping.','color'=>'violet','delay'=>'observe-delay-2'],
                    ['num'=>'03','icon'=>'🏆','title'=>'O\'qing va yuting','desc'=>'O\'qiganingiz uchun ball to\'plang, yutuqlar qozonishing, hamjamiyat bilan bahslashing.','color'=>'amber','delay'=>'observe-delay-3'],
                ];
            @endphp

            @foreach($steps as $step)
            <div class="observe-animate {{ $step['delay'] }} relative flex flex-col items-center text-center">
                <!-- Number Badge -->
                <div class="relative mb-6">
                    <div class="w-32 h-32 rounded-3xl bg-white dark:bg-slate-800 shadow-soft border border-slate-100 dark:border-slate-700 flex items-center justify-center text-5xl group-hover:scale-110 transition-transform duration-300">
                        {{ $step['icon'] }}
                    </div>
                    <div class="absolute -top-3 -right-3 w-8 h-8 bg-gradient-to-br from-{{ $step['color'] }}-500 to-{{ $step['color'] }}-700 rounded-xl flex items-center justify-center text-white text-xs font-black shadow-md">
                        {{ $step['num'] }}
                    </div>
                </div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white mb-3">{{ $step['title'] }}</h3>
                <p class="text-slate-500 dark:text-slate-400 leading-relaxed max-w-xs">{{ $step['desc'] }}</p>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-14 observe-animate">
            <a href="/register" class="inline-flex items-center gap-2 px-8 py-4 text-lg font-bold text-white bg-gradient-to-r from-indigo-600 to-violet-600 rounded-2xl shadow-glow hover:shadow-none transition-all duration-300 hover:scale-105 hover:-translate-y-1">
                🚀 Hoziroq boshlang — Bepul!
            </a>
        </div>
    </div>
</section>

<!-- ========== GAMIFICATION SECTION ========== -->
<section class="py-24 lg:py-32 bg-slate-900 relative overflow-hidden">
    <!-- Background effects -->
    <div class="absolute inset-0">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-900/30 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-violet-900/30 rounded-full blur-3xl"></div>
        <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle, #818cf8 1px, transparent 1px); background-size: 30px 30px;"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

            <!-- Left: Text -->
            <div class="observe-animate">
                <span class="inline-block px-4 py-1.5 bg-amber-500/10 text-amber-400 text-sm font-bold rounded-full mb-6 tracking-wide uppercase border border-amber-500/20">
                    🎮 Gamifikatsiya
                </span>
                <h2 class="text-4xl lg:text-5xl font-black text-white leading-tight mb-6">
                    O'qish endi<br>
                    <span class="gradient-text">o'yin kabi</span><br>
                    qiziqarli!
                </h2>
                <p class="text-slate-400 text-lg leading-relaxed mb-8">
                    Ball to'plang, yutuqlar qozonib, hamdo'stlaringizni quvib o'ting. Har bir kitob yangi imkoniyat!
                </p>

                <div class="space-y-4">
                    @php
                        $gamFeatures = [
                            ['icon'=>'🔥','title'=>'Kunlik Streak','desc'=>'Har kuni o\'qib, seriya yoqing. 7 kunlik streakda maxsus mukofot!','color'=>'orange'],
                            ['icon'=>'⚡','title'=>'XP Ballar','desc'=>'Har sahifa, har test, har muhokamada ball to\'plang.','color'=>'indigo'],
                            ['icon'=>'🏆','title'=>'Yutuqlar (Badges)','desc'=>'100+ maxsus nishon. Kitob qurtigacha, tarjibali o\'quvchigacha!','color'=>'amber'],
                        ];
                    @endphp
                    @foreach($gamFeatures as $gf)
                    <div class="flex items-start gap-4 p-4 bg-white/5 dark:bg-white/5 backdrop-blur rounded-2xl border border-white/10 hover:border-indigo-500/30 transition-colors duration-300">
                        <div class="w-12 h-12 bg-{{ $gf['color'] }}-500/20 rounded-xl flex items-center justify-center text-2xl flex-shrink-0">{{ $gf['icon'] }}</div>
                        <div>
                            <h4 class="font-bold text-white mb-1">{{ $gf['title'] }}</h4>
                            <p class="text-slate-400 text-sm">{{ $gf['desc'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Right: Gamification Preview Card -->
            <div class="observe-animate observe-delay-2">
                <div class="relative">
                    <!-- Glow -->
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/30 to-violet-600/30 rounded-3xl blur-2xl scale-105"></div>

                    <!-- Card -->
                    <div class="relative bg-slate-800 rounded-3xl border border-slate-700 p-8 shadow-2xl">
                        <!-- Profile Header -->
                        <div class="flex items-center gap-4 mb-6 pb-6 border-b border-slate-700">
                            <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-2xl flex items-center justify-center text-2xl">👨‍💻</div>
                            <div>
                                <p class="font-bold text-white">Alisher T.</p>
                                <p class="text-slate-400 text-sm">🔥 14 kunlik streak</p>
                            </div>
                            <div class="ml-auto text-right">
                                <p class="text-2xl font-black text-amber-400">2,840</p>
                                <p class="text-slate-500 text-xs">XP ball</p>
                            </div>
                        </div>

                        <!-- Progress -->
                        <div class="mb-6">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-slate-400">Haftalik maqsad</span>
                                <span class="text-indigo-400 font-semibold">68%</span>
                            </div>
                            <div class="h-3 bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full w-[68%] bg-gradient-to-r from-indigo-500 to-violet-500 rounded-full relative">
                                    <div class="absolute inset-0 bg-white/20 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Badges -->
                        <div class="mb-6">
                            <p class="text-slate-400 text-sm mb-3 font-medium">So'nggi yutuqlar</p>
                            <div class="flex gap-3 flex-wrap">
                                @php
                                    $badges = [
                                        ['icon'=>'🔥','label'=>'7-kun','glow'=>true],
                                        ['icon'=>'📚','label'=>'10 kitob','glow'=>false],
                                        ['icon'=>'⚡','label'=>'Tezkor','glow'=>false],
                                        ['icon'=>'🌟','label'=>'Top-10','glow'=>true],
                                        ['icon'=>'💬','label'=>'Faol','glow'=>false],
                                    ];
                                @endphp
                                @foreach($badges as $badge)
                                <div class="flex flex-col items-center gap-1">
                                    <div class="w-12 h-12 bg-slate-700 rounded-2xl flex items-center justify-center text-xl {{ $badge['glow'] ? 'badge-glow border border-amber-500/30' : '' }}">
                                        {{ $badge['icon'] }}
                                    </div>
                                    <span class="text-xs text-slate-500">{{ $badge['label'] }}</span>
                                </div>
                                @endforeach
                                <div class="flex flex-col items-center gap-1">
                                    <div class="w-12 h-12 bg-slate-700/50 rounded-2xl flex items-center justify-center text-slate-600 border-2 border-dashed border-slate-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    </div>
                                    <span class="text-xs text-slate-600">+12 ta</span>
                                </div>
                            </div>
                        </div>

                        <!-- Streak Days -->
                        <div>
                            <p class="text-slate-400 text-sm mb-3 font-medium">Bu hafta</p>
                            <div class="flex gap-2">
                                @foreach(['Du','Se','Ch','Pa','Ju','Sh','Ya'] as $i => $day)
                                <div class="flex-1 flex flex-col items-center gap-1.5">
                                    <div class="w-full h-8 rounded-lg {{ $i < 5 ? 'bg-indigo-500 shadow-glow' : 'bg-slate-700' }} flex items-center justify-center">
                                        {{ $i < 5 ? '🔥' : '' }}
                                    </div>
                                    <span class="text-xs text-slate-500">{{ $day }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== COMMUNITY SECTION ========== -->
<section class="py-24 lg:py-32 bg-white dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center max-w-2xl mx-auto mb-16 observe-animate">
            <span class="inline-block px-4 py-1.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 text-sm font-bold rounded-full mb-4 tracking-wide uppercase">Hamjamiyat</span>
            <h2 class="text-4xl lg:text-5xl font-black text-slate-900 dark:text-white leading-tight">
                Yolg'iz emas, <span class="gradient-text">birga o'qing!</span>
            </h2>
            <p class="text-slate-500 dark:text-slate-400 mt-4 text-lg">1000+ o'quvchi bilan muhokama, savollar va ilhom almashish.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <!-- Chat -->
            <div class="card-hover observe-animate observe-delay-1 p-8 bg-gradient-to-br from-indigo-50 to-violet-50 dark:from-indigo-950/30 dark:to-violet-950/30 rounded-3xl border border-indigo-100 dark:border-indigo-900">
                <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-glow">💬</div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white mb-3">Jonli Chat</h3>
                <p class="text-slate-500 dark:text-slate-400 leading-relaxed mb-4">Kitob bo'yicha fikrlaringizni real vaqtda ulashing. Savollaringizga darhol javob oling.</p>
                <div class="space-y-3">
                    @foreach([
                        ['name'=>'Malika S.','msg'=>'Bu bobni tushunmadim, kimdir tushuntira oladi?','time'=>'2 daqiqa'],
                        ['name'=>'Jasur K.','msg'=>'Men ham shu savolni bermoqchi edim! 😅','time'=>'1 daqiqa'],
                    ] as $chat)
                    <div class="flex gap-3 p-3 bg-white dark:bg-slate-800 rounded-2xl shadow-soft">
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-400 to-violet-500 rounded-full flex items-center justify-center text-xs text-white font-bold flex-shrink-0">{{ substr($chat['name'],0,1) }}</div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $chat['name'] }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $chat['msg'] }}</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500">{{ $chat['time'] }} oldin</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Groups -->
            <div class="card-hover observe-animate observe-delay-2 p-8 bg-gradient-to-br from-violet-50 to-purple-50 dark:from-violet-950/30 dark:to-purple-950/30 rounded-3xl border border-violet-100 dark:border-violet-900">
                <div class="w-16 h-16 bg-violet-600 rounded-2xl flex items-center justify-center text-3xl mb-6">👥</div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white mb-3">O'qish Guruhlar</h3>
                <p class="text-slate-500 dark:text-slate-400 leading-relaxed mb-4">Bir xil kitobni o'qiyotganlar bilan guruh tuzing. Haftalik muhokamalarda qatnashing.</p>
                <div class="space-y-3">
                    @foreach([
                        ['name'=>'Biznes & Moliya','members'=>'47 a\'zo','emoji'=>'💼'],
                        ['name'=>'Shaxsiy O\'sish','members'=>'89 a\'zo','emoji'=>'🌱'],
                        ['name'=>'Ilm-fan & Texnologiya','members'=>'34 a\'zo','emoji'=>'🔬'],
                    ] as $group)
                    <div class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-2xl shadow-soft">
                        <span class="text-2xl">{{ $group['emoji'] }}</span>
                        <div>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $group['name'] }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $group['members'] }}</p>
                        </div>
                        <div class="ml-auto w-6 h-6 bg-violet-100 dark:bg-violet-900 text-violet-600 dark:text-violet-400 rounded-full flex items-center justify-center">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Live Events -->
            <div class="card-hover observe-animate observe-delay-3 p-8 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/20 dark:to-orange-950/20 rounded-3xl border border-amber-100 dark:border-amber-900">
                <div class="w-16 h-16 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-glow-amber">📅</div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white mb-3">Jonli Tadbirlar</h3>
                <p class="text-slate-500 dark:text-slate-400 leading-relaxed mb-4">Muallif suhbatlari, ekspert tahlillar va haftalik kitob klublari.</p>
                <div class="space-y-3">
                    @foreach([
                        ['title'=>'Atom odatlari muhokama','date'=>'Juma, 19:00','tag'=>'YAQIN'],
                        ['title'=>'Muallif suhbati: Dilnoza R.','date'=>'Shanba, 15:00','tag'=>'YANGI'],
                    ] as $event)
                    <div class="p-3 bg-white dark:bg-slate-800 rounded-2xl shadow-soft">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $event['title'] }}</p>
                            <span class="text-xs font-black px-2 py-0.5 bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 rounded-lg flex-shrink-0">{{ $event['tag'] }}</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $event['date'] }}
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== TESTIMONIALS ========== -->
<section class="py-24 lg:py-32 bg-slate-50 dark:bg-slate-800/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16 observe-animate">
            <span class="inline-block px-4 py-1.5 bg-green-50 dark:bg-green-950 text-green-700 dark:text-green-300 text-sm font-bold rounded-full mb-4 tracking-wide uppercase">Izohlar</span>
            <h2 class="text-4xl lg:text-5xl font-black text-slate-900 dark:text-white leading-tight">
                O'quvchilar <span class="gradient-text">nima deydi</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @php
                $testimonials = [
                    ['name'=>'Aziza M.','role'=>'Biznes tahlilchi','emoji'=>'👩‍💼','text'=>'Kitobxon hayotimni o\'zgartirdi! Har kuni ishga borishda audio kitob tinglaymen. Streakni to\'xtatmagan 40 kun bo\'ldi!','rating'=>5,'badge'=>'🔥 40-kun'],
                    ['name'=>'Bobur T.','role'=>'IT mutaxassisi','emoji'=>'👨‍💻','text'=>'Gamifikatsiya elementi zo\'r! O\'qiyotganda ball to\'plab, leaderboardda birinchi bo\'lishga harakat qilaman. Hamjamiyat ham juda faol.','rating'=>5,'badge'=>'⭐ Top-3'],
                    ['name'=>'Nilufar K.','role'=>'O\'qituvchi','emoji'=>'👩‍🏫','text'=>'Sifatli tarjimalar va professional audio o\'qish. O\'quvchilarimga ham tavsiya qilaman. Platformaning dizayni ham juda qulay.','rating'=>5,'badge'=>'📚 50-kitob'],
                ];
            @endphp
            @foreach($testimonials as $t)
            <div class="card-hover observe-animate observe-delay-{{ $loop->iteration }} p-8 bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-soft">
                <!-- Stars -->
                <div class="flex gap-1 mb-4">
                    @for($i=0;$i<$t['rating'];$i++)<svg class="w-5 h-5 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                </div>
                <!-- Quote -->
                <p class="text-slate-600 dark:text-slate-300 leading-relaxed mb-6 italic">"{{ $t['text'] }}"</p>
                <!-- Author -->
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-400 to-violet-500 rounded-2xl flex items-center justify-center text-xl">{{ $t['emoji'] }}</div>
                    <div>
                        <p class="font-bold text-slate-900 dark:text-white">{{ $t['name'] }}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $t['role'] }}</p>
                    </div>
                    <div class="ml-auto px-3 py-1 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 text-xs font-bold rounded-xl">{{ $t['badge'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ========== CTA SECTION ========== -->
<section class="py-24 lg:py-32 relative overflow-hidden bg-gradient-to-br from-indigo-900 via-violet-900 to-slate-900">
    <!-- BG effects -->
    <div class="absolute inset-0">
        <div class="absolute top-0 left-0 w-full h-full opacity-30"
             style="background-image: radial-gradient(ellipse 80% 80% at 50% 50%, rgba(79,70,229,0.4) 0%, transparent 70%);"></div>
        <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 40px 40px;"></div>
    </div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="observe-animate">
            <div class="inline-flex items-center gap-2 px-5 py-2 bg-white/10 backdrop-blur text-white/80 text-sm font-semibold rounded-full mb-8 border border-white/20">
                ✨ Bepul boshlang, istagan vaqt to'xtating
            </div>

            <h2 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white leading-tight mb-6">
                Hoziroq<br>
                <span class="gradient-text">qo'shiling!</span>
            </h2>

            <p class="text-indigo-200 text-xl leading-relaxed mb-10 max-w-xl mx-auto">
                1000+ o'quvchi allaqachon o'qish sayohatini boshladi. Siz nima kutayapsiz?
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="/register"
                   class="group w-full sm:w-auto px-10 py-5 text-lg font-black text-indigo-900 bg-white rounded-2xl hover:bg-indigo-50 shadow-2xl transition-all duration-300 hover:scale-105 hover:-translate-y-1 flex items-center justify-center gap-2">
                    🚀 Bepul Ro'yxatdan O'tish
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <a href="/books"
                   class="group w-full sm:w-auto px-10 py-5 text-lg font-bold text-white bg-white/10 backdrop-blur rounded-2xl border border-white/20 hover:bg-white/20 transition-all duration-300 hover:scale-105 hover:-translate-y-1 flex items-center justify-center gap-2">
                    📚 Kitoblarni Ko'rish
                </a>
            </div>

            <!-- Trust Badges -->
            <div class="flex flex-wrap items-center justify-center gap-6 mt-12 opacity-60">
                <div class="flex items-center gap-2 text-white text-sm font-medium">
                    <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Karta talab etilmaydi
                </div>
                <div class="flex items-center gap-2 text-white text-sm font-medium">
                    <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Istalgan vaqt bekor qiling
                </div>
                <div class="flex items-center gap-2 text-white text-sm font-medium">
                    <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    SSL himoyalangan
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== FOOTER ========== -->
<footer class="bg-slate-900 dark:bg-slate-950 text-slate-400 border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">

            <!-- Brand -->
            <div class="lg:col-span-1">
                <a href="/" class="flex items-center gap-2 group mb-4">
                    <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-violet-600 rounded-xl flex items-center justify-center">📚</div>
                    <span class="text-xl font-black text-white">Kitob<span class="text-indigo-400">xon</span></span>
                </a>
                <p class="text-slate-500 text-sm leading-relaxed mb-6">O'zbek tilidagi eng yaxshi onlayn kitob o'qish platformasi. Har hafta bitta kitob, cheksiz bilim.</p>
                <div class="flex gap-3">
                    @foreach([
                        ['icon'=>'🐦','label'=>'Twitter','href'=>'#'],
                        ['icon'=>'📘','label'=>'Facebook','href'=>'#'],
                        ['icon'=>'📸','label'=>'Instagram','href'=>'#'],
                        ['icon'=>'▶️','label'=>'YouTube','href'=>'#'],
                    ] as $social)
                    <a href="{{ $social['href'] }}" title="{{ $social['label'] }}"
                       class="w-9 h-9 bg-slate-800 hover:bg-indigo-600 rounded-xl flex items-center justify-center transition-colors duration-200 text-sm">
                        {{ $social['icon'] }}
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Platform -->
            <div>
                <h4 class="font-bold text-white mb-5">Platforma</h4>
                <ul class="space-y-3">
                    @foreach([['Kitoblar','/books'],['Haqimizda','/about'],['FAQ','/faq'],['Aloqa','/contact']] as $link)
                    <li><a href="{{ $link[1] }}" class="text-slate-500 hover:text-indigo-400 transition-colors duration-200 text-sm">{{ $link[0] }}</a></li>
                    @endforeach
                </ul>
            </div>

            <!-- Legal -->
            <div>
                <h4 class="font-bold text-white mb-5">Huquqiy</h4>
                <ul class="space-y-3">
                    @foreach([['Maxfiylik siyosati','/privacy'],['Foydalanish shartlari','/terms'],['Cookie siyosati','/cookies'],['Litsenziya','/license']] as $link)
                    <li><a href="{{ $link[1] }}" class="text-slate-500 hover:text-indigo-400 transition-colors duration-200 text-sm">{{ $link[0] }}</a></li>
                    @endforeach
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4 class="font-bold text-white mb-5">Bog'laning</h4>
                <ul class="space-y-3">
                    <li class="flex items-center gap-2 text-sm text-slate-500">
                        <span class="text-indigo-400">📧</span> info@kitobxon.uz
                    </li>
                    <li class="flex items-center gap-2 text-sm text-slate-500">
                        <span class="text-indigo-400">📞</span> +998 71 000-00-00
                    </li>
                    <li class="flex items-center gap-2 text-sm text-slate-500">
                        <span class="text-indigo-400">📍</span> Toshkent, O'zbekiston
                    </li>
                </ul>

                <!-- Newsletter -->
                <div class="mt-6">
                    <p class="text-sm font-semibold text-white mb-3">Yangiliklar olish</p>
                    <div class="flex gap-2">
                        <input type="email" placeholder="Email manzilingiz" class="flex-1 px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 min-w-0">
                        <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors duration-200">→</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-slate-600 text-sm">© {{ date('Y') }} Kitobxon. Barcha huquqlar himoyalangan.</p>
            <div class="flex items-center gap-6">
                <a href="/privacy" class="text-slate-600 hover:text-indigo-400 text-sm transition-colors">Maxfiylik</a>
                <a href="/terms" class="text-slate-600 hover:text-indigo-400 text-sm transition-colors">Shartlar</a>
                <a href="/contact" class="text-slate-600 hover:text-indigo-400 text-sm transition-colors">Aloqa</a>
            </div>
            <div class="flex items-center gap-2 text-slate-600 text-sm">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                Barcha tizimlar ishlaydi
            </div>
        </div>
    </div>
</footer>

<!-- ========== INTERSECTION OBSERVER ANIMATION ========== -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        document.querySelectorAll('.observe-animate').forEach(el => observer.observe(el));
    });
</script>

</body>
</html>
