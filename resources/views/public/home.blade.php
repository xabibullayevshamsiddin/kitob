<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="max-w-full" x-data="{ mobileMenu: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <x-seo-meta :title="__('site.home.hero_title') . __('site.home.hero_title_bold')" :description="__('site.home.hero_subtitle')" />

    @include('partials.design-system')

    <!-- Google Fonts: Instrument Serif (display) & Inter (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        /* ── Velorah Cinematic Hero CSS Variables & Effects ── */
        :root {
            --font-display: 'Instrument Serif', serif;
            --font-body: 'Inter', sans-serif;
            --background: 201 100% 13%;
            --foreground: 0 0% 100%;
            --muted-foreground: 240 4% 66%;
            --primary: 0 0% 100%;
            --primary-foreground: 0 0% 4%;
            --secondary: 0 0% 10%;
            --muted: 0 0% 10%;
            --accent: 0 0% 10%;
            --border: 0 0% 18%;
            --input: 0 0% 18%;
        }

        /* Liquid Glass Effect */
        .liquid-glass {
            background: rgba(255, 255, 255, 0.04);
            background-blend-mode: luminosity;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.2), 0 8px 24px -4px rgba(0, 0, 0, 0.4);
            position: relative;
            overflow: hidden;
        }
        .liquid-glass::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 1.4px;
            background: linear-gradient(180deg,
                rgba(255,255,255,0.45) 0%, rgba(255,255,255,0.15) 20%,
                rgba(255,255,255,0) 40%, rgba(255,255,255,0) 60%,
                rgba(255,255,255,0.15) 80%, rgba(255,255,255,0.45) 100%);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }
        .liquid-glass::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 1.4px;
            background: linear-gradient(180deg,
                rgba(255,255,255,0.45) 0%, rgba(255,255,255,0.15) 20%,
                rgba(255,255,255,0) 40%, rgba(255,255,255,0) 60%,
                rgba(255,255,255,0.15) 80%, rgba(255,255,255,0.45) 100%);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        /* Animations */
        @keyframes fade-rise {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-rise { animation: fade-rise 0.8s ease-out both; }
        .animate-fade-rise-delay { animation: fade-rise 0.8s ease-out 0.2s both; }
        .animate-fade-rise-delay-2 { animation: fade-rise 0.8s ease-out 0.4s both; }

        /* 🌠 Hardware-accelerated Shooting Star Trails (Faqat yuqori osmonda, bolalar sathidan balandda/orqada) */
        .shooting-star-layer {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 44%;
            overflow: hidden;
            pointer-events: none;
            z-index: 1;
            -webkit-mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 0%, rgba(0,0,0,1) 55%, rgba(0,0,0,0) 100%);
            mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 0%, rgba(0,0,0,1) 55%, rgba(0,0,0,0) 100%);
        }
        .shooting-star-trail {
            position: absolute;
            height: 2px;
            border-radius: 9999px;
            background: linear-gradient(90deg, 
                rgba(255, 255, 255, 0) 0%, 
                rgba(147, 197, 253, 0.45) 40%, 
                rgba(255, 255, 255, 0.95) 88%, 
                #ffffff 100%
            );
            filter: drop-shadow(0 0 6px rgba(255, 255, 255, 0.9)) drop-shadow(0 0 12px rgba(56, 189, 248, 0.8));
            transform-origin: right center;
            pointer-events: none;
            will-change: transform, opacity;
            opacity: 0;
        }
        .shooting-star-trail::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: #ffffff;
            box-shadow: 0 0 8px 2px #ffffff, 0 0 16px 4px #38bdf8;
        }

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

        /* ── StPageFlip (Nodlik/StPageFlip) Core & Realistic Book Styling ── */
        .scrolly-book-stage {
            position: relative;
            user-select: none;
            -webkit-user-select: none;
            touch-action: pan-y;
            transition: transform 0.65s cubic-bezier(0.22, 1, 0.36, 1);
            will-change: transform;
        }
        .stf__parent {
            position: relative;
            display: block;
            box-sizing: border-box;
            transform: translateZ(0);
            -ms-touch-action: pan-y;
            touch-action: pan-y;
        }
        .stf__wrapper,
        .sft__wrapper {
            position: relative;
            width: 100%;
            box-sizing: border-box;
        }
        .stf__parent canvas {
            position: absolute;
            width: 100%;
            height: 100%;
            left: 0;
            top: 0;
        }
        .stf__block {
            position: absolute;
            width: 100%;
            height: 100%;
            left: 0;
            top: 0;
            box-sizing: border-box;
            perspective: 2000px;
            -webkit-perspective: 2000px;
        }
        .stf__item {
            display: none;
            position: absolute;
            transform-style: preserve-3d;
            -webkit-transform-style: preserve-3d;
            box-sizing: border-box;
            overflow: hidden;
            cursor: pointer;
        }
        .stf__outerShadow,
        .stf__innerShadow,
        .stf__hardShadow,
        .stf__hardInnerShadow {
            position: absolute;
            left: 0;
            top: 0;
            pointer-events: none;
        }
        /* Paper Pages & Hardcover Styling for StPageFlip */
        .stf-book-page {
            background-color: #FAF7F0;
            color: #1A1D24;
            border: 1px solid #D9D2C5;
            box-sizing: border-box;
            overflow: hidden;
        }
        .stf-book-page.--left {
            border-right: 0;
            border-radius: 5px 0 0 5px;
            box-shadow:
                inset -14px 0 26px -8px rgba(18, 14, 10, 0.36),
                inset 3px 0 0 #E5DFD5,
                inset 5px 0 0 #CFC7B8,
                -6px 8px 20px rgba(0, 0, 0, 0.35);
        }
        .stf-book-page.--right {
            border-left: 0;
            border-radius: 0 5px 5px 0;
            box-shadow:
                inset 14px 0 26px -8px rgba(18, 14, 10, 0.36),
                inset -3px 0 0 #E5DFD5,
                inset -5px 0 0 #CFC7B8,
                6px 8px 20px rgba(0, 0, 0, 0.35);
        }
        .stf-book-page.stf-page-cover {
            background-color: #0F1420;
            border: 1px solid rgba(212, 175, 55, 0.38);
            border-radius: 3px 8px 8px 3px;
            box-shadow:
                inset 8px 0 16px rgba(0, 0, 0, 0.65),
                inset -2px 0 5px rgba(245, 158, 11, 0.25),
                8px 12px 26px rgba(0, 0, 0, 0.6);
        }
        .stf-book-page.stf-page-forzats {
            background-color: #F5EFE6;
            border-radius: 8px 0 0 8px;
            box-shadow:
                inset -14px 0 26px -8px rgba(18, 14, 10, 0.38),
                inset 0 0 0 3px #181412,
                inset 0 0 0 4px rgba(212, 175, 55, 0.45),
                -6px 10px 24px rgba(0, 0, 0, 0.48);
        }
    </style>
@include('components.pwa-head')
</head>
<body class="bg-ink-950 text-paper font-sans selection:bg-amber-500 selection:text-ink-950 antialiased min-h-screen relative ks-grain w-full max-w-full">

    <!-- ── Page Transition & Loader ── -->
    @include('components.page-loader')

    <div id="smooth-page-wrapper" class="w-full max-w-full overflow-x-clip">

    <!-- ── Universal Platform Header ── -->
    <x-nav.main-header />

    <!-- ── 1. KITOBXON CINEMATIC FULLSCREEN VIDEO HERO SECTION ── -->
    <section class="relative w-full min-h-screen -mt-16 flex flex-col justify-between overflow-hidden bg-[hsl(var(--background))] text-[hsl(var(--foreground))] select-none max-w-full">
        
        <!-- Fullscreen Looping Video Background -->
        <video 
            autoplay 
            loop 
            muted 
            playsinline 
            class="absolute inset-0 w-full h-full object-cover z-0 pointer-events-none">
            <source src="https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260314_131748_f2ca2a28-fed7-44c8-b9a9-bd9acdd5ec31.mp4" type="video/mp4">
        </video>

        <!-- 🌠 Cinematic Shooting Stars Layer (60FPS Silky-Smooth Physics) -->
        <div id="shooting-stars-field" class="shooting-star-layer">
            <div class="shooting-star-trail" id="star-meteor-1"></div>
            <div class="shooting-star-trail" id="star-meteor-2"></div>
        </div>

        <!-- Centered Cinematic Hero Section Content -->
        <div class="relative z-10 flex flex-col items-center justify-center text-center px-4 sm:px-6 pt-24 sm:pt-32 pb-20 sm:pb-28 max-w-7xl mx-auto w-full flex-1 overflow-hidden">
            
            <!-- Eyebrow Pill Badge -->
            <div class="hero-anim-item animate-fade-rise inline-flex items-center gap-2.5 px-3.5 sm:px-4 py-1.5 rounded-full liquid-glass text-[10.5px] sm:text-xs text-amber-300 font-mono tracking-wider mb-6 max-w-full font-semibold">
                <span class="relative flex h-2 w-2 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                </span>
                <span class="break-words">HAR HAFTA BITTA SARA ASAR VA CHUQUR MUTOLAA</span>
            </div>

            <!-- H1 Cinematic Headline -->
            <h1 class="hero-anim-item animate-fade-rise text-4xl sm:text-6xl md:text-7xl lg:text-8xl leading-[0.98] sm:leading-[0.95] tracking-tight sm:tracking-[-2.46px] max-w-7xl font-normal text-white break-words"
                style="font-family: 'Instrument Serif', serif;">
                Sahifalar aro <em class="not-italic text-amber-300">orzular</em> va <em class="not-italic text-white/70">teran tafakkur yuksaladi.</em>
            </h1>

            <!-- Subtext -->
            <p class="hero-anim-item animate-fade-rise-delay text-white/75 text-base sm:text-lg max-w-2xl mt-8 leading-relaxed font-normal"
               style="font-family: var(--font-body, 'Inter', sans-serif);">
                Chalg'ituvchi shovqinlar orasida — chuqur mutolaa, 3D interaktiv varaqlash va ilhom maskani. Sara jahon hamda o'zbek adabiyoti, audio asarlar va intellektual kitobxonlar ekotizimi.
            </p>

            <!-- Hero CTA Buttons -->
            <div class="hero-anim-item animate-fade-rise-delay-2 mt-12 flex flex-col sm:flex-row items-center justify-center gap-4 w-full sm:w-auto">
                <a href="{{ auth()->check() ? route('books.catalog') : route('register') }}"
                   class="group relative inline-flex items-center justify-center gap-3 px-8 sm:px-10 py-4 rounded-full bg-gradient-to-r from-amber-500 via-amber-400 to-yellow-400 text-ink-950 font-bold text-sm sm:text-base shadow-[0_0_30px_rgba(245,158,11,0.45),inset_0_1px_1px_rgba(255,255,255,0.6)] border border-amber-300/80 hover:scale-[1.03] active:scale-[0.98] transition-all duration-300 w-full sm:w-auto"
                   style="font-family: var(--font-body, 'Inter', sans-serif);">
                    <span>Mutolaani boshlash</span>
                    <span class="w-7 h-7 rounded-full bg-ink-950/15 flex items-center justify-center group-hover:translate-x-1 transition-transform duration-200">
                        <svg class="w-3.5 h-3.5 text-ink-950" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                </a>
                <a href="#book-reveal-section"
                   class="inline-flex items-center justify-center gap-2.5 px-8 sm:px-10 py-4 rounded-full bg-black/45 hover:bg-black/60 text-white/90 hover:text-white font-medium text-sm sm:text-base backdrop-blur-md border border-white/20 hover:border-amber-400/50 shadow-lg shadow-black/40 hover:scale-[1.03] active:scale-[0.98] transition-all duration-300 w-full sm:w-auto"
                   style="font-family: var(--font-body, 'Inter', sans-serif);">
                    <span>📖 3D Kitobni ochish</span>
                </a>
            </div>

            <!-- Micro-features Pill Bar -->
            <div class="hero-anim-item animate-fade-rise-delay-2 mt-12 flex flex-wrap items-center justify-center gap-2.5 sm:gap-4 text-xs font-mono">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-black/45 backdrop-blur-md border border-white/15 text-white/90 shadow-lg shadow-black/40 hover:border-amber-400/40 transition-colors">
                    <span class="text-sm">📚</span>
                    <span class="font-bold text-amber-300 font-mono">{{ $booksCount ?? 1000 }}+</span>
                    <span class="text-white/90 font-medium">{{ __('site.home.stat_books') }}</span>
                </span>
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-black/45 backdrop-blur-md border border-white/15 text-white/90 shadow-lg shadow-black/40 hover:border-amber-400/40 transition-colors">
                    <span class="text-sm">🎧</span>
                    <span class="text-white/90 font-medium">Audio mutolaa</span>
                </span>
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-black/45 backdrop-blur-md border border-white/15 text-white/90 shadow-lg shadow-black/40 hover:border-amber-400/40 transition-colors">
                    <span class="text-sm">🔥</span>
                    <span class="text-white/90 font-medium">Kunlik streak va ballar</span>
                </span>
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-black/45 backdrop-blur-md border border-white/15 text-white/90 shadow-lg shadow-black/40 hover:border-amber-400/40 transition-colors">
                    <span class="text-sm">👥</span>
                    <span class="font-bold text-amber-300 font-mono">{{ $usersCount ?? 1200 }}+</span>
                    <span class="text-white/90 font-medium">{{ __('site.home.active_readers') }}</span>
                </span>
            </div>

        </div>

        <!-- Bottom scroll cue -->
        <div class="relative z-10 pb-6 w-full flex justify-center">
            <a href="#book-reveal-section" class="inline-flex flex-col items-center gap-1.5 text-[11px] font-mono tracking-widest uppercase text-white/50 hover:text-amber-400 transition-colors cursor-pointer group">
                <span>Varaqlab o'qish</span>
                <svg class="w-4 h-4 animate-bounce text-amber-400/80 group-hover:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
            </a>
        </div>

    </section>

    <!-- ── 2. SCROLLYTELLING BOOK REVEAL SECTION (APPLE-GRADE PINNED SCROLL) ── -->
    @php
        $scrollyBook = $featuredBook ?? null;

        // Filter out short or placeholder dummy text for title, author, and genre
        $cleanTitle = trim((string)($scrollyBook->title ?? ''));
        $isDummyTitle = strlen($cleanTitle) < 3 || preg_match('/^(fds|asdf|test|lorem)/i', $cleanTitle);
        $scrollyTitle = (!$isDummyTitle && !empty($cleanTitle)) ? $cleanTitle : "O'tkan Kunlar";

        $cleanAuthor = trim((string)($scrollyBook->author ?? ''));
        $isDummyAuthor = strlen($cleanAuthor) < 3 || preg_match('/^(fds|asdf|test|lorem)/i', $cleanAuthor);
        $scrollyAuthor = (!$isDummyAuthor && !empty($cleanAuthor)) ? $cleanAuthor : "Abdulla Qodiriy";

        $cleanGenre = trim((string)($scrollyBook->genre ?? ''));
        $isDummyGenre = strlen($cleanGenre) < 3 || preg_match('/^(fds|asdf|test|lorem)/i', $cleanGenre);
        $scrollyGenre = (!$isDummyGenre && !empty($cleanGenre)) ? $cleanGenre : "Tarixiy Roman";

        // Filter out short or placeholder dummy text for description
        $cleanDesc = trim(strip_tags((string)($scrollyBook->description ?? '')));
        $isDummyDesc = strlen($cleanDesc) < 40 || preg_match('/^(fds|asdf|test|lorem)/i', $cleanDesc);

        $scrollyDesc = (!$isDummyDesc && !empty($cleanDesc))
            ? \Illuminate\Support\Str::limit($cleanDesc, 280) 
            : "O'zbek adabiyotining shoh asari bo'lmish ushbu kitobda inson qadri, muhabbat va vatan taqdiri teran falsafiy nigoh bilan yoritilgan. Har bir bobida chuqur ma'no va qalbni larzaga soluvchi tuyg'ular mujassam.";

        // Realistic book excerpt for printed page with drop cap
        $scrollyPageExcerpt = (!$isDummyDesc && !empty($cleanDesc))
            ? \Illuminate\Support\Str::limit($cleanDesc, 220)
            : "Har bir buyuk asar inson qalbining eng yashirin torlarini chertadi. Sahifalar varaqlangani sari, so'zlar jonlanib, qahramonlarning quvonch va iztiroblari kitobxon vujudiga singib boradi. Bu sahifalarda bitilgan har bir satr sizni bepoyon tafakkur sayohatiga chorlaydi.";

        $scrollyLink = $scrollyBook ? route('books.show', $scrollyBook->slug) : route('books.public');
        $scrollyReaders = $featuredReadersCount ?? ($scrollyBook ? 48 : 24);
        $scrollyChapters = $featuredChaptersCount ?? ($scrollyBook ? 18 : 12);
        $scrollyAudio = $featuredAudioMinutes ?? 35;
        $scrollyCover = $scrollyBook->cover_url ?? null;
    @endphp

    <section id="book-reveal-section" class="relative w-full min-h-screen bg-ink-950 border-b border-ink-border flex items-center justify-center py-16 lg:py-0 overflow-x-clip max-w-full">
        <!-- Atmospheric Ambient Spotlight behind the book -->
        <div class="scrolly-ambient-glow absolute inset-0 pointer-events-none flex items-center justify-center opacity-60 overflow-hidden" aria-hidden="true">
            <div class="w-[500px] lg:w-[650px] h-[500px] lg:h-[650px] rounded-full bg-gradient-radial from-amber-500/15 via-vermilion/5 to-transparent blur-3xl max-w-full"></div>
        </div>

        <!-- Inner Centered Container -->
        <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 relative z-10 overflow-x-clip lg:overflow-visible">
            <!-- Eyebrow Bar: Chapter / Week Indicator -->
            <div class="scrolly-eyebrow-bar mb-6 lg:mb-10 flex flex-wrap items-center justify-between gap-4 border-b border-ink-border/50 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 dark:bg-amber-400 animate-pulse"></span>
                    <span class="font-mono text-xs uppercase tracking-widest text-amber-600 dark:text-amber-400 font-bold">
                        {{ $scrollyBook ? ($scrollyBook->week_number . '-HAFTA MUTOLAASI') : 'HAFTANING ASOSIY KITOBI' }}
                    </span>
                    <span class="text-mist/40">|</span>
                    <span class="font-mono text-[11px] text-mist tracking-wider uppercase">
                        Scrollytelling Tajribasi
                    </span>
                </div>
                <div class="hidden lg:flex items-center gap-2 font-mono text-[11px] text-mist">
                    <span>Scroll qiling va kitob ochilishini kuzating</span>
                    <svg class="w-3.5 h-3.5 animate-bounce text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
            </div>

            <!-- Stage Grid: 3D Book on Left (col-span-6) + Revealed Story on Right (col-span-6) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                
                <!-- LEFT (cols 1-6): The Grand 3D Book Stage (Multi-Leaf 3D Interactive Flip) -->
                <div class="lg:col-span-6 flex flex-col justify-center lg:justify-end lg:pr-8 items-center max-w-full">
                    <div class="scrolly-book-stage relative w-[310px] sm:w-[440px] lg:w-[540px] xl:w-[580px] max-w-[94vw] select-none" id="scrolly-book-stage" style="transform: translateX(-25%);">
                        
                        <!-- Floating Drag/Swipe Gesture Hint Pill -->
                        <div id="scrolly-drag-hint"
                             class="absolute -top-11 left-1/2 -translate-x-1/2 pointer-events-none z-50 transition-all duration-500 flex items-center gap-1.5 px-3 py-1 rounded-full bg-ink-950/95 border border-amber-500/40 dark:border-amber-400/40 text-[10px] sm:text-[10.5px] font-mono text-amber-700 dark:text-amber-300 shadow-2xl backdrop-blur-md max-w-[90vw] whitespace-nowrap overflow-hidden">
                            <span class="inline-block animate-[bounce_1.2s_infinite]">👈</span>
                            <span>Burchakdan tortib yoki bosib varaqlang</span>
                            <span class="inline-block animate-[bounce_1.2s_infinite]">👉</span>
                        </div>

                        <!-- Dynamic Floor Shadow underneath book -->
                        <div class="scrolly-book-shadow absolute -bottom-6 left-[52%] right-[2%] h-7 rounded-full bg-black/80 blur-xl transition-all duration-500 pointer-events-none"></div>

                        <!-- StPageFlip Host Container -->
                        <div id="scrolly-flipbook-host" class="w-full relative z-10">
                            <div id="scrolly-flipbook" class="w-full">

                                <!-- ══════════════════════════════════════════════════════════════════ -->
                                <!-- PAGE 0 (Spread 0 Right): FRONT HARDCOVER (Muqova)                 -->
                                <!-- ══════════════════════════════════════════════════════════════════ -->
                                <div class="stf-book-page stf-page-cover" data-density="hard">
                                    <div class="relative w-full h-full overflow-hidden">
                                        @if($scrollyCover)
                                            <img src="{{ $scrollyCover }}" alt="{{ $scrollyTitle }}" class="w-full h-full object-cover pointer-events-none">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-ink-900 via-ink-800 to-ink-950 p-5 sm:p-6 flex flex-col justify-between border border-amber-500/20">
                                                <div>
                                                    <span class="font-mono text-[10px] text-amber-400 tracking-wider uppercase block">Kitobxon Exclusive</span>
                                                    <h3 class="font-serif text-base sm:text-lg font-bold text-paper mt-2">{{ $scrollyTitle }}</h3>
                                                    <p class="text-xs text-mist font-sans mt-1">{{ $scrollyAuthor }}</p>
                                                </div>
                                                <div class="pt-4 border-t border-ink-border flex justify-between items-center text-xs font-mono text-amber-400">
                                                    <span>Hafta Kitobi</span>
                                                    <span>✦</span>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="absolute inset-y-0 left-0 w-8 pointer-events-none bg-gradient-to-r from-black/70 via-black/25 to-transparent"></div>
                                        <div class="absolute inset-0 pointer-events-none ring-1 ring-inset ring-white/15"></div>
                                    </div>
                                </div>

                                <!-- ══════════════════════════════════════════════════════════════════ -->
                                <!-- PAGE 1 (Spread 1 Left): INSIDE FRONT COVER (Forzats)              -->
                                <!-- ══════════════════════════════════════════════════════════════════ -->
                                <div class="stf-book-page stf-page-forzats" data-density="hard">
                                    <div class="relative w-full h-full p-3 sm:p-4 flex flex-col justify-between text-ink-950 overflow-hidden">
                                        <!-- Vintage Double Inner Border Frame -->
                                        <div class="absolute inset-2 pointer-events-none border border-[#C5BBA8]/50 rounded-[2px]"></div>
                                        <div class="absolute inset-2.5 pointer-events-none border border-[#C5BBA8]/30 rounded-[1px]"></div>

                                        <!-- Top Header -->
                                        <div class="relative z-10 border-b border-[#D5CBB9] pb-1.5 text-center">
                                            <span class="font-mono text-[7px] sm:text-[8px] uppercase tracking-[0.2em] text-[#8B9BAD] block">Nodir Nusxalar Kolleksiyasi</span>
                                            <span class="font-serif text-[10px] sm:text-[11px] text-[#1A1D24] font-semibold tracking-wide mt-0.5 block">KITOBXON KUTUBXONASI</span>
                                        </div>

                                        <!-- Center: Ex-Libris Seal & Quote -->
                                        <div class="relative z-10 my-auto py-1 text-center space-y-1.5">
                                            <div class="inline-flex flex-col items-center justify-center p-1.5 sm:p-2 rounded-full border border-amber-700/30 bg-amber-500/5 mx-auto">
                                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                                                    <circle cx="12" cy="9" r="2"/>
                                                    <path d="M9 14h6"/>
                                                </svg>
                                                <span class="font-mono text-[6.5px] uppercase tracking-widest text-amber-800 font-bold mt-0.5">EX LIBRIS</span>
                                            </div>
                                            <blockquote class="font-serif italic text-[9px] sm:text-[10px] text-[#2C3440] leading-relaxed px-1">
                                                «Kitob — zamonlar to'lqinida suzuvchi va o'zining qimmatbaho yukini avlodlarga eltuvchi hikmat kemasidir.»
                                            </blockquote>
                                            <div class="w-8 h-px bg-amber-700/30 mx-auto"></div>
                                        </div>

                                        <!-- Bottom Metadata -->
                                        <div class="relative z-10 border-t border-[#D5CBB9] pt-1.5 space-y-0.5 text-[7.5px] sm:text-[8px] font-mono text-[#6E7B8B]">
                                            <div class="flex items-center justify-between">
                                                <span>NASHR:</span>
                                                <span class="text-[#1A1D24] font-semibold">№ 084 · HAFTALIK</span>
                                            </div>
                                            <div class="flex items-center justify-between text-[7px] text-[#8B9BAD]">
                                                <span>✦ ASLIYATGA MOS</span>
                                                <span>TOSHKENT · 2026 ✦</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ══════════════════════════════════════════════════════════════════ -->
                                <!-- PAGE 2 (Spread 1 Right): 1-BET (Birinchi Bob — Soft Curling Page) -->
                                <!-- ══════════════════════════════════════════════════════════════════ -->
                                <div class="stf-book-page" data-density="soft">
                                    <div class="relative w-full h-full p-3 sm:p-5 flex flex-col justify-between text-ink-950 overflow-hidden">
                                        <!-- Header Bar -->
                                        <div class="border-b border-[#D9D2C5]/80 pb-1.5 flex items-center justify-between text-[8px] sm:text-[9px] font-mono tracking-widest text-[#8B9BAD] uppercase">
                                            <span class="truncate max-w-[90px] sm:max-w-[130px] font-semibold text-[#526071]">{{ $scrollyTitle }}</span>
                                            <span class="text-amber-700/70 font-serif text-xs">❦</span>
                                            <span>BOB I · 1</span>
                                        </div>

                                        <!-- Chapter Heading & Body with Drop Cap -->
                                        <div class="space-y-1.5 py-1 my-auto">
                                            <div class="text-center pb-0.5">
                                                <span class="font-mono text-[7.5px] sm:text-[8.5px] tracking-[0.2em] text-[#8B9BAD] uppercase block">Birinchi Bob</span>
                                                <h4 class="font-serif italic text-[11px] sm:text-xs font-semibold text-[#1A1D24] mt-0.5">«Ibtido va Tafakkur»</h4>
                                                <div class="w-10 h-px bg-gradient-to-r from-transparent via-amber-600/40 to-transparent mx-auto mt-1"></div>
                                            </div>

                                            <p class="font-serif italic text-[8.5px] sm:text-[9.5px] text-[#526071] leading-relaxed border-l-2 border-amber-600/40 pl-2 text-left">
                                                «Tafakkur qilgan inson uchun har bir sahifada butun bir olam yashiringan...»
                                            </p>

                                            <div class="font-serif text-[9px] sm:text-[10.5px] text-[#1A1D24] leading-[1.55] sm:leading-[1.65] text-justify pt-0.5">
                                                <span class="float-left text-2xl sm:text-3xl font-serif font-bold text-amber-700 leading-none mr-1 mt-0.5 select-none">{{ mb_substr($scrollyPageExcerpt, 0, 1) }}</span><span class="inline">{{ mb_substr($scrollyPageExcerpt, 1) }}</span>
                                            </div>

                                            <p class="font-serif text-[9.5px] text-[#4A5568] leading-relaxed pt-0.5 text-justify hidden sm:block">
                                                Mutolaa — qalbni yorituvchi nur, ruhni yuksaltiruvchi qanotdir.
                                            </p>
                                        </div>

                                        <!-- Running Footer -->
                                        <div class="border-t border-[#D9D2C5]/80 pt-1.5 flex items-center justify-between text-[8px] sm:text-[9px] font-mono text-[#8B9BAD]">
                                            <span>Kitobxon</span>
                                            <span class="font-serif italic text-[#526071] font-semibold">— 1 —</span>
                                            <span class="text-amber-700 font-semibold">2-bet ➔</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- ══════════════════════════════════════════════════════════════════ -->
                                <!-- PAGE 3 (Spread 2 Left): 1-BET VERSO (Muqaddima — Soft Page)       -->
                                <!-- ══════════════════════════════════════════════════════════════════ -->
                                <div class="stf-book-page" data-density="soft">
                                    <div class="relative w-full h-full p-3 sm:p-5 flex flex-col justify-between bg-[#F5EFE6] text-ink-950 overflow-hidden">
                                        <div class="border-b border-[#D5CBB9] pb-1.5 text-center">
                                            <span class="font-mono text-[7.5px] sm:text-[8px] uppercase tracking-[0.2em] text-[#8B9BAD] block">Muqaddima</span>
                                            <span class="font-serif text-[10px] sm:text-[11px] text-[#1A1D24] font-semibold">«Kitob Falsafasi»</span>
                                        </div>
                                        <div class="my-auto py-2 text-center space-y-2">
                                            <blockquote class="font-serif italic text-[9px] sm:text-[10px] text-[#2C3440] leading-relaxed px-1 sm:px-2">
                                                «So'z — qalb ko'zgusi, kitob esa butun insoniyatning bebaho xazinasidir.»
                                            </blockquote>
                                            <div class="w-8 h-px bg-amber-700/30 mx-auto"></div>
                                            <p class="font-serif text-[8.5px] sm:text-[9.5px] text-[#526071] leading-relaxed px-1 hidden sm:block">
                                                Asar davomida inson irodasi, sabr va ma'rifatning ulug'vor kuchi badiiy mahorat bilan tasvirlanadi.
                                            </p>
                                        </div>
                                        <div class="border-t border-[#D5CBB9] pt-1.5 flex justify-between text-[7.5px] sm:text-[8px] font-mono text-[#6E7B8B]">
                                            <span>BOB I XULOSASI</span>
                                            <span>№ {{ $scrollyBook->week_number ?? 1 }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- ══════════════════════════════════════════════════════════════════ -->
                                <!-- PAGE 4 (Spread 2 Right): 2-BET (Ikkinchi Bob — Soft Curling Page) -->
                                <!-- ══════════════════════════════════════════════════════════════════ -->
                                <div class="stf-book-page" data-density="soft">
                                    <div class="relative w-full h-full p-3 sm:p-5 flex flex-col justify-between text-ink-950 overflow-hidden">
                                        <!-- Header -->
                                        <div class="border-b border-[#D9D2C5]/80 pb-1.5 flex items-center justify-between text-[8px] sm:text-[9px] font-mono tracking-widest text-[#8B9BAD] uppercase">
                                            <span class="truncate max-w-[90px] sm:max-w-[130px] font-semibold text-[#526071]">{{ $scrollyTitle }}</span>
                                            <span class="text-amber-700/70 font-serif text-xs">❦</span>
                                            <span>BOB II · 2</span>
                                        </div>

                                        <!-- Body -->
                                        <div class="space-y-1.5 py-1 my-auto">
                                            <div class="text-center pb-0.5">
                                                <span class="font-mono text-[7.5px] sm:text-[8.5px] tracking-[0.2em] text-[#8B9BAD] uppercase block">Ikkinchi Bob</span>
                                                <h4 class="font-serif italic text-[11px] sm:text-xs font-semibold text-[#1A1D24] mt-0.5">«Marg'ilon Yo'lida»</h4>
                                                <div class="w-10 h-px bg-gradient-to-r from-transparent via-amber-600/40 to-transparent mx-auto mt-1"></div>
                                            </div>

                                            <p class="font-serif italic text-[8.5px] sm:text-[9.5px] text-[#526071] leading-relaxed border-l-2 border-amber-600/40 pl-2 text-left">
                                                «Qalb istagan manzilga yetmoq uchun yo'l mashaqqatlariga chidamoq darkor...»
                                            </p>

                                            <div class="font-serif text-[9px] sm:text-[10.5px] text-[#1A1D24] leading-[1.55] sm:leading-[1.65] text-justify pt-0.5">
                                                Otabek Marg'ilonga yaqinlashar ekan, uning qalbida noma'lum bir orziqish uyg'ongan edi. Oqshom shafag'i ostida shahar devorlari sirli tus olib, har bir ko'cha o'tmish hikoyalaridan so'zlayotgandek tuyulardi.
                                            </div>

                                            <p class="font-serif text-[9.5px] text-[#4A5568] leading-relaxed pt-0.5 text-justify hidden sm:block">
                                                Taqdirning sirli burilishlari va buyuk muhabbat sinovlari bu sahifalarda o'z aksini topgan.
                                            </p>
                                        </div>

                                        <!-- Footer -->
                                        <div class="border-t border-[#D9D2C5]/80 pt-1.5 flex items-center justify-between text-[8px] sm:text-[9px] font-mono text-[#8B9BAD]">
                                            <span>Kitobxon</span>
                                            <span class="font-serif italic text-[#526071] font-semibold">— 2 —</span>
                                            <span class="text-amber-700 font-semibold">3-bet ➔</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- ══════════════════════════════════════════════════════════════════ -->
                                <!-- PAGE 5 (Spread 3 Left): 2-BET VERSO (Adabiy Sharh — Soft Page)    -->
                                <!-- ══════════════════════════════════════════════════════════════════ -->
                                <div class="stf-book-page" data-density="soft">
                                    <div class="relative w-full h-full p-3 sm:p-5 flex flex-col justify-between bg-[#F5EFE6] text-ink-950 overflow-hidden">
                                        <div class="border-b border-[#D5CBB9] pb-1.5 text-center">
                                            <span class="font-mono text-[7.5px] sm:text-[8px] uppercase tracking-[0.2em] text-[#8B9BAD] block">Adabiy Sharh</span>
                                            <span class="font-serif text-[10px] sm:text-[11px] text-[#1A1D24] font-semibold">«Qahramonlar Qalbi»</span>
                                        </div>
                                        <div class="my-auto py-2 text-center space-y-2">
                                            <blockquote class="font-serif italic text-[9px] sm:text-[10px] text-[#2C3440] leading-relaxed px-1 sm:px-2">
                                                «Har bir qahramonning o'z haqiqati bor. Lekin eng oliy haqiqat — bu sadoqat va ma'rifatdir.»
                                            </blockquote>
                                            <div class="w-8 h-px bg-amber-700/30 mx-auto"></div>
                                        </div>
                                        <div class="border-t border-[#D5CBB9] pt-1.5 flex justify-between text-[7.5px] sm:text-[8px] font-mono text-[#6E7B8B]">
                                            <span>BOB II TAHLILI</span>
                                            <span>№ {{ $scrollyBook->week_number ?? 1 }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- ══════════════════════════════════════════════════════════════════ -->
                                <!-- PAGE 6 (Spread 3 Right): 3-BET (Xotima & Reader Link — Soft Page) -->
                                <!-- ══════════════════════════════════════════════════════════════════ -->
                                <div class="stf-book-page" data-density="soft">
                                    <div class="relative w-full h-full p-3 sm:p-5 flex flex-col justify-between text-ink-950 overflow-hidden">
                                        <!-- Header Bar -->
                                        <div class="border-b border-[#D9D2C5]/80 pb-1.5 flex items-center justify-between text-[8px] sm:text-[9px] font-mono tracking-widest text-[#8B9BAD] uppercase">
                                            <span class="truncate max-w-[90px] sm:max-w-[130px] font-semibold text-[#526071]">{{ $scrollyTitle }}</span>
                                            <span class="text-amber-700/70 font-serif text-xs">❦</span>
                                            <span>BOB III · 3</span>
                                        </div>

                                        <!-- Chapter 3 Body & Reader Callout -->
                                        <div class="space-y-1.5 py-1 my-auto">
                                            <div class="text-center pb-0.5">
                                                <span class="font-mono text-[7.5px] sm:text-[8.5px] tracking-[0.2em] text-[#8B9BAD] uppercase block">Uchinchi Bob</span>
                                                <h4 class="font-serif italic text-[11px] sm:text-xs font-semibold text-[#1A1D24] mt-0.5">«Xotima va Hikmat»</h4>
                                                <div class="w-10 h-px bg-gradient-to-r from-transparent via-amber-600/40 to-transparent mx-auto mt-1"></div>
                                            </div>

                                            <p class="font-serif italic text-[8.5px] sm:text-[9.5px] text-[#526071] leading-relaxed border-l-2 border-amber-600/40 pl-2 text-left">
                                                «Kitob tugagan joyda inson tafakkurining cheksiz ufqlariga parvoz boshlanadi...»
                                            </p>

                                            <p class="font-serif text-[9px] sm:text-[10px] text-[#1A1D24] leading-[1.5] text-justify pt-0.5">
                                                Ushbu asar qalbni yorituvchi teran hikmatdir. Qahramonlarning har bir qarori bugungi kunimiz uchun ham qimmatli saboq beradi.
                                            </p>

                                            <!-- Direct CTA to Open Full Book Reader -->
                                            <div class="pt-1 space-y-1">
                                                <a href="{{ $scrollyLink }}"
                                                   class="w-full py-1.5 sm:py-2 px-2.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-ink-950 font-serif font-bold text-[10px] sm:text-xs flex items-center justify-center gap-1 shadow-md transition-all hover:scale-[1.02] active:scale-95 group">
                                                    <span>📖 Kitobni to'liq o'qish</span>
                                                    <span class="group-hover:translate-x-1 transition-transform">➔</span>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Running Footer -->
                                        <div class="border-t border-[#D9D2C5]/80 pt-1.5 flex items-center justify-between text-[8px] sm:text-[9px] font-mono text-[#8B9BAD]">
                                            <span>Kitobxon</span>
                                            <span class="font-serif italic text-[#526071] font-semibold">— 3 —</span>
                                            <span>№ {{ $scrollyBook->week_number ?? 1 }}</span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <!-- 3D Book Interactive Controls & Page Flip Toolbar -->
                    <div class="scrolly-book-controls flex items-center justify-between gap-2 mt-4 px-1 w-full max-w-[280px] sm:max-w-[340px] lg:max-w-[380px] select-none">
                        <button type="button" id="scrolly-prev-page-btn"
                                class="px-2.5 py-1 rounded-full bg-ink-900 border border-ink-border text-mist hover:text-paper hover:border-amber-400/50 text-xs font-mono transition-colors flex items-center gap-1 active:scale-95 disabled:opacity-30 disabled:pointer-events-none"
                                title="Oldingi bet">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span class="text-[11px]">Oldingi</span>
                        </button>

                        <div class="px-2.5 py-1 rounded-full bg-ink-900/90 border border-ink-border flex items-center gap-2 shadow-sm">
                            <span id="scrolly-page-label" class="font-mono text-[11px] text-amber-600 dark:text-amber-400 font-semibold tracking-wider whitespace-nowrap shrink-0">Muqova</span>
                            <div class="flex items-center gap-1">
                                <span class="scrolly-dot w-1.5 h-1.5 rounded-full bg-amber-500 dark:bg-amber-400 transition-all cursor-pointer" data-page="0" title="Muqova"></span>
                                <span class="scrolly-dot w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-ink-700 transition-all cursor-pointer" data-page="1" title="1-bet"></span>
                                <span class="scrolly-dot w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-ink-700 transition-all cursor-pointer" data-page="2" title="2-bet"></span>
                                <span class="scrolly-dot w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-ink-700 transition-all cursor-pointer" data-page="3" title="3-bet (Xotima)"></span>
                            </div>
                        </div>

                        <button type="button" id="scrolly-next-page-btn"
                                class="px-2.5 py-1 rounded-full bg-ink-900 border border-ink-border text-mist hover:text-paper hover:border-amber-400/50 text-xs font-mono transition-colors flex items-center gap-1 active:scale-95"
                                title="Keyingi bet">
                            <span class="text-[11px]">Keyingi</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>

                </div>

                <!-- RIGHT (cols 7-12): Revealed Content & Scrolly Narrative Panel -->
                <div class="lg:col-span-6 scrolly-content-panel space-y-6 min-w-0">
                    
                    <!-- Badge & Week Tag -->
                    <div class="scrolly-content-item inline-flex items-center gap-2 px-3 py-1 rounded-badge bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400 font-mono text-xs tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 dark:bg-amber-400"></span>
                        <span class="uppercase">{{ $scrollyBook ? ($scrollyBook->week_number . '-HAFTA TANLOVI') : 'MAXSUS NASHR' }}</span>
                    </div>

                    <!-- Book Title & Author -->
                    <div class="scrolly-content-item space-y-2">
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold font-serif text-paper leading-[1.2] tracking-tight break-words">
                            {{ $scrollyTitle }}
                        </h2>
                        <div class="text-base sm:text-lg text-amber-600 dark:text-amber-400 font-sans flex flex-wrap items-center gap-2 break-words">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <p class="scrolly-content-item text-sm sm:text-base text-mist leading-relaxed font-sans max-w-2xl break-words whitespace-pre-line">
                        {{ $scrollyDesc }}
                    </p>

                    <!-- Actions: Read CTA + Audio / Details + Share -->
                    <div class="scrolly-content-item flex flex-wrap items-center gap-3 pt-1">
                        <a href="{{ $scrollyLink }}" class="ks-btn-primary inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold">
                            <span>Mutolaani boshlash</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>

                        @php
                            $homeFirstAudio = $scrollyBook ? $scrollyBook->audios->first() : null;
                        @endphp
                        @if($homeFirstAudio)
                            <button type="button"
                                    onclick="window.playGlobalAudio({
                                        id: {{ $homeFirstAudio->id }},
                                        bookId: {{ $scrollyBook->id }},
                                        title: '{{ addslashes($scrollyTitle) }}',
                                        author: '{{ addslashes($scrollyAuthor) }}',
                                        coverUrl: '{{ $scrollyCover }}',
                                        audioUrl: '{{ $homeFirstAudio->file_url }}',
                                        chapterTitle: '{{ addslashes($homeFirstAudio->title ?: '1-qism') }}',
                                        duration: {{ (int) ($homeFirstAudio->duration ?? 0) }},
                                        shareUrl: '{{ $scrollyLink }}'
                                    })"
                                    class="px-4 py-2.5 rounded-btn bg-amber-400 hover:bg-amber-300 text-ink-950 font-bold text-sm inline-flex items-center gap-2 shadow-md transition-all active:scale-95"
                                    title="Pastki audio pleyerda tinglash">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                                <span>Tinglash</span>
                            </button>
                        @endif

                        <!-- Share Button -->
                        <button type="button"
                                onclick="window.openBookShare({
                                    title: '{{ addslashes($scrollyTitle) }}',
                                    author: '{{ addslashes($scrollyAuthor) }}',
                                    url: '{{ $scrollyLink }}',
                                    coverUrl: '{{ $scrollyCover }}',
                                    description: '{{ addslashes(\Illuminate\Support\Str::limit($scrollyDesc, 150)) }}'
                                })"
                                class="p-2.5 rounded-btn bg-ink-900 hover:bg-white/10 border border-ink-border hover:border-amber-400/40 text-mist hover:text-amber-400 transition-all flex items-center justify-center shadow"
                                title="Kitobni ulashish">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="18" cy="5" r="3"/>
                                <circle cx="6" cy="12" r="3"/>
                                <circle cx="18" cy="19" r="3"/>
                                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                            </svg>
                        </button>

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
                                <span class="text-xs text-amber-600 dark:text-amber-400 font-normal">+</span>
                            </p>
                        </div>

                        <!-- Stat 2: Chapters -->
                        <div class="p-3.5 rounded-card bg-ink-900/90 border border-ink-border/80 text-left">
                            <span class="text-[10px] sm:text-[11px] font-mono uppercase tracking-wider text-mist block">Mundarija</span>
                            <p class="font-mono text-xl sm:text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">
                                <span class="scrolly-stat-num" data-target="{{ $scrollyChapters }}">0</span>
                                <span class="text-xs text-mist font-normal">bob</span>
                            </p>
                        </div>

                        <!-- Stat 3: Audio Duration -->
                        <div class="p-3.5 rounded-card bg-ink-900/90 border border-ink-border/80 text-left">
                            <span class="text-[10px] sm:text-[11px] font-mono uppercase tracking-wider text-mist block">Audio Tahlil</span>
                            <p class="font-mono text-xl sm:text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
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
                <span class="flex items-center gap-2"><span class="text-amber-600 dark:text-amber-500 font-bold">✦</span> {{ __("site.home.$tk") }}</span>
            @endforeach
        </div>
    </div>

    <!-- ── JONLI PLATFORMA STATISTIKASI ── -->
    <section class="py-8 border-b border-ink-border bg-ink-950 overflow-hidden max-w-full">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 rounded-card bg-ink-900 border border-ink-border text-center">
                    <p class="text-2xl sm:text-3xl font-bold text-paper font-mono ks-stat">
                        <span class="counter-element" data-target="{{ $usersCount ?? 0 }}">{{ $usersCount ?? 0 }}</span>
                    </p>
                    <p class="text-[11px] text-mist mt-1 uppercase tracking-wider font-mono">{{ __('site.home.stat_readers') }}</p>
                </div>
                <div class="p-4 rounded-card bg-ink-900 border border-ink-border text-center">
                    <p class="text-2xl sm:text-3xl font-bold text-amber-600 dark:text-amber-400 font-mono ks-stat">
                        <span class="counter-element" data-target="{{ $booksCount ?? 0 }}">{{ $booksCount ?? 0 }}</span>
                    </p>
                    <p class="text-[11px] text-mist mt-1 uppercase tracking-wider font-mono">{{ __('site.home.stat_books') }}</p>
                </div>
                <div class="p-4 rounded-card bg-ink-900 border border-ink-border text-center">
                    <p class="text-2xl sm:text-3xl font-bold text-emerald-600 dark:text-emerald-400 font-mono ks-stat">
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
                            1 => 'border-amber-400/60 dark:border-[#F59E0B]/50 hover:border-amber-500 dark:hover:border-[#F59E0B] bg-gradient-to-b from-amber-500/15 via-ink-900 to-ink-900 dark:from-[#F59E0B]/12 dark:via-ink-900/80 dark:to-ink-950 shadow-sm dark:shadow-[0_4px_20px_rgba(245,158,11,0.12)]',
                            2 => 'border-slate-300 dark:border-[#E2E8F0]/40 hover:border-slate-400 dark:hover:border-[#E2E8F0] bg-gradient-to-b from-slate-200/70 via-ink-900 to-ink-900 dark:from-[#E2E8F0]/8 dark:via-ink-900/80 dark:to-ink-950 shadow-sm dark:shadow-[0_4px_16px_rgba(226,232,240,0.08)]',
                            3 => 'border-amber-700/30 dark:border-[#D97706]/40 hover:border-amber-700 dark:hover:border-[#D97706] bg-gradient-to-b from-amber-700/10 via-ink-900 to-ink-900 dark:from-[#D97706]/8 dark:via-ink-900/80 dark:to-ink-950 shadow-sm dark:shadow-[0_4px_16px_rgba(217,119,6,0.08)]',
                            default => 'border-indigo-300 dark:border-[#6366F1]/30 hover:border-indigo-400 dark:hover:border-[#6366F1] bg-gradient-to-b from-indigo-500/10 via-ink-900 to-ink-900 dark:from-[#6366F1]/6 dark:via-ink-900/80 dark:to-ink-950 shadow-sm dark:shadow-[0_4px_16px_rgba(99,102,241,0.08)]',
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
                            <span class="font-mono text-xs font-bold text-amber-600 dark:text-amber-400">
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
    <section id="features" class="py-20 md:py-28 relative overflow-hidden max-w-full">
        <div class="max-w-7xl mx-auto px-6">
            
            <div class="bento-header max-w-2xl mb-12 space-y-2">
                <span class="ks-eyebrow">{{ __('site.home.features_badge') }}</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-paper font-serif leading-tight">
                    {{ __('site.home.features_title') }} <span class="text-amber-600 dark:text-amber-400 italic">{{ __('site.home.features_title_b') }}</span> {{ __('site.home.features_title_e') }}
                </h2>
                <p class="text-mist text-sm sm:text-base leading-relaxed">
                    {{ __('site.home.features_sub') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-5">

                <!-- Bento 1 (Large - Col 7): Multi-format Reading -->
                <div class="bento-card md:col-span-7 ks-panel p-6 sm:p-8 flex flex-col justify-between bg-ink-900 border border-ink-border">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold font-serif text-paper">{{ __('site.home.bento1_title') }}</h3>
                        <p class="text-mist text-sm max-w-md leading-relaxed">
                            {{ __('site.home.bento1_sub') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-3 gap-3 mt-8 pt-5 border-t border-ink-border">
                        <div class="p-3 rounded-card bg-ink-950 border border-ink-border text-center">
                            <svg class="w-4 h-4 mx-auto text-amber-600 dark:text-amber-400 mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            <p class="text-xs font-semibold text-paper">{{ __('site.home.bento1_ebook') }}</p>
                            <p class="text-[10px] text-mist font-mono">{{ __('site.home.bento1_ebook_sub') }}</p>
                        </div>
                        <div class="p-3 rounded-card bg-ink-950 border border-ink-border text-center">
                            <svg class="w-4 h-4 mx-auto text-amber-600 dark:text-amber-400 mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                            <p class="text-xs font-semibold text-paper">{{ __('site.home.bento1_audio') }}</p>
                            <p class="text-[10px] text-mist font-mono">{{ __('site.home.bento1_audio_sub') }}</p>
                        </div>
                        <div class="p-3 rounded-card bg-ink-950 border border-ink-border text-center">
                            <svg class="w-4 h-4 mx-auto text-amber-600 dark:text-amber-400 mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                            <p class="text-xs font-semibold text-paper">{{ __('site.home.bento1_video') }}</p>
                            <p class="text-[10px] text-mist font-mono">{{ __('site.home.bento1_video_sub') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Bento 2 (Col 5): Streak & Habit -->
                <div class="bento-card md:col-span-5 ks-panel p-6 sm:p-8 flex flex-col justify-between bg-ink-900 border border-ink-border">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-10 h-10 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-500 flex items-center justify-center">
                                <svg class="w-5 h-5 ks-flame is-lit" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c-.5 2.5-2.5 4.5-4 6-2 2-3 4.5-3 7 0 4.4 3.6 8 8 8s8-3.6 8-8c0-3.5-2-6-4-8-.5 2-2 3.5-3 4-1-2.5 0-6.5-2-9z"/></svg>
                            </span>
                            <span class="font-mono text-xs text-amber-700 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-badge border border-amber-500/20 uppercase font-semibold">
                                {{ __('site.home.bento2_tag') }}
                            </span>
                        </div>
                        <h3 class="text-xl font-bold font-serif text-paper">{{ __('site.home.bento2_title') }}</h3>
                        <p class="text-mist text-sm mt-2 leading-relaxed">
                            {{ __('site.home.bento2_sub') }} <strong class="text-amber-600 dark:text-amber-400 font-mono">{{ $maxStreak ?? 0 }} {{ __('site.common.days') }}</strong>.
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
                            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 font-mono">
                                <span class="counter-element" data-target="{{ $displayStreak }}">{{ $displayStreak }}</span> {{ __('site.common.days') }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-1.5" title="{{ __('site.common.streak') }}: {{ $displayStreak }} {{ __('site.common.days') }}">
                            <div class="flex items-end gap-1 h-7">
                                @for($i = 1; $i <= 5; $i++)
                                    @php
                                        $isLit = $displayStreak >= $i;
                                        $barHeights = [1 => 'h-2.5', 2 => 'h-3.5', 3 => 'h-4.5', 4 => 'h-5.5', 5 => 'h-7'];
                                        $hClass = $barHeights[$i] ?? 'h-5';
                                    @endphp
                                    <div class="w-2 {{ $hClass }} rounded-xs transition-all duration-300 {{ $isLit ? 'bg-amber-500 dark:bg-amber-400 shadow-[0_0_8px_rgba(251,191,36,0.6)]' : 'bg-slate-300 dark:bg-ink-800 border border-ink-border/50 opacity-50' }}"
                                         title="{{ $i }}-kun"></div>
                                @endfor
                            </div>
                            <span class="text-[9px] font-mono text-mist uppercase tracking-wider">
                                {{ min($displayStreak, 5) }}/5 {{ __('site.common.days') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Bento 3 (Col 5): AI Analysis -->
                <div class="bento-card md:col-span-5 ks-panel p-6 sm:p-8 flex flex-col justify-between bg-ink-900 border border-ink-border">
                    <div>
                        <div class="w-10 h-10 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-4">
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
                            <span class="text-amber-600 dark:text-amber-400 font-bold">✦</span>
                            <span>{{ __('site.home.bento3_a') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Bento 4 (Col 7): Community & Debates -->
                <div class="bento-card md:col-span-7 ks-panel p-6 sm:p-8 flex flex-col justify-between bg-ink-900 border border-ink-border">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
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
    <section class="py-20 md:py-28 border-t border-ink-border bg-ink-900/30 overflow-hidden max-w-full">
        <div class="max-w-7xl mx-auto px-6">
            
            <div class="step-header text-center max-w-xl mx-auto mb-14 space-y-2">
                <span class="ks-eyebrow">{{ __('site.home.steps_badge') }}</span>
                <h2 class="text-3xl sm:text-4xl font-bold font-serif text-paper">{{ __('site.home.steps_title') }}</h2>
                <p class="text-mist text-sm mt-1">{{ __('site.home.steps_sub') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="step-card p-6 sm:p-8 rounded-card bg-ink-900 border border-ink-border space-y-3">
                    <span class="font-mono text-3xl font-bold text-amber-600 dark:text-amber-500">01</span>
                    <h3 class="text-lg font-bold font-serif text-paper">{{ __('site.home.step1_title') }}</h3>
                    <p class="text-xs text-mist leading-relaxed">
                        {{ __('site.home.step1_sub') }}
                    </p>
                </div>

                <div class="step-card p-6 sm:p-8 rounded-card bg-ink-900 border border-ink-border space-y-3">
                    <span class="font-mono text-3xl font-bold text-amber-600 dark:text-amber-500">02</span>
                    <h3 class="text-lg font-bold font-serif text-paper">{{ __('site.home.step2_title') }}</h3>
                    <p class="text-xs text-mist leading-relaxed">
                        {{ __('site.home.step2_sub') }}
                    </p>
                </div>

                <div class="step-card p-6 sm:p-8 rounded-card bg-ink-900 border border-ink-border space-y-3">
                    <span class="font-mono text-3xl font-bold text-amber-600 dark:text-amber-500">03</span>
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
                <span class="text-amber-600 dark:text-amber-400 italic">{{ __('site.home.cta_title_2') }}</span> {{ __('site.home.cta_title_3') }}
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
        let homeAnimationInitAttempts = 0;
        let homeAnimationsInitialized = false;
        function initHomePageAnimations() {
            if (homeAnimationsInitialized) return;
            if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
                if (homeAnimationInitAttempts++ < 100) {
                    setTimeout(initHomePageAnimations, 50);
                } else {
                    const section = document.getElementById('book-reveal-section');
                    if (section) section.classList.add('book-is-static-open');
                    document.querySelectorAll('.scrolly-content-panel, .scrolly-content-item, .scrolly-stats-row').forEach((el) => {
                        el.style.opacity = '1';
                        el.style.transform = 'none';
                    });
                    document.querySelectorAll('.scrolly-stat-num').forEach((el) => {
                        el.textContent = Number(el.dataset.target || 0).toLocaleString('en-US');
                    });
                }
                return;
            }
            homeAnimationsInitialized = true;
            homeAnimationInitAttempts = 0;

            gsap.registerPlugin(ScrollTrigger);
            if (gsap.config) gsap.config({ nullTargetWarn: false });

            try {
                // Auto-refresh on standard events & ignore mobile browser address-bar resize jumps
                ScrollTrigger.config({
                    autoRefreshEvents: "visibilitychange,DOMContentLoaded,load,resize",
                    ignoreMobileResize: true
                });

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
                if (document.querySelector('#site-header')) {
                    gsap.fromTo('#site-header',
                        { y: -20, opacity: 0 },
                        { y: 0, opacity: 1, duration: 0.6, ease: "power2.out" }
                    );
                }

                // 🌠 1.1 Silky-Smooth 60FPS Shooting Stars (Meteors in Night Sky)
                function launchMeteor(el) {
                    if (!el || typeof gsap === 'undefined') return;

                    const heroSec = el.closest('section') || document.body;
                    const w = heroSec.clientWidth || window.innerWidth;
                    const h = heroSec.clientHeight || window.innerHeight;

                    // O'ng tepa chuqur osmon koordinatalari (bolalar sathidan ancha balandda, ularga yetib bormasdan so'nadi)
                    const startX = w * (0.68 + Math.random() * 0.26);
                    const startY = h * (0.02 + Math.random() * 0.12);
                    const angle = 142 + (Math.random() * 8 - 4); // 138° - 146°
                    const length = 120 + Math.random() * 60; // 120px - 180px
                    const distance = 360 + Math.random() * 220; // 360px - 580px
                    const duration = 0.85 + Math.random() * 0.25; // 0.85s - 1.1s
                    const rad = angle * (Math.PI / 180);
                    const deltaX = Math.cos(rad) * distance;
                    const deltaY = Math.sin(rad) * distance;

                    gsap.killTweensOf(el);
                    gsap.set(el, {
                        left: startX,
                        top: startY,
                        width: length,
                        rotation: angle,
                        x: 0,
                        y: 0,
                        scaleX: 0.15,
                        opacity: 0,
                        force3D: true
                    });

                    const tl = gsap.timeline({
                        onComplete: () => {
                            // Keyingi parvoz uchun tabiiy tanaffus (3.5 - 6 soniya)
                            const nextDelay = 3.5 + Math.random() * 3;
                            gsap.delayedCall(nextDelay, () => launchMeteor(el));
                        }
                    });

                    // 100% silliq, uzluksiz, bir tekis, hech qanday to'xtashlarsiz parvoz
                    tl.to(el, {
                        x: deltaX,
                        y: deltaY,
                        scaleX: 1,
                        duration: duration,
                        ease: "power1.inOut",
                        force3D: true
                    }, 0)
                    .to(el, {
                        opacity: 1,
                        duration: duration * 0.18,
                        ease: "power1.out"
                    }, 0)
                    .to(el, {
                        opacity: 0,
                        duration: duration * 0.32,
                        ease: "power2.in"
                    }, duration * 0.68);
                }

                const meteor1 = document.getElementById('star-meteor-1');
                const meteor2 = document.getElementById('star-meteor-2');
                if (meteor1) gsap.delayedCall(0.8, () => launchMeteor(meteor1));
                if (meteor2) gsap.delayedCall(3.8, () => launchMeteor(meteor2));

                // 2. Editorial Hero Staggered Entrance Reveal
                if (document.querySelectorAll('.hero-anim-item').length > 0) {
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
                }

                // ── 2.1. SCROLLYTELLING BOOK REVEAL SCENE (STPAGEFLIP 3D ENGINE + PINNED SCROLL) ──
                window.initBookRevealSection = function() {
                    const bookRevealSection = document.getElementById('book-reveal-section');
                    if (!bookRevealSection || typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined' || typeof window.PageFlip === 'undefined') return;

                    // Clean up existing trigger if already present (e.g. on resize or PJAX)
                    if (window.bookRevealMedia) {
                        window.bookRevealMedia.revert();
                        window.bookRevealMedia = null;
                    }
                    const existing = ScrollTrigger.getById('book-reveal-st');
                    if (existing) {
                        existing.kill();
                    }

                    const flipbookHost = document.getElementById('scrolly-flipbook-host');
                    if (!flipbookHost) return;

                    // Preserve pristine HTML template before StPageFlip mutates/removes DOM on destroy()
                    if (!window._scrollyFlipbookTemplate) {
                        window._scrollyFlipbookTemplate = flipbookHost.innerHTML;
                    }
                    if (window.scrollyPageFlip) {
                        try {
                            window.scrollyPageFlip.destroy();
                        } catch (_) {}
                        window.scrollyPageFlip = null;
                        flipbookHost.innerHTML = window._scrollyFlipbookTemplate;
                    }

                    const flipbookEl = document.getElementById('scrolly-flipbook');
                    if (!flipbookEl) return;

                    const isDesktop = () => window.matchMedia("(min-width: 1024px)").matches;
                    const prefersReduced = () => window.matchMedia("(prefers-reduced-motion: reduce)").matches;

                    const stage = document.getElementById('scrolly-book-stage');
                    const shadow = document.querySelector('.scrolly-book-shadow');
                    const contentPanel = document.querySelector('.scrolly-content-panel');
                    const contentItems = document.querySelectorAll('.scrolly-content-item');
                    const statsRow = document.querySelector('.scrolly-stats-row');

                    const dragHint = document.getElementById('scrolly-drag-hint');
                    const pageLabel = document.getElementById('scrolly-page-label');
                    const prevBtn = document.getElementById('scrolly-prev-page-btn');
                    const nextBtn = document.getElementById('scrolly-next-page-btn');
                    const dots = document.querySelectorAll('.scrolly-dot');

                    const maxPages = 3;
                    const pageLabels = ['Muqova', '1 / 3-bet', '2 / 3-bet', '3 / 3-bet (Xotima)'];
                    // Map logical spreads (0..3) to StPageFlip page indices:
                    // Spread 0: [0] (Cover)
                    // Spread 1: [1, 2] (Forzats + 1-bet)
                    // Spread 2: [3, 4] (Muqaddima + 2-bet)
                    // Spread 3: [5, 6] (Adabiy Sharh + 3-bet)
                    const spreadToPageIdx = [0, 1, 3, 5];
                    const pageProgressTargets = [0.0, 0.25, 0.48, 0.72];
                    let currentPage = 0;

                    function pageIdxToSpread(idx) {
                        if (idx <= 0) return 0;
                        if (idx <= 2) return 1;
                        if (idx <= 4) return 2;
                        return 3;
                    }

                    // Instantiate Nodlik/StPageFlip (page-flip) in 2-page spread landscape mode
                    const pageFlip = new window.PageFlip(flipbookEl, {
                        width: 290,
                        height: 435,
                        size: 'stretch',
                        minWidth: 135,
                        maxWidth: 340,
                        minHeight: 205,
                        maxHeight: 510,
                        maxShadowOpacity: 0.65,
                        showCover: true,
                        usePortrait: false,
                        mobileScrollSupport: true,
                        flippingTime: 850,
                        showPageCorners: true,
                        clickEventForward: true
                    });

                    pageFlip.loadFromHTML(flipbookEl.querySelectorAll('.stf-book-page'));
                    window.scrollyPageFlip = pageFlip;

                    function syncStagePosture(spreadIdx) {
                        if (stage) {
                            // When closed (spread 0), StPageFlip draws cover on the right half of the 2-page canvas;
                            // shifting stage by -25% centers the closed cover neatly in the column.
                            stage.style.transform = spreadIdx === 0 ? 'translateX(-25%)' : 'translateX(0%)';
                        }
                        if (shadow) {
                            if (spreadIdx === 0) {
                                shadow.style.left = '52%';
                                shadow.style.right = '2%';
                                shadow.style.opacity = '0.68';
                            } else {
                                shadow.style.left = '4%';
                                shadow.style.right = '4%';
                                shadow.style.opacity = '0.92';
                            }
                        }
                        if (spreadIdx > 0 && dragHint) {
                            dragHint.style.opacity = '0';
                            dragHint.style.pointerEvents = 'none';
                        }
                    }

                    function updateUI(spreadIndex) {
                        currentPage = Math.max(0, Math.min(maxPages, spreadIndex));
                        if (pageLabel) pageLabel.textContent = pageLabels[currentPage] || `${currentPage}-bet`;
                        if (prevBtn) prevBtn.disabled = (currentPage === 0);
                        if (nextBtn) {
                            if (currentPage === maxPages) {
                                nextBtn.innerHTML = `<span class="text-[11px] text-amber-600 dark:text-amber-400 font-bold">O'qish ➔</span>`;
                            } else {
                                nextBtn.innerHTML = `<span class="text-[11px]">Keyingi</span><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>`;
                            }
                        }
                        dots.forEach((dot, idx) => {
                            if (idx === currentPage) {
                                dot.className = 'scrolly-dot w-2 h-2 rounded-full bg-amber-500 dark:bg-amber-400 transition-all cursor-pointer ring-2 ring-amber-500/40 dark:ring-amber-400/40';
                            } else {
                                dot.className = 'scrolly-dot w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-ink-700 hover:bg-slate-400 dark:hover:bg-ink-600 transition-all cursor-pointer';
                            }
                        });
                    }

                    const statElements = document.querySelectorAll('.scrolly-stat-num');
                    const statTargets = Array.from(statElements).map(el => parseInt(el.getAttribute('data-target') || '0', 10));
                    const statProgressObj = { progress: 0 };
                    let statsRevealedOnce = false;

                    function revealRightColumnSmoothly() {
                        if (contentPanel && parseFloat(getComputedStyle(contentPanel).opacity) < 0.5) {
                            gsap.to(contentPanel, { opacity: 1, x: 0, duration: 0.45, ease: 'power2.out' });
                        }
                        if (contentItems && contentItems.length > 0) {
                            gsap.to(contentItems, { opacity: 1, y: 0, duration: 0.4, stagger: 0.06, ease: 'power2.out' });
                        }
                        if (statsRow && !statsRevealedOnce) {
                            statsRevealedOnce = true;
                            gsap.to(statsRow, { opacity: 1, y: 0, duration: 0.5, ease: 'power2.out' });
                            gsap.to(statProgressObj, {
                                progress: 1,
                                duration: 0.9,
                                ease: 'power2.out',
                                onUpdate: () => {
                                    statElements.forEach((el, idx) => {
                                        const targetVal = statTargets[idx] || 0;
                                        el.textContent = Math.round(statProgressObj.progress * targetVal).toLocaleString('en-US');
                                    });
                                }
                            });
                        }
                    }

                    function flipToSpread(targetSpread, instant = false) {
                        const clamped = Math.max(0, Math.min(maxPages, targetSpread));
                        const targetPageIdx = spreadToPageIdx[clamped] ?? 0;

                        updateUI(clamped);
                        syncStagePosture(clamped);

                        try {
                            const render = pageFlip.getRender && pageFlip.getRender();
                            if (render && typeof render.finishAnimation === 'function') {
                                render.finishAnimation();
                            }
                            const currentStfSpread = pageIdxToSpread(pageFlip.getCurrentPageIndex());
                            if (currentStfSpread === clamped) return;

                            if (instant || prefersReduced()) {
                                pageFlip.turnToPage(targetPageIdx);
                            } else {
                                pageFlip.flip(targetPageIdx, 'bottom');
                            }
                        } catch (_) {}
                    }

                    // Sync UI & posture when user flips pages directly on StPageFlip (drag corner / click page)
                    pageFlip.on('flip', (e) => {
                        const newSpread = pageIdxToSpread(typeof e.data === 'number' ? e.data : 0);
                        updateUI(newSpread);
                        syncStagePosture(newSpread);
                        if (newSpread >= 1) {
                            revealRightColumnSmoothly();
                        }
                    });

                    pageFlip.on('changeState', (e) => {
                        if (e.data === 'user_fold' || e.data === 'flipping') {
                            if (dragHint) {
                                dragHint.style.opacity = '0';
                                dragHint.style.pointerEvents = 'none';
                            }
                            if (currentPage === 0 && stage) {
                                stage.style.transform = 'translateX(0%)';
                            }
                        } else if (e.data === 'read') {
                            const actualSpread = pageIdxToSpread(pageFlip.getCurrentPageIndex());
                            updateUI(actualSpread);
                            syncStagePosture(actualSpread);
                        }
                    });

                    // Initial state setup
                    if (contentPanel) gsap.set(contentPanel, { opacity: 0, x: 45 });
                    if (contentItems && contentItems.length > 0) gsap.set(contentItems, { opacity: 0, y: 16 });
                    if (statsRow) gsap.set(statsRow, { opacity: 0, y: 24 });
                    updateUI(0);
                    syncStagePosture(0);

                    // ── MASTER TIMELINE: DRIVES SCROLL-LINKED SPREAD FLIPS & EDITORIAL CONTENT REVEAL ──
                    const masterTl = gsap.timeline({
                        paused: true,
                        onUpdate: function() {
                            const p = this.progress();
                            let targetSpread = 0;
                            if (p >= 0.64) targetSpread = 3;
                            else if (p >= 0.40) targetSpread = 2;
                            else if (p >= 0.14) targetSpread = 1;
                            else targetSpread = 0;

                            if (targetSpread !== currentPage) {
                                flipToSpread(targetSpread, prefersReduced());
                            }
                        }
                    });

                    masterTl.to({}, { duration: 1.0 }, 0);

                    if (dragHint) {
                        masterTl.to(dragHint, {
                            opacity: 0,
                            duration: 0.08,
                            ease: 'power1.out'
                        }, 0.04);
                    }

                    // 25–65%: sarlavha, muallif va tavsif navbat bilan ko'rinadi
                    if (contentPanel) {
                        masterTl.to(contentPanel, {
                            opacity: 1,
                            x: 0,
                            ease: 'power2.out',
                            duration: 0.18
                        }, 0.22);
                    }
                    if (contentItems && contentItems.length > 0) {
                        masterTl.to(contentItems, {
                            opacity: 1,
                            y: 0,
                            ease: 'power2.out',
                            duration: 0.12,
                            stagger: 0.055
                        }, 0.30);
                    }

                    // 65–100%: statistika chiqadi va sonlar scroll bilan sanaladi
                    if (statsRow) {
                        masterTl.to(statsRow, {
                            opacity: 1,
                            y: 0,
                            ease: 'power2.out',
                            duration: 0.30
                        }, 0.65);
                    }

                    masterTl.to(statProgressObj, {
                        progress: 1,
                        ease: 'none',
                        duration: 0.30,
                        onUpdate: () => {
                            statElements.forEach((el, idx) => {
                                const targetVal = statTargets[idx] || 0;
                                const currentVal = Math.round(statProgressObj.progress * targetVal);
                                el.textContent = currentVal.toLocaleString('en-US');
                            });
                        }
                    }, 0.65);

                    // ── ATTACH SCROLLTRIGGER (DESKTOP PIN + MOBILE AUTO OPEN & CLOSE ON SCROLL) ──
                    window.bookRevealMedia = ScrollTrigger.matchMedia({
                        "(min-width: 1024px) and (prefers-reduced-motion: no-preference)": function() {
                            flipToSpread(0, true);
                            ScrollTrigger.create({
                                id: 'book-reveal-st',
                                animation: masterTl,
                                trigger: bookRevealSection,
                                start: 'top top',
                                end: '+=150%',
                                pin: true,
                                scrub: 0.6,
                                anticipatePin: 1,
                                invalidateOnRefresh: true
                            });
                        },
                        "(max-width: 1023px) and (prefers-reduced-motion: no-preference)": function() {
                            masterTl.progress(0);
                            flipToSpread(0, true);

                            if (contentPanel) gsap.set(contentPanel, { opacity: 1, x: 0 });
                            if (contentItems && contentItems.length > 0) gsap.set(contentItems, { opacity: 1, y: 0 });
                            if (statsRow) gsap.set(statsRow, { opacity: 1, y: 0 });

                            statElements.forEach((el, idx) => {
                                el.textContent = (statTargets[idx] || 0).toLocaleString('en-US');
                            });

                            const mobileST = ScrollTrigger.create({
                                id: 'book-reveal-mobile-st',
                                trigger: bookRevealSection,
                                start: 'top 75%',
                                onEnter: () => {
                                    flipToSpread(1, false);
                                },
                                onLeaveBack: () => {
                                    flipToSpread(0, false);
                                }
                            });

                            return () => {
                                if (mobileST) mobileST.kill();
                            };
                        },
                        "(prefers-reduced-motion: reduce)": function() {
                            masterTl.progress(0.75);
                            flipToSpread(maxPages, true);
                            if (dragHint) gsap.set(dragHint, { autoAlpha: 0 });
                            if (contentPanel) gsap.set(contentPanel, { opacity: 1, x: 0 });
                            if (contentItems && contentItems.length > 0) gsap.set(contentItems, { opacity: 1, y: 0 });
                            if (statsRow) gsap.set(statsRow, { opacity: 1, y: 0 });
                            statElements.forEach((el, idx) => {
                                el.textContent = (statTargets[idx] || 0).toLocaleString('en-US');
                            });
                        }
                    });

                    // ── PROGRAMMATIC NAVIGATION (BUTTONS, DOTS, CTA) ──
                    function goToPage(targetPage, duration = 0.75) {
                        if (targetPage < 0) targetPage = 0;
                        if (targetPage > maxPages) {
                            const readLink = "{{ $scrollyLink }}";
                            if (readLink) window.location.href = readLink;
                            return;
                        }

                        if (dragHint) {
                            dragHint.style.opacity = '0';
                            dragHint.style.pointerEvents = 'none';
                        }

                        const targetProg = pageProgressTargets[targetPage] ?? 0;
                        const st = (typeof ScrollTrigger !== 'undefined') ? ScrollTrigger.getById('book-reveal-st') : null;

                        if (st && isDesktop() && !prefersReduced()) {
                            // If section is currently pinned in viewport, scroll to matching checkpoint;
                            // otherwise flip directly so buttons also work before/after pin range.
                            const inPinRange = window.scrollY >= (st.start - 80) && window.scrollY <= (st.end + 80);
                            if (inPinRange) {
                                const targetScroll = st.start + targetProg * (st.end - st.start);
                                const scrollObj = { y: window.scrollY };
                                gsap.to(scrollObj, {
                                    y: targetScroll,
                                    duration: duration,
                                    ease: "power2.inOut",
                                    onUpdate: () => {
                                        window.scrollTo(0, scrollObj.y);
                                    }
                                });
                            } else {
                                flipToSpread(targetPage, false);
                                if (targetPage >= 1) revealRightColumnSmoothly();
                            }
                        } else {
                            flipToSpread(targetPage, prefersReduced());
                            if (targetPage >= 1) revealRightColumnSmoothly();
                        }
                    }

                    window.flipBookToPage = function(p) { goToPage(p); };
                    window.flipBookNext = function() { goToPage(currentPage + 1); };
                    window.flipBookPrev = function() { goToPage(currentPage - 1); };

                    if (prevBtn) {
                        prevBtn.onclick = (e) => {
                            e.preventDefault();
                            goToPage(currentPage - 1);
                        };
                    }

                    if (nextBtn) {
                        nextBtn.onclick = (e) => {
                            e.preventDefault();
                            goToPage(currentPage + 1);
                        };
                    }

                    dots.forEach((dot) => {
                        dot.onclick = (e) => {
                            e.preventDefault();
                            const p = parseInt(dot.getAttribute('data-page') || '0', 10);
                            goToPage(p);
                        };
                    });

                    // Hero and bottom scroll cue smooth trigger
                    document.querySelectorAll('a[href="#book-reveal-section"]').forEach((link) => {
                        link.onclick = (e) => {
                            e.preventDefault();
                            if (bookRevealSection) {
                                bookRevealSection.scrollIntoView({ behavior: 'smooth' });
                                setTimeout(() => {
                                    goToPage(1, 0.9);
                                }, 500);
                            }
                        };
                    });
                };

                // Initialize immediately
                window.initBookRevealSection();

                // 3. Bento Grid Reveal
                if (document.querySelector('#features') && document.querySelectorAll('.bento-header').length > 0) {
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
                }

                if (document.querySelector('#features') && document.querySelectorAll('.bento-card').length > 0) {
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
                }

                // 4. Step Cards Stagger Reveal
                if (document.querySelectorAll('.step-card').length > 0) {
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
                }

                // 5. CTA Box Reveal
                if (document.querySelectorAll('.cta-box').length > 0) {
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
                }

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
            } catch(e) {
                console.warn('Animation init error:', e);
            }
        }

        window.initHomePageAnimations = initHomePageAnimations;
        window.addEventListener('kitobxon:gsap-ready', () => {
            initHomePageAnimations();
        });

        if (document.getElementById('scrolly-book-stage') && typeof gsap !== 'undefined') {
            initHomePageAnimations();
        } else if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initHomePageAnimations);
        } else {
            initHomePageAnimations();
        }
    
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
        let __failsafeTicking = false;
        function __throttledVisibilityFailsafe() {
            if (__failsafeTicking) return;
            __failsafeTicking = true;
            requestAnimationFrame(() => {
                __kitobxonVisibilityFailsafe();
                __failsafeTicking = false;
            });
        }
        if (window.requestIdleCallback) {
            requestIdleCallback(__kitobxonVisibilityFailsafe);
        } else {
            setTimeout(__kitobxonVisibilityFailsafe, 150);
        }
        window.addEventListener('scroll', __throttledVisibilityFailsafe, { passive: true });
        setInterval(__throttledVisibilityFailsafe, 3000);
    </script>

    <!-- ── Universal Toast Notification Container ── -->
    <x-toast-container />

    <!-- ── Universal Book Share Modal ── -->
    <x-book-share-modal />

    <!-- ── Persistent Global Audio Player (Mutolaa Dock) ── -->
    <x-global-audio-player />

    <!-- ── Mobile Bottom Navigation Bar ── -->
    <x-nav.mobile-bottom-bar />
</body>
</html>
