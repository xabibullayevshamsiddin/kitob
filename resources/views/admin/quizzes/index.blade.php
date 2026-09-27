@extends('admin.layouts.app')
@section('title', 'Test topshiriqlarini boshqarish')

@section('content')
<div class="space-y-6">

    {{-- Top header bar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>📝</span> Kitob Test Topshiriqlari
            </h2>
            <p class="text-sm text-slate-500">Kitoblarga biriktirilgan bilimlarni tekshirish testlari va savollari</p>
        </div>
        <a href="{{ route('admin.quizzes.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-xl transition-all shadow-md shadow-indigo-600/25 active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Yangi test yaratish
        </a>
    </div>

    {{-- Table Card --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-800/50 border-b border-slate-800">
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Test nomi</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Bog'langan kitob</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Murakkabligi</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Savollar</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Topshirganlar</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Vaqt</th>
                        <th class="px-5 py-3.5 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($quizzes as $quiz)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-lg shrink-0">
                                        🎯
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-white">{{ $quiz->title }}</p>
                                        @if($quiz->description)
                                            <p class="text-xs text-slate-500 truncate max-w-xs">{{ $quiz->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if($quiz->book)
                                    <a href="{{ route('books.show', $quiz->book->slug) }}" target="_blank" class="flex items-center gap-2 group">
                                        <img src="{{ $quiz->book->cover_url }}" class="w-7 h-9 object-cover rounded shadow" alt="{{ $quiz->book->title }}">
                                        <div>
                                            <p class="text-xs font-bold text-slate-200 group-hover:text-amber-400 transition-colors">{{ $quiz->book->title }}</p>
                                            <span class="text-[10px] text-slate-500">Kitobga bog'langan</span>
                                        </div>
                                    </a>
                                @else
                                    <span class="text-xs text-slate-500">—</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                @php
                                    $diffConfig = [
                                        'easy'   => ['label' => '🟢 Oson',   'class' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20'],
                                        'medium' => ['label' => '🟡 O\'rta',  'class' => 'bg-amber-500/15 text-amber-400 border-amber-500/20'],
                                        'hard'   => ['label' => '🔴 Qiyin',  'class' => 'bg-rose-500/15 text-rose-400 border-rose-500/20'],
                                    ];
                                    $dc = $diffConfig[$quiz->difficulty] ?? ['label' => $quiz->difficulty, 'class' => 'bg-slate-700 text-slate-300'];
                                @endphp
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $dc['class'] }}">
                                    {{ $dc['label'] }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                    {{ $quiz->questions_count }} ta savol
                                </span>
                            </td>

                            <td class="px-5 py-4 text-xs font-semibold text-slate-400">
                                👥 {{ $quiz->attempts_count }} marta
                            </td>

                            <td class="px-5 py-4 text-xs font-mono text-slate-300">
                                ⏱ {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' daqiqa' : 'Cheksiz' }}
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Savollarni ko'rish --}}
                                    <a href="{{ route('admin.quizzes.show', $quiz->id) }}"
                                       class="px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-700 transition-colors"
                                       title="Savollarni ko'rish">
                                        Savollar ({{ $quiz->questions_count }}) →
                                    </a>

                                    {{-- O'chirish --}}
                                    <form action="{{ route('admin.quizzes.destroy', $quiz->id) }}" method="POST"
                                          onsubmit="return confirm('Rostdan ham ushbu testni va undagi barcha savollarni o\'chirmoqchimisiz?')" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-500/20 transition-colors"
                                                title="Testni o'chirish">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <span class="text-4xl block mb-2">📝</span>
                                <p class="text-slate-400 font-medium">Hozircha test topshiriqlari mavjud emas</p>
                                <p class="text-slate-600 text-xs mt-1">Kitoblar bo'yicha birinchi test topshirig'ini yarating</p>
                                <a href="{{ route('admin.quizzes.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white font-bold text-xs rounded-xl shadow">
                                    + Test yaratish
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($quizzes->hasPages())
            <div class="p-4 sm:p-5 border-t border-slate-800">
                {{ $quizzes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
