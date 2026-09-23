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

{{-- Chart Placeholder --}}
<div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
        <div>
            <h2 class="text-sm font-semibold text-white">O'sish grafigi</h2>
            <p class="text-xs text-slate-500 mt-0.5">Foydalanuvchilar va kitoblar dinamikasi</p>
        </div>
        <div class="flex items-center gap-2">
            <button class="text-xs text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 px-3 py-1.5 rounded-lg hover:bg-indigo-500/20 transition-colors">7 kun</button>
            <button class="text-xs text-slate-500 hover:text-slate-300 px-3 py-1.5 rounded-lg hover:bg-slate-800 transition-colors">30 kun</button>
            <button class="text-xs text-slate-500 hover:text-slate-300 px-3 py-1.5 rounded-lg hover:bg-slate-800 transition-colors">1 yil</button>
        </div>
    </div>

    <div class="relative h-56 flex items-center justify-center overflow-hidden">
        <!-- Fake chart grid lines -->
        <div class="absolute inset-0 p-5">
            <div class="h-full relative">
                @foreach(range(1,4) as $i)
                    <div class="absolute left-0 right-0 border-t border-slate-800/80" style="bottom: {{ ($i * 25) }}%"></div>
                @endforeach
                <!-- Fake bars -->
                <div class="absolute inset-x-5 bottom-0 flex items-end gap-2 h-full">
                    @foreach([35, 55, 42, 78, 62, 88, 71] as $height)
                        <div class="flex-1 relative rounded-t-lg overflow-hidden" style="height: {{ $height }}%;">
                            <div class="absolute inset-0 bg-gradient-to-t from-indigo-600/70 to-violet-600/30 rounded-t-lg"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Coming soon overlay -->
        <div class="relative z-10 text-center">
            <div class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-800/90 border border-slate-700 rounded-xl backdrop-blur-sm shadow-xl">
                <span class="text-xl">📊</span>
                <div class="text-left">
                    <p class="text-sm font-semibold text-white">Grafik tez orada</p>
                    <p class="text-xs text-slate-400">Chart.js integratsiyasi amalga oshirilmoqda</p>
                </div>
                <span class="ml-2 text-xs font-medium text-amber-400 bg-amber-500/10 border border-amber-500/20 px-2 py-0.5 rounded-full">Beta</span>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
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
</script>
@endpush
