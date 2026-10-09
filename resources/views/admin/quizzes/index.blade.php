@extends('admin.layouts.app')
@section('title', 'Test topshiriqlarini boshqarish')

@section('content')
<div class="space-y-6">

    {{-- Top header bar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold font-serif text-paper flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/><path d="M8 7h8M8 11h6"/></svg> Kitob test topshiriqlari
            </h2>
            <p class="text-sm text-mist">Ustozlar va adminlar yaratgan barcha testlar, topshiruvchilar va natijalar</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.quiz-reviews.index') }}" class="ks-btn-ghost min-h-11 inline-flex items-center px-3 py-2 text-sm">Yozma javoblar</a>
        <a href="{{ route('admin.quizzes.create') }}"
           class="ks-btn-primary min-h-11 inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 text-sm font-bold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Yangi test yaratish
        </a>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="ks-panel overflow-hidden">
        <div class="admin-table-scroll overflow-x-auto">
            <table class="w-full min-w-[88rem] table-fixed">
                <colgroup>
                    <col style="width: 20%">
                    <col style="width: 13%">
                    <col style="width: 13%">
                    <col style="width: 10%">
                    <col style="width: 8%">
                    <col style="width: 9%">
                    <col style="width: 7%">
                    <col style="width: 20%">
                </colgroup>
                <thead>
                    <tr class="bg-ink-950/60 border-b border-ink-border">
                        <th class="whitespace-nowrap px-4 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Test nomi</th>
                        <th class="whitespace-nowrap px-3 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Bog'langan kitob</th>
                        <th class="whitespace-nowrap px-3 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Yaratgan</th>
                        <th class="whitespace-nowrap px-3 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Murakkabligi</th>
                        <th class="whitespace-nowrap px-3 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Savollar</th>
                        <th class="whitespace-nowrap px-3 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Topshirganlar</th>
                        <th class="whitespace-nowrap px-3 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Vaqt</th>
                        <th class="whitespace-nowrap px-3 py-3.5 text-right text-[11px] font-semibold text-mist uppercase tracking-wider">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border/60">
                    @forelse($quizzes as $quiz)
                        <tr class="hover:bg-ink-800/50 transition-colors">
                            <td class="min-w-0 px-4 py-4 align-middle">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="w-10 h-10 rounded-card bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-lg shrink-0">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/><path d="m14.5 9.5 5-5"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-bold text-paper" title="{{ $quiz->title }}">{{ $quiz->title }}</p>
                                        @if($quiz->description)
                                            <p class="max-w-full truncate text-xs text-mist" title="{{ $quiz->description }}">{{ $quiz->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="min-w-0 px-3 py-4 align-middle">
                                @if($quiz->book)
                                    <a href="{{ route('books.show', $quiz->book->slug) }}" target="_blank" class="group flex min-w-0 items-center gap-2">
                                        <img src="{{ $quiz->book->cover_url }}" class="w-7 h-9 object-cover rounded shadow" alt="{{ $quiz->book->title }}">
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-bold text-paper transition-colors group-hover:text-amber-400" title="{{ $quiz->book->title }}">{{ $quiz->book->title }}</p>
                                            <span class="whitespace-nowrap text-[10px] text-mist">Kitobga bog'langan</span>
                                        </div>
                                    </a>
                                @else
                                    <span class="text-xs text-mist">—</span>
                                @endif
                            </td>

                            <td class="min-w-0 px-3 py-4 align-middle">
                                @if($quiz->creator)
                                    <p class="truncate text-xs font-semibold text-paper" title="{{ $quiz->creator->name }}">{{ $quiz->creator->name }}</p>
                                    <span class="text-[10px] text-mist">Test muallifi</span>
                                @else
                                    <span class="text-xs text-mist">Eski test · noma’lum</span>
                                @endif
                            </td>

                            <td class="px-3 py-4 align-middle">
                                @php
                                    $diffConfig = [
                                        'easy'   => ['label' => 'Oson',   'class' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20'],
                                        'medium' => ['label' => 'O\'rta',  'class' => 'bg-amber-500/15 text-amber-400 border-amber-500/20'],
                                        'hard'   => ['label' => 'Qiyin',  'class' => 'bg-rose-500/15 text-rose-300 border-rose-500/20'],
                                    ];
                                    $dc = $diffConfig[$quiz->difficulty] ?? ['label' => $quiz->difficulty, 'class' => 'bg-ink-800 text-mist'];
                                @endphp
                                <span class="whitespace-nowrap rounded-badge border px-2.5 py-1 text-xs font-semibold {{ $dc['class'] }}">
                                    {{ $dc['label'] }}
                                </span>
                            </td>

                            <td class="px-3 py-4 align-middle">
                                <span class="whitespace-nowrap rounded-badge border border-ink-border bg-ink-800 px-2.5 py-1 text-xs font-bold text-paper">
                                    {{ $quiz->questions_count }} ta savol
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-3 py-4 text-xs font-semibold text-mist">
                                {{ $quiz->attempts_count }} marta
                                @if($quiz->pending_attempts_count > 0)
                                    <span class="mt-1 block text-[10px] font-bold text-amber-300">{{ $quiz->pending_attempts_count }} ta yozma javob kutilmoqda</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-3 py-4 font-mono text-xs text-paper">
                                {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' daqiqa' : 'Cheksiz' }}
                            </td>

                            <td class="px-3 py-4 text-right align-middle">
                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                    {{-- Savollarni ko'rish --}}
                                    <a href="{{ route('admin.quizzes.show', ['quiz' => $quiz, 'section' => 'results']) }}"
                                       class="inline-flex min-h-10 items-center justify-center rounded-btn border border-sky-500/25 bg-sky-500/10 px-2.5 text-[11px] font-semibold text-sky-200 transition-colors hover:bg-sky-500/20"
                                       title="Savollarni ko'rish">
                                        Natijalar ({{ $quiz->attempts_count }}) →
                                    </a>

                                    <a href="{{ route('admin.quizzes.show', ['quiz' => $quiz, 'section' => 'questions']) }}" class="inline-flex min-h-10 items-center justify-center rounded-btn border border-ink-border bg-ink-800 px-2.5 text-[11px] font-semibold text-paper hover:border-amber-500/30 hover:text-amber-300">Savollar</a>
                                    <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="inline-flex min-h-10 items-center justify-center rounded-btn border border-amber-500/25 bg-amber-500/10 px-2.5 text-[11px] font-semibold text-amber-300 hover:bg-amber-500/20">Tahrir</a>

                                    {{-- O'chirish --}}
                                    <form action="{{ route('admin.quizzes.destroy', $quiz->id) }}" method="POST"
                                          onsubmit="return confirm('Rostdan ham ushbu testni va undagi barcha savollarni o\'chirmoqchimisiz?')" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-btn border border-rose-500/20 bg-rose-500/10 text-rose-300 transition-colors hover:bg-rose-500/20"
                                                aria-label="Testni o'chirish"
                                                title="Testni o'chirish">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <p class="text-paper font-medium">Hozircha test topshiriqlari mavjud emas</p>
                                <p class="text-mist text-xs mt-1">Kitoblar bo'yicha birinchi test topshirig'ini yarating</p>
                                <a href="{{ route('admin.quizzes.create') }}" class="ks-btn-primary min-h-11 mt-4 inline-flex items-center gap-2 px-4 py-2 text-xs font-bold">
                                    + Test yaratish
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($quizzes->hasPages())
            <div class="p-4 sm:p-5 border-t border-ink-border">
                {{ $quizzes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
