@extends('teacher.layouts.app')
@section('title', 'Bosh panel')

@section('content')
<div class="space-y-6">

    {{-- Welcome Banner --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-700 p-6 sm:p-7 text-white shadow-xl shadow-indigo-950/40">
        <div class="absolute -right-10 -top-10 w-44 h-44 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-10 -bottom-10 w-36 h-36 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <p class="text-indigo-200 text-xs font-mono font-semibold mb-1.5 uppercase tracking-wider">{{ now()->locale('uz')->isoFormat('D MMMM, YYYY') }}</p>
                <h1 class="text-2xl sm:text-3xl font-black font-manrope">Xush kelibsiz, {{ auth()->user()->name }}! 👋</h1>
                <p class="text-indigo-100/90 text-xs sm:text-sm mt-1">O'qituvchi panelingiz tayyor. Bugun ham samarali ishlang!</p>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 shrink-0 flex-wrap">
                <a href="{{ route('teacher.books.create') }}"
                   class="px-3.5 py-2 sm:px-4 sm:py-2.5 bg-white hover:bg-slate-100 text-indigo-700 font-bold text-xs uppercase tracking-wider rounded-xl shadow-md transition-all active:scale-95">
                    + Kitob qo'shish
                </a>
                <a href="{{ route('teacher.quizzes.create') }}"
                   class="px-3.5 py-2 sm:px-4 sm:py-2.5 bg-white/15 hover:bg-white/25 border border-white/20 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all active:scale-95">
                    📝 + Test yaratish
                </a>
                <a href="{{ route('live.index', ['start' => 1]) }}"
                   class="px-3.5 py-2 sm:px-4 sm:py-2.5 bg-rose-500 hover:bg-rose-400 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md shadow-rose-600/30 transition-all active:scale-95 flex items-center gap-1.5">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                    </span>
                    <span>🔴 Jonli efir</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Quick Stats Row --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4"
         x-data="{
            stats: [
                { label: 'Jami kitoblar',    icon: '📚', value: {{ \App\Models\Book::count() }},                                  color: 'indigo' },
                { label: 'Aktiv kitoblar',   icon: '✅', value: {{ \App\Models\Book::where('is_active', true)->count() }},        color: 'emerald' },
                { label: 'O\'quvchilar',     icon: '🎓', value: {{ \App\Models\User::where('role', 'student')->count() }},        color: 'blue' },
                { label: 'Jami testlar',     icon: '📝', value: {{ \App\Models\Quiz::count() }},                                  color: 'amber'  },
            ],
            counters: [0,0,0,0],
            animate() {
                this.stats.forEach((s, i) => {
                    let start = 0; const end = s.value; const dur = 1000;
                    if (end === 0) { this.counters[i] = 0; return; }
                    const step = () => {
                        start += Math.ceil(end / (dur / 16));
                        if (start >= end) { this.counters[i] = end; } else { this.counters[i] = start; requestAnimationFrame(step); }
                    };
                    requestAnimationFrame(step);
                });
            }
         }" x-init="animate()">
        <template x-for="(stat, i) in stats" :key="i">
            <div class="p-5 bg-slate-900 border border-slate-800 rounded-2xl shadow-sm hover:border-slate-700 transition-all">
                <div class="text-2xl mb-2" x-text="stat.icon"></div>
                <p class="text-2xl sm:text-3xl font-black text-white font-manrope" x-text="counters[i].toLocaleString()"></p>
                <p class="text-xs text-slate-400 font-medium mt-1" x-text="stat.label"></p>
            </div>
        </template>
    </div>

    {{-- Main grid: Books + Teacher Profile/Quick Actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Recent Books (2 columns) --}}
        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                <h3 class="font-bold text-white text-sm flex items-center gap-2">
                    <span>📚</span> So'nggi kitoblar
                </h3>
                <a href="{{ route('teacher.books.index') }}" class="text-xs text-indigo-400 font-semibold hover:text-indigo-300 transition-colors">Barchasi →</a>
            </div>
            <div class="divide-y divide-slate-800/80 flex-1">
                @forelse(\App\Models\Book::latest()->take(5)->get() as $book)
                    <div class="px-6 py-3.5 flex items-center justify-between hover:bg-slate-800/40 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm shrink-0">
                                {{ $loop->iteration }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-200 truncate">{{ $book->title }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ $book->author }}</p>
                            </div>
                        </div>
                        <span @class([
                            'px-2.5 py-1 rounded-lg text-xs font-bold shrink-0',
                            'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' => $book->is_active,
                            'bg-slate-800 text-slate-400 border border-slate-700' => !$book->is_active,
                        ])>
                            {{ $book->is_active ? 'Aktiv' : 'Noaktiv' }}
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center text-slate-500 text-sm">Hali kitoblar mavjud emas</div>
                @endforelse
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-6">
            {{-- My Info Card --}}
            <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-sm p-6">
                <div class="flex flex-col items-center text-center">
                    <img src="{{ auth()->user()->avatar_url }}" class="w-16 h-16 rounded-full ring-2 ring-amber-400/50 object-cover mb-3 shadow-lg">
                    <h4 class="font-bold text-white text-base">{{ auth()->user()->name }}</h4>
                    <p class="text-xs text-slate-400 font-mono mb-4">{{ '@' . auth()->user()->username }}</p>
                    <div class="flex items-center gap-6 text-center w-full justify-center pt-2 border-t border-slate-800">
                        <div>
                            <p class="text-lg font-black text-indigo-400">{{ number_format(auth()->user()->total_points) }}</p>
                            <p class="text-[10px] text-slate-500 uppercase tracking-wider">Ball</p>
                        </div>
                        <div class="w-px h-8 bg-slate-800"></div>
                        <div>
                            <p class="text-lg font-black text-amber-400">🔥 {{ auth()->user()->current_streak }}</p>
                            <p class="text-[10px] text-slate-500 uppercase tracking-wider">Kun</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick actions --}}
            <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-sm p-5">
                <h4 class="font-bold text-white text-xs uppercase tracking-wider text-slate-400 mb-3">Tezkor amallar</h4>
                <div class="space-y-2">
                    <a href="{{ route('teacher.books.create') }}"
                       class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.03] hover:bg-white/[0.08] border border-white/[0.06] text-slate-300 hover:text-white transition-all text-xs font-semibold">
                        <span class="text-base">📖</span> Yangi kitob qo'shish
                    </a>
                    <a href="{{ route('teacher.quizzes.index') }}"
                       class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.03] hover:bg-white/[0.08] border border-white/[0.06] text-slate-300 hover:text-white transition-all text-xs font-semibold">
                        <span class="text-base">📝</span> Test yaratish
                    </a>
                    <a href="{{ route('teacher.students.index') }}"
                       class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.03] hover:bg-white/[0.08] border border-white/[0.06] text-slate-300 hover:text-white transition-all text-xs font-semibold">
                        <span class="text-base">🎓</span> O'quvchilarni ko'rish
                    </a>
                    <a href="{{ route('teacher.live.index') }}"
                       class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.03] hover:bg-white/[0.08] border border-white/[0.06] text-slate-300 hover:text-white transition-all text-xs font-semibold">
                        <span class="text-base">🔴</span> Jonli efir boshlash
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
