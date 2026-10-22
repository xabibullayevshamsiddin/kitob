@extends('layouts.auth')

@section('title', 'Telegram tasdiqlash kodi')

@section('content')
<div class="space-y-6">
    <div class="text-center space-y-1.5">
        <h1 class="text-2xl font-bold font-serif text-paper">Tasdiqlash kodi</h1>
        <p class="text-xs text-mist font-sans">
            @if($hasTelegram)
                <b>{{ $maskedPhone }}</b> raqamiga ulangan Telegram botingizga 6 xonali tasdiqlash kodi yuborildi.
            @else
                Kodni olish uchun pastdagi tugma orqali Telegram botimizga kiring va <b>«Start»</b> bosing:
            @endif
        </p>
    </div>

    @if ($errors->any())
        <div class="p-3 bg-ink-950 border border-rose-500/30 text-rose-300 rounded-card text-xs space-y-1">
            @foreach ($errors->all() as $error)
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
    @endif

    @if (session('status'))
        <div class="p-3 bg-ink-950 border border-emerald-500/30 text-emerald-400 rounded-card text-xs font-medium text-center">
            {{ session('status') }}
        </div>
    @endif

    @if(!$hasTelegram)
        <div class="p-3.5 rounded-2xl bg-sky-500/10 border border-sky-500/20 text-center space-y-2">
            <p class="text-xs text-sky-300">
                Hisobingizga hali Telegram ulanmagan. Kod olish uchun botni oching:
            </p>
            <a href="{{ $tgUrl }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-[#24A1DE] hover:bg-[#2092c9] text-white rounded-xl text-xs font-bold transition-all shadow-md">
                <span>Telegram botidan kod olish ↗</span>
            </a>
        </div>
    @endif

    <form method="POST" action="{{ route('login.verify-code.submit', ['token' => $token]) }}" class="space-y-4">
        @csrf

        <div>
            <label for="code" class="block text-center text-xs font-mono uppercase tracking-wider {{ $errors->has('code') ? 'text-rose-400 font-semibold' : 'text-mist' }} mb-2">
                6 xonali kodni kiriting
            </label>
            <div class="relative">
                <input id="code" type="text" name="code" required autofocus maxlength="6" inputmode="numeric" autocomplete="one-time-code"
                    class="w-full text-center tracking-[0.5em] text-2xl font-mono font-bold py-3 px-4 rounded-xl text-paper transition-all placeholder:tracking-normal placeholder:text-sm placeholder:text-slate-600 {{ $errors->has('code') ? '!border-2 !border-rose-500 !bg-rose-500/10 focus:!border-rose-500 focus:!ring-rose-500/30 text-rose-300' : 'bg-ink-950 border border-ink-border focus:border-amber-500 focus:ring-1 focus:ring-amber-500' }}"
                    placeholder="••••••">
            </div>
            @error('code')
                <p class="text-[11px] text-center text-rose-400 font-mono mt-1.5 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ $message }}</span>
                </p>
            @enderror
            <p class="text-[11px] text-center text-mist mt-1.5 font-mono">
                Kod Telegram botingizda: <b>@{{ $botUsername }}</b>
            </p>
        </div>

        <button type="submit"
            class="ks-btn-primary w-full py-2.5 flex items-center justify-center gap-2">
            <span>Kirishni tasdiqlash</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>
    </form>

    <!-- Resend Code -->
    <div class="flex items-center justify-between pt-2 border-t border-ink-border text-xs">
        <form method="POST" action="{{ route('login.resend-code', ['token' => $token]) }}">
            @csrf
            <button type="submit" class="text-amber-400 hover:text-amber-300 font-mono transition-colors">
                Kodni qayta yuborish ↻
            </button>
        </form>

        <a href="{{ route('login') }}" class="text-mist hover:text-paper font-mono transition-colors">
            ← Orqaga
        </a>
    </div>
</div>
@endsection
