<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark overflow-x-hidden max-w-full" x-data="{ mobileMenu: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kitobxon — Har hafta bitta sara kitob, chuqur tahlil va gamifikatsiya. O'zbekistondagi eng ilg'or kitobxonlar ekotizimi.">
    <title>Kitobxon — @yield('title', __('site.home.hero_title') . __('site.home.hero_title_bold'))</title>

    @include('partials.design-system')

    <!-- Google Fonts: Instrument Serif (display) & Inter (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- GSAP & ScrollTrigger for Subtle Editorial Physics Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

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
            background: rgba(255, 255, 255, 0.01);
            background-blend-mode: luminosity;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            border: none;
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.1);
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

        /* Animations */
        @keyframes fade-rise {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-rise { animation: fade-rise 0.8s ease-out both; }
        .animate-fade-rise-delay { animation: fade-rise 0.8s ease-out 0.2s both; }
        .animate-fade-rise-delay-2 { animation: fade-rise 0.8s ease-out 0.4s both; }

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

        /* Scrollytelling 3D Book Stage (Apple-grade Physics + Interactive 3D Drag/Swipe) */
        .scrolly-book-stage {
            perspective: 1600px;
            transform-style: preserve-3d;
            cursor: grab;
            user-select: none;
            -webkit-user-select: none;
            touch-action: pan-y;
        }
        .scrolly-book-stage:active,
        .scrolly-book-stage.is-dragging {
            cursor: grabbing;
        }
        .scrolly-book-cover,
        .scrolly-book-leaf {
            transform-origin: left center;
            transform-style: preserve-3d;
            will-change: transform;
            border-radius: 3px 8px 8px 3px;
            transition: box-shadow 0.3s ease;
        }
        .scrolly-cover-face,
        .scrolly-leaf-face {
            position: absolute;
            inset: 0;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            border-radius: 3px 8px 8px 3px;
            overflow: hidden;
        }
        .scrolly-cover-back,
        .scrolly-leaf-back {
            transform: rotateY(180deg);
        }
        .scrolly-book-inside {
            transform-style: preserve-3d;
        }
        /* Dog-ear page corner curl cue */
        .scrolly-dogear {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 26px;
            height: 26px;
            background: linear-gradient(135deg, transparent 50%, rgba(212,175,55,0.7) 50%, #FAF7F2 100%);
            box-shadow: -2px -2px 6px rgba(0,0,0,0.18);
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: pointer;
            z-index: 25;
            border-bottom-right-radius: 8px;
        }
        .scrolly-dogear:hover {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, transparent 50%, #D4AF37 50%, #FAF7F2 100%);
            box-shadow: -3px -3px 10px rgba(0,0,0,0.25);
        }
        @media (prefers-reduced-motion: reduce) {
            .scrolly-book-cover,
            .scrolly-book-leaf {
                transform: none !important;
            }
        }
    </style>
</head>
<body class="bg-ink-950 text-paper font-sans selection:bg-amber-500 selection:text-ink-950 antialiased min-h-screen relative ks-grain overflow-x-hidden w-full max-w-full">

    <!-- ── Page Transition & Loader ── -->
    @include('components.page-loader')

    <div id="smooth-page-wrapper" class="w-full max-w-full overflow-x-clip">

    <!-- ── Universal Platform Header ── -->
    <x-nav.main-header />

    <!-- ── 1. KITOBXON CINEMATIC FULLSCREEN VIDEO HERO SECTION ── -->
    <section class="relative w-full min-h-[calc(100vh-4rem)] flex flex-col justify-between overflow-hidden bg-[hsl(var(--background))] text-[hsl(var(--foreground))] select-none max-w-full">
        
        <!-- Fullscreen Looping Video Background -->
        <video 
            autoplay 
            loop 
            muted 
            playsinline 
            class="absolute inset-0 w-full h-full object-cover z-0 pointer-events-none">
            <source src="https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260314_131748_f2ca2a28-fed7-44c8-b9a9-bd9acdd5ec31.mp4" type="video/mp4">
        </video>

        <!-- Centered Cinematic Hero Section Content -->
        <div class="relative z-10 flex flex-col items-center justify-center text-center px-4 sm:px-6 pt-16 sm:pt-24 pb-20 sm:pb-28 max-w-7xl mx-auto w-full flex-1 overflow-hidden">
            
            <!-- Eyebrow Pill Badge -->
            <div class="animate-fade-rise inline-flex items-center gap-2.5 px-3.5 sm:px-4 py-1.5 rounded-full liquid-glass text-[10.5px] sm:text-xs text-amber-300 font-mono tracking-wider mb-6 max-w-full">
                <span class="relative flex h-2 w-2 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                </span>
                <span class="break-words">HAR HAFTA BITTA SARA ASAR VA CHUQUR MUTOLAA</span>
            </div>

            <!-- H1 Cinematic Headline -->
            <h1 class="animate-fade-rise text-4xl sm:text-6xl md:text-7xl lg:text-8xl leading-[0.98] sm:leading-[0.95] tracking-tight sm:tracking-[-2.46px] max-w-7xl font-normal text-[hsl(var(--foreground))] break-words"
                style="font-family: 'Instrument Serif', serif;">
                Sahifalar aro <em class="not-italic text-amber-300/90">orzular</em> va <em class="not-italic text-[hsl(var(--muted-foreground))]">teran tafakkur yuksaladi.</em>
            </h1>

            <!-- Subtext -->
            <p class="animate-fade-rise-delay text-[hsl(var(--muted-foreground))] text-base sm:text-lg max-w-2xl mt-8 leading-relaxed font-normal"
               style="font-family: var(--font-body, 'Inter', sans-serif);">
                Chalg'ituvchi shovqinlar orasida — chuqur mutolaa, 3D interaktiv varaqlash va ilhom maskani. Sara jahon hamda o'zbek adabiyoti, audio asarlar va intellektual kitobxonlar ekotizimi.
            </p>

            <!-- Hero CTA Buttons -->
            <div class="animate-fade-rise-delay-2 mt-12 flex flex-col sm:flex-row items-center justify-center gap-4 w-full sm:w-auto">
                <a href="{{ auth()->check() ? route('books.catalog') : route('register') }}"
                   class="liquid-glass rounded-full px-10 sm:px-14 py-4 sm:py-5 text-base text-[hsl(var(--foreground))] hover:scale-[1.03] cursor-pointer inline-flex items-center justify-center gap-2.5 transition-transform duration-300 font-medium group w-full sm:w-auto"
                   style="font-family: var(--font-body, 'Inter', sans-serif);">
                    <span>Mutolaani boshlash</span>
                    <svg class="w-4 h-4 text-amber-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="#book-reveal-section"
                   class="liquid-glass rounded-full px-8 py-4 sm:py-5 text-base text-[hsl(var(--muted-foreground))] hover:text-white hover:scale-[1.03] cursor-pointer inline-flex items-center justify-center gap-2 transition-all duration-300 font-medium w-full sm:w-auto"
                   style="font-family: var(--font-body, 'Inter', sans-serif);">
                    <span>📖 3D Kitobni ochish</span>
                </a>
            </div>

            <!-- Micro-features Pill Bar -->
            <div class="animate-fade-rise-delay-2 mt-12 flex flex-wrap items-center justify-center gap-2.5 sm:gap-4 text-xs text-[hsl(var(--muted-foreground))] font-mono">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full liquid-glass">
                    <span>📚</span> {{ $booksCount ?? 1000 }}+ {{ __('site.home.stat_books') }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full liquid-glass">
                    <span>🎧</span> Audio mutolaa
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full liquid-glass">
                    <span>🔥</span> Kunlik streak va ballar
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full liquid-glass">
                    <span>👥</span> {{ $usersCount ?? 1200 }}+ {{ __('site.home.active_readers') }}
                </span>
            </div>

        </div>

        <!-- Bottom scroll cue -->
        <div class="relative z-10 pb-6 w-full flex justify-center">
            <a href="#book-reveal-section" class="inline-flex flex-col items-center gap-1.5 text-[11px] font-mono tracking-widest uppercase text-white/40 hover:text-amber-400 transition-colors cursor-pointer group">
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

    <section id="book-reveal-section" class="relative w-full min-h-screen bg-ink-950 border-b border-ink-border flex items-center justify-center py-16 lg:py-0 overflow-hidden max-w-full">
        <!-- Atmospheric Ambient Spotlight behind the book -->
        <div class="scrolly-ambient-glow absolute inset-0 pointer-events-none flex items-center justify-center opacity-60 overflow-hidden" aria-hidden="true">
            <div class="w-[500px] lg:w-[650px] h-[500px] lg:h-[650px] rounded-full bg-gradient-radial from-amber-500/15 via-vermilion/5 to-transparent blur-3xl max-w-full"></div>
        </div>

        <!-- Inner Centered Container -->
        <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 relative z-10 overflow-hidden lg:overflow-visible">
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

            <!-- Stage Grid: 3D Book on Left (col-span-6) + Revealed Story on Right (col-span-6) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                
                <!-- LEFT (cols 1-6): The Grand 3D Book Stage (Multi-Leaf 3D Interactive Flip) -->
                <div class="lg:col-span-6 flex flex-col justify-center lg:justify-end lg:pr-8 items-center max-w-full">
                    <div class="scrolly-book-stage relative w-[205px] sm:w-[260px] lg:w-[320px] aspect-[2/3] select-none" id="scrolly-book-stage">
                        
                        <!-- Floating Drag/Swipe Gesture Hint Pill -->
                        <div id="scrolly-drag-hint"
                             class="absolute -top-11 left-1/2 -translate-x-1/2 pointer-events-none z-50 transition-all duration-500 flex items-center gap-1.5 px-3 py-1 rounded-full bg-ink-950/95 border border-amber-400/40 text-[10px] sm:text-[10.5px] font-mono text-amber-300 shadow-2xl backdrop-blur-md max-w-[90vw] whitespace-nowrap overflow-hidden">
                            <span class="inline-block animate-[bounce_1.2s_infinite]">👈</span>
                            <span>Mishka yoki qo'l bilan suring (boshqa betga)</span>
                            <span class="inline-block animate-[bounce_1.2s_infinite]">👉</span>
                        </div>

                        <!-- Dynamic Floor Shadow underneath book -->
                        <div class="scrolly-book-shadow absolute -bottom-6 left-[8%] right-[8%] h-7 rounded-full bg-black/80 blur-xl"></div>

                        <!-- Inner Spine and Layered Paper Edge (Right) -->
                        <div class="scrolly-book-pages-edge absolute top-1 bottom-1 -right-2 w-4 rounded-r-sm bg-gradient-to-r from-[#D9D2C5] via-[#FAF7F2] to-[#E5DFD5] shadow-md border-r border-[#B8B0A2]"
                             style="background-image: repeating-linear-gradient(90deg, #D9D2C5 0 1px, #FAF7F2 1px 2px);"></div>

                        <!-- ══════════════════════════════════════════════════════════════════ -->
                        <!-- BASE LAYER (Page 3 / Final Conclusion & Reader Link) — z-index: 10 -->
                        <!-- ══════════════════════════════════════════════════════════════════ -->
                        <div class="scrolly-book-inside scrolly-page-base absolute inset-0 rounded-l-[2px] rounded-r-md bg-[#FAF7F0] text-ink-950 p-5 flex flex-col justify-between overflow-hidden shadow-inner border border-[#E5DFD5] z-10">
                            <!-- Left Spine Gutter Crease Shadow -->
                            <div class="absolute inset-y-0 left-0 w-6 pointer-events-none bg-gradient-to-r from-black/20 via-black/5 to-transparent z-10"></div>
                            <!-- Right Page Edge subtle shadow -->
                            <div class="absolute inset-y-0 right-0 w-3 pointer-events-none bg-gradient-to-l from-black/10 to-transparent z-10"></div>

                            <!-- Header Bar -->
                            <div class="border-b border-[#D9D2C5]/80 pb-2 relative z-0 flex items-center justify-between text-[9px] font-mono tracking-widest text-[#8B9BAD] uppercase">
                                <span class="truncate max-w-[130px] font-semibold text-[#526071]">{{ $scrollyTitle }}</span>
                                <span class="text-amber-700/70 font-serif text-xs">❦</span>
                                <span>BOB III · 3</span>
                            </div>

                            <!-- Chapter 3 Body & Reader Callout -->
                            <div class="space-y-2 py-1.5 relative z-0">
                                <div class="text-center pt-0.5 pb-0.5">
                                    <span class="font-mono text-[8.5px] tracking-[0.25em] text-[#8B9BAD] uppercase block">Uchinchi Bob</span>
                                    <h4 class="font-serif italic text-xs font-semibold text-[#1A1D24] mt-0.5">«Xotima va Hikmat»</h4>
                                    <div class="w-12 h-px bg-gradient-to-r from-transparent via-amber-600/40 to-transparent mx-auto mt-1"></div>
                                </div>

                                <p class="font-serif italic text-[9.5px] text-[#526071] px-2 leading-relaxed border-l-2 border-amber-600/40 pl-2 text-left my-1">
                                    «Kitob tugagan joyda inson tafakkurining cheksiz ufqlariga parvoz boshlanadi...»
                                </p>

                                <p class="font-serif text-[10px] text-[#1A1D24] leading-[1.55] text-justify pt-0.5">
                                    Ushbu asar qalbni yorituvchi teran hikmatdir. Qahramonlarning har bir qarori va taqdiri bugungi kunimiz uchun ham qimmatli saboq beradi.
                                </p>

                                <!-- Direct CTA to Open Full Book Reader ("boshqa betga") -->
                                <div class="pt-1.5 space-y-1">
                                    <a href="{{ $scrollyLink }}"
                                       class="w-full py-2 px-3 rounded-lg bg-amber-500 hover:bg-amber-400 text-ink-950 font-serif font-bold text-xs flex items-center justify-center gap-1.5 shadow-md transition-all hover:scale-[1.02] active:scale-95 group">
                                        <span>📖 Kitobni to'liq o'qish</span>
                                        <span class="group-hover:translate-x-1 transition-transform">➔</span>
                                    </a>
                                    <span class="font-mono text-[7.5px] text-[#8B9BAD] text-center block">To'liq sahifaga o'tish uchun bosing yoki suring</span>
                                </div>
                            </div>

                            <!-- Running Footer -->
                            <div class="border-t border-[#D9D2C5]/80 pt-2 relative z-0 flex items-center justify-between text-[9px] font-mono text-[#8B9BAD]">
                                <span>Kitobxon Nashri</span>
                                <span class="font-serif italic text-[#526071] font-semibold">— 3 —</span>
                                <span>№ {{ $scrollyBook->week_number ?? 1 }}</span>
                            </div>
                        </div>

                        <!-- ══════════════════════════════════════════════════════════════════ -->
                        <!-- FLIPPABLE LEAF 2 (Page 2 front & verso) — z-index: 20              -->
                        <!-- ══════════════════════════════════════════════════════════════════ -->
                        <div class="scrolly-book-leaf scrolly-leaf-2 absolute inset-0 z-20" style="transform: rotateY(0deg);">
                            <!-- Front Face: Page 2 -->
                            <div class="scrolly-leaf-face scrolly-leaf-front bg-[#FAF7F0] text-ink-950 p-5 flex flex-col justify-between shadow-md border border-[#E5DFD5]">
                                <!-- Left Spine Crease Shadow -->
                                <div class="absolute inset-y-0 left-0 w-6 pointer-events-none bg-gradient-to-r from-black/20 via-black/5 to-transparent z-10"></div>
                                <div class="absolute inset-y-0 right-0 w-3 pointer-events-none bg-gradient-to-l from-black/10 to-transparent z-10"></div>

                                <!-- Dog-ear corner -->
                                <div class="scrolly-dogear" onclick="window.flipBookToPage && window.flipBookToPage(3)" title="3-betga o'tish"></div>

                                <!-- Header -->
                                <div class="border-b border-[#D9D2C5]/80 pb-2 relative z-0 flex items-center justify-between text-[9px] font-mono tracking-widest text-[#8B9BAD] uppercase">
                                    <span class="truncate max-w-[130px] font-semibold text-[#526071]">{{ $scrollyTitle }}</span>
                                    <span class="text-amber-700/70 font-serif text-xs">❦</span>
                                    <span>BOB II · 2</span>
                                </div>

                                <!-- Body -->
                                <div class="space-y-2 py-1.5 relative z-0">
                                    <div class="text-center pt-0.5 pb-0.5">
                                        <span class="font-mono text-[8.5px] tracking-[0.25em] text-[#8B9BAD] uppercase block">Ikkinchi Bob</span>
                                        <h4 class="font-serif italic text-xs font-semibold text-[#1A1D24] mt-0.5">«Marg'ilon Yo'lida»</h4>
                                        <div class="w-12 h-px bg-gradient-to-r from-transparent via-amber-600/40 to-transparent mx-auto mt-1"></div>
                                    </div>

                                    <p class="font-serif italic text-[9.5px] text-[#526071] px-2 leading-relaxed border-l-2 border-amber-600/40 pl-2 text-left my-1.5">
                                        «Qalb istagan manzilga yetmoq uchun yo'l mashaqqatlariga chidamoq darkor...»
                                    </p>

                                    <div class="font-serif text-[10.5px] text-[#1A1D24] leading-[1.65] text-justify pt-0.5">
                                        Otabek Marg'ilonga yaqinlashar ekan, uning qalbida noma'lum bir orziqish uyg'ongan edi. Oqshom shafag'i ostida shahar devorlari sirli tus olib, har bir ko'cha o'tmish hikoyalaridan so'zlayotgandek tuyulardi.
                                    </div>

                                    <p class="font-serif text-[10px] text-[#4A5568] leading-relaxed pt-1 text-justify hidden sm:block">
                                        Taqdirning sirli burilishlari va buyuk muhabbat sinovlari bu sahifalarda o'z aksini topgan.
                                    </p>
                                </div>

                                <!-- Footer -->
                                <div class="border-t border-[#D9D2C5]/80 pt-2 relative z-0 flex items-center justify-between text-[9px] font-mono text-[#8B9BAD]">
                                    <span>Kitobxon Nashri</span>
                                    <span class="font-serif italic text-[#526071] font-semibold">— 2 —</span>
                                    <span class="text-amber-700 font-semibold cursor-pointer hover:underline" onclick="window.flipBookToPage && window.flipBookToPage(3)">3-bet ➔</span>
                                </div>
                            </div>

                            <!-- Back Face (Verso): Page 2 Back -->
                            <div class="scrolly-leaf-face scrolly-leaf-back bg-[#F5EFE6] text-ink-950 p-5 flex flex-col justify-between shadow-2xl border border-[#DDD5C7]">
                                <div class="border-b border-[#D5CBB9] pb-2 text-center">
                                    <span class="font-mono text-[8px] uppercase tracking-[0.25em] text-[#8B9BAD] block">Adabiy Sharh</span>
                                    <span class="font-serif text-[11px] text-[#1A1D24] font-semibold">«Qahramonlar Qalbi»</span>
                                </div>
                                <div class="my-auto py-2 text-center space-y-2">
                                    <blockquote class="font-serif italic text-[10px] text-[#2C3440] leading-relaxed px-2">
                                        «Har bir qahramonning o'z haqiqati bor. Lekin eng oliy haqiqat — bu sadoqat va ma'rifatdir.»
                                    </blockquote>
                                    <div class="w-8 h-px bg-amber-700/30 mx-auto"></div>
                                </div>
                                <div class="border-t border-[#D5CBB9] pt-2 flex justify-between text-[8px] font-mono text-[#6E7B8B]">
                                    <span>BOB II TAHLILI</span>
                                    <span>№ {{ $scrollyBook->week_number ?? 1 }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- ══════════════════════════════════════════════════════════════════ -->
                        <!-- FLIPPABLE LEAF 1 (Page 1 front & verso) — z-index: 30              -->
                        <!-- ══════════════════════════════════════════════════════════════════ -->
                        <div class="scrolly-book-leaf scrolly-leaf-1 absolute inset-0 z-30" style="transform: rotateY(0deg);">
                            <!-- Front Face: Page 1 -->
                            <div class="scrolly-leaf-face scrolly-leaf-front bg-[#FAF7F0] text-ink-950 p-5 flex flex-col justify-between shadow-md border border-[#E5DFD5]">
                                <!-- Left Spine Gutter Crease Shadow -->
                                <div class="absolute inset-y-0 left-0 w-6 pointer-events-none bg-gradient-to-r from-black/20 via-black/5 to-transparent z-10"></div>
                                <div class="absolute inset-y-0 right-0 w-3 pointer-events-none bg-gradient-to-l from-black/10 to-transparent z-10"></div>

                                <!-- Dog-ear corner -->
                                <div class="scrolly-dogear" onclick="window.flipBookToPage && window.flipBookToPage(2)" title="2-betga o'tish"></div>

                                <!-- Header Bar -->
                                <div class="border-b border-[#D9D2C5]/80 pb-2 relative z-0 flex items-center justify-between text-[9px] font-mono tracking-widest text-[#8B9BAD] uppercase">
                                    <span class="truncate max-w-[130px] font-semibold text-[#526071]">{{ $scrollyTitle }}</span>
                                    <span class="text-amber-700/70 font-serif text-xs">❦</span>
                                    <span>BOB I · 1</span>
                                </div>

                                <!-- Chapter Heading & Body with Drop Cap -->
                                <div class="space-y-2 py-1.5 relative z-0">
                                    <div class="text-center pt-0.5 pb-0.5">
                                        <span class="font-mono text-[8.5px] tracking-[0.25em] text-[#8B9BAD] uppercase block">Birinchi Bob</span>
                                        <h4 class="font-serif italic text-xs font-semibold text-[#1A1D24] mt-0.5">«Ibtido va Tafakkur»</h4>
                                        <div class="w-12 h-px bg-gradient-to-r from-transparent via-amber-600/40 to-transparent mx-auto mt-1"></div>
                                    </div>

                                    <!-- Epigraph -->
                                    <p class="font-serif italic text-[9.5px] text-[#526071] px-2 leading-relaxed border-l-2 border-amber-600/40 pl-2 text-left my-1.5">
                                        «Tafakkur qilgan inson uchun har bir sahifada butun bir olam yashiringan...»
                                    </p>

                                    <!-- Text with Drop Cap -->
                                    <div class="font-serif text-[10.5px] text-[#1A1D24] leading-[1.65] text-justify pt-0.5">
                                        <span class="float-left text-3xl font-serif font-bold text-amber-700 leading-none mr-0.5 mt-0.5 select-none">{{ mb_substr($scrollyPageExcerpt, 0, 1) }}</span><span class="inline">{{ mb_substr($scrollyPageExcerpt, 1) }}</span>
                                    </div>

                                    <p class="font-serif text-[10px] text-[#4A5568] leading-relaxed pt-1 text-justify hidden sm:block">
                                        Mutolaa — qalbni yorituvchi nur, ruhni yuksaltiruvchi qanotdir. Sahifalar orasidagi hikmat inson tafakkurini boyitadi.
                                    </p>
                                </div>

                                <!-- Running Footer -->
                                <div class="border-t border-[#D9D2C5]/80 pt-2 relative z-0 flex items-center justify-between text-[9px] font-mono text-[#8B9BAD]">
                                    <span>Kitobxon Nashri</span>
                                    <span class="font-serif italic text-[#526071] font-semibold">— 1 —</span>
                                    <span class="text-amber-700 font-semibold cursor-pointer hover:underline" onclick="window.flipBookToPage && window.flipBookToPage(2)">2-bet ➔</span>
                                </div>
                            </div>

                            <!-- Back Face (Verso): Page 1 Back -->
                            <div class="scrolly-leaf-face scrolly-leaf-back bg-[#F5EFE6] text-ink-950 p-5 flex flex-col justify-between shadow-2xl border border-[#DDD5C7]">
                                <div class="border-b border-[#D5CBB9] pb-2 text-center">
                                    <span class="font-mono text-[8px] uppercase tracking-[0.25em] text-[#8B9BAD] block">Muqaddima</span>
                                    <span class="font-serif text-[11px] text-[#1A1D24] font-semibold">«Kitob Falsafasi»</span>
                                </div>
                                <div class="my-auto py-2 text-center space-y-2">
                                    <blockquote class="font-serif italic text-[10px] text-[#2C3440] leading-relaxed px-2">
                                        «So'z — qalb ko'zgusi, kitob esa butun insoniyatning bebaho xazinasidir.»
                                    </blockquote>
                                    <div class="w-8 h-px bg-amber-700/30 mx-auto"></div>
                                </div>
                                <div class="border-t border-[#D5CBB9] pt-2 flex justify-between text-[8px] font-mono text-[#6E7B8B]">
                                    <span>BOB I XULOSASI</span>
                                    <span>№ {{ $scrollyBook->week_number ?? 1 }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- ══════════════════════════════════════════════════════════════════ -->
                        <!-- TOPMOST COVER LEAF (Muqova & Forzats) — z-index: 40                -->
                        <!-- ══════════════════════════════════════════════════════════════════ -->
                        <div class="scrolly-book-cover absolute inset-0 rounded-l-[3px] rounded-r-md origin-left z-40">
                            
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
                            <div class="scrolly-cover-face scrolly-cover-back absolute inset-0 rounded-r-[3px] rounded-l-md bg-[#F5EFE6] border border-[#DDD5C7] p-4 flex flex-col justify-between shadow-2xl text-ink-950 overflow-hidden"
                                 style="transform: rotateY(180deg);">
                                
                                <!-- Right Spine Gutter Crease Shadow (connects to spine) -->
                                <div class="absolute inset-y-0 right-0 w-8 pointer-events-none bg-gradient-to-l from-black/25 via-black/10 to-transparent z-10"></div>
                                <!-- Left Outer Edge Subtle Shadow -->
                                <div class="absolute inset-y-0 left-0 w-3 pointer-events-none bg-gradient-to-r from-black/10 to-transparent z-10"></div>

                                <!-- Vintage Double Inner Border Frame with Ornate Corner Accents -->
                                <div class="absolute inset-2 pointer-events-none border border-[#C5BBA8]/50 rounded-[2px]"></div>
                                <div class="absolute inset-2.5 pointer-events-none border border-[#C5BBA8]/30 rounded-[1px]"></div>
                                <span class="absolute top-3 left-3 text-[8px] text-[#A89F8D] pointer-events-none select-none">✦</span>
                                <span class="absolute top-3 right-3 text-[8px] text-[#A89F8D] pointer-events-none select-none">✦</span>
                                <span class="absolute bottom-3 left-3 text-[8px] text-[#A89F8D] pointer-events-none select-none">✦</span>
                                <span class="absolute bottom-3 right-3 text-[8px] text-[#A89F8D] pointer-events-none select-none">✦</span>

                                <!-- Top Header -->
                                <div class="relative z-0 border-b border-[#D5CBB9] pb-2 text-center">
                                    <span class="font-mono text-[8px] uppercase tracking-[0.25em] text-[#8B9BAD] block">Nodir Nusxalar Kolleksiyasi</span>
                                    <span class="font-serif text-[11px] text-[#1A1D24] font-semibold tracking-wide mt-0.5 block">KITOBXON KUTUBXONASI</span>
                                </div>

                                <!-- Center: Ornate Ex-Libris Crest & Literary Motto -->
                                <div class="relative z-0 my-auto py-2 text-center space-y-2">
                                    <!-- Illustrated Ex-Libris Seal -->
                                    <div class="inline-flex flex-col items-center justify-center p-2 rounded-full border border-amber-700/30 bg-amber-500/5 mx-auto">
                                        <svg class="w-6 h-6 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                                            <circle cx="12" cy="9" r="2"/>
                                            <path d="M9 14h6"/>
                                        </svg>
                                        <span class="font-mono text-[7px] uppercase tracking-widest text-amber-800 font-bold mt-0.5">EX LIBRIS</span>
                                    </div>
                                    <span class="font-serif text-[9.5px] text-[#526071] block italic">Maxsus Kolleksiya Nusxasi</span>

                                    <!-- Calligraphic Quote -->
                                    <blockquote class="font-serif italic text-[10.5px] text-[#2C3440] leading-relaxed px-2">
                                        «Kitob — zamonlar to'lqinida suzuvchi va o'zining qimmatbaho yukini avlodlarga eltuvchi hikmat kemasidir.»
                                    </blockquote>
                                    <div class="w-10 h-px bg-amber-700/30 mx-auto"></div>
                                </div>

                                <!-- Bottom Metadata Grid -->
                                <div class="relative z-0 border-t border-[#D5CBB9] pt-2 space-y-1 text-[8.5px] font-mono text-[#6E7B8B]">
                                    <div class="flex items-center justify-between">
                                        <span>NASHR SERIYASI:</span>
                                        <span class="text-[#1A1D24] font-semibold">№ 084 · HAFTALIK</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span>MUQOVA:</span>
                                        <span class="text-[#1A1D24] font-semibold">Klassik Qattiq Muqova</span>
                                    </div>
                                    <div class="flex items-center justify-between pt-0.5 border-t border-[#D5CBB9]/60 text-[8px] text-[#8B9BAD]">
                                        <span>✦ ASLIYATGA MOS</span>
                                        <span>TOSHKENT · 2026 ✦</span>
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- 3D Book Interactive Controls & Page Flip Toolbar -->
                    <div class="scrolly-book-controls flex items-center justify-between gap-2 mt-4 px-1 w-full max-w-[205px] sm:max-w-[260px] lg:max-w-[320px] select-none">
                        <button type="button" id="scrolly-prev-page-btn"
                                class="px-2.5 py-1 rounded-full bg-ink-900 border border-ink-border text-mist hover:text-paper hover:border-amber-400/50 text-xs font-mono transition-colors flex items-center gap-1 active:scale-95 disabled:opacity-30 disabled:pointer-events-none"
                                title="Oldingi bet">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span class="text-[11px]">Oldingi</span>
                        </button>

                        <div class="px-2.5 py-1 rounded-full bg-ink-900/90 border border-ink-border flex items-center gap-2 shadow-sm">
                            <span id="scrolly-page-label" class="font-mono text-[11px] text-amber-400 font-semibold tracking-wider">Muqova</span>
                            <div class="flex items-center gap-1">
                                <span class="scrolly-dot w-1.5 h-1.5 rounded-full bg-amber-400 transition-all cursor-pointer" data-page="0" title="Muqova"></span>
                                <span class="scrolly-dot w-1.5 h-1.5 rounded-full bg-ink-700 transition-all cursor-pointer" data-page="1" title="1-bet"></span>
                                <span class="scrolly-dot w-1.5 h-1.5 rounded-full bg-ink-700 transition-all cursor-pointer" data-page="2" title="2-bet"></span>
                                <span class="scrolly-dot w-1.5 h-1.5 rounded-full bg-ink-700 transition-all cursor-pointer" data-page="3" title="3-bet (Xotima)"></span>
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
                <div class="lg:col-span-6 scrolly-content-panel space-y-6">
                    
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
    <section id="features" class="py-20 md:py-28 relative overflow-hidden max-w-full">
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
                        <div class="flex flex-col items-end gap-1.5" title="{{ __('site.common.streak') }}: {{ $displayStreak }} {{ __('site.common.days') }}">
                            <div class="flex items-end gap-1 h-7">
                                @for($i = 1; $i <= 5; $i++)
                                    @php
                                        $isLit = $displayStreak >= $i;
                                        $barHeights = [1 => 'h-2.5', 2 => 'h-3.5', 3 => 'h-4.5', 4 => 'h-5.5', 5 => 'h-7'];
                                        $hClass = $barHeights[$i] ?? 'h-5';
                                    @endphp
                                    <div class="w-2 {{ $hClass }} rounded-xs transition-all duration-300 {{ $isLit ? 'bg-amber-400 shadow-[0_0_8px_rgba(251,191,36,0.6)]' : 'bg-ink-800 border border-ink-border/50 opacity-40' }}"
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
    <section class="py-20 md:py-28 border-t border-ink-border bg-ink-900/30 overflow-hidden max-w-full">
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
        function initHomePageAnimations() {
            if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
                setTimeout(initHomePageAnimations, 50);
                return;
            }

            gsap.registerPlugin(ScrollTrigger);

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
                window.initBookRevealSection = function() {
                    const bookRevealSection = document.getElementById('book-reveal-section');
                    if (!bookRevealSection || typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;

                    // Clean up existing trigger if already present (e.g. on resize or PJAX)
                    const existing = ScrollTrigger.getById('book-reveal-st');
                    if (existing) {
                        existing.kill();
                    }

                    const isDesktop = window.matchMedia("(min-width: 1024px)").matches;
                    const prefersReduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

                    const cover = document.querySelector('.scrolly-book-cover');
                    const leaf1 = document.querySelector('.scrolly-leaf-1');
                    const leaf2 = document.querySelector('.scrolly-leaf-2');
                    const stage = document.getElementById('scrolly-book-stage');
                    const shadow = document.querySelector('.scrolly-book-shadow');
                    const contentPanel = document.querySelector('.scrolly-content-panel');
                    const statsRow = document.querySelector('.scrolly-stats-row');

                    const dragHint = document.getElementById('scrolly-drag-hint');
                    const pageLabel = document.getElementById('scrolly-page-label');
                    const prevBtn = document.getElementById('scrolly-prev-page-btn');
                    const nextBtn = document.getElementById('scrolly-next-page-btn');
                    const dots = document.querySelectorAll('.scrolly-dot');

                    const maxPages = 3;
                    const pageLabels = ['Muqova', '1 / 3-bet', '2 / 3-bet', '3 / 3-bet (Xotima)'];
                    // Progress checkpoints for each page along the 0.0 -> 1.0 timeline
                    const pageProgressTargets = [0.0, 0.28, 0.54, 0.80];
                    let currentPage = 0;
                    let isNavigatingByCode = false;

                    function updateUI(pageIndex) {
                        currentPage = Math.max(0, Math.min(maxPages, pageIndex));
                        if (pageLabel) pageLabel.textContent = pageLabels[currentPage] || `${currentPage}-bet`;
                        if (prevBtn) prevBtn.disabled = (currentPage === 0);
                        if (nextBtn) {
                            if (currentPage === maxPages) {
                                nextBtn.innerHTML = `<span class="text-[11px] text-amber-400 font-bold">O'qish ➔</span>`;
                            } else {
                                nextBtn.innerHTML = `<span class="text-[11px]">Keyingi</span><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>`;
                            }
                        }
                        dots.forEach((dot, idx) => {
                            if (idx === currentPage) {
                                dot.className = 'scrolly-dot w-2 h-2 rounded-full bg-amber-400 transition-all cursor-pointer ring-2 ring-amber-400/40';
                            } else {
                                dot.className = 'scrolly-dot w-1.5 h-1.5 rounded-full bg-ink-700 hover:bg-ink-600 transition-all cursor-pointer';
                            }
                        });
                    }

                    function updateControlsFromProgress(p) {
                        let page = 0;
                        if (p >= 0.70) page = 3;
                        else if (p >= 0.44) page = 2;
                        else if (p >= 0.16) page = 1;
                        else page = 0;

                        if (page !== currentPage) {
                            updateUI(page);
                        }
                    }

                    // Reset initial positions & clear old inline transforms
                    gsap.set(cover, { rotateY: 0, transformOrigin: "left center", zIndex: 40 });
                    if (leaf1) gsap.set(leaf1, { rotateY: 0, transformOrigin: "left center", zIndex: 30 });
                    if (leaf2) gsap.set(leaf2, { rotateY: 0, transformOrigin: "left center", zIndex: 20 });
                    gsap.set(stage, { x: 0 });
                    gsap.set(shadow, { scaleX: 1, opacity: 0.6, x: 0 });
                    gsap.set(contentPanel, { opacity: isDesktop ? 0 : 1, x: isDesktop ? 45 : 0 });
                    gsap.set(statsRow, { opacity: isDesktop ? 0 : 1, y: isDesktop ? 25 : 0 });

                    const statElements = document.querySelectorAll('.scrolly-stat-num');
                    const statTargets = Array.from(statElements).map(el => parseInt(el.getAttribute('data-target') || '0', 10));
                    const statProgressObj = { progress: 0 };

                    // ── MASTER TIMELINE: SINGLE SOURCE OF TRUTH FOR ALL LEAVES ──
                    const masterTl = gsap.timeline({
                        paused: prefersReduced,
                        onUpdate: function() {
                            updateControlsFromProgress(this.progress());
                        }
                    });

                    // Hint pill fades out as user scrolls
                    if (dragHint) {
                        masterTl.to(dragHint, {
                            opacity: 0,
                            duration: 0.08,
                            ease: 'power1.out'
                        }, 0.04);
                    }

                    // 1. Cover opens (0.00 -> 0.28)
                    masterTl.to(cover, {
                        rotateY: -142,
                        ease: 'power1.inOut',
                        duration: 0.28,
                    }, 0);

                    // Drop cover z-index behind right leaves as it passes -90deg
                    masterTl.set(cover, { zIndex: 12 }, 0.14);

                    // Gentle X adjustment to keep open book centered
                    const shiftX = isDesktop ? 40 : Math.round(Math.min(50, Math.max(25, (window.innerWidth - 205) * 0.28)));
                    masterTl.to(stage, {
                        x: shiftX,
                        ease: 'power1.inOut',
                        duration: 0.28
                    }, 0);

                    masterTl.to(shadow, {
                        scaleX: isDesktop ? 1.25 : 1.15,
                        x: isDesktop ? -24 : -15,
                        opacity: 0.9,
                        ease: 'power1.inOut',
                        duration: 0.28
                    }, 0);

                    if (isDesktop) {
                        masterTl.to(contentPanel, {
                            opacity: 1,
                            x: 0,
                            ease: 'power2.out',
                            duration: 0.22
                        }, 0.06);
                    }

                    // 2. Leaf 1 flips (0.28 -> 0.54)
                    if (leaf1) {
                        masterTl.to(leaf1, {
                            rotateY: -148,
                            ease: 'power1.inOut',
                            duration: 0.26
                        }, 0.28);

                        masterTl.set(leaf1, { zIndex: 14 }, 0.41);

                        masterTl.to(cover, {
                            rotateY: -152,
                            ease: 'power1.inOut',
                            duration: 0.26
                        }, 0.28);
                    }

                    // 3. Leaf 2 flips (0.54 -> 0.80)
                    if (leaf2) {
                        masterTl.to(leaf2, {
                            rotateY: -148,
                            ease: 'power1.inOut',
                            duration: 0.26
                        }, 0.54);

                        masterTl.set(leaf2, { zIndex: 16 }, 0.67);

                        if (leaf1) {
                            masterTl.to(leaf1, {
                                rotateY: -158,
                                ease: 'power1.inOut',
                                duration: 0.26
                            }, 0.54);
                        }
                    }

                    // 4. Stats row count up (0.80 -> 1.00)
                    masterTl.to(statsRow, {
                        opacity: 1,
                        y: 0,
                        ease: 'power2.out',
                        duration: 0.20
                    }, 0.80);

                    masterTl.to(statProgressObj, {
                        progress: 1,
                        ease: 'none',
                        duration: 0.20,
                        onUpdate: () => {
                            statElements.forEach((el, idx) => {
                                const targetVal = statTargets[idx] || 0;
                                const currentVal = Math.round(statProgressObj.progress * targetVal);
                                el.textContent = currentVal.toLocaleString('en-US');
                            });
                        }
                    }, 0.80);

                    // ── ATTACH SCROLLTRIGGER (DESKTOP PIN + MOBILE FLUID SCRUB) ──
                    if (!prefersReduced && typeof ScrollTrigger !== 'undefined') {
                        if (isDesktop) {
                            ScrollTrigger.create({
                                id: 'book-reveal-st',
                                animation: masterTl,
                                trigger: '#book-reveal-section',
                                start: 'top top',
                                end: '+=200%',
                                pin: true,
                                scrub: 0.8,
                                anticipatePin: 1,
                                invalidateOnRefresh: true
                            });
                        } else {
                            // Mobile / Tablet (< 1024px): Smooth scroll scrub as book travels through viewport
                            ScrollTrigger.create({
                                id: 'book-reveal-st',
                                animation: masterTl,
                                trigger: stage || '#book-reveal-section',
                                start: 'top 85%',
                                end: 'bottom 15%',
                                scrub: 0.7,
                                invalidateOnRefresh: true
                            });
                        }
                    } else if (prefersReduced) {
                        // Reduced motion fallback
                        gsap.set(contentPanel, { opacity: 1, x: 0 });
                        gsap.set(statsRow, { opacity: 1, y: 0 });
                        statElements.forEach((el, idx) => {
                            const targetVal = statTargets[idx] || 0;
                            el.textContent = targetVal.toLocaleString('en-US');
                        });
                    }

                    // ── PROGRAMMATIC NAVIGATION (BUTTONS, DOTS, DOGEAR) ──
                    function goToPage(targetPage, duration = 0.65) {
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

                        if (st && isDesktop) {
                            isNavigatingByCode = true;
                            const targetScroll = st.start + targetProg * (st.end - st.start);
                            const scrollObj = { y: window.scrollY };
                            gsap.to(scrollObj, {
                                y: targetScroll,
                                duration: duration,
                                ease: "power2.out",
                                onUpdate: () => {
                                    window.scrollTo(0, scrollObj.y);
                                },
                                onComplete: () => {
                                    setTimeout(() => { isNavigatingByCode = false; }, 100);
                                }
                            });
                        } else {
                            gsap.to(masterTl, {
                                progress: targetProg,
                                duration: duration,
                                ease: "power2.out"
                            });
                        }
                    }

                    window.flipBookToPage = function(p) { goToPage(p); };
                    window.flipBookNext = function() { goToPage(currentPage + 1); };
                    window.flipBookPrev = function() { goToPage(currentPage - 1); };

                    if (prevBtn) {
                        prevBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            goToPage(currentPage - 1);
                        });
                    }

                    if (nextBtn) {
                        nextBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            goToPage(currentPage + 1);
                        });
                    }

                    dots.forEach((dot) => {
                        dot.addEventListener('click', (e) => {
                            e.preventDefault();
                            const p = parseInt(dot.getAttribute('data-page') || '0', 10);
                            goToPage(p);
                        });
                    });

                    // ── MOUSE / TOUCH DRAG & SWIPE GESTURE ENGINE ──
                    if (stage) {
                        let isPointerDown = false;
                        let startX = 0;
                        let startY = 0;
                        let currentX = 0;
                        let currentY = 0;
                        let startTime = 0;
                        let startProg = 0;
                        let isHorizontalDrag = false;

                        stage.addEventListener('pointerdown', (e) => {
                            if (e.target.closest('a') || e.target.closest('button') || e.target.closest('.scrolly-dogear')) return;

                            isPointerDown = true;
                            isHorizontalDrag = false;
                            startX = e.clientX;
                            startY = e.clientY;
                            currentX = e.clientX;
                            currentY = e.clientY;
                            startTime = performance.now();
                            startProg = masterTl.progress();
                            stage.classList.add('is-dragging');
                        });

                        window.addEventListener('pointermove', (e) => {
                            if (!isPointerDown) return;

                            currentX = e.clientX;
                            currentY = e.clientY;
                            const diffX = currentX - startX;
                            const diffY = currentY - startY;

                            if (!isHorizontalDrag) {
                                if (Math.abs(diffX) > 8 && Math.abs(diffX) > Math.abs(diffY)) {
                                    isHorizontalDrag = true;
                                } else if (Math.abs(diffY) > 10) {
                                    isPointerDown = false;
                                    stage.classList.remove('is-dragging');
                                    return;
                                }
                            }

                            if (isHorizontalDrag) {
                                e.preventDefault();
                                const stageWidth = stage.offsetWidth || 280;
                                const deltaProg = -(diffX / stageWidth) * 0.28;
                                const newProg = Math.max(0, Math.min(1, startProg + deltaProg));
                                masterTl.progress(newProg);
                            }
                        }, { passive: false });

                        function onPointerRelease() {
                            if (!isPointerDown) return;
                            isPointerDown = false;
                            stage.classList.remove('is-dragging');

                            if (!isHorizontalDrag) {
                                // Simple tap: right half = next, left half = prev
                                const rect = stage.getBoundingClientRect();
                                const tapRatio = (startX - rect.left) / rect.width;
                                if (tapRatio > 0.6) goToPage(currentPage + 1);
                                else if (tapRatio < 0.3) goToPage(currentPage - 1);
                                return;
                            }

                            const diffX = currentX - startX;
                            const elapsed = performance.now() - startTime;
                            const velocity = Math.abs(diffX) / (elapsed || 1);

                            if (diffX < -35 || (diffX < -15 && velocity > 0.3)) {
                                goToPage(currentPage + 1);
                            } else if (diffX > 35 || (diffX > 15 && velocity > 0.3)) {
                                goToPage(currentPage - 1);
                            } else {
                                goToPage(currentPage, 0.4);
                            }
                        }

                        window.addEventListener('pointerup', onPointerRelease);
                        window.addEventListener('pointercancel', onPointerRelease);
                    }

                    updateUI(0);
                };

                // Initialize immediately
                window.initBookRevealSection();

                // Responsive listener for viewport breakpoint changes & device orientation
                try {
                    window.matchMedia("(min-width: 1024px)").addEventListener('change', () => {
                        window.initBookRevealSection();
                        setTimeout(() => ScrollTrigger.refresh(), 50);
                    });
                    window.addEventListener('orientationchange', () => {
                        setTimeout(() => {
                            window.initBookRevealSection();
                            ScrollTrigger.refresh();
                        }, 150);
                    });
                } catch(e) {}

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
            } catch(e) {
                console.warn('Animation init error:', e);
            }
        }

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
        __kitobxonVisibilityFailsafe();
        window.addEventListener('scroll', __kitobxonVisibilityFailsafe, { passive: true });
        setInterval(__kitobxonVisibilityFailsafe, 1500);
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
