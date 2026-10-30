<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark force-dark scroll-smooth" data-motion-zone="dense" x-data="{ mobileMenu: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Davra_books') }} — @yield('title', 'Boshqaruv paneli')</title>

    @include('partials.design-system')

    @if(class_exists('Livewire\Livewire'))
        @livewireStyles
    @endif
    @stack('styles')

    <style>
        input[type="number"] {
            -moz-appearance: textfield;
            appearance: textfield;
        }
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        [x-cloak] { display: none !important; }

        /* Slim custom scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #07090E; }
        ::-webkit-scrollbar-thumb { background: #1F293D; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #2E3E5B; }
    </style>
    @include('components.pwa-head')
</head>
<body class="bg-ink-950 text-paper font-sans selection:bg-cyan-500 selection:text-ink-950 antialiased min-h-screen relative overflow-x-hidden ks-grain">

    <!-- ── Universal Header ── -->
    <x-nav.main-header />

    <!-- ── Universal Toast Notification Container & Interface Sounds ── -->
    <x-toast-container />
    <x-interface-sounds />

    <!-- ── Universal Book Share Modal ── -->
    <x-book-share-modal />

    <!-- ── Persistent Global Audio Player (Mutolaa Dock) ── -->
    <x-global-audio-player />
    <x-global-live-player />

    <!-- ── Page Content ── -->
    <main class="relative pt-4 sm:pt-6 pb-28 sm:pb-20 px-3 sm:px-6 min-h-[calc(100vh-72px)]">
        <div class="max-w-7xl mx-auto">
            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </main>

    <!-- ── Universal Footer ── -->
    <x-nav.main-footer />

    <!-- ── Mobile Bottom Navigation Bar ── -->
    <x-nav.mobile-bottom-bar />

    @if(class_exists('Livewire\Livewire'))
        @livewireScripts
    @endif
    @stack('scripts')
</body>
</html>
