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
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-ink-border bg-ink-950/60 font-mono text-[11px] uppercase tracking-wider text-mist">
                        <th class="py-2.5 px-3.5">Test nomi</th>
                        <th class="py-2.5 px-3.5">Bog'langan kitob</th>
                        <th class="py-2.5 px-3.5">Murakkabligi</th>
                        <th class="py-2.5 px-3.5">Savollar</th>
                        <th class="py-2.5 px-3.5">Topshirganlar</th>
                        <th class="py-2.5 px-3.5">Vaqt</th>
                        <th class="py-2.5 px-3.5 text-right">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border font-sans text-xs">
                    @forelse($quizzes as $quiz)
                        <tr class="hover:bg-ink-800/40 transition-colors">
                            <td class="py-2.5 px-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-btn bg-ink-800 border border-ink-border text-amber-400 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-paper truncate max-w-xs">{{ $quiz->title }}</p>
                                        @if($quiz->description)
                                            <p class="text-[11px] text-mist truncate max-w-xs">{{ $quiz->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="py-2.5 px-3.5">
                                @if($quiz->book)
                                    <a href="{{ route('books.show', $quiz->book->slug) }}" target="_blank" class="flex items-center gap-2 group">
                                        <img src="{{ $quiz->book->cover_url }}" class="w-6 h-8 object-cover rounded border border-ink-border" alt="{{ $quiz->book->title }}">
                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-paper group-hover:text-amber-400 transition-colors truncate max-w-[140px]">{{ $quiz->book->title }}</p>
                                            <span class="text-[10px] font-mono text-mist">{{ $quiz->book->week_number ? "{$quiz->book->week_number}-hafta" : 'Kitob' }}</span>
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
                                <span class="px-2 py-0.5 rounded-pill text-[11px] font-mono font-medium {{ $dc['class'] }}">
                                    {{ $dc['label'] }}
                                </span>
                            </td>

                            <td class="py-2.5 px-3.5">
                                <span class="px-2 py-0.5 rounded-pill text-[11px] font-mono bg-ink-800 border border-ink-border text-mist">
                                    {{ $quiz->questions_count }} ta savol
                                </span>
                            </td>

                            <td class="py-2.5 px-3.5 font-mono text-xs text-mist">
                                {{ $quiz->attempts_count }} marta
                            </td>

                            <td class="py-2.5 px-3.5 font-mono text-xs text-mist">
                                {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' daqiqa' : 'Cheksiz' }}
                            </td>

                            <td class="py-2.5 px-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Savollarni ko'rish --}}
                                    <a href="{{ route('teacher.quizzes.show', $quiz->id) }}"
                                       class="px-2.5 py-1 rounded-btn bg-ink-800 hover:bg-ink-700/60 border border-ink-border text-xs font-mono text-paper transition-colors">
                                        Savollar ({{ $quiz->questions_count }})
                                    </a>

                                    {{-- O'chirish --}}
                                    <form action="{{ route('teacher.quizzes.destroy', $quiz->id) }}" method="POST"
                                          onsubmit="return confirm('Ushbu test va uning barcha savollari butunlay o\'chiriladi. Rozimisiz?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-1 rounded-btn text-mist hover:text-rose-300 hover:bg-ink-800 border border-transparent hover:border-ink-border transition-colors"
                                                title="O'chirish">
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

    {{-- Kitoblar bo'yicha tezkor test qo'shish paneli --}}
    <div class="ks-panel bg-ink-900 border border-ink-border p-5 space-y-4">
        <div class="flex items-center justify-between border-b border-ink-border pb-3">
            <div>
                <h3 class="font-bold text-paper text-sm flex items-center gap-2 font-serif">
                    <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20 M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    Kitoblarga to'g'ridan-to'g'ri test qo'shish
                </h3>
                <p class="text-xs text-mist font-mono mt-0.5">Istalgan kitobni tanlab, unga yangi test savollarini tezda biriktiring</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5">
            @foreach($books as $book)
                <div class="p-3.5 rounded-panel border border-ink-border bg-ink-950/60 flex flex-col justify-between hover:border-amber-500/30 transition-all">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[10px] font-mono text-amber-400">{{ $book->week_number }}-hafta</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded-pill bg-ink-800 border border-ink-border text-mist">
                                {{ $book->quizzes_count }} test
                            </span>
                        </div>
                        <h4 class="text-xs font-semibold text-paper line-clamp-1">{{ $book->title }}</h4>
                        <p class="text-[11px] text-mist line-clamp-1 font-sans">{{ $book->author }}</p>
                    </div>

                    <a href="{{ route('teacher.quizzes.create', ['book_id' => $book->id]) }}"
                       class="mt-3 w-full py-1.5 px-3 ks-btn-ghost text-[11px] text-center justify-center">
                        + Test qo'shish
                    </a>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
