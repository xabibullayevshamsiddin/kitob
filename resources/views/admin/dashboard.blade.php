@extends('admin.layouts.app')

@section('title', 'Bosh panel')
@section('breadcrumb', 'Bosh panel')

@section('content')

{{-- Editorial Welcome Banner --}}
<div class="relative overflow-hidden rounded-panel bg-ink-900 border border-ink-border p-5 sm:p-6 mb-5">
    <div class="relative flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="ks-badge bg-amber-500/10 text-amber-400 border border-amber-500/25 font-mono">
                    ADMINISTRATOR
                </span>
                <span class="text-xs font-mono text-mist">
                    {{ now()->locale('uz')->isoFormat('dddd, D MMMM YYYY') }} • {{ now()->format('H:i') }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold font-serif text-paper">
                Xush kelibsiz, <span class="text-amber-400">{{ auth()->user()->name ?? 'Admin' }}</span>!
            </h1>
            <p class="text-mist text-xs sm:text-sm mt-1 font-sans">
                Kitobxon boshqaruv konsoli — platforma ko'rsatkichlari, foydalanuvchilar va materiallar nazorati.
            </p>
        </div>
        <div class="hidden md:flex items-center justify-center w-14 h-14 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-400">
            <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        </div>
    </div>

    <!-- Mini stats row in banner -->
    <div class="relative flex flex-wrap items-center gap-4 mt-4 pt-4 border-t border-ink-border text-xs font-mono">
        <div class="flex items-center gap-2 text-emerald-400">
            <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
            <span>Tizim barqaror</span>
        </div>
        <div class="flex items-center gap-2 text-mist">
            <span class="text-amber-400">✦</span>
            <span>PHP {{ PHP_VERSION }}</span>
        </div>
        <div class="flex items-center gap-2 text-mist">
            <span class="text-amber-400">✦</span>
            <span>Laravel {{ app()->version() }}</span>
        </div>
    </div>
</div>

{{-- KPI Cards Row (DENSITY: 7, p-4) --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5 mb-5">

    {{-- Total Users --}}
    <div class="ks-panel p-4 bg-ink-900 border border-ink-border"
         x-data="counterCard({{ $stats['total_users'] ?? 0 }})">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] text-mist font-mono uppercase tracking-wider mb-1">Jami foydalanuvchilar</p>
                <p class="text-2xl font-bold font-mono text-paper ks-stat" x-text="displayValue">{{ number_format($stats['total_users'] ?? 0) }}</p>
                @php $uGrowth = (int) ($stats['user_growth_pct'] ?? 0); @endphp
                <div class="flex items-center gap-1.5 mt-1 font-mono text-xs {{ $uGrowth > 0 ? 'text-emerald-400' : ($uGrowth < 0 ? 'text-rose-400' : 'text-mist') }}">
                    <span>{{ $uGrowth > 0 ? '↑ +' : ($uGrowth < 0 ? '↓ ' : '') }}{{ $uGrowth }}%</span>
                    <span class="text-mist text-[10px]">oylik dinamika</span>
                </div>
            </div>
            <div class="w-9 h-9 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-amber-400 flex-shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <div class="mt-3 h-1 rounded-full bg-ink-950 overflow-hidden border border-ink-border/50">
            <div class="h-full bg-amber-500 rounded-full" style="width: 100%"></div>
        </div>
    </div>

    {{-- Total Students --}}
    <div class="ks-panel p-4 bg-ink-900 border border-ink-border"
         x-data="counterCard({{ $stats['total_students'] ?? 0 }})">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] text-mist font-mono uppercase tracking-wider mb-1">O'quvchilar</p>
                <p class="text-2xl font-bold font-mono text-paper ks-stat" x-text="displayValue">{{ number_format($stats['total_students'] ?? 0) }}</p>
                <div class="flex items-center gap-1.5 mt-1 font-mono text-xs text-emerald-400">
                    <span>{{ $stats['student_pct'] ?? 0 }}%</span>
                    <span class="text-mist text-[10px]">jami a'zolardan</span>
                </div>
            </div>
            <div class="w-9 h-9 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-emerald-400 flex-shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            </div>
        </div>
        <div class="mt-3 h-1 rounded-full bg-ink-950 overflow-hidden border border-ink-border/50">
            <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: {{ max($stats['student_pct'] ?? 0, 5) }}%"></div>
        </div>
    </div>

    {{-- Total Teachers --}}
    <div class="ks-panel p-4 bg-ink-900 border border-ink-border"
         x-data="counterCard({{ $stats['total_teachers'] ?? 0 }})">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] text-mist font-mono uppercase tracking-wider mb-1">O'qituvchilar</p>
                <p class="text-2xl font-bold font-mono text-paper ks-stat" x-text="displayValue">{{ number_format($stats['total_teachers'] ?? 0) }}</p>
                <div class="flex items-center gap-1.5 mt-1 font-mono text-xs text-amber-400">
                    <span>{{ $stats['teacher_pct'] ?? 0 }}%</span>
                    <span class="text-mist text-[10px]">jami a'zolardan</span>
                </div>
            </div>
            <div class="w-9 h-9 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-amber-400 flex-shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            </div>
        </div>
        <div class="mt-3 h-1 rounded-full bg-ink-950 overflow-hidden border border-ink-border/50">
            <div class="h-full bg-amber-400 rounded-full transition-all duration-500" style="width: {{ max($stats['teacher_pct'] ?? 0, 5) }}%"></div>
        </div>
    </div>

    {{-- Total Books --}}
    <div class="ks-panel p-4 bg-ink-900 border border-ink-border"
         x-data="counterCard({{ $stats['total_books'] ?? 0 }})">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] text-mist font-mono uppercase tracking-wider mb-1">Jami kitoblar</p>
                <p class="text-2xl font-bold font-mono text-paper ks-stat" x-text="displayValue">{{ number_format($stats['total_books'] ?? 0) }}</p>
                <div class="flex items-center gap-1.5 mt-1 font-mono text-xs text-amber-500">
                    <span>{{ $stats['active_books'] ?? 0 }} faol</span>
                    <span class="text-mist text-[10px]">({{ $stats['active_books_pct'] ?? 0 }}%)</span>
                </div>
            </div>
            <div class="w-9 h-9 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-amber-500 flex-shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
        </div>
        <div class="mt-3 h-1 rounded-full bg-ink-950 overflow-hidden border border-ink-border/50">
            <div class="h-full bg-amber-500 rounded-full transition-all duration-500" style="width: {{ max($stats['active_books_pct'] ?? 0, 5) }}%"></div>
        </div>
    </div>
</div>

{{-- Main Content: Recent Users + Quick Actions --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-5">

    {{-- Recent Users Table (2/3 width) --}}
    <div class="xl:col-span-2 bg-ink-900 border border-ink-border rounded-panel overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-ink-border">
            <div>
                <h2 class="text-sm font-bold font-serif text-paper">So'nggi ro'yxatdan o'tganlar</h2>
                <p class="text-xs text-mist font-mono">Oxirgi 5 foydalanuvchi</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-xs text-amber-400 hover:text-amber-300 font-mono flex items-center gap-1 transition-colors">
                Barchasini ko'rish →
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-ink-border bg-ink-950/40 text-[11px] font-mono uppercase text-mist">
                        <th class="py-2.5 px-3.5">Foydalanuvchi</th>
                        <th class="py-2.5 px-3.5">Rol</th>
                        <th class="py-2.5 px-3.5">Qo'shilgan</th>
                        <th class="py-2.5 px-3.5">Holat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border/50 text-xs">
                    @forelse($recentUsers ?? [] as $user)
                        <tr class="hover:bg-ink-800/40 transition-colors">
                            <td class="py-2.5 px-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-xs font-mono font-bold text-paper flex-shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-paper truncate">{{ $user->name }}</p>
                                        <p class="text-[10px] text-mist font-mono truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-3.5">
                                @php
                                    $role = $user->roles->first()?->name ?? 'student';
                                    $roleColors = [
                                        'admin' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
                                        'teacher' => 'bg-ink-950 text-mist border-ink-border',
                                        'student' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                    ];
                                    $roleLabels = [
                                        'admin' => 'Admin',
                                        'teacher' => "O'qituvchi",
                                        'student' => "O'quvchi",
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-badge text-[10px] font-mono font-semibold border {{ $roleColors[$role] ?? 'bg-ink-950 text-mist border-ink-border' }}">
                                    {{ $roleLabels[$role] ?? $role }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3.5 font-mono text-[11px] text-mist">
                                {{ $user->created_at->format('d.m.Y') }}
                            </td>
                            <td class="py-2.5 px-3.5">
                                <div class="flex items-center gap-1.5 font-mono text-[11px] text-emerald-400">
                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
                                    <span>Faol</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-mist font-mono text-xs">
                                Foydalanuvchilar mavjud emas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Quick Actions + System Status (1/3 width) --}}
    <div class="flex flex-col gap-4">

        {{-- Quick Actions --}}
        <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
            <h2 class="text-xs font-mono uppercase tracking-wider text-mist mb-3">Tezkor amallar</h2>
            <div class="space-y-2">
                <a href="{{ route('admin.books.index') }}"
                   class="flex items-center gap-3 p-2.5 rounded-btn bg-ink-950 border border-ink-border hover:border-amber-400/40 text-paper transition-colors group">
                    <span class="w-7 h-7 rounded-badge bg-amber-500/10 text-amber-400 flex items-center justify-center text-xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-paper">Kitob boshqaruvi</p>
                        <p class="text-[10px] text-mist font-mono">Kitob qo'shish va tahrirlash</p>
                    </div>
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 p-2.5 rounded-btn bg-ink-950 border border-ink-border hover:border-amber-400/40 text-paper transition-colors group">
                    <span class="w-7 h-7 rounded-badge bg-amber-500/10 text-amber-400 flex items-center justify-center text-xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-paper">Foydalanuvchilar</p>
                        <p class="text-[10px] text-mist font-mono">Rollarni belgilash va nazorat</p>
                    </div>
                </a>

                <a href="{{ route('admin.stats') }}"
                   class="flex items-center gap-3 p-2.5 rounded-btn bg-ink-950 border border-ink-border hover:border-amber-400/40 text-paper transition-colors group">
                    <span class="w-7 h-7 rounded-badge bg-amber-500/10 text-amber-400 flex items-center justify-center text-xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-paper">Batafsil statistika</p>
                        <p class="text-[10px] text-mist font-mono">Platforma tahlili</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- System Status --}}
        <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
            <h2 class="text-xs font-mono uppercase tracking-wider text-mist mb-3">Tizim holati</h2>
            <div class="space-y-2 text-xs font-mono">
                <div class="flex items-center justify-between py-1 border-b border-ink-border/50">
                    <span class="text-mist">PHP versiya</span>
                    <span class="text-paper font-semibold">{{ PHP_VERSION }}</span>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-ink-border/50">
                    <span class="text-mist">Laravel</span>
                    <span class="text-paper font-semibold">{{ app()->version() }}</span>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-ink-border/50">
                    <span class="text-mist">Ma'lumotlar bazasi</span>
                    <span class="text-emerald-400 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Ulangan
                    </span>
                </div>
                <div class="flex items-center justify-between py-1">
                    <span class="text-mist">Kesh</span>
                    <span class="text-emerald-400 font-semibold">Faol</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Real Chart.js Charts --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-5">
    <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
        <div class="flex items-center justify-between mb-3 border-b border-ink-border pb-3">
            <div>
                <h2 class="text-sm font-bold font-serif text-paper">Foydalanuvchilar o'sishi</h2>
                <p class="text-[11px] text-mist font-mono">So'nggi 30 kunlik dinamika</p>
            </div>
            @php $growthPct = (int) ($stats['user_growth_pct'] ?? 0); @endphp
            <span class="text-xs font-mono px-2 py-0.5 rounded-badge border {{ $growthPct > 0 ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : ($growthPct < 0 ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : 'bg-ink-800 text-mist border-ink-border') }}">
                {{ $growthPct > 0 ? '↑ +' : ($growthPct < 0 ? '↓ ' : '') }}{{ $growthPct }}% oyiga
            </span>
        </div>
        <div class="h-60 relative"><canvas id="chart-signups"></canvas></div>
    </div>

    <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
        <div class="flex items-center justify-between mb-3 border-b border-ink-border pb-3">
            <div>
                <h2 class="text-sm font-bold font-serif text-paper">O'qilgan daqiqalar</h2>
                <p class="text-[11px] text-mist font-mono">Kunlik faol mutolaa (so'nggi 14 kun)</p>
            </div>
            <span class="text-xs font-mono text-amber-400 font-semibold">
                Jami: {{ number_format($stats['total_reading_minutes'] ?? 0) }} daq ({{ $stats['total_reading_hours'] ?? 0 }} soat)
            </span>
        </div>
        <div class="h-60 relative"><canvas id="chart-minutes"></canvas></div>
    </div>
</div>

{{-- Format distribution + Summary numbers --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-5">
    <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
        <div class="mb-3 border-b border-ink-border pb-2">
            <h2 class="text-sm font-bold font-serif text-paper">Kontent taqsimoti</h2>
            <p class="text-[11px] text-mist font-mono">Formatlar bo'yicha materiallar</p>
        </div>
        <div class="h-52 relative"><canvas id="chart-formats"></canvas></div>
    </div>

    <div class="xl:col-span-2 grid grid-cols-2 gap-3.5">
        <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
            <p class="text-[11px] text-mist font-mono uppercase mb-1">Bugun faollar</p>
            <p class="text-2xl font-bold font-mono text-paper">{{ number_format($stats['online_today'] ?? 1) }}</p>
            <p class="text-[10px] text-mist mt-1 font-mono">Kunlik tashrif va mutolaa</p>
        </div>
        <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
            <p class="text-[11px] text-mist font-mono uppercase mb-1">Jami ballar</p>
            <p class="text-2xl font-bold font-mono text-amber-400">{{ number_format($stats['total_points'] ?? 0) }}</p>
            <p class="text-[10px] text-mist mt-1 font-mono">Barcha a'zolar to'plagan</p>
        </div>
        <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
            <p class="text-[11px] text-mist font-mono uppercase mb-1">Guruhlar</p>
            <p class="text-2xl font-bold font-mono text-emerald-400">{{ number_format($stats['total_groups'] ?? 0) }}</p>
            <p class="text-[10px] text-mist mt-1 font-mono">Kitobxon klublari</p>
        </div>
        <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
            <p class="text-[11px] text-mist font-mono uppercase mb-1">Test topshiriqlari</p>
            <p class="text-2xl font-bold font-mono text-amber-500">{{ number_format($stats['total_quizzes'] ?? 0) }}</p>
            <p class="text-[10px] text-mist mt-1 font-mono">Bilimni sinash viktorinalari</p>
        </div>
    </div>
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
// Global counterCard definition immediately available to Alpine
function counterCard(target) {
    const num = Number(target) || 0;
    return {
        displayValue: num.toLocaleString('uz-UZ'),
        init() {
            if (num > 0) {
                this.animateCounter(num);
            }
        },
        animateCounter(targetVal) {
            const duration = 800;
            const start = performance.now();
            const step = (currentTime) => {
                const elapsed = currentTime - start;
                const progress = Math.min(elapsed / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = Math.round(eased * targetVal);
                this.displayValue = current.toLocaleString('uz-UZ');
                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            };
            requestAnimationFrame(step);
        }
    };
}
window.counterCard = counterCard;
if (window.Alpine) {
    window.Alpine.data('counterCard', counterCard);
} else {
    document.addEventListener('alpine:init', () => {
        if (window.Alpine) window.Alpine.data('counterCard', counterCard);
    });
}

// Chart.js initialization
const signupLabels = @json($signupLabels ?? []);
const signupData   = @json($signupData ?? []);
const minutesLabels = @json($minutesLabels ?? []);
const minutesData   = @json($minutesData ?? []);
const formatData    = {!! json_encode($formatData ?? [0, 0, 0, 0]) !!};

function initDashboardCharts(attempts = 0) {
    if (typeof Chart === 'undefined') {
        if (attempts < 25) {
            setTimeout(() => initDashboardCharts(attempts + 1), 100);
        }
        return;
    }

    Chart.defaults.color = '#8B9BAD';
    Chart.defaults.borderColor = 'rgba(31, 41, 61, 0.6)';
    Chart.defaults.font.family = "'DM Sans', sans-serif";

    // 1. Line Chart: Signups
    const signupsCtx = document.getElementById('chart-signups');
    if (signupsCtx && !signupsCtx._chartInstance) {
        const grad = signupsCtx.getContext('2d').createLinearGradient(0, 0, 0, 240);
        grad.addColorStop(0, 'rgba(245, 158, 11, 0.25)');
        grad.addColorStop(1, 'rgba(245, 158, 11, 0)');
        signupsCtx._chartInstance = new Chart(signupsCtx, {
            type: 'line',
            data: {
                labels: signupLabels,
                datasets: [{
                    label: 'Jami foydalanuvchilar',
                    data: signupData,
                    borderColor: '#F59E0B',
                    backgroundColor: grad,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2,
                    pointHoverRadius: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: false, ticks: { precision: 0 } }
                }
            }
        });
    }

    // 2. Bar Chart: Minutes
    const minutesCtx = document.getElementById('chart-minutes');
    if (minutesCtx && !minutesCtx._chartInstance) {
        minutesCtx._chartInstance = new Chart(minutesCtx, {
            type: 'bar',
            data: {
                labels: minutesLabels,
                datasets: [{
                    label: 'Daqiqalar',
                    data: minutesData,
                    backgroundColor: 'rgba(245, 158, 11, 0.75)',
                    hoverBackgroundColor: '#F59E0B',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }

    // 3. Doughnut Chart: Content Formats
    const formatsCtx = document.getElementById('chart-formats');
    if (formatsCtx && !formatsCtx._chartInstance) {
        const formatsTotal = formatData.reduce((a, b) => a + b, 0);
        formatsCtx._chartInstance = new Chart(formatsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Boblar (Matn)', 'Audio darslar', 'Video darslar', 'Testlar'],
                datasets: [{
                    data: formatsTotal > 0 ? formatData : [1, 0, 0, 0],
                    backgroundColor: formatsTotal > 0
                        ? ['#F59E0B', '#10B981', '#C1392B', '#8B5CF6']
                        : ['#1F293D', '#1F293D', '#1F293D', '#1F293D'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, padding: 10, font: { size: 11 }, color: '#8B9BAD' }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (formatsTotal === 0) return ' Hozircha material yuklanmagan';
                                return ` ${context.label}: ${context.raw} ta`;
                            }
                        }
                    }
                },
                cutout: '70%',
            }
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initDashboardCharts());
} else {
    initDashboardCharts();
}
</script>
@endpush
