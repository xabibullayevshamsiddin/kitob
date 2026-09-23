@extends('layouts.app')

@section('title', 'Sozlamalar')

@section('content')
<div class="max-w-3xl mx-auto space-y-8 pb-16">
    <div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white font-manrope">Hisob sozlamalari</h1>
        <p class="text-xs text-slate-400 mt-1">Shaxsiy ma'lumotlaringiz va o'qish afzalliklaringizni boshqaring</p>
    </div>

    <!-- Profile Settings Card -->
    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-6">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3">Profil ma'lumotlari</h3>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">To'liq ism</label>
                <input type="text" value="{{ auth()->user()->name }}" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Username</label>
                <input type="text" value="{{ auth()->user()->username }}" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                <input type="email" value="{{ auth()->user()->email }}" disabled class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-500 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Bio</label>
                <textarea rows="3" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white">{{ auth()->user()->bio }}</textarea>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                O'zgarishlarni saqlash
            </button>
        </div>
    </div>
</div>
@endsection
