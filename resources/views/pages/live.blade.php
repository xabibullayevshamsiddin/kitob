@extends('layouts.app')

@section('title', 'Jonli efirlar')

@section('content')
<div class="max-w-5xl mx-auto space-y-8 pb-16">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-manrope">Muallif bilan Jonli Efir</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Har hafta oxirida kitob tahlili va jonli savol-javoblar</p>
        </div>
    </div>

    <!-- Live Stream Player / Banner -->
    <div class="p-8 sm:p-12 rounded-3xl bg-gradient-to-br from-rose-950 via-slate-900 to-slate-900 border border-rose-900/40 text-white shadow-2xl relative overflow-hidden text-center space-y-6">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rose-500/20 text-rose-400 text-xs font-bold uppercase tracking-wider">
            <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
            <span>Navbatdagi efir: Yakshanba, 20:00</span>
        </div>

        <h2 class="text-2xl sm:text-4xl font-black font-manrope max-w-2xl mx-auto leading-tight">
            «Atom Odatlar»: Kichik odatlar amaliyotda qanday ishlaydi?
        </h2>

        <p class="text-xs sm:text-sm text-slate-300 max-w-xl mx-auto leading-relaxed">
            Kitobdagi eng muhim savollarga muallif jonli javob beradi. Siz ham o'z savolingizni oldindan yuborishingiz mumkin!
        </p>

        <!-- Question submission box -->
        <div class="max-w-md mx-auto pt-4">
            <div class="flex gap-2">
                <input type="text" placeholder="Muallifga savolingizni yozing..." 
                    class="flex-1 px-4 py-2.5 bg-white/10 border border-white/20 rounded-xl text-xs text-white placeholder-slate-400 focus:ring-2 focus:ring-rose-500">
                <button class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                    Yuborish
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
