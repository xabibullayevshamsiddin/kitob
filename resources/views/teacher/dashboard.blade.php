@extends('teacher.layouts.app')
@section('title', 'Bosh panel')

@section('content')
<div class="space-y-5">

    {{-- Editorial Welcome Banner --}}
    <div class="relative overflow-hidden rounded-panel bg-ink-900 border border-ink-border p-5 sm:p-6">
        <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="ks-badge bg-amber-500/10 text-amber-400 border border-amber-500/25 font-mono">
                        O'QITUVCHI KONSOLI
                    </span>
                    <span class="text-xs font-mono text-mist">
                        {{ now()->locale('uz')->isoFormat('dddd, D MMMM YYYY') }}
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold font-serif text-paper">
                    Xush kelibsiz, <span class="text-amber-400">{{ auth()->user()->name }}</span>!
                </h1>
                <p class="text-mist text-xs sm:text-sm mt-1 font-sans">
                    O'qituvchi panelingiz tayyor. Kitoblar, test topshiriqlari va o'quvchilar ko'rsatkichlarini boshqaring.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                <a href="{{ route('teacher.books.create') }}"
                   class="ks-btn-primary text-xs py-2 px-3.5 inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                    <span>Kitob qo'shish</span>
                </a>
                <a href="{{ route('teacher.quizzes.create') }}"
                   class="ks-btn-ghost text-xs py-2 px-3.5 inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Test yaratish</span>
                </a>
                <a href="{{ route('live.index', ['start' => 1]) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-btn bg-[#C1392B] hover:bg-[#a63024] text-paper font-bold text-xs uppercase tracking-wider transition-all shadow-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-paper opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-paper"></span>
                    </span>
                    <span>Jonli efir</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Quick Stats Row (DENSITY: 7) --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5"
         x-data="{
            stats: [
                { label: 'Jami kitoblar',  value: {{ \App\Models\Book::count() }}, path: 'M4 19.5A2.5 2.5 0 0 1 6.5 17H20 M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z' },
                { label: 'Aktiv kitoblar', value: {{ \App\Models\Book::where('is_active', true)->count() }}, path: 'M22 11.08V12a10 10 0 1 1-5.93-9.14 M22 4L12 14.01l-3-3' },
                { label: 'O\'quvchilar',   value: {{ \App\Models\User::where('role', 'student')->count() }}, path: 'M22 10v6M2 10l10-5 10 5-10 5z M6 12v5c3 3 9 3 12 0v-5' },
                { label: 'Jami testlar',   value: {{ \App\Models\Quiz::count() }}, path: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' },
            ],
            counters: [0,0,0,0],
            animate() {
                this.stats.forEach((s, i) => {
                    let start = 0; const end = s.value; const dur = 800;
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
            <div class="ks-panel p-4 bg-ink-900 border border-ink-border">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[11px] text-mist font-mono uppercase tracking-wider truncate" x-text="stat.label"></p>
                    <div class="w-7 h-7 rounded-btn bg-ink-800 border border-ink-border flex items-center justify-center text-amber-400">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path :d="stat.path" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold font-mono text-paper ks-stat" x-text="counters[i].toLocaleString()"></p>
            </div>
        </template>
    </div>

    {{-- Main grid: Books + Teacher Profile/Quick Actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Recent Books (2 columns) --}}
        <div class="lg:col-span-2 ks-panel bg-ink-900 border border-ink-border flex flex-col overflow-hidden">
            <div class="px-5 py-3.5 border-b border-ink-border flex items-center justify-between">
                <h3 class="font-bold text-paper text-sm flex items-center gap-2 font-serif">
                    <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20 M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    So'nggi kitoblar
                </h3>
                <a href="{{ route('teacher.books.index') }}" class="text-xs text-amber-400 hover:text-amber-300 font-mono transition-colors">Barchasi →</a>
            </div>
            <div class="divide-y divide-ink-border flex-1">
                @forelse(\App\Models\Book::latest()->take(5)->get() as $book)
                    <div class="px-5 py-3 flex items-center justify-between hover:bg-ink-800/50 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-btn bg-ink-800 border border-ink-border flex items-center justify-center text-paper font-mono text-xs shrink-0">
                                {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs sm:text-sm font-semibold text-paper truncate">{{ $book->title }}</p>
                                <p class="text-[11px] text-mist truncate font-sans">{{ $book->author }}</p>
                            </div>
                        </div>
                        <span @class([
                            'px-2 py-0.5 rounded-pill text-[11px] font-mono font-medium shrink-0',
                            'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' => $book->is_active,
                            'bg-ink-800 text-mist border border-ink-border' => !$book->is_active,
                        ])>
                            {{ $book->is_active ? 'Aktiv' : 'Noaktiv' }}
                        </span>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-mist text-xs font-mono">Hali kitoblar mavjud emas</div>
                @endforelse
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-5">
            {{-- My Info Card --}}
            <div class="ks-panel bg-ink-900 border border-ink-border p-5">
                <div class="flex flex-col items-center text-center">
                    <img src="{{ auth()->user()->avatar_url }}" class="w-16 h-16 rounded-full border-2 border-amber-400/40 object-cover mb-2.5 shadow-md">
                    <h4 class="font-bold text-paper text-sm sm:text-base font-serif">{{ auth()->user()->name }}</h4>
                    <p class="text-xs text-mist font-mono mb-4">{{ '@' . auth()->user()->username }}</p>
                    <div class="grid grid-cols-2 gap-3 w-full pt-3 border-t border-ink-border">
                        <div class="p-2.5 rounded-btn bg-ink-800 border border-ink-border">
                            <p class="text-lg font-bold font-mono text-paper">{{ number_format(auth()->user()->total_points) }}</p>
                            <p class="text-[10px] text-mist font-mono uppercase tracking-wider">Ball</p>
                        </div>
                        <div class="p-2.5 rounded-btn bg-ink-800 border border-ink-border flex flex-col items-center justify-center">
                            <div class="flex items-center gap-1">
                                <span class="ks-flame is-lit inline-block scale-75">
                                    <svg class="w-4 h-4 text-amber-400 fill-amber-400" viewBox="0 0 24 24"><path d="M12 2c0 4-4 6-4 10a6 6 0 0 0 12 0c0-4-4-6-4-10z"/></svg>
                                </span>
                                <span class="text-lg font-bold font-mono text-amber-400">{{ auth()->user()->current_streak }}</span>
                            </div>
                            <p class="text-[10px] text-mist font-mono uppercase tracking-wider">Kun</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick actions --}}
            <div class="ks-panel bg-ink-900 border border-ink-border p-4">
                <h4 class="font-mono text-[11px] uppercase tracking-wider text-mist mb-3 px-1">Tezkor amallar</h4>
                <div class="space-y-1.5">
                    <a href="{{ route('teacher.books.create') }}"
                       class="flex items-center gap-2.5 p-2.5 rounded-btn bg-ink-800 hover:bg-ink-700/60 border border-ink-border text-paper transition-all text-xs font-medium">
                        <svg class="w-4 h-4 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                        <span>Yangi kitob qo'shish</span>
                    </a>
                    <a href="{{ route('teacher.quizzes.index') }}"
                       class="flex items-center gap-2.5 p-2.5 rounded-btn bg-ink-800 hover:bg-ink-700/60 border border-ink-border text-paper transition-all text-xs font-medium">
                        <svg class="w-4 h-4 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Test topshiriqlari</span>
                    </a>
                    <a href="{{ route('teacher.students.index') }}"
                       class="flex items-center gap-2.5 p-2.5 rounded-btn bg-ink-800 hover:bg-ink-700/60 border border-ink-border text-paper transition-all text-xs font-medium">
                        <svg class="w-4 h-4 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>O'quvchilar ro'yxati</span>
                    </a>
                    <a href="{{ route('teacher.live.index') }}"
                       class="flex items-center gap-2.5 p-2.5 rounded-btn bg-ink-800 hover:bg-ink-700/60 border border-ink-border text-paper transition-all text-xs font-medium">
                        <svg class="w-4 h-4 text-[#C1392B] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                        <span>Jonli efir boshlash</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
