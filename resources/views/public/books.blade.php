<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth" x-data="{ mobileMenu: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ __('site.books.meta') }}">
    <title>{{ __('site.books.title') }} — Kitobxon</title>

    @include('partials.design-system')


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

        /* 3D Book Cover perspective */
        .book-cover-3d {
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            transform-style: preserve-3d;
        }
        .spotlight-card:hover .book-cover-3d {
            transform: perspective(800px) rotateY(-8deg) rotateX(4deg) scale3d(1.03, 1.03, 1.03);
        }

        /* Audio wave dynamic bars */
        .bar-anim:nth-child(1) { animation: bounceBar 1.1s infinite ease-in-out; }
        .bar-anim:nth-child(2) { animation: bounceBar 0.85s infinite ease-in-out 0.2s; }
        .bar-anim:nth-child(3) { animation: bounceBar 1.3s infinite ease-in-out 0.4s; }
        @keyframes bounceBar {
            0%, 100% { height: 4px; }
            50% { height: 16px; }
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

    <!-- ── Main Content (Chiqib keluvchi Kitoblar Katalogi) ── -->
    <main class="py-16 md:py-24 noise-bg">
        <div class="max-w-7xl mx-auto px-6 space-y-12">
            
            <div class="books-header max-w-2xl space-y-3">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-400/10 border border-amber-400/25 text-amber-400 font-mono text-xs tracking-wider">
                    {{ __('site.books.badge') }}
                </span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-tight">
                    {{ __('site.books.title_1') }}<span class="text-amber-400 italic font-serif">{{ __('site.books.title_b') }}</span>{{ __('site.books.title_2') }}
                </h1>
                <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
                    {{ __('site.books.sub') }}
                </p>
            </div>

            <!-- Books Grid with 3D Book Cards (MASTER §5.2) -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-5 gap-y-10">
                @forelse($books as $book)
                    <x-ui.book-card :book="$book">
                        <div class="pt-3 mt-3 border-t border-ink-border flex items-center justify-between">
                            <span class="ks-eyebrow">{{ $book->genre }}</span>
                            @auth
                                <a href="{{ route('books.show', $book->slug) }}" 
                                   class="ks-btn-primary !py-1 !px-2.5 !text-[11px]">
                                    {{ __('site.books.read_cta') }} →
                                </a>
                            @else
                                <a href="{{ route('login') }}" 
                                   class="ks-btn-ghost !py-1 !px-2.5 !text-[11px]">
                                    {{ __('site.nav.login') }}
                                </a>
                            @endauth
                        </div>
                    </x-ui.book-card>
                @empty
                    <div class="col-span-full py-16 ks-panel text-center space-y-3">
                        <p class="text-mist text-sm">{{ __('site.books.empty') }}</p>
                    </div>
                @endforelse
            </div>

            @if($books->hasPages())
                <div class="pt-8">
                    {{ $books->links() }}
                </div>
            @endif

        </div>
    </main>

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

            // 2. Books Header Entrance
            gsap.fromTo('.books-header',
                { opacity: 0, y: 35, filter: 'blur(8px)' },
                { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.9, ease: "power4.out" }
            );

            // 3. Book Cards Stagger Entrance
            gsap.fromTo('.book-card',
                { opacity: 0, y: 45, scale: 0.95 },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.75,
                    stagger: 0.08,
                    ease: "power3.out",
                    clearProps: "transform,scale"
                }
            );

            // 4. Dynamic Spotlight Cursor Glow
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
