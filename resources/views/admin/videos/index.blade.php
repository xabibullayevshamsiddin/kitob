@extends('admin.layouts.app')
@section('title', 'Kitob videolarini boshqarish')

@section('content')
<div class="space-y-6">

    {{-- Top header bar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold font-serif text-paper flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="14" height="14" rx="2"/><path d="m17 10 4-2v8l-4-2z"/></svg> Kitob videolari
            </h2>
            <p class="text-sm text-mist">Kitoblarga biriktirilgan video sharhlar, tahlillar va video darslar</p>
        </div>
        <a href="{{ route('admin.videos.create') }}"
           class="ks-btn-primary min-h-11 inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 text-sm font-bold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Yangi video qo'shish
        </a>
    </div>

    {{-- Table Card --}}
    <div class="ks-panel overflow-hidden">
        <div class="admin-table-scroll overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-ink-950/60 border-b border-ink-border">
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Video</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Bog'langan kitob</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Turi</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Davomiyligi</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Qo'shilgan</th>
                        <th class="px-5 py-3.5 text-right text-[11px] font-semibold text-mist uppercase tracking-wider">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border/60">
                    @forelse($videos as $video)
                        <tr class="hover:bg-ink-800/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative w-16 h-10 rounded-card overflow-hidden bg-ink-800 shrink-0 border border-ink-border">
                                        <img src="{{ $video->thumbnail_url }}" class="w-full h-full object-cover" alt="{{ $video->title }}">
                                        <span class="absolute inset-0 flex items-center justify-center bg-black/40 text-white" aria-hidden="true"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="m9 6 10 6-10 6z"/></svg></span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-paper truncate max-w-xs">{{ $video->title }}</p>
                                        @if(str_starts_with($video->video_path, 'http'))
                                            <a href="{{ $video->video_path }}" target="_blank" class="text-[10px] text-amber-400 hover:underline truncate block max-w-xs">Tashqi havola ↗</a>
                                        @else
                                            <span class="text-[10px] text-mist">Yuklangan video fayl</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if($video->book)
                                    <a href="{{ route('books.show', $video->book->slug) }}" target="_blank" class="flex items-center gap-2 group">
                                        <img src="{{ $video->book->cover_url }}" class="w-7 h-9 object-cover rounded shadow" alt="{{ $video->book->title }}">
                                        <div>
                                            <p class="text-xs font-bold text-paper group-hover:text-amber-400 transition-colors">{{ $video->book->title }}</p>
                                            <span class="text-[10px] text-mist">Kitobga bog'langan</span>
                                        </div>
                                    </a>
                                @else
                                    <span class="text-xs text-mist">—</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                @if($video->type === 'overview')
                                    <span class="px-2.5 py-1 rounded-badge text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        Umumiy sharh
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-badge text-xs font-semibold bg-ink-800 text-mist border border-ink-border">
                                        {{ $video->chapter_number ? $video->chapter_number . '-bob' : 'Bob videosi' }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 rounded-badge text-xs font-mono font-semibold bg-ink-800 text-paper border border-ink-border">
                                    {{ $video->duration ? gmdate('i:s', $video->duration) : '—' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-xs text-mist">
                                {{ $video->created_at->format('d.m.Y H:i') }}
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.videos.edit', $video->id) }}"
                                       class="w-11 h-11 inline-flex items-center justify-center rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-400 hover:bg-amber-500/20 transition-colors"
                                       aria-label="Videoni tahrirlash"
                                       title="Tahrirlash / Kitobni o'zgartirish">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.videos.destroy', $video->id) }}" method="POST"
                                          onsubmit="return confirm('Rostdan ham ushbu video darsni o\'chirmoqchimisiz?')" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-11 h-11 inline-flex items-center justify-center rounded-btn bg-rose-500/10 border border-rose-500/20 text-rose-300 hover:bg-rose-500/20 transition-colors"
                                                aria-label="Videoni o'chirish"
                                                title="Videoni o'chirish">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <p class="text-paper font-medium">Hozircha videolar mavjud emas</p>
                                <p class="text-mist text-xs mt-1">Kitoblarga birinchi video darsni yuklang</p>
                                <a href="{{ route('admin.videos.create') }}" class="ks-btn-primary min-h-11 mt-4 inline-flex items-center gap-2 px-4 py-2 text-xs font-bold">
                                    + Video yuklash
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($videos->hasPages())
            <div class="p-4 sm:p-5 border-t border-ink-border">
                {{ $videos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
