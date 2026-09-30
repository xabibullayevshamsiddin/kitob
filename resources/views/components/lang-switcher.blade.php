{{-- Til almashtirgich: uz (asosiy) / ru / en --}}
@php
    $currentLocale = app()->getLocale();
    $languages = [
        'uz' => ['code' => 'UZ', 'name' => 'Oʻzbekcha', 'flag' => '🇺🇿'],
        'ru' => ['code' => 'RU', 'name' => 'Русский',   'flag' => '🇷🇺'],
        'en' => ['code' => 'EN', 'name' => 'English',   'flag' => '🇬🇧'],
    ];
    $current = $languages[$currentLocale] ?? $languages['uz'];
@endphp

<div x-data="{ langOpen: false }" class="relative">
    <button @click="langOpen = !langOpen"
            class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors text-xs font-semibold text-slate-200 focus:outline-none"
            aria-label="{{ __('site.common.language') }}">
        <span class="text-sm leading-none">{{ $current['flag'] }}</span>
        <span class="font-mono">{{ $current['code'] }}</span>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
    </button>

    <div x-show="langOpen" @click.away="langOpen = false" x-cloak
         x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-40 bg-ink-900/95 backdrop-blur-xl border border-white/10 rounded-2xl shadow-card-depth py-2 z-50 overflow-hidden">
        @foreach($languages as $code => $lang)
            @if($code !== $currentLocale)
                <a href="{{ url()->current() }}?lang={{ $code }}"
                   class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-300 hover:text-amber-400 hover:bg-white/5 transition-colors"
                   @click="langOpen = false">
                    <span class="text-base leading-none">{{ $lang['flag'] }}</span>
                    <span class="font-medium">{{ $lang['name'] }}</span>
                </a>
            @endif
        @endforeach
    </div>
</div>
