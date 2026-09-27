@extends('admin.layouts.app')
@section('title', 'Kitob audiolarini boshqarish')

@section('content')
<div class="space-y-6">

    {{-- Top header bar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>🎵</span> Kitob Audiolari
            </h2>
            <p class="text-sm text-slate-500">Kitoblarga biriktirilgan audio darslar, boblar va audio kitoblar</p>
        </div>
        <a href="{{ route('admin.audios.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-xl transition-all shadow-md shadow-indigo-600/25 active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Yangi audio yuklash
        </a>
    </div>

    {{-- Table Card --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-800/50 border-b border-slate-800">
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Audio nomi</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Bog'langan kitob</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Eshitish (Player)</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Davomiyligi</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Qo'shilgan</th>
                        <th class="px-5 py-3.5 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($audios as $audio)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-lg shrink-0">
                                        🎧
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-white">{{ $audio->title }}</p>
                                        <p class="text-xs text-slate-500 truncate max-w-xs">{{ basename($audio->file_path) }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if($audio->book)
                                    <a href="{{ route('books.show', $audio->book->slug) }}" target="_blank" class="flex items-center gap-2 group">
                                        <img src="{{ $audio->book->cover_url }}" class="w-7 h-9 object-cover rounded shadow" alt="{{ $audio->book->title }}">
                                        <div>
                                            <p class="text-xs font-bold text-slate-200 group-hover:text-amber-400 transition-colors">{{ $audio->book->title }}</p>
                                            <span class="text-[10px] text-slate-500">Kitobga bog'langan</span>
                                        </div>
                                    </a>
                                @else
                                    <span class="text-xs text-slate-500">—</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <audio controls preload="none" class="h-8 max-w-[220px]">
                                    <source src="{{ $audio->file_url }}">
                                    Brauzeringiz audioni qo'llab-quvvatlamaydi.
                                </audio>
                            </td>

                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                                    {{ $audio->duration ? gmdate('i:s', $audio->duration) : '—' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-xs text-slate-400">
                                {{ $audio->created_at->format('d.m.Y H:i') }}
                            </td>

                            <td class="px-5 py-4 text-right">
                                <form action="{{ route('admin.audios.destroy', $audio->id) }}" method="POST"
                                      onsubmit="return confirm('Rostdan ham ushbu audioni o\'chirmoqchimisiz?')" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-500/20 transition-colors"
                                            title="Audioni o'chirish">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <span class="text-4xl block mb-2">🎵</span>
                                <p class="text-slate-400 font-medium">Hozircha audiolar mavjud emas</p>
                                <p class="text-slate-600 text-xs mt-1">Kitoblarga birinchi audio darsni yuklang</p>
                                <a href="{{ route('admin.audios.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white font-bold text-xs rounded-xl shadow">
                                    + Audio yuklash
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($audios->hasPages())
            <div class="p-4 sm:p-5 border-t border-slate-800">
                {{ $audios->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
