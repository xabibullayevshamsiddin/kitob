{{--
    Kitobxon — Yagona Dizayn Tizimi (design-system/kitobxon/MASTER.md v1.1)
    Barcha layout va mustaqil sahifalar shu faylni <head> ichida @include qiladi.
    Rang, shrift, radius, soya va animatsiya tokenlari FAQAT shu yerda o'zgartiriladi.
--}}

{{-- Fonts: Spectral (serif sarlavhalar) + DM Sans (interfeys) + DM Mono (raqamlar) --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Spectral:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

{{-- FOUC Oldini oluvchi Mavzu Initsializatsiyasi (Light / Dark mode) --}}
<script>
    (function() {
        try {
            var el = document.documentElement;
            if (el.classList.contains('force-dark')) {
                el.classList.add('dark');
                el.classList.remove('light');
                return;
            }
            var saved = localStorage.getItem('kitob_theme');
            if (saved === 'light') {
                el.classList.remove('dark');
                el.classList.add('light');
            } else {
                el.classList.remove('light');
                el.classList.add('dark');
            }
        } catch(e) {}
    })();

    window.__setKitobTheme = function(theme, evt) {
        try {
            localStorage.setItem('kitob_theme', theme);
            var el = document.documentElement;
            if (el.classList.contains('force-dark')) {
                el.classList.add('dark');
                el.classList.remove('light');
                return;
            }

            var applyTheme = function() {
                if (theme === 'light') {
                    el.classList.remove('dark');
                    el.classList.add('light');
                } else {
                    el.classList.remove('light');
                    el.classList.add('dark');
                }
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: theme } }));
            };

            // Haptic feedback (if mobile device supports it)
            try { if (navigator.vibrate) navigator.vibrate([12, 24]); } catch(e) {}

            // Trigger Blinkit pulse shockwave on all theme buttons
            try {
                var themeBtns = document.querySelectorAll('.header-theme-btn, [data-theme-toggle]');
                themeBtns.forEach(function(b) {
                    b.classList.remove('is-pulsing');
                    void b.offsetWidth; // force reflow for smooth re-trigger
                    b.classList.add('is-pulsing');
                    setTimeout(function() { b.classList.remove('is-pulsing'); }, 700);
                });
            } catch(e) {}

            // Blinkit View Transition circular clip-path pulse reveal
            var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (document.startViewTransition && !prefersReduced) {
                var x = null, y = null;
                if (evt) {
                    if (typeof evt.clientX === 'number' && evt.clientX !== 0) {
                        x = evt.clientX;
                        y = evt.clientY;
                    } else if (evt.touches && evt.touches[0]) {
                        x = evt.touches[0].clientX;
                        y = evt.touches[0].clientY;
                    }
                }
                if (x === null || y === null) {
                    var targetBtn = document.querySelector('.header-theme-btn');
                    if (targetBtn) {
                        var rect = targetBtn.getBoundingClientRect();
                        x = rect.left + rect.width / 2;
                        y = rect.top + rect.height / 2;
                    } else {
                        x = window.innerWidth / 2;
                        y = window.innerHeight / 2;
                    }
                }

                var endRadius = Math.hypot(
                    Math.max(x, window.innerWidth - x),
                    Math.max(y, window.innerHeight - y)
                );

                var transition = document.startViewTransition(function() {
                    applyTheme();
                });

                transition.ready.then(function() {
                    document.documentElement.animate(
                        {
                            clipPath: [
                                'circle(0px at ' + x + 'px ' + y + 'px)',
                                'circle(' + endRadius + 'px at ' + x + 'px ' + y + 'px)'
                            ]
                        },
                        {
                            duration: 520,
                            easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                            pseudoElement: '::view-transition-new(root)'
                        }
                    );
                });
            } else {
                applyTheme();
            }
        } catch(e) {}
    };
</script>

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                fontFamily: {
                    sans: ['"DM Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    display: ['Spectral', 'Georgia', 'serif'],
                    serif: ['Spectral', 'Georgia', 'serif'],
                    mono: ['"DM Mono"', 'ui-monospace', 'monospace'],
                    manrope: ['"DM Sans"', 'sans-serif'],
                },
                colors: {
                    // Editorial Shkala — Dark & Light dynamic CSS variables
                    ink: {
                        950: 'rgb(var(--ink-950-rgb) / <alpha-value>)',
                        900: 'rgb(var(--ink-900-rgb) / <alpha-value>)',
                        800: 'rgb(var(--ink-800-rgb) / <alpha-value>)',
                        700: 'rgb(var(--ink-700-rgb) / <alpha-value>)',
                        600: 'rgb(var(--ink-600-rgb) / <alpha-value>)',
                        border: 'rgb(var(--ink-border-rgb) / <alpha-value>)',
                    },
                    paper: {
                        DEFAULT: 'rgb(var(--paper-rgb) / <alpha-value>)',
                        50: 'rgb(var(--paper-50-rgb) / <alpha-value>)',
                        100: 'rgb(var(--paper-100-rgb) / <alpha-value>)',
                        200: 'rgb(var(--paper-200-rgb) / <alpha-value>)',
                    },
                    'paper-muted': 'rgb(var(--paper-muted-rgb) / <alpha-value>)',
                    mist: {
                        DEFAULT: 'rgb(var(--mist-rgb) / <alpha-value>)',
                        600: 'rgb(var(--mist-600-rgb) / <alpha-value>)',
                    },
                    vermilion: { DEFAULT: '#C1392B', 600: '#C1392B', 700: '#A82E22', light: '#B83224' },
                    gilt: { DEFAULT: '#B8860B' },
                    gold: { DEFAULT: '#F59E0B', light: '#FBBF24' },
                    aurora: { teal: '#2DD4BF', coral: '#FB7185' },
                    amber: { 300: '#FCD34D', 400: '#FBBF24', 500: '#F59E0B', 600: '#D97706' },
                    // Orqaga moslik
                    primary: {
                        50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc',
                        400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca',
                        800: '#3730a3', 900: '#1e1b4b', 950: '#0f0e2e'
                    },
                    accent: { 300: '#FCD34D', 400: '#FBBF24', 500: '#F59E0B', 600: '#D97706' },
                    surface: {
                        DEFAULT: 'rgb(var(--surface-default-rgb) / <alpha-value>)',
                        muted: 'rgb(var(--surface-muted-rgb) / <alpha-value>)',
                        dark: 'rgb(var(--surface-dark-rgb) / <alpha-value>)',
                        'dark-card': 'rgb(var(--surface-dark-card-rgb) / <alpha-value>)',
                        'dark-border': 'rgb(var(--surface-dark-border-rgb) / <alpha-value>)'
                    },
                    slate: {
                        750: 'rgb(var(--slate-750-rgb) / <alpha-value>)',
                        850: 'rgb(var(--slate-850-rgb) / <alpha-value>)',
                    },
                },
                // Radius tokenlari (MASTER §3). Eski rounded-2xl/3xl qiymatlari editorial shkalaga tushirildi.
                borderRadius: {
                    badge: '4px', btn: '6px', input: '6px', card: '8px', panel: '12px', modal: '16px',
                    xl: '8px', '2xl': '10px', '3xl': '12px',
                },
                boxShadow: {
                    card: '0 4px 20px -2px rgba(0,0,0,0.45)',
                    popover: '0 10px 30px -5px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.05)',
                    'card-depth': '0 18px 40px -16px rgba(0,0,0,0.7), 0 0 0 1px rgba(255,255,255,0.06)',
                    spotlight: '0 0 40px -12px rgba(245,158,11,0.22)',
                    'glow-amber': '0 0 20px rgba(245,158,11,0.25)',
                    soft: '0 4px 20px -2px rgba(0,0,0,0.45)',
                },
                transitionTimingFunction: {
                    out: 'cubic-bezier(0.16, 1, 0.3, 1)',
                    book: 'cubic-bezier(0.22, 1, 0.36, 1)',
                    elastic: 'cubic-bezier(0.34, 1.56, 0.64, 1)',
                },
                transitionDuration: { micro: '150ms', base: '200ms', modal: '300ms', book: '550ms' },
                animation: {
                    'fade-in': 'ksFadeIn 0.3s cubic-bezier(0.16,1,0.3,1) forwards',
                    'slide-up': 'ksSlideUp 0.35s cubic-bezier(0.16,1,0.3,1) forwards',
                    'slide-in-left': 'ksSlideInLeft 0.3s cubic-bezier(0.16,1,0.3,1) forwards',
                    'scale-in': 'ksScaleIn 0.3s cubic-bezier(0.16,1,0.3,1) forwards',
                    'streak-flame': 'ksStreakFlame 0.6s cubic-bezier(0.34,1.56,0.64,1)',
                    'pulse-slow': 'pulse 3s infinite',
                },
                keyframes: {
                    ksFadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                    ksSlideUp: { '0%': { opacity: '0', transform: 'translateY(12px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                    ksSlideInLeft: { '0%': { opacity: '0', transform: 'translateX(-16px)' }, '100%': { opacity: '1', transform: 'translateX(0)' } },
                    ksScaleIn: { '0%': { opacity: '0', transform: 'scale(0.95)' }, '100%': { opacity: '1', transform: 'scale(1)' } },
                    ksStreakFlame: {
                        '0%': { transform: 'scale(1) rotate(0deg)' },
                        '30%': { transform: 'scale(1.3) rotate(-6deg)', filter: 'drop-shadow(0 0 16px #F59E0B)' },
                        '60%': { transform: 'scale(1.1) rotate(6deg)' },
                        '100%': { transform: 'scale(1) rotate(0deg)' },
                    },
                },
            }
        }
    }
</script>

<style type="text/tailwindcss">
    @layer base {
        :root, html.dark, html.force-dark {
            --ink-950-rgb: 7 9 14;
            --ink-900-rgb: 13 17 26;
            --ink-800-rgb: 19 25 38;
            --ink-700-rgb: 31 41 61;
            --ink-600-rgb: 42 54 80;
            --ink-border-rgb: 31 41 61;

            --paper-rgb: 240 237 230;
            --paper-50-rgb: 250 247 242;
            --paper-100-rgb: 240 237 230;
            --paper-200-rgb: 229 223 213;
            --paper-muted-rgb: 201 196 184;

            --mist-rgb: 139 155 173;
            --mist-600-rgb: 82 96 113;

            --surface-default-rgb: 255 255 255;
            --surface-muted-rgb: 250 247 242;
            --surface-dark-rgb: 13 17 26;
            --surface-dark-card-rgb: 19 25 38;
            --surface-dark-border-rgb: 31 41 61;

            --slate-750-rgb: 26 34 54;
            --slate-850-rgb: 13 17 26;

            --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
            --ease-book: cubic-bezier(0.22, 1, 0.36, 1);
            --ease-elastic: cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* Editorial Literary Light Mode (soothing warm linen canvas + soft ivory cards + deep ink typography — zero eye glare) */
        html.light:not(.force-dark) {
            --ink-950-rgb: 243 239 231;       /* #F3EFE7: Soothing warm linen book canvas (eye-friendly, no glare) */
            --ink-900-rgb: 249 246 240;       /* #F9F6F0: Soft warm ivory card surface (not blinding white) */
            --ink-800-rgb: 238 233 223;       /* #EEE9DF: Muted warm stone wells & ghost pills */
            --ink-700-rgb: 228 221 209;       /* #E4DDD1: Border & subtle hover */
            --ink-600-rgb: 214 206 193;       /* #D6CEC1: Active / divider */
            --ink-border-rgb: 224 217 205;    /* #E0D9CD: Refined book-cloth hairline border */

            --paper-rgb: 28 25 22;            /* #1C1916: Rich literary espresso charcoal ink */
            --paper-50-rgb: 20 18 16;
            --paper-100-rgb: 28 25 22;
            --paper-200-rgb: 46 41 36;
            --paper-muted-rgb: 92 85 75;

            --mist-rgb: 107 99 88;            /* #6B6358: Warm taupe metadata (high contrast, zero fatigue) */
            --mist-600-rgb: 78 72 63;         /* #4E483F */

            --surface-default-rgb: 249 246 240;
            --surface-muted-rgb: 238 233 223;
            --surface-dark-rgb: 249 246 240;
            --surface-dark-card-rgb: 243 239 231;
            --surface-dark-border-rgb: 224 217 205;

            --slate-750-rgb: 234 228 218;
            --slate-850-rgb: 243 239 231;
        }

        html {
            -webkit-text-size-adjust: 100%;
            overflow-x: hidden;
            max-width: 100%;
        }
        body {
            font-family: "DM Sans", ui-sans-serif, system-ui, sans-serif;
            font-size: 1rem; line-height: 1.65;
            font-feature-settings: "ss01";
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
            max-width: 100%;
            position: relative;
        }

        /* Tipografik shkala (MASTER §4) — Spectral sarlavhalar kengroq line-height bilan */
        h1, h2, h3, h4, .font-display {
            font-family: Spectral, Georgia, serif;
            font-feature-settings: "liga", "kern";
            text-wrap: balance;
        }
        h1 { letter-spacing: -0.02em;  line-height: 1.3; }
        h2 { letter-spacing: -0.015em; line-height: 1.35; }
        h3 { letter-spacing: -0.01em;  line-height: 1.4; }
        h4 { line-height: 1.45; }
        /* Interfeys sarlavhalari (kichik label/karta yorlig'i) sans bo'lib qoladi */
        h5, h6 { font-family: "DM Sans", ui-sans-serif, sans-serif; }
        .font-mono, code, kbd, samp { font-variant-numeric: tabular-nums; }
        p { text-wrap: pretty; }

        /* Interaktivlik: cursor + ko'rinadigan fokus */
        a, button, [role="button"], label[for], select, summary,
        input[type="checkbox"], input[type="radio"], input[type="submit"], input[type="file"] { cursor: pointer; }
        button:disabled, [aria-disabled="true"] { cursor: not-allowed; }
        :focus-visible { outline: 2px solid #F59E0B; outline-offset: 2px; border-radius: 4px; }
        input:focus-visible, textarea:focus-visible, select:focus-visible { outline-offset: 0; }

        ::selection { background: #F59E0B; color: #07090E; }
    }

    @layer components {
        /* Tugmalar (MASTER §6.1) */
        .ks-btn {
            @apply inline-flex items-center justify-center gap-2 px-4 py-2 rounded-btn text-sm font-medium
                   transition-[background-color,border-color,color,transform] duration-base ease-out
                   active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none;
        }
        .ks-btn-primary { @apply ks-btn bg-vermilion text-paper hover:bg-vermilion-700; }
        .ks-btn-gold    { @apply ks-btn bg-amber-500 text-ink-950 hover:bg-amber-400; }
        .ks-btn-ghost   { @apply ks-btn bg-ink-800 text-paper border border-ink-border hover:bg-ink-700; }
        .ks-input {
            @apply w-full rounded-input bg-ink-950 border border-ink-border px-3.5 py-2.5 text-sm text-paper
                   placeholder:text-mist/70 transition-colors duration-base
                   focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none;
        }
        .ks-card  { @apply bg-ink-900 border border-ink-border rounded-card; }
        .ks-panel { @apply bg-ink-900 border border-ink-border rounded-panel; }
        .ks-badge { @apply inline-flex items-center gap-1 px-2 py-0.5 rounded-badge font-mono text-[11px] font-medium uppercase tracking-[0.05em]; }
        .ks-eyebrow { @apply font-mono text-[11px] uppercase tracking-[0.14em] text-mist; }
        .ks-stat { @apply font-mono text-[1.75rem] font-semibold leading-none tabular-nums; }
        .ks-rule { @apply h-px w-full bg-ink-border; }
    }
</style>

<style>
    [x-cloak] { display: none !important; }

    /* Signature 2: streak-olov — .is-lit klassi qo'shilganda bir marta chaqnaydi */
    .ks-flame.is-lit { animation: ksStreakFlameRaw 600ms cubic-bezier(0.34, 1.56, 0.64, 1); }
    @keyframes ksStreakFlameRaw {
        0%   { transform: scale(1) rotate(0deg); }
        30%  { transform: scale(1.3) rotate(-6deg); filter: drop-shadow(0 0 16px #F59E0B); }
        60%  { transform: scale(1.1) rotate(6deg); }
        100% { transform: scale(1) rotate(0deg); }
    }

    /* Paper grain — kitob varag'i teksturasi (juda nozik) */
    .ks-grain { position: relative; isolation: isolate; }
    .ks-grain::before {
        content: ""; position: absolute; inset: 0; z-index: -1; pointer-events: none; opacity: .035;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
    }
    html.light:not(.force-dark) .ks-grain::before { opacity: .03; }

    /* Native select (dark) */
    select {
        background-color: #07090E; color: #F0EDE6; border-color: #1F293D;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%238B9BAD' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right .65rem center; background-repeat: no-repeat; background-size: 1.25em 1.25em;
        padding-right: 2.25rem; -webkit-appearance: none; appearance: none;
    }
    select option { background-color: #0D111A; color: #F0EDE6; }

    /* Native select (light) */
    html.light:not(.force-dark) select {
        background-color: #FAF7F1; color: #1C1916; border-color: #E0D9CD;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236B6358' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    }
    html.light:not(.force-dark) select option { background-color: #F9F6F0; color: #1C1916; }

    /* Light mode Card, Panel & Shadow refinements (Eye-friendly warm ivory, zero glare) */
    html.light:not(.force-dark) .ks-card,
    html.light:not(.force-dark) .ks-panel {
        background-color: #F9F6F0;
        border-color: #E0D9CD;
        box-shadow: 0 2px 10px -2px rgba(44, 38, 30, 0.04), 0 1px 3px 0 rgba(44, 38, 30, 0.02);
    }
    html.light:not(.force-dark) .ks-input {
        background-color: #FAF7F1;
        border-color: #DCD4C6;
        color: #1C1916;
    }
    html.light:not(.force-dark) .ks-input:focus {
        background-color: #FAF7F1;
        border-color: #D97706;
        box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.15);
    }

    /* Floating Dropdown Menus & Popovers in Light Mode */
    html.light:not(.force-dark) .shadow-popover,
    html.light:not(.force-dark) .header-dropdown-menu {
        background-color: #FAF6EF !important;
        border-color: #DDD4C4 !important;
        box-shadow: 0 16px 40px -6px rgba(28, 25, 22, 0.2), 0 2px 8px rgba(28, 25, 22, 0.08), 0 0 0 1px rgba(28, 25, 22, 0.08) !important;
    }

    /* ══════════════════════════════════════════════════════════════════ */
    /* DEDICATED LIGHT MODE BUTTON DESIGNS (ALOHIDA TUGMA DIZAYNLARI)   */
    /* ══════════════════════════════════════════════════════════════════ */

    /* 1. Primary Button (Asosiy / Muhim harakat): Deep obsidian ink + tactile bevel */
    html.light:not(.force-dark) .ks-btn-primary {
        background-color: #1C1916 !important;
        color: #FAF7F2 !important;
        border: 1px solid rgba(28, 25, 22, 0.9) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18), 0 2px 4px rgba(28, 25, 22, 0.12), 0 8px 18px -4px rgba(28, 25, 22, 0.16) !important;
    }
    html.light:not(.force-dark) .ks-btn-primary:hover {
        background-color: #2D2722 !important;
        color: #FFFFFF !important;
        transform: translateY(-1px);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.28), 0 4px 10px rgba(28, 25, 22, 0.18), 0 14px 26px -4px rgba(28, 25, 22, 0.18) !important;
    }
    html.light:not(.force-dark) .ks-btn-primary:active {
        transform: scale(0.98);
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.25) !important;
    }

    /* 2. Gold / Accent Button (Oltin / Rag'batlantiruvchi): Warm honey-amber gradient */
    html.light:not(.force-dark) .ks-btn-gold {
        background: linear-gradient(180deg, #F59E0B 0%, #D97706 100%) !important;
        color: #1C1916 !important;
        border: 1px solid #B45309 !important;
        font-weight: 600 !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.42), 0 2px 6px rgba(217, 119, 6, 0.25), 0 8px 18px -4px rgba(217, 119, 6, 0.2) !important;
    }
    html.light:not(.force-dark) .ks-btn-gold:hover {
        background: linear-gradient(180deg, #FBBF24 0%, #E58A08 100%) !important;
        transform: translateY(-1px);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.52), 0 4px 12px rgba(217, 119, 6, 0.32), 0 14px 26px -4px rgba(217, 119, 6, 0.24) !important;
    }
    html.light:not(.force-dark) .ks-btn-gold:active {
        transform: scale(0.98);
        box-shadow: inset 0 2px 4px rgba(180, 83, 9, 0.4) !important;
    }

    /* 3. Ghost / Secondary Button (Yordamchi / Neytral): Warm linen paper pill */
    html.light:not(.force-dark) .ks-btn-ghost {
        background-color: #FAF7F0 !important;
        color: #24201C !important;
        border: 1px solid #D6CEC0 !important;
        box-shadow: 0 1px 3px rgba(44, 38, 30, 0.05), inset 0 1px 0 rgba(255, 255, 255, 0.6) !important;
    }
    html.light:not(.force-dark) .ks-btn-ghost:hover {
        background-color: #EEE7DA !important;
        border-color: #C2B7A4 !important;
        color: #1C1916 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px -2px rgba(44, 38, 30, 0.08) !important;
    }
    html.light:not(.force-dark) .ks-btn-ghost:active {
        transform: scale(0.98);
        background-color: #E5DEC0 !important;
    }

    /* Spotlight cards & sections in Light Mode */
    html.light:not(.force-dark) .spotlight-card {
        background: #F9F6F0 !important;
        border-color: #E0D9CD !important;
        box-shadow: 0 4px 18px -4px rgba(44, 38, 30, 0.05) !important;
    }
    html.light:not(.force-dark) .faq-item {
        background: #F9F6F0 !important;
        border-color: #E0D9CD !important;
        box-shadow: 0 2px 8px rgba(44, 38, 30, 0.03) !important;
    }
    html.light:not(.force-dark) .noise-bg {
        background-image: radial-gradient(rgba(40, 32, 24, 0.04) 1px, transparent 0) !important;
    }
    html.light:not(.force-dark) .ks-aurora {
        mix-blend-mode: multiply !important;
        opacity: 0.15 !important;
    }

    /* Autofill oq bo'lib qolmasin */
    input:-webkit-autofill, input:-webkit-autofill:hover, input:-webkit-autofill:focus,
    textarea:-webkit-autofill, select:-webkit-autofill {
        -webkit-text-fill-color: #F0EDE6 !important;
        -webkit-box-shadow: 0 0 0 1000px #131926 inset !important;
        caret-color: #F0EDE6 !important;
        transition: background-color 9999s ease-in-out 0s !important;
    }
    html.light:not(.force-dark) input:-webkit-autofill,
    html.light:not(.force-dark) input:-webkit-autofill:hover,
    html.light:not(.force-dark) input:-webkit-autofill:focus,
    html.light:not(.force-dark) textarea:-webkit-autofill,
    html.light:not(.force-dark) select:-webkit-autofill {
        -webkit-text-fill-color: #181613 !important;
        -webkit-box-shadow: 0 0 0 1000px #FAF8F4 inset !important;
        caret-color: #181613 !important;
    }
    input[type="number"] { -moz-appearance: textfield; appearance: textfield; }
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(31, 41, 61, 0.65); border-radius: 8px; }
    ::-webkit-scrollbar-thumb:hover { background: rgba(42, 54, 80, 0.9); }

    html.light:not(.force-dark) ::-webkit-scrollbar-track { background: transparent; }
    html.light:not(.force-dark) ::-webkit-scrollbar-thumb { background: rgba(180, 170, 155, 0.55); border-radius: 8px; }
    html.light:not(.force-dark) ::-webkit-scrollbar-thumb:hover { background: rgba(140, 130, 115, 0.85); }

    /* Mobile UX & Touch Optimization */
    * { -webkit-tap-highlight-color: transparent; }
    .no-scrollbar::-webkit-scrollbar { display: none !important; }
    .no-scrollbar { -ms-overflow-style: none !important; scrollbar-width: none !important; -webkit-overflow-scrolling: touch; }
    .pb-safe { padding-bottom: max(0.75rem, env(safe-area-inset-bottom, 0.75rem)); }

    /* iOS Safari 16px auto-zoom prevention */
    @media (max-width: 640px) {
        input.ks-input, select.ks-input, textarea.ks-input {
            font-size: 16px !important;
        }
    }

    /* prefers-reduced-motion: faqat opacity qoladi */
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 150ms !important; animation-iteration-count: 1 !important;
            transition-duration: 150ms !important; transition-property: opacity, color, background-color, border-color !important;
            scroll-behavior: auto !important;
        }
        .ks-flame.is-lit { animation: none !important; }
    }

    /* ══════════════════════════════════════════════════════════════════ */
    /* BLINKIT LIGHT/DARK MODE TOGGLE PULSE & CIRCULAR WAVE ANIMATION    */
    /* ══════════════════════════════════════════════════════════════════ */

    /* 1. View Transition Circular Wave Reveal */
    ::view-transition-group(root) {
        animation-duration: 0.52s;
        animation-timing-function: cubic-bezier(0.22, 1, 0.36, 1);
    }
    ::view-transition-old(root),
    ::view-transition-new(root) {
        animation: none;
        mix-blend-mode: normal;
    }
    ::view-transition-old(root) {
        z-index: 1;
    }
    ::view-transition-new(root) {
        z-index: 9999;
    }

    /* 2. Theme Toggle Button Tactile Spring Physics */
    .header-theme-btn {
        position: relative;
        overflow: visible !important;
        will-change: transform;
    }
    .header-theme-btn.is-pulsing {
        animation: blinkitButtonBounce 0.65s cubic-bezier(0.34, 1.56, 0.64, 1) both;
    }

    @keyframes blinkitButtonBounce {
        0%   { transform: scale(1); }
        22%  { transform: scale(0.82); }
        58%  { transform: scale(1.15); }
        78%  { transform: scale(0.96); }
        100% { transform: scale(1); }
    }

    /* 3. Icon Spring Rotation & Pop */
    .header-theme-btn.is-pulsing .header-theme-sun {
        animation: blinkitSunPop 0.58s cubic-bezier(0.34, 1.56, 0.64, 1) both;
    }
    .header-theme-btn.is-pulsing .header-theme-moon {
        animation: blinkitMoonPop 0.58s cubic-bezier(0.34, 1.56, 0.64, 1) both;
    }

    @keyframes blinkitSunPop {
        0%   { transform: rotate(-90deg) scale(0.2); opacity: 0; }
        55%  { transform: rotate(190deg) scale(1.26); opacity: 1; }
        100% { transform: rotate(180deg) scale(1); opacity: 1; }
    }

    @keyframes blinkitMoonPop {
        0%   { transform: rotate(-60deg) scale(0.2); opacity: 0; }
        55%  { transform: rotate(22deg) scale(1.22); opacity: 1; }
        100% { transform: rotate(0deg) scale(1); opacity: 1; }
    }

    /* 4. Concentric Shockwave Pulse Rings */
    .theme-pulse-halo {
        position: absolute;
        inset: -2px;
        border-radius: inherit;
        pointer-events: none;
        opacity: 0;
        z-index: -1;
    }

    .header-theme-btn.is-pulsing .theme-pulse-halo-1 {
        animation: blinkitRingPulseDark1 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .header-theme-btn.is-pulsing .theme-pulse-halo-2 {
        animation: blinkitRingPulseDark2 0.65s cubic-bezier(0.16, 1, 0.3, 1) 0.06s both;
    }

    @keyframes blinkitRingPulseDark1 {
        0% {
            transform: scale(0.85);
            opacity: 0.95;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.9), 0 0 16px rgba(245, 158, 11, 0.55);
        }
        100% {
            transform: scale(2.6);
            opacity: 0;
            box-shadow: 0 0 0 0.5px rgba(245, 158, 11, 0), 0 0 28px rgba(245, 158, 11, 0);
        }
    }

    @keyframes blinkitRingPulseDark2 {
        0% {
            transform: scale(0.75);
            opacity: 0.7;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.7), 0 0 24px rgba(245, 158, 11, 0.45);
        }
        100% {
            transform: scale(3.6);
            opacity: 0;
            box-shadow: 0 0 0 0.5px rgba(245, 158, 11, 0), 0 0 42px rgba(245, 158, 11, 0);
        }
    }

    html.light .header-theme-btn.is-pulsing .theme-pulse-halo-1 {
        animation-name: blinkitRingPulseLight1;
    }
    html.light .header-theme-btn.is-pulsing .theme-pulse-halo-2 {
        animation-name: blinkitRingPulseLight2;
    }

    @keyframes blinkitRingPulseLight1 {
        0% {
            transform: scale(0.85);
            opacity: 0.95;
            box-shadow: 0 0 0 2px rgba(217, 119, 6, 0.85), 0 0 16px rgba(217, 119, 6, 0.45);
        }
        100% {
            transform: scale(2.6);
            opacity: 0;
            box-shadow: 0 0 0 0.5px rgba(217, 119, 6, 0), 0 0 28px rgba(217, 119, 6, 0);
        }
    }

    @keyframes blinkitRingPulseLight2 {
        0% {
            transform: scale(0.75);
            opacity: 0.7;
            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.65), 0 0 24px rgba(217, 119, 6, 0.35);
        }
        100% {
            transform: scale(3.6);
            opacity: 0;
            box-shadow: 0 0 0 0.5px rgba(217, 119, 6, 0), 0 0 42px rgba(217, 119, 6, 0);
        }
    }

    /* 5. Micro-Spark Particle Rays */
    .theme-spark {
        position: absolute;
        width: 3.5px;
        height: 3.5px;
        border-radius: 50%;
        top: 50%;
        left: 50%;
        margin-top: -1.75px;
        margin-left: -1.75px;
        pointer-events: none;
        opacity: 0;
        background: #F59E0B;
        box-shadow: 0 0 6px #F59E0B;
        z-index: 10;
    }

    html.light .theme-spark {
        background: #D97706;
        box-shadow: 0 0 6px #D97706;
    }

    .header-theme-btn.is-pulsing .theme-spark-1 { animation: blinkitSpark1 0.55s cubic-bezier(0.16, 1, 0.3, 1) both; }
    .header-theme-btn.is-pulsing .theme-spark-2 { animation: blinkitSpark2 0.55s cubic-bezier(0.16, 1, 0.3, 1) both; }
    .header-theme-btn.is-pulsing .theme-spark-3 { animation: blinkitSpark3 0.55s cubic-bezier(0.16, 1, 0.3, 1) both; }
    .header-theme-btn.is-pulsing .theme-spark-4 { animation: blinkitSpark4 0.55s cubic-bezier(0.16, 1, 0.3, 1) both; }
    .header-theme-btn.is-pulsing .theme-spark-5 { animation: blinkitSpark5 0.55s cubic-bezier(0.16, 1, 0.3, 1) both; }
    .header-theme-btn.is-pulsing .theme-spark-6 { animation: blinkitSpark6 0.55s cubic-bezier(0.16, 1, 0.3, 1) both; }

    @keyframes blinkitSpark1 { 0% { transform: translate(0, 0) scale(1); opacity: 1; } 100% { transform: translate(0, -22px) scale(0); opacity: 0; } }
    @keyframes blinkitSpark2 { 0% { transform: translate(0, 0) scale(1); opacity: 1; } 100% { transform: translate(19px, -11px) scale(0); opacity: 0; } }
    @keyframes blinkitSpark3 { 0% { transform: translate(0, 0) scale(1); opacity: 1; } 100% { transform: translate(19px, 11px) scale(0); opacity: 0; } }
    @keyframes blinkitSpark4 { 0% { transform: translate(0, 0) scale(1); opacity: 1; } 100% { transform: translate(0, 22px) scale(0); opacity: 0; } }
    @keyframes blinkitSpark5 { 0% { transform: translate(0, 0) scale(1); opacity: 1; } 100% { transform: translate(-19px, 11px) scale(0); opacity: 0; } }
    @keyframes blinkitSpark6 { 0% { transform: translate(0, 0) scale(1); opacity: 1; } 100% { transform: translate(-19px, -11px) scale(0); opacity: 0; } }
</style>

{{-- Motion layer: scroll-reveal, aurora, spotlight, ripple, counters, page transitions --}}
@include('partials.motion')
