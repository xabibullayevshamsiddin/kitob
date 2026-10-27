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

    <x-password-input name="password" id="password" :label="__('site.auth.password')" :autofocus="true" autocomplete="new-password" placeholder="Kamida 8 ta belgi" />

    <x-password-input name="password_confirmation" id="password_confirmation" :label="__('site.auth.confirm_password')" autocomplete="new-password" placeholder="••••••••" />

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
