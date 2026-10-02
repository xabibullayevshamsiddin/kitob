@extends('teacher.layouts.app')
@section('title', 'Kitob Test Topshiriqlari')

@section('content')
<div class="space-y-6">

    {{-- Top header bar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>📝</span> Kitob Test Topshiriqlari
            </h2>
            <p class="text-sm text-slate-500">Kitoblar bo'yicha bilimlarni baholovchi interaktiv testlar</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('teacher.quizzes.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-xl transition-all shadow-md shadow-indigo-600/25 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Yangi test yaratish
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm font-bold flex items-center gap-2">
            <span>✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Quizzes Table Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Test nomi</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Bog'langan kitob</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Murakkabligi</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Savollar</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Topshirganlar</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Vaqt</th>
                        <th class="px-5 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($quizzes as $quiz)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg shrink-0">
                                        🎯
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $quiz->title }}</p>
                                        @if($quiz->description)
                                            <p class="text-xs text-slate-400 truncate max-w-xs">{{ $quiz->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if($quiz->book)
                                    <a href="{{ route('books.show', $quiz->book->slug) }}" target="_blank" class="flex items-center gap-2 group">
                                        <img src="{{ $quiz->book->cover_url }}" class="w-7 h-9 object-cover rounded shadow" alt="{{ $quiz->book->title }}">
                                        <div>
                                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 transition-colors">{{ $quiz->book->title }}</p>
                                            <span class="text-[10px] text-slate-400">{{ $quiz->book->week_number ? "{$quiz->book->week_number}-hafta" : 'Kitob' }}</span>
                                        </div>
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">— Umumiy</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                @php
                                    $diffConfig = [
                                        'easy'   => ['label' => '🟢 Oson',   'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'],
                                        'medium' => ['label' => '🟡 O\'rta',  'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'],
                                        'hard'   => ['label' => '🔴 Qiyin',  'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400'],
                                    ];
                                    $dc = $diffConfig[$quiz->difficulty] ?? ['label' => $quiz->difficulty, 'class' => 'bg-slate-100 text-slate-600'];
                                @endphp
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $dc['class'] }}">
                                    {{ $dc['label'] }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                    {{ $quiz->questions_count }} ta savol
                                </span>
                            </td>

                            <td class="px-5 py-4 text-xs font-semibold text-slate-500">
                                👥 {{ $quiz->attempts_count }} marta
                            </td>

                            <td class="px-5 py-4 text-xs font-mono text-slate-600 dark:text-slate-300">
                                ⏱ {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' daqiqa' : 'Cheksiz' }}
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Savollarni ko'rish --}}
                                    <a href="{{ route('teacher.quizzes.show', $quiz->id) }}"
                                       class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-900/30 transition-colors">
                                        Savollar ({{ $quiz->questions_count }})
                                    </a>

                                    {{-- O'chirish --}}
                                    <form action="{{ route('teacher.quizzes.destroy', $quiz->id) }}" method="POST"
                                          onsubmit="return confirm('Ushbu test va uning barcha savollari butunlay o\'chiriladi. Rozimisiz?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors"
                                                title="O'chirish">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="max-w-md mx-auto space-y-3">
                                    <p class="text-4xl">📝</p>
                                    <h3 class="text-base font-bold text-slate-800 dark:text-white">Hozircha testlar mavjud emas</h3>
                                    <p class="text-xs text-slate-500">Kitoblar mutolaasini tekshirish uchun yangi test topshiriqlari va savollar yarating.</p>
                                    <a href="{{ route('teacher.quizzes.create') }}"
                                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl shadow-md transition-all active:scale-95">
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
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700">
                {{ $quizzes->links() }}
            </div>
        @endif
    </div>

    {{-- Kitoblar bo'yicha tezkor test qo'shish paneli --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <span>📚</span> Kitoblarga to'g'ridan-to'g'ri test qo'shish
                </h3>
                <p class="text-xs text-slate-500">Istalgan kitobni tanlab, unga yangi test savollarini tezda biriktiring</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($books as $book)
                <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-750 flex flex-col justify-between hover:border-indigo-400 transition-all">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $book->week_number }}-hafta</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold">
                                {{ $book->quizzes_count }} test
                            </span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-white line-clamp-1">{{ $book->title }}</h4>
                        <p class="text-[11px] text-slate-500 line-clamp-1">{{ $book->author }}</p>
                    </div>

                    <a href="{{ route('teacher.quizzes.create', ['book_id' => $book->id]) }}"
                       class="mt-3 w-full py-1.5 px-3 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-600 hover:text-white text-indigo-600 dark:text-indigo-400 text-[11px] font-bold rounded-lg text-center transition-all">
                        + Test qo'shish
                    </a>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
