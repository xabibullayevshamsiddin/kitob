@extends('layouts.auth')

@section('title', 'Parolni tiklash')

@section('content')
<div class="mb-6 text-center space-y-1">
    <h1 class="text-2xl font-bold font-serif text-paper">Parolni tiklash</h1>
    <p class="text-xs text-mist font-sans">Telefon raqamingiz yoki emailingiz orqali parolni yangilang</p>
</div>

@if ($errors->any())
    <div class="mb-4 p-3 bg-ink-950 border border-rose-500/30 text-rose-300 rounded-card text-xs space-y-1">
        @foreach ($errors->all() as $error)
            <div class="flex items-center gap-2">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ $error }}</span>
            </div>
        @endforeach
    </div>
@endif

@if (session('status'))
    <div class="mb-4 p-3 bg-ink-950 border border-emerald-500/30 text-emerald-400 rounded-card text-xs font-medium flex items-center gap-2">
        <span>✅</span>
        <span>{{ session('status') }}</span>
    </div>
@endif

{{-- Telegram orqali parolsiz tezkor kirish --}}
<div class="mb-5 space-y-3">
    <a href="{{ route('auth.telegram.redirect') }}" 
       class="w-full py-2.5 px-4 rounded-xl bg-[#24A1DE] hover:bg-[#2092c9] active:scale-[0.99] text-white font-medium text-xs flex items-center justify-center gap-2.5 transition-all shadow-md shadow-[#24A1DE]/20 group">
        <svg class="w-4 h-4 fill-current group-hover:scale-110 transition-transform shrink-0" viewBox="0 0 24 24">
            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.121l-6.871 4.326-2.962-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.196 1.006.128.832.926z"/>
        </svg>
        <span class="font-bold tracking-wide">Telegram orqali parolsiz kirish</span>
    </a>

    <div class="relative flex items-center justify-center pt-1">
        <div class="border-t border-ink-border w-full"></div>
        <span class="bg-ink-900 px-3 text-[10px] font-mono uppercase tracking-wider text-mist shrink-0">yoki havola olish</span>
        <div class="border-t border-ink-border w-full"></div>
    </div>
</div>

<form method="POST" action="{{ route('password.email') }}" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="block text-xs font-mono uppercase tracking-wider text-mist mb-1.5">Telefon raqam yoki Email</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-mist">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </div>
            <input id="email" type="text" name="email" value="{{ old('email') }}" required autofocus
                class="ks-input pl-9 font-mono"
                placeholder="+998 90 123 45 67 yoki email">
        </div>
        <p class="text-[10px] text-mist mt-1 font-mono">Telegram ulangan bo'lsa, havola botga yuboriladi.</p>
    </div>

    <button type="submit"
        class="ks-btn-primary w-full py-2.5 flex items-center justify-center gap-2">
        <span>Tiklash havolasini olish</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
    </button>
</form>

<div class="mt-6 pt-5 border-t border-ink-border text-center">
    <a href="{{ route('login') }}" class="text-xs text-amber-400 font-semibold hover:text-amber-300 transition-colors inline-flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        <span>{{ __('site.auth.back_to_login') }}</span>
    </a>
</div>
@endsection
