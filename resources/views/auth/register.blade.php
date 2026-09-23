@extends('layouts.auth')

@section('title', 'Ro\'yxatdan o\'tish')

@section('content')
<div class="mb-6 text-center">
    <h1 class="text-2xl font-bold text-white tracking-tight">Hisob ochish</h1>
    <p class="text-sm text-slate-400 mt-1">Kitobxonlar hamjamiyatiga qo'shiling</p>
</div>

@if ($errors->any())
    <div class="mb-4 p-3.5 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-xs space-y-1">
        @foreach ($errors->all() as $error)
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ $error }}</span>
            </div>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('register') }}" class="space-y-4">
    @csrf

    <div>
        <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">To'liq ismingiz</label>
        <div class="relative rounded-xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="w-full pl-11 pr-4 py-2.5 bg-slate-900/60 border border-slate-700 text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-slate-500 text-sm transition-all"
                placeholder="Ali Valiyev">
        </div>
    </div>

    <div>
        <label for="username" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Foydalanuvchi nomi (username)</label>
        <div class="relative rounded-xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <span class="text-sm font-bold text-slate-500">@</span>
            </div>
            <input id="username" type="text" name="username" value="{{ old('username') }}" required autocomplete="username"
                class="w-full pl-11 pr-4 py-2.5 bg-slate-900/60 border border-slate-700 text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-slate-500 text-sm transition-all"
                placeholder="ali_readly">
        </div>
    </div>

    <div>
        <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email manzil</label>
        <div class="relative rounded-xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
            </div>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                class="w-full pl-11 pr-4 py-2.5 bg-slate-900/60 border border-slate-700 text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-slate-500 text-sm transition-all"
                placeholder="ali@misol.uz">
        </div>
    </div>

    <div>
        <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Parol</label>
        <div class="relative rounded-xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="w-full pl-11 pr-4 py-2.5 bg-slate-900/60 border border-slate-700 text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-slate-500 text-sm transition-all"
                placeholder="Kamida 8 ta belgi">
        </div>
    </div>

    <div>
        <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Parolni tasdiqlang</label>
        <div class="relative rounded-xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                class="w-full pl-11 pr-4 py-2.5 bg-slate-900/60 border border-slate-700 text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-slate-500 text-sm transition-all"
                placeholder="••••••••">
        </div>
    </div>

    <button type="submit"
        class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-semibold rounded-xl shadow-lg shadow-indigo-600/30 transition-all transform active:scale-[0.98] text-sm flex items-center justify-center gap-2 mt-2">
        <span>Ro'yxatdan o'tish</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
    </button>
</form>

<div class="mt-6 pt-6 border-t border-slate-700/60 text-center">
    <p class="text-xs text-slate-400">
        Akkauntingiz bormi?
        <a href="{{ route('login') }}" class="text-indigo-400 font-semibold hover:text-indigo-300 ml-1 transition-colors">Tizimga kirish</a>
    </p>
</div>
@endsection
