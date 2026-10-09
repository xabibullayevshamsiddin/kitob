<!DOCTYPE html>
<html lang="uz" class="dark force-dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') — Kitobxon Admin</title>

    @include('partials.design-system')

    <!-- Alpine.js CDN -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* Sidebar scrollbar */
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #1F293D; border-radius: 99px; border: 0; }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover { background: #2A3650; }

        .admin-shell { height: 100vh; height: 100dvh; }
        .admin-table-scroll {
            max-width: 100%;
            overflow-x: auto;
            overscroll-behavior-x: contain;
            -webkit-overflow-scrolling: touch;
            scrollbar-color: #475569 #0D111A;
            scrollbar-width: thin;
        }
        .admin-table-scroll::-webkit-scrollbar { height: 6px; }
        .admin-table-scroll::-webkit-scrollbar-track { background: #0D111A; }
        .admin-table-scroll::-webkit-scrollbar-thumb { background: #475569; border-radius: 99px; }
        @media (max-width: 639px) {
            .admin-table-scroll > table { min-width: 48rem; }
        }

        /* Nav — editorial: amber 2px chap chiziq + ink-800 fon (gradient yo'q) */
        .nav-item {
            position: relative;
            border-left: 2px solid transparent;
            transition: background-color 200ms cubic-bezier(0.16,1,0.3,1), color 200ms cubic-bezier(0.16,1,0.3,1), border-color 200ms cubic-bezier(0.16,1,0.3,1);
        }
        .nav-item:hover { background: #0D111A; border-left-color: #2A3650; }
        .nav-active,
        .nav-active:hover { background: #131926; border-left-color: #F59E0B; color: #F0EDE6; }
        .nav-active .nav-icon { color: #FBBF24; }

        /* Tooltip */
        [data-tooltip] { position: relative; }
        [data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 50%;
            bottom: calc(100% + 6px);
            transform: translateX(-50%);
            background: #131926;
            color: #F0EDE6;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-family: "DM Mono", ui-monospace, monospace;
            white-space: nowrap;
            z-index: 50;
            border: 1px solid #1F293D;
            pointer-events: none;
        }

        /* Skeleton loader */
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .shimmer {
            background: linear-gradient(90deg, #0D111A 25%, #131926 50%, #0D111A 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-ink-950 text-paper antialiased" x-data="adminLayout()">

    <!-- Mobile overlay -->
    <div
        x-show="sidebarOpen && isMobile"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-30 bg-ink-950/80 lg:hidden"
        style="display:none;"
    ></div>

    @php
        $pendingReportsCount = \App\Models\Report::pending()->count();
        $navIcon = function (string $name) {
            $paths = [
                'dashboard' => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
                'users'     => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
                'flag'      => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" x2="4" y1="22" y2="15"/>',
                'book'      => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
                'audio'     => '<path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/>',
                'video'     => '<path d="m22 8-6 4 6 4V8Z"/><rect width="14" height="12" x="2" y="6" rx="2" ry="2"/>',
                'quiz'      => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>',
                'stats'     => '<path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>',
                'settings'  => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
            ];
            return '<svg class="nav-icon w-4 h-4 flex-shrink-0 text-mist transition-colors duration-base" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$name] ?? '') . '</svg>';
        };
        $navGroups = [
            'Asosiy' => [
                ['route' => 'admin.dashboard',   'match' => 'admin.dashboard', 'label' => 'Bosh panel',          'icon' => 'dashboard'],
                ['route' => 'admin.users.index', 'match' => 'admin.users.*',   'label' => 'Foydalanuvchilar',    'icon' => 'users'],
                ['route' => 'admin.reports.index','match' => 'admin.reports.*','label' => 'Aloqa & Shikoyatlar', 'icon' => 'flag', 'badge' => $pendingReportsCount],
                ['route' => 'admin.books.index', 'match' => 'admin.books.*',   'label' => 'Kitoblar',            'icon' => 'book'],
            ],
            'Kontent' => [
                ['route' => 'admin.audios.index',  'match' => 'admin.audios.*',  'label' => 'Audiolar',          'icon' => 'audio'],
                ['route' => 'admin.videos.index',  'match' => 'admin.videos.*',  'label' => 'Videolar',          'icon' => 'video'],
                ['route' => 'admin.quizzes.index', 'match' => 'admin.quizzes.*', 'label' => 'Test topshiriqlari','icon' => 'quiz'],
            ],
            'Tahlil' => [
                ['route' => 'admin.stats', 'match' => 'admin.stats', 'label' => 'Statistika', 'icon' => 'stats'],
            ],
            'Tizim' => [
                ['route' => 'admin.settings', 'match' => 'admin.settings', 'label' => 'Sozlamalar', 'icon' => 'settings'],
            ],
        ];
    @endphp

    <div class="admin-shell flex overflow-hidden">

        <!-- ═══════════════ SIDEBAR ═══════════════ -->
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-40 flex flex-col w-60 bg-ink-950 border-r border-ink-border transition-transform duration-modal ease-out lg:relative lg:z-auto sidebar-scroll overflow-y-auto"
        >
            <!-- Logo -->
            <div class="flex items-center justify-between h-14 px-4 border-b border-ink-border flex-shrink-0">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 group">
                    <span class="w-8 h-8 rounded-btn bg-vermilion flex items-center justify-center text-paper">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                    </span>
                    <span class="leading-tight">
                        <span class="block font-display text-[15px] font-semibold text-paper">Kitobxon</span>
                        <span class="block font-mono text-[10px] uppercase tracking-[0.14em] text-mist">Admin</span>
                    </span>
                </a>
                <span class="font-mono text-[10px] text-mist border border-ink-border px-1.5 py-0.5 rounded-badge">v1.0</span>
            </div>

            <!-- Nav -->
            <nav class="flex-1 py-3">
                @foreach($navGroups as $groupLabel => $items)
                    <p class="ks-eyebrow px-4 {{ $loop->first ? 'pt-1' : 'pt-4' }} pb-1.5 text-[10px]">{{ $groupLabel }}</p>
                    @foreach($items as $item)
                        @php $active = request()->routeIs($item['match']); @endphp
                        <a href="{{ route($item['route']) }}"
                           class="nav-item flex items-center gap-2.5 min-h-11 px-4 text-[13px] font-medium {{ $active ? 'nav-active' : 'text-mist hover:text-paper' }}">
                            {!! $navIcon($item['icon']) !!}
                            <span class="truncate">{{ $item['label'] }}</span>
                            @if(!empty($item['badge']))
                                <span class="ml-auto min-w-[20px] text-center px-1.5 py-0.5 rounded-badge font-mono text-[10px] font-bold bg-rose-500 text-paper">
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                @endforeach
            </nav>

            <!-- Sidebar footer -->
            <div class="flex-shrink-0 px-4 py-3 border-t border-ink-border">
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="flex items-center gap-2 text-xs text-mist hover:text-paper transition-colors duration-base">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                    Saytga qaytish
                </a>
            </div>
        </aside>

        <!-- ═══════════════ MAIN CONTENT AREA ═══════════════ -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <!-- TOP BAR -->
            <header class="flex-shrink-0 h-14 bg-ink-950 border-b border-ink-border flex items-center justify-between gap-2 px-3 sm:px-4 lg:px-5 z-20">

                <!-- Left: Mobile toggle + Breadcrumb -->
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <button @click="sidebarOpen = !sidebarOpen" aria-label="Menyu"
                            :aria-expanded="sidebarOpen.toString()"
                            class="lg:hidden w-11 h-11 shrink-0 flex items-center justify-center rounded-btn border border-ink-border text-mist hover:text-paper hover:bg-ink-800 transition-colors duration-base">
                        <svg x-show="!sidebarOpen" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                        <svg x-show="sidebarOpen" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>

                    <!-- Breadcrumb -->
                    <nav class="hidden sm:flex items-center gap-2 font-mono text-[11px] uppercase tracking-wider min-w-0">
                        <a href="{{ route('admin.dashboard') }}" class="text-mist hover:text-paper transition-colors duration-base">Admin</a>
                        <span class="text-ink-600">/</span>
                        @if(View::hasSection('breadcrumb'))
                            <span class="text-paper truncate">@yield('breadcrumb')</span>
                        @else
                            <span class="text-paper truncate">@yield('title', 'Bosh panel')</span>
                        @endif
                    </nav>
                </div>

                <!-- Right: notifications + user -->
                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">

                    <!-- Notification bell -->
                    <a href="{{ route('admin.reports.index') }}" aria-label="Shikoyatlar"
                       class="relative w-10 h-10 flex items-center justify-center rounded-btn border border-ink-border text-mist hover:text-paper hover:bg-ink-800 transition-colors duration-base">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        @if($pendingReportsCount > 0)
                            <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                        @endif
                    </a>

                    <!-- User dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false"
                                class="flex items-center gap-2 h-9 pl-1 pr-2 rounded-btn border border-ink-border hover:bg-ink-800 transition-colors duration-base">
                            <span class="w-7 h-7 rounded-badge bg-ink-800 border border-ink-border flex items-center justify-center font-mono text-xs font-semibold text-amber-400">
                                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                            </span>
                            <span class="hidden sm:block text-left leading-tight">
                                <span class="block text-xs font-medium text-paper">{{ auth()->user()->name ?? 'Admin' }}</span>
                                <span class="block font-mono text-[10px] text-mist">Administrator</span>
                            </span>
                            <svg class="w-3.5 h-3.5 text-mist transition-transform duration-base" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </button>

                        <!-- Dropdown menu -->
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="absolute right-0 mt-1.5 w-52 bg-ink-800 border border-ink-border rounded-card shadow-popover z-50 overflow-hidden"
                             style="display:none;">
                            <div class="px-3.5 py-2.5 border-b border-ink-border">
                                <p class="text-xs font-medium text-paper truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                                <p class="font-mono text-[11px] text-mist mt-0.5 truncate">{{ auth()->user()->email ?? 'admin@kitobxon.uz' }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('settings') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-paper/90 hover:bg-ink-700 transition-colors duration-base">
                                    <svg class="w-4 h-4 text-mist" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    Profil
                                </a>
                                <a href="{{ route('admin.settings') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-paper/90 hover:bg-ink-700 transition-colors duration-base">
                                    {!! $navIcon('settings') !!}
                                    Sozlamalar
                                </a>
                            </div>
                            <div class="py-1 border-t border-ink-border">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-rose-300 hover:bg-rose-500/10 transition-colors duration-base">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                                        Chiqish
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- ── Universal Toast Notification Container ── -->
            <x-toast-container />

            <!-- PAGE CONTENT -->
            <main class="admin-main flex-1 min-w-0 overflow-x-hidden overflow-y-auto bg-ink-950">
                <div class="w-full min-w-0 px-3 py-4 sm:p-4 lg:p-5 animate-fade-in">
                    @yield('content')
                </div>
            </main>

            <!-- FOOTER -->
            <footer class="flex-shrink-0 border-t border-ink-border bg-ink-950 px-4 lg:px-5 h-9 flex items-center justify-between font-mono text-[10px] uppercase tracking-wider text-mist">
                <p>Kitobxon Admin <span class="text-amber-400">v1.0</span></p>
                <p class="hidden sm:block">{{ now()->format('d.m.Y') }}</p>
            </footer>
        </div>
    </div>

    <script>
        function adminLayout() {
            return {
                sidebarOpen: window.innerWidth >= 1024,
                isMobile: window.innerWidth < 1024,
                init() {
                    window.addEventListener('resize', () => {
                        this.isMobile = window.innerWidth < 1024;
                        if (!this.isMobile) {
                            this.sidebarOpen = true;
                        }
                    });
                }
            }
        }
    </script>

    @stack('scripts')
</body>
</html>
