@extends('admin.layouts.app')

@section('title', 'Analitika')
@section('breadcrumb', 'Analitika')

@section('content')
<div class="space-y-5">

    {{-- Top Header: Period Filter (7/30/90/Custom), Timezone Badge (Asia/Tashkent), Cache Refresh, CSV Export & Tabs --}}
    @include('admin.analytics.partials._header')

    {{-- Tab Content --}}
    @if($activeTab === 'overview')
        @include('admin.analytics.partials._tab_overview')
    @elseif($activeTab === 'users')
        @include('admin.analytics.partials._tab_users')
    @elseif(View::exists('admin.analytics.partials._tab_' . str_replace('-', '_', $activeTab)))
        @include('admin.analytics.partials._tab_' . str_replace('-', '_', $activeTab))
    @else
        <div class="ks-panel bg-ink-900 border border-ink-border p-8 text-center">
            <div class="w-10 h-10 rounded-btn bg-amber-500/10 border border-amber-500/30 text-amber-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>
                </svg>
            </div>
            <h2 class="text-base font-bold font-serif text-paper">
                Bo'lim: {{ $activeTab }}
            </h2>
            <p class="text-xs font-mono text-mist mt-1">
                Ushbu bo'lim navbatdagi bosqichda faollashtiriladi. Ma'lumotlar servis qatlamida tayyor.
            </p>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
<script>
if (typeof Chart === 'undefined') {
    document.write('<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"><\/script>');
}
</script>
<script>
(function () {
    const activeTab = @json($activeTab);
    const tabData   = @json($data ?? []);
    const chartRegistry = [];

    function readCssRgb(varName, fallbackRgb) {
        try {
            const raw = getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
            if (!raw) return fallbackRgb;
            return raw.split(/\s+/).join(', ');
        } catch (e) {
            return fallbackRgb;
        }
    }

    function getThemePalette() {
        const paperRgb  = readCssRgb('--paper-rgb', '240, 237, 230');
        const mistRgb   = readCssRgb('--mist-rgb', '139, 155, 173');
        const borderRgb = readCssRgb('--ink-border-rgb', '31, 41, 61');
        const cardRgb   = readCssRgb('--ink-900-rgb', '13, 17, 26');

        return {
            text: `rgb(${paperRgb})`,
            muted: `rgb(${mistRgb})`,
            grid: `rgba(${borderRgb}, 0.55)`,
            tooltipBg: `rgba(${cardRgb}, 0.96)`,
            tooltipBorder: `rgba(${borderRgb}, 0.9)`,
            amber: '#F59E0B',
            emerald: '#10B981',
            sky: '#38BDF8',
            indigo: '#818CF8',
            rose: '#F43F5E',
        };
    }

    function formatUzNumber(num) {
        const n = Number(num) || 0;
        return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    function applyDefaults(palette) {
        if (typeof Chart === 'undefined') return;
        Chart.defaults.color = palette.muted;
        Chart.defaults.borderColor = palette.grid;
        Chart.defaults.font.family = "'DM Mono', 'DM Sans', sans-serif";
    }

    function destroyAllCharts() {
        while (chartRegistry.length > 0) {
            const instance = chartRegistry.pop();
            try { instance.destroy(); } catch (e) {}
        }
    }

    function buildOverviewCharts(palette) {
        const trends = tabData.trends || {};
        const labels = trends.labels || [];

        // 1. Activity & Signups Line Chart
        const actCanvas = document.getElementById('chart-overview-activity');
        if (actCanvas && labels.length > 0) {
            const ctx = actCanvas.getContext('2d');
            const dauGrad = ctx.createLinearGradient(0, 0, 0, 260);
            dauGrad.addColorStop(0, 'rgba(245, 158, 11, 0.25)');
            dauGrad.addColorStop(1, 'rgba(245, 158, 11, 0.0)');

            const chart1 = new Chart(actCanvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'DAU (Jami faol)',
                            data: trends.dau || [],
                            borderColor: palette.amber,
                            backgroundColor: dauGrad,
                            borderWidth: 2.2,
                            fill: true,
                            tension: 0.32,
                            pointRadius: 2,
                            pointHoverRadius: 5,
                        },
                        {
                            label: "Faol o'quvchilar (≥5 daq)",
                            data: trends.active_readers || [],
                            borderColor: palette.emerald,
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.32,
                            pointRadius: 2,
                            pointHoverRadius: 4,
                        },
                        {
                            label: 'Yangi foydalanuvchilar',
                            data: trends.new_users || [],
                            borderColor: palette.sky,
                            backgroundColor: 'transparent',
                            borderDash: [4, 4],
                            borderWidth: 1.8,
                            tension: 0.3,
                            pointRadius: 2,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 10, padding: 12, font: { size: 11 }, color: palette.muted }
                        },
                        tooltip: {
                            backgroundColor: palette.tooltipBg,
                            titleColor: palette.text,
                            bodyColor: palette.text,
                            borderColor: palette.tooltipBorder,
                            borderWidth: 1,
                            callbacks: {
                                label: function (ctx) {
                                    return ` ${ctx.dataset.label}: ${formatUzNumber(ctx.raw)} ta`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: palette.muted, maxTicksLimit: 10 } },
                        y: { beginAtZero: true, grid: { color: palette.grid }, ticks: { color: palette.muted, precision: 0 } }
                    }
                }
            });
            chartRegistry.push(chart1);
        }

        // 2. Reading Minutes & Quiz Attempts Bar/Line Combo Chart
        const readCanvas = document.getElementById('chart-overview-reading');
        if (readCanvas && labels.length > 0) {
            const chart2 = new Chart(readCanvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            type: 'bar',
                            label: "O'qilgan daqiqalar",
                            data: trends.minutes_read || [],
                            backgroundColor: 'rgba(245, 158, 11, 0.72)',
                            hoverBackgroundColor: palette.amber,
                            borderRadius: 4,
                            yAxisID: 'y',
                        },
                        {
                            type: 'line',
                            label: 'Quiz urinishlari',
                            data: trends.quiz_attempts || [],
                            borderColor: palette.emerald,
                            backgroundColor: palette.emerald,
                            borderWidth: 2,
                            tension: 0.3,
                            pointRadius: 2.5,
                            yAxisID: 'y1',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 10, padding: 12, font: { size: 11 }, color: palette.muted }
                        },
                        tooltip: {
                            backgroundColor: palette.tooltipBg,
                            titleColor: palette.text,
                            bodyColor: palette.text,
                            borderColor: palette.tooltipBorder,
                            borderWidth: 1,
                            callbacks: {
                                label: function (ctx) {
                                    const unit = ctx.dataset.yAxisID === 'y' ? 'daq' : 'ta';
                                    return ` ${ctx.dataset.label}: ${formatUzNumber(ctx.raw)} ${unit}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: palette.muted, maxTicksLimit: 10 } },
                        y: {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            grid: { color: palette.grid },
                            ticks: { color: palette.muted, precision: 0 }
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            beginAtZero: true,
                            grid: { drawOnChartArea: false },
                            ticks: { color: palette.emerald, precision: 0 }
                        }
                    }
                }
            });
            chartRegistry.push(chart2);
        }
    }

    function buildUsersCharts(palette) {
        // 1. Funnel Horizontal Bar Chart
        const funnelCanvas = document.getElementById('chart-users-funnel');
        const steps = (tabData.funnel && tabData.funnel.steps) ? tabData.funnel.steps : [];
        if (funnelCanvas && steps.length > 0) {
            const chartFunnel = new Chart(funnelCanvas, {
                type: 'bar',
                data: {
                    labels: steps.map(s => s.label),
                    datasets: [{
                        label: 'Foydalanuvchilar soni',
                        data: steps.map(s => s.count),
                        backgroundColor: [
                            'rgba(245, 158, 11, 0.85)',
                            'rgba(56, 189, 248, 0.80)',
                            'rgba(16, 185, 129, 0.80)',
                            'rgba(129, 140, 248, 0.80)',
                            'rgba(251, 191, 36, 0.85)',
                        ],
                        borderRadius: 4,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: palette.tooltipBg,
                            titleColor: palette.text,
                            bodyColor: palette.text,
                            borderColor: palette.tooltipBorder,
                            borderWidth: 1,
                            callbacks: {
                                label: function (ctx) {
                                    const step = steps[ctx.dataIndex] || {};
                                    return ` ${formatUzNumber(ctx.raw)} ta (${step.percent_of_total || 0}% jami)`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { beginAtZero: true, grid: { color: palette.grid }, ticks: { color: palette.muted, precision: 0 } },
                        y: { grid: { display: false }, ticks: { color: palette.text, font: { size: 11 } } }
                    }
                }
            });
            chartRegistry.push(chartFunnel);
        }

        // 2. Segment: Reading Goal
        const goalCanvas = document.getElementById('chart-segment-goal');
        const goalItems = (tabData.segments && tabData.segments.by_reading_goal && tabData.segments.by_reading_goal.items)
            ? tabData.segments.by_reading_goal.items
            : [];
        if (goalCanvas && goalItems.length > 0) {
            const chartGoal = new Chart(goalCanvas, {
                type: 'bar',
                data: {
                    labels: goalItems.map(i => i.label),
                    datasets: [
                        {
                            label: 'Foydalanuvchilar',
                            data: goalItems.map(i => i.count),
                            backgroundColor: 'rgba(245, 158, 11, 0.78)',
                            borderRadius: 4,
                        },
                        {
                            label: "O'rtacha daqiqa",
                            data: goalItems.map(i => i.avg_minutes_read),
                            backgroundColor: 'rgba(16, 185, 129, 0.72)',
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 10, padding: 10, font: { size: 11 }, color: palette.muted }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: palette.muted } },
                        y: { beginAtZero: true, grid: { color: palette.grid }, ticks: { color: palette.muted, precision: 0 } }
                    }
                }
            });
            chartRegistry.push(chartGoal);
        }

        // 3. Segment: Reading Place
        const placeCanvas = document.getElementById('chart-segment-place');
        const placeItems = (tabData.segments && tabData.segments.by_reading_place && tabData.segments.by_reading_place.items)
            ? tabData.segments.by_reading_place.items
            : [];
        if (placeCanvas && placeItems.length > 0) {
            const chartPlace = new Chart(placeCanvas, {
                type: 'bar',
                data: {
                    labels: placeItems.map(i => i.label),
                    datasets: [
                        {
                            label: 'Foydalanuvchilar',
                            data: placeItems.map(i => i.count),
                            backgroundColor: 'rgba(56, 189, 248, 0.78)',
                            borderRadius: 4,
                        },
                        {
                            label: "O'rtacha daqiqa",
                            data: placeItems.map(i => i.avg_minutes_read),
                            backgroundColor: 'rgba(16, 185, 129, 0.72)',
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 10, padding: 10, font: { size: 11 }, color: palette.muted }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: palette.muted } },
                        y: { beginAtZero: true, grid: { color: palette.grid }, ticks: { color: palette.muted, precision: 0 } }
                    }
                }
            });
            chartRegistry.push(chartPlace);
        }
    }

    function initAnalyticsCharts(attempts = 0) {
        if (typeof Chart === 'undefined') {
            if (attempts < 25) {
                setTimeout(() => initAnalyticsCharts(attempts + 1), 100);
            }
            return;
        }

        destroyAllCharts();
        const palette = getThemePalette();
        applyDefaults(palette);

        if (activeTab === 'overview') {
            buildOverviewCharts(palette);
        } else if (activeTab === 'users') {
            buildUsersCharts(palette);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initAnalyticsCharts());
    } else {
        initAnalyticsCharts();
    }

    // Re-render charts with updated CSS variables when Light/Dark theme toggles
    window.addEventListener('theme-changed', () => {
        setTimeout(() => initAnalyticsCharts(), 50);
    });
})();
</script>
@endpush
