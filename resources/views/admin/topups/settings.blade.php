@extends('admin.layouts.app')
@section('title', 'Karta to\'lovlari — sozlamalar')

@section('content')
@php
    $rows = old('packages', $packages);
    // Yangi paket qo'shish uchun 2 ta bo'sh qator
    $rows = array_merge(array_values($rows), [[], []]);
    $input = 'w-full min-h-10 rounded-btn border border-ink-border bg-ink-900 px-3 text-sm text-paper focus:border-amber-500/50 focus:outline-none';
@endphp
<div class="mx-auto max-w-4xl space-y-5">
    <div>
        <a href="{{ route('admin.topups.index') }}" class="inline-flex min-h-10 items-center text-sm font-semibold text-amber-400 hover:text-amber-300">← Karta to'lovlari</a>
        <h1 class="mt-1 font-serif text-2xl font-bold text-paper">Karta va paketlar</h1>
        <p class="mt-1 text-sm text-mist">Foydalanuvchilar shu kartaga pul o'tkazadi. Karta raqami faqat tizimga kirgan foydalanuvchilarga ko'rsatiladi.</p>
    </div>

    @include('admin.topups.partials.flash')

    @foreach($warnings as $warning)
        <div role="alert" class="rounded-panel border border-amber-500/30 bg-amber-500/10 p-3 text-sm text-amber-300">⚠️ {{ $warning }}</div>
    @endforeach

    <form method="POST" action="{{ route('admin.topups.settings.update') }}" class="space-y-5">
        @csrf

        <div class="ks-panel p-5 space-y-4">
            <label class="flex items-center gap-3 text-sm font-semibold text-paper">
                <input type="checkbox" name="enabled" value="1" {{ old('enabled', $enabled) ? 'checked' : '' }} class="h-4 w-4">
                Karta orqali sotib olish yoqilgan
            </label>
            <div class="grid gap-3 sm:grid-cols-3">
                <label class="block space-y-1 sm:col-span-3">
                    <span class="text-xs font-semibold text-mist">Karta raqami (16 xona)</span>
                    <input type="text" name="card_number" inputmode="numeric" value="{{ old('card_number', $cardNumber) }}" placeholder="8600 0000 0000 0000" class="{{ $input }} font-mono">
                </label>
                <label class="block space-y-1 sm:col-span-2">
                    <span class="text-xs font-semibold text-mist">Karta egasi</span>
                    <input type="text" name="card_holder" value="{{ old('card_holder', $cardHolder) }}" placeholder="ISM FAMILIYA" class="{{ $input }}">
                </label>
                <label class="block space-y-1">
                    <span class="text-xs font-semibold text-mist">Bank / karta turi</span>
                    <input type="text" name="card_bank" value="{{ old('card_bank', $cardBank) }}" placeholder="Uzcard · Kapitalbank" class="{{ $input }}">
                </label>
                <label class="block space-y-1">
                    <span class="text-xs font-semibold text-mist">Bir foydalanuvchi uchun kunlik limit, so'm</span>
                    <input type="number" name="daily_limit" min="1000" value="{{ old('daily_limit', $dailyLimit) }}" class="{{ $input }} font-mono">
                </label>
            </div>
        </div>

        <div class="ks-panel p-5 space-y-3">
            <div>
                <p class="font-semibold text-paper">Paketlar</p>
                <p class="text-xs text-mist">Narx kamida 1 000 so'm. Tanga yoki balldan kamida bittasi 0 dan katta bo'lsin. Nomi bo'sh qator saqlanmaydi.</p>
            </div>
            <div class="hidden sm:grid grid-cols-[1.4fr_1fr_0.8fr_0.8fr_auto] gap-2 text-[11px] font-mono uppercase text-mist">
                <span>Nomi</span><span>Narx, so'm</span><span>Tanga</span><span>Ball</span><span>Faol</span>
            </div>
            @foreach($rows as $i => $row)
                <div class="grid grid-cols-2 sm:grid-cols-[1.4fr_1fr_0.8fr_0.8fr_auto] gap-2 items-center">
                    <input type="hidden" name="packages[{{ $i }}][key]" value="{{ $row['key'] ?? '' }}">
                    <input type="text" name="packages[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}" placeholder="Masalan: 250 tanga" class="{{ $input }} col-span-2 sm:col-span-1">
                    <input type="number" name="packages[{{ $i }}][price]" min="0" value="{{ $row['price'] ?? '' }}" placeholder="Narx" class="{{ $input }} font-mono">
                    <input type="number" name="packages[{{ $i }}][coins]" min="0" value="{{ $row['coins'] ?? '' }}" placeholder="Tanga" class="{{ $input }} font-mono">
                    <input type="number" name="packages[{{ $i }}][points]" min="0" value="{{ $row['points'] ?? '' }}" placeholder="Ball" class="{{ $input }} font-mono">
                    <label class="flex min-h-10 items-center gap-2 text-xs text-mist">
                        <input type="checkbox" name="packages[{{ $i }}][active]" value="1" {{ ($row['active'] ?? !isset($row['title'])) ? 'checked' : '' }} class="h-4 w-4">
                        <span class="sm:hidden">Faol</span>
                    </label>
                </div>
            @endforeach
        </div>

        <button type="submit" class="min-h-11 px-6 rounded-btn bg-amber-500 hover:bg-amber-400 text-ink-950 text-sm font-bold">Saqlash</button>
    </form>
</div>
@endsection
