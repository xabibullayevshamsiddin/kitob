@php
    $isTopFive = ($userRank !== null && $userRank >= 1 && $userRank <= 5);
@endphp

<div class="max-w-6xl mx-auto space-y-6 pb-16">

    <!-- Profile Header Card with Custom Visual Banner -->
    <div class="rounded-2xl sm:rounded-3xl bg-ink-900 border relative overflow-hidden transition-all duration-500 shadow-xl {{ $profileVisual['card_border'] }} {{ $profileVisual['card_glow'] }}">
        
        <!-- Atmospheric Cover Banner (Sleek h-28 sm:h-36 md:h-40) -->
        <div class="relative w-full h-28 sm:h-36 md:h-40 overflow-hidden select-none transition-all duration-500 {{ $profileVisual['banner_gradient'] }}">
            <!-- Ambient Radial Aura Glow with Smooth Breathing Animation -->
            <div class="absolute inset-0 pointer-events-none transition-opacity duration-700 animate-ambient-breathe"
                 style="background: {{ $profileVisual['banner_ambient'] }};"></div>

            <!-- Dynamic Animated Micro-Patterns -->
            @if($profileVisual['pattern'] === 'cyber')
                <!-- Cyberpunk Refined Micro-Dot Matrix & Laser Horizon Sweep -->
                <div class="absolute inset-0 opacity-20 pointer-events-none"
                     style="background-image: radial-gradient(rgba(244,63,94,0.7) 1px, transparent 1px); background-size: 20px 20px;"></div>
                <!-- Dynamic laser scan beam -->
                <div class="absolute inset-x-0 bottom-5 h-[2px] bg-gradient-to-r from-transparent via-rose-500 to-transparent pointer-events-none animate-laser-scan shadow-[0_0_10px_rgba(244,63,94,0.7)]"></div>
            @elseif($profileVisual['pattern'] === 'aurora')
                <!-- Aurora Smooth Ambient Waves with Undulating Drift Animation -->
                <div class="absolute inset-0 opacity-35 mix-blend-screen pointer-events-none animate-aurora-drift">
                    <svg class="w-full h-full object-cover" viewBox="0 0 1000 200" preserveAspectRatio="none" fill="none">
                        <path d="M0,120 Q300,40 600,140 T1000,80 L1000,0 L0,0 Z" fill="rgba(45,212,191,0.35)"/>
                        <path d="M0,70 Q400,150 800,50 T1000,110 L1000,0 L0,0 Z" fill="rgba(16,185,129,0.25)"/>
                    </svg>
                </div>
            @elseif($profileVisual['pattern'] === 'gold' || $profileVisual['pattern'] === 'rank-gold')
                <!-- Royal Gold Soft Shimmer with Twinkling Stardust Animation -->
                <div class="absolute inset-0 opacity-30 pointer-events-none animate-star-twinkle"
                     style="background-image: radial-gradient(#FCD34D 1.2px, transparent 1.2px); background-size: 28px 28px;"></div>
                <div class="absolute inset-0 pointer-events-none overflow-hidden">
                    <div class="w-1/2 h-full bg-gradient-to-r from-transparent via-amber-400/20 to-transparent animate-laser-scan"></div>
                </div>
            @elseif($profileVisual['pattern'] === 'rank-silver')
                <!-- Silver Delicate Shimmer Animation -->
                <div class="absolute inset-0 opacity-25 pointer-events-none animate-star-twinkle"
                     style="background: radial-gradient(circle at 80% 30%, rgba(226,232,240,0.35) 0%, transparent 60%);"></div>
            @elseif($profileVisual['pattern'] === 'rank-bronze')
                <!-- Bronze Warm Hearth Aura -->
                <div class="absolute inset-0 opacity-25 pointer-events-none animate-ambient-breathe"
                     style="background: radial-gradient(circle at 80% 30%, rgba(217,119,6,0.35) 0%, transparent 60%);"></div>
            @elseif($profileVisual['pattern'] === 'rank-indigo')
                <!-- Indigo Star Dots with Twinkling Animation -->
                <div class="absolute inset-0 opacity-30 pointer-events-none animate-star-twinkle"
                     style="background-image: radial-gradient(#818CF8 1.2px, transparent 1.2px); background-size: 24px 24px;"></div>
            @elseif($profileVisual['pattern'] === 'emerald')
                <!-- Emerald Crystal Dot Sparkles with Twinkle Animation -->
                <div class="absolute inset-0 opacity-30 pointer-events-none animate-star-twinkle"
                     style="background-image: radial-gradient(#10B981 1.2px, transparent 1.2px); background-size: 24px 24px;"></div>
            @elseif($profileVisual['pattern'] === 'cosmic')
                <!-- Cosmic Stardust with Twinkle Animation -->
                <div class="absolute inset-0 opacity-35 pointer-events-none animate-star-twinkle"
                     style="background-image: radial-gradient(#E9D5FF 1.2px, transparent 1.2px); background-size: 32px 32px;"></div>
            @elseif($profileVisual['pattern'] === 'vintage')
                <!-- Vintage Quiet Warmth with Soft Breathing Ambient -->
                <div class="absolute inset-0 opacity-20 pointer-events-none animate-ambient-breathe"
                     style="background: radial-gradient(circle at 80% 30%, rgba(217,119,6,0.3) 0%, transparent 65%);"></div>
            @elseif($profileVisual['pattern'] === 'sunset')
                <!-- Sunset Twilight Dusk with Ambient Drift -->
                <div class="absolute inset-0 opacity-30 mix-blend-screen pointer-events-none animate-aurora-drift">
                    <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 1000 150" fill="none">
                        <path d="M0,60 Q300,120 600,60 T1000,90 L1000,150 L0,150 Z" fill="rgba(249,115,22,0.35)"/>
                    </svg>
                </div>
            @endif

            <!-- Bottom Gradient Fade into Card Background -->
            <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-ink-900 to-transparent"></div>

            <!-- Top Right Theme Badge Capsule with Floating Micro-Animation -->
            @if($profileVisual['badge_text'])
                <div class="absolute top-3 right-3 sm:top-4 sm:right-4 z-10 flex items-center animate-badge-float">
                    <span class="{{ $profileVisual['badge_class'] }} flex items-center gap-1.5 shadow-sm">
                        <span>{{ $profileVisual['badge_text'] }}</span>
                    </span>
                </div>
            @endif
        </div>

        <!-- Main Profile Info Body (Single clean avatar ring without double borders) -->
        <div class="px-5 pb-6 sm:px-8 sm:pb-7 pt-0 relative z-10 -mt-10 sm:-mt-14">
            <div class="flex flex-col sm:flex-row items-center sm:items-end gap-5 text-center sm:text-left">
                <!-- Avatar (Single ring, subtle breathing pulse) -->
                <div class="relative shrink-0 rounded-2xl animate-ring-pulse">
                    <x-ui.avatar :user="$user" size="3xl" shape="rounded-2xl" :showRank="false" />
                </div>

                <!-- Profile Info -->
                <div class="flex-1 min-w-0 sm:pb-0.5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center justify-center sm:justify-start gap-2.5 flex-wrap">
                                @php
                                    $equippedTitle = $user->equipped_title;
                                    $equippedNameColor = $user->equipped_name_color;
                                @endphp
                                <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold tracking-tight font-serif break-words {{ $equippedNameColor ? '' : 'text-paper' }}"
                                    @if($equippedNameColor) style="color: {{ $equippedNameColor }}; text-shadow: 0 0 20px {{ $equippedNameColor }}40;" @endif>
                                    {{ $user->name }}
                                </h1>

                                @if($equippedTitle)
                                    @php
                                        $titleColor = $equippedTitle->safeColor('badge_color', '#FBBF24');
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold shadow-md select-none transition-transform hover:scale-105"
                                          style="background-color: {{ $titleColor }}18; color: {{ $titleColor }}; border: 1.5px solid {{ $titleColor }}60;">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        <span>{{ $equippedTitle->name_text }}</span>
                                    </span>
                                @endif

                                @if($isTopFive)
                                    <x-ui.rank-badge :rank="$userRank" size="md" />
                                @endif
                                @if($user->role === 'admin' || $user->hasRole('admin'))
                                    <span class="px-2.5 py-0.5 rounded-full bg-rose-500/10 border border-rose-500/25 text-rose-700 dark:text-rose-300 text-[10px] font-mono uppercase tracking-wider font-semibold">Admin</span>
                                @elseif($user->role === 'teacher' || $user->hasRole('teacher'))
                                    <span class="px-2.5 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/25 text-amber-800 dark:text-amber-300 text-[10px] font-mono uppercase tracking-wider font-semibold">O‘qituvchi</span>
                                @endif

                                {{-- Level Badge --}}
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-500/15 border border-amber-500/40 text-amber-900 dark:text-amber-300 text-[11px] font-mono font-bold flex items-center gap-1 select-none shadow-sm" title="{{ $user->level_title }}">
                                    <span>{{ $user->level_icon }}</span>
                                    <span>Lv.{{ $user->level }}</span>
                                    <span class="hidden sm:inline font-sans font-bold text-[10px] text-amber-800 dark:text-amber-200">({{ $user->level_title }})</span>
                                </span>
                            </div>
                            <div class="flex items-center justify-center sm:justify-start gap-2 mt-1">
                                <p class="text-xs text-mist font-mono">{{ '@' . $user->username }}</p>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="self-center sm:self-auto shrink-0">
                            @if ($isOwner)
                                <a href="{{ route('settings') }}" class="px-3.5 py-1.5 rounded-full text-xs font-mono font-medium bg-ink-800/80 hover:bg-ink-700/80 hover:scale-105 active:scale-95 text-paper border border-ink-border transition-all flex items-center gap-1.5 shadow-sm">
                                    <svg class="w-3.5 h-3.5 text-mist" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                    <span>{{ __('site.profile.settings') }}</span>
                                </a>
                            @else
                                <button wire:click="toggleFollow" class="text-xs py-1.5 px-4 rounded-full font-mono font-bold hover:scale-105 active:scale-95 transition-all {{ $isFollowing ? 'bg-ink-800 hover:bg-ink-700/60 border border-ink-border text-paper' : 'ks-btn-primary' }}">
                                    {{ $isFollowing ? '✓ '.__('site.profile.following') : '+ '.__('site.profile.follow') }}
                                </button>
                            @endif
                        </div>
                    </div>

                    @if ($user->bio)
                        <p class="text-xs sm:text-sm text-mist/90 max-w-2xl leading-relaxed font-sans break-words whitespace-pre-line pt-2">
                            {{ $user->bio }}
                        </p>
                    @endif

                    <!-- Meta Chips: Clean, Modern Inline Items -->
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-4 gap-y-1.5 pt-2.5 text-xs text-mist/80 font-sans">
                        @if($user->profile && $user->profile->reading_place)
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-mist/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <span>{{ ucfirst($user->profile->reading_place) }}</span>
                            </span>
                        @endif

                        @if($user->profile && $user->profile->reading_goal)
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-mist/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                                <span>{{ ucfirst($user->profile->reading_goal) }}</span>
                            </span>
                        @endif

                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-mist/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <span>{{ __('site.profile.joined_date', ['date' => $user->created_at->format('d.m.Y')]) }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Bento Grid in Header (Interactive Lift & Glow Animation) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-5 mt-5 border-t border-ink-border/60">
                <div class="p-3.5 sm:p-4 rounded-xl bg-slate-50/90 dark:bg-ink-950/40 border border-slate-200 dark:border-ink-border/60 hover:border-slate-300 dark:hover:border-ink-border hover:-translate-y-0.5 hover:shadow-lg transition-all duration-300 group flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono uppercase tracking-[0.15em] text-mist flex items-center gap-1">
                            <span class="ks-flame is-lit inline-block scale-75 group-hover:scale-100 transition-transform">
                                <svg class="w-3.5 h-3.5 text-amber-500 dark:text-amber-400 fill-amber-500 dark:fill-amber-400" viewBox="0 0 24 24"><path d="M12 2c0 4-4 6-4 10a6 6 0 0 0 12 0c0-4-4-6-4-10z"/></svg>
                            </span>
                            <span>{{ __('site.profile.streak') }}</span>
                        </span>
                        @if(($user->streak?->freeze_count ?? 0) > 0)
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-full bg-sky-500/10 text-sky-700 dark:text-sky-400 border border-sky-500/25" title="{{ __('site.profile.streak_freeze_active') }}">
                                🧊 {{ $user->streak->freeze_count }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xl sm:text-2xl font-bold font-mono text-amber-600 dark:text-amber-400 mt-1.5 group-hover:text-amber-500 transition-colors">
                        {{ $stats['current_streak'] }} <span class="text-xs text-mist font-normal font-sans">{{ __('site.profile.days') }}</span>
                    </p>
                </div>

                <div class="p-3.5 sm:p-4 rounded-xl bg-slate-50/90 dark:bg-ink-950/40 border border-slate-200 dark:border-ink-border/60 hover:border-slate-300 dark:hover:border-ink-border hover:-translate-y-0.5 hover:shadow-lg transition-all duration-300 group flex flex-col justify-between">
                    <span class="text-[10px] font-mono uppercase tracking-[0.15em] text-mist flex items-center gap-1">
                        <span class="group-hover:scale-110 transition-transform">⭐</span>
                        <span>{{ __('site.profile.total_points') }}</span>
                    </span>
                    <p class="text-xl sm:text-2xl font-bold font-mono text-paper mt-1.5">
                        {{ number_format($stats['total_points']) }}
                    </p>
                </div>

                <div class="p-3.5 sm:p-4 rounded-xl bg-slate-50/90 dark:bg-ink-950/40 border border-slate-200 dark:border-ink-border/60 hover:border-slate-300 dark:hover:border-ink-border hover:-translate-y-0.5 hover:shadow-lg transition-all duration-300 group flex flex-col justify-between">
                    <span class="text-[10px] font-mono uppercase tracking-[0.15em] text-mist flex items-center gap-1">
                        <span class="group-hover:scale-110 transition-transform">🪙</span>
                        <span>{{ __('site.profile.coin_balance') }}</span>
                    </span>
                    <p class="text-xl sm:text-2xl font-bold font-mono text-amber-600 dark:text-amber-300 mt-1.5">
                        {{ number_format($stats['coin_balance']) }}
                    </p>
                </div>

                <div class="p-3.5 sm:p-4 rounded-xl bg-slate-50/90 dark:bg-ink-950/40 border border-slate-200 dark:border-ink-border/60 hover:border-slate-300 dark:hover:border-ink-border hover:-translate-y-0.5 hover:shadow-lg transition-all duration-300 group flex flex-col justify-between">
                    <span class="text-[10px] font-mono uppercase tracking-[0.15em] text-mist flex items-center gap-1">
                        <span class="group-hover:scale-110 transition-transform">⏱️</span>
                        <span>{{ __('site.profile.reading_time') }}</span>
                    </span>
                    <p class="text-xl sm:text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-1.5">
                        {{ number_format($stats['total_minutes']) }} <span class="text-xs text-mist font-normal font-sans">{{ __('site.profile.minutes_short') }}</span>
                    </p>
                </div>
            </div>

            {{-- Level Progress Banner --}}
            @php
                $lvlProg = $user->level_progress;
            @endphp
            <div class="mt-4 p-4 rounded-xl bg-gradient-to-r from-amber-500/10 via-ink-950/40 to-amber-500/5 border border-amber-500/25 space-y-2.5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">{{ $user->level_icon }}</span>
                        <div>
                            <div class="font-serif font-bold text-paper text-sm">
                                Lv.{{ $user->level }} — {{ $user->level_title }}
                            </div>
                            <div class="text-[11px] text-stone-500 dark:text-mist font-mono">
                                @if($lvlProg['next_level'])
                                    Keyingi daraja: <strong class="text-amber-400">Lv.{{ $lvlProg['next_level'] }} {{ $lvlProg['next_info']['title'] }}</strong> (yana {{ number_format($lvlProg['points_needed']) }} ball)
                                @else
                                    <span class="text-amber-400 font-bold">Maksimal darajaga erishildi! 👑</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($lvlProg['next_level'] && $lvlProg['next_info']['coin_bonus'] > 0)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 font-mono text-[11px] font-bold self-start sm:self-auto border border-amber-500/30">
                            <span>Bonus:</span>
                            <span>+{{ $lvlProg['next_info']['coin_bonus'] }} tanga 🪙</span>
                        </span>
                    @endif
                </div>

                <div class="space-y-1">
                    <div class="w-full h-2 rounded-full bg-ink-900 border border-ink-border overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-amber-300 transition-all duration-500"
                             style="width: {{ $lvlProg['progress_percent'] }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] font-mono text-mist">
                        <span>{{ number_format($user->total_points) }} ball</span>
                        <span>{{ $lvlProg['progress_percent'] }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-ink-border pb-2 overflow-x-auto no-scrollbar py-0.5 -mx-1 px-1 sm:mx-0 sm:px-0">
        <button wire:click="setTab('overview')" class="px-3.5 py-1.5 rounded-btn text-xs font-mono font-medium transition-all shrink-0 {{ $activeTab === 'overview' ? 'bg-cyan-500/15 dark:bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 border border-cyan-500/35 font-bold shadow-sm' : 'text-mist hover:text-paper hover:bg-slate-100 dark:hover:bg-ink-900 border border-transparent' }}">
            {{ __('site.profile.tab_overview') }}
        </button>
        <button wire:click="setTab('books')" class="px-3.5 py-1.5 rounded-btn text-xs font-mono font-medium transition-all shrink-0 {{ $activeTab === 'books' ? 'bg-cyan-500/15 dark:bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 border border-cyan-500/35 font-bold shadow-sm' : 'text-mist hover:text-paper hover:bg-slate-100 dark:hover:bg-ink-900 border border-transparent' }}">
            {{ __('site.profile.tab_books') }} ({{ count($readingProgresses) }})
        </button>
        <button wire:click="setTab('saved')" class="px-3.5 py-1.5 rounded-btn text-xs font-mono font-medium transition-all shrink-0 {{ $activeTab === 'saved' ? 'bg-cyan-500/15 dark:bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 border border-cyan-500/35 font-bold shadow-sm' : 'text-mist hover:text-paper hover:bg-slate-100 dark:hover:bg-ink-900 border border-transparent' }}">
            {{ __('site.profile.tab_saved') }} ({{ count($savedBooks) }})
        </button>
        <button wire:click="setTab('badges')" class="px-3.5 py-1.5 rounded-btn text-xs font-mono font-medium transition-all shrink-0 {{ $activeTab === 'badges' ? 'bg-cyan-500/15 dark:bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 border border-cyan-500/35 font-bold shadow-sm' : 'text-mist hover:text-paper hover:bg-slate-100 dark:hover:bg-ink-900 border border-transparent' }}">
            {{ __('site.profile.tab_badges') }} ({{ count($badges) }})
        </button>
        @if($isOwner)
            <button wire:click="setTab('notes')" class="px-3.5 py-1.5 rounded-btn text-xs font-mono font-medium transition-all shrink-0 {{ $activeTab === 'notes' ? 'bg-cyan-500/15 dark:bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 border border-cyan-500/35 font-bold shadow-sm' : 'text-mist hover:text-paper hover:bg-slate-100 dark:hover:bg-ink-900 border border-transparent' }}">
                {{ __('site.profile.tab_notes') }} ({{ count($notes) }})
            </button>
            <button wire:click="setTab('shop')" class="px-3.5 py-1.5 rounded-btn text-xs font-mono font-medium transition-all shrink-0 flex items-center gap-1.5 {{ $activeTab === 'shop' ? 'bg-cyan-500/15 dark:bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 border border-cyan-500/35 font-bold shadow-sm' : 'text-mist hover:text-paper hover:bg-slate-100 dark:hover:bg-ink-900 border border-transparent' }}" title="{{ __('site.profile.tab_shop') }}">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                <span>{{ __('shop.tabs.inventory') }}</span>
                <span class="sr-only">{{ __('site.profile.tab_shop') }}</span>
                @if(isset($userItems) && count($userItems) > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-cyan-500/20 text-cyan-800 dark:text-cyan-300 font-mono">{{ count($userItems) }}</span>
                @endif
            </button>
        @endif
    </div>

    <!-- Tab 1: Overview & Heatmap -->
    @if ($activeTab === 'overview')
        <div class="space-y-5">
            @if(isset($dailyReview) && $dailyReview)
                <!-- Spaced Repetition (Daily Review / Kunlik Takrorlash) -->
                <div class="p-5 sm:p-6 rounded-panel bg-gradient-to-br from-violet-500/10 via-ink-900 to-ink-900 border border-violet-500/30 space-y-3 relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🧠</span>
                            <span class="text-xs font-mono font-bold uppercase tracking-wider text-violet-400">
                                {{ __('site.daily_review.title') }}
                            </span>
                            <span class="px-2 py-0.5 rounded-badge bg-violet-500/20 text-violet-300 text-[10px] font-mono">
                                {{ $dailyReview['days_ago'] }}
                            </span>
                        </div>
                        @if(!empty($dailyReview['book_slug']))
                            <a href="{{ route('books.show', $dailyReview['book_slug']) }}" class="text-xs font-mono text-violet-400 hover:text-violet-300 flex items-center gap-1 transition-colors">
                                <span>{{ $dailyReview['book_title'] }}</span>
                                <span>→</span>
                            </a>
                        @endif
                    </div>

                    <blockquote class="italic text-paper text-sm sm:text-base font-serif border-l-2 border-violet-500/50 pl-3.5 py-1">
                        "{{ $dailyReview['text'] }}"
                    </blockquote>

                    @if(!empty($dailyReview['note']))
                        <div class="text-xs text-mist font-mono pl-3.5">
                            <span class="text-violet-400 font-semibold">{{ __('site.daily_review.your_note') }}:</span> {{ $dailyReview['note'] }}
                        </div>
                    @elseif(!empty($dailyReview['author']))
                        <div class="text-xs text-mist font-mono pl-3.5">
                            — {{ $dailyReview['author'] }}
                        </div>
                    @endif
                </div>
            @endif

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
                                $colorClass = 'bg-slate-200/90 dark:bg-ink-800';
                                if ($minutes > 0 && $minutes <= 15) $colorClass = 'bg-emerald-600/50';
                                elseif ($minutes > 15 && $minutes <= 30) $colorClass = 'bg-emerald-500/80';
                                elseif ($minutes > 30) $colorClass = 'bg-emerald-500';
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
                        <span class="w-3 h-3 rounded-sm bg-slate-200/90 dark:bg-ink-800"></span>
                        <span class="w-3 h-3 rounded-sm bg-emerald-600/50"></span>
                        <span class="w-3 h-3 rounded-sm bg-emerald-500/80"></span>
                        <span class="w-3 h-3 rounded-sm bg-emerald-500"></span>
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
                    <span class="text-xl font-bold font-mono text-amber-600 dark:text-amber-400">{{ $stats['quizzes_passed'] }} <small class="text-xs text-mist font-sans">{{ __('site.profile.count_suffix') }}</small></span>
                </div>
                <div class="p-4 rounded-panel bg-ink-900 border border-ink-border space-y-1">
                    <span class="text-[11px] text-mist font-mono uppercase tracking-wider block">{{ __('site.profile.groups') }}</span>
                    <span class="text-xl font-bold font-mono text-paper">{{ $stats['groups_count'] }} <small class="text-xs text-mist font-sans">{{ __('site.profile.count_suffix') }}</small></span>
                </div>
                <div class="p-4 rounded-panel bg-ink-900 border border-ink-border space-y-1">
                    <span class="text-[11px] text-mist font-mono uppercase tracking-wider block">{{ __('site.profile.chat_messages') }}</span>
                    <span class="text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400">{{ $stats['messages_count'] }} <small class="text-xs text-mist font-sans">{{ __('site.profile.count_suffix') }}</small></span>
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
                                    <span>{{ $progress->percent_complete >= 100 ? __('site.books.completed') : __('site.books.reading') }}</span>
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

    <!-- Tab: Saved Books (Saqlangan kitoblar) -->
    @if ($activeTab === 'saved')
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($savedBooks as $savedBook)
                <div class="p-3.5 rounded-panel bg-ink-900 border border-ink-border flex gap-3.5 hover:border-amber-500/30 transition-all group">
                    <a href="{{ route('books.show', $savedBook->slug) }}" class="shrink-0 block">
                        <img src="{{ $savedBook->cover_url }}" alt="{{ $savedBook->title }}" class="w-16 h-24 rounded object-cover border border-ink-border group-hover:scale-105 transition-transform">
                    </a>
                    <div class="flex-1 min-w-0 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-1">
                                <span class="text-[10px] font-mono uppercase tracking-wider text-amber-400 truncate">{{ $savedBook->genre }}</span>
                                @if($isOwner)
                                    <button type="button" 
                                            wire:click="unsaveBook({{ $savedBook->id }})"
                                            class="text-mist hover:text-rose-400 p-1 -mr-1 -mt-1 transition-colors"
                                            title="{{ __('site.saved_books.remove_from_saved') }}">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                @endif
                            </div>
                            <a href="{{ route('books.show', $savedBook->slug) }}" class="block">
                                <h4 class="text-xs sm:text-sm font-semibold text-paper hover:text-amber-400 transition-colors truncate">{{ $savedBook->title }}</h4>
                            </a>
                            <p class="text-[11px] text-mist truncate font-sans">{{ $savedBook->author }}</p>
                        </div>
                        <div class="pt-2 flex items-center justify-between border-t border-ink-border/50">
                            <div class="flex items-center gap-2 text-[10px] text-mist font-mono">
                                <span>{{ $savedBook->chapters_count ?? 0 }} {{ __('site.books.chapters_count_suffix') }}</span>
                                @if(($savedBook->audios_count ?? 0) > 0)
                                    <span>• 🎵 Audio</span>
                                @endif
                            </div>
                            <a href="{{ route('books.show', $savedBook->slug) }}" class="text-[11px] text-amber-400 hover:text-amber-300 font-semibold font-mono flex items-center gap-0.5">
                                <span>{{ __('site.books.read_cta') }}</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center p-8 rounded-panel bg-ink-900 border border-ink-border space-y-3">
                    <div class="w-12 h-12 mx-auto rounded-btn bg-amber-500/10 border border-amber-500/25 flex items-center justify-center text-amber-400">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-paper font-serif">{{ __('site.profile.empty_saved') }}</h3>
                    <p class="text-xs text-mist font-sans max-w-sm mx-auto">
                        @if($isOwner)
                            {{ __('site.saved_books.owner_empty_desc') }}
                        @else
                            {{ __('site.saved_books.other_empty_desc') }}
                        @endif
                    </p>
                    @if($isOwner)
                        <a href="{{ route('books.catalog') }}" class="ks-btn-primary inline-flex items-center gap-2 text-xs py-2 px-4 mt-2">
                            <span>{{ __('site.saved_books.go_to_catalog') }}</span>
                            <span>→</span>
                        </a>
                    @endif
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
    @if ($activeTab === 'notes' && $isOwner)
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
                        <blockquote class="p-3 rounded-panel bg-ink-950/60 border-l-2 text-xs italic font-serif text-paper"
                                    :class="{
                                        'border-amber-400': '{{ $note->color ?? 'amber' }}' === 'amber',
                                        'border-emerald-400': '{{ $note->color ?? 'amber' }}' === 'emerald',
                                        'border-sky-400': '{{ $note->color ?? 'amber' }}' === 'sky',
                                        'border-rose-400': '{{ $note->color ?? 'amber' }}' === 'rose',
                                        'border-purple-400': '{{ $note->color ?? 'amber' }}' === 'purple',
                                    }">
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

    <!-- Tab 5: Tangalar do'koni (Coin Shop) -->
    @if ($activeTab === 'shop' && $isOwner)
        @php
            $profile = $user->profile;
            $currentPref = $profile?->chat_style_preference ?? 'shop';
            $activeTheme = $profile?->profile_theme ?? $profile?->avatar_frame ?? $profile?->chat_bubble_theme;
            $previewStyle = \App\Services\Gamification\ChatStyleService::resolveStyle($user, $userRank, true);
            $visualCatalog = \App\Services\Gamification\ProfileVisualService::THEMES;
        @endphp

        <div class="space-y-6">
            <!-- Header Banner -->
            <div class="p-5 sm:p-6 rounded-panel bg-ink-900 border border-ink-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-paper font-serif flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                        <span>{{ __('shop.tabs.inventory') }}</span>
                    </h3>
                    <p class="text-xs text-mist font-sans mt-1">Siz xarid qilgan profil mavzulari, avatar auralari va unvonlar to‘plami.</p>
                </div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <div class="flex items-center gap-2 px-3.5 py-2 rounded-btn bg-slate-100 dark:bg-ink-800 border border-slate-200 dark:border-ink-border text-amber-700 dark:text-amber-400 font-mono font-bold text-xs shrink-0">
                        <svg class="w-4 h-4 text-gilt" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/></svg>
                        <span>{{ number_format($user->coin_balance) }} tanga</span>
                    </div>
                    <a href="{{ route('shop.index') }}" class="ks-btn-gold inline-flex items-center gap-1.5 !px-4 !py-2 text-xs font-mono font-bold shadow-md">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                        <span>{{ __('shop.open_shop') ?? 'Do‘konga o‘tish' }}</span>
                        <span>→</span>
                    </a>
                </div>
            </div>

            <!-- Chat & Profile Visual Style Preference Switcher -->
            <div class="p-5 sm:p-6 rounded-panel bg-white dark:bg-ink-900/90 border border-slate-200 dark:border-ink-border space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h4 class="text-sm font-bold text-paper flex items-center gap-2">
                            <span>🎨</span>
                            <span>{{ __('site.profile.visual_appearance_title') }}</span>
                        </h4>
                        <p class="text-xs text-mist mt-0.5">{{ __('site.profile.visual_appearance_subtitle') }}</p>
                    </div>

                    @if($activeTheme)
                        <button type="button" wire:click="unequipShopTheme"
                                class="text-[11px] font-mono text-mist hover:text-rose-500 transition underline self-start sm:self-auto">
                            {{ __('site.profile.unequip_theme') }}
                        </button>
                    @endif
                </div>

                <!-- 3 Options Buttons -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                    <!-- Option 1: Shop Style -->
                    <button type="button"
                            wire:click="setChatStylePreference('shop')"
                            class="p-3.5 rounded-btn border text-left transition-all relative {{ $currentPref === 'shop' ? 'bg-cyan-500/10 border-cyan-500/50 shadow-sm ring-1 ring-cyan-500/40' : 'bg-slate-50 dark:bg-ink-950/60 border-slate-200 dark:border-ink-border hover:border-slate-300 dark:hover:border-ink-border/80 opacity-90 hover:opacity-100' }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-paper flex items-center gap-1.5">
                                <span>🛍️</span>
                                <span>{{ __('site.profile.opt_shop_theme') }}</span>
                            </span>
                            @if($currentPref === 'shop')
                                <span class="w-2 h-2 rounded-full bg-cyan-500 dark:bg-amber-400 animate-pulse"></span>
                            @endif
                        </div>
                        <p class="text-[11px] text-mist font-sans mt-1 leading-snug">
                            {{ __('site.profile.opt_shop_desc') }}
                        </p>
                        <div class="text-[10px] font-mono text-cyan-700 dark:text-amber-400/90 mt-2 font-semibold">
                            {{ __('site.profile.active_theme', ['theme' => $activeTheme ? ($visualCatalog[$activeTheme]['name'] ?? ucfirst($activeTheme)) : __('site.profile.not_selected')]) }}
                        </div>
                    </button>

                    <!-- Option 2: Leaderboard Rank Style -->
                    <button type="button"
                            wire:click="setChatStylePreference('rank')"
                            class="p-3.5 rounded-btn border text-left transition-all relative {{ $currentPref === 'rank' ? 'bg-cyan-500/10 border-cyan-500/50 shadow-sm ring-1 ring-cyan-500/40' : 'bg-slate-50 dark:bg-ink-950/60 border-slate-200 dark:border-ink-border hover:border-slate-300 dark:hover:border-ink-border/80 opacity-90 hover:opacity-100' }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-paper flex items-center gap-1.5">
                                <span>🏆</span>
                                <span>{{ __('site.profile.opt_rank_theme') }}</span>
                            </span>
                            @if($currentPref === 'rank')
                                <span class="w-2 h-2 rounded-full bg-cyan-500 dark:bg-amber-400 animate-pulse"></span>
                            @endif
                        </div>
                        <p class="text-[11px] text-mist font-sans mt-1 leading-snug">
                            {{ __('site.profile.opt_rank_desc') }}
                        </p>
                        <div class="text-[10px] font-mono text-cyan-700 dark:text-amber-400/90 mt-2 font-semibold">
                            {{ $userRank ? __('site.profile.your_rank', ['rank' => "#{$userRank}"]) : __('site.profile.insufficient_points') }}
                        </div>
                    </button>

                    <!-- Option 3: Default Standard Style -->
                    <button type="button"
                            wire:click="setChatStylePreference('default')"
                            class="p-3.5 rounded-btn border text-left transition-all relative {{ $currentPref === 'default' ? 'bg-cyan-500/10 border-cyan-500/50 shadow-sm ring-1 ring-cyan-500/40' : 'bg-slate-50 dark:bg-ink-950/60 border-slate-200 dark:border-ink-border hover:border-slate-300 dark:hover:border-ink-border/80 opacity-90 hover:opacity-100' }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-paper flex items-center gap-1.5">
                                <span>👤</span>
                                <span>{{ __('site.profile.opt_default_theme') }}</span>
                            </span>
                            @if($currentPref === 'default')
                                <span class="w-2 h-2 rounded-full bg-cyan-500 dark:bg-amber-400 animate-pulse"></span>
                            @endif
                        </div>
                        <p class="text-[11px] text-mist font-sans mt-1 leading-snug">
                            {{ __('site.profile.opt_default_desc') }}
                        </p>
                        <div class="text-[10px] font-mono text-mist mt-2">
                            {{ __('site.profile.default_quiet_state') }}
                        </div>
                    </button>
                </div>

                <!-- Live Previews Grid (Profile Cover + Chat Message) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                    <!-- Profile Header Live Preview -->
                    <div class="p-3.5 rounded-card bg-slate-50 dark:bg-ink-950/70 border border-slate-200 dark:border-ink-border/60 flex flex-col justify-between">
                        <span class="text-[10px] font-mono text-mist uppercase tracking-wider block mb-2">{{ __('site.profile.preview_header') }}</span>
                        <div class="rounded-btn overflow-hidden border relative p-3 flex flex-col justify-between min-h-[96px] {{ $profileVisual['card_border'] }} {{ $profileVisual['banner_gradient'] }}">
                            <div class="absolute inset-0 pointer-events-none animate-ambient-breathe" style="background: {{ $profileVisual['banner_ambient'] }};"></div>
                            <div class="relative z-10 flex items-center justify-between">
                                <span class="text-[10px] font-mono text-paper font-bold uppercase tracking-wider">{{ __('site.profile.preview_cover') }}</span>
                                @if($profileVisual['badge_text'])
                                    <span class="{{ $profileVisual['badge_class'] }} text-[9px] px-2 py-0.5">{{ $profileVisual['badge_text'] }}</span>
                                @endif
                            </div>
                            <div class="relative z-10 flex items-center gap-2.5 mt-2">
                                <div class="w-8 h-8 rounded-full border border-paper/40 bg-white dark:bg-ink-950 flex items-center justify-center text-xs font-bold font-serif {{ $profileVisual['avatar_ring'] }}">
                                    {{ mb_substr($user->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-paper truncate">{{ $user->name }}</div>
                                    <div class="text-[10px] font-mono text-mist">{{ '@' . $user->username }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Message Live Preview -->
                    <div class="p-3.5 rounded-card bg-slate-50 dark:bg-ink-950/70 border border-slate-200 dark:border-ink-border/60 flex flex-col justify-between">
                        <span class="text-[10px] font-mono text-mist uppercase tracking-wider block mb-2">{{ __('site.profile.preview_chat') }}</span>
                        <div class="flex items-start gap-2.5">
                            <x-ui.avatar :user="$user" size="sm" :rank="$userRank" />
                            <div class="flex flex-col items-start min-w-0">
                                <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                                    <span class="text-xs font-semibold {{ $previewStyle['name_class'] }}">{{ $user->name }}</span>
                                    @if($previewStyle['badge_text'])
                                        <span class="{{ $previewStyle['badge_class'] }}">{{ $previewStyle['badge_text'] }}</span>
                                    @endif
                                    <span class="text-[10px] font-mono text-mist">{{ __('site.profile.preview_now') }}</span>
                                </div>
                                <div class="px-3 py-1.5 rounded-card text-xs leading-relaxed border {{ $previewStyle['bubble_class'] }}">
                                    {{ __('site.profile.preview_message') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Items & Consumables Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold text-paper font-serif flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                            <span>Mavjud buyumlarim</span>
                        </h4>
                        <p class="text-xs text-mist mt-0.5">Xarid qilingan buyumlarni shu yerdan kiyishingiz yoki almashtirishingiz mumkin.</p>
                    </div>
                    <a href="{{ route('shop.index') }}" class="text-xs font-mono text-amber-400 hover:text-amber-300 transition flex items-center gap-1">
                        <span>Yangi buyumlar olish</span>
                        <span>→</span>
                    </a>
                </div>

                @if($userItems->isEmpty() && ($user->streak?->freeze_count ?? 0) === 0)
                    <div class="p-8 rounded-panel bg-ink-900 border border-ink-border text-center space-y-3">
                        <div class="w-12 h-12 mx-auto rounded-full bg-ink-800 border border-ink-border flex items-center justify-center text-mist">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-paper">{{ __('shop.inventory_empty') }}</h4>
                        <p class="text-xs text-mist max-w-md mx-auto">{{ __('shop.inventory_empty_sub') }}</p>
                        <div class="pt-2">
                            <a href="{{ route('shop.index') }}" class="ks-btn-gold inline-flex items-center gap-1.5 !px-4 !py-2 text-xs font-mono font-bold">
                                <span>{{ __('shop.open_shop') }}</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        {{-- Streak Freeze Consumable Card if present --}}
                        @if(($user->streak?->freeze_count ?? 0) > 0)
                            <div class="p-4 rounded-panel bg-ink-900 border border-sky-500/30 flex flex-col justify-between space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-btn bg-sky-500/10 border border-sky-500/25 flex items-center justify-center text-sky-400">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                        </div>
                                        <div>
                                            <h5 class="text-xs font-bold text-paper">{{ __('shop.items.streak_freeze_name') }}</h5>
                                            <span class="text-[10px] font-mono text-mist">{{ __('shop.categories.consumable') }}</span>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-mono font-bold bg-sky-500/15 border border-sky-500/30 text-sky-300">
                                        {{ $user->streak->freeze_count }}/5 dona
                                    </span>
                                </div>
                                <p class="text-[11px] text-mist leading-relaxed">
                                    {{ __('shop.items.streak_freeze_desc') }}
                                </p>
                                <div class="text-[10px] font-mono text-sky-400/80 pt-1 border-t border-ink-border/50">
                                    Avtomatik faol (kun o‘tkazib yuborilganda sarflanadi)
                                </div>
                            </div>
                        @endif

                        {{-- Acquired Items --}}
                        @foreach($userItems as $uItem)
                            @php
                                $sItem = $uItem->shopItem;
                                if (!$sItem) continue;
                                // Streak freeze yuqorida alohida karta sifatida ko'rsatilgani uchun bu yerda takrorlamaymiz
                                if ($sItem->key === 'streak_freeze') continue;
                                $isEquipped = in_array($sItem->id, $equippedItemIds, true);
                            @endphp
                            <div class="p-4 rounded-panel bg-ink-900 border transition-all flex flex-col justify-between space-y-3 {{ $isEquipped ? 'border-amber-500/50 shadow-[0_0_12px_rgba(245,158,11,0.12)]' : 'border-ink-border hover:border-ink-border/80' }}">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-mono px-2 py-0.5 rounded bg-ink-800 border border-ink-border text-mist">
                                                {{ $sItem->category_label }}
                                            </span>
                                            <span class="text-[10px] font-mono uppercase font-bold {{ $sItem->rarity_color }}">
                                                {{ $sItem->rarity_label }}
                                            </span>
                                        </div>
                                        @if($isEquipped)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-500/15 border border-emerald-500/30 text-emerald-300">
                                                ✓ {{ __('shop.equipped') }}
                                            </span>
                                        @endif
                                    </div>

                                    <div>
                                        <h5 class="text-xs font-bold text-paper">{{ $sItem->name_text }}</h5>
                                        <p class="text-[11px] text-mist line-clamp-2 mt-0.5 leading-relaxed">{{ $sItem->description_text }}</p>
                                    </div>

                                    {{-- Aniq Vizual Ko'rgazma (Live Visual Preview & Effect) --}}
                                    @if($sItem->slot === 'title')
                                        @php
                                            $bColor = $sItem->safeColor('badge_color', '#A78BFA');
                                        @endphp
                                        <div class="p-2.5 rounded-btn bg-slate-50 dark:bg-ink-950/80 border border-slate-200 dark:border-ink-border/60 flex items-center justify-between">
                                            <span class="text-[10px] font-mono text-mist uppercase">Nishon:</span>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold"
                                                  style="background-color: {{ $bColor }}20; color: {{ $bColor }}; border: 1.5px solid {{ $bColor }}60;">
                                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                                <span>{{ $sItem->name_text }}</span>
                                            </span>
                                        </div>
                                        <p class="text-[10px] font-mono text-amber-700 dark:text-amber-400/90 leading-tight">
                                            ✦ Ismingiz yonida va chatda ushbu faxriy unvon nishoni ko‘rinadi.
                                        </p>
                                    @elseif($sItem->slot === 'name_color')
                                        @php
                                            $nColor = $sItem->safeColor('color', '#F59E0B');
                                        @endphp
                                        <div class="p-2.5 rounded-btn bg-slate-50 dark:bg-ink-950/80 border border-slate-200 dark:border-ink-border/60 flex items-center justify-between">
                                            <span class="text-[10px] font-mono text-mist uppercase">Ism rangi:</span>
                                            <span class="text-xs font-serif font-bold" style="color: {{ $nColor }}; text-shadow: 0 0 12px {{ $nColor }}50;">
                                                {{ $user->name }}
                                            </span>
                                        </div>
                                        <p class="text-[10px] font-mono text-amber-700 dark:text-amber-400/90 leading-tight">
                                            ✦ Profil, chat va sharhlarda ismingiz shu rangda porlab turadi.
                                        </p>
                                    @elseif($sItem->slot === 'accent')
                                        @php
                                            $aColor = $sItem->safeColor('color', '#10B981');
                                        @endphp
                                        <div class="p-2.5 rounded-btn bg-slate-50 dark:bg-ink-950/80 border border-slate-200 dark:border-ink-border/60 flex items-center justify-between">
                                            <span class="text-[10px] font-mono text-mist uppercase">Sayt urg‘usi:</span>
                                            <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold" style="background-color: {{ $aColor }}; color: #07090E;">
                                                Tugma namunasi
                                            </span>
                                        </div>
                                        <p class="text-[10px] font-mono text-amber-700 dark:text-amber-400/90 leading-tight">
                                            ✦ Butun saytdagi asosiy tugmalar va aksentlar shu rangga o‘tadi.
                                        </p>
                                    @elseif($sItem->slot === 'reader_palette')
                                        @php
                                            $bgDark = $sItem->safeColor('bg_dark', '#121212');
                                            $txDark = $sItem->safeColor('text_dark', '#ffffff');
                                        @endphp
                                        <div class="p-2 rounded-btn border flex items-center justify-between" style="background-color: {{ $bgDark }}; color: {{ $txDark }}; border-color: {{ $txDark }}33;">
                                            <span class="text-[10px] font-mono opacity-80 uppercase">Mutolaa foni:</span>
                                            <span class="text-xs font-serif font-bold">Aa Kitob sahifasi</span>
                                        </div>
                                        <p class="text-[10px] font-mono text-amber-700 dark:text-amber-400/90 leading-tight">
                                            ✦ Kitobxon o‘quvchisida sahifa foni va matn rangi shunga o‘zgaradi.
                                        </p>
                                    @elseif($sItem->slot === 'highlight_color')
                                        @php
                                            $hColor = $sItem->safeColor('color', '#84CC16');
                                        @endphp
                                        <div class="p-2.5 rounded-btn bg-slate-50 dark:bg-ink-950/80 border border-slate-200 dark:border-ink-border/60 flex items-center justify-between">
                                            <span class="text-[10px] font-mono text-mist uppercase">Belgilash:</span>
                                            <span class="px-2 py-0.5 rounded text-[11px] font-mono text-white font-bold" style="background-color: {{ $hColor }};">
                                                Iqtibos belgisi
                                            </span>
                                        </div>
                                        <p class="text-[10px] font-mono text-amber-700 dark:text-amber-400/90 leading-tight">
                                            ✦ Kitob ichida belgilangan matnlar va qaydlar shu rangda yonadi.
                                        </p>
                                    @elseif($sItem->category === 'bundle')
                                        <div class="p-2.5 rounded-btn bg-slate-50 dark:bg-ink-950/80 border border-slate-200 dark:border-ink-border/60 flex items-center justify-between">
                                            <span class="text-[10px] font-mono text-mist uppercase">To‘plam:</span>
                                            <span class="text-[11px] font-mono font-bold text-amber-700 dark:text-amber-300">
                                                Profil + Avatar + Chat
                                            </span>
                                        </div>
                                        <p class="text-[10px] font-mono text-amber-700 dark:text-amber-400/90 leading-tight">
                                            ✦ Profil foni, avatar ramkasi va chat pufakchasini o‘zgartiradi.
                                        </p>
                                    @endif
                                </div>

                                @if(!$sItem->is_consumable)
                                    <div>
                                        @if($isEquipped)
                                            <button type="button" wire:click="unequipItem('{{ $sItem->category === 'bundle' ? 'all' : ($sItem->slot ?? 'all') }}')" wire:loading.attr="disabled"
                                                    class="w-full py-1.5 rounded-btn text-xs font-mono font-medium border border-ink-border hover:border-rose-500/40 hover:text-rose-500 text-mist transition">
                                                {{ __('shop.unequip') }}
                                            </button>
                                        @else
                                            <button type="button" wire:click="equipItem('{{ $sItem->key }}')" wire:loading.attr="disabled"
                                                    class="w-full py-1.5 rounded-btn text-xs font-mono font-bold bg-amber-500/15 border border-amber-500/30 hover:bg-amber-500/25 text-amber-800 dark:text-amber-300 transition">
                                                {{ __('shop.equip') }}
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-[10px] font-mono text-mist pt-1 border-t border-ink-border/50 flex items-center justify-between">
                                        <span>Miqdor: <strong class="text-amber-700 dark:text-amber-400">{{ $uItem->quantity }} ta</strong></span>
                                        <span class="text-mist/70">Avtomatik sarflanadi</span>
                                    </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

</div>
