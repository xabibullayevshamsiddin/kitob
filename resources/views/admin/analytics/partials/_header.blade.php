@php
    $tabs = [
        'overview' => [
            'label' => 'Umumiy',
            'icon'  => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
        ],
        'users' => [
            'label' => 'Foydalanuvchilar',
            'icon'  => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        ],
        'content' => [
            'label' => 'Kontent',
            'icon'  => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
        ],
        'economy' => [
            'label' => "O'yin iqtisodi",
            'icon'  => '<circle cx="12" cy="12" r="8"/><path d="M9.5 9.5c.5-1 1.5-1.5 2.5-1.5 1.5 0 2.5.8 2.5 2 0 2.5-5 2-5 4.5 0 1.2 1 2 2.5 2 1 0 2-.5 2.5-1.5"/><path d="M12 6.5v1.5"/><path d="M12 16v1.5"/>',
        ],
        'community' => [
            'label' => 'Jamiyat',
            'icon'  => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M8 10h.01"/><path d="M12 10h.01"/><path d="M16 10h.01"/>',
        ],
        'ai-system' => [
            'label' => 'AI va Tizim',
            'icon'  => '<rect width="16" height="16" x="4" y="4" rx="2"/><rect width="6" height="6" x="9" y="9" rx="1"/><path d="M15 2v2"/><path d="M15 20v2"/><path d="M2 15h2"/><path d="M2 9h2"/><path d="M20 15h2"/><path d="M20 9h2"/><path d="M9 2v2"/><path d="M9 20v2"/>',
        ],
    ];

    $queryParams = [
        'period' => $range['preset'] ?? '30',
    ];
    if (($range['preset'] ?? '') === 'custom') {
        $queryParams['from'] = $range['from'];
        $queryParams['to']   = $range['to'];
    }

    $exportMetricMap = [
        'overview'  => 'overview',
        'users'     => 'retention',
        'content'   => 'content',
        'economy'   => 'economy',
        'community' => 'community',
        'ai-system' => 'ai',
    ];
    $currentExportMetric = $exportMetricMap[$activeTab] ?? 'overview';
    $alertsCount = is_array($alerts ?? null) ? count($alerts) : 0;
@endphp

<div class="ks-panel bg-ink-900 border border-ink-border p-4 sm:p-5"
     x-data="{ showCustomDates: {{ ($range['preset'] ?? '') === 'custom' ? 'true' : 'false' }} }">

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        {{-- Left: Title + Timezone & Period Metadata --}}
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                <span class="ks-badge bg-amber-500/10 text-amber-400 border border-amber-500/25 font-mono text-[10px] uppercase tracking-wider">
                    ANALITIKA MARKAZI
                </span>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-badge bg-ink-950 border border-ink-border font-mono text-[11px] text-mist"
                      title="Barcha kun chegaralari va soatlar Asia/Tashkent vaqt zonasida hisoblanadi">
                    <svg class="w-3.5 h-3.5 text-amber-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span>Vaqt zonasi: <strong class="text-paper">{{ $range['timezone'] ?? 'Asia/Tashkent' }}</strong></span>
                </span>
                @if($alertsCount > 0)
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-badge bg-rose-500/10 border border-rose-500/30 font-mono text-[11px] text-rose-400">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>
                        </svg>
                        <span>{{ $alertsCount }} ta e'tibor talab qiluvchi holat</span>
                    </span>
                @endif
            </div>

            <h1 class="text-xl sm:text-2xl font-bold font-serif text-paper">
                Platforma tahlili va o'sish ko'rsatkichlari
            </h1>
            <p class="text-xs sm:text-sm text-mist mt-0.5 font-mono">
                Tanlangan davr: <span class="text-paper font-medium">{{ $range['from'] }}</span> — <span class="text-paper font-medium">{{ $range['to'] }}</span>
                ({{ number_format($range['days'] ?? 30, 0, '.', ' ') }} kun)
                <span class="text-mist/60">·</span>
                Solishtirish davri: <span class="text-mist">{{ $range['previous_from'] ?? '' }} — {{ $range['previous_to'] ?? '' }}</span>
            </p>
        </div>

        {{-- Right: Period Selector + Refresh + CSV Export --}}
        <div class="flex flex-wrap items-center gap-2">
            {{-- Preset buttons (7 / 30 / 90 / Maxsus) --}}
            <div class="inline-flex items-center rounded-btn bg-ink-950 border border-ink-border p-1" role="group" aria-label="Davr tanlagich">
                @foreach(['7' => '7 kun', '30' => '30 kun', '90' => '90 kun'] as $presetKey => $presetLabel)
                    @php $isPresetActive = ($range['preset'] ?? '30') === (string) $presetKey; @endphp
                    <a href="{{ route('admin.analytics', ['tab' => $activeTab, 'period' => $presetKey]) }}"
                       class="px-2.5 py-1.5 rounded-badge font-mono text-xs font-medium transition-colors duration-base {{ $isPresetActive ? 'bg-amber-500 text-ink-950 font-semibold' : 'text-mist hover:text-paper' }}"
                       @if($isPresetActive) aria-current="true" @endif>
                        {{ $presetLabel }}
                    </a>
                @endforeach
                <button type="button"
                        @click="showCustomDates = !showCustomDates"
                        :aria-expanded="showCustomDates.toString()"
                        class="px-2.5 py-1.5 rounded-badge font-mono text-xs font-medium transition-colors duration-base flex items-center gap-1 {{ ($range['preset'] ?? '') === 'custom' ? 'bg-amber-500 text-ink-950 font-semibold' : 'text-mist hover:text-paper' }}">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
                    </svg>
                    <span>Maxsus</span>
                </button>
            </div>

            {{-- Cache Refresh button (?refresh=1) --}}
            <a href="{{ route('admin.analytics', array_merge(['tab' => $activeTab], $queryParams, ['refresh' => 1])) }}"
               class="inline-flex items-center gap-1.5 h-9 px-3 rounded-btn bg-ink-950 border border-ink-border text-xs font-mono text-mist hover:text-paper hover:border-amber-500/40 transition-colors duration-base"
               title="Keshni yangilash (5 daqiqalik keshni tozalab qayta hisoblash)"
               aria-label="Keshni yangilash">
                <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/>
                </svg>
                <span class="hidden sm:inline">Yangilash</span>
            </a>

            {{-- Aggregate CSV Export --}}
            <a href="{{ route('admin.analytics.export', array_merge(['metric' => $currentExportMetric], $queryParams)) }}"
               class="inline-flex items-center gap-1.5 h-9 px-3 rounded-btn bg-amber-500/10 border border-amber-500/30 text-xs font-mono text-amber-400 hover:bg-amber-500/20 transition-colors duration-base"
               title="Joriy bo'lim agregat ma'lumotlarini CSV formatida yuklab olish"
               aria-label="CSV eksport">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>
                </svg>
                <span>CSV Eksport</span>
            </a>
        </div>
    </div>

    {{-- Custom Date Range Form --}}
    <form x-show="showCustomDates"
          x-cloak
          method="GET"
          action="{{ route('admin.analytics', ['tab' => $activeTab]) }}"
          class="mt-3.5 pt-3.5 border-t border-ink-border flex flex-wrap items-end gap-3">
        <input type="hidden" name="period" value="custom">
        <div>
            <label for="analytics-date-from" class="block font-mono text-[10px] uppercase tracking-wider text-mist mb-1">
                Boshlanish sanasi
            </label>
            <input id="analytics-date-from"
                   type="date"
                   name="from"
                   value="{{ $range['from'] }}"
                   max="{{ now('Asia/Tashkent')->toDateString() }}"
                   required
                   class="h-9 px-3 rounded-btn bg-ink-950 border border-ink-border text-xs font-mono text-paper focus:outline-none focus:border-amber-500">
        </div>
        <div>
            <label for="analytics-date-to" class="block font-mono text-[10px] uppercase tracking-wider text-mist mb-1">
                Tugash sanasi (maks. 365 kun)
            </label>
            <input id="analytics-date-to"
                   type="date"
                   name="to"
                   value="{{ $range['to'] }}"
                   max="{{ now('Asia/Tashkent')->toDateString() }}"
                   required
                   class="h-9 px-3 rounded-btn bg-ink-950 border border-ink-border text-xs font-mono text-paper focus:outline-none focus:border-amber-500">
        </div>
        <button type="submit"
                class="h-9 px-4 rounded-btn bg-amber-500 text-ink-950 font-mono text-xs font-semibold hover:bg-amber-400 transition-colors duration-base">
            Qo'llash
        </button>
    </form>

    {{-- Navigation Tabs --}}
    <nav class="mt-4 pt-3 border-t border-ink-border flex items-center gap-1.5 overflow-x-auto no-scrollbar"
         aria-label="Analitika bo'limlari">
        @foreach($tabs as $tabKey => $tabMeta)
            @php $isTabActive = $activeTab === $tabKey; @endphp
            <a href="{{ route('admin.analytics', array_merge(['tab' => $tabKey], $queryParams)) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-btn font-sans text-xs sm:text-[13px] font-medium whitespace-nowrap transition-colors duration-base border {{ $isTabActive ? 'bg-amber-500/15 text-amber-400 border-amber-500/35' : 'bg-ink-950/60 text-mist border-transparent hover:text-paper hover:bg-ink-800' }}"
               @if($isTabActive) aria-current="page" @endif>
                <svg class="w-4 h-4 flex-shrink-0 {{ $isTabActive ? 'text-amber-400' : 'text-mist' }}"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    {!! $tabMeta['icon'] !!}
                </svg>
                <span>{{ $tabMeta['label'] }}</span>
            </a>
        @endforeach
    </nav>
</div>
