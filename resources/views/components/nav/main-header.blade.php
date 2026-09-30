{{-- ── Universal Header: barcha sahifalar uchun yagona navigatsiya ── --}}
<header id="site-header" x-data="{ mobileMenu: false, moreOpen: false }" class="sticky top-0 z-30 w-full backdrop-blur-xl bg-ink-950/80 border-b border-white/[0.07] transition-all">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 h-[64px] flex items-center justify-between gap-3">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 group shrink-0">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-ink-950 font-black text-lg shadow-lg shadow-amber-500/25 group-hover:scale-105 transition-all duration-300">
                📖
            </div>
            <div class="hidden sm:flex flex-col leading-none">
                <span class="font-bold text-white text-sm tracking-tight group-hover:text-amber-400 transition-colors">Kitobxon</span>
                <span class="font-mono text-[9px] text-slate-400 uppercase tracking-widest">{{ __('site.common.platform') }}</span>
            </div>
        </a>

        {{-- Desktop Nav — asosiy linklar --}}
        <nav class="hidden lg:flex items-center gap-1 text-[13px] font-medium text-slate-300 flex-1 justify-center">
            <a href="{{ route('books.catalog') }}"   class="px-2.5 py-1.5 rounded-lg hover:text-amber-400 hover:bg-white/5 transition-colors whitespace-nowrap">{{ __('site.nav.books') }}</a>
            <a href="{{ route('leaderboard') }}"     class="px-2.5 py-1.5 rounded-lg hover:text-amber-400 hover:bg-white/5 transition-colors whitespace-nowrap">{{ __('site.nav.leaderboard') }}</a>
            <a href="{{ route('chat') }}"            class="px-2.5 py-1.5 rounded-lg hover:text-amber-400 hover:bg-white/5 transition-colors whitespace-nowrap">{{ __('site.nav.chat') }}</a>
            <a href="{{ route('groups.index') }}"   class="px-2.5 py-1.5 rounded-lg hover:text-amber-400 hover:bg-white/5 transition-colors whitespace-nowrap">{{ __('site.nav.groups') }}</a>
            <a href="{{ route('live.index') }}"     class="px-2.5 py-1.5 rounded-lg hover:text-amber-400 hover:bg-white/5 transition-colors whitespace-nowrap">{{ __('site.nav.live') }}</a>

            {{-- "Ko'proq" dropdown --}}
            <div class="relative" x-data @click.outside="moreOpen = false">
                <button @click="moreOpen = !moreOpen"
                        class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg hover:text-amber-400 hover:bg-white/5 transition-colors whitespace-nowrap">
                    <span>{{ __('site.nav.more') ?? 'Ko\'proq' }}</span>
                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-150" :class="moreOpen ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="moreOpen" x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute top-full left-0 mt-2 w-44 bg-slate-800 border border-slate-700/60 rounded-2xl shadow-xl py-2 z-50">
                    <a href="{{ route('about') }}"   @click="moreOpen=false" class="block px-4 py-2 text-xs text-slate-300 hover:text-amber-400 hover:bg-white/5 transition-colors">{{ __('site.nav.about') }}</a>
                    <a href="{{ route('faq') }}"     @click="moreOpen=false" class="block px-4 py-2 text-xs text-slate-300 hover:text-amber-400 hover:bg-white/5 transition-colors">{{ __('site.nav.faq') }}</a>
                    <a href="{{ route('contact') }}" @click="moreOpen=false" class="block px-4 py-2 text-xs text-slate-300 hover:text-amber-400 hover:bg-white/5 transition-colors">{{ __('site.nav.contact') }}</a>
                </div>
            </div>
        </nav>

        {{-- Right side actions --}}
        <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">

            {{-- Til almashtirgich --}}
            <x-lang-switcher class="hidden sm:block" />

            @auth
                {{-- Streak / Points / Coins — faqat keng ekranlarda --}}
                <div class="hidden xl:flex items-center gap-1.5">
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-amber-400/10 border border-amber-400/25 text-amber-400 font-mono text-[11px] font-bold" title="{{ __('site.common.streak') }}">
                        🔥 {{ auth()->user()->current_streak }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-white/5 border border-white/10 text-slate-200 font-mono text-[11px] font-bold" title="{{ __('site.common.points') }}">
                        ⭐ {{ number_format(auth()->user()->total_points) }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-white/5 border border-white/10 text-slate-200 font-mono text-[11px] font-bold" title="{{ __('site.common.coins') }}">
                        🪙 {{ number_format(auth()->user()->coin_balance) }}
                    </span>
                </div>

                {{-- Bildirishnomalar --}}
                <a href="{{ route('notifications') }}" class="hidden md:inline-flex relative p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 transition-colors" title="{{ __('site.nav.notifications') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-ink-950"></span>
                    @endif
                </a>

                {{-- Admin / Teacher panel tugmasi --}}
                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}" class="hidden md:inline-flex px-3 py-1.5 rounded-xl bg-amber-500 text-ink-950 font-bold text-[11px] uppercase tracking-wider hover:bg-amber-400 active:scale-95 transition-all shadow-md shadow-amber-500/20 whitespace-nowrap">
                        {{ __('site.nav.dashboard') }} →
                    </a>
                @elseif(auth()->user()->hasRole('teacher'))
                    <a href="{{ route('teacher.dashboard') }}" class="hidden md:inline-flex px-3 py-1.5 rounded-xl bg-amber-500 text-ink-950 font-bold text-[11px] uppercase tracking-wider hover:bg-amber-400 active:scale-95 transition-all shadow-md shadow-amber-500/20 whitespace-nowrap">
                        {{ __('site.nav.teacher_panel') }} →
                    </a>
                @endif

                {{-- Avatar Dropdown --}}
                <div x-data="{ userMenuOpen: false }" x-on:click.outside="userMenuOpen = false" class="relative">
                    <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-1.5 px-2 py-1.5 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors focus:outline-none">
                        <img src="{{ auth()->user()->avatar_url }}" class="w-7 h-7 rounded-full object-cover ring-1 ring-amber-400/40" alt="{{ auth()->user()->name }}">
                        <span class="text-xs font-semibold text-slate-200 hidden lg:inline max-w-[80px] truncate">{{ auth()->user()->name }}</span>
                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="userMenuOpen" x-cloak
                         x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-slate-800/95 backdrop-blur-xl border border-slate-700/60 rounded-2xl shadow-xl py-2 z-50 overflow-hidden">
                        <div class="px-4 py-2 border-b border-white/10">
                            <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] font-mono text-slate-500 truncate">{{ '@' . auth()->user()->username }}</p>
                        </div>
                        {{-- Stats (kichik ekran uchun) --}}
                        <div class="px-4 py-2 border-b border-white/10 flex items-center gap-3 xl:hidden">
                            <span class="text-amber-400 font-mono text-[11px] font-bold">🔥 {{ auth()->user()->current_streak }}</span>
                            <span class="text-slate-200 font-mono text-[11px] font-bold">⭐ {{ number_format(auth()->user()->total_points) }}</span>
                            <span class="text-slate-200 font-mono text-[11px] font-bold">🪙 {{ number_format(auth()->user()->coin_balance) }}</span>
                        </div>
                        <a href="{{ route('profile.show', auth()->user()->username) }}" class="block px-4 py-2 text-xs text-slate-300 hover:text-amber-400 hover:bg-white/5 transition-colors">{{ __('site.nav.my_profile') }}</a>
                        <a href="{{ route('settings') }}" class="block px-4 py-2 text-xs text-slate-300 hover:text-amber-400 hover:bg-white/5 transition-colors">{{ __('site.nav.settings') }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-xs text-rose-400 hover:bg-rose-500/10 transition-colors">{{ __('site.nav.logout') }}</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}"    class="px-3 py-1.5 text-sm font-semibold text-slate-300 hover:text-white transition-colors whitespace-nowrap">{{ __('site.nav.login') }}</a>
                <a href="{{ route('register') }}" class="hidden sm:inline-flex px-4 py-2 rounded-xl bg-white text-ink-950 font-bold text-xs uppercase tracking-wider hover:bg-slate-200 active:scale-95 transition-all shadow-md whitespace-nowrap">
                    {{ __('site.nav.register') }}
                </a>
            @endauth

            {{-- Hamburger (mobil) --}}
            <button @click="mobileMenu = !mobileMenu" class="lg:hidden p-2 rounded-lg text-slate-400 hover:text-white focus:outline-none" aria-label="{{ __('site.nav.menu') }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    <path x-show="mobileMenu"  stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="mobileMenu" x-cloak @click.away="mobileMenu = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="lg:hidden border-b border-white/10 bg-slate-900/98 backdrop-blur-xl px-6 py-4 space-y-1">
        <a href="{{ route('books.catalog') }}"  @click="mobileMenu = false" class="block text-sm py-2.5 text-slate-200 hover:text-amber-400 border-b border-white/5">{{ __('site.nav.books') }}</a>
        <a href="{{ route('leaderboard') }}"    @click="mobileMenu = false" class="block text-sm py-2.5 text-slate-200 hover:text-amber-400 border-b border-white/5">{{ __('site.nav.leaderboard') }}</a>
        <a href="{{ route('chat') }}"           @click="mobileMenu = false" class="block text-sm py-2.5 text-slate-200 hover:text-amber-400 border-b border-white/5">{{ __('site.nav.chat') }}</a>
        <a href="{{ route('groups.index') }}"  @click="mobileMenu = false" class="block text-sm py-2.5 text-slate-200 hover:text-amber-400 border-b border-white/5">{{ __('site.nav.groups') }}</a>
        <a href="{{ route('live.index') }}"    @click="mobileMenu = false" class="block text-sm py-2.5 text-slate-200 hover:text-amber-400 border-b border-white/5">{{ __('site.nav.live') }}</a>
        <a href="{{ route('about') }}"         @click="mobileMenu = false" class="block text-sm py-2.5 text-slate-200 hover:text-amber-400 border-b border-white/5">{{ __('site.nav.about') }}</a>
        <a href="{{ route('faq') }}"           @click="mobileMenu = false" class="block text-sm py-2.5 text-slate-200 hover:text-amber-400 border-b border-white/5">{{ __('site.nav.faq') }}</a>
        <a href="{{ route('contact') }}"       @click="mobileMenu = false" class="block text-sm py-2.5 text-slate-200 hover:text-amber-400">{{ __('site.nav.contact') }}</a>

        <div class="pt-3 mt-2 border-t border-white/10 space-y-2">
            @auth
                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}" @click="mobileMenu = false" class="block w-full text-center px-4 py-2.5 rounded-xl bg-amber-500 text-ink-950 font-bold text-xs uppercase tracking-wider">{{ __('site.nav.dashboard') }} →</a>
                @elseif(auth()->user()->hasRole('teacher'))
                    <a href="{{ route('teacher.dashboard') }}" @click="mobileMenu = false" class="block w-full text-center px-4 py-2.5 rounded-xl bg-amber-500 text-ink-950 font-bold text-xs uppercase tracking-wider">{{ __('site.nav.teacher_panel') }} →</a>
                @endif
                <a href="{{ route('profile.show', auth()->user()->username) }}" @click="mobileMenu = false" class="block text-sm py-1.5 text-slate-300 hover:text-amber-400">{{ __('site.nav.my_profile') }}</a>
                <a href="{{ route('settings') }}" @click="mobileMenu = false" class="block text-sm py-1.5 text-slate-300 hover:text-amber-400">{{ __('site.nav.settings') }}</a>
                <form method="POST" action="{{ route('logout') }}" class="pt-1">
                    @csrf
                    <button type="submit" class="text-xs text-rose-400 hover:underline">{{ __('site.nav.logout') }}</button>
                </form>
            @else
                <a href="{{ route('login') }}"    @click="mobileMenu = false" class="block text-sm py-2 text-slate-200 hover:text-white">{{ __('site.nav.login') }}</a>
                <a href="{{ route('register') }}" @click="mobileMenu = false" class="block w-full text-center px-4 py-2.5 rounded-xl bg-white text-ink-950 font-bold text-xs uppercase tracking-wider">{{ __('site.nav.register') }}</a>
            @endauth

            {{-- Til almashtirgich (mobil) --}}
            <div class="pt-2 flex items-center gap-2">
                <span class="text-xs text-slate-500">{{ __('site.common.language') }}:</span>
                @foreach(['uz', 'ru', 'en'] as $code)
                    <a href="{{ url()->current() }}?lang={{ $code }}"
                       class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors {{ app()->getLocale() === $code ? 'bg-amber-400/15 text-amber-400 border border-amber-400/30' : 'text-slate-400 hover:text-white border border-white/10' }}">
                       {{ strtoupper($code) }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</header>
