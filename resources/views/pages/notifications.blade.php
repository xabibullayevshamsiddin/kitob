@extends('layouts.app')

@section('title', 'Bildirishnomalar')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-16">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white font-manrope">Bildirishnomalar</h1>
            <p class="text-xs text-slate-400 mt-1">Platformadagi barcha faolliklar va yangiliklar</p>
        </div>
        <button class="text-xs text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Barchasini o'qilgan qilish</button>
    </div>

    <!-- Notification items list -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-soft divide-y divide-slate-100 dark:divide-slate-800">
        <div class="p-5 flex items-start gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
            <span class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-xl shrink-0">
                🔥
            </span>
            <div class="flex-1 min-w-0">
                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Streak xavf ostida!</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Bugun hali kamida 10 daqiqa kitob mutolaa qilmadingiz. Ketma-ketlikni saqlab qoling!</p>
                <span class="text-[10px] text-slate-400 block mt-1">1 soat oldin</span>
            </div>
            <span class="w-2 h-2 rounded-full bg-indigo-600 shrink-0 mt-2"></span>
        </div>

        <div class="p-5 flex items-start gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
            <span class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                📚
            </span>
            <div class="flex-1 min-w-0">
                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Yangi haftalik kitob e'lon qilindi!</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">«Atom Odatlar (Atomic Habits)» kitobi to'liq audio va video sharhlari bilan yuklandi.</p>
                <span class="text-[10px] text-slate-400 block mt-1">Bugun, 09:00</span>
            </div>
        </div>

        <div class="p-5 flex items-start gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
            <span class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-xl shrink-0">
                🎖️
            </span>
            <div class="flex-1 min-w-0">
                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Yangi nishon qo'lga kiritildi!</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Siz «Birinchi qadam» nishonini qo'lga kiritdingiz va +50 ball oldingiz!</p>
                <span class="text-[10px] text-slate-400 block mt-1">Kecha</span>
            </div>
        </div>
    </div>
</div>
@endsection
