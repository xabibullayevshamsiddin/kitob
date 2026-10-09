@extends('teacher.layouts.app')
@section('title', 'Jonli efirlar')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="flex items-center gap-2 font-serif text-xl font-bold text-paper sm:text-2xl">
                <span class="relative flex h-3 w-3" aria-hidden="true">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-400 opacity-70"></span>
                    <span class="relative inline-flex h-3 w-3 rounded-full bg-rose-500"></span>
                </span>
                Jonli efirlar va onlayn darslar
            </h2>
            <p class="mt-1 text-xs font-mono text-mist">Kitobxonlar bilan video, audio va chat orqali jonli muloqot</p>
        </div>

        <a href="{{ route('live.index', ['start' => 1]) }}" class="ks-btn-primary inline-flex min-h-11 items-center justify-center gap-2 px-4 py-2 text-xs font-bold">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" d="M19 5 15 9"/></svg>
            Yangi jonli efir boshlash
        </a>
    </div>

    @if(session('success'))
        <div role="status" class="flex items-start gap-2 rounded-panel border border-emerald-500/25 bg-emerald-500/10 p-4 text-sm font-semibold text-emerald-300">
            <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="ks-panel flex items-center gap-3 p-4 sm:p-5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-btn border border-rose-500/25 bg-rose-500/10 text-rose-300">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" d="M19 5 15 9"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-mono uppercase tracking-wider text-mist">Faol efirlar</p>
                <p class="mt-0.5 text-lg font-bold text-paper">{{ \App\Models\LiveEvent::where('status', 'live')->count() }} ta</p>
            </div>
        </div>

        <div class="ks-panel flex items-center gap-3 p-4 sm:p-5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-btn border border-amber-500/25 bg-amber-500/10 text-amber-300">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-mono uppercase tracking-wider text-mist">Rejalashtirilgan</p>
                <p class="mt-0.5 text-lg font-bold text-paper">{{ \App\Models\LiveEvent::where('status', 'scheduled')->count() }} ta</p>
            </div>
        </div>

        <div class="ks-panel flex items-center gap-3 p-4 sm:p-5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-btn border border-sky-500/25 bg-sky-500/10 text-sky-300">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5m-8 6 1.7-3.4A8 8 0 1 1 20 16.5L21 20l-4-1a8 8 0 0 1-12-5v-1"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-mono uppercase tracking-wider text-mist">O‘quvchilar savollari</p>
                <p class="mt-0.5 text-lg font-bold text-paper">{{ \App\Models\LiveQuestion::count() }} ta</p>
            </div>
        </div>
    </div>

    <div class="space-y-3">
        @forelse($events as $event)
            <article class="ks-panel p-4 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 items-start gap-3">
                        <div @class([
                            'flex h-11 w-11 shrink-0 items-center justify-center rounded-btn border',
                            'border-rose-500/30 bg-rose-500/10 text-rose-300' => $event->status === 'live',
                            'border-ink-border bg-ink-800 text-mist' => $event->status !== 'live',
                        ])>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                @if($event->status === 'live')
                                    <circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" d="M19 5 15 9"/>
                                @else
                                    <rect x="3" y="6" width="13" height="12" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m16 10 5-3v10l-5-3"/>
                                @endif
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="break-words text-sm font-bold text-paper">{{ $event->title }}</h3>
                                @if($event->status === 'live')
                                    <span class="rounded-pill border border-rose-500/25 bg-rose-500/10 px-2 py-0.5 text-[10px] font-mono font-bold uppercase tracking-wide text-rose-300">Efirda</span>
                                @else
                                    <span class="rounded-pill border border-amber-500/25 bg-amber-500/10 px-2 py-0.5 text-[10px] font-mono font-bold text-amber-300">Rejalashtirilgan</span>
                                @endif
                                @if($event->book)
                                    <span class="max-w-full truncate rounded-pill border border-ink-border bg-ink-800 px-2 py-0.5 text-[10px] font-mono text-mist">{{ $event->book->title }}</span>
                                @endif
                            </div>

                            @if($event->description)
                                <p class="mt-1 break-words text-xs leading-relaxed text-mist">{{ $event->description }}</p>
                            @endif

                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-[11px] font-mono text-mist">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18"/></svg>
                                    {{ $event->scheduled_at?->timezone('Asia/Tashkent')->format('d.m.Y H:i') ?? $event->created_at->format('d.m.Y H:i') }}
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M5 21a7 7 0 0 1 14 0"/></svg>
                                    {{ $event->hostUser?->name ?? 'Ustoz' }}
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5m-8 6 1.7-3.4A8 8 0 1 1 20 16.5L21 20l-4-1a8 8 0 0 1-12-5v-1"/></svg>
                                    {{ $event->questions()->count() }} savol
                                </span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('live.show', $event->id) }}" class="ks-btn-ghost inline-flex min-h-11 shrink-0 items-center justify-center gap-2 px-4 py-2 text-xs font-bold {{ $event->status === 'live' ? 'border-rose-500/30 text-rose-300 hover:bg-rose-500/10' : 'text-amber-300 hover:border-amber-500/30 hover:bg-amber-500/10' }}">
                        @if($event->status === 'live')
                            Efirga kirish
                        @else
                            Efirni boshlash
                        @endif
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </article>
        @empty
            <div class="ks-panel px-5 py-10 text-center sm:py-14">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-panel border border-amber-500/20 bg-amber-500/10 text-amber-300">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="6" width="13" height="12" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m16 10 5-3v10l-5-3"/></svg>
                </div>
                <h3 class="mt-4 font-serif text-lg font-bold text-paper">Hozircha jonli efirlar yo‘q</h3>
                <p class="mx-auto mt-1 max-w-md text-xs leading-relaxed text-mist">O‘quvchilar bilan kitob muhokamasi yoki onlayn dars o‘tkazish uchun yangi jonli efir boshlang.</p>
                <a href="{{ route('live.index', ['start' => 1]) }}" class="ks-btn-primary mt-5 inline-flex min-h-11 items-center justify-center gap-2 px-4 py-2 text-xs font-bold">
                    Birinchi efirni boshlash
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
                </a>
            </div>
        @endforelse

        @if($events->hasPages())
            <div class="pt-2 font-mono text-xs">{{ $events->links() }}</div>
        @endif
    </div>
</div>
@endsection
