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

    html.light:not(.force-dark) .text-ink-950 {
        color: #181613 !important;
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

    /* Blur In Effect */
    .overlay-blur .modal,
    .ks-blur-dialog,
    .overlay-blur .ks-modal-card {
        filter: blur(20px);
        transform: scale(1.2);
        opacity: 0;
        will-change: transform, filter, opacity;
        transition: filter .7s cubic-bezier(0.16, 1, 0.3, 1),
                    transform .7s cubic-bezier(0.16, 1, 0.3, 1),
                    opacity .5s cubic-bezier(0.16, 1, 0.3, 1);
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
    }

    /* Alpine.js transition utility classes for Blur-In modals */
    .ks-blur-in-enter {
        filter: blur(20px) !important;
        transform: scale(1.2) !important;
        opacity: 0 !important;
    }
    .ks-blur-in-enter-active {
        transition: filter 0.65s cubic-bezier(0.16, 1, 0.3, 1),
                    transform 0.65s cubic-bezier(0.16, 1, 0.3, 1),
                    opacity 0.5s cubic-bezier(0.16, 1, 0.3, 1) !important;
        will-change: transform, filter, opacity;
    }
    .ks-blur-in-enter-end {
        filter: blur(0px) !important;
        transform: scale(1) !important;
        opacity: 1 !important;
    }
    .ks-blur-in-leave {
        filter: blur(0px) !important;
        transform: scale(1) !important;
        opacity: 1 !important;
    }
    .ks-blur-in-leave-active {
        transition: filter 0.35s ease,
                    transform 0.35s cubic-bezier(0.16, 1, 0.3, 1),
                    opacity 0.3s ease !important;
        will-change: transform, filter, opacity;
    }
    .ks-blur-in-leave-end {
        filter: blur(16px) !important;
        transform: scale(1.15) !important;
        opacity: 0 !important;
    }

    /* Blur-In for Dropdown Menus (Navigation / User menus) */
    .ks-menu-blur-enter {
        filter: blur(14px) !important;
        transform: scale(1.1) translateY(-8px) !important;
        opacity: 0 !important;
    }
    .ks-menu-blur-enter-active {
        transition: filter 0.4s cubic-bezier(0.16, 1, 0.3, 1),
                    transform 0.4s cubic-bezier(0.16, 1, 0.3, 1),
                    opacity 0.35s ease !important;
        will-change: transform, filter, opacity;
    }
    .ks-menu-blur-enter-end {
        filter: blur(0px) !important;
        transform: scale(1) translateY(0) !important;
        opacity: 1 !important;
    }
    .ks-menu-blur-leave {
        filter: blur(0px) !important;
        transform: scale(1) translateY(0) !important;
        opacity: 1 !important;
    }
    .ks-menu-blur-leave-active {
        transition: filter 0.25s ease,
                    transform 0.25s ease,
                    opacity 0.2s ease !important;
        will-change: transform, filter, opacity;
    }
    .ks-menu-blur-leave-end {
        filter: blur(10px) !important;
        transform: scale(0.96) translateY(-4px) !important;
        opacity: 0 !important;
    }
</style>

{{-- Motion layer: scroll-reveal, aurora, spotlight, ripple, counters, page transitions --}}
@include('partials.motion')

{{-- Universal Custom Form Validation & Tooltips (Dark & Light mode) --}}
@include('partials.custom-form-validation')
