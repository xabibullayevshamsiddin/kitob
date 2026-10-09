@props([
    'rank'    => null,
    'size'    => 'sm',     // 'xs', 'sm', 'md', 'lg'
    'compact' => false,    // faqat mini-badge (chat yoki ro'yxatlar uchun)
])

@php
    $rank = (int) $rank;
    if ($rank < 1 || $rank > 5) {
        return;
    }

    $tier = match($rank) {
        1       => 'gold',
        2       => 'silver',
        3       => 'bronze',
        default => 'top5',
    };

    $containerClasses = match($tier) {
        'gold'   => 'bg-amber-500/15 dark:bg-[#F59E0B]/15 border border-amber-500/40 dark:border-[#F59E0B]/40 text-amber-700 dark:text-[#F59E0B] shadow-sm dark:shadow-[0_0_10px_rgba(245,158,11,0.2)]',
        'silver' => 'bg-slate-200/90 dark:bg-[#E2E8F0]/10 border border-slate-400/60 dark:border-[#E2E8F0]/40 text-slate-700 dark:text-[#E2E8F0] shadow-sm dark:shadow-[0_0_8px_rgba(226,232,240,0.15)]',
        'bronze' => 'bg-amber-700/15 dark:bg-[#D97706]/15 border border-amber-700/35 dark:border-[#D97706]/40 text-amber-800 dark:text-[#D97706] shadow-sm dark:shadow-[0_0_8px_rgba(217,119,6,0.15)]',
        'top5'   => 'bg-indigo-500/15 dark:bg-[#6366F1]/10 border border-indigo-500/35 dark:border-[#6366F1]/30 text-indigo-700 dark:text-[#818CF8]',
    };

    $iconSizes = match($size) {
        'xs' => 'w-2.5 h-2.5',
        'sm' => 'w-3 h-3',
        'md' => 'w-3.5 h-3.5',
        'lg' => 'w-4 h-4',
        default => 'w-3 h-3',
    };

    $textSizes = match($size) {
        'xs' => 'text-[9px] px-1 py-0.2',
        'sm' => 'text-[10px] px-1.5 py-0.5',
        'md' => 'text-xs px-2 py-0.5',
        'lg' => 'text-xs sm:text-sm px-2.5 py-1',
        default => 'text-[10px] px-1.5 py-0.5',
    };
@endphp

<span class="inline-flex items-center gap-1 font-mono font-bold rounded {{ $containerClasses }} {{ $textSizes }} select-none shrink-0"
      title="{{ $rank }}-o'rin (Peshqadam)">
    {{-- 1-o'rin: Toj (Crown) --}}
    @if($tier === 'gold')
        <svg class="{{ $iconSizes }} shrink-0 text-amber-600 dark:text-[#F59E0B]" viewBox="0 0 24 24" fill="currentColor">
            <path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5zm14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/>
        </svg>
    {{-- 2-o'rin: Kumush medal --}}
    @elseif($tier === 'silver')
        <svg class="{{ $iconSizes }} shrink-0 text-slate-700 dark:text-[#E2E8F0]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="8" r="6"/>
            <path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>
        </svg>
    {{-- 3-o'rin: Bronza medal --}}
    @elseif($tier === 'bronze')
        <svg class="{{ $iconSizes }} shrink-0 text-amber-800 dark:text-[#D97706]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="8" r="6"/>
            <path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>
        </svg>
    {{-- 4 va 5-o'rin: Top 5 Yulduz (Star) --}}
    @else
        <svg class="{{ $iconSizes }} shrink-0 text-indigo-700 dark:text-[#818CF8]" viewBox="0 0 24 24" fill="currentColor">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
        </svg>
    @endif

    @if(!$compact)
        @if($tier === 'gold')
            <span>1-o'rin</span>
        @elseif($tier === 'silver')
            <span>2-o'rin</span>
        @elseif($tier === 'bronze')
            <span>3-o'rin</span>
        @else
            <span>Top 5 (#{{ $rank }})</span>
        @endif
    @else
        <span>#{{ $rank }}</span>
    @endif
</span>
