<!DOCTYPE html>
<html lang="uz" x-data="{ darkMode: false, mobileMenu: false, viewMode: 'grid' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kitobxon — Barcha kitoblar katalogi. Matn, audio va video formatlarda o'qing.">
    <title>Kitoblar Katalogi — Kitobxon 📚</title>

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
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4,0,0.6,1) infinite',
                    },
                    keyframes: {
                        fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                        slideUp: { '0%': { opacity: '0', transform: 'translateY(40px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                        float: { '0%,100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-12px)' } },
                    },
                    boxShadow: {
                        'soft': '0 4px 24px -4px rgba(79,70,229,0.12)',
                        'glow': '0 0 40px rgba(79,70,229,0.25)',
                        'book': '0 20px 60px -15px rgba(0,0,0,0.3)',
                    },
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
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
        .observe-animate { opacity: 0; transform: translateY(20px); transition: all 0.5s ease-out; }
        .observe-animate.visible { opacity: 1; transform: translateY(0); }

        /* Book card styles */
        .book-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .book-card:hover { transform: translateY(-8px); box-shadow: 0 20px 60px -10px rgba(79,70,229,0.25); }

        .book-cover {
            position: relative;
            perspective: 1000px;
        }
        .book-cover-img {
            transition: transform 0.4s ease;
            transform-origin: left center;
        }
        .book-card:hover .book-cover-img {
            transform: rotateY(-8deg) scale(1.02);
        }

        .book-cover::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 50%, rgba(0,0,0,0.1) 100%);
            border-radius: inherit;
            pointer-events: none;
        }

        .genre-badge {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Format badges */
        .format-text { background: rgba(99,102,241,0.1); color: #4f46e5; }
        .dark .format-text { background: rgba(99,102,241,0.2); color: #818cf8; }
        .format-audio { background: rgba(16,185,129,0.1); color: #059669; }
        .dark .format-audio { background: rgba(16,185,129,0.2); color: #34d399; }
        .format-video { background: rgba(245,158,11,0.1); color: #d97706; }
        .dark .format-video { background: rgba(245,158,11,0.2); color: #fbbf24; }

        /* Skeleton loader */
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 1000px 100%;
            animation: shimmer 2s infinite;
        }
        .dark .skeleton {
            background: linear-gradient(90deg, #1e293b 25%, #273548 50%, #1e293b 75%);
            background-size: 1000px 100%;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 3rem;
            background: white;
            border: 2px solid rgb(226 232 240);
            border-radius: 0.875rem;
            font-size: 0.9375rem;
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
                <a href="/" class="flex items-center gap-2 group">
                    <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-violet-600 rounded-xl flex items-center justify-center shadow-glow group-hover:scale-110 transition-transform duration-300">📚</div>
                    <span class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Kitob<span class="text-indigo-600">xon</span></span>
                </a>
                <nav class="hidden lg:flex items-center gap-8">
                    <a href="/" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">Bosh sahifa</a>
                    <a href="/books" class="nav-link text-indigo-600 dark:text-indigo-400 font-semibold transition-colors duration-200">Kitoblar</a>
                    <a href="/about" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">Haqimizda</a>
                    <a href="/contact" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">Aloqa</a>
                    <a href="/faq" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">FAQ</a>
                </nav>
                <div class="hidden lg:flex items-center gap-3">
                    <button @click="darkMode = !darkMode" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors duration-200">
                        <span x-show="!darkMode">🌙</span><span x-show="darkMode">☀️</span>
                    </button>
                    <a href="/login" class="px-4 py-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 transition-all duration-200">Kirish</a>
                    <a href="/register" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 rounded-xl hover:from-indigo-700 hover:to-violet-700 shadow-glow hover:shadow-none transition-all duration-300 hover:scale-105">Ro'yxatdan o'tish</a>
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
                <a href="/" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">Bosh sahifa</a>
                <a href="/books" class="px-4 py-3 rounded-xl bg-indigo-50 dark:bg-indigo-950 font-semibold text-indigo-600 dark:text-indigo-400 transition-colors">Kitoblar</a>
                <a href="/about" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">Haqimizda</a>
                <a href="/contact" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">Aloqa</a>
                <a href="/faq" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">FAQ</a>
                <div class="flex gap-3 pt-2 border-t border-slate-200 dark:border-slate-700">
                    <a href="/login" class="flex-1 py-2.5 text-center font-semibold text-indigo-600 border border-indigo-200 dark:border-indigo-800 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 transition-colors">Kirish</a>
                    <a href="/register" class="flex-1 py-2.5 text-center font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 rounded-xl transition-all">Ro'yxatdan o'tish</a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- ========== HERO / PAGE HEADER ========== -->
<section class="relative pt-28 pb-10 overflow-hidden">
    <div class="absolute inset-0" style="background: radial-gradient(ellipse 80% 80% at 50% -10%, rgba(79,70,229,0.12) 0%, transparent 70%);"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-50 dark:bg-indigo-950 border border-indigo-100 dark:border-indigo-900 text-indigo-700 dark:text-indigo-300 text-sm font-semibold mb-4 animate-fade-in">
                    📚 Kitoblar katalogi
                </div>
                <h1 class="text-4xl lg:text-5xl font-black text-slate-900 dark:text-white leading-tight animate-slide-up">
                    Barcha <span class="gradient-text">Kitoblar</span>
                </h1>
                <p class="text-slate-500 dark:text-slate-400 mt-2 text-lg animate-slide-up" style="animation-delay:0.2s;">
                    @if(isset($books))
                        {{ $books->total() }} ta kitob mavjud — o'zingizga yarashganini toping
                    @else
                        50+ kitob — o'zingizga yarashganini toping
                    @endif
                </p>
            </div>

            <!-- Register CTA Banner (for non-auth users) -->
            <div class="animate-slide-up flex-shrink-0" style="animation-delay:0.3s;">
                <div class="flex items-center gap-3 p-4 bg-gradient-to-r from-indigo-50 to-violet-50 dark:from-indigo-950/40 dark:to-violet-950/40 rounded-2xl border border-indigo-100 dark:border-indigo-900">
                    <div class="text-3xl">🔓</div>
                    <div class="pr-2">
                        <p class="font-bold text-slate-800 dark:text-white text-sm">To'liq kirish uchun</p>
                        <p class="text-slate-500 dark:text-slate-400 text-xs">Bepul ro'yxatdan o'ting</p>
                    </div>
                    <a href="/register" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-glow hover:shadow-none transition-all duration-300 hover:scale-105 whitespace-nowrap">
                        Boshlash →
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== FILTERS & SEARCH ========== -->
<section class="py-6 bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 sticky top-16 lg:top-20 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row gap-4 items-start md:items-center">

            <!-- Search -->
            <div class="relative flex-1 max-w-lg">
                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <form method="GET" action="/books">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Kitob nomi yoki muallif qidirish..."
                           class="search-input">
                </form>
            </div>

            <!-- Genre Filter -->
            <form method="GET" action="/books" class="flex flex-wrap gap-2 items-center">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif

                @php
                    $genres = [
                        ['slug'=>'', 'name'=>'Hammasi', 'icon'=>'🌐'],
                        ['slug'=>'biznes', 'name'=>'Biznes', 'icon'=>'💼'],
                        ['slug'=>'shaxsiy-osish', 'name'=>'Shaxsiy o\'sish', 'icon'=>'🌱'],
                        ['slug'=>'ilm-fan', 'name'=>'Ilm-fan', 'icon'=>'🔬'],
                        ['slug'=>'adabiyot', 'name'=>'Adabiyot', 'icon'=>'📜'],
                        ['slug'=>'tarix', 'name'=>'Tarix', 'icon'=>'🏛️'],
                        ['slug'=>'psixologiya', 'name'=>'Psixologiya', 'icon'=>'🧠'],
                        ['slug'=>'texnologiya', 'name'=>'Texnologiya', 'icon'=>'💻'],
                    ];
                    $currentGenre = request('genre', '');
                @endphp

                @foreach($genres as $genre)
                <button type="submit"
                        name="genre"
                        value="{{ $genre['slug'] }}"
                        class="px-3 py-1.5 text-xs font-bold rounded-xl transition-all duration-200 flex items-center gap-1
                               {{ $currentGenre === $genre['slug']
                                  ? 'bg-indigo-600 text-white shadow-glow'
                                  : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-indigo-950 hover:text-indigo-600 dark:hover:text-indigo-400 border border-slate-200 dark:border-slate-700' }}">
                    {{ $genre['icon'] }} {{ $genre['name'] }}
                </button>
                @endforeach
            </form>

            <!-- Sort + View Toggle -->
            <div class="flex items-center gap-2 ml-auto">
                <!-- Sort -->
                <form method="GET" action="/books">
                    @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                    @if(request('genre'))<input type="hidden" name="genre" value="{{ request('genre') }}">@endif
                    <select name="sort" onchange="this.form.submit()"
                            class="px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 cursor-pointer focus:outline-none focus:border-indigo-500 transition-colors">
                        <option value="newest" {{ request('sort','newest') === 'newest' ? 'selected' : '' }}>🆕 Yangi</option>
                        <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>🔥 Mashhur</option>
                        <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>⭐ Reytingli</option>
                        <option value="title_asc" {{ request('sort') === 'title_asc' ? 'selected' : '' }}>🔤 A-Z</option>
                    </select>
                </form>

                <!-- View Mode Toggle -->
                <div class="flex bg-slate-100 dark:bg-slate-800 rounded-xl p-1 gap-1">
                    <button @click="viewMode = 'grid'"
                            :class="viewMode === 'grid' ? 'bg-white dark:bg-slate-700 shadow-soft text-indigo-600 dark:text-indigo-400' : 'text-slate-500 dark:text-slate-400'"
                            class="p-2 rounded-lg transition-all duration-200">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </button>
                    <button @click="viewMode = 'list'"
                            :class="viewMode === 'list' ? 'bg-white dark:bg-slate-700 shadow-soft text-indigo-600 dark:text-indigo-400' : 'text-slate-500 dark:text-slate-400'"
                            class="p-2 rounded-lg transition-all duration-200">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Active Filters Strip -->
        @if(request('search') || request('genre'))
        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Filtrlar:</span>
            @if(request('search'))
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 text-xs font-semibold rounded-lg border border-indigo-100 dark:border-indigo-900">
                🔍 "{{ request('search') }}"
                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="hover:text-red-500 transition-colors">✕</a>
            </span>
            @endif
            @if(request('genre'))
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-violet-50 dark:bg-violet-950 text-violet-700 dark:text-violet-300 text-xs font-semibold rounded-lg border border-violet-100 dark:border-violet-900">
                🏷️ {{ request('genre') }}
                <a href="{{ request()->fullUrlWithQuery(['genre' => null]) }}" class="hover:text-red-500 transition-colors">✕</a>
            </span>
            @endif
            <a href="/books" class="text-xs text-slate-400 hover:text-red-500 dark:hover:text-red-400 transition-colors ml-2">Tozalash</a>
        </div>
        @endif
    </div>
</section>

<!-- ========== BOOKS GRID ========== -->
<main class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @php
            // Fallback placeholder data when $books is not passed (for design preview)
            $bookList = isset($books) ? $books : collect([
                (object)['id'=>1,'title'=>'Atom Odatlar','author'=>'James Clear','genre'=>'Shaxsiy o\'sish','cover_url'=>null,'formats'=>['text','audio'],'pages'=>320,'rating'=>4.9,'reads'=>1240,'is_new'=>true,'is_premium'=>false],
                (object)['id'=>2,'title'=>'Sapiens: Insoniyat Tarixi','author'=>'Yuval Noah Harari','genre'=>'Tarix','cover_url'=>null,'formats'=>['text','audio','video'],'pages'=>512,'rating'=>4.8,'reads'=>980,'is_new'=>false,'is_premium'=>true],
                (object)['id'=>3,'title'=>'Pul Psixologiyasi','author'=>'Morgan Housel','genre'=>'Biznes','cover_url'=>null,'formats'=>['text','audio'],'pages'=>256,'rating'=>4.7,'reads'=>856,'is_new'=>true,'is_premium'=>false],
                (object)['id'=>4,'title'=>'1984','author'=>'George Orwell','genre'=>'Adabiyot','cover_url'=>null,'formats'=>['text'],'pages'=>384,'rating'=>4.8,'reads'=>2100,'is_new'=>false,'is_premium'=>false],
                (object)['id'=>5,'title'=>'Kichik Shahzoda','author'=>'Antoine de Saint-Exupéry','genre'=>'Adabiyot','cover_url'=>null,'formats'=>['text','audio'],'pages'=>96,'rating'=>4.9,'reads'=>3200,'is_new'=>false,'is_premium'=>false],
                (object)['id'=>6,'title'=>'Fikrlash Tez va Sekin','author'=>'Daniel Kahneman','genre'=>'Psixologiya','cover_url'=>null,'formats'=>['text','audio','video'],'pages'=>499,'rating'=>4.7,'reads'=>640,'is_new'=>false,'is_premium'=>true],
                (object)['id'=>7,'title'=>'Zero to One','author'=>'Peter Thiel','genre'=>'Biznes','cover_url'=>null,'formats'=>['text','audio'],'pages'=>224,'rating'=>4.6,'reads'=>540,'is_new'=>true,'is_premium'=>true],
                (object)['id'=>8,'title'=>'Uyg\'onish Marosimi','author'=>'Robin Sharma','genre'=>'Shaxsiy o\'sish','cover_url'=>null,'formats'=>['text','audio'],'pages'=>368,'rating'=>4.5,'reads'=>720,'is_new'=>false,'is_premium'=>false],
                (object)['id'=>9,'title'=>'Qora Oqqush','author'=>'Nassim Nicholas Taleb','genre'=>'Biznes','cover_url'=>null,'formats'=>['text'],'pages'=>480,'rating'=>4.6,'reads'=>430,'is_new'=>false,'is_premium'=>true],
                (object)['id'=>10,'title'=>'Ichki Muhandis','author'=>'Sadhguru','genre'=>'Shaxsiy o\'sish','cover_url'=>null,'formats'=>['text','audio','video'],'pages'=>352,'rating'=>4.8,'reads'=>890,'is_new'=>true,'is_premium'=>false],
                (object)['id'=>11,'title'=>'Dune','author'=>'Frank Herbert','genre'=>'Ilm-fan','cover_url'=>null,'formats'=>['text','audio'],'pages'=>896,'rating'=>4.7,'reads'=>560,'is_new'=>false,'is_premium'=>true],
                (object)['id'=>12,'title'=>'Puxta Fikrlovchi','author'=>'Rolf Dobelli','genre'=>'Psixologiya','cover_url'=>null,'formats'=>['text'],'pages'=>384,'rating'=>4.4,'reads'=>320,'is_new'=>false,'is_premium'=>false],
            ]);

            $genreColors = [
                'Shaxsiy o\'sish' => ['bg'=>'bg-green-100 dark:bg-green-900/30','text'=>'text-green-700 dark:text-green-300'],
                'Biznes' => ['bg'=>'bg-blue-100 dark:bg-blue-900/30','text'=>'text-blue-700 dark:text-blue-300'],
                'Tarix' => ['bg'=>'bg-amber-100 dark:bg-amber-900/30','text'=>'text-amber-700 dark:text-amber-300'],
                'Adabiyot' => ['bg'=>'bg-rose-100 dark:bg-rose-900/30','text'=>'text-rose-700 dark:text-rose-300'],
                'Psixologiya' => ['bg'=>'bg-violet-100 dark:bg-violet-900/30','text'=>'text-violet-700 dark:text-violet-300'],
                'Ilm-fan' => ['bg'=>'bg-cyan-100 dark:bg-cyan-900/30','text'=>'text-cyan-700 dark:text-cyan-300'],
                'Texnologiya' => ['bg'=>'bg-slate-100 dark:bg-slate-700','text'=>'text-slate-700 dark:text-slate-300'],
            ];

            $coverGradients = [
                'from-indigo-500 to-violet-600',
                'from-pink-500 to-rose-600',
                'from-emerald-500 to-teal-600',
                'from-amber-400 to-orange-500',
                'from-cyan-500 to-blue-600',
                'from-violet-500 to-purple-600',
                'from-red-500 to-pink-600',
                'from-slate-600 to-slate-800',
                'from-yellow-400 to-amber-600',
                'from-teal-500 to-emerald-600',
                'from-blue-500 to-indigo-600',
                'from-orange-400 to-red-500',
            ];

            $coverEmojis = ['📖','🔬','💼','🌍','⚗️','🧠','💡','🏛️','🚀','🌱','🌟','📜'];
        @endphp

        <!-- No results message -->
        @if(isset($books) && $books->isEmpty())
        <div class="py-20 text-center">
            <div class="text-7xl mb-6">📭</div>
            <h2 class="text-2xl font-black text-slate-700 dark:text-slate-300 mb-3">Kitob topilmadi</h2>
            <p class="text-slate-500 dark:text-slate-400 mb-8 max-w-md mx-auto">
                @if(request('search'))
                    "{{ request('search') }}" bo'yicha kitob topilmadi. Boshqa kalit so'z sinab ko'ring.
                @else
                    Bu filtr bo'yicha hozircha kitob mavjud emas.
                @endif
            </p>
            <a href="/books" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-all duration-300 hover:scale-105">
                🔄 Barcha kitoblarni ko'rish
            </a>
        </div>

        @else

        <!-- GRID VIEW -->
        <div x-show="viewMode === 'grid'"
             class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($bookList as $index => $book)
            @php
                $gradient = $coverGradients[$index % count($coverGradients)];
                $emoji = $coverEmojis[$index % count($coverEmojis)];
                $genreStyle = $genreColors[$book->genre] ?? ['bg'=>'bg-slate-100 dark:bg-slate-700','text'=>'text-slate-600 dark:text-slate-300'];
            @endphp
            <div class="book-card observe-animate group relative bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-soft overflow-hidden flex flex-col">

                <!-- New / Premium badges -->
                <div class="absolute top-3 left-3 z-10 flex flex-col gap-1.5">
                    @if($book->is_new ?? false)
                    <span class="px-2.5 py-1 bg-green-500 text-white text-xs font-black rounded-lg shadow-md">✨ YANGI</span>
                    @endif
                    @if($book->is_premium ?? false)
                    <span class="px-2.5 py-1 bg-amber-500 text-white text-xs font-black rounded-lg shadow-md">👑 PREMIUM</span>
                    @endif
                </div>

                <!-- Book Cover -->
                <div class="book-cover relative h-52 overflow-hidden">
                    @if(!empty($book->cover_url))
                    <img src="{{ $book->cover_url }}"
                         alt="{{ $book->title }}"
                         class="book-cover-img w-full h-full object-cover">
                    @else
                    <!-- Placeholder Cover -->
                    <div class="book-cover-img w-full h-full bg-gradient-to-br {{ $gradient }} flex flex-col items-center justify-center relative overflow-hidden">
                        <!-- Book spine effect -->
                        <div class="absolute left-0 top-0 bottom-0 w-3 bg-black/20"></div>
                        <!-- Background pattern -->
                        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 20px 20px;"></div>
                        <!-- Content -->
                        <div class="relative text-center px-4">
                            <div class="text-5xl mb-3">{{ $emoji }}</div>
                            <p class="text-white font-serif font-bold text-sm leading-tight line-clamp-2">{{ $book->title }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Hover Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center pb-4">
                        <a href="/register"
                           class="px-5 py-2.5 bg-white text-slate-900 font-bold text-sm rounded-xl hover:bg-indigo-50 transition-all duration-200 transform translate-y-4 group-hover:translate-y-0 shadow-lg">
                            📖 O'qishni boshlash
                        </a>
                    </div>
                </div>

                <!-- Book Info -->
                <div class="flex-1 p-5 flex flex-col">
                    <!-- Genre + Formats -->
                    <div class="flex items-center justify-between mb-3">
                        <span class="genre-badge {{ $genreStyle['bg'] }} {{ $genreStyle['text'] }}">
                            {{ $book->genre }}
                        </span>
                        <div class="flex gap-1">
                            @php $formats = $book->formats ?? ['text']; @endphp
                            @if(in_array('text', $formats))
                            <span class="format-text text-xs px-1.5 py-0.5 rounded-md font-semibold">📖</span>
                            @endif
                            @if(in_array('audio', $formats))
                            <span class="format-audio text-xs px-1.5 py-0.5 rounded-md font-semibold">🎵</span>
                            @endif
                            @if(in_array('video', $formats))
                            <span class="format-video text-xs px-1.5 py-0.5 rounded-md font-semibold">🎥</span>
                            @endif
                        </div>
                    </div>

                    <!-- Title -->
                    <h3 class="font-black text-slate-900 dark:text-white text-base leading-snug mb-1 line-clamp-2 group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition-colors duration-200">
                        {{ $book->title }}
                    </h3>

                    <!-- Author -->
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-1">
                        <span class="text-xs">✍️</span>
                        {{ $book->author }}
                    </p>

                    <!-- Stats Row -->
                    <div class="flex items-center gap-3 text-xs text-slate-400 dark:text-slate-500 mb-4 mt-auto">
                        <!-- Rating -->
                        <span class="flex items-center gap-1 text-amber-500 font-semibold">
                            ⭐ {{ number_format($book->rating ?? 4.5, 1) }}
                        </span>
                        <span class="w-1 h-1 bg-slate-300 dark:bg-slate-600 rounded-full"></span>
                        <!-- Pages -->
                        <span>{{ $book->pages ?? '—' }} bet</span>
                        <span class="w-1 h-1 bg-slate-300 dark:bg-slate-600 rounded-full"></span>
                        <!-- Reads -->
                        <span>{{ number_format($book->reads ?? 0) }} o'qigan</span>
                    </div>

                    <!-- CTA Button -->
                    <a href="/register"
                       class="group/btn flex items-center justify-center gap-2 w-full py-3 px-4 text-sm font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-600 dark:hover:bg-indigo-600 hover:text-white border border-indigo-100 dark:border-indigo-900 hover:border-indigo-600 dark:hover:border-indigo-600 rounded-xl transition-all duration-300">
                        <span>Batafsil</span>
                        <svg class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <!-- LIST VIEW -->
        <div x-show="viewMode === 'list'" x-cloak class="space-y-4">
            @foreach($bookList as $index => $book)
            @php
                $gradient = $coverGradients[$index % count($coverGradients)];
                $emoji = $coverEmojis[$index % count($coverEmojis)];
                $genreStyle = $genreColors[$book->genre] ?? ['bg'=>'bg-slate-100 dark:bg-slate-700','text'=>'text-slate-600 dark:text-slate-300'];
            @endphp
            <div class="observe-animate group flex items-center gap-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-soft p-4 hover:border-indigo-200 dark:hover:border-indigo-700 hover:shadow-glow transition-all duration-300">

                <!-- Cover Thumbnail -->
                <div class="w-20 h-28 rounded-xl overflow-hidden flex-shrink-0 relative">
                    @if(!empty($book->cover_url))
                    <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full bg-gradient-to-br {{ $gradient }} flex items-center justify-center text-3xl relative">
                        <div class="absolute left-0 top-0 bottom-0 w-2 bg-black/20"></div>
                        {{ $emoji }}
                    </div>
                    @endif
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <span class="genre-badge {{ $genreStyle['bg'] }} {{ $genreStyle['text'] }}">{{ $book->genre }}</span>
                        @if($book->is_new ?? false)<span class="genre-badge bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">✨ YANGI</span>@endif
                        @if($book->is_premium ?? false)<span class="genre-badge bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">👑 PREMIUM</span>@endif
                    </div>
                    <h3 class="font-black text-slate-900 dark:text-white text-lg leading-tight mb-1 group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition-colors">{{ $book->title }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">✍️ {{ $book->author }}</p>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400 dark:text-slate-500">
                        <span class="text-amber-500 font-semibold">⭐ {{ number_format($book->rating ?? 4.5, 1) }}</span>
                        <span>{{ $book->pages ?? '—' }} bet</span>
                        <span>{{ number_format($book->reads ?? 0) }} o'qigan</span>
                        <!-- Format tags -->
                        @php $formats = $book->formats ?? ['text']; @endphp
                        @if(in_array('text', $formats))<span class="format-text px-1.5 py-0.5 rounded-md font-semibold">📖 Matn</span>@endif
                        @if(in_array('audio', $formats))<span class="format-audio px-1.5 py-0.5 rounded-md font-semibold">🎵 Audio</span>@endif
                        @if(in_array('video', $formats))<span class="format-video px-1.5 py-0.5 rounded-md font-semibold">🎥 Video</span>@endif
                    </div>
                </div>

                <!-- CTA -->
                <div class="flex-shrink-0">
                    <a href="/register"
                       class="flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-600 dark:hover:bg-indigo-600 hover:text-white border border-indigo-100 dark:border-indigo-900 hover:border-indigo-600 rounded-xl transition-all duration-300 whitespace-nowrap">
                        Batafsil
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- ========== PAGINATION ========== -->
        @if(isset($books) && $books->hasPages())
        <div class="mt-12 flex items-center justify-center">
            <div class="flex items-center gap-2 flex-wrap justify-center">

                {{-- Previous Page --}}
                @if($books->onFirstPage())
                <span class="px-4 py-2 text-sm font-semibold text-slate-400 dark:text-slate-600 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl cursor-not-allowed">
                    ← Oldingi
                </span>
                @else
                <a href="{{ $books->previousPageUrl() }}"
                   class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 hover:border-indigo-300 dark:hover:border-indigo-700 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all duration-200">
                    ← Oldingi
                </a>
                @endif

                {{-- Page Numbers --}}
                @php
                    $currentPage = $books->currentPage();
                    $lastPage = $books->lastPage();
                    $start = max(1, $currentPage - 2);
                    $end = min($lastPage, $currentPage + 2);
                @endphp

                @if($start > 1)
                <a href="{{ $books->url(1) }}" class="w-10 h-10 flex items-center justify-center text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 hover:border-indigo-300 hover:text-indigo-600 transition-all duration-200">1</a>
                @if($start > 2)<span class="text-slate-400 dark:text-slate-600 text-sm px-1">...</span>@endif
                @endif

                @for($page = $start; $page <= $end; $page++)
                @if($page == $currentPage)
                <span class="w-10 h-10 flex items-center justify-center text-sm font-bold text-white bg-gradient-to-br from-indigo-600 to-violet-600 rounded-xl shadow-glow">{{ $page }}</span>
                @else
                <a href="{{ $books->url($page) }}" class="w-10 h-10 flex items-center justify-center text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 hover:border-indigo-300 hover:text-indigo-600 transition-all duration-200">{{ $page }}</a>
                @endif
                @endfor

                @if($end < $lastPage)
                @if($end < $lastPage - 1)<span class="text-slate-400 dark:text-slate-600 text-sm px-1">...</span>@endif
                <a href="{{ $books->url($lastPage) }}" class="w-10 h-10 flex items-center justify-center text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 hover:border-indigo-300 hover:text-indigo-600 transition-all duration-200">{{ $lastPage }}</a>
                @endif

                {{-- Next Page --}}
                @if($books->hasMorePages())
                <a href="{{ $books->nextPageUrl() }}"
                   class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 hover:border-indigo-300 dark:hover:border-indigo-700 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all duration-200">
                    Keyingi →
                </a>
                @else
                <span class="px-4 py-2 text-sm font-semibold text-slate-400 dark:text-slate-600 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl cursor-not-allowed">
                    Keyingi →
                </span>
                @endif
            </div>
        </div>

        <!-- Pagination Info -->
        <div class="text-center mt-4">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ $books->firstItem() }}–{{ $books->lastItem() }} ko'rsatilmoqda, jami {{ $books->total() }} ta kitob
            </p>
        </div>
        @endif

    </div>
</main>

<!-- ========== CTA BANNER ========== -->
<section class="py-16 bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 40px 40px;"></div>
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="text-5xl mb-5 animate-float">🚀</div>
        <h2 class="text-3xl lg:text-4xl font-black text-white mb-4">
            Barcha kitoblarni to'liq o'qish uchun ro'yxatdan o'ting
        </h2>
        <p class="text-indigo-200 text-lg mb-8 max-w-xl mx-auto">
            Bepul hisob yarating va 50+ kitobga darhol kirish qiling. Karta talab etilmaydi.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/register"
               class="group w-full sm:w-auto px-8 py-4 font-black text-indigo-700 bg-white rounded-2xl hover:bg-indigo-50 shadow-2xl transition-all duration-300 hover:scale-105 hover:-translate-y-1 flex items-center justify-center gap-2 text-lg">
                🎉 Bepul Ro'yxatdan O'tish
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <a href="/login"
               class="w-full sm:w-auto px-8 py-4 font-bold text-white bg-white/10 backdrop-blur rounded-2xl border border-white/20 hover:bg-white/20 transition-all duration-300 hover:scale-105 hover:-translate-y-1 flex items-center justify-center gap-2 text-lg">
                🔐 Kirish
            </a>
        </div>
    </div>
</section>

<!-- ========== FOOTER ========== -->
<footer class="bg-slate-900 dark:bg-slate-950 text-slate-400 border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">
            <div>
                <a href="/" class="flex items-center gap-2 mb-4">
                    <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-violet-600 rounded-xl flex items-center justify-center">📚</div>
                    <span class="text-xl font-black text-white">Kitob<span class="text-indigo-400">xon</span></span>
                </a>
                <p class="text-slate-500 text-sm leading-relaxed">O'zbek tilidagi eng yaxshi onlayn kitob o'qish platformasi.</p>
            </div>
            <div>
                <h4 class="font-bold text-white mb-4">Platforma</h4>
                <ul class="space-y-2.5">
                    @foreach([['Kitoblar','/books'],['Haqimizda','/about'],['FAQ','/faq'],['Aloqa','/contact']] as $link)
                    <li><a href="{{ $link[1] }}" class="text-slate-500 hover:text-indigo-400 transition-colors text-sm">{{ $link[0] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-white mb-4">Huquqiy</h4>
                <ul class="space-y-2.5">
                    @foreach([['Maxfiylik','/privacy'],['Shartlar','/terms'],['Cookie','/cookies']] as $link)
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('visible'); });
        }, { threshold: 0.05, rootMargin: '0px 0px -20px 0px' });
        document.querySelectorAll('.observe-animate').forEach(el => observer.observe(el));
    });
</script>

</body>
</html>
