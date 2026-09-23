@extends('teacher.layouts.app')
@section('title', 'Bosh panel')

@section('content')
<div class="space-y-6 animate-fade-in">

    {{-- Welcome Banner --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600 p-6 text-white shadow-xl">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -left-10 -bottom-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <p class="text-blue-200 text-sm font-medium mb-1">{{ now()->locale('uz')->isoFormat('D MMMM, YYYY') }}</p>
                <h1 class="text-2xl font-black">Xush kelibsiz, {{ auth()->user()->name }}! 👋</h1>
                <p class="text-blue-100 text-sm mt-1">O'qituvchi panelingiz tayyor. Bugun ham samarali ishlang!</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('teacher.books.create') }}"
                   class="px-4 py-2.5 bg-white text-indigo-700 font-bold text-sm rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95">
                    + Kitob qo'shish
                </a>
            </div>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4"
         x-data="{
            stats: [
                { label: 'Jami kitoblar',    icon: '📚', value: {{ \App\Models\Book::count() }},       color: 'indigo' },
                { label: 'Aktiv kitoblar',   icon: '✅', value: {{ \App\Models\Book::where('is_active',true)->count() }}, color: 'emerald' },
                { label: 'O\'quvchilar',     icon: '🎓', value: {{ \App\Models\User::role('student')->count() }}, color: 'blue' },
                { label: 'Jami testlar',     icon: '📝', value: 0,                                     color: 'amber'  },
            ],
            counters: [0,0,0,0],
            animate() {
                this.stats.forEach((s, i) => {
                    let start = 0; const end = s.value; const dur = 1200;
                    const step = () => {
                        start += Math.ceil(end / (dur / 16));
                        if (start >= end) { this.counters[i] = end; } else { this.counters[i] = start; requestAnimationFrame(step); }
                    };
                    requestAnimationFrame(step);
                });
            }
         }" x-init="animate()">
        <template x-for="(stat, i) in stats" :key="i">
            <div class="p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow">
                <div class="text-2xl mb-2" x-text="stat.icon"></div>
                <p class="text-2xl font-black text-slate-900 dark:text-white" x-text="counters[i].toLocaleString()"></p>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5" x-text="stat.label"></p>
            </div>
        </template>
    </div>

    {{-- Main grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Recent Books --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <span>📚</span> So'nggi kitoblar
                </h3>
                <a href="{{ route('teacher.books.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">Barchasi →</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse(\App\Models\Book::latest()->take(5)->get() as $book)
                    <div class="px-6 py-3.5 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm shrink-0">
                                {{ $loop->iteration }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">{{ $book->title }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ $book->author }}</p>
                            </div>
                        </div>
                        <span @class([
                            'px-2.5 py-1 rounded-lg text-xs font-bold',
                            'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' => $book->is_active,
                            'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' => !$book->is_active,
                        ])>
                            {{ $book->is_active ? 'Aktiv' : 'Noaktiv' }}
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-slate-400 text-sm">Hali kitoblar yo'q</div>
                @endforelse
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="space-y-4">
            {{-- My Info --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                <div class="flex flex-col items-center text-center">
                    <img src="{{ auth()->user()->avatar_url }}" class="w-16 h-16 rounded-full ring-4 ring-indigo-100 dark:ring-indigo-900 object-cover mb-3">
                    <h4 class="font-bold text-slate-900 dark:text-white">{{ auth()->user()->name }}</h4>
                    <p class="text-xs text-slate-400 mb-3">{{ '@' . auth()->user()->username }}</p>
                    <div class="flex items-center gap-4 text-center">
                        <div>
                            <p class="text-lg font-black text-indigo-600">{{ number_format(auth()->user()->total_points) }}</p>
                            <p class="text-[11px] text-slate-400">ball</p>
                        </div>
                        <div class="w-px h-8 bg-slate-200 dark:bg-slate-700"></div>
                        <div>
                            <p class="text-lg font-black text-amber-500">🔥 {{ auth()->user()->current_streak }}</p>
                            <p class="text-[11px] text-slate-400">kun</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick actions --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
                <h4 class="font-bold text-slate-800 dark:text-white text-sm mb-3">Tezkor amallar</h4>
                <div class="space-y-2">
                    <a href="{{ route('teacher.books.create') }}"
                       class="flex items-center gap-3 p-3 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 transition-colors text-sm font-medium">
                        <span>📖</span> Yangi kitob qo'shish
                    </a>
                    <a href="{{ route('teacher.quizzes.index') }}"
                       class="flex items-center gap-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/40 text-amber-700 dark:text-amber-400 transition-colors text-sm font-medium">
                        <span>📝</span> Test yaratish
                    </a>
                    <a href="{{ route('teacher.students.index') }}"
                       class="flex items-center gap-3 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 transition-colors text-sm font-medium">
                        <span>🎓</span> O'quvchilarni ko'rish
                    </a>
                    <a href="{{ route('teacher.live.index') }}"
                       class="flex items-center gap-3 p-3 rounded-xl bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-400 transition-colors text-sm font-medium">
                        <span>🔴</span> Jonli efir boshlash
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
