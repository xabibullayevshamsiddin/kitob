@extends('errors.layout')

@section('code', '403')
@section('title', 'Ruxsat etilmagan hudud')
@section('badge', '✦ 403 XATOLIK • CHEKLANGAN HUQUQ')
@section('icon', '🛡️')

@section('message')
    Ushbu sahifa yoki boshqaruv paneliga kirish uchun sizning hisobingizda yetarli huquqlar mavjud emas. Bu bo'lim faqat O'qituvchilar yoki Ma'murlar (Admin) uchun ochiq.
@endsection

@section('actions')
    @auth
        <a href="{{ route('dashboard') }}" 
           class="px-7 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider transition-all duration-200 active:scale-95 shadow-lg shadow-amber-500/25 flex items-center gap-2">
            <span>Mening panelimga qaytish</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </a>
    @else
        <a href="{{ route('login') }}" 
           class="px-7 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider transition-all duration-200 active:scale-95 shadow-lg shadow-amber-500/25 flex items-center gap-2">
            <span>Hisobga kirish</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </a>
    @endauth

    <a href="{{ route('home') }}" 
       class="px-6 py-3.5 rounded-xl bg-ink-900 hover:bg-ink-800 text-slate-200 font-semibold text-xs border border-white/10 hover:border-amber-400/30 transition-all duration-200 active:scale-95">
        Bosh sahifa
    </a>
@endsection
