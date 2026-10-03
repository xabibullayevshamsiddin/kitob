@extends('teacher.layouts.app')
@section('title', 'Kitoblar')

@section('content')
<div class="space-y-5">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold font-serif text-paper flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20 M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                Kitoblar ro'yxati
            </h2>
            <p class="text-xs text-mist font-mono mt-0.5">O'qituvchiga tegishli kitoblar va ularning test topshiriqlari</p>
        </div>
        <a href="{{ route('teacher.books.create') }}"
           class="ks-btn-primary text-xs py-2 px-3.5 inline-flex items-center gap-1.5 shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            <span>Yangi kitob</span>
        </a>
    </div>

    <div class="ks-panel bg-ink-900 border border-ink-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-ink-border bg-ink-950/60 font-mono text-[11px] uppercase tracking-wider text-mist">
                        <th class="py-2.5 px-3.5 w-12 text-center">#</th>
                        <th class="py-2.5 px-3.5">Kitob</th>
                        <th class="py-2.5 px-3.5">Muallif</th>
                        <th class="py-2.5 px-3.5">Janr</th>
                        <th class="py-2.5 px-3.5">Testlar</th>
                        <th class="py-2.5 px-3.5">Holat</th>
                        <th class="py-2.5 px-3.5 text-right">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border font-sans text-xs">
                    @forelse($books as $book)
                        @php $quizCount = $book->quizzes()->count(); @endphp
                        <tr class="hover:bg-ink-800/40 transition-colors">
                            <td class="py-2.5 px-3.5 text-center font-mono text-mist">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="py-2.5 px-3.5">
                                <a href="{{ route('books.show', $book->slug) }}" target="_blank" class="font-medium text-paper hover:text-amber-400 transition-colors line-clamp-1">
                                    {{ $book->title }}
                                </a>
                                <p class="text-[11px] font-mono text-mist">{{ $book->week_number }}-hafta</p>
                            </td>
                            <td class="py-2.5 px-3.5 text-mist font-medium">{{ $book->author }}</td>
                            <td class="py-2.5 px-3.5">
                                <span class="px-2 py-0.5 rounded-pill text-[11px] font-mono bg-ink-800 border border-ink-border text-mist">
                                    {{ $book->genre }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3.5">
                                <span @class([
                                    'px-2 py-0.5 rounded-pill text-[11px] font-mono font-medium',
                                    'bg-amber-500/10 text-amber-400 border border-amber-500/25' => $quizCount > 0,
                                    'bg-ink-800 text-mist border border-ink-border' => $quizCount === 0,
                                ])>
                                    {{ $quizCount }} ta test
                                </span>
                            </td>
                            <td class="py-2.5 px-3.5">
                                <span @class([
                                    'px-2 py-0.5 rounded-pill text-[11px] font-mono font-medium',
                                    'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' => $book->is_active,
                                    'bg-ink-800 text-mist border border-ink-border' => !$book->is_active,
                                ])>
                                    {{ $book->is_active ? 'Aktiv' : 'Noaktiv' }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('teacher.quizzes.create', ['book_id' => $book->id]) }}"
                                       class="ks-btn-primary py-1 px-2.5 text-[11px] inline-flex items-center gap-1"
                                       title="Ushbu kitobga test qo'shish">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                        <span>Test qo'shish</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 px-4 text-center text-mist font-mono text-xs">Hali kitoblar mavjud emas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($books->hasPages())
            <div class="p-3 border-t border-ink-border font-mono text-xs">
                {{ $books->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
