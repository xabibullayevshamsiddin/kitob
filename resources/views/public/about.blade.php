<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark" x-data="{ mobileMenu: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ __('site.about.meta') }}">
    <title>{{ __('site.about.title') }} — Kitobxon</title>

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

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .noise-bg {
            background-image: radial-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 0);
            background-size: 24px 24px;
        }
        .spotlight-card {
            position: relative;
            background: rgba(17, 23, 38, 0.75);
            backdrop-filter: blur(16px);
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
            width: 360px;
            height: 360px;
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
            transform: translateY(-5px);
            box-shadow: 0 20px 45px -15px rgba(0, 0, 0, 0.7), 0 0 25px -5px rgba(251, 191, 36, 0.18);
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

    <div id="smooth-page-wrapper">
    <!-- ── Header ── -->
    <x-nav.main-header />

    <!-- ── Hero Section (Chiqib keluvchi Editorial Missiya) ── -->
    <section class="py-20 md:py-28 noise-bg">
        <div class="max-w-5xl mx-auto px-6 text-center space-y-6">
            <div class="about-hero-item inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-400/10 border border-amber-400/25 text-amber-400 font-mono text-xs tracking-wider shadow-sm">
                <span>{{ __('site.about.badge') }}</span>
            </div>
            
            <h1 class="about-hero-item text-4xl sm:text-6xl lg:text-7xl font-extrabold text-white tracking-tight leading-[1.08]">
                {{ __('site.about.hero_1') }}<br class="hidden sm:block">
                <span class="text-amber-400 italic font-serif">{{ __('site.about.hero_b') }}</span>{{ __('site.about.hero_2') }}
            </h1>
            
            <p class="about-hero-item text-base sm:text-xl text-slate-300 max-w-2xl mx-auto leading-relaxed">
                {{ __('site.about.hero_sub') }}
            </p>

            <div class="about-hero-item pt-4 flex flex-wrap justify-center gap-4">
                <a href="{{ route('register') }}" class="px-7 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider transition-all duration-200 active:scale-95 shadow-lg shadow-amber-500/25">
                    {{ __('site.about.join_cta') }}
                </a>
                <a href="{{ route('books.public') }}" class="px-6 py-3.5 rounded-xl bg-ink-800/80 hover:bg-ink-700 text-slate-200 font-semibold text-xs border border-white/10 transition-all duration-200 active:scale-95">
                    {{ __('site.about.books_list') }}
                </a>
            </div>
        </div>
    </section>

    <!-- ── Key Stats Row (ScrollTrigger Rolling Counters) ── -->
    <section class="py-14 border-y border-white/[0.08] bg-ink-900/50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                
                <div class="about-stat-card p-6 sm:p-8 rounded-3xl bg-ink-950/70 border border-white/10 hover:border-amber-400/30 transition-all spotlight-card">
                    <p class="text-4xl sm:text-5xl font-black text-amber-400 font-mono tracking-tight">
                        <span class="counter-element" data-target="{{ $booksCount ?? 0 }}">{{ $booksCount ?? 0 }}</span>
                    </p>
                    <p class="text-xs text-slate-400 mt-2 uppercase tracking-wider font-mono">{{ __('site.about.stat_books') }}</p>
                </div>

                <div class="about-stat-card p-6 sm:p-8 rounded-3xl bg-ink-950/70 border border-white/10 hover:border-amber-400/30 transition-all spotlight-card">
                    <p class="text-4xl sm:text-5xl font-black text-white font-mono tracking-tight">
                        <span class="counter-element" data-target="{{ $usersCount ?? 0 }}">{{ $usersCount ?? 0 }}</span>
                    </p>
                    <p class="text-xs text-slate-400 mt-2 uppercase tracking-wider font-mono">{{ __('site.about.stat_readers') }}</p>
                </div>

                <div class="about-stat-card p-6 sm:p-8 rounded-3xl bg-ink-950/70 border border-white/10 hover:border-amber-400/30 transition-all spotlight-card">
                    <p class="text-4xl sm:text-5xl font-black text-emerald-400 font-mono tracking-tight">
                        <span class="counter-element" data-target="{{ $maxStreak ?? 0 }}">{{ $maxStreak ?? 0 }}</span> {{ __('site.common.days') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-2 uppercase tracking-wider font-mono">{{ __('site.about.stat_streak') }}</p>
                </div>

                <div class="about-stat-card p-6 sm:p-8 rounded-3xl bg-ink-950/70 border border-white/10 hover:border-amber-400/30 transition-all spotlight-card">
                    <p class="text-4xl sm:text-5xl font-black text-indigo-400 font-mono tracking-tight">
                        <span class="counter-element" data-target="{{ $totalMinutes ?? 0 }}">{{ $totalMinutes ?? 0 }}</span>
                    </p>
                    <p class="text-xs text-slate-400 mt-2 uppercase tracking-wider font-mono">{{ __('site.about.stat_minutes') }}</p>
                </div>

            </div>
        </div>
    </section>

    <!-- ── Core Values (ScrollTrigger Staggered Bento) ── -->
    <section class="py-24 md:py-32">
        <div class="max-w-7xl mx-auto px-6 space-y-12">
            <div class="values-header space-y-2">
                <span class="text-xs font-mono text-amber-400 uppercase tracking-widest block">{{ __('site.about.values_badge') }}</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">{{ __('site.about.values_title') }}</h2>
                <p class="text-sm text-slate-400">{{ __('site.about.values_sub') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="value-card spotlight-card rounded-3xl p-8 sm:p-10 space-y-5">
                    <div class="w-14 h-14 rounded-2xl bg-amber-400/10 border border-amber-400/25 text-amber-400 flex items-center justify-center text-3xl">
                        💎
                    </div>
                    <h3 class="text-2xl font-bold text-white tracking-tight">{{ __('site.about.value1_title') }}</h3>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        {{ __('site.about.value1_sub') }}
                    </p>
                    <div class="pt-2 text-xs font-mono text-amber-400">{{ __('site.about.value1_tag') }}</div>
                </div>

                <div class="value-card spotlight-card rounded-3xl p-8 sm:p-10 space-y-5">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 border border-indigo-500/25 text-indigo-400 flex items-center justify-center text-3xl">
                        ⚡️
                    </div>
                    <h3 class="text-2xl font-bold text-white tracking-tight">{{ __('site.about.value2_title') }}</h3>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        {{ __('site.about.value2_sub') }}
                    </p>
                    <div class="pt-2 text-xs font-mono text-indigo-400">{{ __('site.about.value2_tag') }}</div>
                </div>

                <div class="value-card spotlight-card rounded-3xl p-8 sm:p-10 space-y-5">
                    <div class="w-14 h-14 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-rose-400 flex items-center justify-center text-3xl">
                        🤝
                    </div>
                    <h3 class="text-2xl font-bold text-white tracking-tight">{{ __('site.about.value3_title') }}</h3>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        {{ __('site.about.value3_sub') }}
                    </p>
                    <div class="pt-2 text-xs font-mono text-rose-400">{{ __('site.about.value3_tag') }}</div>
                </div>

            </div>
        </div>
    </section>

    <!-- ── Team Section (Interactive Expert Cards) ── -->
    <section class="py-24 border-t border-white/[0.07] bg-ink-900/40">
        <div class="max-w-7xl mx-auto px-6 space-y-12">
            <div class="team-header space-y-2">
                <span class="text-xs font-mono text-amber-400 uppercase tracking-widest block">{{ __('site.about.team_badge') }}</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">{{ __('site.about.team_title') }}</h2>
                <p class="text-sm text-slate-400">{{ __('site.about.team_sub') }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <div class="team-card spotlight-card rounded-3xl p-6 flex items-center gap-5">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-ink-950 font-black text-2xl shrink-0 shadow-lg shadow-amber-500/20">
                        XS
                    </div>
                    <div>
                        <h4 class="font-bold text-white text-base">Xabibullayev Shamsiddin</h4>
                        <p class="text-xs text-amber-400 font-mono mt-0.5">{{ __('site.about.t1_role') }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ __('site.about.t1_desc') }}</p>
                    </div>
                </div>

                <div class="team-card spotlight-card rounded-3xl p-6 flex items-center gap-5">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-700 flex items-center justify-center text-white font-bold text-2xl shrink-0 shadow-lg shadow-indigo-600/20">
                        DK
                    </div>
                    <div>
                        <h4 class="font-bold text-white text-base">Dilshod Karimov</h4>
                        <p class="text-xs text-indigo-300 font-mono mt-0.5">{{ __('site.about.t2_role') }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ __('site.about.t2_desc') }}</p>
                    </div>
                </div>

                <div class="team-card spotlight-card rounded-3xl p-6 flex items-center gap-5">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-700 flex items-center justify-center text-white font-bold text-2xl shrink-0 shadow-lg shadow-emerald-600/20">
                        NR
                    </div>
                    <div>
                        <h4 class="font-bold text-white text-base">Nodira Rahimova</h4>
                        <p class="text-xs text-emerald-300 font-mono mt-0.5">{{ __('site.about.t3_role') }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ __('site.about.t3_desc') }}</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ── Universal Footer ── -->
    <x-nav.main-footer />
    </div>

    <!-- ── Advanced Motion & Physics Script ── -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            gsap.registerPlugin(ScrollTrigger);

            // 1. Header Entrance
            gsap.fromTo('#site-header',
                { y: -30, opacity: 0 },
                { y: 0, opacity: 1, duration: 0.8, ease: "power3.out" }
            );

            // 2. Hero Staggered Entrance Reveal
            gsap.fromTo('.about-hero-item', 
                { opacity: 0, y: 40, filter: 'blur(8px)', scale: 0.96 },
                { 
                    opacity: 1, 
                    y: 0, 
                    filter: 'blur(0px)',
                    scale: 1,
                    duration: 0.9, 
                    stagger: 0.12, 
                    ease: "power4.out",
                    clearProps: "transform,scale,filter"
                }
            );

            // 3. Stats Cards Stagger Reveal
            gsap.fromTo('.about-stat-card',
                { opacity: 0, y: 35, scale: 0.94 },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.8,
                    stagger: 0.12,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: '.about-stat-card',
                        start: "top 85%",
                    },
                    clearProps: "transform,scale"
                }
            );

            // 4. Values Section Reveal
            gsap.fromTo('.values-header',
                { opacity: 0, y: 30 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.8,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: '.values-header',
                        start: "top 85%",
                    }
                }
            );

            gsap.fromTo('.value-card',
                { opacity: 0, y: 50, scale: 0.95 },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.85,
                    stagger: 0.15,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: '.value-card',
                        start: "top 80%",
                    },
                    clearProps: "transform,scale"
                }
            );

            // 5. Team Cards Stagger Reveal
            gsap.fromTo('.team-header',
                { opacity: 0, y: 30 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.8,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: '.team-header',
                        start: "top 85%",
                    }
                }
            );

            gsap.fromTo('.team-card',
                { opacity: 0, y: 40, scale: 0.95 },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.8,
                    stagger: 0.15,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: '.team-card',
                        start: "top 85%",
                    },
                    clearProps: "transform,scale"
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
                            duration: 1.5,
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

            // 7. Dynamic Spotlight Cursor Glow
            document.querySelectorAll('.spotlight-card').forEach(card => {
                card.addEventListener('mousemove', e => {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    card.style.setProperty('--mouse-x', `${x}px`);
                    card.style.setProperty('--mouse-y', `${y}px`);
                });
            });
        });
    
        // ── Fail-safe: GSAP yuklanmasa yoki xato bo'lsa kontent ko'rinadi ──
        const __kitobxonSeen = new WeakMap();
        function __kitobxonVisibilityFailsafe() {
            document.querySelectorAll('.hero-anim-item, .bento-header, .bento-card, .step-card, .cta-box, .legal-content, .about-stat-card, .value-card, .team-card, .contact-form-col, .contact-info-card, .faq-card, .book-card, .error-anim-item').forEach(el => {
                const r = el.getBoundingClientRect();
                if (r.height === 0) return;
                const s = getComputedStyle(el);
                if (parseFloat(s.opacity) >= 0.05) { __kitobxonSeen.delete(el); return; }

                // GSAP umuman yuklanmagan bo'lsa — darhol ochamiz
                if (typeof gsap === 'undefined') {
                    el.style.opacity = '1'; el.style.filter = 'none'; el.style.transform = 'none';
                    return;
                }

                const reached = r.top < window.innerHeight + 100; // ekranda yoki undan yuqorida
                const first = __kitobxonSeen.get(el);
                // 4+ soniya opacity:0 da qotgan bo'lsa — animatsiya ishlamagan: ochamiz
                if (reached || (first && Date.now() - first > 4000)) {
                    el.style.opacity = '1'; el.style.filter = 'none'; el.style.transform = 'none';
                    __kitobxonSeen.delete(el);
                } else if (!first) {
                    __kitobxonSeen.set(el, Date.now());
                }
            });
        }
        // Darhol tekshiruv
        __kitobxonVisibilityFailsafe();
        // Scroll paytida darhol sinxron tekshiruv (taymer throttling ta'sir qilmaydi)
        window.addEventListener('scroll', __kitobxonVisibilityFailsafe, { passive: true });
        // Zaxira interval (PJAX navigatsiyadan keyin ham ishlashi uchun)
        setInterval(__kitobxonVisibilityFailsafe, 2000);
    </script>

</body>
</html>
