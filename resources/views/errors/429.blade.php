@extends('errors.layout')

@section('code', '429')
@section('title', 'Juda ko\'p so\'rov')
@section('badge', '✦ 429 XATOLIK • TEZLIK CHEKLOVI')
@section('icon', '🚦')

@section('message')
    Qisqa vaqt ichida juda ko'p so'rov yuborildi. Server barqarorligini va foydalanuvchilar xavfsizligini ta'minlash maqsadida cheklov qo'llanildi. Iltimos, 1 daqiqa kutib, so'ng davom eting.
@endsection

@section('actions')
    <button onclick="window.location.reload()" 
            class="px-7 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider transition-all duration-200 active:scale-95 shadow-lg shadow-amber-500/25 flex items-center gap-2 cursor-pointer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>Qayta tekshirish</span>
    </button>

    <a href="{{ route('home') }}" 
       class="px-6 py-3.5 rounded-xl bg-ink-900 hover:bg-ink-800 text-slate-200 font-semibold text-xs border border-white/10 hover:border-amber-400/30 transition-all duration-200 active:scale-95">
        Bosh sahifa
    </a>
@endsection
