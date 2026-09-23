@extends('admin.layouts.app')

@section('title', 'Kitoblar boshqaruvi')
@section('breadcrumb', 'Kitoblar')

@section('content')

{{-- Page header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-white">Kitoblar boshqaruvi</h1>
        <p class="text-sm text-slate-500 mt-0.5">Platformadagi barcha kitoblarni boshqaring</p>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-xs text-slate-500 bg-slate-800 border border-slate-700 px-3 py-1.5 rounded-lg">
            Jami: <span class="text-indigo-400 font-semibold">{{ $books->total() }}</span> ta
        </span>
        <a href="{{ route('admin.books.create') ?? '#' }}"
           class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-indigo-900/30">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Yangi kitob
        </a>
    </div>
</div>

{{-- Stats row --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
    @php
        $totalBooks = $books->total();
        $activeBooks = $books->getCollection()->where('is_active', true)->count();
    @endphp
    <div class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-indigo-500/15 flex items-center justify-center text-lg">📚</div>
        <div>
            <p class="text-xs text-slate-500">Jami</p>
            <p class="text-lg font-bold text-white">{{ $books->total() }}</p>
        </div>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-emerald-500/15 flex items-center justify-center text-lg">✅</div>
        <div>
            <p class="text-xs text-slate-500">Faol</p>
            <p class="text-lg font-bold text-emerald-400">{{ $books->getCollection()->where('is_active', true)->count() }}</p>
        </div>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-red-500/15 flex items-center justify-center text-lg">⏸️</div>
        <div>
            <p class="text-xs text-slate-500">Nofaol</p>
            <p class="text-lg font-bold text-red-400">{{ $books->getCollection()->where('is_active', false)->count() }}</p>
        </div>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-amber-500/15 flex items-center justify-center text-lg">📅</div>
        <div>
            <p class="text-xs text-slate-500">Bu hafta</p>
            <p class="text-lg font-bold text-amber-400">{{ $books->getCollection()->where('created_at', '>=', now()->startOfWeek())->count() }}</p>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-800">
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider w-16">Muqova</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Sarlavha</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Muallif</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Janr</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Hafta</th>
                    <th class="px-5 py-3.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Holat</th>
                    <th class="px-5 py-3.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse($books as $book)
                    <tr class="hover:bg-slate-800/30 transition-colors group" id="book-row-{{ $book->id }}">
                        {{-- Cover --}}
                        <td class="px-5 py-3.5">
                            @if($book->cover_image)
                                <img src="{{ asset('storage/' . $book->cover_image) }}"
                                     alt="{{ $book->title }}"
                                     class="w-10 h-14 object-cover rounded-lg shadow-md group-hover:shadow-indigo-900/30 transition-shadow">
                            @else
                                <div class="w-10 h-14 rounded-lg bg-gradient-to-b from-indigo-800 to-slate-800 flex items-center justify-center text-xl border border-slate-700">
                                    📖
                                </div>
                            @endif
                        </td>

                        {{-- Title --}}
                        <td class="px-5 py-3.5">
                            <p class="text-sm font-semibold text-slate-200 group-hover:text-white transition-colors line-clamp-1">{{ $book->title }}</p>
                            <p class="text-xs text-slate-600 mt-0.5">{{ $book->created_at->format('d.m.Y') }}</p>
                        </td>

                        {{-- Author --}}
                        <td class="px-5 py-3.5">
                            <p class="text-sm text-slate-400">{{ $book->author }}</p>
                        </td>

                        {{-- Genre --}}
                        <td class="px-5 py-3.5">
                            @if($book->genre)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-violet-500/10 text-violet-400 border border-violet-500/20">
                                    {{ $book->genre->name ?? $book->genre }}
                                </span>
                            @else
                                <span class="text-slate-600 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Week --}}
                        <td class="px-5 py-3.5">
                            @if($book->week_number)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    📅 {{ $book->week_number }}-hafta
                                </span>
                            @else
                                <span class="text-slate-600 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Active toggle --}}
                        <td class="px-5 py-3.5 text-center">
                            <form method="POST" action="{{ route('admin.books.toggle', $book) }}" id="toggle-form-{{ $book->id }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 {{ $book->is_active ? 'bg-indigo-600' : 'bg-slate-700' }}"
                                        title="{{ $book->is_active ? 'Nofaol qilish' : 'Faollashtirish' }}">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-md transition-transform duration-200 {{ $book->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </form>
                            <p class="text-[10px] mt-1 {{ $book->is_active ? 'text-indigo-400' : 'text-slate-600' }}">
                                {{ $book->is_active ? 'Faol' : 'Nofaol' }}
                            </p>
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                {{-- View --}}
                                <a href="{{ route('books.show', $book) }}" target="_blank"
                                   class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-800 border border-slate-700 text-slate-400 hover:text-white hover:bg-slate-700 hover:border-slate-600 transition-all"
                                   title="Ko'rish">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                {{-- Edit --}}
                                <a href="{{ route('admin.books.edit', $book) }}"
                                   class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 hover:bg-indigo-500/20 transition-all"
                                   title="Tahrirlash">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>

                                {{-- Delete --}}
                                <div x-data="{ confirm: false }" class="relative">
                                    <button x-show="!confirm" @click="confirm = true"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20 transition-all"
                                            title="O'chirish">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                    <div x-show="confirm"
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="absolute right-0 top-full mt-1 z-20 bg-slate-800 border border-red-500/30 rounded-xl p-3 shadow-2xl w-48"
                                         style="display:none;">
                                        <p class="text-xs text-slate-300 font-medium mb-2 text-center">Kitobni o'chirishni tasdiqlang</p>
                                        <form method="POST" action="{{ route('admin.books.destroy', $book) }}">
                                            @csrf
                                            @method('DELETE')
                                            <div class="flex gap-2">
                                                <button type="submit" class="flex-1 py-1.5 bg-red-600 hover:bg-red-500 text-white rounded-lg text-xs font-semibold transition-colors">Ha</button>
                                                <button type="button" @click="confirm = false" class="flex-1 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-xs transition-colors">Yo'q</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <div class="text-5xl mb-4">📚</div>
                            <p class="text-slate-400 font-medium mb-1">Kitoblar topilmadi</p>
                            <p class="text-slate-600 text-sm mb-4">Hali birorta kitob qo'shilmagan</p>
                            <a href="{{ route('admin.books.create') ?? '#' }}"
                               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-xl transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Birinchi kitobni qo'shing
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($books->hasPages())
        <div class="px-5 py-4 border-t border-slate-800 flex items-center justify-between">
            <p class="text-xs text-slate-500">
                {{ $books->firstItem() }}–{{ $books->lastItem() }} / {{ $books->total() }} ta kitob
            </p>
            <div class="flex items-center gap-1">
                @if($books->onFirstPage())
                    <span class="px-3 py-1.5 text-xs text-slate-600 bg-slate-800 rounded-lg cursor-not-allowed border border-slate-700">← Oldingi</span>
                @else
                    <a href="{{ $books->previousPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-lg transition-colors border border-slate-700">← Oldingi</a>
                @endif

                @foreach($books->getUrlRange(max(1, $books->currentPage()-2), min($books->lastPage(), $books->currentPage()+2)) as $page => $url)
                    @if($page == $books->currentPage())
                        <span class="w-8 h-8 flex items-center justify-center text-xs font-semibold text-white bg-indigo-600 rounded-lg">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center text-xs text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-lg transition-colors border border-slate-700">{{ $page }}</a>
                    @endif
                @endforeach

                @if($books->hasMorePages())
                    <a href="{{ $books->nextPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-lg transition-colors border border-slate-700">Keyingi →</a>
                @else
                    <span class="px-3 py-1.5 text-xs text-slate-600 bg-slate-800 rounded-lg cursor-not-allowed border border-slate-700">Keyingi →</span>
                @endif
            </div>
        </div>
    @endif
</div>

@endsection
