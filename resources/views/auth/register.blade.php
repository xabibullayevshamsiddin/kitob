@extends('layouts.auth')

@section('title', __('site.nav.register'))

@section('content')
<div class="mb-6 text-center space-y-1">
    <h1 class="text-2xl font-bold font-serif text-paper">{{ __('site.auth.create_account') }}</h1>
    <p class="text-xs text-mist font-sans">{{ __('site.auth.join_community') }}</p>
</div>

@if(!setting('registration_open', true))
    <div class="mb-5 p-4 rounded-card bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs flex items-start gap-2.5">
        <span class="text-base flex-shrink-0">⚠️</span>
        <div>
            <p class="font-bold">Ro'yxatdan o'tish vaqtincha to'xtatilgan</p>
            <p class="text-mist mt-0.5">Hozirda yangi foydalanuvchilarni qabul qilish ma'muriyat tomonidan vaqtincha yopilgan. Mavjud hisobingiz bo'lsa, tizimga kiring.</p>
        </div>
    </div>
@endif

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

<form method="POST" action="{{ route('register') }}" class="space-y-3.5">
    @csrf

    <div>
        <label for="name" class="block text-xs font-mono uppercase tracking-wider text-mist mb-1.5">{{ __('site.auth.name') }}</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-mist">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="ks-input pl-9"
                placeholder="Ali Valiyev">
        </div>
    </div>

    <div>
        <label for="username" class="block text-xs font-mono uppercase tracking-wider text-mist mb-1.5">{{ __('site.auth.username') }}</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-mist font-mono text-xs">
                @
            </div>
            <input id="username" type="text" name="username" value="{{ old('username') }}" required autocomplete="username"
                class="ks-input pl-8 font-mono"
                placeholder="ali_kitobxon">
        </div>
    </div>

    <div>
        <label for="email" class="block text-xs font-mono uppercase tracking-wider text-mist mb-1.5">{{ __('site.auth.email') }}</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-mist">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
            </div>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                class="ks-input pl-9"
                placeholder="ali@misol.uz">
        </div>
    </div>

    <div>
        <label for="password" class="block text-xs font-mono uppercase tracking-wider text-mist mb-1.5">{{ __('site.auth.password') }}</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-mist">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="ks-input pl-9"
                placeholder="Kamida 8 ta belgi">
        </div>
    </div>

    <div>
        <label for="password_confirmation" class="block text-xs font-mono uppercase tracking-wider text-mist mb-1.5">{{ __('site.auth.confirm_password') }}</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-mist">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                class="ks-input pl-9"
                placeholder="••••••••">
        </div>
    </div>

    <button type="submit"
        {{ !setting('registration_open', true) ? 'disabled' : '' }}
        class="{{ !setting('registration_open', true) ? 'ks-btn-ghost opacity-50 cursor-not-allowed' : 'ks-btn-primary' }} w-full py-2.5 flex items-center justify-center gap-2 mt-2">
        <span>{{ !setting('registration_open', true) ? 'Ro\'yxatdan o\'tish yopiq' : __('site.nav.register') }}</span>
        @if(setting('registration_open', true))
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        @endif
    </button>
</form>

<div class="mt-6 pt-5 border-t border-ink-border text-center">
    <p class="text-xs text-mist">
        {{ __('site.auth.have_account') }}
        <a href="{{ route('login') }}" class="text-amber-400 font-semibold hover:text-amber-300 ml-1 transition-colors">{{ __('site.auth.login_now') }}</a>
    </p>
</div>
@endsection
