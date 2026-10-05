<div class="max-w-5xl mx-auto space-y-8 pb-16">
    <!-- Header Section -->
    <div class="text-center max-w-xl mx-auto space-y-2">
        <span class="ks-eyebrow">
            {{ __('site.leaderboard.badge') }}
        </span>
        <h1 class="text-3xl sm:text-4xl font-bold font-serif text-paper tracking-tight">{{ __('site.leaderboard.title') }}</h1>
        <p class="text-xs sm:text-sm text-mist font-sans">{{ __('site.leaderboard.subtitle') }}</p>
    </div>

    <!-- Current User Stats Card -->
    @auth
        <div class="ks-panel p-5 sm:p-6 bg-ink-900 border border-ink-border">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <img src="{{ auth()->user()->avatar_url }}" class="w-12 h-12 rounded-card object-cover border border-amber-500/40 shrink-0" alt="{{ auth()->user()->name }}">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-bold uppercase tracking-wider text-amber-400">{{ __('site.leaderboard.your_result') }}</span>
                            @if($myRank)
                                <span class="px-2 py-0.5 rounded-badge bg-amber-500/10 border border-amber-500/30 text-[11px] font-mono font-bold text-amber-400">#{{ $myRank }} {{ __('site.leaderboard.place') }}</span>
                            @endif
                        </div>
                        <h3 class="text-base font-bold font-serif text-paper">{{ auth()->user()->name }}</h3>
                        <p class="text-xs text-mist font-mono">{{ '@' . auth()->user()->username }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 sm:gap-6 text-center sm:text-right font-mono">
                    <div>
                        <span class="text-[11px] text-mist block uppercase">{{ __('site.leaderboard.total_points') }}</span>
                        <span class="text-lg font-bold text-amber-400">{{ number_format($myUser->display_points ?? auth()->user()->total_points) }}</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-mist block uppercase">{{ __('site.leaderboard.reading') }}</span>
                        <span class="text-lg font-bold text-paper">{{ number_format($myUser->display_minutes ?? 0) }} <span class="text-xs text-mist font-sans font-normal">{{ __('site.leaderboard.min_short') }}</span></span>
                    </div>
                    <div>
                        <span class="text-[11px] text-mist block uppercase">{{ __('site.leaderboard.streak') }}</span>
                        <span class="text-lg font-bold text-amber-500 flex items-center gap-1 justify-center sm:justify-end">
                            <svg class="w-4 h-4 ks-flame is-lit" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c-.5 2.5-2.5 4.5-4 6-2 2-3 4.5-3 7 0 4.4 3.6 8 8 8s8-3.6 8-8c0-3.5-2-6-4-8-.5 2-2 3.5-3 4-1-2.5 0-6.5-2-9z"/></svg>
                            {{ $myUser->display_streak ?? auth()->user()->current_streak }} {{ __('site.leaderboard.days') }}
                        </span>
                    </div>
                </div>
            </div>

            @if($myRank && $myRank > 1 && $pointsToNext)
                <div class="mt-4 pt-3 border-t border-ink-border flex items-center justify-between text-xs text-mist font-mono">
                    <span>{{ __('site.leaderboard.to_next', ['rank' => $myRank - 1]) }}</span>
                    <span class="font-bold text-amber-400">{{ __('site.leaderboard.points_needed', ['points' => number_format($pointsToNext)]) }}</span>
                </div>
            @elseif($myRank === 1)
                <div class="mt-4 pt-3 border-t border-ink-border flex items-center justify-between text-xs text-amber-400 font-mono font-bold">
                    <span>✦ {{ __('site.leaderboard.congrats') }}</span>
                    <span>{{ __('site.leaderboard.first_place') }}</span>
                </div>
            @endif
        </div>
    @else
        <div class="ks-panel p-5 bg-ink-900 border border-ink-border flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-amber-500 font-bold text-xl">✦</span>
                <div>
                    <h4 class="text-sm font-bold font-serif text-paper">{{ __('site.leaderboard.join_cta_title') }}</h4>
                    <p class="text-xs text-mist font-sans">{{ __('site.leaderboard.join_cta_sub') }}</p>
                </div>
            </div>
            <a href="{{ route('login') }}" class="ks-btn-primary py-2 px-4 text-xs font-semibold shrink-0">
                {{ __('site.leaderboard.login_start') }}
            </a>
        </div>
    @endauth

    <!-- Filter & Sort Bar -->
    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 sm:gap-4 p-3 sm:p-4 rounded-panel bg-ink-900 border border-ink-border">
        <!-- Period Tabs -->
        <div class="flex items-center p-1 bg-ink-950 border border-ink-border rounded-btn overflow-x-auto no-scrollbar shrink-0 font-mono text-xs">
            <button type="button" wire:click="setPeriod('all_time')"
                class="px-2.5 sm:px-3 py-1 rounded-badge font-bold transition-colors whitespace-nowrap {{ $period === 'all_time' ? 'bg-amber-500 text-ink-950' : 'text-mist hover:text-paper' }}">
                {{ __('site.leaderboard.period_all') }}
            </button>
            <button type="button" wire:click="setPeriod('monthly')"
                class="px-2.5 sm:px-3 py-1 rounded-badge font-bold transition-colors whitespace-nowrap {{ $period === 'monthly' ? 'bg-amber-500 text-ink-950' : 'text-mist hover:text-paper' }}">
                {{ __('site.leaderboard.period_monthly') }}
            </button>
            <button type="button" wire:click="setPeriod('weekly')"
                class="px-2.5 sm:px-3 py-1 rounded-badge font-bold transition-colors whitespace-nowrap {{ $period === 'weekly' ? 'bg-amber-500 text-ink-950' : 'text-mist hover:text-paper' }}">
                {{ __('site.leaderboard.period_weekly') }}
            </button>
            <button type="button" wire:click="setPeriod('today')"
                class="px-2.5 sm:px-3 py-1 rounded-badge font-bold transition-colors whitespace-nowrap {{ $period === 'today' ? 'bg-amber-500 text-ink-950' : 'text-mist hover:text-paper' }}">
                {{ __('site.leaderboard.period_today') }}
            </button>
        </div>

        <!-- Sort Criteria -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
            <span class="text-xs text-mist font-mono hidden lg:inline">{{ __('site.leaderboard.sort_by') }}</span>
            <div class="flex items-center p-1 bg-ink-950 border border-ink-border rounded-btn shrink-0 font-mono text-xs overflow-x-auto no-scrollbar">
                <button type="button" wire:click="setSortBy('points')"
                    class="px-2.5 sm:px-3 py-1 rounded-badge font-bold transition-colors whitespace-nowrap {{ $sortBy === 'points' ? 'bg-ink-800 text-amber-400 border border-amber-500/30' : 'text-mist hover:text-paper' }}">
                    {{ __('site.leaderboard.sort_points') }}
                </button>
                <button type="button" wire:click="setSortBy('reading_time')"
                    class="px-2.5 sm:px-3 py-1 rounded-badge font-bold transition-colors whitespace-nowrap {{ $sortBy === 'reading_time' ? 'bg-ink-800 text-amber-400 border border-amber-500/30' : 'text-mist hover:text-paper' }}">
                    {{ __('site.leaderboard.sort_reading') }}
                </button>
                <button type="button" wire:click="setSortBy('streak')"
                    class="px-2.5 sm:px-3 py-1 rounded-badge font-bold transition-colors whitespace-nowrap {{ $sortBy === 'streak' ? 'bg-ink-800 text-amber-400 border border-amber-500/30' : 'text-mist hover:text-paper' }}">
                    {{ __('site.leaderboard.sort_streak') }}
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="relative w-full md:w-auto md:min-w-[200px]">
            <input type="text" wire:model.debounce.300ms="search" placeholder="{{ __('site.leaderboard.search_placeholder') }}"
                class="ks-input pl-8 py-1.5 text-xs font-mono w-full">
            <svg class="w-3.5 h-3.5 text-mist absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
    </div>

    <!-- Editorial Top 3 Podium (Olympic layout on mobile & desktop) -->
    @if($top3->count() > 0)
        <div class="grid grid-cols-3 gap-2 sm:gap-4 pt-2 sm:pt-4 items-end">
            <!-- 2nd Place Silver -->
            @if(isset($top3[1]))
                <div class="ks-panel p-2.5 sm:p-5 bg-gradient-to-b from-[#E2E8F0]/10 to-ink-900 border border-[#E2E8F0]/40 text-center space-y-1.5 sm:space-y-2.5 relative shadow-lg shadow-black/30">
                    <div class="flex items-center justify-center">
                        <x-ui.rank-badge rank="2" size="xs" :compact="true" class="sm:hidden" />
                        <x-ui.rank-badge rank="2" size="sm" class="hidden sm:inline-flex" />
                    </div>
                    <a href="{{ route('profile.show', $top3[1]->username) }}" class="inline-block">
                        <x-ui.avatar :user="$top3[1]" size="md" :rank="2" class="sm:hidden" />
                        <x-ui.avatar :user="$top3[1]" size="xl" :rank="2" class="hidden sm:inline-block" />
                    </a>
                    <div class="min-w-0">
                        <h3 class="text-xs sm:text-sm font-bold font-serif truncate text-paper">{{ $top3[1]->name }}</h3>
                        <p class="text-[9px] sm:text-[10px] text-[#E2E8F0] font-mono truncate">{{ '@' . $top3[1]->username }}</p>
                    </div>
                    <div class="pt-0.5 sm:pt-1 font-mono text-[11px] sm:text-xs">
                        @if($sortBy === 'reading_time')
                            <span class="text-paper font-bold block truncate">{{ number_format($top3[1]->display_minutes) }} <span class="hidden sm:inline">{{ __('site.leaderboard.min_short') }}</span></span>
                        @elseif($sortBy === 'streak')
                            <span class="text-amber-500 font-bold block truncate">{{ $top3[1]->display_streak }} <span class="hidden sm:inline">{{ __('site.leaderboard.days') }}</span></span>
                        @else
                            <span class="text-[#E2E8F0] font-bold block truncate">{{ number_format($top3[1]->display_points) }} <span class="hidden sm:inline">{{ __('site.leaderboard.points_short') }}</span></span>
                        @endif
                    </div>
                </div>
            @endif

            <!-- 1st Place Gold (Elevated in Center) -->
            @if(isset($top3[0]))
                <div class="ks-panel p-3 sm:p-6 bg-gradient-to-b from-[#F59E0B]/15 via-ink-900 to-ink-900 border-2 border-[#F59E0B]/70 text-center space-y-2 sm:space-y-2.5 relative shadow-xl shadow-[#F59E0B]/10 ring-1 ring-[#F59E0B]/30 sm:-translate-y-2">
                    <div class="flex items-center justify-center">
                        <x-ui.rank-badge rank="1" size="xs" :compact="true" class="sm:hidden" />
                        <x-ui.rank-badge rank="1" size="md" class="hidden sm:inline-flex" />
                    </div>
                    <a href="{{ route('profile.show', $top3[0]->username) }}" class="inline-block relative">
                        <x-ui.avatar :user="$top3[0]" size="lg" :rank="1" class="sm:hidden" />
                        <x-ui.avatar :user="$top3[0]" size="2xl" :rank="1" class="hidden sm:inline-block" />
                    </a>
                    <div class="min-w-0">
                        <h3 class="text-xs sm:text-base font-bold font-serif truncate text-paper">{{ $top3[0]->name }}</h3>
                        <p class="text-[9px] sm:text-[11px] text-[#F59E0B] font-mono font-bold truncate">{{ '@' . $top3[0]->username }}</p>
                    </div>
                    <div class="pt-0.5 sm:pt-1 font-mono text-[11px] sm:text-xs">
                        @if($sortBy === 'reading_time')
                            <span class="px-1.5 sm:px-3 py-0.5 sm:py-1 rounded-badge bg-[#F59E0B] text-ink-950 font-bold inline-block shadow-md truncate max-w-full">{{ number_format($top3[0]->display_minutes) }} <span class="hidden sm:inline">{{ __('site.leaderboard.min_short') }}</span></span>
                        @elseif($sortBy === 'streak')
                            <span class="px-1.5 sm:px-3 py-0.5 sm:py-1 rounded-badge bg-[#F59E0B] text-ink-950 font-bold inline-block shadow-md truncate max-w-full">{{ $top3[0]->display_streak }} <span class="hidden sm:inline">{{ __('site.leaderboard.days') }}</span></span>
                        @else
                            <span class="px-1.5 sm:px-3 py-0.5 sm:py-1 rounded-badge bg-[#F59E0B] text-ink-950 font-bold inline-block shadow-md truncate max-w-full">{{ number_format($top3[0]->display_points) }} <span class="hidden sm:inline">{{ __('site.leaderboard.points_short') }}</span></span>
                        @endif
                    </div>
                </div>
            @endif

            <!-- 3rd Place Bronze -->
            @if(isset($top3[2]))
                <div class="ks-panel p-2.5 sm:p-5 bg-gradient-to-b from-[#D97706]/10 to-ink-900 border border-[#D97706]/40 text-center space-y-1.5 sm:space-y-2.5 relative shadow-lg shadow-black/30">
                    <div class="flex items-center justify-center">
                        <x-ui.rank-badge rank="3" size="xs" :compact="true" class="sm:hidden" />
                        <x-ui.rank-badge rank="3" size="sm" class="hidden sm:inline-flex" />
                    </div>
                    <a href="{{ route('profile.show', $top3[2]->username) }}" class="inline-block">
                        <x-ui.avatar :user="$top3[2]" size="md" :rank="3" class="sm:hidden" />
                        <x-ui.avatar :user="$top3[2]" size="xl" :rank="3" class="hidden sm:inline-block" />
                    </a>
                    <div class="min-w-0">
                        <h3 class="text-xs sm:text-sm font-bold font-serif truncate text-paper">{{ $top3[2]->name }}</h3>
                        <p class="text-[9px] sm:text-[10px] text-[#D97706] font-mono truncate">{{ '@' . $top3[2]->username }}</p>
                    </div>
                    <div class="pt-0.5 sm:pt-1 font-mono text-[11px] sm:text-xs">
                        @if($sortBy === 'reading_time')
                            <span class="text-paper font-bold block truncate">{{ number_format($top3[2]->display_minutes) }} <span class="hidden sm:inline">{{ __('site.leaderboard.min_short') }}</span></span>
                        @elseif($sortBy === 'streak')
                            <span class="text-amber-500 font-bold block truncate">{{ $top3[2]->display_streak }} <span class="hidden sm:inline">{{ __('site.leaderboard.days') }}</span></span>
                        @else
                            <span class="text-[#D97706] font-bold block truncate">{{ number_format($top3[2]->display_points) }} <span class="hidden sm:inline">{{ __('site.leaderboard.points_short') }}</span></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Leaderboard Table -->
    <div class="bg-ink-900 rounded-panel border border-ink-border overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-ink-border flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold font-serif text-paper">{{ __('site.leaderboard.all_participants') }}</h3>
                <span class="text-xs text-mist font-mono">
                    @if($period === 'today') {{ __('site.leaderboard.sub_today') }}
                    @elseif($period === 'weekly') {{ __('site.leaderboard.sub_weekly') }}
                    @elseif($period === 'monthly') {{ __('site.leaderboard.sub_monthly') }}
                    @else {{ __('site.leaderboard.sub_all') }}
                    @endif
                </span>
            </div>
            <span class="text-xs text-mist font-mono">{{ __('site.leaderboard.readers_count', ['count' => $allUsers->total()]) }}</span>
        </div>

        <div class="divide-y divide-ink-border/50">
            @forelse ($allUsers as $u)
                @php 
                    $isCurrent = auth()->check() && auth()->id() === $u->id; 
                    $rank = (int) ($u->leaderboard_rank ?? $loop->iteration);

                    $rowTierClass = match($rank) {
                        1 => 'bg-[#F59E0B]/10 border-l-2 border-[#F59E0B]',
                        2 => 'bg-[#E2E8F0]/5 border-l-2 border-[#E2E8F0]',
                        3 => 'bg-[#D97706]/5 border-l-2 border-[#D97706]',
                        4, 5 => 'bg-[#6366F1]/5 border-l-2 border-[#6366F1]',
                        default => $isCurrent ? 'bg-amber-500/5 border-l-2 border-amber-400' : '',
                    };
                @endphp
                <div class="p-2.5 sm:p-3.5 sm:px-6 flex items-center justify-between hover:bg-ink-800/40 transition-colors {{ $rowTierClass }}">
                    <div class="flex items-center gap-2 sm:gap-4 min-w-0">
                        <!-- Rank Numeral & Badge -->
                        <div class="w-8 sm:w-12 text-center shrink-0 flex items-center justify-center gap-1">
                            @if($rank <= 5)
                                <x-ui.rank-badge :rank="$rank" compact="true" size="xs" />
                            @else
                                <span class="text-xs font-mono font-bold text-mist">
                                    {{ str_pad($rank, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            @endif
                        </div>

                        <!-- User Info with Tier Avatar -->
                        <a href="{{ route('profile.show', $u->username) }}" class="flex items-center gap-2 sm:gap-3 min-w-0 group">
                            <x-ui.avatar :user="$u" size="sm" :rank="$rank" />
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-xs sm:text-sm font-semibold font-sans text-paper truncate block group-hover:text-amber-400 transition-colors max-w-[110px] sm:max-w-none">{{ $u->name }}</span>
                                    @if($u->hasRole('admin') || $u->role === 'admin')
                                        <span class="px-1.5 py-0.2 bg-amber-500/15 border border-amber-500/30 text-amber-400 font-mono font-bold text-[9px] rounded uppercase shrink-0">{{ __('site.leaderboard.role_admin') }}</span>
                                    @elseif($u->hasRole('teacher') || $u->role === 'teacher')
                                        <span class="px-1.5 py-0.2 bg-ink-950 border border-ink-border text-mist font-mono font-bold text-[9px] rounded uppercase shrink-0">{{ __('site.leaderboard.role_teacher') }}</span>
                                    @endif
                                    @if($isCurrent)
                                        <span class="px-1.5 py-0.2 bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 font-mono font-bold text-[9px] rounded uppercase shrink-0">{{ __('site.leaderboard.you') }}</span>
                                    @endif
                                </div>
                                <span class="text-[10px] sm:text-[11px] text-mist font-mono truncate block max-w-[110px] sm:max-w-none">{{ '@' . $u->username }}</span>
                            </div>
                        </a>
                    </div>

                    <!-- Metrics in DM Mono -->
                    <div class="flex items-center gap-2 sm:gap-8 shrink-0 font-mono">
                        <div class="hidden sm:block text-right">
                            <span class="text-xs font-bold text-paper block">{{ number_format($u->display_minutes) }}</span>
                            <span class="text-[10px] text-mist block">{{ __('site.leaderboard.minutes') }}</span>
                        </div>

                        @if($u->display_streak > 0)
                            <div class="hidden md:flex items-center gap-1 text-xs font-bold text-amber-500 shrink-0" title="Ketma-ketlik">
                                <svg class="w-3.5 h-3.5 ks-flame is-lit" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c-.5 2.5-2.5 4.5-4 6-2 2-3 4.5-3 7 0 4.4 3.6 8 8 8s8-3.6 8-8c0-3.5-2-6-4-8-.5 2-2 3.5-3 4-1-2.5 0-6.5-2-9z"/></svg>
                                <span>{{ $u->display_streak }}</span>
                            </div>
                        @endif

                        <div class="text-right min-w-[56px] sm:min-w-[70px]">
                            <span class="text-xs sm:text-sm font-bold text-amber-400 block">
                                {{ number_format($u->display_points) }}
                            </span>
                            <span class="text-[9px] sm:text-[10px] text-mist block">{{ __('site.leaderboard.points_short') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-mist text-xs font-mono">
                    {{ __('site.leaderboard.no_users') }}
                </div>
            @endforelse
        </div>

        @if ($allUsers->hasPages())
            <div class="p-4 border-t border-ink-border">
                {{ $allUsers->links('vendor.pagination.taste-livewire') }}
            </div>
        @endif
    </div>
</div>
