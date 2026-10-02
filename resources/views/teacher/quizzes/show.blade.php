@extends('teacher.layouts.app')
@section('title', $quiz->title . ' - Test savollari')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Top header bar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('teacher.quizzes.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">← Testlar ro'yxatiga</a>
                @if($quiz->book)
                    <span class="text-xs text-slate-400">• {{ $quiz->book->title }}</span>
                @endif
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>🎯</span> {{ $quiz->title }}
            </h2>
        </div>

        <div class="flex items-center gap-2">
            <form action="{{ route('teacher.quizzes.destroy', $quiz->id) }}" method="POST"
                  onsubmit="return confirm('Ushbu test va uning savollari butunlay o\'chiriladi. Rozimisiz?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 text-xs font-bold rounded-xl transition-all border border-rose-200 dark:border-rose-800/40">
                    O'chirish
                </button>
            </form>
        </div>
    </div>

    {{-- Summary Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                <p class="text-xs text-slate-400">Savollar soni</p>
                <p class="text-lg font-black text-slate-800 dark:text-white mt-0.5">{{ $quiz->questions->count() }} ta</p>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                <p class="text-xs text-slate-400">Jami ball</p>
                <p class="text-lg font-black text-indigo-600 dark:text-indigo-400 mt-0.5">{{ $quiz->questions->sum('points') }} ball</p>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                <p class="text-xs text-slate-400">Vaqt limiti</p>
                <p class="text-lg font-black text-slate-800 dark:text-white mt-0.5">{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' daq' : 'Cheksiz' }}</p>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                <p class="text-xs text-slate-400">Topshirganlar</p>
                <p class="text-lg font-black text-emerald-600 mt-0.5">{{ $quiz->attempts()->count() }} marta</p>
            </div>
        </div>

        @if($quiz->description)
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 text-xs text-slate-500 leading-relaxed">
                <span class="font-bold text-slate-700 dark:text-slate-300">Tavsif:</span> {{ $quiz->description }}
            </div>
        @endif
    </div>

    {{-- Questions List --}}
    <div class="space-y-4">
        <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <span>❓</span> Test savollari va to'g'ri javoblari
        </h3>

        @foreach($quiz->questions as $index => $q)
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                    <span class="inline-flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                        <span class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs">
                            {{ $index + 1 }}
                        </span>
                        <span>{{ $q->question_text }}</span>
                    </span>
                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-xs font-bold">
                        {{ $q->points }} ball
                    </span>
                </div>

                {{-- Options --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    @foreach($q->options as $oIdx => $opt)
                        <div @class([
                            'p-3 rounded-xl border text-xs flex items-center justify-between',
                            'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 text-emerald-900 dark:text-emerald-200 font-semibold' => $opt->is_correct,
                            'border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-750 text-slate-700 dark:text-slate-300' => !$opt->is_correct,
                        ])>
                            <span class="flex items-center gap-2">
                                <span class="w-5 text-[11px] font-mono text-slate-400">{{ ['A', 'B', 'C', 'D'][$oIdx] ?? ($oIdx + 1) }}.</span>
                                <span>{{ $opt->option_text }}</span>
                            </span>
                            @if($opt->is_correct)
                                <span class="px-2 py-0.5 rounded bg-emerald-500 text-white text-[10px] font-bold">✓ To'g'ri</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($q->explanation)
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-900/40 text-[11px] text-amber-800 dark:text-amber-300">
                        <span class="font-bold">Izoh:</span> {{ $q->explanation }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>

</div>
@endsection
