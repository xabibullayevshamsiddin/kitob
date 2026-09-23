<!DOCTYPE html>
<html lang="uz" class="scroll-smooth dark" x-data="{ darkMode: true }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') — @yield('title') | Kitobxon</title>

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
                        'card-depth': '0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.08)',
                        'glow-amber': '0 0 45px -5px rgba(245, 158, 11, 0.35)',
                    },
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- GSAP for Pro-level Physics Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .noise-bg {
            background-image: radial-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 0);
            background-size: 24px 24px;
        }

        .error-card {
            position: relative;
            background: rgba(14, 20, 33, 0.85);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2rem;
            overflow: hidden;
            transition: border-color 0.4s ease, box-shadow 0.4s ease, transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: transform;
        }
        .error-card::before {
            content: '';
            position: absolute;
            top: var(--mouse-y, -1000px);
            left: var(--mouse-x, -1000px);
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(251, 191, 36, 0.16) 0%, transparent 70%);
            transform: translate(-50%, -50%);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }
        .error-card:hover::before {
            opacity: 1;
        }

        @keyframes orbFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -25px) scale(1.08); }
        }
        .orb-float-1 { animation: orbFloat 16s ease-in-out infinite; }
        .orb-float-2 { animation: orbFloat 22s ease-in-out infinite reverse; }

        /* Huge Glitch / Glow Code Styling */
        .code-watermark {
            font-size: clamp(6rem, 16vw, 12rem);
            line-height: 0.9;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0.02) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            user-select: none;
        }
    </style>
</head>
<body class="bg-ink-950 text-slate-200 font-sans selection:bg-amber-400 selection:text-ink-950 antialiased min-h-screen flex flex-col justify-between relative overflow-x-hidden noise-bg">

    <!-- ── Page Transition & Loader ── -->
    @include('components.page-loader')

    <!-- Ambient Glowing Orbs -->
    <div class="fixed top-[-100px] left-1/2 -translate-x-1/2 w-[900px] h-[500px] bg-gradient-to-b from-amber-500/12 via-indigo-600/8 to-transparent rounded-full blur-[140px] pointer-events-none -z-10 orb-float-1"></div>
    <div class="fixed bottom-[-120px] right-[-100px] w-[650px] h-[650px] bg-indigo-900/15 rounded-full blur-[160px] pointer-events-none -z-10 orb-float-2"></div>

    <div id="smooth-page-wrapper" class="flex-1 flex flex-col justify-between">
    <!-- ── Minimal Top Header ── -->
    <header id="site-header" class="w-full backdrop-blur-xl bg-ink-950/70 border-b border-white/[0.07] z-40">
        <div class="max-w-7xl mx-auto px-6 h-[70px] flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-ink-950 font-black text-xl shadow-lg shadow-amber-500/20 group-hover:scale-105 group-hover:rotate-3 transition-all duration-300">
                    📖
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-white text-base tracking-tight group-hover:text-amber-400 transition-colors">Kitobxon</span>
                    <span class="font-mono text-[10px] text-slate-400 uppercase tracking-widest">Platforma</span>
                </div>
            </a>

            <a href="{{ route('home') }}" 
               class="px-4 py-2 rounded-xl bg-ink-900/80 hover:bg-ink-800 border border-white/10 text-xs font-mono text-amber-400 transition-all active:scale-95 flex items-center gap-2">
                <span>← Bosh sahifaga qaytish</span>
            </a>
        </div>
    </header>

    <!-- ── Center Hero Error Card ── -->
    <main class="flex-1 flex items-center justify-center p-6 md:p-12 z-10">
        <div id="error-tilt-card" class="error-card max-w-2xl w-full p-8 sm:p-12 text-center shadow-card-depth">
            
            <!-- Large Numeric Watermark & Floating Icon -->
            <div class="relative flex items-center justify-center mb-6">
                <div class="code-watermark font-mono font-black tracking-tighter absolute">
                    @yield('code')
                </div>
                
                <div class="error-anim-item relative z-10 w-24 h-24 rounded-3xl bg-gradient-to-br from-ink-900 to-ink-950 border border-amber-400/30 flex items-center justify-center text-4xl shadow-2xl shadow-amber-500/10">
                    @yield('icon')
                </div>
            </div>

            <!-- Error Content -->
            <div class="space-y-4 relative z-10 mt-4">
                
                <div class="error-anim-item inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-400/10 border border-amber-400/25 text-amber-400 font-mono text-xs tracking-wider">
                    <span>@yield('badge')</span>
                </div>

                <h1 class="error-anim-item text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    @yield('title')
                </h1>

                <p class="error-anim-item text-slate-300 text-sm sm:text-base max-w-lg mx-auto leading-relaxed">
                    @yield('message')
                </p>

                <!-- Actions Button Row -->
                <div class="error-anim-item pt-4 flex flex-wrap items-center justify-center gap-4">
                    @yield('actions')
                </div>

            </div>

        </div>
    </main>

    <!-- ── Error Switcher Bar (Tezkor o'tish paneli) ── -->
    <div class="w-full border-t border-white/[0.08] bg-ink-900/60 backdrop-blur-md py-4 px-6 z-20">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-mono">
            <span class="text-slate-400">✦ XATOLIKLAR DIZAYNINI SINOVDAN O'TKAZISH:</span>
            
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ url('/errors/404') }}" class="px-2.5 py-1 rounded-lg {{ request()->is('*404') ? 'bg-amber-400 text-ink-950 font-bold' : 'bg-ink-950/80 text-slate-300 hover:text-white border border-white/10' }}">404 (Topilmadi)</a>
                <a href="{{ url('/errors/403') }}" class="px-2.5 py-1 rounded-lg {{ request()->is('*403') ? 'bg-amber-400 text-ink-950 font-bold' : 'bg-ink-950/80 text-slate-300 hover:text-white border border-white/10' }}">403 (Ruxsatsiz)</a>
                <a href="{{ url('/errors/500') }}" class="px-2.5 py-1 rounded-lg {{ request()->is('*500') ? 'bg-amber-400 text-ink-950 font-bold' : 'bg-ink-950/80 text-slate-300 hover:text-white border border-white/10' }}">500 (Server)</a>
                <a href="{{ url('/errors/419') }}" class="px-2.5 py-1 rounded-lg {{ request()->is('*419') ? 'bg-amber-400 text-ink-950 font-bold' : 'bg-ink-950/80 text-slate-300 hover:text-white border border-white/10' }}">419 (Sessiya)</a>
                <a href="{{ url('/errors/429') }}" class="px-2.5 py-1 rounded-lg {{ request()->is('*429') ? 'bg-amber-400 text-ink-950 font-bold' : 'bg-ink-950/80 text-slate-300 hover:text-white border border-white/10' }}">429 (Cheklov)</a>
                <a href="{{ url('/errors/503') }}" class="px-2.5 py-1 rounded-lg {{ request()->is('*503') ? 'bg-amber-400 text-ink-950 font-bold' : 'bg-ink-950/80 text-slate-300 hover:text-white border border-white/10' }}">503 (Ta'mirlash)</a>
            </div>
        </div>
    </div>
    </div>

    <!-- ── Advanced Motion & Physics Script ── -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Header entrance
            gsap.fromTo('#site-header',
                { y: -25, opacity: 0 },
                { y: 0, opacity: 1, duration: 0.7, ease: "power3.out" }
            );

            // 2. Error Card Elements Stagger Reveal
            gsap.fromTo('.error-anim-item',
                { opacity: 0, y: 35, filter: 'blur(8px)', scale: 0.96 },
                {
                    opacity: 1,
                    y: 0,
                    filter: 'blur(0px)',
                    scale: 1,
                    duration: 0.85,
                    stagger: 0.1,
                    ease: "power4.out",
                    clearProps: "transform,scale,filter"
                }
            );

            // 3. Dynamic Cursor Spotlight Glow
            const card = document.getElementById('error-tilt-card');
            if (card) {
                card.addEventListener('mousemove', e => {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    card.style.setProperty('--mouse-x', `${x}px`);
                    card.style.setProperty('--mouse-y', `${y}px`);

                    // 3D Tilt calculation
                    const centerX = x - rect.width / 2;
                    const centerY = y - rect.height / 2;
                    const rotX = -(centerY / rect.height) * 10;
                    const rotY = (centerX / rect.width) * 10;
                    card.style.transform = `perspective(1000px) rotateX(${rotX}deg) rotateY(${rotY}deg) scale3d(1.01, 1.01, 1.01)`;
                });

                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
                });
            }
        });
    </script>
</body>
</html>
