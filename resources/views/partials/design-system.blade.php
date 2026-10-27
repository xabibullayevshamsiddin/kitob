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

    window.__togglePassword = function(btn) {
        if (!btn) return;
        var container = btn.closest('.relative') || btn.parentElement;
        if (!container) return;
        var input = container.querySelector('input');
        if (!input) return;
        var isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        var eyeOpen = btn.querySelector('.ks-eye-open');
        var eyeClosed = btn.querySelector('.ks-eye-closed');
        if (eyeOpen && eyeClosed) {
            if (isPass) {
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }
    };
</script>

{{-- Vite Bundled Styles & Scripts (Tailwind v3 + Alpine.js + Collapse, Pusher, Echo, Axios) --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])

@auth
    @php
        $authUser = auth()->user();
        $userAccent = $authUser->getEquippedItem('accent');
        $userAccentColor = $userAccent ? ($userAccent->payload['color'] ?? null) : null;
    @endphp
    @if($userAccentColor && \App\Services\Shop\ShopService::validateHexColor($userAccentColor))
        @php
            list($ar, $ag, $ab) = sscanf($userAccentColor, "#%02x%02x%02x");
        @endphp
        <style>
            :root {
                --user-accent: {{ $userAccentColor }};
                --user-accent-rgb: {{ $ar }} {{ $ag }} {{ $ab }};
            }
            .ks-btn-gold:not(:disabled), .ks-btn-primary:not(:disabled) {
                background: linear-gradient(135deg, {{ $userAccentColor }} 0%, rgba({{ $ar }}, {{ $ag }}, {{ $ab }}, 0.85) 100%) !important;
                border-color: {{ $userAccentColor }} !important;
                box-shadow: 0 4px 14px rgba({{ $ar }}, {{ $ag }}, {{ $ab }}, 0.28) !important;
            }
        </style>
    @endif
@endauth

<style>
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

    /* Brand-Aligned Light Mode (Crisp Ocean Slate Canvas + Pure White Cards + Deep Navy Typography & Vibrant Teal Accents) */
    html.light:not(.force-dark) {
        --ink-950-rgb: 245 248 251;       /* #F5F8FB: Crisp ocean slate canvas matching the logo aesthetic */
        --ink-900-rgb: 255 255 255;       /* #FFFFFF: Pure white card & surface */
        --ink-800-rgb: 237 243 248;       /* #EDF3F8: Soft slate wells, badges & ghost pills */
        --ink-700-rgb: 226 232 240;       /* #E2E8F0: Slate-200 */
        --ink-600-rgb: 203 213 225;       /* #CBD5E1: Slate-300 */
        --ink-border-rgb: 203 213 225;    /* #CBD5E1: Crisp, clearly visible slate-300 border */

        --paper-rgb: 12 28 54;            /* #0C1C36: Deep oceanic navy ink (from logo's "D") */
        --paper-50-rgb: 8 19 38;          /* #081326: Deepest navy text */
        --paper-100-rgb: 12 28 54;        /* #0C1C36 */
        --paper-200-rgb: 20 42 77;        /* #142A4D */
        --paper-muted-rgb: 71 95 125;     /* #475F7D: Muted navy-slate */

        --mist-rgb: 75 97 125;            /* #4B617D: Refined slate metadata (high contrast, crisp) */
        --mist-600-rgb: 47 70 98;         /* #2F4662 */

        --surface-default-rgb: 255 255 255;
        --surface-muted-rgb: 242 246 250;
        --surface-dark-rgb: 255 255 255;
        --surface-dark-card-rgb: 245 248 251;
        --surface-dark-border-rgb: 203 213 225;

        --slate-750-rgb: 235 241 248;
        --slate-850-rgb: 245 248 251;
    }

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

    /* Color scheme & Universal Form Controls */
    :root, html.dark {
        color-scheme: dark;
    }
    html.light:not(.force-dark) {
        color-scheme: light;
    }

    input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="file"]):not([type="range"]):not([type="color"]):not([type="image"]),
    textarea {
        background-color: #07090E;
        color: #F0EDE6;
        border-color: #1F293D;
    }
    input::placeholder, textarea::placeholder {
        color: #8B9BAD;
        opacity: 0.7;
    }

    .ks-input {
        background-color: #07090E;
        color: #F0EDE6;
        border: 1px solid #1F293D;
        border-radius: 0.75rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease, color 0.15s ease;
    }
    .ks-input:focus {
        border-color: #00A8B5 !important;
        box-shadow: 0 0 0 3px rgba(0, 168, 181, 0.22);
        outline: none;
    }
    .ks-input-invalid {
        border-color: #F43F5E !important;
        background-color: rgba(244, 63, 94, 0.05) !important;
    }

    /* Native select (dark) */
    select {
        background-color: #07090E; color: #F0EDE6; border-color: #1F293D;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%238B9BAD' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right .65rem center; background-repeat: no-repeat; background-size: 1.25em 1.25em;
        -webkit-appearance: none; appearance: none;
    }
    /* Keep long selected labels clear of the custom dropdown chevron. */
    select:not([multiple]) {
        box-sizing: border-box;
        padding-right: 2.75rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    select option { background-color: #0D111A; color: #F0EDE6; }

    /* Native select (light) */
    html.light:not(.force-dark) select {
        background-color: #FFFFFF; color: #0C1C36; border-color: #CBD5E1 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%234B617D' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    }
    html.light:not(.force-dark) select option { background-color: #FFFFFF; color: #0C1C36; }

    /* Light mode Card, Panel & Shadow refinements (Pure white cards + cool slate borders matching brand logo) */
    html.light:not(.force-dark) .ks-card,
    html.light:not(.force-dark) .ks-panel {
        background-color: #FFFFFF;
        border-color: #CBD5E1 !important;
        box-shadow: 0 2px 12px -2px rgba(12, 28, 54, 0.05), 0 1px 3px 0 rgba(12, 28, 54, 0.02);
    }
    /* Universal form inputs in light mode */
    html.light:not(.force-dark) input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="file"]):not([type="range"]):not([type="color"]):not([type="image"]),
    html.light:not(.force-dark) textarea {
        background-color: #FFFFFF !important;
        color: #0C1C36 !important;
        border-color: #CBD5E1 !important;
    }
    html.light:not(.force-dark) input::placeholder,
    html.light:not(.force-dark) textarea::placeholder {
        color: #4B617D !important;
        opacity: 0.7 !important;
    }

    html.light:not(.force-dark) .ks-input {
        background-color: #FFFFFF;
        border-color: #CBD5E1 !important;
        color: #0C1C36;
    }
    html.light:not(.force-dark) .ks-input:focus {
        background-color: #FFFFFF;
        border-color: #00A8B5 !important;
        box-shadow: 0 0 0 3px rgba(0, 168, 181, 0.16);
    }

    /* Floating Dropdown Menus & Popovers in Light Mode */
    html.light:not(.force-dark) .shadow-popover,
    html.light:not(.force-dark) .header-dropdown-menu {
        background-color: #FFFFFF !important;
        border-color: #CBD5E1 !important;
        box-shadow: 0 16px 40px -6px rgba(12, 28, 54, 0.14), 0 2px 8px rgba(12, 28, 54, 0.05) !important;
    }

    /* Crisp visible borders across all elements in Light Mode */
    html.light:not(.force-dark) .border-ink-border {
        border-color: #CBD5E1 !important;
    }
    html.light:not(.force-dark) .border-ink-border\/40,
    html.light:not(.force-dark) .border-ink-border\/50,
    html.light:not(.force-dark) .border-ink-border\/60 {
        border-color: #CBD5E1 !important;
    }
    html.light:not(.force-dark) .ks-rule {
        background-color: #CBD5E1 !important;
    }
    html.light:not(.force-dark) hr {
        border-color: #CBD5E1 !important;
    }
    html.light:not(.force-dark) table,
    html.light:not(.force-dark) th,
    html.light:not(.force-dark) td {
        border-color: #CBD5E1 !important;
    }

    /* ══════════════════════════════════════════════════════════════════ */
    /* DEDICATED LIGHT MODE BUTTON DESIGNS (ALOHIDA TUGMA DIZAYNLARI)   */
    /* ══════════════════════════════════════════════════════════════════ */

    /* 1. Primary Button: Deep Navy (Logo "D" ohangi) */
    html.light:not(.force-dark) .ks-btn-primary {
        background-color: #0A1C3C !important;
        color: #FFFFFF !important;
        border: 1px solid rgba(10, 28, 60, 0.9) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18), 0 2px 6px rgba(10, 28, 60, 0.15), 0 8px 18px -4px rgba(10, 28, 60, 0.2) !important;
    }
    html.light:not(.force-dark) .ks-btn-primary:hover {
        background-color: #122C5A !important;
        color: #FFFFFF !important;
        transform: translateY(-1px);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.28), 0 4px 10px rgba(10, 28, 60, 0.2), 0 14px 26px -4px rgba(10, 28, 60, 0.25) !important;
    }
    html.light:not(.force-dark) .ks-btn-primary:active {
        transform: scale(0.98);
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.25) !important;
    }

    /* 2. Gold / Accent Button: Vibrant Logo Teal Gradient */
    html.light:not(.force-dark) .ks-btn-gold {
        background: linear-gradient(180deg, #00A8B5 0%, #008D99 100%) !important;
        color: #FFFFFF !important;
        border: 1px solid #007C87 !important;
        font-weight: 600 !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.35), 0 2px 6px rgba(0, 168, 181, 0.25), 0 8px 18px -4px rgba(0, 168, 181, 0.22) !important;
    }
    html.light:not(.force-dark) .ks-btn-gold:hover {
        background: linear-gradient(180deg, #02BAC6 0%, #009CA8 100%) !important;
        transform: translateY(-1px);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.45), 0 4px 12px rgba(0, 168, 181, 0.32), 0 14px 26px -4px rgba(0, 168, 181, 0.26) !important;
    }
    html.light:not(.force-dark) .ks-btn-gold:active {
        transform: scale(0.98);
        box-shadow: inset 0 2px 4px rgba(0, 124, 135, 0.4) !important;
    }

    /* 3. Ghost / Secondary Button: Crisp Pure White Pill */
    html.light:not(.force-dark) .ks-btn-ghost {
        background-color: #FFFFFF !important;
        color: #0C1C36 !important;
        border: 1px solid #CBD5E1 !important;
        box-shadow: 0 1px 3px rgba(12, 28, 54, 0.04), inset 0 1px 0 rgba(255, 255, 255, 0.9) !important;
    }
    html.light:not(.force-dark) .ks-btn-ghost:hover {
        background-color: #EDF3F8 !important;
        border-color: #94A3B8 !important;
        color: #0C1C36 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px -2px rgba(12, 28, 54, 0.08) !important;
    }
    html.light:not(.force-dark) .ks-btn-ghost:active {
        transform: scale(0.98);
        background-color: #E2ECF5 !important;
    }

    html.light:not(.force-dark) .text-ink-950 {
        color: #0C1C36 !important;
    }

    /* Selection in Light Mode */
    html.light:not(.force-dark) ::selection {
        background: #00A8B5 !important;
        color: #FFFFFF !important;
    }

    /* Spotlight cards & sections in Light Mode */
    html.light:not(.force-dark) .spotlight-card {
        background: #FFFFFF !important;
        border-color: #CBD5E1 !important;
        box-shadow: 0 4px 18px -4px rgba(12, 28, 54, 0.06) !important;
    }
    html.light:not(.force-dark) .faq-item {
        background: #FFFFFF !important;
        border-color: #CBD5E1 !important;
        box-shadow: 0 2px 8px rgba(12, 28, 54, 0.03) !important;
    }
    html.light:not(.force-dark) .noise-bg {
        background-image: radial-gradient(rgba(12, 28, 54, 0.04) 1px, transparent 0) !important;
    }
    html.light:not(.force-dark) .ks-aurora {
        mix-blend-mode: multiply !important;
        opacity: 0.12 !important;
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
        -webkit-text-fill-color: #0C1C36 !important;
        -webkit-box-shadow: 0 0 0 1000px #FFFFFF inset !important;
        caret-color: #0C1C36 !important;
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
    html.light:not(.force-dark) ::-webkit-scrollbar-thumb { background: rgba(12, 28, 54, 0.18); border-radius: 8px; }
    html.light:not(.force-dark) ::-webkit-scrollbar-thumb:hover { background: rgba(0, 168, 181, 0.55); }

    /* Global Accent overrides in Light Mode */
    html.light:not(.force-dark) .border-amber-500\/30,
    html.light:not(.force-dark) .border-amber-500\/40,
    html.light:not(.force-dark) .border-amber-500\/20 {
        border-color: rgba(0, 168, 181, 0.3) !important;
    }
    html.light:not(.force-dark) .bg-amber-500\/10,
    html.light:not(.force-dark) .bg-amber-500\/15 {
        background-color: rgba(0, 168, 181, 0.1) !important;
    }
    html.light:not(.force-dark) .text-amber-400,
    html.light:not(.force-dark) .text-amber-500 {
        color: #0093A0 !important;
    }

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
            box-shadow: 0 0 0 2px rgba(0, 168, 181, 0.85), 0 0 16px rgba(0, 168, 181, 0.45);
        }
        100% {
            transform: scale(2.6);
            opacity: 0;
            box-shadow: 0 0 0 0.5px rgba(0, 168, 181, 0), 0 0 28px rgba(0, 168, 181, 0);
        }
    }

    @keyframes blinkitRingPulseLight2 {
        0% {
            transform: scale(0.75);
            opacity: 0.7;
            box-shadow: 0 0 0 3px rgba(0, 168, 181, 0.65), 0 0 24px rgba(0, 168, 181, 0.35);
        }
        100% {
            transform: scale(3.6);
            opacity: 0;
            box-shadow: 0 0 0 0.5px rgba(0, 168, 181, 0), 0 0 42px rgba(0, 168, 181, 0);
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
        background: #00A8B5;
        box-shadow: 0 0 6px #00A8B5;
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

    /* ════════════════════════════════════════════════════════════════
       Blur-In Modal & Menu Opening Animation (Kitobxon Signature)
       ════════════════════════════════════════════════════════════════ */
    /* Base overlay styles */
    .overlay,
    .overlay-blur {
        position: fixed;
        inset: 0;
        display: flex;
        padding: 16px;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        visibility: hidden;
        opacity: 0;
        overflow-y: auto;
        z-index: 999999 !important;
        transition: opacity 0.5s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.5s ease;
    }

    .overlay.open,
    .overlay-blur.open,
    .overlay.is-open,
    .overlay-blur.is-open {
        visibility: visible;
        opacity: 1;
    }

    /* Modal styling */
    .modal,
    .ks-modal-card {
        margin: auto;
        width: 100%;
        max-width: 520px;
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.6);
    }

    /* Blur In & Out Effect for static/CSS overlays */
    .overlay-blur .modal,
    .ks-blur-dialog,
    .overlay-blur .ks-modal-card {
        filter: blur(20px);
        transform: scale(1.2);
        opacity: 0;
        will-change: transform, filter, opacity;
        transition: filter .35s cubic-bezier(0.16, 1, 0.3, 1),
                    transform .35s cubic-bezier(0.16, 1, 0.3, 1),
                    opacity .3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .overlay-blur.open .modal,
    .overlay-blur.is-open .modal,
    .overlay-blur.open .ks-modal-card,
    .overlay-blur.is-open .ks-modal-card,
    .ks-blur-dialog.open,
    .ks-blur-dialog.is-open {
        filter: blur(0);
        transform: scale(1);
        opacity: 1;
        transition: filter .5s cubic-bezier(0.16, 1, 0.3, 1),
                    transform .5s cubic-bezier(0.16, 1, 0.3, 1),
                    opacity .4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* CSS Keyframe animations for Livewire and dynamic modals */
    @keyframes ksBlurInModalKeyframes {
        0% {
            filter: blur(20px);
            transform: scale(1.2);
            opacity: 0;
        }
        100% {
            filter: blur(0px);
            transform: scale(1);
            opacity: 1;
        }
    }

    @keyframes ksBlurOutModalKeyframes {
        0% {
            filter: blur(0px);
            transform: scale(1);
            opacity: 1;
        }
        100% {
            filter: blur(20px);
            transform: scale(1.18);
            opacity: 0;
        }
    }

    @keyframes ksBackdropFadeKeyframes {
        0% {
            opacity: 0;
        }
        100% {
            opacity: 1;
        }
    }

    @keyframes ksBackdropFadeOutKeyframes {
        0% {
            opacity: 1;
        }
        100% {
            opacity: 0;
        }
    }

    .ks-blur-dialog-anim,
    .ks-modal-card-anim,
    .modal-blur-in {
        animation: ksBlurInModalKeyframes 0.48s cubic-bezier(0.16, 1, 0.3, 1) both !important;
        will-change: transform, filter, opacity;
    }

    .ks-blur-out-dialog-anim,
    .modal-blur-out {
        animation: ksBlurOutModalKeyframes 0.3s cubic-bezier(0.16, 1, 0.3, 1) both !important;
        will-change: transform, filter, opacity;
    }

    .ks-backdrop-fade-anim {
        animation: ksBackdropFadeKeyframes 0.35s ease-out both !important;
    }

    .ks-backdrop-fade-out-anim {
        animation: ksBackdropFadeOutKeyframes 0.3s ease-in both !important;
    }

    /* Alpine.js transition utility classes for Blur-In modals (Enter) */
    .ks-blur-in-enter,
    .ks-blur-in-enter-start {
        filter: blur(20px) !important;
        transform: scale(1.2) !important;
        opacity: 0 !important;
    }
    .ks-blur-in-enter-active {
        transition: filter 0.48s cubic-bezier(0.16, 1, 0.3, 1),
                    transform 0.48s cubic-bezier(0.16, 1, 0.3, 1),
                    opacity 0.38s ease !important;
        will-change: transform, filter, opacity;
    }
    .ks-blur-in-enter-end {
        filter: blur(0px) !important;
        transform: scale(1) !important;
        opacity: 1 !important;
    }

    /* Alpine.js transition utility classes for Blur-Out (Leave / Exit) */
    .ks-blur-in-leave,
    .ks-blur-in-leave-start,
    .ks-blur-out-leave,
    .ks-blur-out-leave-start {
        filter: blur(0px) !important;
        transform: scale(1) !important;
        opacity: 1 !important;
    }
    .ks-blur-in-leave-active,
    .ks-blur-out-leave-active {
        transition: filter 0.32s cubic-bezier(0.16, 1, 0.3, 1),
                    transform 0.32s cubic-bezier(0.16, 1, 0.3, 1),
                    opacity 0.28s ease !important;
        will-change: transform, filter, opacity;
    }
    .ks-blur-in-leave-end,
    .ks-blur-out-leave-end {
        filter: blur(20px) !important;
        transform: scale(1.18) !important;
        opacity: 0 !important;
    }

    /* Blur for Dropdown Menus (Navigation / User menus) */
    .ks-menu-blur-enter,
    .ks-menu-blur-enter-start {
        filter: blur(14px) !important;
        transform: scale(1.1) translateY(-8px) !important;
        opacity: 0 !important;
    }
    .ks-menu-blur-enter-active {
        transition: filter 0.35s cubic-bezier(0.16, 1, 0.3, 1),
                    transform 0.35s cubic-bezier(0.16, 1, 0.3, 1),
                    opacity 0.3s ease !important;
        will-change: transform, filter, opacity;
    }
    .ks-menu-blur-enter-end {
        filter: blur(0px) !important;
        transform: scale(1) translateY(0) !important;
        opacity: 1 !important;
    }
    .ks-menu-blur-leave,
    .ks-menu-blur-leave-start {
        filter: blur(0px) !important;
        transform: scale(1) translateY(0) !important;
        opacity: 1 !important;
    }
    .ks-menu-blur-leave-active {
        transition: filter 0.25s cubic-bezier(0.16, 1, 0.3, 1),
                    transform 0.25s cubic-bezier(0.16, 1, 0.3, 1),
                    opacity 0.2s ease !important;
        will-change: transform, filter, opacity;
    }
    .ks-menu-blur-leave-end {
        filter: blur(12px) !important;
        transform: scale(0.96) translateY(-4px) !important;
        opacity: 0 !important;
    }
</style>

{{-- Motion layer: scroll-reveal, aurora, spotlight, ripple, counters, page transitions --}}
@include('partials.motion')

{{-- Universal Custom Form Validation & Tooltips (Dark & Light mode) --}}
@include('partials.custom-form-validation')
