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


<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    <x-phone-input name="email" id="email" label="Telefon raqam" :required="true" :autofocus="true" />

    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label for="password" class="block text-xs font-mono uppercase tracking-wider {{ $errors->has('password') ? 'text-rose-400 font-semibold' : 'text-mist' }}">{{ __('site.auth.password') }}</label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs text-amber-400 hover:text-amber-300 transition-colors">{{ __('site.auth.forgot_password') }}</a>
            @endif
        </div>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none {{ $errors->has('password') ? 'text-rose-400' : 'text-mist' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="ks-input pl-9 {{ $errors->has('password') ? '!border-2 !border-rose-500 !bg-rose-500/10 focus:!border-rose-500 focus:!ring-rose-500/30 text-rose-200' : '' }}"
                placeholder="••••••••">
        </div>
        @error('password')
            <p class="text-[11px] text-rose-400 font-mono mt-1.5 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ $message }}</span>
            </p>
        @enderror
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
