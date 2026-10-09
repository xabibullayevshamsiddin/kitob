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
            <p class="text-sm text-mist">Kitoblarga biriktirilgan bilimlarni tekshirish testlari va savollari</p>
        </div>
        <a href="{{ route('admin.quizzes.create') }}"
           class="ks-btn-primary min-h-11 inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 text-sm font-bold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Yangi test yaratish
        </a>
    </div>

    {{-- Table Card --}}
    <div class="ks-panel overflow-hidden">
        <div class="admin-table-scroll overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-ink-950/60 border-b border-ink-border">
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Test nomi</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Bog'langan kitob</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Murakkabligi</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Savollar</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Topshirganlar</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Vaqt</th>
                        <th class="px-5 py-3.5 text-right text-[11px] font-semibold text-mist uppercase tracking-wider">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border/60">
                    @forelse($quizzes as $quiz)
                        <tr class="hover:bg-ink-800/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-card bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-lg shrink-0">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/><path d="m14.5 9.5 5-5"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-paper">{{ $quiz->title }}</p>
                                        @if($quiz->description)
                                            <p class="text-xs text-mist truncate max-w-xs">{{ $quiz->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if($quiz->book)
                                    <a href="{{ route('books.show', $quiz->book->slug) }}" target="_blank" class="flex items-center gap-2 group">
                                        <img src="{{ $quiz->book->cover_url }}" class="w-7 h-9 object-cover rounded shadow" alt="{{ $quiz->book->title }}">
                                        <div>
                                            <p class="text-xs font-bold text-paper group-hover:text-amber-400 transition-colors">{{ $quiz->book->title }}</p>
                                            <span class="text-[10px] text-mist">Kitobga bog'langan</span>
                                        </div>
                                    </a>
                                @else
                                    <span class="text-xs text-mist">—</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                @php
                                    $diffConfig = [
                                        'easy'   => ['label' => 'Oson',   'class' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20'],
                                        'medium' => ['label' => 'O\'rta',  'class' => 'bg-amber-500/15 text-amber-400 border-amber-500/20'],
                                        'hard'   => ['label' => 'Qiyin',  'class' => 'bg-rose-500/15 text-rose-300 border-rose-500/20'],
                                    ];
                                    $dc = $diffConfig[$quiz->difficulty] ?? ['label' => $quiz->difficulty, 'class' => 'bg-ink-800 text-mist'];
                                @endphp
                                <span class="px-2.5 py-1 rounded-badge text-xs font-semibold border {{ $dc['class'] }}">
                                    {{ $dc['label'] }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 rounded-badge text-xs font-bold bg-ink-800 text-paper border border-ink-border">
                                    {{ $quiz->questions_count }} ta savol
                                </span>
                            </td>

                            <td class="px-5 py-4 text-xs font-semibold text-mist">
                                {{ $quiz->attempts_count }} marta
                            </td>

                            <td class="px-5 py-4 text-xs font-mono text-paper">
                                {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' daqiqa' : 'Cheksiz' }}
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Savollarni ko'rish --}}
                                    <a href="{{ route('admin.quizzes.show', $quiz->id) }}"
                                       class="min-h-11 inline-flex items-center px-3 py-1.5 rounded-btn bg-ink-800 border border-ink-border text-xs font-semibold text-paper hover:bg-ink-700 transition-colors"
                                       title="Savollarni ko'rish">
                                        Savollar ({{ $quiz->questions_count }}) →
                                    </a>

                                    {{-- O'chirish --}}
                                    <form action="{{ route('admin.quizzes.destroy', $quiz->id) }}" method="POST"
                                          onsubmit="return confirm('Rostdan ham ushbu testni va undagi barcha savollarni o\'chirmoqchimisiz?')" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-11 h-11 inline-flex items-center justify-center rounded-btn bg-rose-500/10 border border-rose-500/20 text-rose-300 hover:bg-rose-500/20 transition-colors"
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
                            <td colspan="7" class="px-5 py-16 text-center">
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
