@extends('admin.layouts.app')
@section('title', 'Test tafsilotlari: ' . $quiz->title)

@section('content')
<div class="max-w-4xl mx-auto space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="ks-eyebrow">Test topshiriqlari</span>
            <h1 class="text-xl sm:text-2xl font-bold font-serif text-paper mt-0.5">{{ $quiz->title }}</h1>
            <p class="text-sm text-mist">
                @if($quiz->book)
                    «{{ $quiz->book->title }}» kitobiga bog'langan test topshirig'i
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.quizzes.index') }}" class="ks-btn-ghost min-h-11 inline-flex items-center px-4 py-2 text-sm font-semibold self-start sm:self-auto">
                ← Barcha testlar
            </a>
        </div>
    </div>

    {{-- Info Card --}}
    <div class="ks-panel p-4 sm:p-5 grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
        <div class="p-3 bg-ink-950/60 rounded-card border border-ink-border">
            <span class="text-xs text-mist block mb-1">Savollar</span>
            <span class="text-xl font-bold text-paper">{{ $quiz->questions->count() }} ta</span>
        </div>
        <div class="p-3 bg-ink-950/60 rounded-card border border-ink-border">
            <span class="text-xs text-mist block mb-1">Umumiy ball</span>
            <span class="text-xl font-bold text-amber-400">{{ $quiz->questions->sum('points') }} ball</span>
        </div>
        <div class="p-3 bg-ink-950/60 rounded-card border border-ink-border">
            <span class="text-xs text-mist block mb-1">Vaqt chegarasi</span>
            <span class="text-xl font-bold text-amber-400">{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' daq' : 'Cheksiz' }}</span>
        </div>
        <div class="p-3 bg-ink-950/60 rounded-card border border-ink-border">
            <span class="text-xs text-mist block mb-1">Murakkablik</span>
            <span class="text-sm font-bold uppercase text-emerald-400">{{ $quiz->difficulty }}</span>
        </div>
    </div>

    {{-- Questions List --}}
    <div class="space-y-4">
        <h2 class="text-lg font-bold font-serif text-paper">Savollar ro'yxati</h2>

        @foreach($quiz->questions as $index => $q)
            <div class="ks-panel p-4 sm:p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-ink-border pb-2">
                    <span class="font-bold text-sm text-amber-400">{{ $index + 1 }}-savol <span class="text-mist font-normal">({{ $q->points }} ball)</span></span>
                </div>
                <p class="text-base font-semibold text-paper">{{ $q->question_text }}</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2">
                    @foreach($q->options as $opt)
                        <div class="p-3 rounded-card border text-xs flex items-center justify-between gap-3 {{ $opt->is_correct ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300 font-bold' : 'bg-ink-950/60 border-ink-border text-mist' }}">
                            <span>{{ $opt->option_text }}</span>
                            @if($opt->is_correct)
                                <span class="px-2 py-0.5 rounded-badge bg-emerald-500/20 text-emerald-300 text-[10px] font-bold uppercase shrink-0">To'g'ri javob</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($q->explanation)
                    <div class="p-3 rounded-card bg-amber-500/5 border border-amber-500/15 text-xs text-amber-200">
                        <strong class="font-bold">Izoh:</strong> {{ $q->explanation }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
