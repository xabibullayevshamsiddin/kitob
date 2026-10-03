<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark" x-data="{ mobileMenu: false }">
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
    </style>
</head>
<body class="bg-ink-950 text-paper font-sans selection:bg-amber-500 selection:text-ink-950 antialiased min-h-screen relative overflow-x-hidden ks-grain">

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

                    <h1 class="hero-anim-item text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-paper font-serif leading-[1.12]">
                        {{ __('site.home.hero_title') }} <span class="italic text-amber-400">{{ __('site.home.hero_title_bold') }}</span> {{ __('site.home.hero_title_end') }}
                    </h1>

                    <p class="hero-anim-item text-base sm:text-lg text-mist max-w-[56ch] leading-relaxed font-normal">
                        {{ __('site.home.hero_sub') }}
                    </p>

                    <div class="hero-anim-item flex flex-wrap items-center gap-3 pt-2">
                        @if(auth()->check())
                            <a href="{{ route('books.public') }}" 
                               class="ks-btn-primary inline-flex items-center gap-2">
                                <span>{{ __('site.home.explore_books') }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @else
                            <a href="{{ route('register') }}" 
                               class="ks-btn-primary inline-flex items-center gap-2">
                                <span>{{ __('site.home.start_now') }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                            <a href="{{ route('books.public') }}" 
                               class="ks-btn-ghost inline-flex items-center gap-2">
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

                                {{-- Signature 3D Book Card --}}
                                <div class="my-2">
                                    <x-ui.book-card :book="$featuredBook" ratio="4 / 5" />
                                </div>

                                @php
                                    $featuredAudio = $featuredBook->audios()->first();
                                @endphp
                                @if($featuredAudio)
                                    <div class="mt-4 p-3 rounded-card bg-ink-950 border border-ink-border flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <button @click="playing = !playing" class="w-8 h-8 rounded-btn bg-amber-500 hover:bg-amber-400 text-ink-950 flex items-center justify-center font-bold text-xs transition-colors">
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

    <!-- ── 2. EDITORIAL KINETIC TICKER ── -->
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
</body>
</html>
