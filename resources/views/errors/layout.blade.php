<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') — @yield('title') | Kitobxon</title>

    @include('partials.design-system')

    <!-- GSAP for Subtle Entrance Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        .code-watermark {
            font-size: clamp(5rem, 14vw, 10rem);
            line-height: 0.9;
            color: rgba(240, 237, 230, 0.04);
            user-select: none;
        }
    </style>
</head>
<body class="bg-ink-950 text-paper font-sans selection:bg-amber-500 selection:text-ink-950 antialiased min-h-screen flex flex-col justify-between relative overflow-x-hidden ks-grain">

    <!-- ── Page Transition & Loader ── -->
    @include('components.page-loader')

    <div id="smooth-page-wrapper" class="flex-1 flex flex-col justify-between">
    <!-- ── Minimal Top Header ── -->
    <header id="site-header" class="w-full bg-ink-950/80 border-b border-ink-border z-40">
        <div class="max-w-7xl mx-auto px-6 h-[64px] flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                <span class="w-8 h-8 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                </span>
                <span class="font-bold text-paper font-serif text-lg tracking-tight group-hover:text-amber-400 transition-colors">Kitobxon</span>
            </a>

            <a href="{{ route('home') }}" 
               class="ks-btn-ghost py-1.5 px-3 text-xs font-mono inline-flex items-center gap-1.5">
                <span>← {{ __('site.errors.back_home') }}</span>
            </a>
        </div>
    </header>

    <!-- ── Center Hero Error Card ── -->
    <main class="flex-1 flex items-center justify-center p-6 md:p-12 z-10">
        <div class="ks-panel max-w-xl w-full p-8 sm:p-12 text-center bg-ink-900 border border-ink-border relative">
            
            <!-- Large Numeric Watermark & Floating Icon -->
            <div class="relative flex items-center justify-center mb-6">
                <div class="code-watermark font-mono font-bold tracking-tighter absolute">
                    @yield('code')
                </div>
                
                <div class="error-anim-item relative z-10 w-20 h-20 rounded-panel bg-ink-950 border border-amber-500/30 flex items-center justify-center text-3xl shadow-lg">
                    @yield('icon')
                </div>
            </div>

            <!-- Error Content -->
            <div class="space-y-4 relative z-10 mt-4">
                
                <div class="error-anim-item inline-flex items-center gap-2 px-3 py-1 rounded-badge bg-amber-500/10 border border-amber-500/25 text-amber-400 font-mono text-xs tracking-wider uppercase">
                    <span>@yield('badge')</span>
                </div>

                <h1 class="error-anim-item text-2xl sm:text-3xl lg:text-4xl font-bold font-serif text-paper tracking-tight leading-tight">
                    @yield('title')
                </h1>

                <p class="error-anim-item text-mist text-sm sm:text-base max-w-md mx-auto leading-relaxed font-sans">
                    @yield('message')
                </p>

                <!-- Actions Button Row -->
                <div class="error-anim-item pt-4 flex flex-wrap items-center justify-center gap-3">
                    @yield('actions')
                </div>

            </div>

        </div>
    </main>
    </div>

    <!-- ── Motion Script ── -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof gsap !== 'undefined') {
                gsap.fromTo('#site-header',
                    { y: -20, opacity: 0 },
                    { y: 0, opacity: 1, duration: 0.5, ease: "power2.out" }
                );

                gsap.fromTo('.error-anim-item',
                    { opacity: 0, y: 20 },
                    {
                        opacity: 1,
                        y: 0,
                        duration: 0.6,
                        stagger: 0.08,
                        ease: "power2.out",
                        clearProps: "all"
                    }
                );
            }
        });
    </script>
</body>
</html>
