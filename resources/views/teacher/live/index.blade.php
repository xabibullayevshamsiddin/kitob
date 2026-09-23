@extends('teacher.layouts.app')
@section('title', 'Jonli efirlar')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-800 dark:text-white">🔴 Jonli efirlar</h2>
            <p class="text-sm text-slate-500">Rejalashtirilgan va o'tgan efirlar</p>
        </div>
    </div>

    @forelse($events as $event)
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white">{{ $event->title }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $event->scheduled_at?->format('d.m.Y H:i') }}</p>
                </div>
                @php
                    $colors = ['scheduled' => 'blue', 'live' => 'rose', 'ended' => 'slate'];
                    $labels = ['scheduled' => '📅 Rejalashtirilgan', 'live' => '🔴 Efirda', 'ended' => '✓ Tugagan'];
                    $c = $colors[$event->status] ?? 'slate';
                    $l = $labels[$event->status] ?? $event->status;
                @endphp
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-{{ $c }}-100 text-{{ $c }}-700 dark:bg-{{ $c }}-900/30 dark:text-{{ $c }}-400">
                    {{ $l }}
                </span>
            </div>
        </div>
    @empty
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-12 text-center">
            <p class="text-4xl mb-3">📺</p>
            <p class="text-slate-500">Hali jonli efirlar yo'q</p>
        </div>
    @endforelse

    @if($events->hasPages())
        <div>{{ $events->links() }}</div>
    @endif
</div>
@endsection
