{{-- Til almashtirgich: uz / ru / en --}}
@php
    $currentLocale = app()->getLocale();
    $languages = [
        'uz' => ['code' => 'UZ', 'name' => "O\u02BBzbekcha", 'flag' => 'uz'],
        'ru' => ['code' => 'RU', 'name' => 'Русский',         'flag' => 'ru'],
        'en' => ['code' => 'EN', 'name' => 'English',         'flag' => 'gb'],
    ];
    $current = $languages[$currentLocale] ?? $languages['uz'];
@endphp

<div x-data="{ langOpen: false }" x-on:click.outside="langOpen = false" class="relative">

    {{-- Trigger button --}}
    <button
        type="button"
        @click.stop="langOpen = !langOpen"
        class="flex items-center gap-2 px-2.5 py-1.5 rounded-xl bg-white/[0.04] hover:bg-white/[0.08] active:scale-[0.98] border border-white/[0.08] hover:border-amber-400/30 transition-all text-xs font-semibold text-slate-200 focus:outline-none select-none shadow-sm"
        aria-label="Tilni tanlang"
    >
        <img
            src="https://flagcdn.com/20x15/{{ $current['flag'] }}.png"
            srcset="https://flagcdn.com/40x30/{{ $current['flag'] }}.png 2x"
            width="18" height="13"
            alt="{{ $current['code'] }}"
            class="rounded-[2px] object-cover ring-1 ring-white/10"
            onerror="this.style.display='none'"
        >
        <span class="font-mono text-[11px] tracking-wide">{{ $current['code'] }}</span>
        <svg
            class="w-3 h-3 text-slate-400 transition-transform duration-200"
            :class="langOpen ? 'rotate-180 text-amber-400' : ''"
            fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Taste-Skill Dropdown --}}
    <div
        x-show="langOpen"
        x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition cubic-bezier(0.16, 1, 0.3, 1) duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
        x-cloak
        class="absolute right-0 mt-2.5 w-48 p-1.5 bg-[#0a0e17]/95 dark:bg-[#0a0e17]/95 backdrop-blur-2xl border border-white/[0.12] rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.7),0_0_0_1px_rgba(255,255,255,0.05)] z-[99999]"
    >
        <div class="px-2.5 py-1 text-[10px] font-mono uppercase tracking-wider text-slate-400 border-b border-white/[0.06] mb-1 flex items-center justify-between">
            <span>Til / Language</span>
            <span class="text-[9px] text-amber-400/80 font-mono">{{ $current['code'] }}</span>
        </div>

        <div class="space-y-0.5">
            @foreach($languages as $code => $lang)
                @php $isActive = ($code === $currentLocale); @endphp
                <a
                    href="{{ url()->current() }}?lang={{ $code }}"
                    class="flex items-center justify-between px-3 py-2 text-xs rounded-xl transition-all {{ $isActive ? 'bg-amber-400/10 text-amber-400 font-bold border border-amber-400/20' : 'text-slate-300 hover:text-white hover:bg-white/[0.07]' }}"
                    @click="langOpen = false"
                >
                    <div class="flex items-center gap-2.5">
                        <img
                            src="https://flagcdn.com/20x15/{{ $lang['flag'] }}.png"
                            srcset="https://flagcdn.com/40x30/{{ $lang['flag'] }}.png 2x"
                            width="18" height="13"
                            alt="{{ $code }}"
                            class="rounded-[2px] object-cover ring-1 ring-white/10"
                            onerror="this.style.display='none'"
                        >
                        <span class="font-medium">{{ $lang['name'] }}</span>
                    </div>

                    @if($isActive)
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>

