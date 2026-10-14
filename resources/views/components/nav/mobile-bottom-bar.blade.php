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
     class="fixed bottom-0 inset-x-0 z-40 lg:hidden bg-ink-950/95 backdrop-blur-2xl border-t border-ink-border/80 px-2 pt-1 pb-[max(0.5rem,env(safe-area-inset-bottom,0.5rem))] shadow-[0_-6px_25px_rgba(0,0,0,0.45)] select-none transition-all duration-300">
    <div class="max-w-md mx-auto grid grid-cols-5 items-stretch gap-1 relative">

        {{-- 1. Bosh sahifa --}}
        <a href="{{ route('home') }}"
           class="relative flex flex-col items-center justify-center pt-1.5 pb-1 px-1 rounded-xl transition-all duration-300 ease-out group {{ $isHomeActive ? 'text-amber-500 dark:text-amber-400 font-semibold' : 'text-mist hover:text-paper' }}"
           @if($isHomeActive) aria-current="page" @endif>
            
            {{-- Top glowing accent indicator --}}
            @if($isHomeActive)
                <span class="absolute top-0 inset-x-0 mx-auto w-8 h-[2.5px] rounded-full bg-gradient-to-r from-amber-400 via-amber-300 to-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.8)]"></span>
            @else
                <span class="absolute top-0 inset-x-0 mx-auto w-0 h-[2px] rounded-full bg-amber-400/0 opacity-0 group-hover:w-4 group-hover:bg-amber-400/50 group-hover:opacity-100 transition-all duration-300 ease-out"></span>
            @endif

            {{-- Icon capsule with interactive hover & spring motion --}}
            <div class="relative flex items-center justify-center w-12 h-7 rounded-full transition-all duration-300 ease-out
                {{ $isHomeActive 
                    ? 'bg-amber-500/15 text-amber-500 dark:text-amber-400 shadow-[0_0_14px_rgba(245,158,11,0.25)] border border-amber-500/30' 
                    : 'text-mist/85 group-hover:text-amber-400 group-hover:bg-white/[0.06] dark:group-hover:bg-white/[0.08] group-hover:-translate-y-1 group-hover:shadow-[0_4px_12px_rgba(0,0,0,0.15)]' }}
                group-active:scale-90 group-active:translate-y-0.5">
                <svg class="w-5 h-5 transition-all duration-300 ease-out group-hover:scale-110 {{ $isHomeActive ? 'scale-105' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $isHomeActive ? '2.25' : '1.75' }}" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" fill="{{ $isHomeActive ? 'currentColor' : 'none' }}" fill-opacity="{{ $isHomeActive ? '0.2' : '0' }}"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
            <span class="text-[10px] font-sans tracking-tight mt-1 leading-none transition-all duration-200 {{ $isHomeActive ? 'text-amber-500 dark:text-amber-400 font-bold' : 'text-mist/75 group-hover:text-paper group-hover:scale-105' }}">
                {{ __('site.nav.home') ?? 'Bosh sahifa' }}
            </span>
        </a>

        {{-- 2. Kitoblar Katalogi --}}
        <a href="{{ route('books.catalog') }}"
           class="relative flex flex-col items-center justify-center pt-1.5 pb-1 px-1 rounded-xl transition-all duration-300 ease-out group {{ $isBooksActive ? 'text-amber-500 dark:text-amber-400 font-semibold' : 'text-mist hover:text-paper' }}"
           @if($isBooksActive) aria-current="page" @endif>

            {{-- Top glowing accent indicator --}}
            @if($isBooksActive)
                <span class="absolute top-0 inset-x-0 mx-auto w-8 h-[2.5px] rounded-full bg-gradient-to-r from-amber-400 via-amber-300 to-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.8)]"></span>
            @else
                <span class="absolute top-0 inset-x-0 mx-auto w-0 h-[2px] rounded-full bg-amber-400/0 opacity-0 group-hover:w-4 group-hover:bg-amber-400/50 group-hover:opacity-100 transition-all duration-300 ease-out"></span>
            @endif

            <div class="relative flex items-center justify-center w-12 h-7 rounded-full transition-all duration-300 ease-out
                {{ $isBooksActive 
                    ? 'bg-amber-500/15 text-amber-500 dark:text-amber-400 shadow-[0_0_14px_rgba(245,158,11,0.25)] border border-amber-500/30' 
                    : 'text-mist/85 group-hover:text-amber-400 group-hover:bg-white/[0.06] dark:group-hover:bg-white/[0.08] group-hover:-translate-y-1 group-hover:shadow-[0_4px_12px_rgba(0,0,0,0.15)]' }}
                group-active:scale-90 group-active:translate-y-0.5">
                <svg class="w-5 h-5 transition-all duration-300 ease-out group-hover:scale-110 {{ $isBooksActive ? 'scale-105' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $isBooksActive ? '2.25' : '1.75' }}" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20" fill="{{ $isBooksActive ? 'currentColor' : 'none' }}" fill-opacity="{{ $isBooksActive ? '0.2' : '0' }}"/>
                    <path d="M8 7h8"/>
                    <path d="M8 11h6"/>
                </svg>
            </div>
            <span class="text-[10px] font-sans tracking-tight mt-1 leading-none transition-all duration-200 {{ $isBooksActive ? 'text-amber-500 dark:text-amber-400 font-bold' : 'text-mist/75 group-hover:text-paper group-hover:scale-105' }}">
                {{ __('site.nav.books') ?? 'Kitoblar' }}
            </span>
        </a>

        {{-- 3. Reyting --}}
        <a href="{{ route('leaderboard') }}"
           class="relative flex flex-col items-center justify-center pt-1.5 pb-1 px-1 rounded-xl transition-all duration-300 ease-out group {{ $isLeaderboardActive ? 'text-amber-500 dark:text-amber-400 font-semibold' : 'text-mist hover:text-paper' }}"
           @if($isLeaderboardActive) aria-current="page" @endif>

            {{-- Top glowing accent indicator --}}
            @if($isLeaderboardActive)
                <span class="absolute top-0 inset-x-0 mx-auto w-8 h-[2.5px] rounded-full bg-gradient-to-r from-amber-400 via-amber-300 to-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.8)]"></span>
            @else
                <span class="absolute top-0 inset-x-0 mx-auto w-0 h-[2px] rounded-full bg-amber-400/0 opacity-0 group-hover:w-4 group-hover:bg-amber-400/50 group-hover:opacity-100 transition-all duration-300 ease-out"></span>
            @endif

            <div class="relative flex items-center justify-center w-12 h-7 rounded-full transition-all duration-300 ease-out
                {{ $isLeaderboardActive 
                    ? 'bg-amber-500/15 text-amber-500 dark:text-amber-400 shadow-[0_0_14px_rgba(245,158,11,0.25)] border border-amber-500/30' 
                    : 'text-mist/85 group-hover:text-amber-400 group-hover:bg-white/[0.06] dark:group-hover:bg-white/[0.08] group-hover:-translate-y-1 group-hover:shadow-[0_4px_12px_rgba(0,0,0,0.15)]' }}
                group-active:scale-90 group-active:translate-y-0.5">
                <svg class="w-5 h-5 transition-all duration-300 ease-out group-hover:scale-110 {{ $isLeaderboardActive ? 'scale-105' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $isLeaderboardActive ? '2.25' : '1.75' }}" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                    <path d="M4 22h16"/>
                    <path d="M10 14.66V17c0 .55-.45 1-1 1H7c-.55 0-1-.45-1-1v-2.34"/>
                    <path d="M18 14.66V17c0 .55-.45 1-1 1h-2c-.55 0-1-.45-1-1v-2.34"/>
                    <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z" fill="{{ $isLeaderboardActive ? 'currentColor' : 'none' }}" fill-opacity="{{ $isLeaderboardActive ? '0.2' : '0' }}"/>
                </svg>
            </div>
            <span class="text-[10px] font-sans tracking-tight mt-1 leading-none transition-all duration-200 {{ $isLeaderboardActive ? 'text-amber-500 dark:text-amber-400 font-bold' : 'text-mist/75 group-hover:text-paper group-hover:scale-105' }}">
                {{ __('site.nav.leaderboard') ?? 'Reyting' }}
            </span>
        </a>

        {{-- 4. Umumiy Chat --}}
        <a href="{{ route('chat') }}"
           class="relative flex flex-col items-center justify-center pt-1.5 pb-1 px-1 rounded-xl transition-all duration-300 ease-out group {{ $isChatActive ? 'text-amber-500 dark:text-amber-400 font-semibold' : 'text-mist hover:text-paper' }}"
           @if($isChatActive) aria-current="page" @endif>

            {{-- Top glowing accent indicator --}}
            @if($isChatActive)
                <span class="absolute top-0 inset-x-0 mx-auto w-8 h-[2.5px] rounded-full bg-gradient-to-r from-amber-400 via-amber-300 to-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.8)]"></span>
            @else
                <span class="absolute top-0 inset-x-0 mx-auto w-0 h-[2px] rounded-full bg-amber-400/0 opacity-0 group-hover:w-4 group-hover:bg-amber-400/50 group-hover:opacity-100 transition-all duration-300 ease-out"></span>
            @endif

            <div class="relative flex items-center justify-center w-12 h-7 rounded-full transition-all duration-300 ease-out
                {{ $isChatActive 
                    ? 'bg-amber-500/15 text-amber-500 dark:text-amber-400 shadow-[0_0_14px_rgba(245,158,11,0.25)] border border-amber-500/30' 
                    : 'text-mist/85 group-hover:text-amber-400 group-hover:bg-white/[0.06] dark:group-hover:bg-white/[0.08] group-hover:-translate-y-1 group-hover:shadow-[0_4px_12px_rgba(0,0,0,0.15)]' }}
                group-active:scale-90 group-active:translate-y-0.5">
                <svg class="w-5 h-5 transition-all duration-300 ease-out group-hover:scale-110 {{ $isChatActive ? 'scale-105' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $isChatActive ? '2.25' : '1.75' }}" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" fill="{{ $isChatActive ? 'currentColor' : 'none' }}" fill-opacity="{{ $isChatActive ? '0.2' : '0' }}"/>
                </svg>
            </div>
            <span class="text-[10px] font-sans tracking-tight mt-1 leading-none transition-all duration-200 {{ $isChatActive ? 'text-amber-500 dark:text-amber-400 font-bold' : 'text-mist/75 group-hover:text-paper group-hover:scale-105' }}">
                {{ __('site.nav.chat') ?? 'Chat' }}
            </span>
        </a>

        {{-- 5. Profil / Kabinet (yoki Mehmon uchun Kirish) --}}
        @auth
            <a href="{{ route('profile.show', auth()->user()->username) }}"
               class="relative flex flex-col items-center justify-center pt-1.5 pb-1 px-1 rounded-xl transition-all duration-300 ease-out group {{ $isProfileActive ? 'text-amber-500 dark:text-amber-400 font-semibold' : 'text-mist hover:text-paper' }}"
               @if($isProfileActive) aria-current="page" @endif>

                {{-- Top glowing accent indicator --}}
                @if($isProfileActive)
                    <span class="absolute top-0 inset-x-0 mx-auto w-8 h-[2.5px] rounded-full bg-gradient-to-r from-amber-400 via-amber-300 to-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.8)]"></span>
                @else
                    <span class="absolute top-0 inset-x-0 mx-auto w-0 h-[2px] rounded-full bg-amber-400/0 opacity-0 group-hover:w-4 group-hover:bg-amber-400/50 group-hover:opacity-100 transition-all duration-300 ease-out"></span>
                @endif

                <div class="relative flex items-center justify-center w-12 h-7 rounded-full transition-all duration-300 ease-out
                    {{ $isProfileActive 
                        ? 'bg-amber-500/15 text-amber-500 dark:text-amber-400 shadow-[0_0_14px_rgba(245,158,11,0.25)] border border-amber-500/30' 
                        : 'group-hover:bg-white/[0.06] dark:group-hover:bg-white/[0.08] group-hover:-translate-y-1 group-hover:shadow-[0_4px_12px_rgba(0,0,0,0.15)]' }}
                    group-active:scale-90 group-active:translate-y-0.5">
                    <img src="{{ auth()->user()->avatar_url }}"
                         class="w-5 h-5 rounded-full object-cover transition-all duration-300 ease-out group-hover:scale-110 {{ $isProfileActive ? 'ring-2 ring-amber-400 ring-offset-2 ring-offset-ink-950 shadow-[0_0_10px_rgba(245,158,11,0.45)]' : 'border border-ink-border group-hover:border-amber-400/70' }}"
                         alt="{{ auth()->user()->name }}">
                </div>
                <span class="text-[10px] font-sans tracking-tight mt-1 leading-none truncate max-w-[56px] transition-all duration-200 {{ $isProfileActive ? 'text-amber-500 dark:text-amber-400 font-bold' : 'text-mist/75 group-hover:text-paper group-hover:scale-105' }}">
                    {{ __('site.nav.profile') ?? 'Profil' }}
                </span>
            </a>
        @else
            <a href="{{ route('login') }}"
               class="relative flex flex-col items-center justify-center pt-1.5 pb-1 px-1 rounded-xl transition-all duration-300 ease-out group {{ $isAuthActive ? 'text-amber-500 dark:text-amber-400 font-semibold' : 'text-mist hover:text-paper' }}"
               @if($isAuthActive) aria-current="page" @endif>

                {{-- Top glowing accent indicator --}}
                @if($isAuthActive)
                    <span class="absolute top-0 inset-x-0 mx-auto w-8 h-[2.5px] rounded-full bg-gradient-to-r from-amber-400 via-amber-300 to-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.8)]"></span>
                @else
                    <span class="absolute top-0 inset-x-0 mx-auto w-0 h-[2px] rounded-full bg-amber-400/0 opacity-0 group-hover:w-4 group-hover:bg-amber-400/50 group-hover:opacity-100 transition-all duration-300 ease-out"></span>
                @endif

                <div class="relative flex items-center justify-center w-12 h-7 rounded-full transition-all duration-300 ease-out
                    {{ $isAuthActive 
                        ? 'bg-amber-500/15 text-amber-500 dark:text-amber-400 shadow-[0_0_14px_rgba(245,158,11,0.25)] border border-amber-500/30' 
                        : 'text-mist/85 group-hover:text-amber-400 group-hover:bg-white/[0.06] dark:group-hover:bg-white/[0.08] group-hover:-translate-y-1 group-hover:shadow-[0_4px_12px_rgba(0,0,0,0.15)]' }}
                    group-active:scale-90 group-active:translate-y-0.5">
                    <svg class="w-5 h-5 transition-all duration-300 ease-out group-hover:scale-110 {{ $isAuthActive ? 'scale-105' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $isAuthActive ? '2.25' : '1.75' }}" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4" fill="{{ $isAuthActive ? 'currentColor' : 'none' }}" fill-opacity="{{ $isAuthActive ? '0.2' : '0' }}"/>
                    </svg>
                </div>
                <span class="text-[10px] font-sans tracking-tight mt-1 leading-none transition-all duration-200 {{ $isAuthActive ? 'text-amber-500 dark:text-amber-400 font-bold' : 'text-mist/75 group-hover:text-paper group-hover:scale-105' }}">
                    {{ __('site.nav.login') ?? 'Kirish' }}
                </span>
            </a>
        @endauth

    </div>
</nav>
