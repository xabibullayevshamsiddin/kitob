@extends('admin.layouts.app')

@section('title', 'Kitoblar boshqaruvi')
@section('breadcrumb', 'Kitoblar')

@section('content')

{{-- Page header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
    <div>
        <h1 class="text-xl font-bold font-serif text-paper">Kitoblar boshqaruvi</h1>
        <p class="text-xs text-mist font-mono mt-0.5">Platformadagi barcha kitoblarni boshqaring</p>
    </div>
    <div class="flex items-center gap-2.5">
        <span class="text-xs text-mist bg-ink-950 border border-ink-border px-3 py-1.5 rounded-badge font-mono">
            Jami: <span class="text-amber-400 font-bold">{{ $books->total() }}</span> ta
        </span>
        <a href="{{ route('admin.books.create') ?? '#' }}"
           class="ks-btn-primary py-1.5 px-3.5 text-xs font-mono inline-flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Yangi kitob</span>
        </a>
    </div>
</div>

{{-- Stats row --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4 font-mono text-xs">
    @php
        $totalBooks = $books->total();
        $activeBooks = $books->getCollection()->where('is_active', true)->count();
    @endphp
    <div class="bg-ink-900 border border-ink-border rounded-panel px-3.5 py-2.5 flex items-center gap-3">
        <div class="w-8 h-8 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-amber-400">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        </div>
        <div>
            <p class="text-[10px] text-mist uppercase">Jami</p>
            <p class="text-base font-bold text-paper">{{ $books->total() }}</p>
        </div>
    </div>
    <div class="bg-ink-900 border border-ink-border rounded-panel px-3.5 py-2.5 flex items-center gap-3">
        <div class="w-8 h-8 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-emerald-400">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        </div>
        <div>
            <p class="text-[10px] text-mist uppercase">Faol</p>
            <p class="text-base font-bold text-emerald-400">{{ $books->getCollection()->where('is_active', true)->count() }}</p>
        </div>
    </div>
    <div class="bg-ink-900 border border-ink-border rounded-panel px-3.5 py-2.5 flex items-center gap-3">
        <div class="w-8 h-8 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-rose-300">
            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
        </div>
        <div>
            <p class="text-[10px] text-mist uppercase">Nofaol</p>
            <p class="text-base font-bold text-rose-300">{{ $books->getCollection()->where('is_active', false)->count() }}</p>
        </div>
    </div>
    <div class="bg-ink-900 border border-ink-border rounded-panel px-3.5 py-2.5 flex items-center gap-3">
        <div class="w-8 h-8 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-amber-500">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div>
            <p class="text-[10px] text-mist uppercase">Bu hafta</p>
            <p class="text-base font-bold text-amber-400">{{ $books->getCollection()->where('created_at', '>=', now()->startOfWeek())->count() }}</p>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="bg-ink-900 border border-ink-border rounded-panel overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-ink-950/60 border-b border-ink-border text-[11px] font-mono uppercase text-mist">
                    <th class="py-2.5 px-3.5 w-14">Muqova</th>
                    <th class="py-2.5 px-3.5">Sarlavha</th>
                    <th class="py-2.5 px-3.5">Muallif</th>
                    <th class="py-2.5 px-3.5">Janr</th>
                    <th class="py-2.5 px-3.5">Hafta</th>
                    <th class="py-2.5 px-3.5 text-center">Holat</th>
                    <th class="py-2.5 px-3.5 text-right">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-border/50 text-xs">
                @forelse($books as $book)
                    <tr class="hover:bg-ink-800/40 transition-colors group" id="book-row-{{ $book->id }}">
                        {{-- Cover --}}
                        <td class="py-2.5 px-3.5">
                            @if($book->cover_image)
                                <img src="{{ asset('storage/' . $book->cover_image) }}"
                                     alt="{{ $book->title }}"
                                     class="w-8 h-12 object-cover rounded-badge border border-ink-border">
                            @else
                                <div class="w-8 h-12 rounded-badge bg-ink-950 flex items-center justify-center text-xs font-mono text-mist border border-ink-border">
                                    KB
                                </div>
                            @endif
                        </td>

                        {{-- Title --}}
                        <td class="py-2.5 px-3.5">
                            <p class="text-xs font-bold font-serif text-paper group-hover:text-amber-400 transition-colors line-clamp-1">{{ $book->title }}</p>
                            <p class="text-[10px] text-mist font-mono mt-0.5">{{ $book->created_at->format('d.m.Y') }}</p>
                        </td>

                        {{-- Author --}}
                        <td class="py-2.5 px-3.5 font-mono text-[11px] text-mist">
                            {{ $book->author }}
                        </td>

                        {{-- Genre --}}
                        <td class="py-2.5 px-3.5">
                            @if($book->genre)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-badge text-[10px] font-mono bg-ink-950 text-mist border border-ink-border">
                                    {{ $book->genre->name ?? $book->genre }}
                                </span>
                            @else
                                <span class="text-mist text-xs">—</span>
                            @endif
                        </td>

                        {{-- Week --}}
                        <td class="py-2.5 px-3.5">
                            @if($book->week_number)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-badge text-[10px] font-mono font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    {{ $book->week_number }}-hafta
                                </span>
                            @else
                                <span class="text-mist text-xs">—</span>
                            @endif
                        </td>

                        {{-- Active toggle --}}
                        <td class="py-2.5 px-3.5 text-center">
                            <form method="POST" action="{{ route('admin.books.toggle', $book) }}" id="toggle-form-{{ $book->id }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-150 {{ $book->is_active ? 'bg-amber-500' : 'bg-ink-950 border border-ink-border' }}"
                                        title="{{ $book->is_active ? 'Nofaol qilish' : 'Faollashtirish' }}">
                                    <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-paper shadow transition-transform duration-150 {{ $book->is_active ? 'translate-x-4.5 bg-ink-950' : 'translate-x-0.5' }}"></span>
                                </button>
                            </form>
                            <p class="text-[9px] mt-0.5 font-mono {{ $book->is_active ? 'text-amber-400' : 'text-mist' }}">
                                {{ $book->is_active ? 'Faol' : 'Nofaol' }}
                            </p>
                        </td>

                        {{-- Actions --}}
                        <td class="py-2.5 px-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                {{-- Quick Add Media Badges --}}
                                <a href="{{ route('admin.audios.create', ['book_id' => $book->id]) }}"
                                   class="px-2 py-0.5 rounded-badge bg-ink-950 border border-ink-border text-amber-400 hover:border-amber-400/40 text-[10px] font-mono font-bold transition-colors"
                                   title="Ushbu kitobga audio qo'shish">
                                    +AUDIO
                                </a>

                                <a href="{{ route('admin.videos.create', ['book_id' => $book->id]) }}"
                                   class="px-2 py-0.5 rounded-badge bg-ink-950 border border-ink-border text-amber-400 hover:border-amber-400/40 text-[10px] font-mono font-bold transition-colors"
                                   title="Ushbu kitobga video dars qo'shish">
                                    +VIDEO
                                </a>

                                <a href="{{ route('admin.quizzes.create', ['book_id' => $book->id]) }}"
                                   class="px-2 py-0.5 rounded-badge bg-ink-950 border border-emerald-500/30 text-emerald-400 hover:border-emerald-500/50 text-[10px] font-mono font-bold transition-colors"
                                   title="Ushbu kitobga test topshirig'i qo'shish">
                                    +TEST
                                </a>

                                <a href="{{ route('admin.books.music.index', $book->id) }}"
                                   class="px-2 py-0.5 rounded-badge bg-ink-950 border border-purple-500/30 text-purple-400 hover:border-purple-500/50 text-[10px] font-mono font-bold transition-colors"
                                   title="Ushbu kitobga fon musiqalari ulash ({{ $book->musics()->count() }} ta)">
                                    🎵 {{ $book->musics()->count() > 0 ? $book->musics()->count() . ' MUSIQA' : '+MUSIQA' }}
                                </a>

                                {{-- PDF yuklab olish --}}
                                @if($book->chapters()->where('is_published', true)->exists())
                                    <a href="{{ route('books.pdf', $book) }}"
                                       class="p-1 rounded-badge bg-ink-950 border border-rose-500/30 text-rose-300 hover:border-rose-500/50 transition-colors"
                                       title="PDF yuklab olish">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </a>
                                @endif

                                {{-- View --}}
                                <a href="{{ route('books.show', $book) }}" target="_blank"
                                   class="p-1 rounded-badge bg-ink-950 border border-ink-border text-mist hover:text-paper hover:border-ink-border transition-colors"
                                   title="Ko'rish">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                {{-- Edit --}}
                                <a href="{{ route('admin.books.edit', $book) }}"
                                   class="p-1 rounded-badge bg-ink-950 border border-ink-border text-amber-400 hover:border-amber-400/50 transition-colors"
                                   title="Tahrirlash">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>

                                {{-- Delete --}}
                                <div x-data="{ confirm: false }" class="relative inline-block">
                                    <button x-show="!confirm" @click="confirm = true"
                                            class="p-1 rounded-badge bg-ink-950 border border-rose-500/30 text-rose-300 hover:border-rose-500/50 transition-colors"
                                            title="O'chirish">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                    <div x-show="confirm"
                                         class="absolute right-0 top-full mt-1 z-20 bg-ink-950 border border-rose-500/40 rounded-panel p-2.5 shadow-2xl w-44"
                                         style="display:none;">
                                        <p class="text-[11px] text-mist font-mono mb-2 text-center">O'chirishni tasdiqlang</p>
                                        <form method="POST" action="{{ route('admin.books.destroy', $book) }}">
                                            @csrf
                                            @method('DELETE')
                                            <div class="flex gap-1.5">
                                                <button type="submit" class="flex-1 py-1 bg-rose-600 hover:bg-rose-500 text-white rounded-btn text-[10px] font-mono">Ha</button>
                                                <button type="button" @click="confirm = false" class="flex-1 py-1 bg-ink-800 text-mist rounded-btn text-[10px] font-mono">Yo'q</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-mist font-mono text-xs">
                            Kitoblar mavjud emas
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($books->hasPages())
        <div class="p-3.5 border-t border-ink-border">
            {{ $books->links('vendor.pagination.taste-livewire') }}
        </div>
    @endif
</div>

@endsection
