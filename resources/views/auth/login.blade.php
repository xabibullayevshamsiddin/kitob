@extends('layouts.auth')

@section('title', __('site.auth.login_title'))

@section('content')
<div class="mb-6 text-center space-y-1">
    <h1 class="text-2xl font-bold font-serif text-paper">{{ __('site.auth.welcome') }}</h1>
    <p class="text-xs text-mist font-sans">{{ __('site.auth.welcome_sub') }}</p>
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
    <div class="mb-4 p-3 bg-ink-950 border border-emerald-500/30 text-emerald-400 rounded-card text-xs font-medium">
        {{ session('status') }}
    </div>
@endif

{{-- Telegram orqali tezkor kirish --}}
<div class="mb-5 space-y-3">
    <a href="{{ route('auth.telegram.redirect') }}" 
       class="w-full py-2.5 px-4 rounded-xl bg-[#24A1DE] hover:bg-[#2092c9] active:scale-[0.99] text-white font-medium text-xs flex items-center justify-center gap-2.5 transition-all shadow-md shadow-[#24A1DE]/20 group">
        <svg class="w-4 h-4 fill-current group-hover:scale-110 transition-transform shrink-0" viewBox="0 0 24 24">
            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.121l-6.871 4.326-2.962-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.196 1.006.128.832.926z"/>
        </svg>
        <span class="font-bold tracking-wide">Telegram orqali 1 bosishda kirish</span>
    </a>

    <div class="relative flex items-center justify-center pt-1">
        <div class="border-t border-ink-border w-full"></div>
        <span class="bg-ink-900 px-3 text-[10px] font-mono uppercase tracking-wider text-mist shrink-0">yoki telefon raqami bilan</span>
        <div class="border-t border-ink-border w-full"></div>
    </div>
</div>

<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="block text-xs font-mono uppercase tracking-wider text-mist mb-1.5">Telefon raqam</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-mist">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </div>
            <input id="email" type="tel" name="email" value="{{ old('email') }}" required autofocus autocomplete="tel"
                class="ks-input pl-9 font-mono"
                placeholder="+998 90 123 45 67">
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label for="password" class="block text-xs font-mono uppercase tracking-wider text-mist">{{ __('site.auth.password') }}</label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs text-amber-400 hover:text-amber-300 transition-colors">{{ __('site.auth.forgot_password') }}</a>
            @endif
        </div>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-mist">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="ks-input pl-9"
                placeholder="••••••••">
        </div>
    </div>

    <div class="flex items-center justify-between pt-1">
        <label class="flex items-center cursor-pointer">
            <input type="checkbox" name="remember" checked class="w-4 h-4 rounded bg-ink-950 border-ink-border text-amber-500 focus:ring-amber-500 focus:ring-offset-ink-900">
            <span class="ml-2 text-xs text-mist select-none">{{ __('site.auth.remember_me') }}</span>
        </label>
    </div>

    <button type="submit"
        class="ks-btn-primary w-full py-2.5 flex items-center justify-center gap-2">
        <span>{{ __('site.auth.login_btn') }}</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
    </button>
</form>

<div class="mt-6 pt-5 border-t border-ink-border text-center">
    <p class="text-xs text-mist">
        {{ __('site.auth.no_account') }}
        <a href="{{ route('register') }}" class="text-amber-400 font-semibold hover:text-amber-300 ml-1 transition-colors">{{ __('site.auth.register_now') }}</a>
    </p>
</div>
@endsection
