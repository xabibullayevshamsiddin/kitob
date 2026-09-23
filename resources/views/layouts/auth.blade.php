<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Kitobxon') }} - @yield('title', 'Kirish / Ro\'yxatdan o\'tish')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">

    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            primary: {
                                50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc',
                                400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca',
                                800: '#3730a3', 900: '#1e1b4b', 950: '#0f0e2e'
                            },
                            accent: { 400: '#fbbf24', 500: '#f59e0b', 600: '#d97706' },
                            surface: { DEFAULT: '#ffffff', dark: '#0f172a', 'dark-card': '#1e293b' }
                        }
                    }
                }
            }
        </script>
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endif

    @if(class_exists('Livewire\Livewire'))
        @livewireStyles
    @endif
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col justify-center relative overflow-x-hidden font-sans selection:bg-indigo-500 selection:text-white">

    @include('components.page-loader')

    <!-- Background Ambient Glow -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -right-40 w-96 h-96 bg-amber-500/15 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 left-1/3 w-96 h-96 bg-violet-600/20 rounded-full blur-3xl"></div>
    </div>

    <div class="relative z-10 sm:mx-auto sm:w-full sm:max-w-md px-4 py-8">
        <div class="text-center mb-8">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3 group">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-500 flex items-center justify-center shadow-lg shadow-indigo-500/30 group-hover:scale-105 transition-transform duration-200">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <div class="text-left">
                    <span class="text-2xl font-black tracking-tight text-white font-manrope block leading-tight">Kitobxon</span>
                    <span class="text-xs text-indigo-300 font-medium tracking-wide uppercase">Haftalik kitob platformasi</span>
                </div>
            </a>
        </div>

        <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/60 py-8 px-6 sm:px-10 shadow-2xl rounded-3xl relative">
            {{ $slot ?? '' }}
            @yield('content')
        </div>

        <div class="mt-8 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} Kitobxon. Barcha huquqlar himoyalangan.
        </div>
    </div>

    @if(class_exists('Livewire\Livewire'))
        @livewireScripts
    @endif
    @stack('scripts')
</body>
</html>
