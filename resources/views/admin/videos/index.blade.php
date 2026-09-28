@extends('admin.layouts.app')
@section('title', 'Kitob videolarini boshqarish')

@section('content')
<div class="space-y-6">

    {{-- Top header bar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>🎥</span> Kitob Videolari
            </h2>
            <p class="text-sm text-slate-500">Kitoblarga biriktirilgan video sharhlar, tahlillar va video darslar</p>
        </div>
        <a href="{{ route('admin.videos.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-xl transition-all shadow-md shadow-indigo-600/25 active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Yangi video qo'shish
        </a>
    </div>

    {{-- Table Card --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-800/50 border-b border-slate-800">
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Video</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Bog'langan kitob</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Turi</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Davomiyligi</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Qo'shilgan</th>
                        <th class="px-5 py-3.5 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($videos as $video)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative w-16 h-10 rounded-lg overflow-hidden bg-slate-800 shrink-0 border border-slate-700">
                                        <img src="{{ $video->thumbnail_url }}" class="w-full h-full object-cover" alt="{{ $video->title }}">
                                        <span class="absolute inset-0 flex items-center justify-center bg-black/40 text-white text-xs">▶</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-white truncate max-w-xs">{{ $video->title }}</p>
                                        @if(str_starts_with($video->video_path, 'http'))
                                            <a href="{{ $video->video_path }}" target="_blank" class="text-[10px] text-indigo-400 hover:underline truncate block max-w-xs">Tashqi havola ↗</a>
                                        @else
                                            <span class="text-[10px] text-slate-500">Yuklangan video fayl</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if($video->book)
                                    <a href="{{ route('books.show', $video->book->slug) }}" target="_blank" class="flex items-center gap-2 group">
                                        <img src="{{ $video->book->cover_url }}" class="w-7 h-9 object-cover rounded shadow" alt="{{ $video->book->title }}">
                                        <div>
                                            <p class="text-xs font-bold text-slate-200 group-hover:text-amber-400 transition-colors">{{ $video->book->title }}</p>
                                            <span class="text-[10px] text-slate-500">Kitobga bog'langan</span>
                                        </div>
                                    </a>
                                @else
                                    <span class="text-xs text-slate-500">—</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                @if($video->type === 'overview')
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-violet-500/10 text-violet-400 border border-violet-500/20">
                                        📺 Umumiy sharh
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                        📖 {{ $video->chapter_number ? $video->chapter_number . '-bob' : 'Bob videosi' }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                                    {{ $video->duration ? gmdate('i:s', $video->duration) : '—' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-xs text-slate-400">
                                {{ $video->created_at->format('d.m.Y H:i') }}
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.videos.edit', $video->id) }}"
                                       class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 hover:bg-indigo-500/20 transition-colors"
                                       title="Tahrirlash / Kitobni o'zgartirish">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.videos.destroy', $video->id) }}" method="POST"
                                          onsubmit="return confirm('Rostdan ham ushbu video darsni o\'chirmoqchimisiz?')" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-500/20 transition-colors"
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
                                <span class="text-4xl block mb-2">🎥</span>
                                <p class="text-slate-400 font-medium">Hozircha videolar mavjud emas</p>
                                <p class="text-slate-600 text-xs mt-1">Kitoblarga birinchi video darsni yuklang</p>
                                <a href="{{ route('admin.videos.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white font-bold text-xs rounded-xl shadow">
                                    + Video yuklash
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($videos->hasPages())
            <div class="p-4 sm:p-5 border-t border-slate-800">
                {{ $videos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
