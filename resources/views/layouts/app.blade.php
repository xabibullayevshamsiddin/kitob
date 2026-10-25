<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-ink-950 text-paper" style="background-color: #07090E; color-scheme: dark;" x-data="{ mobileMenu: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        html, body {
            background-color: #07090E !important;
            color: #F0EDE6;
        }
        html.light:not(.force-dark), html.light:not(.force-dark) body {
            background-color: #F3EFE7 !important;
            color: #1C1916;
        }
    </style>

    @if(View::hasSection('seo'))
        @yield('seo')
    @else
        <x-seo-meta :title="trim($__env->yieldContent('title'))" />
    @endif

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
    @include('components.pwa-head')
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
                            Qoidabuzarlik tufayli chat va jamoaviy bo'limlarda xabar yuborish huquqingiz cheklangan. Agar sizga noto'g'ri ban berilgan bo'lsa, Aloqa bo'limiga yozib qoldiring.
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
                <a href="{{ route('contact', ['appeal' => 1, 'user_id' => auth()->id()]) }}" class="ks-btn-ghost py-2 px-3.5 text-xs font-semibold text-rose-200 bg-rose-500/15 border-rose-500/40 hover:bg-rose-500/25 hover:border-rose-500/60 flex-shrink-0 flex items-center gap-1.5">
                    <span>📩 Noto'g'ri ban berildimi? (Aloqa)</span>
                    <span>→</span>
                </a>
            </div>
        @endif
    </div>

    <!-- ── Universal Toast Notification Container ── -->
    <x-toast-container />

    <!-- ── Universal Custom Confirmation Modal ── -->
    <x-confirm-modal />

    <!-- ── Universal Book Share Modal ── -->
    <x-book-share-modal />

    <!-- ── Persistent Global Audio Player (Mutolaa Dock) ── -->
    <x-global-audio-player />
    <x-global-live-player />

    <!-- ── Page Content ── -->
    <main class="pt-4 sm:pt-6 pb-28 sm:pb-20 px-3 sm:px-6 min-h-[calc(100vh-72px)]">
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

    <!-- ── AI Keyboard Typing Sound Effect ── -->
    <x-ai-typing-sound />
</body>
</html>
