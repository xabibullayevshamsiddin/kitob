@extends('layouts.auth')

@section('title', 'Tizimga kirish')

@section('content')
<div class="mb-6 text-center">
    <h1 class="text-2xl font-bold text-white tracking-tight">Xush kelibsiz!</h1>
    <p class="text-sm text-slate-400 mt-1">Haftalik kitoblar dunyosiga kiring</p>
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

@if (session('status'))
    <div class="mb-4 p-3.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-xs font-medium">
        {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email manzil</label>
        <div class="relative rounded-xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
            </div>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="w-full pl-11 pr-4 py-2.5 bg-slate-900/60 border border-slate-700 text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-slate-500 text-sm transition-all"
                placeholder="ismingiz@misol.uz">
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Parol</label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">Unutdingizmi?</a>
            @endif
        </div>
        <div class="relative rounded-xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="w-full pl-11 pr-4 py-2.5 bg-slate-900/60 border border-slate-700 text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-slate-500 text-sm transition-all"
                placeholder="••••••••">
        </div>
    </div>

    <div class="flex items-center justify-between pt-1">
        <label class="flex items-center cursor-pointer">
            <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-800">
            <span class="ml-2 text-xs text-slate-400 select-none">Meni eslab qol</span>
        </label>
    </div>

    <button type="submit"
        class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-semibold rounded-xl shadow-lg shadow-indigo-600/30 transition-all transform active:scale-[0.98] text-sm flex items-center justify-center gap-2">
        <span>Kirish</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
    </button>
</form>

<div class="mt-6 pt-6 border-t border-slate-700/60 text-center">
    <p class="text-xs text-slate-400">
        Akkauntingiz yo'qmi?
        <a href="{{ route('register') }}" class="text-indigo-400 font-semibold hover:text-indigo-300 ml-1 transition-colors">Ro'yxatdan o'tish</a>
    </p>
</div>
@endsection
