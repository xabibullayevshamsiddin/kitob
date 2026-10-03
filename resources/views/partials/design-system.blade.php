{{--
    Kitobxon — Yagona Dizayn Tizimi (design-system/kitobxon/MASTER.md v1.1)
    Barcha layout va mustaqil sahifalar shu faylni <head> ichida @include qiladi.
    Rang, shrift, radius, soya va animatsiya tokenlari FAQAT shu yerda o'zgartiriladi.
--}}

{{-- Fonts: Spectral (serif sarlavhalar) + DM Sans (interfeys) + DM Mono (raqamlar) --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Spectral:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

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
                    // Editorial Dark — Deep Ink shkalasi
                    ink: {
                        950: '#07090E', 900: '#0D111A', 800: '#131926',
                        700: '#1F293D', 600: '#2A3650', border: '#1F293D',
                    },
                    paper: { DEFAULT: '#F0EDE6', 50: '#FAF7F2', 100: '#F0EDE6', 200: '#E5DFD5' },
                    mist: { DEFAULT: '#8B9BAD', 600: '#526071' },
                    vermilion: { DEFAULT: '#C1392B', 600: '#C1392B', 700: '#A82E22', light: '#B83224' },
                    gilt: { DEFAULT: '#B8860B' },
                    amber: { 300: '#FCD34D', 400: '#FBBF24', 500: '#F59E0B', 600: '#D97706' },
                    // Eski nomlar (orqaga moslik) — yangi palitraga yo'naltirilgan
                    primary: {
                        50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc',
                        400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca',
                        800: '#3730a3', 900: '#1e1b4b', 950: '#0f0e2e'
                    },
                    accent: { 300: '#FCD34D', 400: '#FBBF24', 500: '#F59E0B', 600: '#D97706' },
                    surface: { DEFAULT: '#ffffff', muted: '#FAF7F2', dark: '#0D111A', 'dark-card': '#131926', 'dark-border': '#1F293D' },
                    slate: { 750: '#1A2236', 850: '#0D111A' },
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
        :root {
            --ink-950: #07090E; --ink-900: #0D111A; --ink-800: #131926; --ink-border: #1F293D;
            --paper: #F0EDE6; --text-muted: #8B9BAD; --accent-gold: #F59E0B;
            --accent-book: #C1392B; --text-danger: #FDA4AF;
            --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
            --ease-book: cubic-bezier(0.22, 1, 0.36, 1);
            --ease-elastic: cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        html { -webkit-text-size-adjust: 100%; }
        body {
            font-family: "DM Sans", ui-sans-serif, system-ui, sans-serif;
            font-size: 1rem; line-height: 1.65;
            font-feature-settings: "ss01";
            -webkit-font-smoothing: antialiased;
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

    /* Native select (dark) */
    select {
        background-color: #07090E; color: #F0EDE6; border-color: #1F293D;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%238B9BAD' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right .65rem center; background-repeat: no-repeat; background-size: 1.25em 1.25em;
        padding-right: 2.25rem; -webkit-appearance: none; appearance: none;
    }
    select option { background-color: #0D111A; color: #F0EDE6; }

    /* Autofill oq bo'lib qolmasin */
    input:-webkit-autofill, input:-webkit-autofill:hover, input:-webkit-autofill:focus,
    textarea:-webkit-autofill, select:-webkit-autofill {
        -webkit-text-fill-color: #F0EDE6 !important;
        -webkit-box-shadow: 0 0 0 1000px #131926 inset !important;
        caret-color: #F0EDE6 !important;
        transition: background-color 9999s ease-in-out 0s !important;
    }
    input[type="number"] { -moz-appearance: textfield; appearance: textfield; }
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 10px; height: 10px; }
    ::-webkit-scrollbar-track { background: #07090E; }
    ::-webkit-scrollbar-thumb { background: #1F293D; border-radius: 8px; border: 2px solid #07090E; }
    ::-webkit-scrollbar-thumb:hover { background: #2A3650; }

    /* prefers-reduced-motion: faqat opacity qoladi */
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 150ms !important; animation-iteration-count: 1 !important;
            transition-duration: 150ms !important; transition-property: opacity, color, background-color, border-color !important;
            scroll-behavior: auto !important;
        }
        .ks-flame.is-lit { animation: none !important; }
    }
</style>
