@extends('layouts.auth')

@section('title', 'Yangi parol o\'rnatish')

@section('content')
<div class="mb-6 text-center space-y-1">
    <h1 class="text-2xl font-bold font-serif text-paper">Yangi parol o'rnatish</h1>
    <p class="text-xs text-mist font-sans">Hisobingiz uchun yangi va xavfsiz parol kiriting</p>
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

<form method="POST" action="{{ route('password.update') }}" class="space-y-4">
    @csrf

    <input type="hidden" name="token" value="{{ $request->route('token') }}">
    <input type="hidden" name="email" value="{{ old('email', $request->email) }}">

    <div>
        <label for="password" class="block text-xs font-mono uppercase tracking-wider {{ $errors->has('password') ? 'text-rose-400 font-semibold' : 'text-mist' }} mb-1.5">{{ __('site.auth.password') }}</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none {{ $errors->has('password') ? 'text-rose-400' : 'text-mist' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <input id="password" type="password" name="password" required autofocus autocomplete="new-password"
                class="ks-input pl-9 {{ $errors->has('password') ? '!border-2 !border-rose-500 !bg-rose-500/10 focus:!border-rose-500 focus:!ring-rose-500/30 text-rose-200' : '' }}"
                placeholder="Kamida 8 ta belgi">
        </div>
        @error('password')
            <p class="text-[11px] text-rose-400 font-mono mt-1.5 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ $message }}</span>
            </p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-xs font-mono uppercase tracking-wider {{ $errors->has('password_confirmation') || $errors->has('password') ? 'text-rose-400 font-semibold' : 'text-mist' }} mb-1.5">{{ __('site.auth.confirm_password') }}</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none {{ $errors->has('password_confirmation') || $errors->has('password') ? 'text-rose-400' : 'text-mist' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                class="ks-input pl-9 {{ $errors->has('password_confirmation') || $errors->has('password') ? '!border-2 !border-rose-500 !bg-rose-500/10 focus:!border-rose-500 focus:!ring-rose-500/30 text-rose-200' : '' }}"
                placeholder="••••••••">
        </div>
        @error('password_confirmation')
            <p class="text-[11px] text-rose-400 font-mono mt-1.5 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ $message }}</span>
            </p>
        @enderror
    </div>

    <button type="submit"
        class="ks-btn-primary w-full py-2.5 flex items-center justify-center gap-2">
        <span>Yangi parolni saqlash</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
    </button>
</form>

<div class="mt-6 pt-5 border-t border-ink-border text-center">
    <a href="{{ route('login') }}" class="text-xs text-amber-400 font-semibold hover:text-amber-300 transition-colors inline-flex items-center gap-1.5">
        <span>← {{ __('site.auth.back_to_login') }}</span>
    </a>
</div>
@endsection
