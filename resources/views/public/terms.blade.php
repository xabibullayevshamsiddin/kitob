<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark" x-data="{ mobileMenu: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('site.nav.terms') }} — Kitobxon</title>
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
                        ink: { 950: '#07090e', 900: '#0b0f17', 800: '#111726' },
                        amber: { 400: '#fbbf24', 500: '#f59e0b' },
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .noise-bg {
            background-image: radial-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 0);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="bg-ink-950 text-slate-200 font-sans antialiased min-h-screen noise-bg">
    @include('components.page-loader')
    <div id="smooth-page-wrapper">
    <x-nav.main-header />

    <main class="legal-content py-16 max-w-4xl mx-auto px-6 space-y-8">
        <div>
            <span class="text-xs font-mono text-amber-400 uppercase tracking-widest">✦ {{ __('site.terms.badge') }}</span>
            <h1 class="text-3xl sm:text-5xl font-extrabold text-white mt-2 tracking-tight">{{ __('site.terms.title') }}</h1>
            <p class="text-xs text-slate-400 mt-1 font-mono">{{ __('site.common.last_update') }}</p>
        </div>

        <div class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 text-sm text-slate-300 space-y-6 leading-relaxed">
            <p>1. <strong>{{ __('site.terms.p1_t') }}:</strong> {{ __('site.terms.p1') }}</p>
            <p>2. <strong>{{ __('site.terms.p2_t') }}:</strong> {{ __('site.terms.p2') }}</p>
            <p>3. <strong>{{ __('site.terms.p3_t') }}:</strong> {{ __('site.terms.p3') }}</p>
        </div>
    </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            gsap.fromTo('#site-header', { y: -25, opacity: 0 }, { y: 0, opacity: 1, duration: 0.7, ease: "power3.out" });
            gsap.fromTo('.legal-content', { opacity: 0, y: 30, filter: 'blur(6px)' }, { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.8, ease: "power4.out" });
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
