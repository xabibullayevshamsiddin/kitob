@extends('errors.layout')

@section('code', '500')
@section('title', 'Serverda nosozlik')
@section('badge', '✦ 500 XATOLIK • TIZIM XATOLIGI')
@section('icon', '⚡')

@section('message')
    Serverimiz kutilmagan ichki xatolikka duch keldi. Muhandislarimiz allaqachon bu nosozlik haqida ogohlantirilgan va tuzatish ishlarini olib bormoqda.
@endsection

@section('actions')
    <button onclick="window.location.reload()" 
            class="px-7 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider transition-all duration-200 active:scale-95 shadow-lg shadow-amber-500/25 flex items-center gap-2 cursor-pointer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        <span>Qayta urinib ko'rish</span>
    </button>

    <a href="{{ route('home') }}" 
       class="px-6 py-3.5 rounded-xl bg-ink-900 hover:bg-ink-800 text-slate-200 font-semibold text-xs border border-white/10 hover:border-amber-400/30 transition-all duration-200 active:scale-95">
        Bosh sahifaga qaytish
    </a>

    <a href="{{ route('contact') }}" 
       class="px-6 py-3.5 rounded-xl bg-ink-900 hover:bg-ink-800 text-slate-200 font-semibold text-xs border border-white/10 hover:border-amber-400/30 transition-all duration-200 active:scale-95">
        Aloqa xizmati
    </a>
@endsection
