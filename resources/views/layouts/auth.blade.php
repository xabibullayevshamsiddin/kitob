<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Davra_books') }} — @yield('title', 'Kirish / Ro\'yxatdan o\'tish')</title>

    @include('partials.design-system')

    @if(class_exists('Livewire\Livewire'))
        @livewireStyles
    @endif

    <style>
        [x-cloak] { display: none !important; }
        :root, html.dark { color-scheme: dark; }
        html.light:not(.force-dark) { color-scheme: light; }

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

        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        textarea:-webkit-autofill,
        select:-webkit-autofill {
            -webkit-text-fill-color: #F0EDE6 !important;
            -webkit-box-shadow: 0 0 0 1000px #0F141F inset !important;
            caret-color: #F0EDE6 !important;
            transition: background-color 9999s ease-in-out 0s !important;
        }
        html.light:not(.force-dark) input:-webkit-autofill,
        html.light:not(.force-dark) input:-webkit-autofill:hover,
        html.light:not(.force-dark) input:-webkit-autofill:focus,
        html.light:not(.force-dark) textarea:-webkit-autofill,
        html.light:not(.force-dark) select:-webkit-autofill {
            -webkit-text-fill-color: #1C1916 !important;
            -webkit-box-shadow: 0 0 0 1000px #F9F6F0 inset !important;
            caret-color: #1C1916 !important;
        }
    </style>
    @include('components.pwa-head')
</head>
<body class="bg-ink-950 text-paper min-h-screen flex flex-col justify-center relative overflow-x-hidden font-sans selection:bg-cyan-500 selection:text-ink-950 ks-grain">

    @include('components.page-loader')

    <div class="relative z-10 w-full max-w-5xl mx-auto px-4 py-8 sm:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            @php
                $isOnboardingRoute = request()->routeIs('onboarding');
                $authLoc = app()->getLocale();
                $brandSubtitle = match($authLoc) {
                    'ru' => 'Еженедельное чтение',
                    'en' => 'Weekly Reading Club',
                    default => 'Haftalik mutolaa',
                };
                $brandQuote = match($authLoc) {
                    'ru' => '"Книги — это самые тихие и постоянные друзья; они самые доступные и мудрые советники и самые терпеливые учителя."',
                    'en' => '"Books are the quietest and most constant of friends; they are the most accessible and wisest of counselors, and the most patient of teachers."',
                    default => '"Kitob — eng sokin va doimiy do\'st, eng dono va ochiq maslahatgo\'y hamda eng sabr-toqatli ustozdir."',
                };
                $brandAuthor = match($authLoc) {
                    'ru' => '— Чарльз У. Элиот',
                    'en' => '— Charles W. Eliot',
                    default => '— Charlz V. Eliot',
                };
                $brandPoint1 = match($authLoc) {
                    'ru' => 'Каждую неделю — чтение новой избранной книги',
                    'en' => 'A new curated masterpiece every week',
                    default => 'Har hafta yangi sara asar mutolaasi',
                };
                $brandPoint2 = match($authLoc) {
                    'ru' => 'Живые обсуждения, разборы и геймификация',
                    'en' => 'Live discussions, analysis & gamification',
                    default => 'Jonli muhokama, tahlil va gamifikatsiya',
                };
                $brandPoint3 = match($authLoc) {
                    'ru' => 'Персональный план чтения на 3 языках (UZ / RU / EN)',
                    'en' => 'Personalized reading plan in 3 languages (UZ / RU / EN)',
                    default => '3 tilda shaxsiy mutolaa rejasi (UZ / RU / EN)',
                };
            @endphp

            <!-- Left Side (Editorial Branding, Desktop only) -->
            <div class="hidden lg:flex {{ $isOnboardingRoute ? 'lg:col-span-4' : 'lg:col-span-5' }} flex-col justify-between space-y-8 pr-4">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3 group">
                    <span class="w-10 h-10 rounded-xl overflow-hidden bg-white p-0.5 border border-ink-border flex items-center justify-center transition-transform group-hover:scale-105 shadow-sm">
                        <img src="{{ asset('storage/logo/photo_2026-10-26_14-48-15.jpg') }}" alt="Davra_books" class="w-full h-full object-contain rounded-lg">
                    </span>
                    <div>
                        <span class="text-2xl font-bold font-serif text-paper block leading-tight">Davra_books</span>
                        <span class="text-[10px] font-mono uppercase tracking-widest text-mist">{{ $brandSubtitle }}</span>
                    </div>
                </a>

                <div class="space-y-4 border-l-2 border-amber-500/40 pl-5">
                    <blockquote class="text-base text-paper font-serif italic leading-relaxed">
                        {{ $brandQuote }}
                    </blockquote>
                    <p class="text-xs font-mono text-mist uppercase tracking-wider">{{ $brandAuthor }}</p>
                </div>

                <div class="space-y-2.5 pt-4 border-t border-ink-border">
                    <div class="flex items-center gap-2 text-xs text-mist">
                        <span class="text-amber-500 font-bold">✦</span>
                        <span>{{ $brandPoint1 }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-mist">
                        <span class="text-amber-500 font-bold">✦</span>
                        <span>{{ $brandPoint2 }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-mist">
                        <span class="text-amber-500 font-bold">✦</span>
                        <span>{{ $brandPoint3 }}</span>
                    </div>
                </div>
            </div>

            <!-- Right Side (Auth Form Container) -->
            <div class="{{ $isOnboardingRoute ? 'lg:col-span-8 max-w-xl' : 'lg:col-span-7 max-w-md' }} mx-auto w-full">
                <div class="flex items-center justify-between mb-4">
                    <a href="{{ url('/') }}" class="lg:hidden inline-flex items-center gap-2 text-paper font-serif font-bold text-lg">
                        <span class="w-7 h-7 rounded-btn bg-cyan-500/10 border border-cyan-500/20 text-cyan-500 flex items-center justify-center">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        </span>
                        <span>Davra_books</span>
                    </a>
                    <div class="ml-auto flex items-center gap-2">
                        <x-lang-switcher />
                        <button onclick="window.__setKitobTheme && window.__setKitobTheme(document.documentElement.classList.contains('light') ? 'dark' : 'light', event)" 
                                type="button"
                                class="header-theme-btn relative inline-flex items-center justify-center w-8 h-8 rounded-btn border border-ink-border bg-ink-800 text-mist hover:text-paper hover:bg-ink-700 transition-colors duration-base select-none shrink-0 group focus:outline-none"
                                title="Mavzuni almashtirish (Yorug' / Qorong'i)"
                                aria-label="Mavzuni almashtirish">
                            <!-- Sun icon (Dark mode) -->
                            <svg class="w-4 h-4 text-amber-400 hidden dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="4"/>
                                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                            </svg>
                            <!-- Moon icon (Light mode) -->
                            <svg class="w-4 h-4 text-amber-600 block dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="ks-panel p-6 sm:p-8 bg-ink-900 border border-ink-border relative">
                    {{ $slot ?? '' }}
                    @yield('content')
                </div>

                <div class="mt-6 text-center text-xs font-mono text-mist">
                    &copy; {{ date('Y') }} Davra_books. {{ __('site.footer.rights') }}.
                </div>
            </div>

        </div>
    </div>

    <!-- ── Universal Toast Notification Container & Interface Sounds ── -->
    <x-toast-container />
    <x-interface-sounds />

    @if(class_exists('Livewire\Livewire'))
        @livewireScripts
    @endif
    @stack('scripts')
</body>
</html>
