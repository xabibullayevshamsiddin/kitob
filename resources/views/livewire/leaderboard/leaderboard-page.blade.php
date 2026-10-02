<div class="max-w-5xl mx-auto space-y-8 pb-16">
    <!-- Header Section -->
    <div class="text-center max-w-xl mx-auto space-y-2">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-xs font-bold text-amber-400 uppercase tracking-widest">
            <span>🏆</span> {{ __('site.leaderboard.badge') }}
        </span>
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('site.leaderboard.title') }}</h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">{{ __('site.leaderboard.subtitle') }}</p>
    </div>

    <!-- Current User Stats Card -->
    @auth
        <div class="p-5 sm:p-6 rounded-3xl bg-gradient-to-r from-indigo-900/40 via-ink-900/60 to-purple-900/40 border border-indigo-500/30 shadow-lg backdrop-blur-xl">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <img src="{{ auth()->user()->avatar_url }}" class="w-14 h-14 rounded-2xl object-cover ring-2 ring-indigo-500/50 shadow-md" alt="{{ auth()->user()->name }}">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-indigo-400">{{ __('site.leaderboard.your_result') }}</span>
                            @if($myRank)
                                <span class="px-2 py-0.5 rounded-full bg-indigo-500/20 border border-indigo-500/40 text-[11px] font-black text-indigo-300">#{{ $myRank }} {{ __('site.leaderboard.place') }}</span>
                            @endif
                        </div>
                        <h3 class="text-base font-bold text-white">{{ auth()->user()->name }}</h3>
                        <p class="text-xs text-slate-400">{{ '@' . auth()->user()->username }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 sm:gap-6 text-center sm:text-right">
                    <div>
                        <span class="text-[11px] text-slate-400 block">{{ __('site.leaderboard.total_points') }}</span>
                        <span class="text-lg font-black text-amber-400">⭐️ {{ number_format($myUser->display_points ?? auth()->user()->total_points) }}</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-400 block">{{ __('site.leaderboard.reading') }}</span>
                        <span class="text-lg font-black text-slate-200">📖 {{ number_format($myUser->display_minutes ?? 0) }} {{ __('site.leaderboard.min_short') }}</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-400 block">{{ __('site.leaderboard.streak') }}</span>
                        <span class="text-lg font-black text-amber-500">🔥 {{ $myUser->display_streak ?? auth()->user()->current_streak }} {{ __('site.leaderboard.days') }}</span>
                    </div>
                </div>
            </div>

            @if($myRank && $myRank > 1 && $pointsToNext)
                <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-xs text-slate-400">
                    <span>{{ __('site.leaderboard.to_next', ['rank' => $myRank - 1]) }}</span>
                    <span class="font-bold text-indigo-400">{{ __('site.leaderboard.points_needed', ['points' => number_format($pointsToNext)]) }}</span>
                </div>
            @elseif($myRank === 1)
                <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-xs text-amber-400 font-bold">
                    <span>👑 {{ __('site.leaderboard.congrats') }}</span>
                    <span>{{ __('site.leaderboard.first_place') }}</span>
                </div>
            @endif
        </div>
    @else
        <div class="p-5 rounded-2xl bg-slate-900/60 border border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🏆</span>
                <div>
                    <h4 class="text-sm font-bold text-white">{{ __('site.leaderboard.join_cta_title') }}</h4>
                    <p class="text-xs text-slate-400">{{ __('site.leaderboard.join_cta_sub') }}</p>
                </div>
            </div>
            <a href="{{ route('login') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow transition-all shrink-0">
                {{ __('site.leaderboard.login_start') }}
            </a>
        </div>
    @endauth

    <!-- Filter & Sort Bar -->
    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft">
        <!-- Period Tabs -->
        <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-800/80 rounded-2xl overflow-x-auto shrink-0">
            <button type="button" wire:click="setPeriod('all_time')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $period === 'all_time' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-400 hover:text-white' }}">
                {{ __('site.leaderboard.period_all') }}
            </button>
            <button type="button" wire:click="setPeriod('monthly')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $period === 'monthly' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-400 hover:text-white' }}">
                {{ __('site.leaderboard.period_monthly') }}
            </button>
            <button type="button" wire:click="setPeriod('weekly')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $period === 'weekly' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-400 hover:text-white' }}">
                {{ __('site.leaderboard.period_weekly') }}
            </button>
            <button type="button" wire:click="setPeriod('today')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $period === 'today' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-400 hover:text-white' }}">
                {{ __('site.leaderboard.period_today') }}
            </button>
        </div>

        <!-- Sort Criteria -->
        <div class="flex items-center gap-2 overflow-x-auto">
            <span class="text-xs text-slate-400 hidden lg:inline">{{ __('site.leaderboard.sort_by') }}</span>
            <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-800/80 rounded-2xl shrink-0">
                <button type="button" wire:click="setSortBy('points')"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $sortBy === 'points' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">
                    ⭐️ {{ __('site.leaderboard.sort_points') }}
                </button>
                <button type="button" wire:click="setSortBy('reading_time')"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $sortBy === 'reading_time' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">
                    📖 {{ __('site.leaderboard.sort_reading') }}
                </button>
                <button type="button" wire:click="setSortBy('streak')"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $sortBy === 'streak' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">
                    🔥 {{ __('site.leaderboard.sort_streak') }}
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="relative min-w-[200px]">
            <input type="text" wire:model.debounce.300ms="search" placeholder="{{ __('site.leaderboard.search_placeholder') }}"
                class="w-full pl-9 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
    </div>

    <!-- Top 3 Podium (Real Users) -->
    @if($top3->count() > 0)
        <div class="grid grid-cols-3 gap-3 sm:gap-6 pt-8 items-end max-w-2xl mx-auto">
            <!-- 2nd Place Silver -->
            @if(isset($top3[1]))
                <div class="p-4 sm:p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-soft text-center space-y-2 relative group hover:border-slate-400 transition-colors">
                    <span class="text-2xl sm:text-3xl block">🥈</span>
                    <a href="{{ route('profile.show', $top3[1]->username) }}" class="inline-block">
                        <img src="{{ $top3[1]->avatar_url }}" class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl mx-auto object-cover ring-2 ring-slate-300 dark:ring-slate-600 shadow-md group-hover:scale-105 transition-transform" alt="{{ $top3[1]->name }}">
                    </a>
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold truncate text-slate-900 dark:text-white">{{ $top3[1]->name }}</h3>
                        <p class="text-[10px] text-slate-400 truncate">{{ '@' . $top3[1]->username }}</p>
                    </div>
                    <div class="pt-1">
                        @if($sortBy === 'reading_time')
                            <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-xs font-black text-slate-700 dark:text-slate-300 block">
                                📖 {{ number_format($top3[1]->display_minutes) }} {{ __('site.leaderboard.min_short') }}
                            </span>
                        @elseif($sortBy === 'streak')
                            <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-xs font-black text-amber-500 block">
                                🔥 {{ $top3[1]->display_streak }} {{ __('site.leaderboard.days') }}
                            </span>
                        @else
                            <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-xs font-black text-slate-700 dark:text-slate-300 block">
                                ⭐️ {{ number_format($top3[1]->display_points) }} {{ __('site.leaderboard.points_short') }}
                            </span>
                        @endif
                    </div>
                </div>
            @else
                <div class="p-4 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800 text-center opacity-40">
                    <span class="text-2xl block">🥈</span>
                    <p class="text-xs text-slate-400 mt-2">{{ __('site.leaderboard.empty_podium') }}</p>
                </div>
            @endif

            <!-- 1st Place Gold (Elevated) -->
            @if(isset($top3[0]))
                <div class="p-5 sm:p-6 rounded-3xl bg-gradient-to-b from-amber-500/20 via-white dark:via-slate-900 to-white dark:to-slate-900 border-2 border-amber-500 shadow-xl shadow-amber-500/10 text-center space-y-3 transform -translate-y-4 relative group hover:scale-[1.02] transition-transform">
                    <span class="text-3xl sm:text-4xl block animate-bounce-sm">👑 🥇</span>
                    <a href="{{ route('profile.show', $top3[0]->username) }}" class="inline-block relative">
                        <img src="{{ $top3[0]->avatar_url }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-3xl mx-auto object-cover ring-4 ring-amber-500 shadow-xl" alt="{{ $top3[0]->name }}">
                        <span class="absolute -bottom-1 -right-1 px-1.5 py-0.5 bg-amber-500 text-slate-950 font-black text-[10px] rounded-full">#1</span>
                    </a>
                    <div>
                        <h3 class="text-sm sm:text-base font-black truncate text-slate-900 dark:text-white">{{ $top3[0]->name }}</h3>
                        <p class="text-[11px] text-slate-400 truncate">{{ '@' . $top3[0]->username }}</p>
                    </div>
                    <div class="pt-1">
                        @if($sortBy === 'reading_time')
                            <span class="px-3 py-1.5 bg-amber-500 text-slate-950 rounded-xl text-xs font-black block shadow-md">
                                📖 {{ number_format($top3[0]->display_minutes) }} {{ __('site.leaderboard.min_short') }}
                            </span>
                        @elseif($sortBy === 'streak')
                            <span class="px-3 py-1.5 bg-amber-500 text-slate-950 rounded-xl text-xs font-black block shadow-md">
                                🔥 {{ $top3[0]->display_streak }} {{ __('site.leaderboard.days') }}
                            </span>
                        @else
                            <span class="px-3 py-1.5 bg-amber-500 text-slate-950 rounded-xl text-xs font-black block shadow-md">
                                ⭐️ {{ number_format($top3[0]->display_points) }} {{ __('site.leaderboard.points_short') }}
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            <!-- 3rd Place Bronze -->
            @if(isset($top3[2]))
                <div class="p-4 sm:p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-soft text-center space-y-2 relative group hover:border-amber-700 transition-colors">
                    <span class="text-2xl sm:text-3xl block">🥉</span>
                    <a href="{{ route('profile.show', $top3[2]->username) }}" class="inline-block">
                        <img src="{{ $top3[2]->avatar_url }}" class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl mx-auto object-cover ring-2 ring-amber-700 shadow-md group-hover:scale-105 transition-transform" alt="{{ $top3[2]->name }}">
                    </a>
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold truncate text-slate-900 dark:text-white">{{ $top3[2]->name }}</h3>
                        <p class="text-[10px] text-slate-400 truncate">{{ '@' . $top3[2]->username }}</p>
                    </div>
                    <div class="pt-1">
                        @if($sortBy === 'reading_time')
                            <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-xs font-black text-slate-700 dark:text-slate-300 block">
                                📖 {{ number_format($top3[2]->display_minutes) }} {{ __('site.leaderboard.min_short') }}
                            </span>
                        @elseif($sortBy === 'streak')
                            <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-xs font-black text-amber-500 block">
                                🔥 {{ $top3[2]->display_streak }} {{ __('site.leaderboard.days') }}
                            </span>
                        @else
                            <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-xs font-black text-slate-700 dark:text-slate-300 block">
                                ⭐️ {{ number_format($top3[2]->display_points) }} {{ __('site.leaderboard.points_short') }}
                            </span>
                        @endif
                    </div>
                </div>
            @else
                <div class="p-4 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800 text-center opacity-40">
                    <span class="text-2xl block">🥉</span>
                    <p class="text-xs text-slate-400 mt-2">{{ __('site.leaderboard.empty_podium') }}</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Leaderboard Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-soft overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('site.leaderboard.all_participants') }}</h3>
                <span class="text-xs text-slate-400">
                    @if($period === 'today') {{ __('site.leaderboard.sub_today') }}
                    @elseif($period === 'weekly') {{ __('site.leaderboard.sub_weekly') }}
                    @elseif($period === 'monthly') {{ __('site.leaderboard.sub_monthly') }}
                    @else {{ __('site.leaderboard.sub_all') }}
                    @endif
                </span>
            </div>
            <span class="text-xs text-slate-400">{{ __('site.leaderboard.readers_count', ['count' => $allUsers->total()]) }}</span>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
            @forelse ($allUsers as $u)
                @php 
                    $isCurrent = auth()->check() && auth()->id() === $u->id; 
                    $rank = $u->leaderboard_rank ?? $loop->iteration;
                @endphp
                <div class="p-4 sm:px-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors {{ $isCurrent ? 'bg-indigo-50/60 dark:bg-indigo-950/40 ring-1 ring-inset ring-indigo-500/30' : '' }}">
                    <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                        <!-- Rank Badge -->
                        <span class="w-7 text-center text-xs font-black shrink-0 {{ $rank === 1 ? 'text-amber-400 text-base' : ($rank === 2 ? 'text-slate-300 text-base' : ($rank === 3 ? 'text-amber-600 text-base' : 'text-slate-400')) }}">
                            @if($rank === 1) 🥇
                            @elseif($rank === 2) 🥈
                            @elseif($rank === 3) 🥉
                            @else {{ $rank }}
                            @endif
                        </span>

                        <!-- User Info -->
                        <a href="{{ route('profile.show', $u->username) }}" class="flex items-center gap-3 min-w-0 group">
                            <img src="{{ $u->avatar_url }}" class="w-10 h-10 rounded-xl object-cover shrink-0 ring-1 ring-slate-200 dark:ring-slate-700 group-hover:ring-amber-500 transition-all" alt="{{ $u->name }}">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate block group-hover:text-amber-400 transition-colors">{{ $u->name }}</span>
                                    @if($u->hasRole('admin') || $u->role === 'admin')
                                        <span class="px-1.5 py-0.2 bg-amber-500/10 text-amber-500 font-bold text-[9px] rounded uppercase shrink-0">{{ __('site.leaderboard.role_admin') }}</span>
                                    @elseif($u->hasRole('teacher') || $u->role === 'teacher')
                                        <span class="px-1.5 py-0.2 bg-indigo-500/10 text-indigo-400 font-bold text-[9px] rounded uppercase shrink-0">{{ __('site.leaderboard.role_teacher') }}</span>
                                    @endif
                                    @if($isCurrent)
                                        <span class="px-1.5 py-0.2 bg-emerald-500/10 text-emerald-400 font-bold text-[9px] rounded uppercase shrink-0">{{ __('site.leaderboard.you') }}</span>
                                    @endif
                                </div>
                                <span class="text-[11px] text-slate-400 truncate block">{{ '@' . $u->username }}</span>
                            </div>
                        </a>
                    </div>

                    <!-- Metrics -->
                    <div class="flex items-center gap-4 sm:gap-8 shrink-0">
                        <!-- Reading Minutes -->
                        <div class="hidden sm:block text-right">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 block">📖 {{ number_format($u->display_minutes) }}</span>
                            <span class="text-[10px] text-slate-400 block">{{ __('site.leaderboard.minutes') }}</span>
                        </div>

                        <!-- Streak -->
                        @if($u->display_streak > 0)
                            <div class="hidden md:flex items-center gap-1 text-xs font-bold text-amber-500 shrink-0" title="Ketma-ketlik">
                                <span>🔥</span> {{ $u->display_streak }} {{ __('site.leaderboard.days') }}
                            </div>
                        @endif

                        <!-- Points -->
                        <div class="text-right min-w-[70px]">
                            <span class="text-xs sm:text-sm font-black text-indigo-600 dark:text-indigo-400 block">
                                {{ number_format($u->display_points) }}
                            </span>
                            <span class="text-[10px] text-slate-400 block">{{ __('site.leaderboard.points_short') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-slate-400 text-sm">
                    {{ __('site.leaderboard.no_users') }}
                </div>
            @endforelse
        </div>

        @if ($allUsers->hasPages())
            <div class="p-4 sm:p-5 border-t border-slate-100 dark:border-slate-800">
                {{ $allUsers->links('vendor.pagination.taste-livewire') }}
            </div>
        @endif
    </div>
</div>
