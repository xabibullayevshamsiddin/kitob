<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" x-data="{ mobileMenu: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Kitobxon') }} — @yield('title', "Kitob o'qish platformasi")</title>

    @include('partials.design-system')

    <!-- Alpine.js + Plugins (Collapse must load before Alpine starts) -->
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Vite Bundled Scripts (Pusher, Echo, Axios) -->
    @vite(['resources/js/app.js'])

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

        /* Slim custom scrollbar for dark chrome */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #07090E; }
        ::-webkit-scrollbar-thumb { background: #1F293D; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #2E3E5B; }

        /* Autofill dark styling */
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
<body class="bg-ink-950 text-paper font-sans selection:bg-amber-500 selection:text-ink-950 antialiased min-h-screen relative ks-grain">

    <!-- ── Universal Header ── -->
    <x-nav.main-header />

    <!-- ── Flash Messages & Ban Alert ── -->
    <div class="max-w-7xl mx-auto px-6">
        @if (auth()->check() && auth()->user()->isBanned())
            <div class="mt-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 bg-ink-900 border border-rose-500/30 text-rose-300 rounded-card">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-btn bg-rose-500/15 text-rose-300 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-rose-300">Sizning profilingiz cheklangan (Bloklangan)</h4>
                        <p class="text-xs text-mist mt-0.5">
                            Qoidabuzarlik tufayli chat va jamoaviy bo'limlarda xabar yuborish huquqingiz cheklangan.
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-3 text-xs">
                            <span class="px-2 py-0.5 rounded-badge bg-ink-950 border border-rose-500/30 font-mono text-rose-300 text-[11px]">
                                Qolgan muddat: {{ auth()->user()->ban_remaining }}
                            </span>
                            @if(auth()->user()->ban_reason)
                                <span class="text-mist italic text-[11px]">
                                    Sabab: "{{ auth()->user()->ban_reason }}"
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <a href="{{ route('contact') }}" class="ks-btn-ghost py-1.5 px-3 text-xs text-rose-300 border-rose-500/30 hover:border-rose-500/50 flex-shrink-0">
                    Bog'lanish
                </a>
            </div>
        @endif
    </div>

    <!-- ── Universal Toast Notification Container ── -->
    <x-toast-container />

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
