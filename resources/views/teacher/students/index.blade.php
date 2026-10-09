@extends('teacher.layouts.app')
@section('title', 'O‘quvchilar')

@section('content')
<div class="space-y-5">
    <div>
        <p class="font-mono text-[11px] uppercase tracking-widest text-amber-400">O‘quvchilar</p>
        <h1 class="mt-1 font-serif text-2xl font-bold text-paper">O‘quvchilar ro‘yxati</h1>
        <p class="mt-1 text-sm text-mist">{{ $students->total() }} nafar o‘quvchi ro‘yxatda</p>
    </div>

    <section class="ks-panel overflow-hidden" aria-label="O‘quvchilar jadvali">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[42rem] text-left">
                <thead class="bg-ink-950/60 font-mono text-[11px] uppercase tracking-wider text-mist">
                    <tr>
                        <th class="px-4 py-3 font-semibold sm:px-5">O‘quvchi</th>
                        <th class="px-3 py-3 font-semibold">Ballar</th>
                        <th class="px-3 py-3 font-semibold">Ketma-ketlik</th>
                        <th class="px-4 py-3 font-semibold sm:px-5">Qo‘shilgan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border">
                    @forelse($students as $student)
                        <tr class="transition-colors hover:bg-ink-800/40">
                            <td class="px-4 py-3 sm:px-5">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $student->avatar_url }}" alt="{{ $student->name }} profil rasmi" class="h-10 w-10 shrink-0 rounded-full border border-ink-border object-cover">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-paper">{{ $student->name }}</p>
                                        <p class="truncate text-xs text-mist">{{ '@' . $student->username }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 font-mono text-sm font-bold text-amber-300">{{ number_format($student->total_points) }}</td>
                            <td class="whitespace-nowrap px-3 py-3">
                                <span class="inline-flex items-center gap-1.5 rounded-badge border border-amber-500/20 bg-amber-500/10 px-2 py-1 text-xs font-semibold text-amber-300">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48 2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48 2.83-2.83"/></svg>
                                    {{ $student->current_streak }} kun
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-mist sm:px-5">{{ $student->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-12 text-center text-sm text-mist">Hozircha o‘quvchilar yo‘q.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())
            <div class="border-t border-ink-border px-4 py-3 sm:px-5">{{ $students->links() }}</div>
        @endif
    </section>
</div>
@endsection
