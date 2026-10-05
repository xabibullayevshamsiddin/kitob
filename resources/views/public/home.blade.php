<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" x-data="{ mobileMenu: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kitobxon — Har hafta bitta sara kitob, chuqur tahlil va gamifikatsiya. O'zbekistondagi eng ilg'or kitobxonlar ekotizimi.">
    <title>Kitobxon — @yield('title', __('site.home.hero_title') . __('site.home.hero_title_bold'))</title>

    @include('partials.design-system')

    <!-- GSAP & ScrollTrigger for Subtle Editorial Physics Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        input[type="number"] {
            -moz-appearance: textfield;
            appearance: textfield;
        }
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        [x-cloak] { display: none !important; }

        /* Continuous Kinetic Ticker */
        @keyframes tickerLoop {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .ticker-track {
            display: flex;
            width: 200%;
            animation: tickerLoop 28s linear infinite;
        }
        .ticker-track:hover {
            animation-play-state: paused;
        }

        /* Subtle Audio wave dynamic bars */
        .bar-anim:nth-child(1) { animation: bounceBar 1.1s infinite ease-in-out; }
        .bar-anim:nth-child(2) { animation: bounceBar 0.85s infinite ease-in-out 0.2s; }
        .bar-anim:nth-child(3) { animation: bounceBar 1.3s infinite ease-in-out 0.4s; }
        .bar-anim:nth-child(4) { animation: bounceBar 1.0s infinite ease-in-out 0.1s; }
        .bar-anim:nth-child(5) { animation: bounceBar 0.75s infinite ease-in-out 0.3s; }
        @keyframes bounceBar {
            0%, 100% { height: 4px; }
            50% { height: 18px; }
        }

        /* Scrollytelling 3D Book Stage (Apple-grade Physics) */
        .scrolly-book-stage {
            perspective: 1600px;
            transform-style: preserve-3d;
        }
        .scrolly-book-cover {
            transform-origin: left center;
            transform-style: preserve-3d;
            will-change: transform;
        }
        .scrolly-cover-face {
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }
        .scrolly-book-inside {
            transform-style: preserve-3d;
        }
        @media (prefers-reduced-motion: reduce) {
            .scrolly-book-cover {
                transform: none !important;
            }
        }
    </style>
</head>
<body class="bg-ink-950 text-paper font-sans selection:bg-amber-500 selection:text-ink-950 antialiased min-h-screen relative ks-grain">

    <!-- ── Page Transition & Loader ── -->
    @include('components.page-loader')

    <div id="smooth-page-wrapper">
    <!-- ── Header Navigation ── -->
    <x-nav.main-header />

    <!-- ── 1. EDITORIAL HERO SECTION ── -->
    <section class="relative pt-14 md:pt-20 pb-16 md:pb-24 overflow-hidden border-b border-ink-border/50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                <!-- Left: Focused Copy with Staggered Entrance -->
                <div class="lg:col-span-6 space-y-6">
                    
                    <div class="hero-anim-item inline-flex items-center gap-2 px-3 py-1 rounded-badge bg-ink-900 border border-ink-border text-amber-400 font-mono text-xs tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                        @if(isset($featuredBook) && $featuredBook)
                            <span class="uppercase">{{ $featuredBook->week_number }}-HAFTA · {{ $featuredBook->title }}</span>
                        @else
                            <span class="uppercase">{{ __('site.home.badge_season') }}</span>
                        @endif
                    </div>

                    <h1 class="hero-anim-item text-3xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-paper font-serif leading-[1.18] sm:leading-[1.12] break-words">
                        {{ __('site.home.hero_title') }} <span class="italic text-amber-400">{{ __('site.home.hero_title_bold') }}</span> {{ __('site.home.hero_title_end') }}
                    </h1>

                    <p class="hero-anim-item text-sm sm:text-lg text-mist max-w-[56ch] leading-relaxed font-normal">
                        {{ __('site.home.hero_sub') }}
                    </p>

                    <div class="hero-anim-item flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3 pt-2">
                        @if(auth()->check())
                            <a href="{{ route('books.public') }}" 
                               class="ks-btn-primary inline-flex items-center justify-center gap-2 w-full sm:w-auto">
                                <span>{{ __('site.home.explore_books') }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @else
                            <a href="{{ route('register') }}" 
                               class="ks-btn-primary inline-flex items-center justify-center gap-2 w-full sm:w-auto">
                                <span>{{ __('site.home.start_now') }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                            <a href="{{ route('books.public') }}" 
                               class="ks-btn-ghost inline-flex items-center justify-center gap-2 w-full sm:w-auto">
                                <span>{{ __('site.home.books_catalog') }}</span>
                            </a>
                        @endif
                    </div>

                    <div class="hero-anim-item pt-4 flex items-center gap-4 text-xs text-mist border-t border-ink-border/40">
                        <div class="flex -space-x-2">
                            @if(isset($recentUsers) && $recentUsers->count())
                                @foreach($recentUsers as $ru)
                                    @if($ru->avatar)
                                        <img src="{{ $ru->avatar_url }}" class="w-7 h-7 rounded-full border border-ink-950 object-cover" title="{{ $ru->name }}" alt="{{ $ru->name }}">
                                    @else
                                        <span class="w-7 h-7 rounded-full bg-ink-800 border border-ink-950 flex items-center justify-center font-mono text-[10px] text-paper" title="{{ $ru->name }}">
                                            {{ strtoupper(mb_substr($ru->name, 0, 2)) }}
                                        </span>
                                    @endif
                                @endforeach
                            @else
                                <span class="w-7 h-7 rounded-full bg-ink-800 border border-ink-950 flex items-center justify-center font-mono text-[10px] text-paper">KB</span>
                            @endif
                        </div>
                        <p><strong class="text-paper font-mono text-sm counter-element" data-target="{{ $usersCount ?? 0 }}">{{ $usersCount ?? 0 }}</strong> {{ __('site.home.active_readers') }}</p>
                    </div>

                </div>

                <!-- Right: Featured Book in Editorial Frame -->
                <div class="lg:col-span-6 flex justify-center">
                    <div class="hero-anim-item relative w-full max-w-lg" x-data="{ playing: false }">
                        @if(isset($featuredBook) && $featuredBook)
                            <div class="ks-panel p-5 bg-ink-900 border border-ink-border relative">
                                <div class="flex items-center justify-between mb-3 text-xs">
                                    <span class="ks-badge bg-amber-500/10 text-amber-400 border border-amber-500/20 font-mono">
                                        {{ __('site.home.week_book') }}
                                    </span>
                                    <span class="flex items-center gap-1.5 text-xs text-emerald-400 font-mono">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                        {{ __('site.home.now_reading') }}
                                    </span>
                                </div>

                                {{-- Signature 3D Book Card (2:3 nisbat) --}}
                                <div class="w-full max-w-[260px] mx-auto py-2">
                                    <x-ui.book-card :book="$featuredBook" ratio="2 / 3" :showMeta="false"></x-ui.book-card>
                                    <div class="pt-3 mt-3 border-t border-ink-border flex items-center justify-between">
                                        <span class="ks-eyebrow">{{ $featuredBook->genre }}</span>
                                        <a href="{{ route('books.show', $featuredBook->slug) }}"
                                           class="ks-btn-primary !py-1 !px-3 !text-xs inline-flex items-center gap-1.5">
                                            <span>O'qish</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </a>
                                    </div>
                                </div>

                                @php
                                    $featuredAudio = $featuredBook->audios()->first();
                                @endphp
                                @if($featuredAudio)
                                    <div class="mt-3 p-3 rounded-card bg-ink-950 border border-ink-border flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <button @click="playing = !playing" class="w-8 h-8 rounded-btn bg-amber-500 hover:bg-amber-400 text-ink-950 flex items-center justify-center font-bold text-xs transition-colors cursor-pointer">
                                                <span x-text="playing ? '⏸' : '▶'">▶</span>
                                            </button>
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-paper truncate max-w-[140px]">{{ $featuredAudio->title ?: 'Audio dars' }}</p>
                                                <p class="text-[10px] text-mist font-mono">{{ $featuredAudio->duration ? round($featuredAudio->duration / 60) . ' ' . __('site.common.minutes') : __('site.home.audio_format') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1 h-5 px-1">
                                            <span class="w-0.5 bg-amber-400 rounded-full bar-anim"></span>
                                            <span class="w-0.5 bg-amber-400 rounded-full bar-anim"></span>
                                            <span class="w-0.5 bg-amber-400 rounded-full bar-anim"></span>
                                            <span class="w-0.5 bg-amber-400 rounded-full bar-anim"></span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="ks-panel p-8 bg-ink-900 border border-ink-border text-center space-y-2">
                                <span class="ks-eyebrow">{{ __('site.home.weekly_reading') }}</span>
                                <h3 class="text-xl font-bold font-serif text-paper">{{ __('site.home.coming_soon') }}</h3>
                                <p class="text-xs text-mist">{{ __('site.home.coming_soon_sub') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ── 2. SCROLLYTELLING BOOK REVEAL SECTION (APPLE-GRADE PINNED SCROLL) ── -->
    @php
        $scrollyBook = $featuredBook ?? null;
        $scrollyTitle = $scrollyBook ? $scrollyBook->title : "O'tkan Kunlar";
        $scrollyAuthor = $scrollyBook ? $scrollyBook->author : "Abdulla Qodiriy";
        $scrollyGenre = $scrollyBook ? $scrollyBook->genre : "Tarixiy Roman";
        $scrollyDesc = $scrollyBook && $scrollyBook->description 
            ? \Illuminate\Support\Str::limit(strip_tags((string)$scrollyBook->description), 280) 
            : "O'zbek adabiyotining shoh asari bo'lmish ushbu kitobda inson qadri, muhabbat va vatan taqdiri teran falsafiy nigoh bilan yoritilgan.";
        $scrollyInsideExcerpt = $scrollyBook && $scrollyBook->description
            ? \Illuminate\Support\Str::limit(strip_tags((string)$scrollyBook->description), 160)
            : "Har bir sahifada chuqur ma'no, har bir bobda yangi kashfiyot...";
        $scrollyLink = $scrollyBook ? route('books.show', $scrollyBook->slug) : route('books.public');
        $scrollyReaders = $featuredReadersCount ?? ($scrollyBook ? 48 : 24);
        $scrollyChapters = $featuredChaptersCount ?? ($scrollyBook ? 18 : 12);
        $scrollyAudio = $featuredAudioMinutes ?? 35;
        $scrollyCover = $scrollyBook->cover_url ?? null;
    @endphp

    <section id="book-reveal-section" class="relative w-full min-h-screen bg-ink-950 border-b border-ink-border flex items-center justify-center py-16 lg:py-0">
        <!-- Atmospheric Ambient Spotlight behind the book -->
        <div class="scrolly-ambient-glow absolute inset-0 pointer-events-none flex items-center justify-center opacity-60 overflow-hidden" aria-hidden="true">
            <div class="w-[500px] lg:w-[650px] h-[500px] lg:h-[650px] rounded-full bg-gradient-radial from-amber-500/15 via-vermilion/5 to-transparent blur-3xl"></div>
        </div>

        <!-- Inner Centered Container -->
        <div class="w-full max-w-7xl mx-auto px-6 relative z-10">
            <!-- Eyebrow Bar: Chapter / Week Indicator -->
            <div class="scrolly-eyebrow-bar mb-6 lg:mb-10 flex flex-wrap items-center justify-between gap-4 border-b border-ink-border/50 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    <span class="font-mono text-xs uppercase tracking-widest text-amber-400 font-bold">
                        {{ $scrollyBook ? ($scrollyBook->week_number . '-HAFTA MUTOLAASI') : 'HAFTANING ASOSIY KITOBI' }}
                    </span>
                    <span class="text-mist/40">|</span>
                    <span class="font-mono text-[11px] text-mist tracking-wider uppercase">
                        Scrollytelling Tajribasi
                    </span>
                </div>
                <div class="hidden lg:flex items-center gap-2 font-mono text-[11px] text-mist">
                    <span>Scroll qiling va kitob ochilishini kuzating</span>
                    <svg class="w-3.5 h-3.5 animate-bounce text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
            </div>

            <!-- Stage Grid: 3D Book on Left/Center + Revealed Story on Right -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-14 items-center">
                
                <!-- LEFT (cols 1-5): The Grand 3D Book Stage -->
                <div class="lg:col-span-5 flex justify-center items-center">
                    <div class="scrolly-book-stage relative w-[240px] sm:w-[280px] lg:w-[320px] aspect-[2/3] select-none">
                        
                        <!-- Dynamic Floor Shadow underneath book -->
                        <div class="scrolly-book-shadow absolute -bottom-6 left-[8%] right-[8%] h-7 rounded-full bg-black/80 blur-xl"></div>

                        <!-- Inner Spine and Layered Paper Edge (Right) -->
                        <div class="scrolly-book-pages-edge absolute top-1 bottom-1 -right-2 w-4 rounded-r-sm bg-gradient-to-r from-[#D9D2C5] via-[#FAF7F2] to-[#E5DFD5] shadow-md border-r border-[#B8B0A2]"
                             style="background-image: repeating-linear-gradient(90deg, #D9D2C5 0 1px, #FAF7F2 1px 2px);"></div>

                        <!-- Inside Book Surface (Revealed as cover opens) -->
                        <div class="scrolly-book-inside absolute inset-0 rounded-l-[2px] rounded-r-md bg-[#FAF7F2] text-ink-950 p-6 flex flex-col justify-between overflow-hidden shadow-inner border border-[#E5DFD5]">
                            <div class="border-b border-[#E5DFD5] pb-3">
                                <span class="font-mono text-[10px] uppercase tracking-widest text-[#8B9BAD] block">Kitobxon Nashri</span>
                                <span class="font-serif italic text-xs text-[#526071] mt-0.5 block">Haftalik Tanlov</span>
                            </div>
                            <div class="space-y-2 py-4">
                                <div class="w-8 h-0.5 bg-amber-500 mb-2"></div>
                                <p class="font-serif text-xs text-[#1A1D24] leading-relaxed line-clamp-6">
                                    {{ $scrollyInsideExcerpt }}
                                </p>
                            </div>
                            <div class="border-t border-[#E5DFD5] pt-2 flex items-center justify-between text-[10px] font-mono text-[#8B9BAD]">
                                <span>1-Bob Mutolaasi</span>
                                <span>№ {{ $scrollyBook->week_number ?? 1 }}</span>
                            </div>
                        </div>

                        <!-- The 3D Book Cover (Rotates open via GSAP scrub) -->
                        <div class="scrolly-book-cover absolute inset-0 rounded-l-[3px] rounded-r-md origin-left">
                            
                            <!-- Front Face of Cover (Visible 0deg to -90deg) -->
                            <div class="scrolly-cover-face scrolly-cover-front absolute inset-0 rounded-l-[3px] rounded-r-md bg-ink-900 border border-ink-border overflow-hidden shadow-2xl">
                                @if($scrollyCover)
                                    <img src="{{ $scrollyCover }}" alt="{{ $scrollyTitle }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-ink-900 via-ink-800 to-ink-950 p-6 flex flex-col justify-between border border-amber-500/20">
                                        <div>
                                            <span class="font-mono text-[10px] text-amber-400 tracking-wider uppercase block">Kitobxon Exclusive</span>
                                            <h3 class="font-serif text-lg font-bold text-paper mt-2">{{ $scrollyTitle }}</h3>
                                            <p class="text-xs text-mist font-sans mt-1">{{ $scrollyAuthor }}</p>
                                        </div>
                                        <div class="pt-4 border-t border-ink-border flex justify-between items-center text-xs font-mono text-amber-400">
                                            <span>Hafta Kitobi</span>
                                            <span>✦</span>
                                        </div>
                                    </div>
                                @endif

                                <!-- Spine Crease Shadow overlay on cover -->
                                <div class="absolute inset-y-0 left-0 w-8 pointer-events-none bg-gradient-to-r from-black/60 via-black/20 to-transparent"></div>
                                <div class="absolute inset-0 pointer-events-none ring-1 ring-inset ring-white/10 rounded-l-[3px] rounded-r-md"></div>
                            </div>

                            <!-- Back Face of Cover (Inside Forzats, Visible -90deg to -180deg) -->
                            <div class="scrolly-cover-face scrolly-cover-back absolute inset-0 rounded-r-[3px] rounded-l-md bg-[#FAF7F2] border border-[#E5DFD5] p-5 flex flex-col justify-between shadow-2xl text-ink-950"
                                 style="transform: rotateY(180deg);">
                                <div class="border-b border-[#E5DFD5] pb-2">
                                    <span class="font-mono text-[9px] uppercase tracking-widest text-[#8B9BAD] block">Kitobxon Nashri</span>
                                    <span class="font-serif italic text-[11px] text-[#526071]">Muqova Forzatsi</span>
                                </div>
                                <div class="text-center py-4">
                                    <span class="font-serif italic text-xs text-[#1A1D24]">"Mutolaa — qalb ko'zgusi"</span>
                                </div>
                                <div class="border-t border-[#E5DFD5] pt-2 flex items-center justify-between font-mono text-[9px] text-[#8B9BAD]">
                                    <span>✦ ✦ ✦</span>
                                    <span>Kitobxon</span>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- RIGHT (cols 6-12): Revealed Content & Scrolly Narrative Panel -->
                <div class="lg:col-span-7 scrolly-content-panel space-y-6">
                    
                    <!-- Badge & Week Tag -->
                    <div class="scrolly-content-item inline-flex items-center gap-2 px-3 py-1 rounded-badge bg-amber-500/10 border border-amber-500/30 text-amber-400 font-mono text-xs tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                        <span class="uppercase">{{ $scrollyBook ? ($scrollyBook->week_number . '-HAFTA TANLOVI') : 'MAXSUS NASHR' }}</span>
                    </div>

                    <!-- Book Title & Author -->
                    <div class="scrolly-content-item space-y-2">
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold font-serif text-paper leading-[1.2] tracking-tight">
                            {{ $scrollyTitle }}
                        </h2>
                        <div class="text-base sm:text-lg text-amber-400 font-sans flex flex-wrap items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                            <span>{{ $scrollyAuthor }}</span>
                            @if($scrollyGenre)
                                <span class="text-mist text-xs font-mono uppercase px-2 py-0.5 rounded-badge bg-ink-900 border border-ink-border">
                                    {{ $scrollyGenre }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Narrative Description -->
                    <p class="scrolly-content-item text-sm sm:text-base text-mist leading-relaxed font-sans max-w-2xl">
                        {{ $scrollyDesc }}
                    </p>

                    <!-- Actions: Read CTA + Audio / Details -->
                    <div class="scrolly-content-item flex flex-wrap items-center gap-3 pt-1">
                        <a href="{{ $scrollyLink }}" class="ks-btn-primary inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold">
                            <span>Mutolaani boshlash</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        <a href="{{ route('books.public') }}" class="ks-btn-ghost inline-flex items-center gap-2 px-4 py-2.5 text-sm">
                            <span>Barcha kitoblar</span>
                        </a>
                    </div>

                    <!-- 70-100% STATS ROW: Dynamic Live Counters -->
                    <div class="scrolly-stats-row pt-6 border-t border-ink-border/70 grid grid-cols-3 gap-3 sm:gap-4 max-w-xl">
                        
                        <!-- Stat 1: Readers -->
                        <div class="p-3.5 rounded-card bg-ink-900/90 border border-ink-border/80 text-left">
                            <span class="text-[10px] sm:text-[11px] font-mono uppercase tracking-wider text-mist block">Faol O'quvchilar</span>
                            <p class="font-mono text-xl sm:text-2xl font-bold text-paper mt-1">
                                <span class="scrolly-stat-num" data-target="{{ $scrollyReaders }}">0</span>
                                <span class="text-xs text-amber-400 font-normal">+</span>
                            </p>
                        </div>

                        <!-- Stat 2: Chapters -->
                        <div class="p-3.5 rounded-card bg-ink-900/90 border border-ink-border/80 text-left">
                            <span class="text-[10px] sm:text-[11px] font-mono uppercase tracking-wider text-mist block">Mundarija</span>
                            <p class="font-mono text-xl sm:text-2xl font-bold text-amber-400 mt-1">
                                <span class="scrolly-stat-num" data-target="{{ $scrollyChapters }}">0</span>
                                <span class="text-xs text-mist font-normal">bob</span>
                            </p>
                        </div>

                        <!-- Stat 3: Audio Duration -->
                        <div class="p-3.5 rounded-card bg-ink-900/90 border border-ink-border/80 text-left">
                            <span class="text-[10px] sm:text-[11px] font-mono uppercase tracking-wider text-mist block">Audio Tahlil</span>
                            <p class="font-mono text-xl sm:text-2xl font-bold text-emerald-400 mt-1">
                                <span class="scrolly-stat-num" data-target="{{ $scrollyAudio }}">0</span>
                                <span class="text-xs text-mist font-normal">daq</span>
                            </p>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </section>

    <!-- ── 3. EDITORIAL KINETIC TICKER ── -->
    <div class="py-3.5 border-b border-ink-border bg-ink-900/60 overflow-hidden relative">
        <div class="ticker-track font-mono text-[11px] uppercase tracking-widest text-mist flex items-center gap-8 whitespace-nowrap">
            @foreach(['ticker_1', 'ticker_2', 'ticker_3', 'ticker_4', 'ticker_5', 'ticker_6', 'ticker_1', 'ticker_2', 'ticker_3', 'ticker_4', 'ticker_5', 'ticker_6'] as $tk)
                <span class="flex items-center gap-2"><span class="text-amber-500 font-bold">✦</span> {{ __("site.home.$tk") }}</span>
            @endforeach
        </div>
    </div>

    <!-- ── JONLI PLATFORMA STATISTIKASI ── -->
    <section class="py-8 border-b border-ink-border bg-ink-950">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 rounded-card bg-ink-900 border border-ink-border text-center">
                    <p class="text-2xl sm:text-3xl font-bold text-paper font-mono ks-stat">
                        <span class="counter-element" data-target="{{ $usersCount ?? 0 }}">{{ $usersCount ?? 0 }}</span>
                    </p>
                    <p class="text-[11px] text-mist mt-1 uppercase tracking-wider font-mono">{{ __('site.home.stat_readers') }}</p>
                </div>
                <div class="p-4 rounded-card bg-ink-900 border border-ink-border text-center">
                    <p class="text-2xl sm:text-3xl font-bold text-amber-400 font-mono ks-stat">
                        <span class="counter-element" data-target="{{ $booksCount ?? 0 }}">{{ $booksCount ?? 0 }}</span>
                    </p>
                    <p class="text-[11px] text-mist mt-1 uppercase tracking-wider font-mono">{{ __('site.home.stat_books') }}</p>
                </div>
                <div class="p-4 rounded-card bg-ink-900 border border-ink-border text-center">
                    <p class="text-2xl sm:text-3xl font-bold text-emerald-400 font-mono ks-stat">
                        <span class="counter-element" data-target="{{ $maxStreak ?? 0 }}">{{ $maxStreak ?? 0 }}</span>
                    </p>
                    <p class="text-[11px] text-mist mt-1 uppercase tracking-wider font-mono">{{ __('site.home.stat_streak') }}</p>
                </div>
                <div class="p-4 rounded-card bg-ink-900 border border-ink-border text-center">
                    <p class="text-2xl sm:text-3xl font-bold text-paper font-mono ks-stat">
                        <span class="counter-element" data-target="{{ $totalMinutes ?? 0 }}">{{ $totalMinutes ?? 0 }}</span>
                    </p>
                    <p class="text-[11px] text-mist mt-1 uppercase tracking-wider font-mono">{{ __('site.home.stat_minutes') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ── FAXRIY BESHLIK (TOP 5 HALL OF FAME) ── -->
    @php
        $topFiveUsers = $topFiveUsers ?? app(\App\Services\LeaderboardService::class)->getTopUsers(5);
    @endphp
    @if(isset($topFiveUsers) && $topFiveUsers->isNotEmpty())
    <section class="py-12 border-b border-ink-border bg-ink-900/40 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-badge bg-amber-500/10 border border-amber-500/30 text-amber-400 font-mono text-[11px] uppercase tracking-wider mb-2">
                        <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="currentColor"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>
                        <span>SHARAF ZALI</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold font-serif text-paper">
                        Faxriy Beshlik <span class="text-amber-400 font-sans text-xl sm:text-2xl font-normal">(Top 5)</span>
                    </h2>
                    <p class="text-mist text-xs sm:text-sm mt-1 max-w-xl">
                        Platformaning all-time reytingida eng yuqori natija va mutolaa intizomini ko'rsatayotgan peshqadam kitobxonlar.
                    </p>
                </div>
                <div>
                    <a href="{{ route('leaderboard') }}" class="ks-btn-ghost text-xs py-2 px-3.5 inline-flex items-center gap-1.5 hover:border-amber-400/50">
                        <span>To'liq reytingni ko'rish</span>
                        <svg class="w-3.5 h-3.5 text-mist" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-4">
                @foreach($topFiveUsers as $topUser)
                    @php
                        $rank = $loop->iteration;
                        $cardBorder = match($rank) {
                            1 => 'border-[#F59E0B]/50 hover:border-[#F59E0B] bg-gradient-to-b from-[#F59E0B]/12 via-ink-900/80 to-ink-950 shadow-[0_4px_20px_rgba(245,158,11,0.12)]',
                            2 => 'border-[#E2E8F0]/40 hover:border-[#E2E8F0] bg-gradient-to-b from-[#E2E8F0]/8 via-ink-900/80 to-ink-950 shadow-[0_4px_16px_rgba(226,232,240,0.08)]',
                            3 => 'border-[#D97706]/40 hover:border-[#D97706] bg-gradient-to-b from-[#D97706]/8 via-ink-900/80 to-ink-950 shadow-[0_4px_16px_rgba(217,119,6,0.08)]',
                            default => 'border-[#6366F1]/30 hover:border-[#6366F1] bg-gradient-to-b from-[#6366F1]/6 via-ink-900/80 to-ink-950 shadow-[0_4px_16px_rgba(99,102,241,0.08)]',
                        };
                    @endphp
                    <a href="{{ route('profile.show', $topUser->username) }}"
                       class="group p-4 rounded-panel border {{ $cardBorder }} transition-all duration-200 hover:-translate-y-1 flex flex-col items-center text-center relative overflow-hidden">
                        
                        <div class="mb-3">
                            <x-ui.avatar :user="$topUser" size="xl" shape="rounded-panel" :rank="$rank" />
                        </div>

                        <div class="space-y-1 w-full min-w-0">
                            <h3 class="text-sm font-bold text-paper truncate group-hover:text-amber-400 transition-colors">
                                {{ $topUser->name }}
                            </h3>
                            <p class="text-[11px] text-mist font-mono truncate">
                                {{ '@' . $topUser->username }}
                            </p>
                        </div>

                        <div class="mt-3 pt-3 border-t border-ink-border/60 w-full flex items-center justify-between">
                            <x-ui.rank-badge :rank="$rank" size="sm" />
                            <span class="font-mono text-xs font-bold text-amber-400">
                                {{ number_format($topUser->total_points) }} <span class="text-[10px] text-mist font-normal">ball</span>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- ── 3. ASYMMETRICAL BENTO GRID ── -->
    <section id="features" class="py-20 md:py-28 relative">
        <div class="max-w-7xl mx-auto px-6">
            
            <div class="bento-header max-w-2xl mb-12 space-y-2">
                <span class="ks-eyebrow">{{ __('site.home.features_badge') }}</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-paper font-serif leading-tight">
                    {{ __('site.home.features_title') }} <span class="text-amber-400 italic">{{ __('site.home.features_title_b') }}</span> {{ __('site.home.features_title_e') }}
                </h2>
                <p class="text-mist text-sm sm:text-base leading-relaxed">
                    {{ __('site.home.features_sub') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-5">

                <!-- Bento 1 (Large - Col 7): Multi-format Reading -->
                <div class="bento-card md:col-span-7 ks-panel p-6 sm:p-8 flex flex-col justify-between bg-ink-900 border border-ink-border">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold font-serif text-paper">{{ __('site.home.bento1_title') }}</h3>
                        <p class="text-mist text-sm max-w-md leading-relaxed">
                            {{ __('site.home.bento1_sub') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-3 gap-3 mt-8 pt-5 border-t border-ink-border">
                        <div class="p-3 rounded-card bg-ink-950 border border-ink-border text-center">
                            <svg class="w-4 h-4 mx-auto text-amber-400 mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            <p class="text-xs font-semibold text-paper">{{ __('site.home.bento1_ebook') }}</p>
                            <p class="text-[10px] text-mist font-mono">{{ __('site.home.bento1_ebook_sub') }}</p>
                        </div>
                        <div class="p-3 rounded-card bg-ink-950 border border-ink-border text-center">
                            <svg class="w-4 h-4 mx-auto text-amber-400 mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                            <p class="text-xs font-semibold text-paper">{{ __('site.home.bento1_audio') }}</p>
                            <p class="text-[10px] text-mist font-mono">{{ __('site.home.bento1_audio_sub') }}</p>
                        </div>
                        <div class="p-3 rounded-card bg-ink-950 border border-ink-border text-center">
                            <svg class="w-4 h-4 mx-auto text-amber-400 mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                            <p class="text-xs font-semibold text-paper">{{ __('site.home.bento1_video') }}</p>
                            <p class="text-[10px] text-mist font-mono">{{ __('site.home.bento1_video_sub') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Bento 2 (Col 5): Streak & Habit -->
                <div class="bento-card md:col-span-5 ks-panel p-6 sm:p-8 flex flex-col justify-between bg-ink-900 border border-ink-border">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-10 h-10 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-500 flex items-center justify-center">
                                <svg class="w-5 h-5 ks-flame is-lit" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c-.5 2.5-2.5 4.5-4 6-2 2-3 4.5-3 7 0 4.4 3.6 8 8 8s8-3.6 8-8c0-3.5-2-6-4-8-.5 2-2 3.5-3 4-1-2.5 0-6.5-2-9z"/></svg>
                            </span>
                            <span class="font-mono text-xs text-amber-400 bg-amber-400/10 px-2 py-0.5 rounded-badge border border-amber-400/20 uppercase">
                                {{ __('site.home.bento2_tag') }}
                            </span>
                        </div>
                        <h3 class="text-xl font-bold font-serif text-paper">{{ __('site.home.bento2_title') }}</h3>
                        <p class="text-mist text-sm mt-2 leading-relaxed">
                            {{ __('site.home.bento2_sub') }} <strong class="text-amber-400 font-mono">{{ $maxStreak ?? 0 }} {{ __('site.common.days') }}</strong>.
                        </p>
                    </div>

                    @php
                        $displayStreak = auth()->check() ? (auth()->user()->current_streak ?? 0) : ($maxStreak ?? 0);
                    @endphp
                    <div class="mt-6 p-4 rounded-card bg-ink-950 border border-ink-border flex items-center justify-between">
                        <div>
                            <p class="text-[10px] text-mist uppercase font-mono tracking-wider">
                                {{ auth()->check() ? __('site.home.your_streak') : __('site.home.platform_record') }}
                            </p>
                            <p class="text-2xl font-bold text-amber-400 font-mono">
                                <span class="counter-element" data-target="{{ $displayStreak }}">{{ $displayStreak }}</span> {{ __('site.common.days') }}
                            </p>
                        </div>
                        <div class="flex gap-1">
                            @for($i = 0; $i < min(max($displayStreak, 1), 5); $i++)
                                <div class="w-2.5 h-6 rounded-sm {{ $i == 4 ? 'bg-amber-400' : 'bg-amber-500/70' }}"></div>
                            @endfor
                        </div>
                    </div>
                </div>

                <!-- Bento 3 (Col 5): AI Analysis -->
                <div class="bento-card md:col-span-5 ks-panel p-6 sm:p-8 flex flex-col justify-between bg-ink-900 border border-ink-border">
                    <div>
                        <div class="w-10 h-10 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M12 2v4m0 12v4M2 12h4m12 0h4m-3.17-6.83l-2.83 2.83m-8 8l-2.83 2.83m0-13.66l2.83 2.83m8 8l2.83 2.83"/></svg>
                        </div>
                        <h3 class="text-xl font-bold font-serif text-paper">{{ __('site.home.bento3_title') }}</h3>
                        <p class="text-mist text-sm mt-2 leading-relaxed">
                            {{ __('site.home.bento3_sub') }}
                        </p>
                    </div>

                    <div class="mt-6 space-y-2 text-xs">
                        <div class="p-3 rounded-card bg-ink-950 border border-ink-border text-mist">
                            "{{ __('site.home.bento3_q') }}"
                        </div>
                        <div class="p-3 rounded-card bg-ink-800 border border-ink-border text-paper flex items-center gap-2">
                            <span class="text-amber-400 font-bold">✦</span>
                            <span>{{ __('site.home.bento3_a') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Bento 4 (Col 7): Community & Debates -->
                <div class="bento-card md:col-span-7 ks-panel p-6 sm:p-8 flex flex-col justify-between bg-ink-900 border border-ink-border">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <h3 class="text-xl font-bold font-serif text-paper">{{ __('site.home.bento4_title') }}</h3>
                        <p class="text-mist text-sm max-w-md leading-relaxed">
                            {{ __('site.home.bento4_sub') }}
                        </p>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-2.5">
                        <span class="px-3 py-1 rounded-badge bg-ink-950 border border-ink-border text-xs font-mono text-mist">
                            {{ __('site.home.bento4_chat') }}
                        </span>
                        <span class="px-3 py-1 rounded-badge bg-ink-950 border border-ink-border text-xs font-mono text-mist">
                            {{ __('site.home.bento4_board') }}
                        </span>
                        <span class="px-3 py-1 rounded-badge bg-ink-950 border border-ink-border text-xs font-mono text-mist">
                            {{ __('site.home.bento4_qa') }}
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ── 4. HOW IT WORKS (Numbered Editorial Steps) ── -->
    <section class="py-20 md:py-28 border-t border-ink-border bg-ink-900/30">
        <div class="max-w-7xl mx-auto px-6">
            
            <div class="step-header text-center max-w-xl mx-auto mb-14 space-y-2">
                <span class="ks-eyebrow">{{ __('site.home.steps_badge') }}</span>
                <h2 class="text-3xl sm:text-4xl font-bold font-serif text-paper">{{ __('site.home.steps_title') }}</h2>
                <p class="text-mist text-sm mt-1">{{ __('site.home.steps_sub') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="step-card p-6 sm:p-8 rounded-card bg-ink-900 border border-ink-border space-y-3">
                    <span class="font-mono text-3xl font-bold text-amber-500">01</span>
                    <h3 class="text-lg font-bold font-serif text-paper">{{ __('site.home.step1_title') }}</h3>
                    <p class="text-xs text-mist leading-relaxed">
                        {{ __('site.home.step1_sub') }}
                    </p>
                </div>

                <div class="step-card p-6 sm:p-8 rounded-card bg-ink-900 border border-ink-border space-y-3">
                    <span class="font-mono text-3xl font-bold text-amber-500">02</span>
                    <h3 class="text-lg font-bold font-serif text-paper">{{ __('site.home.step2_title') }}</h3>
                    <p class="text-xs text-mist leading-relaxed">
                        {{ __('site.home.step2_sub') }}
                    </p>
                </div>

                <div class="step-card p-6 sm:p-8 rounded-card bg-ink-900 border border-ink-border space-y-3">
                    <span class="font-mono text-3xl font-bold text-amber-500">03</span>
                    <h3 class="text-lg font-bold font-serif text-paper">{{ __('site.home.step3_title') }}</h3>
                    <p class="text-xs text-mist leading-relaxed">
                        {{ __('site.home.step3_sub') }}
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- ── 5. CALL TO ACTION ── -->
    <section class="py-20 md:py-28 border-t border-ink-border relative overflow-hidden bg-ink-950">
        <div class="cta-box max-w-4xl mx-auto px-6 text-center space-y-5">
            
            <h2 class="text-3xl sm:text-5xl font-bold font-serif text-paper tracking-tight leading-tight">
                {{ __('site.home.cta_title_1') }}<br class="hidden sm:block">
                <span class="text-amber-400 italic">{{ __('site.home.cta_title_2') }}</span> {{ __('site.home.cta_title_3') }}
            </h2>

            <p class="text-mist text-sm sm:text-base max-w-xl mx-auto leading-relaxed">
                {{ __('site.home.cta_sub', ['books' => $booksCount ?? 0]) }}
            </p>

            <div class="pt-3 flex justify-center">
                @if(auth()->check())
                    <a href="{{ route('books.public') }}" 
                       class="ks-btn-primary inline-flex items-center gap-2">
                        {{ __('site.home.explore_books') }} →
                    </a>
                @else
                    <a href="{{ route('register') }}" 
                       class="ks-btn-primary inline-flex items-center gap-2">
                        {{ __('site.home.start_reading_cta') }}
                    </a>
                @endif
            </div>

        </div>
    </section>

    <!-- ── Universal Footer ── -->
    <x-nav.main-footer />
    </div>

    <!-- ── Motion Script (GSAP + Dynamic Rolling Counters) ── -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof gsap !== 'undefined') {
                gsap.registerPlugin(ScrollTrigger);

                // Auto-refresh on standard events
                ScrollTrigger.config({
                    autoRefreshEvents: "visibilitychange,DOMContentLoaded,load,resize"
                });

                // Touch stability: normalize scroll on touch devices to prevent mobile browser address bar jumps
                if (ScrollTrigger.isTouch === 1) {
                    ScrollTrigger.normalizeScroll({ allowNestedScroll: true });
                }

                // Silliq ichki havolalar (anchor link) — global scroll-smooth o'rniga nuqtali JS smooth scroll
                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', function(e) {
                        const targetId = this.getAttribute('href');
                        if (targetId && targetId.length > 1) {
                            const targetEl = document.querySelector(targetId);
                            if (targetEl) {
                                e.preventDefault();
                                targetEl.scrollIntoView({ behavior: 'smooth' });
                            }
                        }
                    });
                });

                // 1. Header Entrance
                gsap.fromTo('#site-header',
                    { y: -20, opacity: 0 },
                    { y: 0, opacity: 1, duration: 0.6, ease: "power2.out" }
                );

                // 2. Editorial Hero Staggered Entrance Reveal
                gsap.fromTo('.hero-anim-item', 
                    { opacity: 0, y: 25 },
                    { 
                        opacity: 1, 
                        y: 0, 
                        duration: 0.7, 
                        stagger: 0.08, 
                        ease: "power2.out",
                        clearProps: "all"
                    }
                );

                // ── 2.1. SCROLLYTELLING BOOK REVEAL SCENE (APPLE-GRADE PINNED SCROLL) ──
                const bookRevealSection = document.getElementById('book-reveal-section');
                if (bookRevealSection) {
                    const mm = gsap.matchMedia();

                    // Desktop (min-width: 1024px) AND prefers-reduced-motion: no-preference
                    mm.add("(min-width: 1024px) and (prefers-reduced-motion: no-preference)", () => {
                        gsap.set('.scrolly-book-cover', { rotateY: 0, transformOrigin: "left center" });
                        gsap.set('.scrolly-book-shadow', { scaleX: 1, opacity: 0.6, x: 0 });
                        gsap.set('.scrolly-content-panel', { opacity: 0, x: 45 });
                        gsap.set('.scrolly-stats-row', { opacity: 0, y: 25 });

                        const statElements = document.querySelectorAll('.scrolly-stat-num');
                        const statTargets = Array.from(statElements).map(el => parseInt(el.getAttribute('data-target') || '0', 10));
                        const statProgressObj = { progress: 0 };

                        const scrollyTl = gsap.timeline({
                            scrollTrigger: {
                                trigger: '#book-reveal-section',
                                start: 'top top',
                                end: '+=150%',
                                pin: true,
                                scrub: 1,
                                anticipatePin: 1,
                                invalidateOnRefresh: true,
                            }
                        });

                        // 0% -> 30%: Book cover opens in 3D & floor shadow expands
                        scrollyTl.to('.scrolly-book-cover', {
                            rotateY: -135,
                            ease: 'power1.inOut',
                            duration: 0.35,
                        }, 0);

                        scrollyTl.to('.scrolly-book-shadow', {
                            scaleX: 1.25,
                            x: -24,
                            opacity: 0.9,
                            ease: 'power1.inOut',
                            duration: 0.35,
                        }, 0);

                        // 30% -> 70%: Story & narrative content panel slides in & fades in
                        scrollyTl.to('.scrolly-content-panel', {
                            opacity: 1,
                            x: 0,
                            ease: 'power2.out',
                            duration: 0.38,
                        }, 0.32);

                        // 70% -> 100%: Live stats row reveals & numbers count up with scroll
                        scrollyTl.to('.scrolly-stats-row', {
                            opacity: 1,
                            y: 0,
                            ease: 'power2.out',
                            duration: 0.3,
                        }, 0.7);

                        scrollyTl.to(statProgressObj, {
                            progress: 1,
                            ease: 'none',
                            duration: 0.3,
                            onUpdate: () => {
                                statElements.forEach((el, idx) => {
                                    const targetVal = statTargets[idx] || 0;
                                    const currentVal = Math.round(statProgressObj.progress * targetVal);
                                    el.textContent = currentVal.toLocaleString('en-US');
                                });
                            }
                        }, 0.7);

                        return () => {
                            scrollyTl.kill();
                        };
                    });

                    // Mobile (< 1024px) OR prefers-reduced-motion: reduce
                    mm.add("(max-width: 1023px), (prefers-reduced-motion: reduce)", () => {
                        // Static, clean presentation without scroll lock
                        gsap.set('.scrolly-book-cover', { rotateY: -28, transformOrigin: "left center" });
                        gsap.set('.scrolly-book-shadow', { scaleX: 1.1, opacity: 0.75, x: 0 });
                        gsap.set('.scrolly-content-panel', { opacity: 1, x: 0 });
                        gsap.set('.scrolly-stats-row', { opacity: 1, y: 0 });

                        document.querySelectorAll('.scrolly-stat-num').forEach(el => {
                            const t = el.getAttribute('data-target') || '0';
                            el.textContent = parseInt(t, 10).toLocaleString('en-US');
                        });
                    });
                }

                // 3. Bento Grid Reveal
                gsap.fromTo('.bento-header',
                    { opacity: 0, y: 25 },
                    {
                        opacity: 1,
                        y: 0,
                        duration: 0.6,
                        ease: "power2.out",
                        scrollTrigger: {
                            trigger: '#features',
                            start: "top 85%",
                        }
                    }
                );

                gsap.fromTo('.bento-card', 
                    { opacity: 0, y: 30 },
                    {
                        opacity: 1,
                        y: 0,
                        duration: 0.7,
                        stagger: 0.1,
                        ease: "power2.out",
                        scrollTrigger: {
                            trigger: '#features',
                            start: "top 80%",
                        },
                        clearProps: "all"
                    }
                );

                // 4. Step Cards Stagger Reveal
                gsap.fromTo('.step-card',
                    { opacity: 0, y: 25 },
                    {
                        opacity: 1,
                        y: 0,
                        duration: 0.6,
                        stagger: 0.1,
                        ease: "power2.out",
                        scrollTrigger: {
                            trigger: '.step-card',
                            start: "top 85%",
                        },
                        clearProps: "all"
                    }
                );

                // 5. CTA Box Reveal
                gsap.fromTo('.cta-box',
                    { opacity: 0, y: 20 },
                    {
                        opacity: 1,
                        y: 0,
                        duration: 0.7,
                        ease: "power2.out",
                        scrollTrigger: {
                            trigger: '.cta-box',
                            start: "top 85%",
                        },
                        clearProps: "all"
                    }
                );

                // 6. Dynamic Rolling Number Counters
                document.querySelectorAll('.counter-element').forEach(el => {
                    const target = parseInt(el.getAttribute('data-target') || 0, 10);
                    if (target === 0) {
                        el.textContent = '0';
                        return;
                    }
                    ScrollTrigger.create({
                        trigger: el,
                        start: "top 90%",
                        once: true,
                        onEnter: () => {
                            const obj = { count: 0 };
                            gsap.to(obj, {
                                count: target,
                                duration: 1.2,
                                ease: "power2.out",
                                onUpdate: () => {
                                    el.textContent = Math.floor(obj.count).toLocaleString('en-US');
                                },
                                onComplete: () => {
                                    el.textContent = target.toLocaleString('en-US');
                                }
                            });
                        }
                    });
                });

                // Recalculate ScrollTrigger positions once all resources and web fonts are fully loaded
                window.addEventListener('load', () => {
                    ScrollTrigger.refresh();
                });

                if (document.fonts && document.fonts.ready) {
                    document.fonts.ready.then(() => {
                        ScrollTrigger.refresh();
                    });
                }
            }
        });
    
        // ── Fail-safe: GSAP yuklanmasa yoki kechiksa kontent to'liq ko'rinadi ──
        const __kitobxonSeen = new WeakMap();
        function __kitobxonVisibilityFailsafe() {
            document.querySelectorAll('.hero-anim-item, .bento-header, .bento-card, .step-card, .cta-box, .legal-content, .about-stat-card, .value-card, .team-card, .contact-form-col, .contact-info-card, .faq-card, .book-card, .error-anim-item').forEach(el => {
                const r = el.getBoundingClientRect();
                if (r.height === 0) return;
                const s = getComputedStyle(el);
                if (parseFloat(s.opacity) >= 0.05) { __kitobxonSeen.delete(el); return; }

                if (typeof gsap === 'undefined') {
                    el.style.opacity = '1'; el.style.filter = 'none'; el.style.transform = 'none';
                    document.querySelectorAll('.scrolly-content-panel, .scrolly-stats-row').forEach(scEl => {
                        scEl.style.opacity = '1'; scEl.style.transform = 'none';
                    });
                    return;
                }

                const reached = r.top < window.innerHeight + 100;
                const first = __kitobxonSeen.get(el);
                if (reached || (first && Date.now() - first > 3000)) {
                    el.style.opacity = '1'; el.style.filter = 'none'; el.style.transform = 'none';
                    __kitobxonSeen.delete(el);
                } else if (!first) {
                    __kitobxonSeen.set(el, Date.now());
                }
            });
        }
        __kitobxonVisibilityFailsafe();
        window.addEventListener('scroll', __kitobxonVisibilityFailsafe, { passive: true });
        setInterval(__kitobxonVisibilityFailsafe, 1500);
    </script>

    <!-- ── Universal Toast Notification Container ── -->
    <x-toast-container />

    <!-- ── Mobile Bottom Navigation Bar ── -->
    <x-nav.mobile-bottom-bar />
</body>
</html>
