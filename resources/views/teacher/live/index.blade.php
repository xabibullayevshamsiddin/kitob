@extends('teacher.layouts.app')
@section('title', 'Jonli efirlar')

@section('content')
<div class="space-y-6">

    {{-- Top header bar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-600"></span>
                </span>
                <span>🔴 Jonli Efirlar & Onlayn Darslar</span>
            </h2>
            <p class="text-sm text-slate-500">Real vaqtda kitobxonlar bilan video, audio va chat orqali jonli muloqot</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('live.index', ['start' => 1]) }}"
               class="inline-flex items-center gap-2.5 px-5 py-3 rounded-xl bg-gradient-to-r from-rose-600 via-rose-500 to-amber-500 hover:from-rose-500 hover:to-amber-400 text-white font-bold text-sm shadow-lg shadow-rose-600/30 active:scale-95 transition-all">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
                </span>
                <span>🎙️ Yangi Jonli Efir Boshlash</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm font-bold flex items-center gap-2">
            <span>✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 flex items-center justify-center text-xl shrink-0">
                🔴
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400">Faol efirlar</p>
                <p class="text-xl font-black text-slate-800 dark:text-white mt-0.5">
                    {{ \App\Models\LiveEvent::where('status', 'live')->count() }} ta
                </p>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 flex items-center justify-center text-xl shrink-0">
                📅
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400">Rejalashtirilgan</p>
                <p class="text-xl font-black text-slate-800 dark:text-white mt-0.5">
                    {{ \App\Models\LiveEvent::where('status', 'scheduled')->count() }} ta
                </p>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                ❓
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400">O'quvchilar savollari</p>
                <p class="text-xl font-black text-slate-800 dark:text-white mt-0.5">
                    {{ \App\Models\LiveQuestion::count() }} ta
                </p>
            </div>
        </div>
    </div>

    {{-- Live Events List --}}
    <div class="space-y-4">
        @forelse($events as $event)
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 sm:p-6 transition-all hover:border-slate-300 dark:hover:border-slate-600">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr {{ $event->status === 'live' ? 'from-rose-500 to-amber-500 animate-pulse' : 'from-slate-600 to-slate-700' }} flex items-center justify-center text-white text-xl shrink-0 shadow-md">
                            {{ $event->status === 'live' ? '🔴' : '📺' }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-bold text-base text-slate-900 dark:text-white">{{ $event->title }}</h3>
                                @if($event->status === 'live')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500 text-white animate-pulse">
                                        Efirda
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                        Rejalashtirilgan
                                    </span>
                                @endif

                                @if($event->book)
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                        📖 {{ $event->book->title }}
                                    </span>
                                @endif
                            </div>

                            @if($event->description)
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-1">{{ $event->description }}</p>
                            @endif

                            <div class="flex items-center gap-4 mt-2 text-xs text-slate-400">
                                <span>📅 {{ $event->scheduled_at?->timezone('Asia/Tashkent')->format('d.m.Y H:i') ?? $event->created_at->format('d.m.Y H:i') }}</span>
                                <span>👤 Host: {{ $event->hostUser?->name ?? 'Ustoz' }}</span>
                                <span>❓ {{ $event->questions()->count() }} savol</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                        <a href="{{ route('live.show', $event->id) }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl {{ $event->status === 'live' ? 'bg-rose-600 hover:bg-rose-500 text-white shadow-lg shadow-rose-600/30' : 'bg-indigo-600 hover:bg-indigo-500 text-white' }} font-bold text-xs transition-all active:scale-95">
                            <span>{{ $event->status === 'live' ? '🎥 Efirga kirish (Studio)' : '▶ Efirni boshlash' }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-12 text-center shadow-sm">
                <div class="max-w-md mx-auto space-y-4">
                    <div class="w-16 h-16 rounded-3xl bg-rose-50 dark:bg-rose-900/20 text-rose-500 flex items-center justify-center text-3xl mx-auto">
                        🎙️
                    </div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white">Hozircha jonli efirlar yo'q</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        O'quvchilar bilan yangi kitob muhokamasi yoki onlayn dars o'tkazish uchun yangi jonli efir boshlang.
                    </p>
                    <a href="{{ route('live.index', ['start' => 1]) }}"
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-rose-600 to-amber-500 hover:from-rose-500 hover:to-amber-400 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-rose-500/25 active:scale-95 transition-all">
                        <span>🎙️ Birinchi efirni boshlash</span>
                    </a>
                </div>
            </div>
        @endforelse

        @if($events->hasPages())
            <div class="pt-4">{{ $events->links() }}</div>
        @endif
    </div>

</div>
@endsection
