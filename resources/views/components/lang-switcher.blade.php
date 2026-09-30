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

{{-- @click.outside to'g'ri yopish uchun, Alpine x-on:click.outside ishlatamiz --}}
<div x-data="{ langOpen: false }" x-on:click.outside="langOpen = false" class="relative">

    {{-- Trigger button --}}
    <button
        type="button"
        @click.stop="langOpen = !langOpen"
        class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors text-xs font-semibold text-slate-200 focus:outline-none select-none"
        aria-label="Tilni tanlang"
    >
        {{-- Flag image from flagcdn.com - works in all browsers --}}
        <img
            src="https://flagcdn.com/20x15/{{ $current['flag'] }}.png"
            srcset="https://flagcdn.com/40x30/{{ $current['flag'] }}.png 2x"
            width="20" height="15"
            alt="{{ $current['code'] }}"
            class="rounded-[2px] object-cover"
            onerror="this.style.display='none'"
        >
        <span class="font-mono tracking-wide">{{ $current['code'] }}</span>
        <svg
            class="w-3 h-3 text-slate-400 transition-transform duration-200"
            :class="langOpen ? 'rotate-180' : ''"
            fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Dropdown --}}
    <div
        x-show="langOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak
        class="absolute right-0 mt-2 w-44 bg-slate-800 backdrop-blur-xl border border-slate-700/60 rounded-2xl shadow-xl py-2 z-[99999] overflow-hidden"
    >
        @foreach($languages as $code => $lang)
            @if($code !== $currentLocale)
                <a
                    href="{{ url()->current() }}?lang={{ $code }}"
                    class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-slate-300 hover:text-amber-400 hover:bg-white/5 transition-colors"
                    @click="langOpen = false"
                >
                    <img
                        src="https://flagcdn.com/20x15/{{ $lang['flag'] }}.png"
                        srcset="https://flagcdn.com/40x30/{{ $lang['flag'] }}.png 2x"
                        width="20" height="15"
                        alt="{{ $code }}"
                        class="rounded-[2px] object-cover"
                        onerror="this.style.display='none'"
                    >
                    <span class="font-medium">{{ $lang['name'] }}</span>
                </a>
            @endif
        @endforeach
    </div>
</div>
