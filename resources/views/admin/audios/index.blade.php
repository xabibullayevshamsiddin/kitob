@extends('admin.layouts.app')
@section('title', 'Kitob audiolarini boshqarish')

@section('content')
<div class="space-y-6">

    {{-- Top header bar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold font-serif text-paper flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg> Kitob audiolari
            </h2>
            <p class="text-sm text-mist">Kitoblarga biriktirilgan audio darslar, boblar va audio kitoblar</p>
        </div>
        <a href="{{ route('admin.audios.create') }}"
           class="ks-btn-primary min-h-11 inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 text-sm font-bold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Yangi audio yuklash
        </a>
    </div>

    {{-- Table Card --}}
    <div class="ks-panel overflow-hidden">
        <div class="admin-table-scroll overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-ink-950/60 border-b border-ink-border">
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Audio nomi</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Bog'langan kitob</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Eshitish (Player)</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Davomiyligi</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-mist uppercase tracking-wider">Qo'shilgan</th>
                        <th class="px-5 py-3.5 text-right text-[11px] font-semibold text-mist uppercase tracking-wider">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border/60">
                    @forelse($audios as $audio)
                        <tr class="hover:bg-ink-800/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-card bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-paper">{{ $audio->title }}</p>
                                        <p class="text-xs text-mist truncate max-w-xs">{{ basename($audio->file_path) }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if($audio->book)
                                    <a href="{{ route('books.show', $audio->book->slug) }}" target="_blank" class="flex items-center gap-2 group">
                                        <img src="{{ $audio->book->cover_url }}" class="w-7 h-9 object-cover rounded shadow" alt="{{ $audio->book->title }}">
                                        <div>
                                            <p class="text-xs font-bold text-paper group-hover:text-amber-400 transition-colors">{{ $audio->book->title }}</p>
                                            <span class="text-[10px] text-mist">Kitobga bog'langan</span>
                                        </div>
                                    </a>
                                @else
                                    <span class="text-xs text-mist">—</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <audio controls preload="none" class="h-8 max-w-[220px]">
                                    <source src="{{ $audio->file_url }}">
                                    Brauzeringiz audioni qo'llab-quvvatlamaydi.
                                </audio>
                            </td>

                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 rounded-badge text-xs font-mono font-semibold bg-ink-800 text-paper border border-ink-border">
                                    {{ $audio->duration ? gmdate('i:s', $audio->duration) : '—' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-xs text-mist">
                                {{ $audio->created_at->format('d.m.Y H:i') }}
                            </td>

                            <td class="px-5 py-4 text-right">
                                <form action="{{ route('admin.audios.destroy', $audio->id) }}" method="POST"
                                      onsubmit="return confirm('Rostdan ham ushbu audioni o\'chirmoqchimisiz?')" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-11 h-11 inline-flex items-center justify-center rounded-btn bg-rose-500/10 border border-rose-500/20 text-rose-300 hover:bg-rose-500/20 transition-colors"
                                            aria-label="Audioni o'chirish"
                                            title="Audioni o'chirish">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <p class="text-paper font-medium">Hozircha audiolar mavjud emas</p>
                                <p class="text-mist text-xs mt-1">Kitoblarga birinchi audio darsni yuklang</p>
                                <a href="{{ route('admin.audios.create') }}" class="ks-btn-primary min-h-11 mt-4 inline-flex items-center gap-2 px-4 py-2 text-xs font-bold">
                                    + Audio yuklash
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($audios->hasPages())
            <div class="p-4 sm:p-5 border-t border-ink-border">
                {{ $audios->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
