@extends('admin.layouts.app')
@section('title', 'Test tafsilotlari: ' . $quiz->title)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>📝</span> {{ $quiz->title }}
            </h2>
            <p class="text-sm text-slate-500">
                @if($quiz->book)
                    «{{ $quiz->book->title }}» kitobiga bog'langan test topshirig'i
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.quizzes.index') }}" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-semibold hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
                ← Barcha testlar
            </a>
        </div>
    </div>

    {{-- Info Card --}}
    <div class="p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
        <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-800">
            <span class="text-xs text-slate-400 block mb-1">Savollar</span>
            <span class="text-xl font-black text-white">{{ $quiz->questions->count() }} ta</span>
        </div>
        <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-800">
            <span class="text-xs text-slate-400 block mb-1">Umumiy ball</span>
            <span class="text-xl font-black text-amber-500">{{ $quiz->questions->sum('points') }} ball</span>
        </div>
        <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-800">
            <span class="text-xs text-slate-400 block mb-1">Vaqt chegarasi</span>
            <span class="text-xl font-black text-indigo-400">{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' daq' : 'Cheksiz' }}</span>
        </div>
        <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-800">
            <span class="text-xs text-slate-400 block mb-1">Murakkablik</span>
            <span class="text-sm font-bold uppercase text-emerald-400">{{ $quiz->difficulty }}</span>
        </div>
    </div>

    {{-- Questions List --}}
    <div class="space-y-4">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Savollar ro'yxati:</h3>

        @foreach($quiz->questions as $index => $q)
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-2">
                    <span class="font-bold text-sm text-indigo-400">{{ $index + 1 }}-Savol ({{ $q->points }} ball)</span>
                </div>
                <p class="text-base font-semibold text-slate-900 dark:text-white">{{ $q->question_text }}</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2">
                    @foreach($q->options as $opt)
                        <div class="p-3 rounded-xl border text-xs flex items-center justify-between {{ $opt->is_correct ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-400 font-bold' : 'bg-slate-50 dark:bg-slate-900/40 border-slate-200 dark:border-slate-700 text-slate-300' }}">
                            <span>{{ $opt->option_text }}</span>
                            @if($opt->is_correct)
                                <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 text-[10px] font-black uppercase">To'g'ri javob ✓</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($q->explanation)
                    <div class="p-3 rounded-xl bg-indigo-500/5 border border-indigo-500/15 text-xs text-indigo-300">
                        <strong class="font-bold">Izoh:</strong> {{ $q->explanation }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
