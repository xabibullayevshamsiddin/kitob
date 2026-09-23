@extends('layouts.auth')

@section('title', 'Parolni tiklash')

@section('content')
<div class="mb-6 text-center">
    <h1 class="text-2xl font-bold text-white tracking-tight">Parolni unutdingizmi?</h1>
    <p class="text-sm text-slate-400 mt-1">Emailingizni kiriting, tiklash havolasini yuboramiz</p>
</div>

@if (session('status'))
    <div class="mb-4 p-3.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-xs font-medium">
        {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}" class="space-y-4">
    @csrf
    <div>
        <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email manzil</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
            class="w-full px-4 py-2.5 bg-slate-900/60 border border-slate-700 text-white rounded-xl focus:ring-2 focus:ring-indigo-500 placeholder-slate-500 text-sm"
            placeholder="ismingiz@misol.uz">
    </div>
    <button type="submit"
        class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl text-sm transition-all shadow-lg shadow-indigo-600/30">
        Tiklash havolasini yuborish
    </button>
</form>

<div class="mt-6 pt-6 border-t border-slate-700/60 text-center">
    <a href="{{ route('login') }}" class="text-xs text-indigo-400 hover:text-indigo-300">← Tizimga qaytish</a>
</div>
@endsection
