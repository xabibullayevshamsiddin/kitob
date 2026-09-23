@extends('layouts.app')

@section('title', 'Kitob bo\'yicha test - ' . $book->title)

@section('content')
<div class="max-w-3xl mx-auto space-y-8 pb-16" x-data="{
    currentQ: 0,
    selectedOption: null,
    score: 0,
    finished: false,
    questions: [
        {
            q: 'Odatlarni shakllantirish bo\'yicha 1-qoida qaysi?',
            options: [
                'Uni ko\'zga yaqqol tashlanadigan qiling (Make it obvious)',
                'Uni murakkab qiling',
                'Uni darhol unuting',
                'Hech kimga aytmang'
            ],
            correct: 0,
            explain: 'James Clear fikricha, birinchi qadam muhitni o\'zgartirish va kerakli ishora (cue) ni ko\'zga ko\'rinadigan qilishdir.'
        },
        {
            q: 'Har kuni 1% yaxshilanish bir yil oxirida qanday natija beradi?',
            options: [
                'Atigi 3.65 barobar',
                'Qariyb 37 barobar kuchliroq',
                'O\'zgarishsiz qoladi',
                '10 barobar'
            ],
            correct: 1,
            explain: '1.01 ning 365-darajasi taxminan 37.78 ga teng bo\'ladi.'
        }
    ],
    nextQuestion() {
        if (this.selectedOption === this.questions[this.currentQ].correct) {
            this.score += 10;
        }
        this.selectedOption = null;
        if (this.currentQ < this.questions.length - 1) {
            this.currentQ++;
        } else {
            this.finished = true;
        }
    }
}">

    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
        <div>
            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">Haftalik Sinov</span>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">«{{ $book->title }}» bo'yicha Test</h1>
        </div>
        <a href="{{ route('books.show', $book->slug) }}" class="text-xs text-slate-400 hover:text-slate-600">← Chiqish</a>
    </div>

    <!-- Active Quiz Card -->
    <div x-show="!finished" class="p-6 sm:p-10 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-6">
        <div class="flex items-center justify-between text-xs font-bold text-slate-400">
            <span>Savol <span x-text="currentQ + 1"></span> / <span x-text="questions.length"></span></span>
            <span class="text-amber-500 font-black">+10 Ball</span>
        </div>

        <h3 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white" x-text="questions[currentQ].q"></h3>

        <div class="space-y-3 pt-2">
            <template x-for="(opt, idx) in questions[currentQ].options" :key="idx">
                <button type="button" @click="selectedOption = idx"
                    class="w-full p-4 rounded-2xl border text-left text-xs sm:text-sm font-semibold transition-all flex items-center justify-between"
                    :class="selectedOption === idx ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700/80 text-slate-800 dark:text-slate-200 hover:border-slate-400'">
                    <span x-text="opt"></span>
                    <span class="w-5 h-5 rounded-full border flex items-center justify-center text-xs" :class="selectedOption === idx ? 'border-white bg-white text-indigo-600 font-black' : 'border-slate-400'">
                        <span x-show="selectedOption === idx">✓</span>
                    </span>
                </button>
            </template>
        </div>

        <div class="pt-4 flex justify-end">
            <button type="button" @click="nextQuestion()" :disabled="selectedOption === null"
                class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md transition-all disabled:opacity-50">
                <span x-text="currentQ === questions.length - 1 ? 'Testni yakunlash' : 'Keyingi savol →'"></span>
            </button>
        </div>
    </div>

    <!-- Quiz Result Card -->
    <div x-show="finished" class="p-8 sm:p-12 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft text-center space-y-6 animate-scale-in">
        <span class="text-6xl block">🎉</span>
        <h2 class="text-2xl font-black text-slate-900 dark:text-white">Ajoyib natija!</h2>
        <p class="text-sm text-slate-500">Siz testni muvaffaqiyatli yakunladingiz va profilingizga yangi ballar qo'shildi!</p>

        <div class="inline-flex items-center gap-4 px-6 py-3 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-500">
            <span class="text-2xl font-black">+<span x-text="score"></span> BALL</span>
            <span>⭐️</span>
        </div>

        <div class="pt-4">
            <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md transition-all inline-block">
                Bosh sahifaga qaytish →
            </a>
        </div>
    </div>
</div>
@endsection
