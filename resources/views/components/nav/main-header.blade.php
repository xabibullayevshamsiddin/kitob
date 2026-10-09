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
            isHome: @js($isHome),
            theme: (function() {
                try {
                    return localStorage.getItem('kitob_theme') || (document.documentElement.classList.contains('light') ? 'light' : 'dark');
                } catch(e) { return 'dark'; }
            })(),
            toggleTheme(evt) {
                this.theme = this.theme === 'dark' ? 'light' : 'dark';
                if (window.__setKitobTheme) {
                    window.__setKitobTheme(this.theme, evt);
                }
            }
        }"
        x-init="if (isHome) { scrolled = (window.scrollY > 20); }"
        @scroll.window.passive="if (isHome) { scrolled = (window.scrollY > 20); }"
        @theme-changed.window="theme = $event.detail.theme"
        class="sticky top-0 z-40 w-full transition-all duration-300 {{ $isHome ? 'bg-transparent border-b border-white/10 is-hero-header' : 'bg-ink-900/95 border-b border-ink-border backdrop-blur-md shadow-lg shadow-black/20' }}"
        :class="(!isHome || scrolled || mobileMenu) 
            ? 'bg-ink-900/95 border-b border-ink-border backdrop-blur-md shadow-lg shadow-black/10' 
            : 'bg-transparent border-b border-white/10 backdrop-blur-none shadow-none is-hero-header'">
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
                     class="header-dropdown-menu absolute top-full left-0 mt-px w-56 py-1.5 bg-ink-800 border border-ink-border rounded-panel shadow-popover z-50">
                    <div class="px-3.5 pt-1.5 pb-2 ks-eyebrow border-b border-ink-border mb-1">
                        {{ __('site.nav.sections') }}
                    </div>
                    <a href="{{ route('about') }}" @click="moreOpen=false" class="flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-paper/85 dark:text-mist hover:text-paper hover:bg-ink-700/60 transition-colors duration-base">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                        <span>{{ __('site.nav.about') }}</span>
                    </a>
                    <a href="{{ route('faq') }}" @click="moreOpen=false" class="flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-paper/85 dark:text-mist hover:text-paper hover:bg-ink-700/60 transition-colors duration-base">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                        <span>{{ __('site.nav.faq') }}</span>
                    </a>
                    <a href="{{ route('contact') }}" @click="moreOpen=false" class="flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-paper/85 dark:text-mist hover:text-paper hover:bg-ink-700/60 transition-colors duration-base">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        <span>{{ __('site.nav.contact') }}</span>
                    </a>
                </div>
            </div>
        </nav>

        {{-- Right side actions --}}
        <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">

            {{-- Til almashtirgich --}}
            <x-lang-switcher class="hidden sm:block" />

            {{-- Mavzu almashtirgich (Light / Dark mode toggle) --}}
            <button @click="toggleTheme($event)" 
                    type="button"
                    class="header-theme-btn relative inline-flex items-center justify-center w-8 h-8 rounded-btn border border-ink-border bg-ink-800 text-mist hover:text-paper hover:bg-ink-700 transition-colors duration-base select-none shrink-0 group focus:outline-none"
                    :title="theme === 'dark' ? 'Yorug\' rejim (Light mode)' : 'Tungi rejim (Dark mode)'"
                    :aria-label="theme === 'dark' ? 'Light mode' : 'Dark mode'">
                {{-- Concentric Pulse Shockwave Rings (Blinkit style) --}}
                <span class="theme-pulse-halo theme-pulse-halo-1" aria-hidden="true"></span>
                <span class="theme-pulse-halo theme-pulse-halo-2" aria-hidden="true"></span>

                {{-- Micro-Spark Particle Rays --}}
                <span class="theme-spark theme-spark-1" aria-hidden="true"></span>
                <span class="theme-spark theme-spark-2" aria-hidden="true"></span>
                <span class="theme-spark theme-spark-3" aria-hidden="true"></span>
                <span class="theme-spark theme-spark-4" aria-hidden="true"></span>
                <span class="theme-spark theme-spark-5" aria-hidden="true"></span>
                <span class="theme-spark theme-spark-6" aria-hidden="true"></span>

                {{-- Sun icon (Dark rejimda ko'rinadi) --}}
                <svg x-show="theme === 'dark'" x-cloak class="header-theme-sun w-4 h-4 text-amber-400 select-none transition-transform duration-base group-hover:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                </svg>
                {{-- Moon icon (Light rejimda ko'rinadi) --}}
                <svg x-show="theme === 'light'" x-cloak class="header-theme-moon w-4 h-4 text-amber-600 select-none transition-transform duration-base group-hover:-rotate-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
                </svg>
            </button>

            @auth
                {{-- Streak / Points / Coins — barcha ekranlarda chiroyli ko'rinadi va jonli o'zgaradi --}}
                <div class="header-stats-pill flex items-center divide-x rounded-btn relative transition-all duration-300 {{ $isHome ? 'divide-white/15 border border-white/20 bg-white/[0.08] backdrop-blur-md shadow-sm' : 'divide-ink-border border border-ink-border bg-ink-950/90' }}"
                     :class="(!isHome || scrolled || mobileMenu) 
                         ? 'divide-ink-border border border-ink-border bg-ink-950/90 shadow-none' 
                         : 'divide-white/15 border border-white/20 bg-white/[0.08] backdrop-blur-md shadow-sm'"
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
                          class="header-streak-pill inline-flex items-center gap-1 sm:gap-1.5 px-2 sm:px-2.5 h-8 text-amber-500 dark:text-amber-400 font-mono text-[11px] sm:text-[12px] font-medium tabular-nums"
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
                    <button @click="userMenuOpen = !userMenuOpen" type="button" 
                            class="header-avatar-btn flex items-center gap-2 pl-1 pr-2 py-1 rounded-btn transition-colors duration-base select-none {{ $isHome ? 'border border-white/20 bg-white/[0.08] hover:bg-white/[0.14] backdrop-blur-md' : 'hover:bg-ink-800 border border-ink-border' }}"
                            :class="(!isHome || scrolled || mobileMenu) ? 'hover:bg-ink-800 border border-ink-border bg-transparent' : 'border border-white/20 bg-white/[0.08] hover:bg-white/[0.14] backdrop-blur-md'">
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
                         class="header-dropdown-menu absolute right-0 mt-2 w-64 bg-ink-800 border border-ink-border rounded-panel shadow-popover z-50 overflow-hidden">

                        {{-- User Profile Header --}}
                        <div class="px-4 py-3.5 border-b border-ink-border">
                            <div class="flex items-center gap-3">
                                <img src="{{ auth()->user()->avatar_url }}" class="w-10 h-10 rounded-full object-cover" alt="{{ auth()->user()->name }}">
                                <div class="min-w-0 flex-1">
                                    <p class="font-display text-[15px] font-semibold text-paper truncate leading-tight">{{ auth()->user()->name }}</p>
                                    <p class="text-[11px] font-mono text-mist truncate mt-0.5">{{ '@' . auth()->user()->username }}</p>
                                </div>
                                @if(auth()->user()->hasRole('admin'))
                                    <span class="ks-badge bg-rose-500/15 border border-rose-500/30 text-rose-700 dark:text-rose-300 font-bold">Admin</span>
                                @elseif(auth()->user()->hasRole('teacher'))
                                    <span class="ks-badge bg-amber-500/15 border border-amber-500/30 text-amber-700 dark:text-amber-400 font-bold">Ustoz</span>
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
                                      class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400 font-mono text-[11px] font-medium tabular-nums">
                                    <svg class="ks-flame w-3.5 h-3.5" :class="mobileStreak > 0 ? 'is-lit' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
                                    <span x-text="mobileStreak">{{ auth()->user()->current_streak }}</span>
                                </span>
                                <span class="inline-flex items-center gap-1 text-paper font-mono text-[11px] font-medium tabular-nums">
                                    <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <span x-text="mobilePoints.toLocaleString()">{{ number_format(auth()->user()->total_points) }}</span>
                                </span>
                                <span class="inline-flex items-center gap-1 text-paper font-mono text-[11px] font-medium tabular-nums">
                                    <svg class="w-3.5 h-3.5 text-amber-600 dark:text-gilt" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/></svg>
                                    <span x-text="mobileCoins.toLocaleString()">{{ number_format(auth()->user()->coin_balance) }}</span>
                                </span>
                            </div>
                        </div>

                        {{-- Menu Items --}}
                        <div class="py-1.5">
                            @if(auth()->user()->hasRole('admin'))
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-[13px] font-medium text-amber-700 dark:text-amber-400 hover:bg-ink-700/60 transition-colors duration-base">
                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                                    <span>{{ __('site.nav.dashboard') }}</span>
                                </a>
                            @elseif(auth()->user()->hasRole('teacher'))
                                <a href="{{ route('teacher.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-[13px] font-medium text-amber-700 dark:text-amber-400 hover:bg-ink-700/60 transition-colors duration-base">
                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                                    <span>{{ __('site.nav.teacher_panel') }}</span>
                                </a>
                            @endif

                            <a href="{{ route('profile.show', auth()->user()->username) }}" class="flex items-center gap-2.5 px-4 py-2 text-[13px] text-paper/85 dark:text-mist hover:text-paper hover:bg-ink-700/60 transition-colors duration-base">
                                <svg class="w-4 h-4 text-amber-600 dark:text-mist shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <span>{{ __('site.nav.my_profile') }}</span>
                            </a>
                            <a href="{{ route('settings') }}" class="flex items-center gap-2.5 px-4 py-2 text-[13px] text-paper/85 dark:text-mist hover:text-paper hover:bg-ink-700/60 transition-colors duration-base">
                                <svg class="w-4 h-4 text-amber-600 dark:text-mist shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                                <span>{{ __('site.nav.settings') }}</span>
                            </a>

                            {{-- Mavzu almashtirgich (Profile Dropdown) --}}
                            <button @click="toggleTheme($event); userMenuOpen = false" type="button" data-theme-toggle class="w-full flex items-center justify-between px-4 py-2 text-[13px] text-paper/85 dark:text-mist hover:text-paper hover:bg-ink-700/60 transition-colors duration-base">
                                <span class="flex items-center gap-2.5">
                                    <svg x-show="theme === 'dark'" x-cloak class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                                    <svg x-show="theme === 'light'" x-cloak class="w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                                    <span x-text="theme === 'dark' ? 'Yorug\' rejim' : 'Tungi rejim'"></span>
                                </span>
                                <span class="px-1.5 py-0.5 rounded-badge font-mono text-[10px] uppercase font-bold"
                                      :class="theme === 'light' ? 'bg-amber-500/15 text-amber-800 border border-amber-500/30' : 'bg-ink-950 text-amber-400 border border-ink-border'"
                                      x-text="theme"></span>
                            </button>

                            <div class="my-1.5 ks-rule"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-[13px] font-medium text-rose-600 dark:text-rose-300 hover:text-rose-700 dark:hover:text-rose-200 hover:bg-rose-500/10 transition-colors duration-base">
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
               class="flex items-center justify-between font-display text-lg py-2.5 border-b border-ink-border transition-colors duration-base {{ $active ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-paper hover:text-amber-600 dark:hover:text-amber-400' }}">
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
                    <button type="submit" class="text-sm text-rose-600 dark:text-rose-300 hover:text-rose-700 dark:hover:text-rose-200 transition-colors">{{ __('site.nav.logout') }}</button>
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

            {{-- Mavzu almashtirgich (mobil) --}}
            <div class="pt-3 mt-1 flex items-center justify-between border-t border-ink-border">
                <span class="ks-eyebrow">Mavzu / Theme</span>
                <button @click="toggleTheme($event)" type="button" data-theme-toggle class="inline-flex items-center gap-2 px-3 py-1.5 rounded-card border border-ink-border bg-ink-800 text-paper text-xs font-mono transition-colors hover:bg-ink-700">
                    <span x-show="theme === 'dark'">☀️ Yorug' rejim</span>
                    <span x-show="theme === 'light'">🌙 Tungi rejim</span>
                </button>
            </div>
        </div>
    </div>
</header>

<style>
/* ── Velorah & Editorial Hero Header Overrides ──
   When on Home and unscrolled, the header sits transparently over the dark cinematic video.
   Regardless of light/dark mode, text and key indicators remain luminous white and frosted glass.
   Once scrolled (scrolled === true), standard light/dark background and ink text smoothly resume.
*/
#site-header.is-hero-header {
    --paper-rgb: 255 255 255 !important;
    --paper-50-rgb: 255 255 255 !important;
    --paper-100-rgb: 255 255 255 !important;
    --paper-200-rgb: 241 245 249 !important;
    --paper-muted-rgb: 203 213 225 !important;
    --mist-rgb: 226 232 240 !important;
    --mist-600-rgb: 148 163 184 !important;
    --ink-border-rgb: 255 255 255 / 0.15 !important;
}
#site-header.is-hero-header .header-theme-btn {
    background-color: rgba(255, 255, 255, 0.08) !important;
    border-color: rgba(255, 255, 255, 0.2) !important;
    color: #ffffff !important;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
#site-header.is-hero-header .header-theme-btn:hover {
    background-color: rgba(255, 255, 255, 0.16) !important;
    border-color: rgba(255, 255, 255, 0.35) !important;
}
#site-header.is-hero-header .header-theme-moon {
    color: #fde047 !important;
}
#site-header.is-hero-header .header-lang-btn {
    background-color: rgba(255, 255, 255, 0.08) !important;
    border-color: rgba(255, 255, 255, 0.2) !important;
    color: #ffffff !important;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
#site-header.is-hero-header .header-lang-btn:hover {
    background-color: rgba(255, 255, 255, 0.16) !important;
    border-color: rgba(255, 255, 255, 0.35) !important;
}
#site-header.is-hero-header .header-streak-pill {
    color: #f59e0b !important;
}
#site-header.is-hero-header .ks-btn-primary {
    background-color: #C1392B !important;
    color: #ffffff !important;
    border-color: #A82E22 !important;
    box-shadow: 0 2px 8px rgba(193, 57, 43, 0.4) !important;
}
#site-header.is-hero-header .ks-btn-primary:hover {
    background-color: #A82E22 !important;
}

/* ── Isolated Popover & Dropdown Scope ──
   Dropdown menus and popovers inside the header must NEVER inherit the transparent video hero text colors!
   They are solid elevated cards and must strictly follow the active theme (light or dark).
*/
#site-header .header-dropdown-menu,
#site-header [class*="shadow-popover"] {
    isolation: isolate;
}

html.light:not(.force-dark) #site-header .header-dropdown-menu,
html.light:not(.force-dark) #site-header [class*="shadow-popover"],
html.light:not(.force-dark) #site-header [x-show="mobileMenu"] {
    --paper-rgb: 28 25 22 !important;
    --paper-50-rgb: 20 18 16 !important;
    --paper-100-rgb: 28 25 22 !important;
    --paper-200-rgb: 46 41 36 !important;
    --paper-muted-rgb: 92 85 75 !important;
    --mist-rgb: 107 99 88 !important;
    --mist-600-rgb: 78 72 63 !important;
    --ink-border-rgb: 224 217 205 !important;
    --ink-800-rgb: 249 246 240 !important;
    --ink-700-rgb: 236 230 219 !important;
}

html.light:not(.force-dark) #site-header .header-dropdown-menu,
html.light:not(.force-dark) #site-header [class*="shadow-popover"] {
    background-color: #FAF6EF !important;
    border-color: #DDD4C4 !important;
    color: #1C1916 !important;
    box-shadow: 0 16px 40px -6px rgba(28, 25, 22, 0.22), 0 2px 8px rgba(28, 25, 22, 0.08), 0 0 0 1px rgba(28, 25, 22, 0.08) !important;
}

html.light:not(.force-dark) #site-header [x-show="mobileMenu"] {
    background-color: #FAF6EF !important;
    border-color: #DDD4C4 !important;
    color: #1C1916 !important;
    box-shadow: 0 16px 36px -4px rgba(28, 25, 22, 0.16) !important;
}

html.dark #site-header .header-dropdown-menu,
html.dark #site-header [class*="shadow-popover"],
html:not(.light) #site-header .header-dropdown-menu,
html:not(.light) #site-header [class*="shadow-popover"] {
    --paper-rgb: 240 237 230 !important;
    --paper-50-rgb: 250 247 242 !important;
    --paper-100-rgb: 240 237 230 !important;
    --paper-200-rgb: 229 223 213 !important;
    --paper-muted-rgb: 201 196 184 !important;
    --mist-rgb: 139 155 173 !important;
    --mist-600-rgb: 82 96 113 !important;
    --ink-border-rgb: 31 41 61 !important;
    --ink-800-rgb: 19 25 38 !important;
    --ink-700-rgb: 31 41 61 !important;
    background-color: #131926 !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
    color: #F0EDE6 !important;
}
</style>
