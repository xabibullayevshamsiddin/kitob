<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Kitobxon') }} — @yield('title', 'Kirish / Ro\'yxatdan o\'tish')</title>

    @include('partials.design-system')

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>

    @if(class_exists('Livewire\Livewire'))
        @livewireStyles
    @endif

    <style>
        [x-cloak] { display: none !important; }
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
    </style>
</head>
<body class="bg-ink-950 text-paper min-h-screen flex flex-col justify-center relative overflow-x-hidden font-sans selection:bg-amber-500 selection:text-ink-950 ks-grain">

    @include('components.page-loader')

    <div class="relative z-10 w-full max-w-5xl mx-auto px-4 py-8 sm:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            <!-- Left Side (Editorial Branding, Desktop only) -->
            <div class="hidden lg:flex lg:col-span-5 flex-col justify-between space-y-8 pr-4">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3 group">
                    <span class="w-10 h-10 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center transition-transform group-hover:scale-105">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    </span>
                    <div>
                        <span class="text-2xl font-bold font-serif text-paper block leading-tight">Kitobxon</span>
                        <span class="text-[10px] font-mono uppercase tracking-widest text-mist">Haftalik mutolaa</span>
                    </div>
                </a>

                <div class="space-y-4 border-l-2 border-amber-500/40 pl-5">
                    <blockquote class="text-base text-paper font-serif italic leading-relaxed">
                        "Kitob — eng sokin va doimiy do'st, eng dono va ochiq maslahatgo'y hamda eng sabr-toqatli ustozdir."
                    </blockquote>
                    <p class="text-xs font-mono text-mist uppercase tracking-wider">— Charlz V. Eliot</p>
                </div>

                <div class="space-y-2 pt-4 border-t border-ink-border">
                    <div class="flex items-center gap-2 text-xs text-mist">
                        <span class="text-amber-500 font-bold">✦</span>
                        <span>Har hafta yangi sara asar mutolaasi</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-mist">
                        <span class="text-amber-500 font-bold">✦</span>
                        <span>Jonli muhokama, tahlil va gamifikatsiya</span>
                    </div>
                </div>
            </div>

            <!-- Right Side (Auth Form Container) -->
            <div class="lg:col-span-7 max-w-md mx-auto w-full">
                <div class="flex items-center justify-between mb-4">
                    <a href="{{ url('/') }}" class="lg:hidden inline-flex items-center gap-2 text-paper font-serif font-bold text-lg">
                        <span class="w-7 h-7 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        </span>
                        <span>Kitobxon</span>
                    </a>
                    <div class="ml-auto">
                        <x-lang-switcher />
                    </div>
                </div>

                <div class="ks-panel p-6 sm:p-8 bg-ink-900 border border-ink-border relative">
                    {{ $slot ?? '' }}
                    @yield('content')
                </div>

                <div class="mt-6 text-center text-xs font-mono text-mist">
                    &copy; {{ date('Y') }} Kitobxon. {{ __('site.footer.rights') }}.
                </div>
            </div>

        </div>
    </div>

    <!-- ── Universal Toast Notification Container ── -->
    <x-toast-container />

    @if(class_exists('Livewire\Livewire'))
        @livewireScripts
    @endif
    @stack('scripts')
</body>
</html>
