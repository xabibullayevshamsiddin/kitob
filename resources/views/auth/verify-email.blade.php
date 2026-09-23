@extends('layouts.auth')

@section('title', 'Emailni tasdiqlash')

@section('content')
<div class="mb-6 text-center">
    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-400 mx-auto flex items-center justify-center mb-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
    </div>
    <h1 class="text-2xl font-bold text-white tracking-tight">Emailni tasdiqlang</h1>
    <p class="text-sm text-slate-400 mt-2">Davom etishdan oldin emailingizga yuborilgan tasdiqlash havolasini bosing.</p>
</div>

@if (session('status') == 'verification-link-sent')
    <div class="mb-4 p-3.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-xs font-medium text-center">
        Yangi tasdiqlash havolasi emailingizga yuborildi!
    </div>
@endif

<div class="space-y-3">
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl text-sm transition-all">
            Tasdiqlash havolasini qayta yuborish
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl text-sm transition-all">
            Chiqish
        </button>
    </form>
</div>
@endsection
