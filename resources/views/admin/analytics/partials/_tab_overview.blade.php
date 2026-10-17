@php
    $kpis = $data['kpis'] ?? [];
    $trends = $data['trends'] ?? [];
    $overviewAlerts = $alerts ?? ($data['alerts'] ?? []);

    $uzNum = function ($val, int $decimals = 0): string {
        return number_format((float) $val, $decimals, '.', ' ');
    };

    $buildSparklinePoints = function (array $values, int $width = 120, int $height = 32): array {
        if (empty($values)) {
            $values = array_fill(0, 14, 0);
        }
        $count = count($values);
        $min = min($values);
        $max = max($values);
        $range = ($max - $min) > 0 ? ($max - $min) : 1;

        $coords = [];
        foreach (array_values($values) as $i => $v) {
            $x = $count > 1 ? round(($i / ($count - 1)) * $width, 1) : ($width / 2);
            $y = ($max === $min)
                ? round($height - 6, 1)
                : round($height - 4 - ((($v - $min) / $range) * ($height - 8)), 1);
            $coords[] = "{$x},{$y}";
        }

        $line = implode(' ', $coords);
        $area = "0,{$height} " . $line . " {$width},{$height}";

        return ['line' => $line, 'area' => $area];
    };

    $kpiCards = [
        [
            'key'       => 'dau',
            'title'     => 'DAU (Kunlik faol)',
            'display'   => $uzNum($kpis['dau']['value'] ?? 0),
            'sub_type'  => 'dau_split',
            'logged_in' => $uzNum($kpis['dau']['logged_in_users'] ?? 0),
            'reading'   => $uzNum($kpis['dau']['reading_users'] ?? 0),
            'delta'     => $kpis['dau']['delta'] ?? [],
            'sparkline' => $kpis['dau']['sparkline'] ?? [],
            'color'     => '#F59E0B',
            'icon'      => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/>',
        ],
        [
            'key'       => 'wau',
            'title'     => 'WAU (7 kunlik faol)',
            'display'   => $uzNum($kpis['wau']['value'] ?? 0),
            'subtitle'  => 'Oxirgi 7 kun noyob faollar',
            'delta'     => $kpis['wau']['delta'] ?? [],
            'sparkline' => $kpis['wau']['sparkline'] ?? [],
            'color'     => '#38BDF8',
            'icon'      => '<rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>',
        ],
        [
            'key'       => 'mau',
            'title'     => 'MAU (30 kunlik faol)',
            'display'   => $uzNum($kpis['mau']['value'] ?? 0),
            'subtitle'  => 'Oxirgi 30 kun noyob faollar',
            'delta'     => $kpis['mau']['delta'] ?? [],
            'sparkline' => $kpis['mau']['sparkline'] ?? [],
            'color'     => '#818CF8',
            'icon'      => '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
        ],
        [
            'key'       => 'stickiness',
            'title'     => 'Yopishqoqlik (DAU/MAU)',
            'display'   => $uzNum($kpis['stickiness']['value'] ?? 0, 1) . '%',
            'subtitle'  => 'Kunlik qaytish intensivligi',
            'delta'     => $kpis['stickiness']['delta'] ?? [],
            'sparkline' => $kpis['stickiness']['sparkline'] ?? [],
            'color'     => '#10B981',
            'icon'      => '<path d="M12 2v20"/><path d="m17 5-5-3-5 3"/><path d="m17 19-5 3-5-3"/><path d="M2 12h20"/>',
        ],
        [
            'key'       => 'new_users',
            'title'     => 'Yangi foydalanuvchilar',
            'display'   => $uzNum($kpis['new_users']['value'] ?? 0),
            'subtitle'  => "Tanlangan davrda ro'yxatdan o'tgan",
            'delta'     => $kpis['new_users']['delta'] ?? [],
            'sparkline' => $kpis['new_users']['sparkline'] ?? [],
            'color'     => '#F59E0B',
            'icon'      => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/>',
        ],
        [
            'key'       => 'reading_hours',
            'title'     => "O'qilgan vaqt",
            'display'   => $kpis['reading_hours']['formatted'] ?? '0 daq',
            'subtitle'  => 'Jami ' . $uzNum($kpis['reading_hours']['minutes_read'] ?? 0) . ' daqiqa',
            'delta'     => $kpis['reading_hours']['delta'] ?? [],
            'sparkline' => $kpis['reading_hours']['sparkline'] ?? [],
            'color'     => '#10B981',
            'icon'      => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        ],
        [
            'key'       => 'active_readers',
            'title'     => "Faol o'quvchilar (≥5 daq)",
            'display'   => $uzNum($kpis['active_readers']['value'] ?? 0),
            'subtitle'  => 'Bugun: ' . $uzNum($kpis['active_readers']['today_value'] ?? 0) . ' ta faol',
            'delta'     => $kpis['active_readers']['delta'] ?? [],
            'sparkline' => $kpis['active_readers']['sparkline'] ?? [],
            'color'     => '#34D399',
            'icon'      => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
        ],
        [
            'key'       => 'books_finished',
            'title'     => 'Tugatilgan kitoblar',
            'display'   => $uzNum($kpis['books_finished']['value'] ?? 0),
            'subtitle'  => 'Jami: ' . $uzNum($kpis['books_finished']['total_all_time'] ?? 0) . ' ta (≥90%)',
            'delta'     => $kpis['books_finished']['delta'] ?? [],
            'sparkline' => $kpis['books_finished']['sparkline'] ?? [],
            'color'     => '#FBBF24',
            'icon'      => '<path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>',
        ],
    ];
@endphp

<div class="space-y-5">

    {{-- ★ 1. "Diqqat talab qiladi" (Alerts Panel — Section 7) --}}
    <section aria-labelledby="alerts-panel-heading" class="ks-panel bg-ink-900 border border-ink-border p-4 sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-btn flex items-center justify-center border {{ count($overviewAlerts) > 0 ? 'bg-rose-500/10 border-rose-500/30 text-rose-400' : 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' }}">
                    @if(count($overviewAlerts) > 0)
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>
                        </svg>
                    @else
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    @endif
                </span>
                <div>
                    <h2 id="alerts-panel-heading" class="text-sm sm:text-base font-bold font-serif text-paper">
                        Diqqat talab qiladi
                    </h2>
                    <p class="text-xs text-mist">
                        Tizim barqarorligi, DAU pasayishi, shikoyatlar va AI byudjeti bo'yicha avtomatik diagnostika
                    </p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-badge font-mono text-[11px] font-semibold border {{ count($overviewAlerts) > 0 ? 'bg-rose-500/10 text-rose-400 border-rose-500/30' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' }}">
                {{ count($overviewAlerts) > 0 ? count($overviewAlerts) . ' ta faol ogohlantirish' : "Me'yorda" }}
            </span>
        </div>

        @if(count($overviewAlerts) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($overviewAlerts as $alert)
                    @php
                        $isCritical = ($alert['severity'] ?? 'warning') === 'critical';
                    @endphp
                    <div class="rounded-card p-3.5 border flex items-start justify-between gap-3 {{ $isCritical ? 'bg-rose-500/5 border-rose-500/30' : 'bg-amber-500/5 border-amber-500/30' }}">
                        <div class="flex items-start gap-2.5 min-w-0">
                            <span class="mt-0.5 w-6 h-6 rounded-badge flex items-center justify-center flex-shrink-0 {{ $isCritical ? 'bg-rose-500/15 text-rose-400' : 'bg-amber-500/15 text-amber-400' }}">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="font-mono text-[10px] uppercase tracking-wider px-1.5 py-0.5 rounded-badge {{ $isCritical ? 'bg-rose-500/20 text-rose-300' : 'bg-amber-500/20 text-amber-300' }}">
                                        {{ $isCritical ? 'Kritik' : 'Ogohlantirish' }}
                                    </span>
                                    <h3 class="text-xs sm:text-sm font-semibold text-paper">{{ $alert['title'] }}</h3>
                                </div>
                                <p class="text-xs text-mist mt-1 leading-relaxed">
                                    {{ $alert['description'] }}
                                </p>
                            </div>
                        </div>
                        @if(!empty($alert['action_url']))
                            <a href="{{ $alert['action_url'] }}"
                               class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-btn font-mono text-[11px] font-medium border transition-colors duration-base {{ $isCritical ? 'bg-rose-500/15 text-rose-300 border-rose-500/30 hover:bg-rose-500/25' : 'bg-amber-500/15 text-amber-300 border-amber-500/30 hover:bg-amber-500/25' }}">
                                <span>{{ $alert['action_label'] ?? "Ko'rish" }}</span>
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-card bg-ink-950/60 border border-ink-border px-4 py-3 flex items-center gap-2.5 text-xs text-mist">
                <span class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                <span>Barcha tizim ko'rsatkichlari me'yorda — DAU barqaror, snapshot yangilangan, muddati o'tgan shikoyatlar yoki xato joblar yo'q.</span>
            </div>
        @endif
    </section>

    {{-- 2. 8 KPI Cards (2 columns on mobile: grid-cols-2 sm:grid-cols-2 xl:grid-cols-4) --}}
    <section aria-label="Asosiy KPI ko'rsatkichlari">
        <div class="grid grid-cols-2 sm:grid-cols-2 xl:grid-cols-4 gap-3.5">
            @foreach($kpiCards as $card)
                @php
                    $delta = $card['delta'] ?? [];
                    $isNew = !empty($delta['is_new']);
                    $isPositive = !empty($delta['is_positive']);
                    $dir = $delta['direction'] ?? 'flat';
                    $pct = $delta['percent'] ?? 0;

                    if ($isNew) {
                        $deltaClass = 'bg-amber-500/15 text-amber-400 border-amber-500/30';
                    } elseif ($dir === 'flat') {
                        $deltaClass = 'bg-ink-950 text-mist border-ink-border';
                    } elseif ($isPositive) {
                        $deltaClass = 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30';
                    } else {
                        $deltaClass = 'bg-rose-500/15 text-rose-400 border-rose-500/30';
                    }

                    $spark = $buildSparklinePoints($card['sparkline'] ?? []);
                @endphp
                <div class="ks-panel bg-ink-900 border border-ink-border p-3.5 sm:p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-[11px] font-mono uppercase tracking-wider text-mist leading-snug">
                                {{ $card['title'] }}
                            </p>
                            <span class="w-8 h-8 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-amber-400 flex-shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    {!! $card['icon'] !!}
                                </svg>
                            </span>
                        </div>

                        <div class="mt-2 flex flex-wrap items-baseline justify-between gap-2">
                            <p class="text-xl sm:text-2xl font-bold font-mono text-paper ks-stat">
                                {{ $card['display'] }}
                            </p>

                            {{-- Delta badge --}}
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-badge border font-mono text-[10px] font-semibold {{ $deltaClass }}"
                                  title="Oldingi teng davrga ({{ $range['previous_from'] ?? '' }} — {{ $range['previous_to'] ?? '' }}) nisbatan o'zgarish">
                                @if($isNew)
                                    <span>yangi</span>
                                @elseif($dir === 'up')
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="m18 15-6-6-6 6"/>
                                    </svg>
                                    <span>+{{ number_format(abs((float) $pct), 1, '.', ' ') }}%</span>
                                @elseif($dir === 'down')
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="m6 9 6 6 6-6"/>
                                    </svg>
                                    <span>-{{ number_format(abs((float) $pct), 1, '.', ' ') }}%</span>
                                @else
                                    <span>0%</span>
                                @endif
                            </span>
                        </div>

                        {{-- Subtitle / DAU split ("Kirgan" vs "O'qigan" alohida) --}}
                        @if(($card['sub_type'] ?? '') === 'dau_split')
                            <div class="mt-2 flex flex-wrap items-center gap-1.5 font-mono text-[11px]">
                                <span class="px-1.5 py-0.5 rounded-badge bg-ink-950 border border-ink-border text-mist">
                                    Kirgan: <strong class="text-paper">{{ $card['logged_in'] }}</strong>
                                </span>
                                <span class="px-1.5 py-0.5 rounded-badge bg-emerald-500/10 border border-emerald-500/25 text-emerald-400">
                                    O'qigan: <strong>{{ $card['reading'] }}</strong>
                                </span>
                            </div>
                        @else
                            <p class="mt-1.5 text-[11px] font-mono text-mist truncate">
                                {{ $card['subtitle'] ?? '' }}
                            </p>
                        @endif
                    </div>

                    {{-- 14-Day SVG Sparkline --}}
                    <div class="mt-3 pt-2 border-t border-ink-border/60 flex items-center justify-between gap-2">
                        <span class="font-mono text-[10px] text-mist">14k trend</span>
                        <svg class="w-24 sm:w-28 h-7 overflow-visible" viewBox="0 0 120 32" role="img" aria-label="{{ $card['title'] }} oxirgi 14 kunlik sparkline grafigi">
                            <polygon points="{{ $spark['area'] }}" fill="{{ $card['color'] }}" fill-opacity="0.12" />
                            <polyline points="{{ $spark['line'] }}" fill="none" stroke="{{ $card['color'] }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 3. Trend Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Chart 1: Daily Active Users & Signups Trend --}}
        <section class="ks-panel bg-ink-900 border border-ink-border p-4 sm:p-5"
                 x-data="{ tableView: false }">
            <div class="flex flex-wrap items-start justify-between gap-2 mb-4">
                <div>
                    <h2 class="text-sm sm:text-base font-bold font-serif text-paper">
                        Faol foydalanuvchilar va ro'yxatdan o'tish dinamikasi
                    </h2>
                    <p class="text-xs text-mist font-mono mt-0.5">
                        DAU, kirganlar, faol o'quvchilar (≥5 daq) va yangi qo'shilganlar
                    </p>
                </div>

                <button type="button"
                        @click="tableView = !tableView"
                        :aria-pressed="tableView.toString()"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-btn bg-ink-950 border border-ink-border font-mono text-[11px] text-mist hover:text-paper transition-colors duration-base">
                    <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/>
                    </svg>
                    <span x-text="tableView ? 'Grafik ko\'rinishi' : 'Jadval ko\'rinishi'">Jadval ko'rinishi</span>
                </button>
            </div>

            {{-- Visual Chart --}}
            <div x-show="!tableView">
                @if(!empty($trends['dates']))
                    <div class="relative h-64 sm:h-72 w-full">
                        <canvas id="chart-overview-activity"
                                role="img"
                                aria-label="Kunlik faol foydalanuvchilar, kirganlar, faol o'quvchilar va yangi foydalanuvchilar grafigi"></canvas>
                    </div>
                @else
                    <div class="h-64 flex flex-col items-center justify-center rounded-card bg-ink-950/60 border border-ink-border p-6 text-center">
                        <svg class="w-8 h-8 text-mist mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>
                        </svg>
                        <p class="text-xs font-mono text-mist">Tanlangan davr uchun faollik ma'lumotlari mavjud emas.</p>
                    </div>
                @endif
            </div>

            {{-- Accessible Table View ("Jadval ko'rinishi") --}}
            <div x-show="tableView" x-cloak class="admin-table-scroll max-h-72 overflow-y-auto rounded-card border border-ink-border">
                <table class="w-full text-left font-mono text-xs" aria-label="Kunlik faollik va ro'yxatdan o'tish jadvali">
                    <thead class="bg-ink-950 text-mist uppercase text-[10px] sticky top-0">
                        <tr>
                            <th class="py-2.5 px-3 border-b border-ink-border">Sana</th>
                            <th class="py-2.5 px-3 border-b border-ink-border text-right">DAU</th>
                            <th class="py-2.5 px-3 border-b border-ink-border text-right">Kirgan</th>
                            <th class="py-2.5 px-3 border-b border-ink-border text-right">O'qigan (&gt;0m)</th>
                            <th class="py-2.5 px-3 border-b border-ink-border text-right">Faol (≥5m)</th>
                            <th class="py-2.5 px-3 border-b border-ink-border text-right">Yangi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-border/60 text-paper">
                        @forelse($trends['dates'] ?? [] as $idx => $dateStr)
                            <tr class="hover:bg-ink-800/50">
                                <td class="py-2 px-3 text-mist">{{ $dateStr }}</td>
                                <td class="py-2 px-3 text-right font-semibold">{{ $uzNum($trends['dau'][$idx] ?? 0) }}</td>
                                <td class="py-2 px-3 text-right">{{ $uzNum($trends['logged_in_users'][$idx] ?? 0) }}</td>
                                <td class="py-2 px-3 text-right text-emerald-400">{{ $uzNum($trends['reading_users'][$idx] ?? 0) }}</td>
                                <td class="py-2 px-3 text-right">{{ $uzNum($trends['active_readers'][$idx] ?? 0) }}</td>
                                <td class="py-2 px-3 text-right text-amber-400">{{ $uzNum($trends['new_users'][$idx] ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 text-center text-mist">Ma'lumot topilmadi</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Chart 2: Daily Reading Minutes & Quiz Attempts Trend --}}
        <section class="ks-panel bg-ink-900 border border-ink-border p-4 sm:p-5"
                 x-data="{ tableView: false }">
            <div class="flex flex-wrap items-start justify-between gap-2 mb-4">
                <div>
                    <h2 class="text-sm sm:text-base font-bold font-serif text-paper">
                        Kunlik o'qish daqiqalari va test urinishlari
                    </h2>
                    <p class="text-xs text-mist font-mono mt-0.5">
                        Platformada sarflangan mutolaa vaqti (daq) va yakunlangan quizlar
                    </p>
                </div>

                <button type="button"
                        @click="tableView = !tableView"
                        :aria-pressed="tableView.toString()"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-btn bg-ink-950 border border-ink-border font-mono text-[11px] text-mist hover:text-paper transition-colors duration-base">
                    <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/>
                    </svg>
                    <span x-text="tableView ? 'Grafik ko\'rinishi' : 'Jadval ko\'rinishi'">Jadval ko'rinishi</span>
                </button>
            </div>

            {{-- Visual Chart --}}
            <div x-show="!tableView">
                @if(!empty($trends['dates']))
                    <div class="relative h-64 sm:h-72 w-full">
                        <canvas id="chart-overview-reading"
                                role="img"
                                aria-label="Kunlik o'qilgan daqiqalar, quiz urinishlari va tugatilgan kitoblar grafigi"></canvas>
                    </div>
                @else
                    <div class="h-64 flex flex-col items-center justify-center rounded-card bg-ink-950/60 border border-ink-border p-6 text-center">
                        <svg class="w-8 h-8 text-mist mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                        </svg>
                        <p class="text-xs font-mono text-mist">Tanlangan davr uchun mutolaa ma'lumotlari mavjud emas.</p>
                    </div>
                @endif
            </div>

            {{-- Accessible Table View ("Jadval ko'rinishi") --}}
            <div x-show="tableView" x-cloak class="admin-table-scroll max-h-72 overflow-y-auto rounded-card border border-ink-border">
                <table class="w-full text-left font-mono text-xs" aria-label="Kunlik o'qish daqiqalari va test urinishlari jadvali">
                    <thead class="bg-ink-950 text-mist uppercase text-[10px] sticky top-0">
                        <tr>
                            <th class="py-2.5 px-3 border-b border-ink-border">Sana</th>
                            <th class="py-2.5 px-3 border-b border-ink-border text-right">Daqiqalar</th>
                            <th class="py-2.5 px-3 border-b border-ink-border text-right">Soat</th>
                            <th class="py-2.5 px-3 border-b border-ink-border text-right">Quiz urinishlar</th>
                            <th class="py-2.5 px-3 border-b border-ink-border text-right">Tugatilgan kitob</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-border/60 text-paper">
                        @forelse($trends['dates'] ?? [] as $idx => $dateStr)
                            <tr class="hover:bg-ink-800/50">
                                <td class="py-2 px-3 text-mist">{{ $dateStr }}</td>
                                <td class="py-2 px-3 text-right font-semibold text-amber-400">{{ $uzNum($trends['minutes_read'][$idx] ?? 0) }}</td>
                                <td class="py-2 px-3 text-right">{{ $uzNum($trends['reading_hours'][$idx] ?? 0, 1) }}</td>
                                <td class="py-2 px-3 text-right text-emerald-400">{{ $uzNum($trends['quiz_attempts'][$idx] ?? 0) }}</td>
                                <td class="py-2 px-3 text-right">{{ $uzNum($trends['books_finished'][$idx] ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-mist">Ma'lumot topilmadi</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</div>
