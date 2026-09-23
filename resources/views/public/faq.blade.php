<!DOCTYPE html>
<html lang="uz" x-data="{ darkMode: false, mobileMenu: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kitobxon FAQ — Tez-tez so'raladigan savollarga javoblar.">
    <title>FAQ — Kitobxon 📚</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                        serif: ['Merriweather', 'ui-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out forwards',
                        'slide-up': 'slideUp 0.7s ease-out forwards',
                        'float': 'float 3s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                        slideUp: { '0%': { opacity: '0', transform: 'translateY(40px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                        float: { '0%,100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-12px)' } },
                    },
                    boxShadow: {
                        'soft': '0 4px 24px -4px rgba(79,70,229,0.12)',
                        'glow': '0 0 40px rgba(79,70,229,0.25)',
                    },
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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
        @keyframes gradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
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
        .nav-link { position: relative; }
        .nav-link::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 0; height: 2px; background: linear-gradient(90deg, #4f46e5, #f59e0b); transition: width 0.3s ease; }
        .nav-link:hover::after { width: 100%; }
        .observe-animate { opacity: 0; transform: translateY(30px); transition: all 0.7s ease-out; }
        .observe-animate.visible { opacity: 1; transform: translateY(0); }
        .observe-delay-1 { transition-delay: 0.05s; }
        .observe-delay-2 { transition-delay: 0.1s; }
        .observe-delay-3 { transition-delay: 0.15s; }
        .observe-delay-4 { transition-delay: 0.2s; }
        .observe-delay-5 { transition-delay: 0.25s; }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .faq-answer.open {
            max-height: 500px;
        }

        .search-input {
            width: 100%;
            padding: 1rem 1.25rem 1rem 3.5rem;
            background: white;
            border: 2px solid rgb(226 232 240);
            border-radius: 1rem;
            font-size: 1rem;
            color: rgb(15 23 42);
            transition: all 0.2s ease;
            outline: none;
        }
        .dark .search-input {
            background: rgb(30 41 59);
            border-color: rgb(51 65 85);
            color: rgb(226 232 240);
        }
        .search-input:focus {
            border-color: rgb(99 102 241);
            box-shadow: 0 0 0 4px rgba(99,102,241,0.1);
        }
        .search-input::placeholder { color: rgb(148 163 184); }
    </style>
</head>
<body class="font-sans bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 transition-colors duration-300 overflow-x-hidden">

<!-- ========== HEADER ========== -->
<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 20">
    <div :class="scrolled ? 'glass shadow-soft' : 'bg-transparent'" class="transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-20">
                <a href="{{ url('/') }}" class="flex items-center gap-2 group">
                    <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-violet-600 rounded-xl flex items-center justify-center shadow-glow group-hover:scale-110 transition-transform duration-300">📚</div>
                    <span class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Kitob<span class="text-indigo-600">xon</span></span>
                </a>
                <nav class="hidden lg:flex items-center gap-8">
                    <a href="{{ url('/') }}" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">Bosh sahifa</a>
                    <a href="{{ url('books') }}" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">Kitoblar</a>
                    <a href="{{ url('about') }}" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">Haqimizda</a>
                    <a href="{{ url('contact') }}" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">Aloqa</a>
                    <a href="{{ url('faq') }}" class="nav-link text-indigo-600 dark:text-indigo-400 font-semibold transition-colors duration-200">FAQ</a>
                </nav>
                <div class="hidden lg:flex items-center gap-3">
                    <button @click="darkMode = !darkMode" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors duration-200">
                        <span x-show="!darkMode">🌙</span><span x-show="darkMode">☀️</span>
                    </button>
                    <a href="{{ url('login') }}" class="px-4 py-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 transition-all duration-200">Kirish</a>
                    <a href="{{ url('register') }}" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 rounded-xl hover:from-indigo-700 hover:to-violet-700 shadow-glow hover:shadow-none transition-all duration-300 hover:scale-105">Ro'yxatdan o'tish</a>
                </div>
                <button @click="mobileMenu = !mobileMenu" class="lg:hidden w-10 h-10 flex flex-col items-center justify-center gap-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <span :class="mobileMenu ? 'rotate-45 translate-y-2' : ''" class="w-5 h-0.5 bg-slate-700 dark:bg-slate-300 transition-transform duration-300"></span>
                    <span :class="mobileMenu ? 'opacity-0' : ''" class="w-5 h-0.5 bg-slate-700 dark:bg-slate-300 transition-opacity duration-300"></span>
                    <span :class="mobileMenu ? '-rotate-45 -translate-y-2' : ''" class="w-5 h-0.5 bg-slate-700 dark:bg-slate-300 transition-transform duration-300"></span>
                </button>
            </div>
        </div>
        <div x-show="mobileMenu" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="lg:hidden glass border-t border-white/20 dark:border-slate-700/50">
            <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col gap-2">
                <a href="{{ url('/') }}" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">Bosh sahifa</a>
                <a href="{{ url('books') }}" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">Kitoblar</a>
                <a href="{{ url('about') }}" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">Haqimizda</a>
                <a href="{{ url('contact') }}" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">Aloqa</a>
                <a href="{{ url('faq') }}" class="px-4 py-3 rounded-xl bg-indigo-50 dark:bg-indigo-950 font-semibold text-indigo-600 dark:text-indigo-400 transition-colors">FAQ</a>
                <div class="flex gap-3 pt-2 border-t border-slate-200 dark:border-slate-700">
                    <a href="{{ url('login') }}" class="flex-1 py-2.5 text-center font-semibold text-indigo-600 border border-indigo-200 dark:border-indigo-800 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 transition-colors">Kirish</a>
                    <a href="{{ url('register') }}" class="flex-1 py-2.5 text-center font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 rounded-xl transition-all">Ro'yxatdan o'tish</a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- ========== HERO ========== -->
<section class="relative pt-32 pb-16 overflow-hidden">
    <div class="absolute inset-0" style="background: radial-gradient(ellipse 80% 80% at 50% -10%, rgba(79,70,229,0.15) 0%, transparent 70%);"></div>
    <div class="absolute top-32 left-[10%] text-3xl opacity-10 animate-float">❓</div>
    <div class="absolute top-40 right-[12%] text-3xl opacity-10 animate-float" style="animation-delay:1s;">💡</div>
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-50 dark:bg-amber-950 border border-amber-100 dark:border-amber-900 text-amber-700 dark:text-amber-300 text-sm font-semibold mb-6 animate-fade-in">
            ❓ Tez-tez so'raladigan savollar
        </div>
        <h1 class="text-5xl lg:text-6xl font-black text-slate-900 dark:text-white leading-tight mb-6 animate-slide-up">
            <span class="gradient-text">FAQ</span> — Savol<br>va javoblar
        </h1>
        <p class="text-xl text-slate-500 dark:text-slate-400 leading-relaxed max-w-xl mx-auto animate-slide-up" style="animation-delay:0.2s;">
            Eng ko'p so'raladigan savollarga to'liq javoblar. Topmaganingizni <a href="{{ url('contact') }}" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">aloqa sahifasi</a>dan so'rang.
        </p>
    </div>
</section>

<!-- ========== FAQ MAIN CONTENT ========== -->
<section class="pb-24 lg:pb-32" x-data="faqApp()">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Search -->
        <div class="mb-10 observe-animate">
            <div class="relative">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text"
                       x-model="search"
                       placeholder="Savol qidiring... (masalan: to'lov, audio, ro'yxat)"
                       class="search-input">
                <button x-show="search" @click="search=''"
                        class="absolute right-4 top-1/2 -translate-y-1/2 w-6 h-6 bg-slate-200 dark:bg-slate-700 rounded-full flex items-center justify-center hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors text-xs">
                    ✕
                </button>
            </div>
            <!-- Search results info -->
            <p x-show="search" class="text-sm text-slate-500 dark:text-slate-400 mt-2 pl-1">
                <span x-text="filteredFaqs.length"></span> ta natija topildi "<span x-text="search" class="font-semibold text-indigo-600 dark:text-indigo-400"></span>" uchun
            </p>
        </div>

        <!-- Category Tabs -->
        <div class="flex flex-wrap gap-2 mb-8 observe-animate observe-delay-1">
            <button @click="activeCategory = 'all'"
                    :class="activeCategory === 'all' ? 'bg-indigo-600 text-white shadow-glow' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-700'"
                    class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200">
                🌐 Hammasi (<span x-text="faqs.length"></span>)
            </button>
            <template x-for="cat in categories" :key="cat.id">
                <button @click="activeCategory = cat.id"
                        :class="activeCategory === cat.id ? 'bg-indigo-600 text-white shadow-glow' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-700'"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 flex items-center gap-1.5">
                    <span x-text="cat.icon"></span>
                    <span x-text="cat.name"></span>
                    <span class="text-xs opacity-70">(<span x-text="getFaqsByCategory(cat.id).length"></span>)</span>
                </button>
            </template>
        </div>

        <!-- FAQ Items -->
        <div class="space-y-3">
            <!-- No results -->
            <div x-show="filteredFaqs.length === 0"
                 class="py-16 text-center observe-animate">
                <div class="text-6xl mb-4">🔍</div>
                <h3 class="text-xl font-bold text-slate-700 dark:text-slate-300 mb-2">Hech narsa topilmadi</h3>
                <p class="text-slate-500 dark:text-slate-400 mb-6">
                    "<span x-text="search" class="font-semibold"></span>" so'rovi bo'yicha savol topilmadi.
                </p>
                <a href="{{ url('contact') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-all duration-300 hover:scale-105">
                    📧 Savol yuborish
                </a>
            </div>

            <!-- FAQ Accordion Items -->
            <template x-for="(faq, index) in filteredFaqs" :key="faq.id">
                <div class="observe-animate group bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-soft hover:border-indigo-200 dark:hover:border-indigo-700 hover:shadow-glow transition-all duration-300 overflow-hidden">

                    <!-- Question Button -->
                    <button @click="toggle(faq.id)"
                            class="w-full text-left p-6 flex items-start gap-4">

                        <!-- Icon -->
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0 mt-0.5 transition-all duration-300"
                             :class="openItem === faq.id ? 'bg-indigo-600 text-white shadow-glow scale-110' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-950'">
                            <span x-text="faq.icon"></span>
                        </div>

                        <!-- Question Text -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-bold px-2 py-0.5 rounded-lg"
                                      :class="getCategoryStyle(faq.category)"
                                      x-text="getCategoryName(faq.category)"></span>
                            </div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base leading-snug pr-4"
                                x-text="faq.question"
                                :class="openItem === faq.id ? 'text-indigo-700 dark:text-indigo-300' : ''"></h3>
                        </div>

                        <!-- Chevron -->
                        <div class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center flex-shrink-0 transition-all duration-300"
                             :class="openItem === faq.id ? 'bg-indigo-50 dark:bg-indigo-950 rotate-180' : ''">
                            <svg class="w-4 h-4 text-slate-500 dark:text-slate-400"
                                 :class="openItem === faq.id ? 'text-indigo-600 dark:text-indigo-400' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    <!-- Answer -->
                    <div x-show="openItem === faq.id"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2">
                        <div class="px-6 pb-6">
                            <div class="ml-14 pl-4 border-l-2 border-indigo-200 dark:border-indigo-800">
                                <p class="text-slate-600 dark:text-slate-300 leading-relaxed" x-text="faq.answer"></p>

                                <!-- Additional links if any -->
                                <template x-if="faq.link">
                                    <div class="mt-4">
                                        <a :href="faq.link.url"
                                           class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 group/link">
                                            <span x-text="faq.link.text"></span>
                                            <svg class="w-4 h-4 group-hover/link:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Expand/Collapse All -->
        <div class="flex justify-center gap-4 mt-8 observe-animate">
            <button @click="openAll()" class="px-5 py-2.5 text-sm font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950 hover:bg-indigo-100 dark:hover:bg-indigo-900 rounded-xl border border-indigo-100 dark:border-indigo-900 transition-colors duration-200">
                ➕ Hammasini ochish
            </button>
            <button @click="closeAll()" class="px-5 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-700 transition-colors duration-200">
                ➖ Hammasini yopish
            </button>
        </div>
    </div>
</section>

<!-- ========== STILL HAVE QUESTIONS ========== -->
<section class="py-20 bg-gradient-to-br from-indigo-50 to-violet-50 dark:from-indigo-950/30 dark:to-violet-950/30 border-y border-indigo-100 dark:border-indigo-900">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center observe-animate">
        <div class="text-6xl mb-6 animate-float">🤔</div>
        <h2 class="text-3xl font-black text-slate-900 dark:text-white mb-4">Savolingizga javob topolmadingizmi?</h2>
        <p class="text-slate-500 dark:text-slate-400 text-lg leading-relaxed mb-8">
            Tushunmagan narsangiz bo'lsa, bizga bevosita yozing. Jamoa 24 soat ichida javob beradi.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ url('contact') }}"
               class="group w-full sm:w-auto px-8 py-4 font-bold text-white bg-gradient-to-r from-indigo-600 to-violet-600 rounded-2xl shadow-glow hover:shadow-none transition-all duration-300 hover:scale-105 hover:-translate-y-1 flex items-center justify-center gap-2">
                📧 Murojaat yuborish
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <a href="https://t.me/kitobxon_uz" target="_blank" rel="noopener"
               class="group w-full sm:w-auto px-8 py-4 font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-700 shadow-soft transition-all duration-300 hover:scale-105 hover:-translate-y-1 flex items-center justify-center gap-2">
                💬 Telegram orqali
            </a>
        </div>
    </div>
</section>

<!-- ========== FOOTER ========== -->
<footer class="bg-slate-900 dark:bg-slate-950 text-slate-400 border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">
            <div>
                <a href="{{ url('/') }}" class="flex items-center gap-2 mb-4">
                    <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-violet-600 rounded-xl flex items-center justify-center">📚</div>
                    <span class="text-xl font-black text-white">Kitob<span class="text-indigo-400">xon</span></span>
                </a>
                <p class="text-slate-500 text-sm leading-relaxed">O'zbek tilidagi eng yaxshi onlayn kitob o'qish platformasi.</p>
            </div>
            <div>
                <h4 class="font-bold text-white mb-4">Platforma</h4>
                <ul class="space-y-2.5">
                    @foreach([['Kitoblar', url('books')],['Haqimizda', url('about')],['FAQ', url('faq')],['Aloqa', url('contact')]] as $link)
                    <li><a href="{{ $link[1] }}" class="text-slate-500 hover:text-indigo-400 transition-colors text-sm">{{ $link[0] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-white mb-4">Huquqiy</h4>
                <ul class="space-y-2.5">
                    @foreach([['Maxfiylik', url('privacy')],['Shartlar', url('terms')]] as $link)
                    <li><a href="{{ $link[1] }}" class="text-slate-500 hover:text-indigo-400 transition-colors text-sm">{{ $link[0] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-white mb-4">Bog'laning</h4>
                <ul class="space-y-2">
                    <li class="text-sm text-slate-500">📧 info@kitobxon.uz</li>
                    <li class="text-sm text-slate-500">📞 +998 71 000-00-00</li>
                    <li class="text-sm text-slate-500">📍 Toshkent, O'zbekiston</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-slate-600 text-sm">© {{ date('Y') }} Kitobxon. Barcha huquqlar himoyalangan.</p>
            <div class="flex gap-5">
                <a href="{{ url('privacy') }}" class="text-slate-600 hover:text-indigo-400 text-sm transition-colors">Maxfiylik</a>
                <a href="{{ url('terms') }}" class="text-slate-600 hover:text-indigo-400 text-sm transition-colors">Shartlar</a>
                <a href="{{ url('contact') }}" class="text-slate-600 hover:text-indigo-400 text-sm transition-colors">Aloqa</a>
            </div>
        </div>
    </div>
</footer>

<script>
    function faqApp() {
        return {
            search: '',
            openItem: null,
            activeCategory: 'all',

            categories: [
                { id: 'account', name: 'Hisob', icon: '👤' },
                { id: 'reading', name: 'O\'qish', icon: '📖' },
                { id: 'payment', name: 'To\'lov', icon: '💳' },
                { id: 'technical', name: 'Texnik', icon: '⚙️' },
                { id: 'gamification', name: 'Gamifikatsiya', icon: '🏆' },
            ],

            faqs: [
                {
                    id: 1,
                    category: 'account',
                    icon: '📝',
                    question: 'Kitobxonga qanday ro\'yxatdan o\'tish mumkin?',
                    answer: 'Ro\'yxatdan o\'tish juda oson! "Ro\'yxatdan o\'tish" tugmasini bosing, ismingiz, email manzilingiz va parolingizni kiriting. 30 soniyada hisob tayyor. Email tasdiqlash xati yuboriladi — uni tasdiqlang va platformadan to\'liq foydalaning.',
                    link: { text: 'Ro\'yxatdan o\'tish sahifasiga o\'tish', url: '/register' }
                },
                {
                    id: 2,
                    category: 'account',
                    icon: '🔐',
                    question: 'Parolimni unutib qo\'ydim. Nima qilishim kerak?',
                    answer: 'Kirish sahifasida "Parolni unutdim" havolasini bosing. Email manzilingizni kiriting — parolni tiklash uchun havola yuboriladi. 15 daqiqa ichida xatni tekshiring (spam papkasini ham). Muammo davom etsa, info@kitobxon.uz ga yozing.',
                    link: { text: 'Parolni tiklash', url: '/forgot-password' }
                },
                {
                    id: 3,
                    category: 'account',
                    icon: '✏️',
                    question: 'Profilimdagi ma\'lumotlarni qanday o\'zgartirish mumkin?',
                    answer: 'Hisob sozlamalari bo\'limiga o\'ting (yuqori o\'ng burchakdagi avatar → Sozlamalar). U yerda ismingizni, bio'grafiyangizni, profilingiz rasmini va boshqa ma\'lumotlarni tahrirlashingiz mumkin. O\'zgarishlar avtomatik saqlanadi.',
                    link: null
                },
                {
                    id: 4,
                    category: 'reading',
                    icon: '📖',
                    question: 'Qaysi formatlarda kitob o\'qish mumkin?',
                    answer: 'Kitobxon uch formatni qo\'llab-quvvatlaydi: (1) Matn — qulay o\'qish rejimi bilan, shrift o\'lchamini va rangini sozlash imkoniyati bor. (2) Audio — professional diktorlar tomonidan o\'qilgan, tezlikni boshqarish mumkin. (3) Video — kitob bo\'yicha tayyorlangan darslar va muhokamalar. Barcha formatlar bir hisob ostida mavjud.',
                    link: null
                },
                {
                    id: 5,
                    category: 'reading',
                    icon: '📱',
                    question: 'Kitoblarni oflayn o\'qish mumkinmi?',
                    answer: 'Ha, Premium a\'zo sifatida kitoblarni oflayn o\'qish uchun yuklab olishingiz mumkin. Bepul hisobda oflayn rejim mavjud emas. Yüklangan kitoblar qurilmangizda 30 kun saqlanadi, keyin esa qayta yuklab olishingiz kerak bo\'ladi.',
                    link: null
                },
                {
                    id: 6,
                    category: 'reading',
                    icon: '🔖',
                    question: 'O\'qishni davom ettirish uchun sahifani qanday belgilash mumkin?',
                    answer: 'O\'qish jarayonida yuqori o\'ng burchakdagi kitobcha belgisini bosing — sahifa avtomatik belgilanadi. Keyingi kirganda "Davom ettirish" tugmasi shu yerdan o\'qishni boshlaydi. Belgilangan sahifalar, izohlar va ta\'kidlangan jumlalar barchasi "Mening kutubxonam" bo\'limida saqlanadi.',
                    link: null
                },
                {
                    id: 7,
                    category: 'payment',
                    icon: '💳',
                    question: 'Kitobxon bepulmi yoki to\'lovlimi?',
                    answer: 'Kitobxonga ro\'yxatdan o\'tish va asosiy funksiyalardan foydalanish BEPUL! Premium a\'zolik orqali: barcha kitoblar, oflayn rejim, reklamasiz tajriba va maxsus tanlovlar mavjud. Premium narxi — oyiga 29,900 so\'m. Birinchi 30 kun bepul sinab ko\'ring!',
                    link: { text: 'Premium haqida ko\'proq', url: '/pricing' }
                },
                {
                    id: 8,
                    category: 'payment',
                    icon: '💰',
                    question: 'Qanday to\'lov usullari qabul qilinadi?',
                    answer: 'Quyidagi to\'lov usullarini qabul qilamiz: Uzcard, Humo (o\'zbek banklari kartalari), Click, Payme elektron hamyonlari, va xorijiy Visa/Mastercard kartalari. Barcha to\'lovlar SSL orqali himoyalangan.',
                    link: null
                },
                {
                    id: 9,
                    category: 'payment',
                    icon: '❌',
                    question: 'A\'zolikni bekor qilsam nima bo\'ladi?',
                    answer: 'A\'zolikni istalgan vaqt bekor qilishingiz mumkin. Bekor qilganingizdan so\'ng, joriy to\'lov davri tugagunga qadar Premium xizmatdan foydalanishda davom etasiz. Ma\'lumotlaringiz va o\'qish tarixingiz saqlanib qoladi. Qaytib kelganingizda xuddi shu joydan davom etishingiz mumkin.',
                    link: null
                },
                {
                    id: 10,
                    category: 'gamification',
                    icon: '🔥',
                    question: 'Streak nima va u qanday ishlaydi?',
                    answer: 'Streak — bu ketma-ket o\'qigan kunlaringiz soni. Har kuni kamida 10 daqiqa kitob o\'qisangiz, streak yonadi! 7-kunlik streakda maxsus nishon, 30-kunlikda katta mukofot beriladi. Agar bir kuni o\'qishni o\'tkazib yuborsangiz, streak noldan boshlanadi. "Freeze" tokenlarini to\'plab, streakingizni himoya qilishingiz mumkin.',
                    link: null
                },
                {
                    id: 11,
                    category: 'gamification',
                    icon: '⚡',
                    question: 'XP ball va leaderboard qanday ishlaydi?',
                    answer: 'XP (tajriba ballari) quyidagi harakatlar uchun beriladi: Sahifa o\'qish (+1 XP), bob yakunlash (+20 XP), test topshirish (+10-50 XP), muhokamada qatnashish (+5 XP), kitob yakunlash (+100 XP). Haftalik leaderboard dushanba kuni yangilanadi. Top-3 o\'quvchilar maxsus mukofot oladi.',
                    link: null
                },
                {
                    id: 12,
                    category: 'gamification',
                    icon: '🏆',
                    question: 'Qanday nishonlar (badges) mavjud?',
                    answer: 'Platformada 100+ xil nishon mavjud: kitob yakunlash, streak seriyalari (7/30/100 kun), janr bo\'yicha mutaxassis, jamoa a\'zosi, tezkor o\'quvchi va boshqalar. Har bir nishon XP ball ham olib keladi. Eng kamdan-kam nishonlar — "Kitob qurti" (1000+ sahifa), "Yillik o\'quvchi" (52 kitob).',
                    link: null
                },
                {
                    id: 13,
                    category: 'technical',
                    icon: '⚙️',
                    question: 'Qaysi qurilma va brauzerlarda ishlaydi?',
                    answer: 'Kitobxon barcha zamonaviy brauzerlar (Chrome, Firefox, Safari, Edge) va qurilmalarda (kompyuter, planshet, smartfon) ishlaydi. Maxsus mobil ilova iOS va Android uchun tez orada chiqadi. Minimal tavsiya: Chrome 90+, Firefox 88+, Safari 14+.',
                    link: null
                },
                {
                    id: 14,
                    category: 'technical',
                    icon: '🌐',
                    question: 'Platforma qaysi tilde ishlaydi?',
                    answer: 'Kitobxon interfeysi O\'zbek tilida (lotin alifbosi). Kontent esa o\'zbek tilidagi asl asarlar va professional tarjimalar. Ayrim kitoblar rus tilida ham mavjud. Ingliz tilidagi versiya ishlab chiqilmoqda.',
                    link: null
                },
                {
                    id: 15,
                    category: 'technical',
                    icon: '🔒',
                    question: 'Ma\'lumotlarim xavfsizmi?',
                    answer: 'Ha, to\'liq xavfsiz! Barcha ma\'lumotlar SSL/TLS orqali shifrlangan. Parollar bcrypt algoritmi bilan himoyalangan. Biz hech qachon kredit karta ma\'lumotlaringizni saqlamaymiz — to\'lovlar Click va Payme orqali amalga oshiriladi. Maxfiylik siyosatimizni o\'qib chiqing.',
                    link: { text: 'Maxfiylik siyosatini o\'qish', url: '/privacy' }
                },
            ],

            get filteredFaqs() {
                return this.faqs.filter(faq => {
                    const matchesSearch = !this.search ||
                        faq.question.toLowerCase().includes(this.search.toLowerCase()) ||
                        faq.answer.toLowerCase().includes(this.search.toLowerCase());
                    const matchesCategory = this.activeCategory === 'all' || faq.category === this.activeCategory;
                    return matchesSearch && matchesCategory;
                });
            },

            toggle(id) {
                this.openItem = this.openItem === id ? null : id;
            },

            openAll() {
                // Open first filtered item if only one
                if (this.filteredFaqs.length > 0) {
                    this.openItem = this.filteredFaqs[0].id;
                }
            },

            closeAll() {
                this.openItem = null;
            },

            getFaqsByCategory(catId) {
                return this.faqs.filter(f => f.category === catId);
            },

            getCategoryName(catId) {
                const cat = this.categories.find(c => c.id === catId);
                return cat ? cat.icon + ' ' + cat.name : '';
            },

            getCategoryStyle(catId) {
                const styles = {
                    account: 'bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300',
                    reading: 'bg-green-50 dark:bg-green-950/50 text-green-700 dark:text-green-300',
                    payment: 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300',
                    technical: 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300',
                    gamification: 'bg-violet-50 dark:bg-violet-950/50 text-violet-700 dark:text-violet-300',
                };
                return styles[catId] || 'bg-slate-100 text-slate-600';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('visible'); });
        }, { threshold: 0.05, rootMargin: '0px 0px -30px 0px' });
        document.querySelectorAll('.observe-animate').forEach(el => observer.observe(el));
    });
</script>

</body>
</html>
