<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code', '404') — @yield('title', 'Kitobxon') | Kitobxon</title>

    {{-- Theme initialization (FOUC prevention) --}}
    <script>
        (function() {
            try {
                var el = document.documentElement;
                var saved = localStorage.getItem('kitob_theme');
                if (saved === 'light') {
                    el.classList.remove('dark');
                    el.classList.add('light');
                } else if (saved === 'dark') {
                    el.classList.remove('light');
                    el.classList.add('dark');
                } else {
                    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
                        el.classList.remove('dark');
                        el.classList.add('light');
                    } else {
                        el.classList.remove('light');
                        el.classList.add('dark');
                    }
                }
            } catch(e) {}
        })();
    </script>

    <style>
        @font-face {
            font-family: "Geist Mono:SemiBold";
            font-style: normal;
            font-weight: 600;
            font-display: swap;
            src: url("https://static.figma.com/font/GeistMono_wght__1") format("woff2");
        }

        :root,
        html {
            --bg-fallback: #000000;
            --text-color: #ffffff;
            --divider-color: #ffffff;
            --gradient-start: rgb(255, 255, 255);
            --gradient-end: rgba(255, 255, 255, 0.4);
            --bg-overlay: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.48) 0%, rgba(0, 0, 0, 0.78) 100%);
            --video-brightness: 0.82;
            background-color: #000000 !important;
            color: #ffffff !important;
        }

        html.light {
            --bg-overlay: rgba(0, 0, 0, 0.28);
            --video-brightness: 0.95;
        }

        html.dark {
            --bg-overlay: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.48) 0%, rgba(0, 0, 0, 0.78) 100%);
            --video-brightness: 0.82;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-fallback);
            color: var(--text-color);
            min-height: 100svh;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
            font-family: "Geist Mono:SemiBold", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-weight: 600;
            user-select: none;
            -webkit-user-select: none;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .bg-error-video {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 1;
            z-index: 0;
            filter: brightness(var(--video-brightness, 0.82));
            transition: filter 0.4s ease;
            pointer-events: none;
        }

        .bg-error-overlay {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            background: var(--bg-overlay);
            z-index: 1;
            pointer-events: none;
            transition: background 0.4s ease;
        }

        /* ── Centered Content Group ── */
        .error-center-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 483px;
            z-index: 20;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 44px;
        }

        /* ── Heading ── */
        .error-heading {
            font-family: "Geist Mono:SemiBold", ui-monospace, monospace;
            font-weight: 600;
            font-size: 295.751px;
            line-height: 1.1;
            letter-spacing: -24.6459px;
            text-align: center;
            padding-bottom: 0.08em;
            height: auto;
            min-height: 0;
            background-image: linear-gradient(
                247.3282658084845deg,
                var(--gradient-start) 2.5334%,
                var(--gradient-end) 93.612%
            );
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin: 0;
        }

        /* ── Divider ── */
        .error-divider {
            width: 425px;
            height: 1px;
            background-color: var(--divider-color);
            transition: background-color 0.3s ease;
        }

        /* ── Message ── */
        .error-message {
            font-family: "Geist Mono:SemiBold", ui-monospace, monospace;
            font-weight: 600;
            font-size: 24px;
            line-height: 1.1;
            letter-spacing: -2px;
            text-align: center;
            color: var(--text-color);
            width: 100%;
            transition: color 0.3s ease;
            text-decoration: none;
            display: block;
        }

        /* ── Mobile Behavior (max-width: 640px) ── */
        @media (max-width: 640px) {
            .error-center-content {
                width: min(calc(100% - 40px), 360px);
                gap: 28px;
            }

            .error-heading {
                font-size: clamp(140px, 52vw, 200px);
                letter-spacing: -0.09em;
                padding-bottom: 0.05em;
            }

            .error-divider {
                width: 100%;
            }

            .error-message {
                font-size: clamp(16px, 4.5vw, 20px);
                letter-spacing: -1.3px;
            }
        }
    </style>
    @include('components.pwa-head')
</head>
<body>

    @php
        $errCode = trim($__env->yieldContent('code', '404'));
        $validErrorCodes = ['403', '404', '419', '429', '500', '503'];
        $bgCode = in_array($errCode, $validErrorCodes) ? $errCode : '404';
    @endphp

    <!-- Semantic Full-Page Background Video (Code-Specific Literary Stone Engraving) -->
    <video
        autoplay
        loop
        muted
        playsinline
        preload="metadata"
        poster="{{ asset('media/errors/poster-' . $bgCode . '.webp') }}"
        aria-hidden="true"
        class="bg-error-video"
        id="error-bg-video"
    >
        <source src="{{ asset('media/errors/bg-' . $bgCode . '.webm') }}" type="video/webm">
        <source src="{{ asset('media/errors/bg-' . $bgCode . '.mp4') }}" type="video/mp4">
        <source src="{{ asset('media/error-bg.webm') }}" type="video/webm">
        <source src="{{ asset('media/error-bg.mp4') }}" type="video/mp4">
    </video>

    <!-- Subtle Black Overlay / Blend Layer -->
    <div class="bg-error-overlay" aria-hidden="true"></div>

    <main>
        <!-- Centered Error Content -->
        <section class="error-center-content">
            <!-- 1. Heading -->
            <h1 class="error-heading">@yield('code', '404')</h1>

            <!-- 2. Thin Horizontal Divider -->
            <div class="error-divider"></div>

            <!-- 3. Message -->
            <a href="{{ route('home') }}" class="error-message" id="error-message-text" title="{{ __('site.common.back_home') }}">
                @yield('message', "Bu sahifa kitob javonimizda topilmadi. Keling, sizni mutolaaga qaytaramiz.")
            </a>

            @if(config('app.debug') && isset($exception) && is_object($exception) && method_exists($exception, 'getMessage') && $exception->getMessage())
                <div style="margin-top: 1.5rem; padding: 1rem; border-radius: 8px; background: rgba(69, 10, 10, 0.9); border: 1px solid rgba(239, 68, 68, 0.5); color: #fecaca; font-family: monospace; font-size: 12px; text-align: left; max-width: 650px; margin-left: auto; margin-right: auto; overflow-x: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
                    <div style="font-weight: bold; color: #f87171; margin-bottom: 0.25rem;">
                        ⚠️ {{ get_class($exception) }}
                    </div>
                    <div style="font-size: 13px; font-weight: 600; color: #fff; margin-bottom: 0.5rem; word-break: break-word;">
                        {{ $exception->getMessage() }}
                    </div>
                    <div style="font-size: 11px; color: #fbbf24;">
                        📍 {{ $exception->getFile() }}:{{ $exception->getLine() }}
                    </div>
                </div>
            @endif
        </section>
    </main>

    {{-- Seamless Theme ('T') & Language ('L') Keyboard Toggles --}}
    @php
        $errCode = trim($__env->yieldContent('code', '404'));
        $messagesData = [
            'uz' => __('site.errors.e' . $errCode . '_msg', [], 'uz'),
            'ru' => __('site.errors.e' . $errCode . '_msg', [], 'ru'),
            'en' => __('site.errors.e' . $errCode . '_msg', [], 'en'),
        ];
    @endphp
    <script>
        (function() {
            var errorMessages = @json($messagesData);
            var currentLocale = "{{ app()->getLocale() }}";
            var languages = ['uz', 'ru', 'en'];

            function updateUIForLocale(lang) {
                var msgEl = document.getElementById('error-message-text');
                if (msgEl && errorMessages[lang]) {
                    msgEl.textContent = errorMessages[lang];
                }
            }

            document.addEventListener('keydown', function(e) {
                // Key 'T': Toggle Light / Dark mode
                if ((e.key === 't' || e.key === 'T') && !e.ctrlKey && !e.metaKey && !e.altKey) {
                    var el = document.documentElement;
                    var isDark = el.classList.contains('dark');
                    if (isDark) {
                        el.classList.remove('dark');
                        el.classList.add('light');
                        try { localStorage.setItem('kitob_theme', 'light'); } catch(err) {}
                    } else {
                        el.classList.remove('light');
                        el.classList.add('dark');
                        try { localStorage.setItem('kitob_theme', 'dark'); } catch(err) {}
                    }
                }

                // Key 'L': Cycle Language (uz -> ru -> en -> uz)
                if ((e.key === 'l' || e.key === 'L') && !e.ctrlKey && !e.metaKey && !e.altKey) {
                    var nextIdx = (languages.indexOf(currentLocale) + 1) % languages.length;
                    currentLocale = languages[nextIdx];
                    updateUIForLocale(currentLocale);

                    // Persist selected language via background request
                    try {
                        var url = new URL(window.location.href);
                        url.searchParams.set('lang', currentLocale);
                        fetch(url.toString(), { method: 'GET', credentials: 'same-origin' }).catch(function(){});
                    } catch(err) {}
                }
            });

            // Respect prefers-reduced-motion and Save-Data
            (function() {
                var video = document.getElementById('error-bg-video');
                if (!video) return;
                var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                var saveData = navigator.connection && (navigator.connection.saveData === true);
                if (prefersReduced || saveData) {
                    video.pause();
                    video.removeAttribute('autoplay');
                }
            })();
        })();
    </script>
</body>
</html>
