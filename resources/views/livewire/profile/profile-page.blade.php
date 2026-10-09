@php
    $isTopFive = ($userRank !== null && $userRank >= 1 && $userRank <= 5);
    $cardAccent = match(true) {
        $userRank === 1 => 'border-amber-500/40 dark:border-[#F59E0B]/40 before:absolute before:inset-0 before:bg-gradient-to-r before:from-amber-500/10 dark:before:from-[#F59E0B]/10 before:via-transparent before:to-transparent before:pointer-events-none',
        $userRank === 2 => 'border-slate-400/50 dark:border-[#E2E8F0]/35 before:absolute before:inset-0 before:bg-gradient-to-r before:from-slate-400/15 dark:before:from-[#E2E8F0]/8 before:via-transparent before:to-transparent before:pointer-events-none',
        $userRank === 3 => 'border-amber-700/40 dark:border-[#D97706]/35 before:absolute before:inset-0 before:bg-gradient-to-r before:from-amber-700/10 dark:before:from-[#D97706]/8 before:via-transparent before:to-transparent before:pointer-events-none',
        $userRank === 4 || $userRank === 5 => 'border-indigo-400/40 dark:border-[#6366F1]/35 before:absolute before:inset-0 before:bg-gradient-to-r before:from-indigo-500/10 dark:before:from-[#6366F1]/8 before:via-transparent before:to-transparent before:pointer-events-none',
        default => 'border-ink-border',
    };
@endphp

<div class="max-w-6xl mx-auto space-y-6 pb-16">

    <!-- Profile Header Card -->
    <div class="p-6 sm:p-8 rounded-panel bg-ink-900 border relative overflow-hidden {{ $cardAccent }}">
        <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left">
            <!-- Avatar -->
            <div class="relative shrink-0">
                <x-ui.avatar :user="$user" size="3xl" shape="rounded-panel" :rank="$userRank" />
                @if($stats['current_streak'] > 0)
                    <div class="absolute -bottom-2 -right-2 px-2 py-0.5 bg-amber-400 text-ink-950 font-mono font-bold text-[11px] rounded-pill shadow-md flex items-center gap-1 z-20">
                        <span class="ks-flame is-lit inline-block scale-75">
                            <svg class="w-3.5 h-3.5 text-ink-950 fill-current" viewBox="0 0 24 24"><path d="M12 2c0 4-4 6-4 10a6 6 0 0 0 12 0c0-4-4-6-4-10z"/></svg>
                        </span>
                        <span>{{ $stats['current_streak'] }}</span>
                    </div>
                @endif
            </div>

            <!-- Profile Info -->
            <div class="flex-1 space-y-3 min-w-0">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center justify-center sm:justify-start gap-2.5 flex-wrap">
                            <h1 class="text-2xl sm:text-3xl font-bold text-paper font-serif">{{ $user->name }}</h1>
                            @if($isTopFive)
                                <x-ui.rank-badge :rank="$userRank" size="md" />
                            @endif
                            @if($user->role === 'admin' || $user->hasRole('admin'))
                                <span class="px-2 py-0.5 rounded-pill bg-rose-500/10 dark:bg-[#C1392B]/15 border border-rose-500/25 dark:border-rose-500/30 text-rose-700 dark:text-rose-300 text-[10px] font-mono uppercase tracking-wider">{{ __('site.profile.role_admin') }}</span>
                            @elseif($user->role === 'teacher' || $user->hasRole('teacher'))
                                <span class="px-2 py-0.5 rounded-pill bg-amber-500/10 border border-amber-500/25 text-amber-800 dark:text-amber-400 text-[10px] font-mono uppercase tracking-wider">{{ __('site.profile.role_teacher') }}</span>
                            @else
                                <span class="px-2 py-0.5 rounded-pill bg-ink-800 border border-ink-border text-mist text-[10px] font-mono uppercase tracking-wider">{{ __('site.profile.role_reader') }}</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-center sm:justify-start gap-3 mt-1 flex-wrap">
                            <p class="text-xs text-mist font-mono">{{ '@' . $user->username }}</p>
                            @if($isTopFive)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-pill text-[11px] font-mono font-bold
                                    {{ $userRank === 1 ? 'bg-amber-500/15 text-amber-700 dark:text-[#F59E0B] border border-amber-500/40 dark:border-[#F59E0B]/40 shadow-sm dark:shadow-[0_0_10px_rgba(245,158,11,0.2)]' : '' }}
                                    {{ $userRank === 2 ? 'bg-slate-200/90 dark:bg-[#E2E8F0]/15 text-slate-700 dark:text-[#E2E8F0] border border-slate-400/60 dark:border-[#E2E8F0]/40 shadow-sm dark:shadow-[0_0_8px_rgba(226,232,240,0.15)]' : '' }}
                                    {{ $userRank === 3 ? 'bg-amber-700/15 dark:bg-[#D97706]/15 text-amber-800 dark:text-[#D97706] border border-amber-700/40 dark:border-[#D97706]/40 shadow-sm dark:shadow-[0_0_8px_rgba(217,119,6,0.15)]' : '' }}
                                    {{ ($userRank === 4 || $userRank === 5) ? 'bg-indigo-500/15 dark:bg-[#6366F1]/15 text-indigo-700 dark:text-[#818CF8] border border-indigo-500/40 dark:border-[#6366F1]/40 shadow-sm dark:shadow-[0_0_8px_rgba(99,102,241,0.15)]' : '' }}">
                                    @if($userRank === 1)
                                        <svg class="w-3.5 h-3.5 shrink-0 text-amber-600 dark:text-[#F59E0B]" viewBox="0 0 24 24" fill="currentColor"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>
                                        <span>Reytingda #1 o'rin &bull; Peshqadam</span>
                                    @elseif($userRank === 2)
                                        <svg class="w-3.5 h-3.5 shrink-0 text-slate-700 dark:text-[#E2E8F0]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="14" r="7"/><path d="M8.21 13.89L7 22l5-3 5 3-1.21-8.11"/><path d="M12 7V3"/></svg>
                                        <span>Reytingda #2 o'rin &bull; Kumush sovrindor</span>
                                    @elseif($userRank === 3)
                                        <svg class="w-3.5 h-3.5 shrink-0 text-amber-800 dark:text-[#D97706]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="14" r="7"/><path d="M8.21 13.89L7 22l5-3 5 3-1.21-8.11"/><path d="M12 7V3"/></svg>
                                        <span>Reytingda #3 o'rin &bull; Bronza sovrindor</span>
                                    @else
                                        <svg class="w-3.5 h-3.5 shrink-0 text-indigo-700 dark:text-[#818CF8]" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        <span>Reytingda #{{ $userRank }} o'rin &bull; Top 5</span>
                                    @endif
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div>
                        @if ($isOwner)
                            <a href="{{ route('settings') }}" class="ks-btn-ghost text-xs py-2 px-3.5 inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-mist" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                <span>{{ __('site.profile.settings') }}</span>
                            </a>
                        @else
                            <button wire:click="toggleFollow" class="text-xs py-2 px-4 rounded-btn font-mono font-bold transition-all {{ $isFollowing ? 'bg-ink-800 hover:bg-ink-700/60 border border-ink-border text-paper' : 'ks-btn-primary' }}">
                                {{ $isFollowing ? '✓ '.__('site.profile.following') : '+ '.__('site.profile.follow') }}
                            </button>
                        @endif
                    </div>
                </div>

                @if ($user->bio)
                    <p class="text-xs sm:text-sm text-mist max-w-2xl leading-relaxed font-sans">
                        {{ $user->bio }}
                    </p>
                @endif

                <!-- Meta Pills -->
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1 font-mono text-xs">
                    @if($user->profile && $user->profile->reading_place)
                        <span class="px-2.5 py-1 rounded-pill bg-ink-800 border border-ink-border text-mist flex items-center gap-1.5">
                            <svg class="w-3 h-3 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span>{{ __('site.profile.reading_place') }}: <strong class="text-paper">{{ ucfirst($user->profile->reading_place) }}</strong></span>
                        </span>
                    @endif

                    @if($user->profile && $user->profile->reading_goal)
                        <span class="px-2.5 py-1 rounded-pill bg-ink-800 border border-ink-border text-mist flex items-center gap-1.5">
                            <svg class="w-3 h-3 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                            <span>{{ __('site.profile.goal') }}: <strong class="text-paper">{{ ucfirst($user->profile->reading_goal) }}</strong></span>
                        </span>
                    @endif

                    <span class="px-2.5 py-1 rounded-pill bg-ink-800 border border-ink-border text-mist flex items-center gap-1.5">
                        <svg class="w-3 h-3 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ __('site.profile.joined') }}: {{ $user->created_at->format('d.m.Y') }}</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Stats Grid in Header (DENSITY: 6, DM Mono) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-6 border-t border-ink-border">
            <div class="p-3.5 rounded-panel bg-ink-800/60 border border-ink-border">
                <div class="flex items-center gap-1 text-[11px] text-mist font-mono uppercase tracking-wider mb-1">
                    <span class="ks-flame is-lit inline-block scale-75">
                        <svg class="w-3.5 h-3.5 text-amber-400 fill-amber-400" viewBox="0 0 24 24"><path d="M12 2c0 4-4 6-4 10a6 6 0 0 0 12 0c0-4-4-6-4-10z"/></svg>
                    </span>
                    <span>{{ __('site.profile.streak') }}</span>
                </div>
                <p class="text-2xl font-bold font-mono text-amber-400">{{ $stats['current_streak'] }} <span class="text-xs text-mist font-normal font-sans">{{ __('site.profile.days') }}</span></p>
            </div>
            <div class="p-3.5 rounded-panel bg-ink-800/60 border border-ink-border">
                <span class="text-[11px] text-mist font-mono uppercase tracking-wider block mb-1">{{ __('site.profile.total_points') }}</span>
                <p class="text-2xl font-bold font-mono text-paper">{{ number_format($stats['total_points']) }}</p>
            </div>
            <div class="p-3.5 rounded-panel bg-ink-800/60 border border-ink-border">
                <span class="text-[11px] text-mist font-mono uppercase tracking-wider block mb-1">{{ __('site.profile.coin_balance') }}</span>
                <p class="text-2xl font-bold font-mono text-amber-400">{{ number_format($stats['coin_balance']) }}</p>
            </div>
            <div class="p-3.5 rounded-panel bg-ink-800/60 border border-ink-border">
                <span class="text-[11px] text-mist font-mono uppercase tracking-wider block mb-1">{{ __('site.profile.reading_time') }}</span>
                <p class="text-2xl font-bold font-mono text-emerald-400">{{ number_format($stats['total_minutes']) }} <span class="text-xs text-mist font-normal font-sans">{{ __('site.profile.minutes_short') }}</span></p>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-ink-border pb-2 overflow-x-auto no-scrollbar py-0.5 -mx-1 px-1 sm:mx-0 sm:px-0">
        <button wire:click="setTab('overview')" class="px-3.5 py-1.5 rounded-btn text-xs font-mono font-medium transition-all shrink-0 {{ $activeTab === 'overview' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/25' : 'text-mist hover:text-paper hover:bg-ink-900' }}">
            {{ __('site.profile.tab_overview') }}
        </button>
        <button wire:click="setTab('books')" class="px-3.5 py-1.5 rounded-btn text-xs font-mono font-medium transition-all shrink-0 {{ $activeTab === 'books' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/25' : 'text-mist hover:text-paper hover:bg-ink-900' }}">
            {{ __('site.profile.tab_books') }} ({{ count($readingProgresses) }})
        </button>
        <button wire:click="setTab('badges')" class="px-3.5 py-1.5 rounded-btn text-xs font-mono font-medium transition-all shrink-0 {{ $activeTab === 'badges' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/25' : 'text-mist hover:text-paper hover:bg-ink-900' }}">
            {{ __('site.profile.tab_badges') }} ({{ count($badges) }})
        </button>
        <button wire:click="setTab('notes')" class="px-3.5 py-1.5 rounded-btn text-xs font-mono font-medium transition-all shrink-0 {{ $activeTab === 'notes' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/25' : 'text-mist hover:text-paper hover:bg-ink-900' }}">
            {{ __('site.profile.tab_notes') }} ({{ count($notes) }})
        </button>
    </div>

    <!-- Tab 1: Overview & Heatmap -->
    @if ($activeTab === 'overview')
        <div class="space-y-5">
            <!-- Activity Heatmap Card -->
            <div class="p-5 sm:p-6 rounded-panel bg-ink-900 border border-ink-border space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-paper font-serif flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                            {{ __('site.profile.heatmap_title') }}
                        </h3>
                        <p class="text-xs text-mist font-mono mt-0.5">{{ __('site.profile.heatmap_hint') }}</p>
                    </div>
                    <span class="text-xs font-mono text-mist">Asia/Tashkent</span>
                </div>

                <div class="overflow-x-auto no-scrollbar pb-2 pt-2">
                    <div class="flex gap-1.5 min-w-max">
                        @for ($i = 59; $i >= 0; $i--)
                            @php
                                $date = now()->subDays($i)->toDateString();
                                $minutes = $activities[$date] ?? 0;
                                $colorClass = 'bg-ink-800';
                                if ($minutes > 0 && $minutes <= 15) $colorClass = 'bg-emerald-600/50';
                                elseif ($minutes > 15 && $minutes <= 30) $colorClass = 'bg-emerald-500/80';
                                elseif ($minutes > 30) $colorClass = 'bg-emerald-400';
                            @endphp
                            <div class="w-3.5 h-10 rounded-sm {{ $colorClass }} transition-all hover:scale-110 cursor-pointer" 
                                 title="{{ $date }}: {{ $minutes }} {{ __('site.profile.minutes_read') }}"></div>
                        @endfor
                    </div>
                </div>

                <!-- Legend -->
                <div class="flex items-center justify-between text-[11px] text-mist font-mono pt-2 border-t border-ink-border">
                    <span>{{ __('site.profile.less') }}</span>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-sm bg-ink-800"></span>
                        <span class="w-3 h-3 rounded-sm bg-emerald-600/50"></span>
                        <span class="w-3 h-3 rounded-sm bg-emerald-500/80"></span>
                        <span class="w-3 h-3 rounded-sm bg-emerald-400"></span>
                    </div>
                    <span>{{ __('site.profile.more') }}</span>
                </div>
            </div>

            <!-- Real Activity Grid Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
                <div class="p-4 rounded-panel bg-ink-900 border border-ink-border space-y-1">
                    <span class="text-[11px] text-mist font-mono uppercase tracking-wider block">{{ __('site.profile.books_reading') }}</span>
                    <span class="text-xl font-bold font-mono text-paper">{{ $stats['books_reading'] }} <small class="text-xs text-mist font-sans">{{ __('site.profile.count_suffix') }}</small></span>
                </div>
                <div class="p-4 rounded-panel bg-ink-900 border border-ink-border space-y-1">
                    <span class="text-[11px] text-mist font-mono uppercase tracking-wider block">{{ __('site.profile.quizzes_passed') }}</span>
                    <span class="text-xl font-bold font-mono text-amber-400">{{ $stats['quizzes_passed'] }} <small class="text-xs text-mist font-sans">{{ __('site.profile.count_suffix') }}</small></span>
                </div>
                <div class="p-4 rounded-panel bg-ink-900 border border-ink-border space-y-1">
                    <span class="text-[11px] text-mist font-mono uppercase tracking-wider block">{{ __('site.profile.groups') }}</span>
                    <span class="text-xl font-bold font-mono text-paper">{{ $stats['groups_count'] }} <small class="text-xs text-mist font-sans">{{ __('site.profile.count_suffix') }}</small></span>
                </div>
                <div class="p-4 rounded-panel bg-ink-900 border border-ink-border space-y-1">
                    <span class="text-[11px] text-mist font-mono uppercase tracking-wider block">{{ __('site.profile.chat_messages') }}</span>
                    <span class="text-xl font-bold font-mono text-emerald-400">{{ $stats['messages_count'] }} <small class="text-xs text-mist font-sans">{{ __('site.profile.count_suffix') }}</small></span>
                </div>
            </div>
        </div>
    @endif

    <!-- Tab 2: Books Progress -->
    @if ($activeTab === 'books')
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($readingProgresses as $progress)
                @if($progress->book)
                    <div class="p-3.5 rounded-panel bg-ink-900 border border-ink-border flex gap-3.5 hover:border-amber-500/30 transition-all">
                        <img src="{{ $progress->book->cover_url }}" class="w-14 h-20 rounded object-cover shrink-0 border border-ink-border">
                        <div class="flex-1 min-w-0 flex flex-col justify-between">
                            <div>
                                <h4 class="text-xs sm:text-sm font-semibold text-paper truncate">{{ $progress->book->title }}</h4>
                                <p class="text-[11px] text-mist truncate font-sans">{{ $progress->book->author }}</p>
                            </div>
                            <div class="space-y-1 pt-2">
                                <div class="flex justify-between text-[10px] font-mono text-mist">
                                    <span>{{ __('site.books.reading') }}</span>
                                    <span class="text-amber-400 font-bold">{{ number_format($progress->percent_complete) }}%</span>
                                </div>
                                <div class="w-full bg-ink-800 rounded-pill h-1.5 overflow-hidden">
                                    <div class="bg-amber-400 h-1.5 rounded-pill" style="width: {{ $progress->percent_complete }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="col-span-full py-12 text-center p-8 rounded-panel bg-ink-900 border border-ink-border">
                    <div class="w-10 h-10 mx-auto rounded-btn bg-ink-800 border border-ink-border flex items-center justify-center text-amber-400 mb-2">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20 M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    </div>
                    <p class="text-xs text-mist font-mono">{{ __('site.profile.empty_books') }}</p>
                </div>
            @endforelse
        </div>
    @endif

    <!-- Tab 3: Badges -->
    @if ($activeTab === 'badges')
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
            @forelse ($badges as $badge)
                <div class="p-4 rounded-panel bg-ink-900 border border-ink-border text-center space-y-2 hover:border-amber-400/40 transition-all">
                    <div class="w-12 h-12 rounded-btn bg-amber-500/10 border border-amber-500/25 text-amber-400 mx-auto flex items-center justify-center">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                    </div>
                    <h4 class="text-xs font-bold text-paper">{{ $badge->name }}</h4>
                    <p class="text-[11px] text-mist leading-snug font-sans">{{ $badge->description }}</p>
                    @if($badge->pivot && $badge->pivot->earned_at)
                        <span class="inline-block text-[10px] text-amber-400 font-mono pt-1">
                            ✓ {{ \Carbon\Carbon::parse($badge->pivot->earned_at)->format('d.m.Y') }}
                        </span>
                    @endif
                </div>
            @empty
                <div class="col-span-full py-12 text-center p-8 rounded-panel bg-ink-900 border border-ink-border">
                    <div class="w-10 h-10 mx-auto rounded-btn bg-ink-800 border border-ink-border flex items-center justify-center text-amber-400 mb-2">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                    </div>
                    <p class="text-xs text-mist font-mono">{{ __('site.profile.empty_badges') }}</p>
                </div>
            @endforelse
        </div>
    @endif

    <!-- Tab 4: Notes (Qaydlar) -->
    @if ($activeTab === 'notes')
        <div class="space-y-3.5">
            @forelse ($notes as $note)
                <div class="p-4 rounded-panel bg-ink-900 border border-ink-border space-y-2.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-amber-400 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20 M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            <span>{{ $note->book?->title ?? __('site.profile.book_fallback') }}</span>
                        </span>
                        <span class="text-mist font-mono text-[11px]">{{ $note->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    @if($note->selected_text)
                        <blockquote class="p-3 rounded-panel bg-ink-950/60 border-l-2 border-amber-400 text-xs italic font-serif text-paper">
                            «{{ $note->selected_text }}»
                        </blockquote>
                    @endif
                    <p class="text-xs text-mist leading-relaxed font-sans">
                        {{ $note->note_text }}
                    </p>
                </div>
            @empty
                <div class="py-12 text-center p-8 rounded-panel bg-ink-900 border border-ink-border">
                    <div class="w-10 h-10 mx-auto rounded-btn bg-ink-800 border border-ink-border flex items-center justify-center text-amber-400 mb-2">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    </div>
                    <p class="text-xs text-mist font-mono">{{ __('site.profile.empty_notes') }}</p>
                </div>
            @endforelse
        </div>
    @endif

</div>
