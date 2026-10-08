{{-- ── Universal Header: barcha sahifalar uchun yagona navigatsiya (Editorial) ── --}}
@php
    $navLinks = [
        ['route' => 'books.catalog', 'match' => ['books.*', 'book.*'], 'label' => __('site.nav.books')],
        ['route' => 'leaderboard',   'match' => ['leaderboard*'],       'label' => __('site.nav.leaderboard')],
        ['route' => 'chat',          'match' => ['chat*'],              'label' => __('site.nav.chat')],
        ['route' => 'groups.index',  'match' => ['groups.*'],           'label' => __('site.nav.groups')],
        ['route' => 'live.index',    'match' => ['live.*'],             'label' => __('site.nav.live')],
    ];
    $moreActive = request()->routeIs('about', 'faq', 'contact');
    $isHome = request()->routeIs('home');
@endphp
<header id="site-header"
        x-data="{ 
            mobileMenu: false, 
            moreOpen: false,
            scrolled: false,
            isHome: @js($isHome)
        }"
        x-init="if (isHome) { scrolled = (window.scrollY > 20); }"
        @scroll.window.passive="if (isHome) { scrolled = (window.scrollY > 20); }"
        class="sticky top-0 z-40 w-full transition-all duration-300 {{ $isHome ? 'bg-transparent border-b border-white/10' : 'bg-ink-900/95 border-b border-ink-border backdrop-blur-md shadow-lg shadow-black/20' }}"
        :class="(!isHome || scrolled || mobileMenu) 
            ? 'bg-ink-900/95 border-b border-ink-border backdrop-blur-md shadow-lg shadow-black/20' 
            : 'bg-transparent border-b border-white/10 backdrop-blur-none shadow-none'">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-3">

        {{-- Logo: SVG kitob belgisi + Spectral so'z belgisi --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 group shrink-0" aria-label="Kitobxon">
            <span class="w-9 h-9 rounded-btn bg-vermilion text-paper flex items-center justify-center transition-colors duration-base group-hover:bg-vermilion-700">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                    <path d="M9 7h6"/>
                </svg>
            </span>
            <span class="hidden sm:flex flex-col leading-none">
                <span class="font-display font-semibold text-paper text-[1.15rem] tracking-[-0.01em] transition-colors duration-base group-hover:text-amber-400">Kitobxon</span>
                <span class="font-mono text-[9px] text-mist uppercase tracking-[0.18em] mt-1">{{ __('site.common.platform') }}</span>
            </span>
        </a>

        {{-- Desktop Nav — asosiy linklar --}}
        <nav class="hidden lg:flex items-stretch self-stretch gap-1 text-[13px] font-medium flex-1 justify-center">
            @foreach($navLinks as $link)
                @php $active = request()->routeIs(...$link['match']); @endphp
                <a href="{{ route($link['route']) }}"
                   class="relative inline-flex items-center px-3 whitespace-nowrap transition-colors duration-base {{ $active ? 'text-paper' : 'text-mist hover:text-paper' }}"
                   @if($active) aria-current="page" @endif>
                    {{ $link['label'] }}
                    <span class="absolute left-3 right-3 bottom-0 h-[2px] {{ $active ? 'bg-amber-500' : 'bg-transparent' }}"></span>
                </a>
            @endforeach

            {{-- "Ko'proq" dropdown --}}
            <div class="relative flex items-stretch" x-data @click.outside="moreOpen = false">
                <button @click="moreOpen = !moreOpen" type="button"
                        class="relative inline-flex items-center gap-1.5 px-3 whitespace-nowrap font-medium select-none transition-colors duration-base {{ $moreActive ? 'text-paper' : 'text-mist hover:text-paper' }}"
                        :aria-expanded="moreOpen.toString()">
                    <span>{{ __('site.nav.more') ?? 'Ko\'proq' }}</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-base" :class="moreOpen ? 'rotate-180 text-amber-400' : ''"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                    <span class="absolute left-3 right-3 bottom-0 h-[2px] {{ $moreActive ? 'bg-amber-500' : 'bg-transparent' }}"></span>
                </button>
                <div x-show="moreOpen" x-cloak
                     x-transition:enter="transition ease-out duration-base"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-out duration-micro"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="absolute top-full left-0 mt-px w-56 py-1.5 bg-ink-800 border border-ink-border rounded-panel shadow-popover z-50">
                    <div class="px-3.5 pt-1.5 pb-2 ks-eyebrow border-b border-ink-border mb-1">
                        {{ __('site.nav.sections') }}
                    </div>
                    <a href="{{ route('about') }}" @click="moreOpen=false" class="flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-mist hover:text-paper hover:bg-ink-700 transition-colors duration-base">
                        <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                        <span>{{ __('site.nav.about') }}</span>
                    </a>
                    <a href="{{ route('faq') }}" @click="moreOpen=false" class="flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-mist hover:text-paper hover:bg-ink-700 transition-colors duration-base">
                        <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                        <span>{{ __('site.nav.faq') }}</span>
                    </a>
                    <a href="{{ route('contact') }}" @click="moreOpen=false" class="flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-mist hover:text-paper hover:bg-ink-700 transition-colors duration-base">
                        <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        <span>{{ __('site.nav.contact') }}</span>
                    </a>
                </div>
            </div>
        </nav>

        {{-- Right side actions --}}
        <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">

            {{-- Til almashtirgich --}}
            <x-lang-switcher class="hidden sm:block" />

            @auth
                {{-- Streak / Points / Coins — barcha ekranlarda chiroyli ko'rinadi va jonli o'zgaradi --}}
                <div class="flex items-center divide-x divide-ink-border border border-ink-border rounded-btn bg-ink-950 relative"
                     x-data="{
                         totalPoints: {{ auth()->check() ? (int) auth()->user()->total_points : 0 }},
                         totalCoins: {{ auth()->check() ? (int) auth()->user()->coin_balance : 0 }},
                         pointsBump: false,
                         coinsBump: false,
                         floater: null,
                         coinFloater: null,
                         pointsTimer: null,
                         coinsTimer: null,
                         pointsAnimFrame: null,
                         coinsAnimFrame: null,

                         animateAdd(pts, newTot) {
                             let pointsToAdd = parseInt(pts) || 0;
                             let targetTotal = parseInt(newTot);

                             if (isNaN(targetTotal)) {
                                 if (pointsToAdd <= 0) return;
                                 targetTotal = this.totalPoints + pointsToAdd;
                             } else {
                                 if (targetTotal <= this.totalPoints) {
                                     this.totalPoints = targetTotal;
                                     return;
                                 }
                                 pointsToAdd = targetTotal - this.totalPoints;
                             }

                             if (this.pointsAnimFrame) cancelAnimationFrame(this.pointsAnimFrame);
                             if (this.pointsTimer) clearTimeout(this.pointsTimer);

                             let addedAmount = pointsToAdd;
                             this.floater = null;
                             this.pointsBump = false;

                             this.$nextTick(() => {
                                 this.floater = '+' + addedAmount;
                                 this.pointsBump = true;
                             });

                             let start = this.totalPoints;
                             let end = targetTotal;
                             let duration = 800;
                             let startTime = performance.now();
                             let self = this;

                             function step(now) {
                                 let progress = Math.min((now - startTime) / duration, 1);
                                 let ease = progress * (2 - progress);
                                 self.totalPoints = Math.round(start + (end - start) * ease);
                                 if (progress < 1) {
                                     self.pointsAnimFrame = requestAnimationFrame(step);
                                 } else {
                                     self.totalPoints = end;
                                     self.pointsTimer = setTimeout(() => {
                                         self.pointsBump = false;
                                         self.floater = null;
                                     }, 1800);
                                 }
                             }
                             this.pointsAnimFrame = requestAnimationFrame(step);
                         },

                         animateAddCoins(cns, newTot) {
                             let coinsToAdd = parseInt(cns) || 0;
                             let targetTotal = parseInt(newTot);

                             if (isNaN(targetTotal)) {
                                 if (coinsToAdd <= 0) return;
                                 targetTotal = this.totalCoins + coinsToAdd;
                             } else {
                                 if (targetTotal <= this.totalCoins) {
                                     this.totalCoins = targetTotal;
                                     return;
                                 }
                                 coinsToAdd = targetTotal - this.totalCoins;
                             }

                             if (this.coinsAnimFrame) cancelAnimationFrame(this.coinsAnimFrame);
                             if (this.coinsTimer) clearTimeout(this.coinsTimer);

                             let addedAmount = coinsToAdd;
                             this.coinFloater = null;
                             this.coinsBump = false;

                             this.$nextTick(() => {
                                 this.coinFloater = '+' + addedAmount;
                                 this.coinsBump = true;
                             });

                             let start = this.totalCoins;
                             let end = targetTotal;
                             let duration = 800;
                             let startTime = performance.now();
                             let self = this;

                             function step(now) {
                                 let progress = Math.min((now - startTime) / duration, 1);
                                 let ease = progress * (2 - progress);
                                 self.totalCoins = Math.round(start + (end - start) * ease);
                                 if (progress < 1) {
                                     self.coinsAnimFrame = requestAnimationFrame(step);
                                 } else {
                                     self.totalCoins = end;
                                     self.coinsTimer = setTimeout(() => {
                                         self.coinsBump = false;
                                         self.coinFloater = null;
                                     }, 1800);
                                 }
                             }
                             this.coinsAnimFrame = requestAnimationFrame(step);
                         }
                     }"
                     @points-awarded.window="animateAdd($event.detail.points, $event.detail.newTotal)"
                     @coins-awarded.window="animateAddCoins($event.detail.coins, $event.detail.newTotal)">

                    {{-- Streak Flame --}}
                    <span x-data="{ streakCount: {{ (int) (auth()->user()->current_streak ?? 0) }} }"
                          @streak-updated.window="streakCount = $event.detail.streak"
                          class="inline-flex items-center gap-1 sm:gap-1.5 px-2 sm:px-2.5 h-8 text-amber-400 font-mono text-[11px] sm:text-[12px] font-medium tabular-nums"
                          title="{{ __('site.common.streak') }}">
                        <svg class="ks-flame w-3.5 h-3.5 sm:w-4 sm:h-4" :class="streakCount > 0 ? 'is-lit' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                        </svg>
                        <span x-text="streakCount">{{ auth()->user()->current_streak }}</span>
                    </span>

                    {{-- Points with Live Counting and Floating Badge --}}
                    <div class="relative">
                        <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2 sm:px-2.5 h-8 font-mono text-[11px] sm:text-[12px] font-medium tabular-nums transition-colors duration-200"
                              :class="pointsBump ? 'bg-amber-500/20 text-amber-300' : 'text-paper'"
                              title="{{ __('site.common.points') }}">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-amber-400 transition-transform duration-300"
                                 :class="pointsBump ? 'scale-125 rotate-12' : ''"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                            <span :class="pointsBump ? 'ks-number-rolling' : ''" x-text="totalPoints.toLocaleString()">{{ number_format(auth()->user()->total_points) }}</span>
                        </span>

                        {{-- Floating +Points Pop Animation (Yuqoriga uchuvchi) --}}
                        <template x-if="floater">
                            <span class="ks-reward-floater px-2 py-0.5 rounded-full bg-gradient-to-r from-amber-400 via-amber-300 to-yellow-400 text-ink-950 font-mono font-black text-[10px] sm:text-[11px] shadow-[0_0_16px_rgba(245,158,11,0.7)] border border-amber-200/90 flex items-center gap-1">
                                <span>⭐</span>
                                <span x-text="floater"></span>
                            </span>
                        </template>
                    </div>

                    {{-- Coins with Live Counting and Floating Badge --}}
                    <div class="relative">
                        <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2 sm:px-2.5 h-8 font-mono text-[11px] sm:text-[12px] font-medium tabular-nums transition-colors duration-200"
                              :class="coinsBump ? 'bg-yellow-500/20 text-yellow-300' : 'text-paper'"
                              title="{{ __('site.common.coins') }}">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gilt transition-transform duration-300"
                                 :class="coinsBump ? 'scale-125 -rotate-12' : ''"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/>
                            </svg>
                            <span :class="coinsBump ? 'ks-coin-rolling' : ''" x-text="totalCoins.toLocaleString()">{{ number_format(auth()->user()->coin_balance) }}</span>
                        </span>

                        {{-- Floating +Coins Pop Animation (Yuqoriga uchuvchi) --}}
                        <template x-if="coinFloater">
                            <span class="ks-reward-floater px-2 py-0.5 rounded-full bg-gradient-to-r from-yellow-300 via-amber-300 to-yellow-400 text-ink-950 font-mono font-black text-[10px] sm:text-[11px] shadow-[0_0_16px_rgba(250,204,21,0.7)] border border-yellow-200/90 flex items-center gap-1">
                                <span>🪙</span>
                                <span x-text="coinFloater"></span>
                            </span>
                        </template>
                    </div>
                </div>

                <style>
                @keyframes ksRewardFloat {
                  0% {
                    opacity: 0;
                    transform: translate(-50%, 6px) scale(0.7);
                  }
                  15% {
                    opacity: 1;
                    transform: translate(-50%, -8px) scale(1.1);
                  }
                  50% {
                    opacity: 1;
                    transform: translate(-50%, -18px) scale(1);
                  }
                  100% {
                    opacity: 0;
                    transform: translate(-50%, -32px) scale(0.85);
                  }
                }
                .ks-reward-floater {
                  position: absolute;
                  left: 50%;
                  bottom: calc(100% + 4px);
                  pointer-events: none;
                  animation: ksRewardFloat 1.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                  z-index: 60;
                  white-space: nowrap;
                }
                @keyframes ksNumberRoll {
                  0% { transform: scale(1); }
                  25% { transform: scale(1.22); color: #FBBF24; filter: drop-shadow(0 0 8px rgba(245, 158, 11, 0.8)); }
                  60% { transform: scale(1.1); color: #FCD34D; filter: drop-shadow(0 0 4px rgba(245, 158, 11, 0.5)); }
                  100% { transform: scale(1); color: inherit; filter: none; }
                }
                .ks-number-rolling {
                  display: inline-block;
                  animation: ksNumberRoll 0.85s cubic-bezier(0.16, 1, 0.3, 1);
                }
                @keyframes ksCoinRoll {
                  0% { transform: scale(1); }
                  25% { transform: scale(1.24); color: #FACC15; filter: drop-shadow(0 0 8px rgba(250, 204, 21, 0.85)); }
                  60% { transform: scale(1.1); color: #FEF08A; filter: drop-shadow(0 0 4px rgba(250, 204, 21, 0.5)); }
                  100% { transform: scale(1); color: inherit; filter: none; }
                }
                .ks-coin-rolling {
                  display: inline-block;
                  animation: ksCoinRoll 0.85s cubic-bezier(0.16, 1, 0.3, 1);
                }
                </style>

                {{-- Bildirishnomalar --}}
                <a href="{{ route('notifications') }}" class="hidden md:inline-flex relative p-2 rounded-btn text-mist hover:text-paper hover:bg-ink-800 transition-colors duration-base" title="{{ __('site.nav.notifications') }}">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-vermilion rounded-full ring-2 ring-ink-900"></span>
                    @endif
                </a>

                {{-- Admin / Teacher panel tugmasi --}}
                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}" class="hidden md:inline-flex ks-btn-gold !px-3 !py-1.5 font-mono !text-[11px] uppercase tracking-[0.08em] whitespace-nowrap">
                        {{ __('site.nav.dashboard') }} →
                    </a>
                @elseif(auth()->user()->hasRole('teacher'))
                    <a href="{{ route('teacher.dashboard') }}" class="hidden md:inline-flex ks-btn-gold !px-3 !py-1.5 font-mono !text-[11px] uppercase tracking-[0.08em] whitespace-nowrap">
                        {{ __('site.nav.teacher_panel') }} →
                    </a>
                @endif

                {{-- Avatar Dropdown --}}
                <div x-data="{ userMenuOpen: false }" x-on:click.outside="userMenuOpen = false" class="relative">
                    <button @click="userMenuOpen = !userMenuOpen" type="button" class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-btn hover:bg-ink-800 border border-ink-border transition-colors duration-base select-none">
                        <img src="{{ auth()->user()->avatar_url }}" class="w-7 h-7 rounded-full object-cover" alt="{{ auth()->user()->name }}">
                        <span class="text-xs font-medium text-paper hidden lg:inline max-w-[90px] truncate">{{ auth()->user()->name }}</span>
                        <svg class="w-3.5 h-3.5 text-mist transition-transform duration-base" :class="userMenuOpen ? 'rotate-180 text-amber-400' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div x-show="userMenuOpen" x-cloak
                         x-transition:enter="transition ease-out duration-base"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-out duration-micro"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="absolute right-0 mt-2 w-64 bg-ink-800 border border-ink-border rounded-panel shadow-popover z-50 overflow-hidden">

                        {{-- User Profile Header --}}
                        <div class="px-4 py-3.5 border-b border-ink-border">
                            <div class="flex items-center gap-3">
                                <img src="{{ auth()->user()->avatar_url }}" class="w-10 h-10 rounded-full object-cover" alt="{{ auth()->user()->name }}">
                                <div class="min-w-0 flex-1">
                                    <p class="font-display text-[15px] font-semibold text-paper truncate leading-tight">{{ auth()->user()->name }}</p>
                                    <p class="text-[11px] font-mono text-mist truncate mt-0.5">{{ '@' . auth()->user()->username }}</p>
                                </div>
                                @if(auth()->user()->hasRole('admin'))
                                    <span class="ks-badge bg-rose-500/10 border border-rose-500/30 text-rose-300">Admin</span>
                                @elseif(auth()->user()->hasRole('teacher'))
                                    <span class="ks-badge bg-amber-500/10 border border-amber-500/30 text-amber-400">Ustoz</span>
                                @endif
                            </div>

                            {{-- Stats for small screens --}}
                            <div class="mt-3 pt-3 border-t border-ink-border grid grid-cols-3 gap-2 md:hidden"
                                 x-data="{ 
                                     mobilePoints: {{ auth()->check() ? (int) auth()->user()->total_points : 0 }},
                                     mobileCoins: {{ auth()->check() ? (int) auth()->user()->coin_balance : 0 }}
                                 }"
                                 @points-awarded.window="mobilePoints = $event.detail.newTotal !== undefined ? $event.detail.newTotal : (mobilePoints + ($event.detail.points || 10))"
                                 @coins-awarded.window="mobileCoins = $event.detail.newTotal !== undefined ? $event.detail.newTotal : (mobileCoins + ($event.detail.coins || 1))">
                                <span x-data="{ mobileStreak: {{ (int) (auth()->user()->current_streak ?? 0) }} }"
                                      @streak-updated.window="mobileStreak = $event.detail.streak"
                                      class="inline-flex items-center gap-1 text-amber-400 font-mono text-[11px] font-medium tabular-nums">
                                    <svg class="ks-flame w-3.5 h-3.5" :class="mobileStreak > 0 ? 'is-lit' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
                                    <span x-text="mobileStreak">{{ auth()->user()->current_streak }}</span>
                                </span>
                                <span class="inline-flex items-center gap-1 text-paper font-mono text-[11px] font-medium tabular-nums">
                                    <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <span x-text="mobilePoints.toLocaleString()">{{ number_format(auth()->user()->total_points) }}</span>
                                </span>
                                <span class="inline-flex items-center gap-1 text-paper font-mono text-[11px] font-medium tabular-nums">
                                    <svg class="w-3.5 h-3.5 text-gilt" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                                    <span x-text="mobileCoins.toLocaleString()">{{ number_format(auth()->user()->coin_balance) }}</span>
                                </span>
                            </div>
                        </div>

                        {{-- Menu Items --}}
                        <div class="py-1.5">
                            @if(auth()->user()->hasRole('admin'))
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-[13px] font-medium text-amber-400 hover:bg-ink-700 transition-colors duration-base">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                                    <span>{{ __('site.nav.dashboard') }}</span>
                                </a>
                            @elseif(auth()->user()->hasRole('teacher'))
                                <a href="{{ route('teacher.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-[13px] font-medium text-amber-400 hover:bg-ink-700 transition-colors duration-base">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                                    <span>{{ __('site.nav.teacher_panel') }}</span>
                                </a>
                            @endif

                            <a href="{{ route('profile.show', auth()->user()->username) }}" class="flex items-center gap-2.5 px-4 py-2 text-[13px] text-mist hover:text-paper hover:bg-ink-700 transition-colors duration-base">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <span>{{ __('site.nav.my_profile') }}</span>
                            </a>
                            <a href="{{ route('settings') }}" class="flex items-center gap-2.5 px-4 py-2 text-[13px] text-mist hover:text-paper hover:bg-ink-700 transition-colors duration-base">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                                <span>{{ __('site.nav.settings') }}</span>
                            </a>

                            <div class="my-1.5 ks-rule"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-[13px] font-medium text-rose-300 hover:bg-rose-500/10 transition-colors duration-base">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                                    <span>{{ __('site.nav.logout') }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="px-3 py-1.5 text-sm font-medium text-mist hover:text-paper transition-colors duration-base whitespace-nowrap">{{ __('site.nav.login') }}</a>
                <a href="{{ route('register') }}" class="hidden sm:inline-flex ks-btn-primary whitespace-nowrap">
                    {{ __('site.nav.register') }}
                </a>
            @endauth

            {{-- Hamburger (mobil) --}}
            <button @click="mobileMenu = !mobileMenu" type="button" class="lg:hidden p-2 rounded-btn text-mist hover:text-paper hover:bg-ink-800 transition-colors duration-base" aria-label="{{ __('site.nav.menu') }}" :aria-expanded="mobileMenu.toString()">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path x-show="!mobileMenu" d="M4 6h16M4 12h16M4 18h10"/>
                    <path x-show="mobileMenu" d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="mobileMenu" x-cloak @click.away="mobileMenu = false"
         x-transition:enter="transition ease-out duration-base"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-out duration-micro"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="lg:hidden border-t border-ink-border bg-ink-900 px-5 sm:px-6 pt-3 pb-5 max-h-[calc(100vh-4rem)] overflow-y-auto">
        <p class="ks-eyebrow pb-2">{{ __('site.nav.sections') }}</p>
        @foreach($navLinks as $link)
            @php $active = request()->routeIs(...$link['match']); @endphp
            <a href="{{ route($link['route']) }}" @click="mobileMenu = false"
               class="flex items-center justify-between font-display text-lg py-2.5 border-b border-ink-border transition-colors duration-base {{ $active ? 'text-amber-400' : 'text-paper hover:text-amber-400' }}">
                <span>{{ $link['label'] }}</span>
                @if($active)<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>@endif
            </a>
        @endforeach
        <a href="{{ route('about') }}"   @click="mobileMenu = false" class="block text-sm py-2.5 text-mist hover:text-paper border-b border-ink-border transition-colors duration-base">{{ __('site.nav.about') }}</a>
        <a href="{{ route('faq') }}"     @click="mobileMenu = false" class="block text-sm py-2.5 text-mist hover:text-paper border-b border-ink-border transition-colors duration-base">{{ __('site.nav.faq') }}</a>
        <a href="{{ route('contact') }}" @click="mobileMenu = false" class="block text-sm py-2.5 text-mist hover:text-paper transition-colors duration-base">{{ __('site.nav.contact') }}</a>

        <div class="pt-4 mt-2 border-t border-ink-border space-y-2">
            @auth
                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}" @click="mobileMenu = false" class="ks-btn-gold w-full font-mono !text-[11px] uppercase tracking-[0.08em]">{{ __('site.nav.dashboard') }} →</a>
                @elseif(auth()->user()->hasRole('teacher'))
                    <a href="{{ route('teacher.dashboard') }}" @click="mobileMenu = false" class="ks-btn-gold w-full font-mono !text-[11px] uppercase tracking-[0.08em]">{{ __('site.nav.teacher_panel') }} →</a>
                @endif
                <a href="{{ route('profile.show', auth()->user()->username) }}" @click="mobileMenu = false" class="block text-sm py-1.5 text-mist hover:text-paper transition-colors duration-base">{{ __('site.nav.my_profile') }}</a>
                <a href="{{ route('notifications') }}" @click="mobileMenu = false" class="block md:hidden text-sm py-1.5 text-mist hover:text-paper transition-colors duration-base">{{ __('site.nav.notifications') }}</a>
                <a href="{{ route('settings') }}" @click="mobileMenu = false" class="block text-sm py-1.5 text-mist hover:text-paper transition-colors duration-base">{{ __('site.nav.settings') }}</a>
                <form method="POST" action="{{ route('logout') }}" class="pt-1">
                    @csrf
                    <button type="submit" class="text-sm text-rose-300 hover:underline">{{ __('site.nav.logout') }}</button>
                </form>
            @else
                <a href="{{ route('login') }}"    @click="mobileMenu = false" class="ks-btn-ghost w-full">{{ __('site.nav.login') }}</a>
                <a href="{{ route('register') }}" @click="mobileMenu = false" class="ks-btn-primary w-full">{{ __('site.nav.register') }}</a>
            @endauth

            {{-- Til almashtirgich (mobil) --}}
            <div class="pt-3 flex items-center gap-2">
                <span class="ks-eyebrow">{{ __('site.common.language') }}</span>
                @foreach(['uz', 'ru', 'en'] as $code)
                    <a href="{{ url()->current() }}?lang={{ $code }}"
                       class="px-2.5 py-1 rounded-badge font-mono text-[11px] font-medium transition-colors duration-base {{ app()->getLocale() === $code ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : 'text-mist hover:text-paper border border-ink-border' }}">
                       {{ strtoupper($code) }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</header>
