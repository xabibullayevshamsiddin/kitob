@extends('admin.layouts.app')

@section('title', 'Bosh panel')
@section('breadcrumb', 'Bosh panel')

@section('content')

{{-- Welcome Banner --}}
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-700 p-6 mb-6 shadow-2xl shadow-indigo-900/40">
    <!-- Background decoration -->
    <div class="absolute inset-0 opacity-20">
        <div class="absolute top-0 right-0 w-64 h-64 rounded-full bg-white/10 -translate-y-1/2 translate-x-1/4"></div>
        <div class="absolute bottom-0 left-1/3 w-48 h-48 rounded-full bg-white/5 translate-y-1/2"></div>
        <div class="absolute top-1/2 left-0 w-32 h-32 rounded-full bg-white/10 -translate-x-1/2 -translate-y-1/2"></div>
    </div>

    <div class="relative flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-2xl">👋</span>
                <span class="text-xs font-medium text-indigo-200 bg-white/10 px-2.5 py-1 rounded-full">Administrator</span>
            </div>
            <h1 class="text-2xl font-bold text-white mt-2">
                Xush kelibsiz, <span class="text-yellow-300">{{ auth()->user()->name ?? 'Admin' }}</span>!
            </h1>
            <p class="text-indigo-200 text-sm mt-1">
                {{ now()->locale('uz')->isoFormat('dddd, D MMMM YYYY') }} • {{ now()->format('H:i') }}
            </p>
        </div>
        <div class="hidden md:block">
            <div class="text-7xl opacity-60 select-none">📖</div>
        </div>
    </div>

    <!-- Mini stats row in banner -->
    <div class="relative flex items-center gap-6 mt-5 pt-5 border-t border-white/20">
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
            <span class="text-xs text-indigo-200">Tizim ishlayapti</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
            <span class="text-xs text-indigo-200">PHP {{ PHP_VERSION }}</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-blue-400"></div>
            <span class="text-xs text-indigo-200">Laravel {{ app()->version() }}</span>
        </div>
    </div>
</div>

{{-- KPI Cards Row --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

    {{-- Total Users --}}
    <div class="relative overflow-hidden rounded-2xl bg-slate-900 border border-slate-800 p-5 hover:border-indigo-500/30 transition-all duration-300 group"
         x-data="counterCard({{ $stats['total_users'] ?? 0 }})">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-2">Jami foydalanuvchilar</p>
                <p class="text-3xl font-bold text-white" x-text="displayValue">0</p>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="text-emerald-400 text-xs font-medium flex items-center gap-0.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        +12%
                    </span>
                    <span class="text-slate-600 text-xs">bu oy</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/15 border border-indigo-500/20 flex items-center justify-center text-2xl flex-shrink-0">
                👥
            </div>
        </div>
        <!-- Progress bar -->
        <div class="mt-4 h-1 rounded-full bg-slate-800 overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500" style="width: 72%"></div>
        </div>
    </div>

    {{-- Total Students --}}
    <div class="relative overflow-hidden rounded-2xl bg-slate-900 border border-slate-800 p-5 hover:border-emerald-500/30 transition-all duration-300 group"
         x-data="counterCard({{ $stats['total_students'] ?? 0 }})">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-600/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-2">O'quvchilar</p>
                <p class="text-3xl font-bold text-white" x-text="displayValue">0</p>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="text-emerald-400 text-xs font-medium flex items-center gap-0.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        +8%
                    </span>
                    <span class="text-slate-600 text-xs">bu oy</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/15 border border-emerald-500/20 flex items-center justify-center text-2xl flex-shrink-0">
                🎓
            </div>
        </div>
        <div class="mt-4 h-1 rounded-full bg-slate-800 overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500" style="width: 58%"></div>
        </div>
    </div>

    {{-- Total Teachers --}}
    <div class="relative overflow-hidden rounded-2xl bg-slate-900 border border-slate-800 p-5 hover:border-amber-500/30 transition-all duration-300 group"
         x-data="counterCard({{ $stats['total_teachers'] ?? 0 }})">
        <div class="absolute inset-0 bg-gradient-to-br from-amber-600/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-2">O'qituvchilar</p>
                <p class="text-3xl font-bold text-white" x-text="displayValue">0</p>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="text-amber-400 text-xs font-medium flex items-center gap-0.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        +3%
                    </span>
                    <span class="text-slate-600 text-xs">bu oy</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/15 border border-amber-500/20 flex items-center justify-center text-2xl flex-shrink-0">
                👨‍🏫
            </div>
        </div>
        <div class="mt-4 h-1 rounded-full bg-slate-800 overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-500" style="width: 35%"></div>
        </div>
    </div>

    {{-- Total Books --}}
    <div class="relative overflow-hidden rounded-2xl bg-slate-900 border border-slate-800 p-5 hover:border-purple-500/30 transition-all duration-300 group"
         x-data="counterCard({{ $stats['total_books'] ?? 0 }})">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-600/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-2">Jami kitoblar</p>
                <p class="text-3xl font-bold text-white" x-text="displayValue">0</p>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="text-purple-400 text-xs font-medium flex items-center gap-0.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        +21%
                    </span>
                    <span class="text-slate-600 text-xs">bu oy</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-500/15 border border-purple-500/20 flex items-center justify-center text-2xl flex-shrink-0">
                📚
            </div>
        </div>
        <div class="mt-4 h-1 rounded-full bg-slate-800 overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-purple-500 to-pink-500" style="width: 88%"></div>
        </div>
    </div>
</div>

{{-- Main Content: Recent Users + Quick Actions --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">

    {{-- Recent Users Table (2/3 width) --}}
    <div class="xl:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
            <div>
                <h2 class="text-sm font-semibold text-white">So'nggi ro'yxatdan o'tganlar</h2>
                <p class="text-xs text-slate-500 mt-0.5">Oxirgi 5 foydalanuvchi</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium flex items-center gap-1 transition-colors">
                Barchasini ko'rish
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left border-b border-slate-800">
                        <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Foydalanuvchi</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Rol</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Qo'shilgan</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Holat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($recentUsers ?? [] as $user)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-200">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                @php
                                    $role = $user->roles->first()?->name ?? 'student';
                                    $roleColors = [
                                        'admin' => 'bg-red-500/15 text-red-400 border-red-500/20',
                                        'teacher' => 'bg-blue-500/15 text-blue-400 border-blue-500/20',
                                        'student' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20',
                                    ];
                                    $roleLabels = [
                                        'admin' => 'Admin',
                                        'teacher' => "O'qituvchi",
                                        'student' => "O'quvchi",
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium border {{ $roleColors[$role] ?? 'bg-slate-500/15 text-slate-400 border-slate-500/20' }}">
                                    {{ $roleLabels[$role] ?? $role }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-sm text-slate-400">{{ $user->created_at->format('d.m.Y') }}</p>
                                <p class="text-xs text-slate-600">{{ $user->created_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
                                    <span class="text-xs text-slate-500">Faol</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center">
                                <div class="text-4xl mb-3">👤</div>
                                <p class="text-slate-500 text-sm">Foydalanuvchilar mavjud emas</p>
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
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <h2 class="text-sm font-semibold text-white mb-4">Tezkor amallar</h2>
            <div class="space-y-2.5">
                <a href="{{ route('admin.books.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 hover:bg-indigo-500/20 hover:border-indigo-500/40 transition-all group">
                    <span class="text-lg">📚</span>
                    <div>
                        <p class="text-sm font-medium">Kitob qo'shish</p>
                        <p class="text-xs text-indigo-400 opacity-70">Yangi kitob yuklash</p>
                    </div>
                    <svg class="w-4 h-4 ml-auto opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 hover:bg-emerald-500/20 hover:border-emerald-500/40 transition-all group">
                    <span class="text-lg">👨‍🏫</span>
                    <div>
                        <p class="text-sm font-medium">O'qituvchi qo'shish</p>
                        <p class="text-xs text-emerald-400 opacity-70">Rol belgilash</p>
                    </div>
                    <svg class="w-4 h-4 ml-auto opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('admin.stats') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-300 hover:bg-purple-500/20 hover:border-purple-500/40 transition-all group">
                    <span class="text-lg">📊</span>
                    <div>
                        <p class="text-sm font-medium">Statistikani ko'rish</p>
                        <p class="text-xs text-purple-400 opacity-70">Batafsil tahlil</p>
                    </div>
                    <svg class="w-4 h-4 ml-auto opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        {{-- System Status --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <h2 class="text-sm font-semibold text-white mb-4">Tizim holati</h2>
            <div class="space-y-3">
                <div class="flex items-center justify-between py-2 border-b border-slate-800/60">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        <span class="text-xs text-slate-400">PHP versiya</span>
                    </div>
                    <span class="text-xs font-mono font-medium text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-lg">{{ PHP_VERSION }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-800/60">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                        <span class="text-xs text-slate-400">Laravel</span>
                    </div>
                    <span class="text-xs font-mono font-medium text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-lg">{{ app()->version() }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-800/60">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                        <span class="text-xs text-slate-400">Ma'lumotlar bazasi</span>
                    </div>
                    <span class="text-xs font-mono font-medium text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-lg flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block animate-pulse"></span>
                        Ulangan
                    </span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/></svg>
                        <span class="text-xs text-slate-400">Kesh</span>
                    </div>
                    <span class="text-xs font-mono font-medium text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-lg">Faol</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- GRAFIKLAR — real ma'lumotlar bilan (Chart.js) --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">

    {{-- 1. Foydalanuvchilar o'sishi (30 kun, kumulyativ chiziq) --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
            <div>
                <h2 class="text-sm font-semibold text-white">📈 Foydalanuvchilar o'sishi</h2>
                <p class="text-xs text-slate-500 mt-0.5">So'nggi 30 kun, jami hisobda</p>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-medium {{ $stats['user_growth_pct'] >= 0 ? 'text-emerald-400 bg-emerald-500/10' : 'text-rose-400 bg-rose-500/10' }} px-2 py-1 rounded-lg">
                    {{ $stats['user_growth_pct'] >= 0 ? '+' : '' }}{{ $stats['user_growth_pct'] }}% oyiga
                </span>
            </div>
        </div>
        <div class="p-5">
            <div class="h-64"><canvas id="chart-signups"></canvas></div>
        </div>
    </div>

    {{-- 2. O'qilgan daqiqalar (14 kun, ustunli) --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
            <div>
                <h2 class="text-sm font-semibold text-white">📖 O'qilgan daqiqalar</h2>
                <p class="text-xs text-slate-500 mt-0.5">Kunlik faol mutolaa, so'nggi 14 kun</p>
            </div>
            <span class="text-xs font-medium text-indigo-400 bg-indigo-500/10 px-2 py-1 rounded-lg">
                Jami: {{ number_format($stats['total_reading_minutes']) }} daqiqa
            </span>
        </div>
        <div class="p-5">
            <div class="h-64"><canvas id="chart-minutes"></canvas></div>
        </div>
    </div>
</div>

{{-- Pastki qator: format taqsimoti + qisqacha ko'rsatkichlar --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">

    {{-- 3. Kontent formatlari (donut) --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-800">
            <h2 class="text-sm font-semibold text-white">🗂 Kontent taqsimoti</h2>
            <p class="text-xs text-slate-500 mt-0.5">Formatlar bo'yicha materiallar</p>
        </div>
        <div class="p-5">
            <div class="h-56"><canvas id="chart-formats"></canvas></div>
        </div>
    </div>

    {{-- 4. Qisqacha ko'rsatkichlar --}}
    <div class="xl:col-span-2 grid grid-cols-2 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <p class="text-xs text-slate-500 uppercase tracking-wider mb-2">Bugun kirgan foydalanuvchilar</p>
            <p class="text-3xl font-bold text-white">{{ number_format($stats['online_today']) }}</p>
            <p class="text-xs text-slate-500 mt-1">Bugungi faollik (DailyActivity)</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <p class="text-xs text-slate-500 uppercase tracking-wider mb-2">Jami to'plangan ballar</p>
            <p class="text-3xl font-bold text-amber-400">{{ number_format($stats['total_points']) }}</p>
            <p class="text-xs text-slate-500 mt-1">Barcha foydalanuvchilar bo'yicha</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <p class="text-xs text-slate-500 uppercase tracking-wider mb-2">Guruhlar</p>
            <p class="text-3xl font-bold text-emerald-400">{{ number_format($stats['total_groups']) }}</p>
            <p class="text-xs text-slate-500 mt-1">Faol kitobxon jamoalari</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <p class="text-xs text-slate-500 uppercase tracking-wider mb-2">Testlar</p>
            <p class="text-3xl font-bold text-purple-400">{{ number_format($stats['total_quizzes']) }}</p>
            <p class="text-xs text-slate-500 mt-1">Yaratilgan quiz'lar soni</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// ── Grafik ma'lumotlari (route'dan uzatildi) ──
const signupLabels = @json($signupLabels);
const signupData   = @json($signupData);
const minutesLabels = @json($minutesLabels);
const minutesData   = @json($minutesData);
const formatData    = @json($formatData);

Chart.defaults.color = '#94a3b8';
Chart.defaults.borderColor = 'rgba(148, 163, 184, 0.08)';
Chart.defaults.font.family = "'Plus Jakarta Sans', 'Inter', sans-serif";

function counterCard(target) {
    return {
        displayValue: '0',
        init() {
            this.animateCounter(target);
        },
        animateCounter(target) {
            const duration = 1200;
            const start = performance.now();
            const step = (currentTime) => {
                const elapsed = currentTime - start;
                const progress = Math.min(elapsed / duration, 1);
                // Easing function
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = Math.round(eased * target);
                this.displayValue = current.toLocaleString('uz-UZ');
                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            };
            requestAnimationFrame(step);
        }
    }
}

// ── Grafiklarni ishga tushirish ──
document.addEventListener('DOMContentLoaded', function () {
    // 1. Foydalanuvchilar o'sishi (kumulyativ chiziq, gradient bilan)
    const signupsCtx = document.getElementById('chart-signups');
    if (signupsCtx) {
        const grad = signupsCtx.getContext('2d').createLinearGradient(0, 0, 0, 260);
        grad.addColorStop(0, 'rgba(99, 102, 241, 0.35)');
        grad.addColorStop(1, 'rgba(99, 102, 241, 0)');
        new Chart(signupsCtx, {
            type: 'line',
            data: {
                labels: signupLabels,
                datasets: [{
                    label: 'Jami foydalanuvchilar',
                    data: signupData,
                    borderColor: '#818cf8',
                    backgroundColor: grad,
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#818cf8',
                    pointHoverBorderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 10,
                        callbacks: { label: (c) => ' ' + c.parsed.y.toLocaleString('uz-UZ') + ' foydalanuvchi' }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } },
                    y: { beginAtZero: true, ticks: { maxTicksLimit: 6, callback: (v) => v >= 1000 ? (v/1000) + 'k' : v } }
                }
            }
        });
    }

    // 2. O'qilgan daqiqalar (kunlik ustunli)
    const minutesCtx = document.getElementById('chart-minutes');
    if (minutesCtx) {
        new Chart(minutesCtx, {
            type: 'bar',
            data: {
                labels: minutesLabels,
                datasets: [{
                    label: 'Daqiqalar',
                    data: minutesData,
                    backgroundColor: 'rgba(139, 92, 246, 0.55)',
                    hoverBackgroundColor: 'rgba(139, 92, 246, 0.85)',
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 28,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 10,
                        callbacks: { label: (c) => ' ' + c.parsed.y.toLocaleString('uz-UZ') + ' daqiqa' }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } },
                    y: { beginAtZero: true, ticks: { maxTicksLimit: 6 } }
                }
            }
        });
    }

    // 3. Kontent formatlari (donut)
    const formatsCtx = document.getElementById('chart-formats');
    if (formatsCtx) {
        new Chart(formatsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Boblar', 'Audiolaringlar', 'Videolar', 'Testlar'],
                datasets: [{
                    data: formatData,
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.75)',
                        'rgba(16, 185, 129, 0.75)',
                        'rgba(245, 158, 11, 0.75)',
                        'rgba(236, 72, 153, 0.75)',
                    ],
                    borderColor: '#0f172a',
                    borderWidth: 3,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 14, boxWidth: 8 } },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 10,
                        callbacks: { label: (c) => ' ' + c.label + ': ' + c.parsed.toLocaleString('uz-UZ') }
                    }
                }
            }
        });
    }
});
</script>
@endpush
