@extends('admin.layouts.app')

@section('title', 'Statistika')
@section('breadcrumb', 'Statistika')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-white">Platforma statistikasi</h1>
        <p class="text-sm text-slate-500 mt-0.5">Umumiy ko'rsatkichlar va tahlil</p>
    </div>
    <div class="flex items-center gap-2 text-xs text-slate-500 bg-slate-900 border border-slate-800 px-3 py-2 rounded-xl">
        <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
        Oxirgi yangilanish: {{ now()->format('H:i') }}
    </div>
</div>

{{-- Big Stats Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">

    {{-- Total Users --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-800 bg-gradient-to-br from-indigo-900/40 to-slate-900 p-6 group hover:border-indigo-500/30 transition-all duration-300">
        <div class="absolute top-0 right-0 w-32 h-32 rounded-full bg-indigo-600/10 -translate-y-8 translate-x-8"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-indigo-400/70 uppercase tracking-widest mb-3">Jami foydalanuvchilar</p>
                <p class="text-5xl font-black text-white mb-1">{{ number_format($stats['total_users'] ?? 0) }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded-lg border border-emerald-500/20">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        +12.5%
                    </span>
                    <span class="text-xs text-slate-600">o'tgan oyga nisbatan</span>
                </div>
            </div>
            <div class="w-16 h-16 rounded-2xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-3xl shadow-lg">
                👥
            </div>
        </div>
        <div class="mt-5 h-2 rounded-full bg-slate-800 overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500 transition-all" style="width: 72%"></div>
        </div>
        <p class="text-[10px] text-slate-600 mt-1.5">Maqsad: 1,000 ta</p>
    </div>

    {{-- Total Students --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-800 bg-gradient-to-br from-emerald-900/30 to-slate-900 p-6 group hover:border-emerald-500/30 transition-all duration-300">
        <div class="absolute top-0 right-0 w-32 h-32 rounded-full bg-emerald-600/10 -translate-y-8 translate-x-8"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-emerald-400/70 uppercase tracking-widest mb-3">O'quvchilar</p>
                <p class="text-5xl font-black text-white mb-1">{{ number_format($stats['total_students'] ?? 0) }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded-lg border border-emerald-500/20">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        +8.3%
                    </span>
                    <span class="text-xs text-slate-600">o'tgan oyga nisbatan</span>
                </div>
            </div>
            <div class="w-16 h-16 rounded-2xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-3xl shadow-lg">
                🎓
            </div>
        </div>
        <div class="mt-5 h-2 rounded-full bg-slate-800 overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500" style="width: 58%"></div>
        </div>
        @php
            $studentPercent = $stats['total_users'] > 0 ? round(($stats['total_students'] / $stats['total_users']) * 100) : 0;
        @endphp
        <p class="text-[10px] text-slate-600 mt-1.5">Jami foydalanuvchilardan {{ $studentPercent }}%</p>
    </div>

    {{-- Total Teachers --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-800 bg-gradient-to-br from-amber-900/30 to-slate-900 p-6 group hover:border-amber-500/30 transition-all duration-300">
        <div class="absolute top-0 right-0 w-32 h-32 rounded-full bg-amber-600/10 -translate-y-8 translate-x-8"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-amber-400/70 uppercase tracking-widest mb-3">O'qituvchilar</p>
                <p class="text-5xl font-black text-white mb-1">{{ number_format($stats['total_teachers'] ?? 0) }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-400 bg-amber-500/10 px-2 py-1 rounded-lg border border-amber-500/20">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        +3.1%
                    </span>
                    <span class="text-xs text-slate-600">o'tgan oyga nisbatan</span>
                </div>
            </div>
            <div class="w-16 h-16 rounded-2xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-3xl shadow-lg">
                👨‍🏫
            </div>
        </div>
        <div class="mt-5 h-2 rounded-full bg-slate-800 overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-500" style="width: 35%"></div>
        </div>
        @php
            $teacherPercent = $stats['total_users'] > 0 ? round(($stats['total_teachers'] / $stats['total_users']) * 100) : 0;
        @endphp
        <p class="text-[10px] text-slate-600 mt-1.5">Jami foydalanuvchilardan {{ $teacherPercent }}%</p>
    </div>

    {{-- Total Books --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-800 bg-gradient-to-br from-purple-900/30 to-slate-900 p-6 group hover:border-purple-500/30 transition-all duration-300">
        <div class="absolute top-0 right-0 w-32 h-32 rounded-full bg-purple-600/10 -translate-y-8 translate-x-8"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-purple-400/70 uppercase tracking-widest mb-3">Jami kitoblar</p>
                <p class="text-5xl font-black text-white mb-1">{{ number_format($stats['total_books'] ?? 0) }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-purple-400 bg-purple-500/10 px-2 py-1 rounded-lg border border-purple-500/20">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        +21.4%
                    </span>
                    <span class="text-xs text-slate-600">o'tgan oyga nisbatan</span>
                </div>
            </div>
            <div class="w-16 h-16 rounded-2xl bg-purple-500/20 border border-purple-500/30 flex items-center justify-center text-3xl shadow-lg">
                📚
            </div>
        </div>
        <div class="mt-5 h-2 rounded-full bg-slate-800 overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-purple-500 to-pink-500" style="width: 88%"></div>
        </div>
        <p class="text-[10px] text-slate-600 mt-1.5">Maqsad: 500 ta kitob</p>
    </div>

    {{-- Active Books --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-800 bg-gradient-to-br from-teal-900/30 to-slate-900 p-6 group hover:border-teal-500/30 transition-all duration-300">
        <div class="absolute top-0 right-0 w-32 h-32 rounded-full bg-teal-600/10 -translate-y-8 translate-x-8"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-teal-400/70 uppercase tracking-widest mb-3">Faol kitoblar</p>
                <p class="text-5xl font-black text-white mb-1">{{ number_format($stats['active_books'] ?? 0) }}</p>
                <div class="flex items-center gap-2 mt-2">
                    @php
                        $activePercent = isset($stats['total_books'], $stats['active_books']) && $stats['total_books'] > 0
                            ? round(($stats['active_books'] / $stats['total_books']) * 100) : 0;
                    @endphp
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-teal-400 bg-teal-500/10 px-2 py-1 rounded-lg border border-teal-500/20">
                        📊 {{ $activePercent }}%
                    </span>
                    <span class="text-xs text-slate-600">umumiy kitoblardan</span>
                </div>
            </div>
            <div class="w-16 h-16 rounded-2xl bg-teal-500/20 border border-teal-500/30 flex items-center justify-center text-3xl shadow-lg">
                ✅
            </div>
        </div>
        <div class="mt-5 h-2 rounded-full bg-slate-800 overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-cyan-500" style="width: {{ $activePercent }}%"></div>
        </div>
        <p class="text-[10px] text-slate-600 mt-1.5">Nofaol: {{ ($stats['total_books'] ?? 0) - ($stats['active_books'] ?? 0) }} ta</p>
    </div>

    {{-- Admins --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-800 bg-gradient-to-br from-red-900/20 to-slate-900 p-6 group hover:border-red-500/20 transition-all duration-300">
        <div class="absolute top-0 right-0 w-32 h-32 rounded-full bg-red-600/10 -translate-y-8 translate-x-8"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-red-400/70 uppercase tracking-widest mb-3">Adminlar</p>
                <p class="text-5xl font-black text-white mb-1">{{ number_format($stats['total_admins'] ?? 1) }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-red-400 bg-red-500/10 px-2 py-1 rounded-lg border border-red-500/20">
                        🔐 To'liq kirish
                    </span>
                </div>
            </div>
            <div class="w-16 h-16 rounded-2xl bg-red-500/20 border border-red-500/30 flex items-center justify-center text-3xl shadow-lg">
                🛡️
            </div>
        </div>
        <div class="mt-5 h-2 rounded-full bg-slate-800 overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-red-500 to-rose-500" style="width: 15%"></div>
        </div>
        <p class="text-[10px] text-slate-600 mt-1.5">Cheklangan kirish</p>
    </div>
</div>

{{-- Breakdown Table + Chart Placeholder --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

    {{-- Distribution table --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-800">
            <h2 class="text-sm font-semibold text-white">Foydalanuvchilar taqsimoti</h2>
            <p class="text-xs text-slate-500 mt-0.5">Rollar bo'yicha ko'rsatkichlar</p>
        </div>
        <div class="p-5 space-y-4">
            @php
                $distributions = [
                    ['label' => "O'quvchilar", 'emoji' => '🎓', 'count' => $stats['total_students'] ?? 0, 'total' => $stats['total_users'] ?? 1, 'colorBar' => 'from-emerald-500 to-teal-400', 'colorText' => 'text-emerald-400', 'colorBg' => 'bg-emerald-500/10'],
                    ['label' => "O'qituvchilar", 'emoji' => '👨‍🏫', 'count' => $stats['total_teachers'] ?? 0, 'total' => $stats['total_users'] ?? 1, 'colorBar' => 'from-amber-500 to-orange-400', 'colorText' => 'text-amber-400', 'colorBg' => 'bg-amber-500/10'],
                    ['label' => 'Adminlar', 'emoji' => '🛡️', 'count' => $stats['total_admins'] ?? 1, 'total' => $stats['total_users'] ?? 1, 'colorBar' => 'from-red-500 to-rose-400', 'colorText' => 'text-red-400', 'colorBg' => 'bg-red-500/10'],
                ];
            @endphp
            @foreach($distributions as $d)
                @php
                    $pct = $d['total'] > 0 ? round(($d['count'] / $d['total']) * 100) : 0;
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span>{{ $d['emoji'] }}</span>
                            <span class="text-sm text-slate-300">{{ $d['label'] }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-bold {{ $d['colorText'] }}">{{ number_format($d['count']) }}</span>
                            <span class="text-xs {{ $d['colorText'] }} {{ $d['colorBg'] }} px-2 py-0.5 rounded-lg font-medium w-12 text-center">{{ $pct }}%</span>
                        </div>
                    </div>
                    <div class="h-2 rounded-full bg-slate-800 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r {{ $d['colorBar'] }} transition-all duration-500"
                             style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Books distribution --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-800">
            <h2 class="text-sm font-semibold text-white">Kitoblar holati</h2>
            <p class="text-xs text-slate-500 mt-0.5">Faol va nofaol kitoblar nisbati</p>
        </div>
        <div class="p-5">
            @php
                $activeBooks = $stats['active_books'] ?? 0;
                $totalBooks = $stats['total_books'] ?? 0;
                $inactiveBooks = $totalBooks - $activeBooks;
                $activePct = $totalBooks > 0 ? round(($activeBooks / $totalBooks) * 100) : 0;
                $inactivePct = 100 - $activePct;
            @endphp

            {{-- Big donut-style display --}}
            <div class="flex items-center justify-center gap-8 py-4">
                <div class="text-center">
                    <div class="text-4xl font-black text-emerald-400">{{ $activePct }}%</div>
                    <p class="text-xs text-slate-500 mt-1">Faol</p>
                    <p class="text-sm font-bold text-white mt-0.5">{{ number_format($activeBooks) }} ta</p>
                </div>
                <div class="w-px h-16 bg-slate-700"></div>
                <div class="text-center">
                    <div class="text-4xl font-black text-red-400">{{ $inactivePct }}%</div>
                    <p class="text-xs text-slate-500 mt-1">Nofaol</p>
                    <p class="text-sm font-bold text-white mt-0.5">{{ number_format($inactiveBooks) }} ta</p>
                </div>
            </div>

            {{-- Combined bar --}}
            <div class="mt-4">
                <div class="flex h-4 rounded-full overflow-hidden bg-slate-800 shadow-inner">
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-400 transition-all duration-700" style="width: {{ $activePct }}%"></div>
                    <div class="bg-gradient-to-r from-red-500/50 to-rose-500/50 flex-1"></div>
                </div>
                <div class="flex justify-between mt-2">
                    <div class="flex items-center gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                        <span class="text-xs text-slate-500">Faol kitoblar</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-red-500/50"></div>
                        <span class="text-xs text-slate-500">Nofaol kitoblar</span>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-slate-800 grid grid-cols-3 gap-3 text-center">
                <div>
                    <p class="text-xs text-slate-600">Jami</p>
                    <p class="text-lg font-bold text-white">{{ number_format($totalBooks) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-600">Haftalik</p>
                    <p class="text-lg font-bold text-violet-400">{{ $stats['weekly_books'] ?? 0 }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-600">Bu oy</p>
                    <p class="text-lg font-bold text-indigo-400">{{ $stats['monthly_books'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
