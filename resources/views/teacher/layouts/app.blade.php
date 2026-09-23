<!DOCTYPE html>
<html lang="uz" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'O\'qituvchi paneli' }} — Kitobxon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#1e1b4b' },
                        accent:  { 300:'#fcd34d',400:'#fbbf24',500:'#f59e0b',600:'#d97706' },
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.4s ease-out',
                        'slide-in': 'slideIn 0.3s ease-out',
                    },
                    keyframes: {
                        fadeIn:  { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                        slideIn: { '0%': { transform: 'translateX(-20px)', opacity: '0' }, '100%': { transform: 'translateX(0)', opacity: '1' } },
                    }
                }
            }
        }
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link { @apply flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white transition-all duration-200; }
        .sidebar-link.active { @apply bg-indigo-600/30 text-indigo-300 border border-indigo-500/30; }
    </style>
</head>
<body class="h-full bg-slate-100 dark:bg-slate-950 font-sans" x-data="{ sidebarOpen: false }">

<div class="flex h-full min-h-screen">

    <!-- ── Sidebar Overlay (mobile) ── -->
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen=false"
         class="fixed inset-0 bg-black/50 z-20 lg:hidden" x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    <!-- ── Sidebar ── -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 border-r border-slate-800 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:inset-auto lg:translate-x-0">

        <!-- Logo -->
        <div class="h-16 flex items-center gap-3 px-6 border-b border-slate-800">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-black text-lg shadow-lg">📚</div>
            <div>
                <p class="font-black text-white text-sm">Kitobxon</p>
                <span class="text-[11px] font-semibold px-2 py-0.5 bg-blue-600/30 text-blue-400 rounded-md border border-blue-500/30">O'qituvchi</span>
            </div>
        </div>

        <!-- Nav -->
        <nav class="flex-1 overflow-y-auto p-4 space-y-1">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 px-4 mb-2">Asosiy</p>

            <a href="{{ route('teacher.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Bosh panel
            </a>

            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 px-4 mb-2 mt-4">Kontent</p>

            <a href="{{ route('teacher.books.index') }}"
               class="sidebar-link {{ request()->routeIs('teacher.books.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Kitoblar
            </a>

            <a href="{{ route('teacher.quizzes.index') }}"
               class="sidebar-link {{ request()->routeIs('teacher.quizzes.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Testlar
            </a>

            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 px-4 mb-2 mt-4">O'quvchilar</p>

            <a href="{{ route('teacher.students.index') }}"
               class="sidebar-link {{ request()->routeIs('teacher.students.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                O'quvchilar
            </a>

            <a href="{{ route('teacher.live.index') }}"
               class="sidebar-link {{ request()->routeIs('teacher.live.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                Jonli efirlar
            </a>
        </nav>

        <!-- Sidebar footer -->
        <div class="p-4 border-t border-slate-800">
            <div class="flex items-center gap-3 px-2">
                <img src="{{ auth()->user()?->avatar_url }}" class="w-9 h-9 rounded-full ring-2 ring-slate-700 object-cover">
                <div class="min-w-0">
                    <p class="text-sm font-bold text-white truncate">{{ auth()->user()?->name }}</p>
                    <p class="text-[11px] text-slate-400">O'qituvchi</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- ── Main content ── -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Topbar -->
        <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 lg:px-6 shrink-0 shadow-sm">
            <div class="flex items-center gap-4">
                <!-- Mobile menu toggle -->
                <button @click="sidebarOpen=!sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-base font-bold text-slate-800 dark:text-white">{{ $title ?? 'O\'qituvchi paneli' }}</h1>
            </div>

            <div class="flex items-center gap-3">
                <div class="relative" x-data="{ open: false }">
                    <button @click="open=!open" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <img src="{{ auth()->user()?->avatar_url }}" class="w-8 h-8 rounded-full object-cover ring-2 ring-slate-200 dark:ring-slate-700">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open=false"
                         class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 py-1 z-50"
                         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                        <a href="{{ route('profile.show', auth()->user()?->username) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Profil
                        </a>
                        <hr class="border-slate-200 dark:border-slate-700 my-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Chiqish
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page content -->
        <main class="flex-1 overflow-y-auto p-4 lg:p-6 animate-fade-in">
            <!-- Flash messages -->
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show=false, 4000)"
                     class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-400 rounded-2xl flex items-center gap-2 text-sm font-medium">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-700 text-rose-700 dark:text-rose-400 rounded-2xl text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="h-10 border-t border-slate-200 dark:border-slate-800 flex items-center justify-center">
            <p class="text-xs text-slate-400">Kitobxon O'qituvchi Paneli &copy; {{ date('Y') }}</p>
        </footer>
    </div>
</div>

</body>
</html>
