@extends('admin.layouts.app')
@section('title', 'Karta to\'lovlari')

@section('content')
@php
    $money = fn (int $v) => number_format($v, 0, '.', ' ');
    $riskClasses = [
        'low'    => 'border-emerald-500/25 bg-emerald-500/10 text-emerald-300',
        'medium' => 'border-amber-500/25 bg-amber-500/10 text-amber-300',
        'high'   => 'border-rose-500/30 bg-rose-500/10 text-rose-300',
    ];
@endphp
<div class="mx-auto max-w-5xl space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="font-mono text-xs uppercase tracking-widest text-amber-400">Moliya</p>
            <div class="mt-1 flex flex-wrap items-center gap-3">
                <h1 class="font-serif text-2xl font-bold text-paper">Karta to'lovlari</h1>
                @if(!$enabled)
                    <span class="rounded-pill border border-ink-border bg-ink-800 px-2.5 py-1 text-xs font-bold text-mist">O'chirilgan</span>
                @endif
            </div>
            <p class="mt-1 text-sm text-mist">Bugun tasdiqlangan: <span class="font-mono font-semibold text-paper">{{ $money($todayApproved) }} so'm</span></p>
        </div>
        <a href="{{ route('admin.topups.settings') }}" class="ks-btn-ghost inline-flex min-h-11 items-center justify-center px-4 py-2 text-xs font-bold">Karta va paketlar</a>
    </div>

    @include('admin.topups.partials.flash')

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <nav class="flex flex-wrap gap-1.5">
            @foreach($tabs as $key => $label)
                @php $count = $key === 'all' ? $counts->sum() : ($counts[$key] ?? 0); @endphp
                <a href="{{ route('admin.topups.index', ['status' => $key]) }}"
                   class="inline-flex min-h-10 items-center gap-1.5 rounded-btn border px-3 text-xs font-semibold {{ $tab === $key ? 'border-amber-500/40 bg-amber-500/10 text-amber-300' : 'border-ink-border text-mist hover:text-paper' }}">
                    {{ $label }} <span class="font-mono">{{ $count }}</span>
                </a>
            @endforeach
        </nav>
        <form method="GET" class="flex gap-2">
            <input type="hidden" name="status" value="{{ $tab }}">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Summa, #KOD yoki ism"
                   class="min-h-10 w-full sm:w-56 rounded-btn border border-ink-border bg-ink-900 px-3 text-sm text-paper focus:border-amber-500/50 focus:outline-none">
        </form>
    </div>

    <div class="ks-panel overflow-hidden divide-y divide-ink-border">
        @forelse($requests as $item)
            <a href="{{ route('admin.topups.show', $item) }}" class="group flex flex-col gap-3 p-4 transition hover:bg-ink-800/60 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <span class="font-mono text-lg font-bold text-paper">{{ $money($item->expected_amount) }} so'm</span>
                        <span class="text-mist">·</span>
                        <span class="font-semibold text-paper">{{ $item->user?->name ?? 'O\'chirilgan foydalanuvchi' }}</span>
                        <span class="text-xs text-mist">{{ '@' . $item->user?->username }}</span>
                    </div>
                    <p class="mt-1 truncate text-xs font-mono text-mist">
                        #{{ $item->reference }} · {{ $item->reward_label }} · {{ ($item->submitted_at ?? $item->created_at)->timezone('Asia/Tashkent')->format('d.m.Y H:i') }} · {{ $item->status_label }}
                    </p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    @if($item->receipt_path)
                        <span class="rounded-pill border px-2.5 py-1 text-xs font-bold {{ $riskClasses[$item->risk_level] }}">Xavf {{ $item->risk_score }}</span>
                    @endif
                    <span class="ks-btn-ghost inline-flex min-h-10 items-center px-3 text-xs font-bold text-amber-300">Ochish →</span>
                </div>
            </a>
        @empty
            <div class="p-10 text-center">
                <p class="font-semibold text-paper">Bu bo'limda so'rov yo'q</p>
                <p class="mt-1 text-sm text-mist">Foydalanuvchi chek yuklaganda shu yerda ko'rinadi va sizga bildirishnoma keladi.</p>
            </div>
        @endforelse
    </div>
    @if($requests->hasPages())
        <div class="font-mono text-xs">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
