{{-- ── Mobile Bottom Navigation Bar (iOS / Android Native App Experience) ── --}}
@php
    $isHomeActive = request()->routeIs('home');
    $isBooksActive = request()->routeIs('books.*', 'book.*');
    $isLeaderboardActive = request()->routeIs('leaderboard*');
    $isChatActive = request()->routeIs('chat*');
    $isProfileActive = auth()->check() && request()->routeIs('profile.*');
    $isAuthActive = !auth()->check() && request()->routeIs('login', 'register');
@endphp

<nav aria-label="Mobil navigatsiya"
     class="fixed bottom-0 inset-x-0 z-40 lg:hidden bg-ink-950/95 backdrop-blur-xl border-t border-ink-border/80 px-2 pt-1.5 pb-[max(0.5rem,env(safe-area-inset-bottom,0.5rem))] shadow-[0_-4px_20px_rgba(44,38,30,0.08)] dark:shadow-[0_-8px_24px_rgba(0,0,0,0.45)] select-none">
    <div class="max-w-md mx-auto grid grid-cols-5 items-center gap-1">

        {{-- 1. Bosh sahifa --}}
        <a href="{{ route('home') }}"
           class="flex flex-col items-center justify-center py-1 px-1 rounded-btn transition-all duration-base group {{ $isHomeActive ? 'text-amber-700 dark:text-amber-400 font-bold' : 'text-paper/75 dark:text-mist hover:text-paper' }}"
           @if($isHomeActive) aria-current="page" @endif>
            <div class="relative flex items-center justify-center w-7 h-7">
                <svg class="w-5 h-5 transition-transform duration-base group-active:scale-90" viewBox="0 0 24 24" fill="{{ $isHomeActive ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="{{ $isHomeActive ? '1.5' : '1.75' }}" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                @if($isHomeActive)
                    <span class="absolute -top-1 w-1.5 h-1.5 rounded-full bg-amber-600 dark:bg-amber-400"></span>
                @endif
            </div>
            <span class="text-[10px] font-sans tracking-tight mt-0.5 leading-none">
                {{ __('site.nav.home') ?? 'Bosh sahifa' }}
            </span>
        </a>

        {{-- 2. Kitoblar Katalogi --}}
        <a href="{{ route('books.catalog') }}"
           class="flex flex-col items-center justify-center py-1 px-1 rounded-btn transition-all duration-base group {{ $isBooksActive ? 'text-amber-700 dark:text-amber-400 font-bold' : 'text-paper/75 dark:text-mist hover:text-paper' }}"
           @if($isBooksActive) aria-current="page" @endif>
            <div class="relative flex items-center justify-center w-7 h-7">
                <svg class="w-5 h-5 transition-transform duration-base group-active:scale-90" viewBox="0 0 24 24" fill="{{ $isBooksActive ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="{{ $isBooksActive ? '1.5' : '1.75' }}" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                    <path d="M8 7h8"/>
                    <path d="M8 11h6"/>
                </svg>
                @if($isBooksActive)
                    <span class="absolute -top-1 w-1.5 h-1.5 rounded-full bg-amber-600 dark:bg-amber-400"></span>
                @endif
            </div>
            <span class="text-[10px] font-sans tracking-tight mt-0.5 leading-none">
                {{ __('site.nav.books') ?? 'Kitoblar' }}
            </span>
        </a>

        {{-- 3. Reyting --}}
        <a href="{{ route('leaderboard') }}"
           class="flex flex-col items-center justify-center py-1 px-1 rounded-btn transition-all duration-base group {{ $isLeaderboardActive ? 'text-amber-700 dark:text-amber-400 font-bold' : 'text-paper/75 dark:text-mist hover:text-paper' }}"
           @if($isLeaderboardActive) aria-current="page" @endif>
            <div class="relative flex items-center justify-center w-7 h-7">
                <svg class="w-5 h-5 transition-transform duration-base group-active:scale-90" viewBox="0 0 24 24" fill="{{ $isLeaderboardActive ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="{{ $isLeaderboardActive ? '1.5' : '1.75' }}" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                    <path d="M4 22h16"/>
                    <path d="M10 14.66V17c0 .55-.45 1-1 1H7c-.55 0-1-.45-1-1v-2.34"/>
                    <path d="M18 14.66V17c0 .55-.45 1-1 1h-2c-.55 0-1-.45-1-1v-2.34"/>
                    <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
                </svg>
                @if($isLeaderboardActive)
                    <span class="absolute -top-1 w-1.5 h-1.5 rounded-full bg-amber-600 dark:bg-amber-400"></span>
                @endif
            </div>
            <span class="text-[10px] font-sans tracking-tight mt-0.5 leading-none">
                {{ __('site.nav.leaderboard') ?? 'Reyting' }}
            </span>
        </a>

        {{-- 4. Umumiy Chat --}}
        <a href="{{ route('chat') }}"
           class="flex flex-col items-center justify-center py-1 px-1 rounded-btn transition-all duration-base group {{ $isChatActive ? 'text-amber-700 dark:text-amber-400 font-bold' : 'text-paper/75 dark:text-mist hover:text-paper' }}"
           @if($isChatActive) aria-current="page" @endif>
            <div class="relative flex items-center justify-center w-7 h-7">
                <svg class="w-5 h-5 transition-transform duration-base group-active:scale-90" viewBox="0 0 24 24" fill="{{ $isChatActive ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="{{ $isChatActive ? '1.5' : '1.75' }}" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                @if($isChatActive)
                    <span class="absolute -top-1 w-1.5 h-1.5 rounded-full bg-amber-600 dark:bg-amber-400"></span>
                @endif
            </div>
            <span class="text-[10px] font-sans tracking-tight mt-0.5 leading-none">
                {{ __('site.nav.chat') ?? 'Chat' }}
            </span>
        </a>

        {{-- 5. Profil / Kabinet (yoki Mehmon uchun Kirish) --}}
        @auth
            <a href="{{ route('profile.show', auth()->user()->username) }}"
               class="flex flex-col items-center justify-center py-1 px-1 rounded-btn transition-all duration-base group {{ $isProfileActive ? 'text-amber-700 dark:text-amber-400 font-bold' : 'text-paper/75 dark:text-mist hover:text-paper' }}"
               @if($isProfileActive) aria-current="page" @endif>
                <div class="relative flex items-center justify-center w-7 h-7">
                    <img src="{{ auth()->user()->avatar_url }}"
                         class="w-5 h-5 rounded-full object-cover border {{ $isProfileActive ? 'border-amber-600 dark:border-amber-400 ring-2 ring-amber-500/25' : 'border-ink-border' }}"
                         alt="{{ auth()->user()->name }}">
                    @if($isProfileActive)
                        <span class="absolute -top-1 w-1.5 h-1.5 rounded-full bg-amber-600 dark:bg-amber-400"></span>
                    @endif
                </div>
                <span class="text-[10px] font-sans tracking-tight mt-0.5 leading-none truncate max-w-[56px]">
                    {{ __('site.nav.profile') ?? 'Profil' }}
                </span>
            </a>
        @else
            <a href="{{ route('login') }}"
               class="flex flex-col items-center justify-center py-1 px-1 rounded-btn transition-all duration-base group {{ $isAuthActive ? 'text-amber-700 dark:text-amber-400 font-bold' : 'text-paper/75 dark:text-mist hover:text-paper' }}"
               @if($isAuthActive) aria-current="page" @endif>
                <div class="relative flex items-center justify-center w-7 h-7">
                    <svg class="w-5 h-5 transition-transform duration-base group-active:scale-90" viewBox="0 0 24 24" fill="{{ $isAuthActive ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="{{ $isAuthActive ? '1.5' : '1.75' }}" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    @if($isAuthActive)
                        <span class="absolute -top-1 w-1.5 h-1.5 rounded-full bg-amber-600 dark:bg-amber-400"></span>
                    @endif
                </div>
                <span class="text-[10px] font-sans tracking-tight mt-0.5 leading-none">
                    {{ __('site.nav.login') ?? 'Kirish' }}
                </span>
            </a>
        @endauth

    </div>
</nav>
