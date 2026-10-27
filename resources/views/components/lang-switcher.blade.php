{{-- Til almashtirgich: uz / ru / en --}}
@php
    $currentLocale = app()->getLocale();
    $languages = [
        'uz' => ['code' => 'UZ', 'name' => "O'zbekcha", 'flag' => 'uz'],
        'ru' => ['code' => 'RU', 'name' => 'Русский',   'flag' => 'ru'],
        'en' => ['code' => 'EN', 'name' => 'English',   'flag' => 'gb'],
    ];
    $current = $languages[$currentLocale] ?? $languages['uz'];
@endphp

<div x-data="{ langOpen: false }" x-on:click.outside="langOpen = false" {{ $attributes->merge(['class' => 'relative']) }}>

    {{-- Trigger button --}}
    <button
        type="button"
        @click.stop="langOpen = !langOpen"
        class="header-lang-btn flex items-center gap-2 px-2.5 py-1.5 rounded-btn bg-ink-800 border border-ink-border hover:bg-ink-700 active:scale-[0.98] transition-all text-xs font-semibold text-paper focus:outline-none select-none shadow-sm"
        aria-label="Tilni tanlang"
    >
        <x-flag :country="$current['flag'] ?? 'uz'" class="w-4.5 h-3 rounded-[2px] shadow-xs" />
        <span class="font-mono text-[11px] tracking-wide">{{ $current['code'] }}</span>
        <svg
            class="w-3 h-3 text-mist transition-transform duration-200"
            :class="langOpen ? 'rotate-180 text-amber-500 dark:text-amber-400' : ''"
            fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Dropdown --}}
    <div
        x-show="langOpen"
        x-transition:enter="ks-menu-blur-enter-active"
        x-transition:enter-start="ks-menu-blur-enter"
        x-transition:enter-end="ks-menu-blur-enter-end"
        x-transition:leave="ks-menu-blur-leave-active"
        x-transition:leave-start="ks-menu-blur-leave"
        x-transition:leave-end="ks-menu-blur-leave-end"
        x-cloak
        class="header-dropdown-menu absolute right-0 mt-2.5 w-48 p-1.5 bg-ink-800 border border-ink-border rounded-panel shadow-popover z-[99999]"
    >
        <div class="px-2.5 py-1 text-[10px] font-mono uppercase tracking-wider text-mist border-b border-ink-border mb-1 flex items-center justify-between">
            <span>Til / Language</span>
            <span class="text-[9px] text-amber-700 dark:text-amber-400 font-mono font-bold">{{ $current['code'] }}</span>
        </div>

        <div class="space-y-0.5">
            @foreach($languages as $code => $lang)
                @php $isActive = ($code === $currentLocale); @endphp
                <a
                    href="{{ url()->current() }}?lang={{ $code }}"
                    class="flex items-center justify-between px-3 py-2 text-xs rounded-btn transition-all {{ $isActive ? 'bg-amber-500/15 text-amber-800 dark:text-amber-400 font-bold border border-amber-500/30' : 'text-paper/85 dark:text-mist hover:text-paper hover:bg-ink-700/60' }}"
                    @click="langOpen = false"
                >
                    <div class="flex items-center gap-2.5">
                        <x-flag :country="$lang['flag'] ?? 'uz'" class="w-4.5 h-3 rounded-[2px] shadow-xs" />
                        <span class="font-medium">{{ $lang['name'] }}</span>
                    </div>

                    @if($isActive)
                        <svg class="w-3.5 h-3.5 text-amber-700 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>

