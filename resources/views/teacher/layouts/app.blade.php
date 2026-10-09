<!DOCTYPE html>
<html lang="uz" class="h-full dark force-dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'O\'qituvchi paneli' }} — Kitobxon</title>
    @include('partials.design-system')
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        * { font-family: 'Inter', sans-serif; }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 99px; }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 antialiased font-sans" x-data="{ sidebarOpen: false }">

<div class="flex h-screen overflow-hidden">

    <!-- Mobile overlay -->
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen=false"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 lg:hidden"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    <!-- ── Sidebar ── -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-40 w-64 bg-[#0a0e17] border-r border-white/[0.08] flex flex-col transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 shrink-0">

        <!-- Logo -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-white/[0.08]">
            <a href="{{ route('teacher.dashboard') }}" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-amber-500 flex items-center justify-center text-white font-black text-lg shadow-lg shadow-indigo-600/30">
                    📚
                </div>
                <div>
                    <p class="font-black text-white text-sm tracking-tight font-manrope">Kitobxon</p>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 bg-amber-400/10 text-amber-400 rounded border border-amber-400/25 uppercase tracking-wider">O'qituvchi</span>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto sidebar-scroll p-4 space-y-6">
            <!-- Asosiy -->
            <div>
                <p class="text-[10px] font-mono font-bold uppercase tracking-widest text-slate-500 px-3 mb-2">Asosiy</p>
                <div class="space-y-1">
                    <a href="{{ route('teacher.dashboard') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('teacher.dashboard') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/[0.06]' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Bosh panel</span>
                    </a>
                </div>
            </div>

            <!-- Kontent -->
            <div>
                <p class="text-[10px] font-mono font-bold uppercase tracking-widest text-slate-500 px-3 mb-2">Kontent</p>
                <div class="space-y-1">
                    <a href="{{ route('teacher.books.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('teacher.books.*') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/[0.06]' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>Kitoblar</span>
                    </a>

                    <a href="{{ route('teacher.quizzes.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('teacher.quizzes.*') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/[0.06]' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span>Testlar</span>
                    </a>
                </div>
            </div>

            <!-- O'quvchilar -->
            <div>
                <p class="text-[10px] font-mono font-bold uppercase tracking-widest text-slate-500 px-3 mb-2">O'quvchilar</p>
                <div class="space-y-1">
                    <a href="{{ route('teacher.students.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('teacher.students.*') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/[0.06]' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>O'quvchilar</span>
                    </a>

                    <a href="{{ route('teacher.live.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('teacher.live.*') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-white/[0.06]' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span>Jonli efirlar</span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Sidebar footer -->
        <div class="p-3.5 border-t border-white/[0.08] bg-white/[0.02]">
            <div class="flex items-center gap-3 px-2 py-1">
                <img src="{{ auth()->user()?->avatar_url }}" class="w-8 h-8 rounded-full ring-2 ring-amber-400/40 object-cover shrink-0">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-white truncate">{{ auth()->user()?->name }}</p>
                    <p class="text-[10px] text-slate-400 font-mono truncate">{{ '@' . auth()->user()?->username }}</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- ── Main content area ── -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden bg-slate-950">

        <!-- Topbar -->
        <header class="h-16 bg-[#0a0e17] border-b border-white/[0.08] flex items-center justify-between px-4 lg:px-8 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <!-- Mobile menu toggle -->
                <button @click="sidebarOpen=!sidebarOpen" class="lg:hidden p-2 rounded-xl bg-white/[0.04] border border-white/10 text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="min-w-0">
                    <h1 class="text-sm sm:text-base font-bold text-white truncate font-manrope">{{ $title ?? 'O\'qituvchi paneli' }}</h1>
                </div>
            </div>

            <div class="flex items-center gap-2.5 sm:gap-4">
                <!-- Saytga qaytish -->
                <a href="{{ route('home') }}"
                   class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.08] text-xs font-semibold text-slate-300 hover:text-white transition-all">
                    <span>←</span>
                    <span>Asosiy sayt</span>
                </a>

                <!-- User Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open=!open" class="flex items-center gap-2 p-1 rounded-xl hover:bg-white/[0.05] transition-colors focus:outline-none">
                        <img src="{{ auth()->user()?->avatar_url }}" class="w-8 h-8 rounded-full object-cover ring-2 ring-amber-400/40">
                        <span class="text-xs font-semibold text-slate-200 hidden md:inline max-w-[100px] truncate">{{ auth()->user()?->name }}</span>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-amber-400' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open=false"
                         class="absolute right-0 mt-2.5 w-52 p-1.5 bg-[#0a0e17]/95 backdrop-blur-2xl rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.7)] border border-white/[0.12] z-50"
                         x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95">
                        
                        <div class="px-3 py-2 border-b border-white/[0.06] mb-1">
                            <p class="text-xs font-bold text-white truncate">{{ auth()->user()?->name }}</p>
                            <span class="text-[10px] text-amber-400 font-mono">Ustoz</span>
                        </div>

                        <a href="{{ route('profile.show', auth()->user()?->username) }}" class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-slate-300 hover:text-white hover:bg-white/[0.07] rounded-xl transition-all">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7 7z"/></svg>
                            <span>Profilim</span>
                        </a>

                        <div class="my-1 border-t border-white/[0.06]"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-xl transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span>Chiqish</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page content scrollable -->
        <main class="flex-1 overflow-y-auto sidebar-scroll p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6">
                <!-- ── Universal Toast Notification Container ── -->
                <x-toast-container />

                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer class="h-10 border-t border-white/[0.08] bg-[#0a0e17] flex items-center justify-center px-4 shrink-0">
            <p class="text-[11px] text-slate-500 font-mono">Kitobxon O'qituvchi Paneli &copy; {{ date('Y') }}</p>
        </footer>
    </div>
</div>

</body>
</html>
