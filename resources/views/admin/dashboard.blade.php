@extends('admin.layouts.app')

@section('title', 'Bosh panel')
@section('breadcrumb', 'Bosh panel')

@section('content')

{{-- Editorial Welcome Banner --}}
<div class="relative overflow-hidden rounded-panel bg-ink-900 border border-ink-border p-5 sm:p-6 mb-5">
    <div class="relative flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="ks-badge bg-amber-500/10 text-amber-400 border border-amber-500/25 font-mono">
                    ADMINISTRATOR
                </span>
                <span class="text-xs font-mono text-mist">
                    {{ now()->locale('uz')->isoFormat('dddd, D MMMM YYYY') }} • {{ now()->format('H:i') }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold font-serif text-paper">
                Xush kelibsiz, <span class="text-amber-400">{{ auth()->user()->name ?? 'Admin' }}</span>!
            </h1>
            <p class="text-mist text-xs sm:text-sm mt-1 font-sans">
                Kitobxon boshqaruv konsoli — platforma ko'rsatkichlari, foydalanuvchilar va materiallar nazorati.
            </p>
        </div>
        <div class="hidden md:flex items-center justify-center w-14 h-14 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-400">
            <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        </div>
    </div>

    <!-- Mini stats row in banner -->
    <div class="relative flex flex-wrap items-center gap-4 mt-4 pt-4 border-t border-ink-border text-xs font-mono">
        <div class="flex items-center gap-2 text-emerald-400">
            <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
            <span>Tizim barqaror</span>
        </div>
        <div class="flex items-center gap-2 text-mist">
            <span class="text-amber-400">✦</span>
            <span>PHP {{ PHP_VERSION }}</span>
        </div>
        <div class="flex items-center gap-2 text-mist">
            <span class="text-amber-400">✦</span>
            <span>Laravel {{ app()->version() }}</span>
        </div>
    </div>
</div>

{{-- KPI Cards Row (DENSITY: 7, p-4) --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5 mb-5">

    {{-- Total Users --}}
    <div class="ks-panel p-4 bg-ink-900 border border-ink-border"
         x-data="counterCard({{ $stats['total_users'] ?? 0 }})">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] text-mist font-mono uppercase tracking-wider mb-1">Jami foydalanuvchilar</p>
                <p class="text-2xl font-bold font-mono text-paper ks-stat" x-text="displayValue">{{ number_format($stats['total_users'] ?? 0) }}</p>
                @php $uGrowth = (int) ($stats['user_growth_pct'] ?? 0); @endphp
                <div class="flex items-center gap-1.5 mt-1 font-mono text-xs {{ $uGrowth > 0 ? 'text-emerald-400' : ($uGrowth < 0 ? 'text-rose-400' : 'text-mist') }}">
                    <span>{{ $uGrowth > 0 ? '↑ +' : ($uGrowth < 0 ? '↓ ' : '') }}{{ $uGrowth }}%</span>
                    <span class="text-mist text-[10px]">oylik dinamika</span>
                </div>
            </div>
            <div class="w-9 h-9 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-amber-400 flex-shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <div class="mt-3 h-1 rounded-full bg-ink-950 overflow-hidden border border-ink-border/50">
            <div class="h-full bg-amber-500 rounded-full" style="width: 100%"></div>
        </div>
    </div>

    {{-- Total Students --}}
    <div class="ks-panel p-4 bg-ink-900 border border-ink-border"
         x-data="counterCard({{ $stats['total_students'] ?? 0 }})">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] text-mist font-mono uppercase tracking-wider mb-1">O'quvchilar</p>
                <p class="text-2xl font-bold font-mono text-paper ks-stat" x-text="displayValue">{{ number_format($stats['total_students'] ?? 0) }}</p>
                <div class="flex items-center gap-1.5 mt-1 font-mono text-xs text-emerald-400">
                    <span>{{ $stats['student_pct'] ?? 0 }}%</span>
                    <span class="text-mist text-[10px]">jami a'zolardan</span>
                </div>
            </div>
            <div class="w-9 h-9 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-emerald-400 flex-shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            </div>
        </div>
        <div class="mt-3 h-1 rounded-full bg-ink-950 overflow-hidden border border-ink-border/50">
            <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: {{ max($stats['student_pct'] ?? 0, 5) }}%"></div>
        </div>
    </div>

    {{-- Total Teachers --}}
    <div class="ks-panel p-4 bg-ink-900 border border-ink-border"
         x-data="counterCard({{ $stats['total_teachers'] ?? 0 }})">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] text-mist font-mono uppercase tracking-wider mb-1">O'qituvchilar</p>
                <p class="text-2xl font-bold font-mono text-paper ks-stat" x-text="displayValue">{{ number_format($stats['total_teachers'] ?? 0) }}</p>
                <div class="flex items-center gap-1.5 mt-1 font-mono text-xs text-amber-400">
                    <span>{{ $stats['teacher_pct'] ?? 0 }}%</span>
                    <span class="text-mist text-[10px]">jami a'zolardan</span>
                </div>
            </div>
            <div class="w-9 h-9 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-amber-400 flex-shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            </div>
        </div>
        <div class="mt-3 h-1 rounded-full bg-ink-950 overflow-hidden border border-ink-border/50">
            <div class="h-full bg-amber-400 rounded-full transition-all duration-500" style="width: {{ max($stats['teacher_pct'] ?? 0, 5) }}%"></div>
        </div>
    </div>

    {{-- Total Books --}}
    <div class="ks-panel p-4 bg-ink-900 border border-ink-border"
         x-data="counterCard({{ $stats['total_books'] ?? 0 }})">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] text-mist font-mono uppercase tracking-wider mb-1">Jami kitoblar</p>
                <p class="text-2xl font-bold font-mono text-paper ks-stat" x-text="displayValue">{{ number_format($stats['total_books'] ?? 0) }}</p>
                <div class="flex items-center gap-1.5 mt-1 font-mono text-xs text-amber-500">
                    <span>{{ $stats['active_books'] ?? 0 }} faol</span>
                    <span class="text-mist text-[10px]">({{ $stats['active_books_pct'] ?? 0 }}%)</span>
                </div>
            </div>
            <div class="w-9 h-9 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-amber-500 flex-shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
        </div>
        <div class="mt-3 h-1 rounded-full bg-ink-950 overflow-hidden border border-ink-border/50">
            <div class="h-full bg-amber-500 rounded-full transition-all duration-500" style="width: {{ max($stats['active_books_pct'] ?? 0, 5) }}%"></div>
        </div>
    </div>
</div>

{{-- KPI Cards Row 2: Jamiyat & Tanga Customizatsiya ko'rsatkichlari --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5 mb-5">

    {{-- Total Groups --}}
    <div class="ks-panel p-4 bg-ink-900 border border-ink-border"
         x-data="counterCard({{ $stats['total_groups'] ?? 0 }})">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] text-mist font-mono uppercase tracking-wider mb-1">Kitobxon Guruhlari</p>
                <p class="text-2xl font-bold font-mono text-paper ks-stat" x-text="displayValue">{{ number_format($stats['total_groups'] ?? 0) }}</p>
                <div class="flex items-center gap-1.5 mt-1 font-mono text-xs text-emerald-400">
                    <span>{{ $stats['users_with_groups_count'] ?? 0 }} a'zo</span>
                    <span class="text-mist text-[10px]">guruh ochgan</span>
                </div>
            </div>
            <div class="w-9 h-9 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-emerald-400 flex-shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <div class="mt-3 h-1 rounded-full bg-ink-950 overflow-hidden border border-ink-border/50">
            <div class="h-full bg-emerald-500 rounded-full" style="width: 100%"></div>
        </div>
    </div>

    {{-- Users with Profile Theme --}}
    <div class="ks-panel p-4 bg-ink-900 border border-ink-border"
         x-data="counterCard({{ $stats['users_with_themes_count'] ?? 0 }})">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] text-mist font-mono uppercase tracking-wider mb-1">Tokenga Fon Olganlar</p>
                <p class="text-2xl font-bold font-mono text-paper ks-stat" x-text="displayValue">{{ number_format($stats['users_with_themes_count'] ?? 0) }}</p>
                <div class="flex items-center gap-1.5 mt-1 font-mono text-xs text-amber-400">
                    <span>{{ $stats['active_themes_count'] ?? 0 }} ta</span>
                    <span class="text-mist text-[10px]">profilda faol fon</span>
                </div>
            </div>
            <div class="w-9 h-9 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-amber-400 flex-shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/></svg>
            </div>
        </div>
        <div class="mt-3 h-1 rounded-full bg-ink-950 overflow-hidden border border-ink-border/50">
            <div class="h-full bg-amber-400 rounded-full" style="width: 100%"></div>
        </div>
    </div>

    {{-- Coins spent on themes --}}
    <div class="ks-panel p-4 bg-ink-900 border border-ink-border"
         x-data="counterCard({{ $stats['coins_spent_on_themes'] ?? 0 }})">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] text-mist font-mono uppercase tracking-wider mb-1">Fon Xaridlari (Tanga)</p>
                <p class="text-2xl font-bold font-mono text-paper ks-stat" x-text="displayValue">{{ number_format($stats['coins_spent_on_themes'] ?? 0) }}</p>
                <div class="flex items-center gap-1.5 mt-1 font-mono text-xs text-amber-500">
                    <span>{{ $stats['theme_purchases_count'] ?? 0 }} ta xarid</span>
                    <span class="text-mist text-[10px]">qayd etilgan</span>
                </div>
            </div>
            <div class="w-9 h-9 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-amber-500 flex-shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
        </div>
        <div class="mt-3 h-1 rounded-full bg-ink-950 overflow-hidden border border-ink-border/50">
            <div class="h-full bg-amber-500 rounded-full" style="width: 100%"></div>
        </div>
    </div>

    {{-- Total Circulating Coins --}}
    <div class="ks-panel p-4 bg-ink-900 border border-ink-border"
         x-data="counterCard({{ $stats['total_coins'] ?? 0 }})">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] text-mist font-mono uppercase tracking-wider mb-1">A'zolarda Tangalar</p>
                <p class="text-2xl font-bold font-mono text-paper ks-stat" x-text="displayValue">{{ number_format($stats['total_coins'] ?? 0) }}</p>
                <div class="flex items-center gap-1.5 mt-1 font-mono text-xs text-amber-400">
                    <span>🪙 {{ number_format($stats['total_points'] ?? 0) }}</span>
                    <span class="text-mist text-[10px]">ball to'plangan</span>
                </div>
            </div>
            <div class="w-9 h-9 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-amber-400 flex-shrink-0 text-base">
                🪙
            </div>
        </div>
        <div class="mt-3 h-1 rounded-full bg-ink-950 overflow-hidden border border-ink-border/50">
            <div class="h-full bg-amber-400 rounded-full" style="width: 100%"></div>
        </div>
    </div>
</div>

{{-- Main Content: Recent Users + Quick Actions --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-5">

    {{-- Recent Users Table (2/3 width) --}}
    <div class="xl:col-span-2 bg-ink-900 border border-ink-border rounded-panel overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-ink-border">
            <div>
                <h2 class="text-sm font-bold font-serif text-paper">So'nggi foydalanuvchilar faolligi</h2>
                <p class="text-xs text-mist font-mono">Guruh ochgani, tokenga olgan foni va tangalar balansi</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-xs text-amber-400 hover:text-amber-300 font-mono flex items-center gap-1 transition-colors">
                Barchasini ko'rish →
            </a>
        </div>
        <div class="admin-table-scroll overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-ink-border bg-ink-950/40 text-[11px] font-mono uppercase text-mist">
                        <th class="py-2.5 px-3.5 whitespace-nowrap">Foydalanuvchi</th>
                        <th class="py-2.5 px-3.5 whitespace-nowrap">Rol</th>
                        <th class="py-2.5 px-3.5 whitespace-nowrap text-center">Guruh ochganmi?</th>
                        <th class="py-2.5 px-3.5 whitespace-nowrap text-center">Tokenga fon olganmi?</th>
                        <th class="py-2.5 px-3.5 whitespace-nowrap text-right">Tangalar</th>
                        <th class="py-2.5 px-3.5 whitespace-nowrap">Qo'shilgan</th>
                        <th class="py-2.5 px-3.5 whitespace-nowrap text-center">Holat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border/50 text-xs">
                    @forelse($recentUsers ?? [] as $user)
                        @php
                            $role = $user->roles->first()?->name ?? 'student';
                            $roleColors = [
                                'admin' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
                                'teacher' => 'bg-ink-950 text-mist border-ink-border',
                                'student' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                            ];
                            $roleLabels = [
                                'admin' => 'Admin',
                                'teacher' => "O'qituvchi",
                                'student' => "O'quvchi",
                            ];

                            // Guruhlar tekshiruvi
                            $createdGroupsCount = $user->createdGroups->count();
                            $firstGroup = $user->createdGroups->first();

                            // Profil foni tekshiruvi
                            $unlockedItems = $user->profile?->unlocked_items ?? [];
                            $activeThemeKey = $user->profile?->profile_theme ?? $user->profile?->avatar_frame ?? $user->profile?->chat_bubble_theme;
                            $themeMeta = $activeThemeKey ? ($themeCatalog[$activeThemeKey] ?? null) : null;
                            $hasTheme = !empty($unlockedItems) || !empty($themeMeta);
                        @endphp
                        <tr class="hover:bg-ink-800/40 transition-colors">
                            {{-- User name & avatar --}}
                            <td class="py-2.5 px-3.5">
                                <div class="flex items-center gap-2.5">
                                    @if($user->avatar)
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-7 h-7 rounded-btn object-cover bg-ink-950 border border-ink-border flex-shrink-0">
                                    @else
                                        <div class="w-7 h-7 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-xs font-mono font-bold text-paper flex-shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-paper truncate">{{ $user->name }}</p>
                                        <p class="text-[10px] text-mist font-mono truncate">{{ $user->email ?: ($user->phone ? \App\Support\PhoneNumber::mask($user->phone) : ($user->telegram_username ? '@' . $user->telegram_username : 'Ko\'rsatilmagan')) }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Role --}}
                            <td class="py-2.5 px-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-badge text-[10px] font-mono font-semibold border {{ $roleColors[$role] ?? 'bg-ink-950 text-mist border-ink-border' }}">
                                    {{ $roleLabels[$role] ?? $role }}
                                </span>
                            </td>

                            {{-- Guruh ochganmi? --}}
                            <td class="py-2.5 px-3.5 text-center whitespace-nowrap">
                                @if($createdGroupsCount > 0)
                                    <div class="inline-flex flex-col items-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-badge text-[10px] font-mono font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>{{ $createdGroupsCount }} ta guruh</span>
                                        </span>
                                        @if($firstGroup)
                                            <span class="text-[9px] text-mist font-mono mt-0.5 truncate max-w-[130px]" title="{{ $firstGroup->name }}">
                                                "{{ Str::limit($firstGroup->name, 16) }}"
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-badge text-[10px] font-mono text-mist/60 bg-ink-950 border border-ink-border">
                                        Ochmagan
                                    </span>
                                @endif
                            </td>

                            {{-- Tokenga fon olganmi? --}}
                            <td class="py-2.5 px-3.5 text-center whitespace-nowrap">
                                @if($hasTheme)
                                    <div class="inline-flex flex-col items-center">
                                        @php
                                            $badgeText = $themeMeta['badge_text'] ?? ($activeThemeKey ? '✨ ' . ucfirst($activeThemeKey) : '🎨 Maxsus fon');
                                            $badgeClass = $themeMeta['badge_class'] ?? 'bg-amber-500/15 text-amber-300 border border-amber-500/30';
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-badge text-[10px] font-mono font-semibold {{ $badgeClass }}">
                                            <span>{{ $themeMeta['icon'] ?? '🎨' }}</span>
                                            <span class="truncate max-w-[110px]">{{ $badgeText }}</span>
                                        </span>
                                        @if(count($unlockedItems) > 0)
                                            <span class="text-[9px] text-amber-400 font-mono mt-0.5">
                                                {{ count($unlockedItems) }} ta fon xaridi
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-badge text-[10px] font-mono text-mist/60 bg-ink-950 border border-ink-border">
                                        Standart fon
                                    </span>
                                @endif
                            </td>

                            {{-- Coins balance --}}
                            <td class="py-2.5 px-3.5 text-right whitespace-nowrap font-mono">
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-400">
                                    <span>{{ number_format($user->coin_balance) }}</span>
                                    <span class="text-[11px]">🪙</span>
                                </span>
                            </td>

                            {{-- Joined date --}}
                            <td class="py-2.5 px-3.5 font-mono text-[11px] text-mist whitespace-nowrap">
                                {{ $user->created_at->format('d.m.Y') }}
                            </td>

                            {{-- Status --}}
                            <td class="py-2.5 px-3.5 text-center whitespace-nowrap">
                                @if($user->isBanned())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-badge text-[10px] font-mono font-bold bg-rose-500/15 text-rose-300 border border-rose-500/30">
                                        Bloklangan
                                    </span>
                                @else
                                    <div class="inline-flex items-center gap-1 font-mono text-[11px] text-emerald-400">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
                                        <span>Faol</span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-mist font-mono text-xs">
                                Foydalanuvchilar mavjud emas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Quick Actions + System Status (1/3 width) --}}
    <div class="flex flex-col gap-4">

        {{-- Quick Actions --}}
        <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
            <h2 class="text-xs font-mono uppercase tracking-wider text-mist mb-3">Tezkor amallar</h2>
            <div class="space-y-2">
                <a href="{{ route('admin.books.index') }}"
                   class="flex items-center gap-3 p-2.5 rounded-btn bg-ink-950 border border-ink-border hover:border-amber-400/40 text-paper transition-colors group">
                    <span class="w-7 h-7 rounded-badge bg-amber-500/10 text-amber-400 flex items-center justify-center text-xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-paper">Kitob boshqaruvi</p>
                        <p class="text-[10px] text-mist font-mono">Kitob qo'shish va tahrirlash</p>
                    </div>
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 p-2.5 rounded-btn bg-ink-950 border border-ink-border hover:border-amber-400/40 text-paper transition-colors group">
                    <span class="w-7 h-7 rounded-badge bg-amber-500/10 text-amber-400 flex items-center justify-center text-xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-paper">Foydalanuvchilar</p>
                        <p class="text-[10px] text-mist font-mono">Rollarni belgilash va nazorat</p>
                    </div>
                </a>

                <a href="{{ route('admin.stats') }}"
                   class="flex items-center gap-3 p-2.5 rounded-btn bg-ink-950 border border-ink-border hover:border-amber-400/40 text-paper transition-colors group">
                    <span class="w-7 h-7 rounded-badge bg-amber-500/10 text-amber-400 flex items-center justify-center text-xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-paper">Batafsil statistika</p>
                        <p class="text-[10px] text-mist font-mono">Platforma tahlili</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- System Status --}}
        <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
            <h2 class="text-xs font-mono uppercase tracking-wider text-mist mb-3">Tizim holati</h2>
            <div class="space-y-2 text-xs font-mono">
                <div class="flex items-center justify-between py-1 border-b border-ink-border/50">
                    <span class="text-mist">PHP versiya</span>
                    <span class="text-paper font-semibold">{{ PHP_VERSION }}</span>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-ink-border/50">
                    <span class="text-mist">Laravel</span>
                    <span class="text-paper font-semibold">{{ app()->version() }}</span>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-ink-border/50">
                    <span class="text-mist">Ma'lumotlar bazasi</span>
                    <span class="text-emerald-400 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Ulangan
                    </span>
                </div>
                <div class="flex items-center justify-between py-1">
                    <span class="text-mist">Kesh</span>
                    <span class="text-emerald-400 font-semibold">Faol</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Real Chart.js Charts --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-5">
    <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
        <div class="flex items-center justify-between mb-3 border-b border-ink-border pb-3">
            <div>
                <h2 class="text-sm font-bold font-serif text-paper">Foydalanuvchilar o'sishi</h2>
                <p class="text-[11px] text-mist font-mono">So'nggi 30 kunlik dinamika</p>
            </div>
            @php $growthPct = (int) ($stats['user_growth_pct'] ?? 0); @endphp
            <span class="text-xs font-mono px-2 py-0.5 rounded-badge border {{ $growthPct > 0 ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : ($growthPct < 0 ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : 'bg-ink-800 text-mist border-ink-border') }}">
                {{ $growthPct > 0 ? '↑ +' : ($growthPct < 0 ? '↓ ' : '') }}{{ $growthPct }}% oyiga
            </span>
        </div>
        <div class="h-60 relative"><canvas id="chart-signups"></canvas></div>
    </div>

    <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
        <div class="flex items-center justify-between mb-3 border-b border-ink-border pb-3">
            <div>
                <h2 class="text-sm font-bold font-serif text-paper">O'qilgan daqiqalar</h2>
                <p class="text-[11px] text-mist font-mono">Kunlik faol mutolaa (so'nggi 14 kun)</p>
            </div>
            <span class="text-xs font-mono text-amber-400 font-semibold">
                Jami: {{ number_format($stats['total_reading_minutes'] ?? 0) }} daq ({{ $stats['total_reading_hours'] ?? 0 }} soat)
            </span>
        </div>
        <div class="h-60 relative"><canvas id="chart-minutes"></canvas></div>
    </div>
</div>

{{-- Format distribution + Summary numbers --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-5">
    <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
        <div class="mb-3 border-b border-ink-border pb-2">
            <h2 class="text-sm font-bold font-serif text-paper">Kontent taqsimoti</h2>
            <p class="text-[11px] text-mist font-mono">Formatlar bo'yicha materiallar</p>
        </div>
        <div class="h-52 relative"><canvas id="chart-formats"></canvas></div>
    </div>

    <div class="xl:col-span-2 grid grid-cols-2 gap-3.5">
        <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
            <p class="text-[11px] text-mist font-mono uppercase mb-1">Bugun faollar</p>
            <p class="text-2xl font-bold font-mono text-paper">{{ number_format($stats['online_today'] ?? 1) }}</p>
            <p class="text-[10px] text-mist mt-1 font-mono">Kunlik tashrif va mutolaa</p>
        </div>
        <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
            <p class="text-[11px] text-mist font-mono uppercase mb-1">Jami ballar</p>
            <p class="text-2xl font-bold font-mono text-amber-400">{{ number_format($stats['total_points'] ?? 0) }}</p>
            <p class="text-[10px] text-mist mt-1 font-mono">Barcha a'zolar to'plagan</p>
        </div>
        <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
            <p class="text-[11px] text-mist font-mono uppercase mb-1">Guruhlar</p>
            <p class="text-2xl font-bold font-mono text-emerald-400">{{ number_format($stats['total_groups'] ?? 0) }}</p>
            <p class="text-[10px] text-mist mt-1 font-mono">Kitobxon klublari</p>
        </div>
        <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
            <p class="text-[11px] text-mist font-mono uppercase mb-1">Test topshiriqlari</p>
            <p class="text-2xl font-bold font-mono text-amber-500">{{ number_format($stats['total_quizzes'] ?? 0) }}</p>
            <p class="text-[10px] text-mist mt-1 font-mono">Bilimni sinash viktorinalari</p>
        </div>
    </div>
</div>

{{-- Deep Dive: Guruhlar va Profil Fonlari Xaridlari Monitoringi --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-5">

    {{-- 1. So'nggi yaratilgan guruhlar --}}
    <div class="bg-ink-900 border border-ink-border rounded-panel overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-ink-border bg-ink-950/40">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-btn bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-xs">
                    👥
                </span>
                <div>
                    <h3 class="text-sm font-bold font-serif text-paper">Yangi ochilgan guruhlar</h3>
                    <p class="text-[11px] text-mist font-mono">Kitobxon klublari va ularning yaratuvchilari</p>
                </div>
            </div>
            <a href="{{ route('groups.index') }}" target="_blank"
               class="text-xs text-emerald-400 hover:text-emerald-300 font-mono flex items-center gap-1 transition-colors">
                Saytda ko'rish →
            </a>
        </div>

        <div class="admin-table-scroll overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-ink-border bg-ink-950/60 text-[10px] font-mono uppercase text-mist">
                        <th class="py-2 px-3.5 whitespace-nowrap">Guruh nomi</th>
                        <th class="py-2 px-3.5 whitespace-nowrap">Kim ochgan?</th>
                        <th class="py-2 px-3.5 whitespace-nowrap text-center">A'zolar</th>
                        <th class="py-2 px-3.5 whitespace-nowrap text-center">Imkoniyatlar</th>
                        <th class="py-2 px-3.5 whitespace-nowrap text-right">Ochilgan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border/50 text-xs">
                    @forelse($recentGroups ?? [] as $group)
                        <tr class="hover:bg-ink-800/40 transition-colors">
                            <td class="py-2.5 px-3.5">
                                <a href="{{ route('groups.show', $group->slug) }}" target="_blank" class="flex items-center gap-2 group">
                                    <div class="w-7 h-7 rounded-btn bg-ink-950 border border-ink-border overflow-hidden flex items-center justify-center text-xs shrink-0">
                                        @if($group->cover_image)
                                            <img src="{{ asset('storage/' . $group->cover_image) }}" alt="{{ $group->name }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-emerald-400 font-mono font-bold">{{ strtoupper(substr($group->name, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-paper group-hover:text-amber-400 transition-colors truncate max-w-[140px]">{{ $group->name }}</p>
                                        @if($group->book)
                                            <p class="text-[10px] text-mist font-mono truncate max-w-[140px]">📖 {{ $group->book->title }}</p>
                                        @endif
                                    </div>
                                </a>
                            </td>
                            <td class="py-2.5 px-3.5 whitespace-nowrap">
                                @if($group->creator)
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-5 h-5 rounded-full bg-ink-950 border border-ink-border flex items-center justify-center text-[10px] font-bold text-paper shrink-0">
                                            {{ strtoupper(substr($group->creator->name, 0, 1)) }}
                                        </div>
                                        <span class="text-xs text-paper truncate max-w-[100px]">{{ $group->creator->name }}</span>
                                    </div>
                                @else
                                    <span class="text-mist font-mono text-[11px]">Noma'lum</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3.5 text-center whitespace-nowrap font-mono text-xs text-paper">
                                <span class="px-2 py-0.5 rounded-full bg-ink-950 border border-ink-border text-emerald-400 font-bold">
                                    {{ $group->members_count ?? 0 }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3.5 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    @if($group->chat_enabled)
                                        <span class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[9px] font-mono" title="Chat faol">💬 Chat</span>
                                    @endif
                                    @if($group->voice_enabled)
                                        <span class="px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[9px] font-mono" title="Ovozli efir faol">🎙️ Ovoz</span>
                                    @endif
                                    @if($group->is_private)
                                        <span class="px-1.5 py-0.5 rounded bg-slate-500/10 text-slate-400 border border-slate-500/20 text-[9px] font-mono" title="Yopiq guruh">🔒 Yopiq</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-2.5 px-3.5 text-right font-mono text-[11px] text-mist whitespace-nowrap">
                                {{ $group->created_at->format('d.m.Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-mist font-mono text-xs">
                                Hozircha ochilgan guruhlar mavjud emas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 2. Tokenga fon sotib olganlar / Faol vizual fonlar --}}
    <div class="bg-ink-900 border border-ink-border rounded-panel overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-ink-border bg-ink-950/40">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-btn bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center text-xs">
                    🎨
                </span>
                <div>
                    <h3 class="text-sm font-bold font-serif text-paper">Tokenga olingan profil fonlari</h3>
                    <p class="text-[11px] text-mist font-mono">Foydalanuvchilarning xaridlari va faol temalari</p>
                </div>
            </div>
            <span class="text-xs text-amber-400 font-mono">
                {{ $stats['users_with_themes_count'] ?? 0 }} a'zo olgan
            </span>
        </div>

        <div class="admin-table-scroll overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-ink-border bg-ink-950/60 text-[10px] font-mono uppercase text-mist">
                        <th class="py-2 px-3.5 whitespace-nowrap">Foydalanuvchi</th>
                        <th class="py-2 px-3.5 whitespace-nowrap">Olingan fon</th>
                        <th class="py-2 px-3.5 whitespace-nowrap text-right">Narxi</th>
                        <th class="py-2 px-3.5 whitespace-nowrap text-right">Vaqt</th>
                        <th class="py-2 px-3.5 whitespace-nowrap text-center">Profil</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border/50 text-xs">
                    @php
                        $hasTx = isset($recentThemePurchases) && $recentThemePurchases->isNotEmpty();
                    @endphp

                    @if($hasTx)
                        @foreach($recentThemePurchases as $tx)
                            @php
                                $u = $tx->user;
                                $activeKey = $u?->profile?->profile_theme ?? $u?->profile?->avatar_frame;
                                $tMeta = $activeKey ? ($themeCatalog[$activeKey] ?? null) : null;
                            @endphp
                            <tr class="hover:bg-ink-800/40 transition-colors">
                                <td class="py-2.5 px-3.5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-[10px] font-bold text-paper shrink-0">
                                            {{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-paper truncate max-w-[120px]">{{ $u->name ?? 'Foydalanuvchi' }}</p>
                                            <p class="text-[9px] text-mist font-mono truncate max-w-[120px]">{{ $u->email ?: ($u->phone ? \App\Support\PhoneNumber::mask($u->phone) : ($u->telegram_username ? '@' . $u->telegram_username : 'Ko\'rsatilmagan')) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2.5 px-3.5 whitespace-nowrap">
                                    @if($tMeta)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-badge text-[10px] font-mono font-semibold {{ $tMeta['badge_class'] ?? 'bg-amber-500/15 text-amber-300 border border-amber-500/30' }}">
                                            <span>{{ $tMeta['icon'] ?? '🎨' }}</span>
                                            <span>{{ $tMeta['badge_text'] ?? $tMeta['name'] }}</span>
                                        </span>
                                    @else
                                        <span class="text-xs text-amber-300 font-mono">
                                            {{ Str::limit(str_replace(' profil vizual foni va chat uslubi xaridi', '', $tx->description), 20) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3.5 text-right whitespace-nowrap font-mono text-xs font-bold text-amber-400">
                                    {{ number_format(abs($tx->coins)) }} 🪙
                                </td>
                                <td class="py-2.5 px-3.5 text-right font-mono text-[11px] text-mist whitespace-nowrap">
                                    {{ $tx->created_at->diffForHumans() }}
                                </td>
                                <td class="py-2.5 px-3.5 text-center whitespace-nowrap">
                                    @if($u)
                                        <a href="{{ route('profile.show', $u->username ?? $u->id) }}" target="_blank"
                                           class="p-1 rounded bg-ink-950 hover:bg-ink-800 text-mist hover:text-amber-400 border border-ink-border inline-flex items-center justify-center"
                                           title="Profilni ko'rish">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @elseif(isset($usersWithThemes) && $usersWithThemes->isNotEmpty())
                        @foreach($usersWithThemes as $prof)
                            @php
                                $u = $prof->user;
                                $unlocked = $prof->unlocked_items ?? [];
                                $activeKey = $prof->profile_theme ?? $prof->avatar_frame ?? ($unlocked[0] ?? null);
                                $tMeta = $activeKey ? ($themeCatalog[$activeKey] ?? null) : null;
                            @endphp
                            <tr class="hover:bg-ink-800/40 transition-colors">
                                <td class="py-2.5 px-3.5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-[10px] font-bold text-paper shrink-0">
                                            {{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-paper truncate max-w-[120px]">{{ $u->name ?? 'Foydalanuvchi' }}</p>
                                            <p class="text-[9px] text-mist font-mono truncate max-w-[120px]">{{ $u->email ?: ($u->phone ? \App\Support\PhoneNumber::mask($u->phone) : ($u->telegram_username ? '@' . $u->telegram_username : 'Ko\'rsatilmagan')) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2.5 px-3.5 whitespace-nowrap">
                                    @if($tMeta)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-badge text-[10px] font-mono font-semibold {{ $tMeta['badge_class'] ?? 'bg-amber-500/15 text-amber-300 border border-amber-500/30' }}">
                                            <span>{{ $tMeta['icon'] ?? '🎨' }}</span>
                                            <span>{{ $tMeta['badge_text'] ?? $tMeta['name'] }}</span>
                                        </span>
                                    @else
                                        <span class="text-xs text-amber-300 font-mono">
                                            {{ count($unlocked) }} ta fon xaridi
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3.5 text-right whitespace-nowrap font-mono text-xs font-bold text-amber-400">
                                    {{ $tMeta['price'] ?? 100 }} 🪙
                                </td>
                                <td class="py-2.5 px-3.5 text-right font-mono text-[11px] text-mist whitespace-nowrap">
                                    {{ $prof->updated_at->diffForHumans() }}
                                </td>
                                <td class="py-2.5 px-3.5 text-center whitespace-nowrap">
                                    @if($u)
                                        <a href="{{ route('profile.show', $u->username ?? $u->id) }}" target="_blank"
                                           class="p-1 rounded bg-ink-950 hover:bg-ink-800 text-mist hover:text-amber-400 border border-ink-border inline-flex items-center justify-center"
                                           title="Profilni ko'rish">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="py-6 text-center text-mist font-mono text-xs">
                                Hozircha tokenga fon sotib olgan foydalanuvchilar mavjud emas
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
<script>
if (typeof Chart === 'undefined') {
    document.write('<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"><\/script>');
}
</script>
<script>
// Global counterCard definition immediately available to Alpine
function counterCard(target) {
    const num = Number(target) || 0;
    return {
        displayValue: num.toLocaleString('uz-UZ'),
        init() {
            if (num > 0) {
                this.animateCounter(num);
            }
        },
        animateCounter(targetVal) {
            const duration = 800;
            const start = performance.now();
            const step = (currentTime) => {
                const elapsed = currentTime - start;
                const progress = Math.min(elapsed / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = Math.round(eased * targetVal);
                this.displayValue = current.toLocaleString('uz-UZ');
                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            };
            requestAnimationFrame(step);
        }
    };
}
window.counterCard = counterCard;
if (window.Alpine) {
    window.Alpine.data('counterCard', counterCard);
} else {
    document.addEventListener('alpine:init', () => {
        if (window.Alpine) window.Alpine.data('counterCard', counterCard);
    });
}

// Chart.js initialization
const signupLabels = @json($signupLabels ?? []);
const signupData   = @json($signupData ?? []);
const minutesLabels = @json($minutesLabels ?? []);
const minutesData   = @json($minutesData ?? []);
const formatData    = {!! json_encode($formatData ?? [0, 0, 0, 0]) !!};

function initDashboardCharts(attempts = 0) {
    if (typeof Chart === 'undefined') {
        if (attempts < 25) {
            setTimeout(() => initDashboardCharts(attempts + 1), 100);
        }
        return;
    }

    Chart.defaults.color = '#8B9BAD';
    Chart.defaults.borderColor = 'rgba(31, 41, 61, 0.6)';
    Chart.defaults.font.family = "'DM Sans', sans-serif";

    // 1. Line Chart: Signups
    const signupsCtx = document.getElementById('chart-signups');
    if (signupsCtx && !signupsCtx._chartInstance) {
        const grad = signupsCtx.getContext('2d').createLinearGradient(0, 0, 0, 240);
        grad.addColorStop(0, 'rgba(245, 158, 11, 0.25)');
        grad.addColorStop(1, 'rgba(245, 158, 11, 0)');
        signupsCtx._chartInstance = new Chart(signupsCtx, {
            type: 'line',
            data: {
                labels: signupLabels,
                datasets: [{
                    label: 'Jami foydalanuvchilar',
                    data: signupData,
                    borderColor: '#F59E0B',
                    backgroundColor: grad,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2,
                    pointHoverRadius: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: false, ticks: { precision: 0 } }
                }
            }
        });
    }

    // 2. Bar Chart: Minutes
    const minutesCtx = document.getElementById('chart-minutes');
    if (minutesCtx && !minutesCtx._chartInstance) {
        minutesCtx._chartInstance = new Chart(minutesCtx, {
            type: 'bar',
            data: {
                labels: minutesLabels,
                datasets: [{
                    label: 'Daqiqalar',
                    data: minutesData,
                    backgroundColor: 'rgba(245, 158, 11, 0.75)',
                    hoverBackgroundColor: '#F59E0B',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }

    // 3. Doughnut Chart: Content Formats
    const formatsCtx = document.getElementById('chart-formats');
    if (formatsCtx && !formatsCtx._chartInstance) {
        const formatsTotal = formatData.reduce((a, b) => a + b, 0);
        formatsCtx._chartInstance = new Chart(formatsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Boblar (Matn)', 'Audio darslar', 'Video darslar', 'Testlar'],
                datasets: [{
                    data: formatsTotal > 0 ? formatData : [1, 0, 0, 0],
                    backgroundColor: formatsTotal > 0
                        ? ['#F59E0B', '#10B981', '#C1392B', '#8B5CF6']
                        : ['#1F293D', '#1F293D', '#1F293D', '#1F293D'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, padding: 10, font: { size: 11 }, color: '#8B9BAD' }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (formatsTotal === 0) return ' Hozircha material yuklanmagan';
                                return ` ${context.label}: ${context.raw} ta`;
                            }
                        }
                    }
                },
                cutout: '70%',
            }
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initDashboardCharts());
} else {
    initDashboardCharts();
}
</script>
@endpush
