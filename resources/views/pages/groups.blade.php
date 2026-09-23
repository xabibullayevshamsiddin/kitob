@extends('layouts.app')

@section('title', 'Kitobxon Guruhlari')

@section('content')
<div class="max-w-6xl mx-auto space-y-8 pb-16">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-manrope">Kitobxon Guruhlari</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Do'stlaringiz yoki hamfikrlaringiz bilan birga o'qing</p>
        </div>
        <button class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center gap-2">
            <span>+ Yangi guruh ochish</span>
        </button>
    </div>

    <!-- Groups Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-2xl font-bold">
                    🚀
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">«Atom Odatlar» Challengers</h3>
                    <span class="text-[11px] text-slate-400">24 ta a'zo • Ommaviy</span>
                </div>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Haftalik kitobni birga o'qib, kundalik natijalarini bo'lishuvchilar klubi.
            </p>
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                <span class="text-xs text-indigo-600 font-bold">Faol muloqot</span>
                <button class="px-4 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold rounded-xl hover:bg-indigo-600 hover:text-white transition-all">
                    Qo'shilish
                </button>
            </div>
        </div>

        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center text-2xl font-bold">
                    💡
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Biznes va Liderlik</h3>
                    <span class="text-[11px] text-slate-400">18 ta a'zo • Ommaviy</span>
                </div>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Karyera va shaxsiy loyihalarda kitob bilimlarini amalda qo'llash jamoasi.
            </p>
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                <span class="text-xs text-indigo-600 font-bold">Faol muloqot</span>
                <button class="px-4 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold rounded-xl hover:bg-indigo-600 hover:text-white transition-all">
                    Qo'shilish
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
