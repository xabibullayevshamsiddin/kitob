@php
    $funnelSteps   = $data['funnel']['steps'] ?? [];
    $weeklyCohorts = $data['weekly_cohorts'] ?? [];
    $dailyCohorts  = $data['daily_cohorts'] ?? [];
    $segments      = $data['segments'] ?? [];
    $churnRisk     = $data['churn_risk'] ?? ['count' => 0, 'min_streak' => 7, 'inactive_days' => 2, 'items' => []];
    $heatmap       = $data['heatmap'] ?? [
        'days'      => [],
        'max_value' => 0,
        'best_slot' => ['day_name' => 'Dushanba', 'hour' => 20, 'label' => 'Dushanba, 20:00', 'count' => 0],
        'timezone'  => 'Asia/Tashkent',
    ];
    $hoursList     = range(0, 23);

    $uzNum = function ($val, int $decimals = 0): string {
        return number_format((float) $val, $decimals, '.', ' ');
    };

    $cohortHeatStyle = function ($pct): string {
        if ($pct === null) {
            return 'background-color: rgba(31, 41, 61, 0.25);';
        }
        $val = max(0.0, min(100.0, (float) $pct));
        $alpha = round(0.08 + ($val / 100.0) * 0.55, 2);
        return "background-color: rgba(16, 185, 129, {$alpha});";
    };
@endphp

<div class="space-y-5">

    {{-- 1. KONVERSIYA VORONKASI (5-Step Conversion Funnel) --}}
    <section class="ks-panel bg-ink-900 border border-ink-border p-4 sm:p-5"
             x-data="{ tableView: false }"
             aria-labelledby="funnel-heading">
        <div class="flex flex-wrap items-start justify-between gap-2 mb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="ks-badge bg-amber-500/10 text-amber-400 border border-amber-500/25 font-mono text-[10px]">
                        VORONKA
                    </span>
                    <h2 id="funnel-heading" class="text-sm sm:text-base font-bold font-serif text-paper">
                        Ro'yxatdan o'tishdan 7 kunlik streakkacha konversiya voronkasi
                    </h2>
                </div>
                <p class="text-xs text-mist font-mono mt-1">
                    Tanlangan davrda ro'yxatdan o'tgan kohorta ({{ $range['from'] }} — {{ $range['to'] }}) bosqichma-bosqich o'tish ko'rsatkichi
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

        {{-- Visual Stepped Funnel + Chart.js Canvas --}}
        <div x-show="!tableView" class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
            <div class="lg:col-span-7 space-y-2.5">
                @foreach($funnelSteps as $idx => $step)
                    @php
                        $pctTotal = (float) ($step['percent_of_total'] ?? 0);
                        $pctPrev  = (float) ($step['percent_of_prev'] ?? 0);
                        $barWidth = max($pctTotal, ($step['count'] ?? 0) > 0 ? 4 : 1);
                    @endphp
                    <div class="rounded-card bg-ink-950/70 border border-ink-border p-3">
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs font-mono mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-badge bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center text-[10px] font-bold">
                                    {{ $idx + 1 }}
                                </span>
                                <span class="font-sans font-medium text-paper">{{ $step['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-paper">{{ $uzNum($step['count'] ?? 0) }} ta</span>
                                <span class="px-1.5 py-0.5 rounded-badge bg-amber-500/15 text-amber-400 text-[10px]">
                                    {{ $uzNum($pctTotal, 1) }}% jami
                                </span>
                                @if($idx > 0)
                                    <span class="px-1.5 py-0.5 rounded-badge bg-ink-900 border border-ink-border text-mist text-[10px]"
                                          title="Oldingi bosqichdan o'tish ulushi">
                                        {{ $uzNum($pctPrev, 1) }}% oldingidan
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="h-2 rounded-full bg-ink-900 overflow-hidden border border-ink-border/60">
                            <div class="h-full rounded-full bg-amber-500 transition-all duration-500"
                                 style="width: {{ min($barWidth, 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="lg:col-span-5 h-60 sm:h-64">
                <canvas id="chart-users-funnel"
                        role="img"
                        aria-label="Foydalanuvchilar konversiya voronkasi grafigi"></canvas>
            </div>
        </div>

        {{-- Accessible Table View ("Jadval ko'rinishi") --}}
        <div x-show="tableView" x-cloak class="admin-table-scroll rounded-card border border-ink-border">
            <table class="w-full text-left font-mono text-xs" aria-label="Foydalanuvchilar konversiya voronkasi jadvali">
                <thead class="bg-ink-950 text-mist uppercase text-[10px]">
                    <tr>
                        <th class="py-2.5 px-3 border-b border-ink-border">#</th>
                        <th class="py-2.5 px-3 border-b border-ink-border">Bosqich</th>
                        <th class="py-2.5 px-3 border-b border-ink-border text-right">Foydalanuvchilar</th>
                        <th class="py-2.5 px-3 border-b border-ink-border text-right">Jami kohortadan (%)</th>
                        <th class="py-2.5 px-3 border-b border-ink-border text-right">Oldingi bosqichdan (%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border/60 text-paper">
                    @foreach($funnelSteps as $idx => $step)
                        <tr class="hover:bg-ink-800/50">
                            <td class="py-2 px-3 text-mist">{{ $idx + 1 }}</td>
                            <td class="py-2 px-3 font-sans font-medium">{{ $step['label'] }}</td>
                            <td class="py-2 px-3 text-right font-semibold">{{ $uzNum($step['count'] ?? 0) }}</td>
                            <td class="py-2 px-3 text-right text-amber-400">{{ $uzNum($step['percent_of_total'] ?? 0, 1) }}%</td>
                            <td class="py-2 px-3 text-right text-emerald-400">{{ $uzNum($step['percent_of_prev'] ?? 0, 1) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- 2. KOHORTA RETENTION JADVALI (D1 / D7 / D30 Issiqlik Xaritasi) --}}
    <section class="ks-panel bg-ink-900 border border-ink-border p-4 sm:p-5"
             x-data="{ cohortMode: 'weekly' }"
             aria-labelledby="cohort-retention-heading">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="ks-badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 font-mono text-[10px]">
                        RETENTION KOHORTALARI
                    </span>
                    <h2 id="cohort-retention-heading" class="text-sm sm:text-base font-bold font-serif text-paper">
                        D1 / D7 / D30 ushlab qolish (Retention) issiqlik xaritasi
                    </h2>
                </div>
                <p class="text-xs text-mist font-mono mt-1">
                    Ro'yxatdan o'tgan kun/hafta kohortasi → n kundan keyin qaytib faollik ko'rsatganlar. Hali n kun o'tmagan kohortalar <strong class="text-paper">N/A</strong> sifatida belgilanadi (0% emas).
                </p>
            </div>

            <div class="inline-flex items-center rounded-btn bg-ink-950 border border-ink-border p-1 font-mono text-xs" role="group" aria-label="Kohorta turi">
                <button type="button"
                        @click="cohortMode = 'weekly'"
                        :class="cohortMode === 'weekly' ? 'bg-amber-500 text-ink-950 font-semibold' : 'text-mist hover:text-paper'"
                        class="px-2.5 py-1 rounded-badge transition-colors duration-base">
                    Haftalik kohortalar
                </button>
                <button type="button"
                        @click="cohortMode = 'daily'"
                        :class="cohortMode === 'daily' ? 'bg-amber-500 text-ink-950 font-semibold' : 'text-mist hover:text-paper'"
                        class="px-2.5 py-1 rounded-badge transition-colors duration-base">
                    Kunlik kohortalar
                </button>
            </div>
        </div>

        {{-- Weekly Cohorts Heatmap Table --}}
        <div x-show="cohortMode === 'weekly'" class="admin-table-scroll rounded-card border border-ink-border">
            <table class="w-full text-left font-mono text-xs" aria-label="Haftalik kohortalar bo'yicha D1, D7, D30 retention jadvali">
                <thead class="bg-ink-950 text-mist uppercase text-[10px]">
                    <tr>
                        <th class="py-2.5 px-3 border-b border-ink-border">Haftalik kohorta</th>
                        <th class="py-2.5 px-3 border-b border-ink-border text-right">Yangi foydalanuvchilar</th>
                        <th class="py-2.5 px-3 border-b border-ink-border text-center">D1 (1 kundan so'ng)</th>
                        <th class="py-2.5 px-3 border-b border-ink-border text-center">D7 (7 kundan so'ng)</th>
                        <th class="py-2.5 px-3 border-b border-ink-border text-center">D30 (30 kundan so'ng)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border/60 text-paper">
                    @forelse($weeklyCohorts as $cohort)
                        <tr class="hover:bg-ink-800/40">
                            <td class="py-2.5 px-3 font-medium">
                                <span class="text-paper">{{ $cohort['label'] }}</span>
                                <span class="block text-[10px] text-mist">{{ $cohort['week_start'] }} — {{ $cohort['week_end'] }}</span>
                            </td>
                            <td class="py-2.5 px-3 text-right font-semibold">
                                {{ $uzNum($cohort['cohort_size'] ?? 0) }} ta
                            </td>
                            @foreach(['d1' => 'd1_count', 'd7' => 'd7_count', 'd30' => 'd30_count'] as $dKey => $cntKey)
                                @php $val = $cohort[$dKey]; @endphp
                                <td class="py-2 px-3 text-center border-l border-ink-border/40"
                                    style="{{ $cohortHeatStyle($val) }}">
                                    @if($val === null)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-badge bg-ink-950/80 border border-ink-border text-mist text-[11px]"
                                              title="Bu kohorta uchun muddat hali yetib kelmagan">
                                            N/A
                                        </span>
                                    @else
                                        <span class="font-bold text-paper">{{ $uzNum($val, 1) }}%</span>
                                        <span class="block text-[10px] text-mist">({{ $uzNum($cohort[$cntKey] ?? 0) }} ta)</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-mist">Kohorta ma'lumotlari mavjud emas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Daily Cohorts Heatmap Table --}}
        <div x-show="cohortMode === 'daily'" x-cloak class="admin-table-scroll max-h-96 overflow-y-auto rounded-card border border-ink-border">
            <table class="w-full text-left font-mono text-xs" aria-label="Kunlik kohortalar bo'yicha D1, D7, D30 retention jadvali">
                <thead class="bg-ink-950 text-mist uppercase text-[10px] sticky top-0">
                    <tr>
                        <th class="py-2.5 px-3 border-b border-ink-border">Kohorta sanasi</th>
                        <th class="py-2.5 px-3 border-b border-ink-border text-right">Yangi foydalanuvchilar</th>
                        <th class="py-2.5 px-3 border-b border-ink-border text-center">D1</th>
                        <th class="py-2.5 px-3 border-b border-ink-border text-center">D7</th>
                        <th class="py-2.5 px-3 border-b border-ink-border text-center">D30</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border/60 text-paper">
                    @forelse($dailyCohorts as $cohort)
                        <tr class="hover:bg-ink-800/40">
                            <td class="py-2 px-3 text-mist">{{ $cohort['cohort_date'] }} ({{ $cohort['label'] }})</td>
                            <td class="py-2 px-3 text-right font-semibold">{{ $uzNum($cohort['cohort_size'] ?? 0) }} ta</td>
                            @foreach(['d1' => 'd1_count', 'd7' => 'd7_count', 'd30' => 'd30_count'] as $dKey => $cntKey)
                                @php $val = $cohort[$dKey]; @endphp
                                <td class="py-2 px-3 text-center border-l border-ink-border/40"
                                    style="{{ $cohortHeatStyle($val) }}">
                                    @if($val === null)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-badge bg-ink-950/80 border border-ink-border text-mist text-[10px]">
                                            N/A
                                        </span>
                                    @else
                                        <span class="font-bold text-paper">{{ $uzNum($val, 1) }}%</span>
                                        <span class="text-[10px] text-mist ml-1">({{ $uzNum($cohort[$cntKey] ?? 0) }})</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-mist">Kunlik kohorta ma'lumotlari mavjud emas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- 3. SEGMENTATSIYA (Reading Goal & Reading Place, < 5 user suppression) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Segment A: Reading Goal --}}
        @php
            $goalSeg = $segments['by_reading_goal'] ?? ['items' => [], 'insufficient' => ['has_hidden' => false]];
            $goalItems = $goalSeg['items'] ?? [];
            $goalInsuf = $goalSeg['insufficient'] ?? [];
        @endphp
        <section class="ks-panel bg-ink-900 border border-ink-border p-4 sm:p-5"
                 x-data="{ tableView: false }"
                 aria-labelledby="segment-goal-heading">
            <div class="flex flex-wrap items-start justify-between gap-2 mb-3">
                <div>
                    <h2 id="segment-goal-heading" class="text-sm sm:text-base font-bold font-serif text-paper">
                        O'qish maqsadi bo'yicha segmentatsiya
                    </h2>
                    <p class="text-xs text-mist font-mono mt-0.5">
                        reading_goal kesimida (kamida {{ $segments['min_users'] ?? 5 }} foydalanuvchili guruhlar)
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

            <div x-show="!tableView">
                @if(count($goalItems) > 0)
                    <div class="relative h-56 w-full mb-3">
                        <canvas id="chart-segment-goal"
                                role="img"
                                aria-label="O'qish maqsadi bo'yicha foydalanuvchilar va o'rtacha o'qish daqiqalari grafigi"></canvas>
                    </div>
                @else
                    <div class="h-44 flex flex-col items-center justify-center rounded-card bg-ink-950/60 border border-ink-border p-4 text-center mb-3">
                        <p class="text-xs font-mono text-amber-400 font-semibold">Kam ma'lumot</p>
                        <p class="text-[11px] font-mono text-mist mt-1">
                            5 va undan ortiq foydalanuvchiga ega segmentlar topilmadi.
                        </p>
                    </div>
                @endif
            </div>

            <div class="admin-table-scroll rounded-card border border-ink-border">
                <table class="w-full text-left font-mono text-xs" aria-label="O'qish maqsadi bo'yicha segmentlar jadvali">
                    <thead class="bg-ink-950 text-mist uppercase text-[10px]">
                        <tr>
                            <th class="py-2 px-3 border-b border-ink-border">Maqsad (reading_goal)</th>
                            <th class="py-2 px-3 border-b border-ink-border text-right">Soni</th>
                            <th class="py-2 px-3 border-b border-ink-border text-right">Ulushi</th>
                            <th class="py-2 px-3 border-b border-ink-border text-right">O'rtacha daq.</th>
                            <th class="py-2 px-3 border-b border-ink-border text-right">Tugatilgan kitob</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-border/60 text-paper">
                        @forelse($goalItems as $item)
                            <tr class="hover:bg-ink-800/40">
                                <td class="py-2 px-3 font-sans font-medium">{{ $item['label'] }}</td>
                                <td class="py-2 px-3 text-right font-semibold">{{ $uzNum($item['count']) }}</td>
                                <td class="py-2 px-3 text-right text-amber-400">{{ $uzNum($item['share_pct'], 1) }}%</td>
                                <td class="py-2 px-3 text-right text-emerald-400">{{ $uzNum($item['avg_minutes_read'], 1) }} daq</td>
                                <td class="py-2 px-3 text-right">{{ $uzNum($item['finished_books_count']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-mist">Yetarli foydalanuvchili guruh yo'q (kam ma'lumot)</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(!empty($goalInsuf['has_hidden']))
                <div class="mt-2.5 px-3 py-2 rounded-badge bg-ink-950 border border-ink-border flex items-center justify-between text-[11px] font-mono text-mist">
                    <span>Maxfiylik qoidasi: &lt; {{ $segments['min_users'] ?? 5 }} kishilik {{ $goalInsuf['hidden_groups_count'] }} ta guruh yashirildi</span>
                    <span class="px-2 py-0.5 rounded-badge bg-amber-500/10 text-amber-400 border border-amber-500/25">
                        {{ $goalInsuf['label'] ?? "Kam ma'lumot" }} ({{ $goalInsuf['hidden_users_count'] }} kishi)
                    </span>
                </div>
            @endif
        </section>

        {{-- Segment B: Reading Place --}}
        @php
            $placeSeg = $segments['by_reading_place'] ?? ['items' => [], 'insufficient' => ['has_hidden' => false]];
            $placeItems = $placeSeg['items'] ?? [];
            $placeInsuf = $placeSeg['insufficient'] ?? [];
        @endphp
        <section class="ks-panel bg-ink-900 border border-ink-border p-4 sm:p-5"
                 x-data="{ tableView: false }"
                 aria-labelledby="segment-place-heading">
            <div class="flex flex-wrap items-start justify-between gap-2 mb-3">
                <div>
                    <h2 id="segment-place-heading" class="text-sm sm:text-base font-bold font-serif text-paper">
                        O'qish joyi bo'yicha segmentatsiya
                    </h2>
                    <p class="text-xs text-mist font-mono mt-0.5">
                        reading_place kesimida (kamida {{ $segments['min_users'] ?? 5 }} foydalanuvchili guruhlar)
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

            <div x-show="!tableView">
                @if(count($placeItems) > 0)
                    <div class="relative h-56 w-full mb-3">
                        <canvas id="chart-segment-place"
                                role="img"
                                aria-label="O'qish joyi bo'yicha foydalanuvchilar va o'rtacha o'qish daqiqalari grafigi"></canvas>
                    </div>
                @else
                    <div class="h-44 flex flex-col items-center justify-center rounded-card bg-ink-950/60 border border-ink-border p-4 text-center mb-3">
                        <p class="text-xs font-mono text-amber-400 font-semibold">Kam ma'lumot</p>
                        <p class="text-[11px] font-mono text-mist mt-1">
                            5 va undan ortiq foydalanuvchiga ega segmentlar topilmadi.
                        </p>
                    </div>
                @endif
            </div>

            <div class="admin-table-scroll rounded-card border border-ink-border">
                <table class="w-full text-left font-mono text-xs" aria-label="O'qish joyi bo'yicha segmentlar jadvali">
                    <thead class="bg-ink-950 text-mist uppercase text-[10px]">
                        <tr>
                            <th class="py-2 px-3 border-b border-ink-border">Joy / muassasa (reading_place)</th>
                            <th class="py-2 px-3 border-b border-ink-border text-right">Soni</th>
                            <th class="py-2 px-3 border-b border-ink-border text-right">Ulushi</th>
                            <th class="py-2 px-3 border-b border-ink-border text-right">O'rtacha daq.</th>
                            <th class="py-2 px-3 border-b border-ink-border text-right">Tugatilgan kitob</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-border/60 text-paper">
                        @forelse($placeItems as $item)
                            <tr class="hover:bg-ink-800/40">
                                <td class="py-2 px-3 font-sans font-medium">{{ $item['label'] }}</td>
                                <td class="py-2 px-3 text-right font-semibold">{{ $uzNum($item['count']) }}</td>
                                <td class="py-2 px-3 text-right text-amber-400">{{ $uzNum($item['share_pct'], 1) }}%</td>
                                <td class="py-2 px-3 text-right text-emerald-400">{{ $uzNum($item['avg_minutes_read'], 1) }} daq</td>
                                <td class="py-2 px-3 text-right">{{ $uzNum($item['finished_books_count']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-mist">Yetarli foydalanuvchili guruh yo'q (kam ma'lumot)</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(!empty($placeInsuf['has_hidden']))
                <div class="mt-2.5 px-3 py-2 rounded-badge bg-ink-950 border border-ink-border flex items-center justify-between text-[11px] font-mono text-mist">
                    <span>Maxfiylik qoidasi: &lt; {{ $segments['min_users'] ?? 5 }} kishilik {{ $placeInsuf['hidden_groups_count'] }} ta guruh yashirildi</span>
                    <span class="px-2 py-0.5 rounded-badge bg-amber-500/10 text-amber-400 border border-amber-500/25">
                        {{ $placeInsuf['label'] ?? "Kam ma'lumot" }} ({{ $placeInsuf['hidden_users_count'] }} kishi)
                    </span>
                </div>
            @endif
        </section>

    </div>

    {{-- 4. ★ "YO'QOLISH XAVFI" (Churn Risk: streak >= 7, 2+ kun nofaol) --}}
    <section class="ks-panel bg-ink-900 border border-ink-border p-4 sm:p-5"
             aria-labelledby="churn-risk-heading">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
            <div class="flex items-start gap-2.5">
                <span class="w-8 h-8 rounded-btn bg-amber-500/10 border border-amber-500/30 text-amber-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                    </svg>
                </span>
                <div>
                    <h2 id="churn-risk-heading" class="text-sm sm:text-base font-bold font-serif text-paper">
                        Yo'qolish xavfi (Churn Risk) — faol kitobxonlarni saqlab qolish ro'yxati
                    </h2>
                    <p class="text-xs text-mist font-mono mt-0.5">
                        Streak ≥ {{ $churnRisk['min_streak'] ?? 7 }} kun bo'lib, oxirgi {{ $churnRisk['inactive_days'] ?? 2 }}+ kun davomida kirmagan yoki o'qimagan foydalanuvchilar
                    </p>
                </div>
            </div>

            <span class="px-3 py-1 rounded-badge font-mono text-xs font-bold border {{ ($churnRisk['count'] ?? 0) > 0 ? 'bg-amber-500/15 text-amber-400 border-amber-500/30' : 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' }}">
                Jami xavf ostida: {{ $uzNum($churnRisk['count'] ?? 0) }} ta
            </span>
        </div>

        <div class="admin-table-scroll max-h-80 overflow-y-auto rounded-card border border-ink-border">
            <table class="w-full text-left font-mono text-xs" aria-label="Yo'qolish xavfi ostidagi foydalanuvchilar ro'yxati">
                <thead class="bg-ink-950 text-mist uppercase text-[10px] sticky top-0">
                    <tr>
                        <th class="py-2.5 px-3 border-b border-ink-border">Foydalanuvchi (username)</th>
                        <th class="py-2.5 px-3 border-b border-ink-border text-right">Joriy streak</th>
                        <th class="py-2.5 px-3 border-b border-ink-border text-right">Rekord streak</th>
                        <th class="py-2.5 px-3 border-b border-ink-border text-right">Oxirgi faol sana</th>
                        <th class="py-2.5 px-3 border-b border-ink-border text-right">Nofaol kunlar</th>
                        <th class="py-2.5 px-3 border-b border-ink-border text-right">Boshqaruv</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border/60 text-paper">
                    @forelse($churnRisk['items'] ?? ($churnRisk['users'] ?? []) as $u)
                        <tr class="hover:bg-ink-800/40">
                            <td class="py-2.5 px-3 font-semibold text-paper">
                                {{ '@' . ($u['username'] ?? ('user_' . $u['user_id'])) }}
                            </td>
                            <td class="py-2.5 px-3 text-right text-amber-400 font-bold">
                                {{ $uzNum($u['current_streak'] ?? 0) }} kun
                            </td>
                            <td class="py-2.5 px-3 text-right text-mist">
                                {{ $uzNum($u['longest_streak'] ?? 0) }} kun
                            </td>
                            <td class="py-2.5 px-3 text-right text-mist">
                                {{ $u['last_active_date'] ?? '—' }}
                            </td>
                            <td class="py-2.5 px-3 text-right text-rose-400 font-semibold">
                                {{ $uzNum($u['days_inactive'] ?? 0) }} kun
                            </td>
                            <td class="py-2.5 px-3 text-right">
                                <a href="{{ $u['admin_url'] }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-btn bg-ink-950 border border-ink-border text-[11px] text-amber-400 hover:border-amber-500/40 transition-colors duration-base">
                                    <span>Profilga o'tish</span>
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-mist">
                                Hozirda streak ≥ 7 bo'lib yo'qolish xavfi ostida turgan foydalanuvchilar yo'q.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- 5. ★ SOAT × HAFTA KUNI FAOLLIK ISSIQLIK XARITASI (7 × 24 Heatmap) --}}
    <section class="ks-panel bg-ink-900 border border-ink-border p-4 sm:p-5"
             x-data="{ tableView: false }"
             aria-labelledby="activity-heatmap-heading">
        @php
            $bestSlot = $heatmap['best_slot'] ?? ['day_name' => 'Dushanba', 'hour' => 20, 'label' => 'Dushanba, 20:00', 'count' => 0];
            $maxSess  = max((int) ($heatmap['max_value'] ?? 0), 1);
            $dayRows  = $heatmap['days'] ?? [];
        @endphp
        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="ks-badge bg-amber-500/10 text-amber-400 border border-amber-500/25 font-mono text-[10px]">
                        7 × 24 ISSIQLIK XARITASI
                    </span>
                    <h2 id="activity-heatmap-heading" class="text-sm sm:text-base font-bold font-serif text-paper">
                        Soat va hafta kuni bo'yicha faollik xaritasi (Asia/Tashkent)
                    </h2>
                </div>
                <p class="text-xs text-mist font-mono mt-1">
                    Jonli efir (Live stream) va push/telegram xabarnomalar uchun eng qulay vaqtni aniqlash
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="px-3 py-1.5 rounded-btn bg-emerald-500/10 border border-emerald-500/30 font-mono text-xs text-emerald-400 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span>
                        Tavsiya etilgan efir vaqti: <strong>{{ $bestSlot['label'] ?? 'Dushanba, 20:00' }}</strong>
                        ({{ $uzNum($bestSlot['count'] ?? 0) }} sessiya)
                    </span>
                </div>

                <button type="button"
                        @click="tableView = !tableView"
                        :aria-pressed="tableView.toString()"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-btn bg-ink-950 border border-ink-border font-mono text-[11px] text-mist hover:text-paper transition-colors duration-base">
                    <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/>
                    </svg>
                    <span x-text="tableView ? 'Xarita ko\'rinishi' : 'Jadval ko\'rinishi'">Jadval ko'rinishi</span>
                </button>
            </div>
        </div>

        {{-- Visual 7x24 Grid --}}
        <div x-show="!tableView"
             class="overflow-x-auto pb-2"
             role="img"
             aria-label="Soat va hafta kuni bo'yicha 7x24 faollik issiqlik xaritasi">
            <div class="min-w-[680px]">
                {{-- Hours Header (00..23) --}}
                <div class="grid grid-cols-[88px_repeat(24,minmax(0,1fr))] gap-1 mb-1 font-mono text-[10px] text-mist">
                    <div>Kun \ Soat</div>
                    @foreach($hoursList as $hr)
                        <div class="text-center">{{ str_pad((string) $hr, 2, '0', STR_PAD_LEFT) }}</div>
                    @endforeach
                </div>

                {{-- 7 Day Rows --}}
                @foreach($dayRows as $dayRow)
                    @php $dayName = $dayRow['day_name'] ?? ''; @endphp
                    <div class="grid grid-cols-[88px_repeat(24,minmax(0,1fr))] gap-1 mb-1 items-center">
                        <div class="font-mono text-xs font-semibold text-paper truncate pr-1">{{ $dayName }}</div>
                        @foreach($hoursList as $hr)
                            @php
                                $sessCount = (int) ($dayRow['hours'][$hr] ?? 0);
                                $ratio     = $sessCount > 0 ? ($sessCount / $maxSess) : 0;
                                $alpha     = $sessCount > 0 ? round(0.15 + ($ratio * 0.75), 2) : 0;
                                $bgStyle   = $sessCount > 0
                                    ? "background-color: rgba(245, 158, 11, {$alpha});"
                                    : 'background-color: rgba(31, 41, 61, 0.25);';
                            @endphp
                            <div class="h-7 rounded-sm border border-ink-border/40 flex items-center justify-center font-mono text-[10px] transition-transform hover:scale-110"
                                 style="{{ $bgStyle }}"
                                 title="{{ $dayName }} {{ str_pad((string) $hr, 2, '0', STR_PAD_LEFT) }}:00 — {{ $uzNum($sessCount) }} ta sessiya">
                                @if($sessCount > 0)
                                    <span class="text-paper font-medium">{{ $sessCount }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Accessible Table View ("Jadval ko'rinishi") --}}
        <div x-show="tableView" x-cloak class="admin-table-scroll max-h-80 overflow-y-auto rounded-card border border-ink-border">
            <table class="w-full text-left font-mono text-xs" aria-label="Soat va hafta kuni bo'yicha faollik jadvali">
                <thead class="bg-ink-950 text-mist uppercase text-[10px] sticky top-0">
                    <tr>
                        <th class="py-2 px-2.5 border-b border-ink-border">Kun</th>
                        @foreach($hoursList as $hr)
                            <th class="py-2 px-1.5 border-b border-ink-border text-center">{{ str_pad((string) $hr, 2, '0', STR_PAD_LEFT) }}:00</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border/60 text-paper">
                    @foreach($dayRows as $dayRow)
                        <tr>
                            <td class="py-2 px-2.5 font-semibold text-amber-400">{{ $dayRow['day_name'] ?? '' }}</td>
                            @foreach($hoursList as $hr)
                                <td class="py-2 px-1.5 text-center">
                                    {{ $uzNum($dayRow['hours'][$hr] ?? 0) }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

</div>
