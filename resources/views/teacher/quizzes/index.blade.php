@extends('teacher.layouts.app')
@section('title', 'Kitob Test Topshiriqlari')

@section('content')
<div class="space-y-5">

    {{-- Top header bar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold font-serif text-paper flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Kitob test topshiriqlari
            </h2>
            <p class="text-xs text-mist font-mono mt-0.5">Kitoblar bo'yicha bilimlarni baholovchi interaktiv testlar</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('teacher.quiz-reviews.index') }}" class="ks-btn-ghost min-h-11 px-3 py-2 inline-flex items-center text-xs">Yozma javoblar</a>
            <a href="{{ route('teacher.quizzes.create') }}"
               class="ks-btn-primary text-xs py-2 px-3.5 inline-flex items-center gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Yangi test yaratish</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3.5 rounded-panel bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-mono flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Quizzes Table Card --}}
    <div class="ks-panel bg-ink-900 border border-ink-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[72rem] table-fixed border-collapse text-left">
                <colgroup>
                    <col style="width: 23%">
                    <col style="width: 15%">
                    <col style="width: 9%">
                    <col style="width: 9%">
                    <col style="width: 10%">
                    <col style="width: 7%">
                    <col style="width: 27%">
                </colgroup>
                <thead>
                    <tr class="border-b border-ink-border bg-ink-950/60 font-mono text-[11px] uppercase tracking-wider text-mist">
                        <th class="whitespace-nowrap px-3 py-2.5">Test nomi</th>
                        <th class="whitespace-nowrap px-3 py-2.5">Bog'langan kitob</th>
                        <th class="whitespace-nowrap px-3 py-2.5">Murakkabligi</th>
                        <th class="whitespace-nowrap px-3 py-2.5">Savollar</th>
                        <th class="whitespace-nowrap px-3 py-2.5">Topshirganlar</th>
                        <th class="whitespace-nowrap px-3 py-2.5">Vaqt</th>
                        <th class="whitespace-nowrap px-3 py-2.5 text-right">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border font-sans text-xs">
                    @forelse($quizzes as $quiz)
                        <tr class="hover:bg-ink-800/40 transition-colors">
                            <td class="py-2.5 px-3.5">
                                <div class="flex min-w-0 items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-btn bg-ink-800 border border-ink-border text-amber-400 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="max-w-full truncate font-semibold text-paper" title="{{ $quiz->title }}">{{ $quiz->title }}</p>
                                        @if($quiz->description)
                                            <p class="max-w-full truncate text-[11px] text-mist" title="{{ $quiz->description }}">{{ $quiz->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="py-2.5 px-3.5">
                                @if($quiz->book)
                                    <a href="{{ route('books.show', $quiz->book->slug) }}" target="_blank" class="group flex min-w-0 items-center gap-2">
                                        <img src="{{ $quiz->book->cover_url }}" class="w-6 h-8 object-cover rounded border border-ink-border" alt="{{ $quiz->book->title }}">
                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-paper group-hover:text-amber-400 transition-colors truncate max-w-[140px]">{{ $quiz->book->title }}</p>
                                            <span class="whitespace-nowrap text-[10px] font-mono text-mist">{{ $quiz->book->week_number ? "{$quiz->book->week_number}-hafta" : 'Kitob' }}</span>
                                        </div>
                                    </a>
                                @else
                                    <span class="text-xs font-mono text-mist">— Umumiy</span>
                                @endif
                            </td>

                            <td class="py-2.5 px-3.5">
                                @php
                                    $diffConfig = [
                                        'easy'   => ['label' => 'Oson',   'class' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'],
                                        'medium' => ['label' => 'O\'rta',  'class' => 'bg-amber-500/10 text-amber-400 border border-amber-500/25'],
                                        'hard'   => ['label' => 'Qiyin',  'class' => 'bg-[#C1392B]/15 text-rose-300 border border-rose-500/30'],
                                    ];
                                    $dc = $diffConfig[$quiz->difficulty] ?? ['label' => $quiz->difficulty, 'class' => 'bg-ink-800 text-mist border border-ink-border'];
                                @endphp
                                <span class="whitespace-nowrap rounded-pill px-2 py-0.5 text-[11px] font-mono font-medium {{ $dc['class'] }}">
                                    {{ $dc['label'] }}
                                </span>
                            </td>

                            <td class="py-2.5 px-3.5">
                                <span class="whitespace-nowrap rounded-pill border border-ink-border bg-ink-800 px-2 py-0.5 text-[11px] font-mono text-mist">
                                    {{ $quiz->questions_count }} ta savol
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-3 py-2.5 font-mono text-xs text-mist">
                                {{ $quiz->attempts_count }} marta
                                @if($quiz->pending_attempts_count > 0)
                                    <span class="mt-1 block font-sans text-[10px] font-bold text-amber-300">{{ $quiz->pending_attempts_count }} ta yozma javob kutilmoqda</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-3 py-2.5 font-mono text-xs text-mist">
                                {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' daqiqa' : 'Cheksiz' }}
                            </td>

                            <td class="px-3 py-2.5 text-right align-middle">
                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                    <a href="{{ route('teacher.quizzes.show', ['quiz' => $quiz, 'section' => 'results']) }}"
                                       class="inline-flex min-h-10 items-center justify-center rounded-btn border border-sky-500/25 bg-sky-500/10 px-2.5 text-[11px] font-semibold text-sky-200 transition-colors hover:bg-sky-500/20">
                                        Natijalar <span class="ml-1 font-mono">{{ $quiz->attempts_count }}</span>
                                    </a>
                                    <a href="{{ route('teacher.quizzes.show', ['quiz' => $quiz, 'section' => 'questions']) }}"
                                       class="inline-flex min-h-10 items-center justify-center rounded-btn border border-ink-border bg-ink-800 px-2.5 text-[11px] font-semibold text-paper transition-colors hover:border-amber-500/30 hover:text-amber-300">
                                        Savollar
                                    </a>
                                    <a href="{{ route('teacher.quizzes.edit', $quiz) }}"
                                       class="inline-flex min-h-10 items-center justify-center rounded-btn border border-amber-500/25 bg-amber-500/10 px-2.5 text-[11px] font-semibold text-amber-300 transition-colors hover:bg-amber-500/20">
                                        Tahrirlash
                                    </a>

                                    {{-- O'chirish --}}
                                    <form action="{{ route('teacher.quizzes.destroy', $quiz->id) }}" method="POST"
                                          onsubmit="return confirm('Ushbu test va uning barcha savollari butunlay o\'chiriladi. Rozimisiz?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-btn border border-ink-border text-mist transition-colors hover:bg-ink-800 hover:text-rose-300"
                                                title="O'chirish" aria-label="Testni o'chirish">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-4 text-center">
                                <div class="max-w-md mx-auto space-y-2">
                                    <div class="w-10 h-10 mx-auto rounded-btn bg-ink-800 border border-ink-border flex items-center justify-center text-amber-400">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-paper font-serif">Hozircha testlar mavjud emas</h3>
                                    <p class="text-xs text-mist font-sans">Kitoblar mutolaasini tekshirish uchun yangi test topshiriqlari va savollar yarating.</p>
                                    <a href="{{ route('teacher.quizzes.create') }}"
                                       class="ks-btn-primary text-xs py-1.5 px-3 inline-flex items-center gap-1.5 mt-2">
                                        <span>+ Birinchi testni yaratish</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($quizzes->hasPages())
            <div class="p-3 border-t border-ink-border font-mono text-xs">
                {{ $quizzes->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
