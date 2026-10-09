@props([
    'user'      => null,
    'size'      => 'md',           // 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl'
    'shape'     => 'rounded-card', // 'rounded-card' (8px) or 'rounded-full'
    'showRank'  => true,
    'rank'      => null,           // optional override
    'class'     => '',
    'link'      => false,          // if true, links to profile
    'href'      => null,           // custom link url override
])

@php
    $sizeClasses = match($size) {
        'xs'  => 'w-6 h-6 text-[10px]',
        'sm'  => 'w-8 h-8 text-xs',
        'md'  => 'w-10 h-10 text-sm',
        'lg'  => 'w-12 h-12 text-base',
        'xl'  => 'w-16 h-16 text-xl',
        '2xl' => 'w-20 h-20 text-2xl',
        '3xl' => 'w-24 h-24 sm:w-28 sm:h-28 text-3xl',
        default => 'w-10 h-10 text-sm',
    };

    $badgeSizes = match($size) {
        'xs'  => 'w-3 h-3 -top-1 -right-1 text-[8px]',
        'sm'  => 'w-4 h-4 -top-1.5 -right-1.5 text-[9px]',
        'md'  => 'w-4.5 h-4.5 -top-1.5 -right-1.5 text-[10px]',
        'lg'  => 'w-5 h-5 -top-2 -right-2 text-xs',
        'xl'  => 'w-6 h-6 -top-2 -right-2 text-xs',
        '2xl' => 'w-7 h-7 -top-2.5 -right-2.5 text-sm',
        '3xl' => 'w-8 h-8 -top-3 -right-3 text-sm',
        default => 'w-4.5 h-4.5 -top-1.5 -right-1.5 text-[10px]',
    };

    $userRank = null;
    if ($showRank && $user) {
        if ($rank !== null) {
            $userRank = (int) $rank;
        } else {
            // Tezkor keshdan top-5 ro'yxatini olish
            $topFive = app(\App\Services\LeaderboardService::class)->getTopFiveIds();
            $userRank = $topFive[$user->id] ?? null;
        }
    }

    $tier = ($userRank >= 1 && $userRank <= 5) ? match($userRank) {
        1       => 'gold',
        2       => 'silver',
        3       => 'bronze',
        default => 'top5',
    } : null;

    $ringClasses = match($tier) {
        'gold'   => 'ring-2 ring-amber-500 dark:ring-[#F59E0B] shadow-sm dark:shadow-[0_0_12px_rgba(245,158,11,0.35)]',
        'silver' => 'ring-2 ring-slate-400 dark:ring-[#E2E8F0] shadow-sm dark:shadow-[0_0_10px_rgba(226,232,240,0.25)]',
        'bronze' => 'ring-2 ring-amber-700 dark:ring-[#D97706] shadow-sm dark:shadow-[0_0_10px_rgba(217,119,6,0.25)]',
        'top5'   => 'ring-2 ring-indigo-500 dark:ring-[#6366F1]/80 shadow-sm dark:shadow-[0_0_8px_rgba(99,102,241,0.25)]',
        default  => 'border border-ink-border',
    };

    $avatarUrl = $user?->avatar_url;
    $userName = $user?->name ?? 'Foydalanuvchi';
    $initial = mb_substr($userName, 0, 1);

    $targetUrl = $href ?? (($link && $user && !empty($user->username)) ? route('profile.show', $user->username) : null);
@endphp

@if($targetUrl)
<a href="{{ $targetUrl }}"
   title="{{ $userName }} profilini ko'rish"
   class="relative inline-block shrink-0 select-none {{ $class }} hover:opacity-90 hover:scale-105 active:scale-95 transition-all duration-150 cursor-pointer">
@else
<div class="relative inline-block shrink-0 select-none {{ $class }}">
@endif

    @if($avatarUrl)
        <img src="{{ $avatarUrl }}"
             alt="{{ $userName }}"
             class="{{ $sizeClasses }} {{ $shape }} object-cover bg-ink-950 {{ $ringClasses }} transition-transform duration-200">
    @else
        <div class="{{ $sizeClasses }} {{ $shape }} bg-gradient-to-br from-ink-800 to-ink-950 flex items-center justify-center font-bold font-serif text-paper {{ $ringClasses }}">
            <span>{{ $initial }}</span>
        </div>
    @endif

    {{-- Top 5 Plashka / Floating Rank Badge --}}
    @if($showRank && $tier)
        <div class="absolute {{ $badgeSizes }} rounded-full flex items-center justify-center shadow-md z-10 animate-fade-in
            {{ $tier === 'gold'   ? 'bg-amber-500 text-ink-950 ring-1 ring-amber-600/40 dark:ring-white/40' : '' }}
            {{ $tier === 'silver' ? 'bg-slate-300 dark:bg-[#E2E8F0] text-slate-800 dark:text-ink-950 ring-1 ring-slate-400/60 dark:ring-white/40' : '' }}
            {{ $tier === 'bronze' ? 'bg-amber-700 text-white ring-1 ring-amber-800/40 dark:ring-white/30' : '' }}
            {{ $tier === 'top5'   ? 'bg-indigo-600 text-white ring-1 ring-indigo-700/40 dark:ring-white/30' : '' }}"
            title="{{ $userRank }}-o'rin (Reyting)">
            @if($tier === 'gold')
                <svg class="w-3/5 h-3/5" viewBox="0 0 24 24" fill="currentColor"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>
            @elseif($tier === 'silver' || $tier === 'bronze')
                <span class="font-mono font-black leading-none">{{ $userRank }}</span>
            @else
                <svg class="w-3/5 h-3/5" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            @endif
        </div>
    @endif

@if($targetUrl)
</a>
@else
</div>
@endif
