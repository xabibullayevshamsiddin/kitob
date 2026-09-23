<div class="max-w-3xl mx-auto space-y-8 pb-16">

    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
        <div>
            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">Haftalik Sinov</span>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">«{{ $book->title }}» bo'yicha Test</h1>
        </div>
        <a href="{{ route('books.show', $book->slug) }}" class="text-xs text-slate-400 hover:text-slate-600">← Chiqish</a>
    </div>

    @if (count($questions) === 0)
        <div class="p-10 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft text-center text-sm text-slate-400">
            Bu test uchun savollar hozircha mavjud emas.
        </div>
    @else

    <!-- Active Quiz Card -->
    <div x-show="!finished" class="p-6 sm:p-10 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-6">
        <div class="flex items-center justify-between text-xs font-bold text-slate-400">
            <span>Savol <span class="text-slate-900 dark:text-white">{{ $currentQuestion + 1 }}</span> / {{ count($questions) }}</span>
            @if (isset($feedback[$currentQuestion]))
                <span class="{{ $feedback[$currentQuestion]['correct'] ? 'text-emerald-500' : 'text-rose-500' }}">
                    {{ $feedback[$currentQuestion]['correct'] ? '✓ To\'g\'ri' : '✗ Xato' }}
                </span>
            @else
                <span class="text-amber-500 font-black">Har to'g'ri javob — ball!</span>
            @endif
        </div>

        <h3 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">{{ $questions[$currentQuestion]['text'] }}</h3>

        <div class="space-y-3 pt-2">
            @foreach ($questions[$currentQuestion]['options'] as $opt)
                <button type="button" wire:click="$set('selectedOption', {{ $opt['id'] }})"
                    class="w-full p-4 rounded-2xl border text-left text-xs sm:text-sm font-semibold transition-all flex items-center justify-between
                        {{ $selectedOption === $opt['id']
                            ? 'bg-indigo-600 text-white border-indigo-600 shadow-md'
                            : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700/80 text-slate-800 dark:text-slate-200 hover:border-slate-400' }}">
                    <span>{{ $opt['text'] }}</span>
                    <span class="w-5 h-5 rounded-full border flex items-center justify-center text-xs {{ $selectedOption === $opt['id'] ? 'border-white bg-white text-indigo-600 font-black' : 'border-slate-400' }}">
                        @if($selectedOption === $opt['id'])✓@endif
                    </span>
                </button>
            @endforeach
        </div>

        @if (isset($feedback[$currentQuestion]) && $feedback[$currentQuestion]['explanation'])
            <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
                💡 {{ $feedback[$currentQuestion]['explanation'] }}
            </div>
        @endif

        <div class="pt-4 flex justify-end">
            <button type="button" wire:click="nextQuestion" @disabled($selectedOption === null)
                class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md transition-all disabled:opacity-50">
                {{ $currentQuestion === count($questions) - 1 ? 'Testni yakunlash' : 'Keyingi savol →' }}
            </button>
        </div>
    </div>

    <!-- Quiz Result Card -->
    @if ($finished)
    <div class="p-8 sm:p-12 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft text-center space-y-6 animate-scale-in">
        <span class="text-6xl block">{{ $percent >= 60 ? '🎉' : '💪' }}</span>
        <h2 class="text-2xl font-black text-slate-900 dark:text-white">
            {{ $percent >= 60 ? 'Ajoyib natija!' : 'Yana harakat qiling!' }}
        </h2>

        @php
            $percent = $maxScore > 0 ? round(($score / $maxScore) * 100) : 0;
        @endphp

        <p class="text-sm text-slate-500">
            Siz {{ count($questions) }} ta savolga javob berdingiz va
            <strong class="text-slate-900 dark:text-white">{{ $score }} / {{ $maxScore }}</strong> ball to'pladingiz
            ({{ $percent }}%).
        </p>

        @if ($pointsAwarded > 0)
            <div class="inline-flex items-center gap-4 px-6 py-3 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-500">
                <span class="text-2xl font-black">+{{ $pointsAwarded }} BALL</span>
                <span>⭐️</span>
            </div>
            <p class="text-xs text-slate-400">Ballar hisobingizga qo'shildi va reytingda darhol aks etadi!</p>
        @elseif ($score > 0 && $alreadyHadFullPoints)
            <div class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-500 text-xs font-semibold">
                Bu testdan avval to'liq ball olgansiz — takroriy urinishlar uchun ball qo'shilmaydi.
            </div>
        @else
            <div class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-500 text-xs font-semibold">
                Bu urinishda ball qo'shilmadi — kitobni o'qib, qaytadan urinib ko'ring!
            </div>
        @endif

        <div class="pt-4 flex items-center justify-center gap-3 flex-wrap">
            <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md transition-all inline-block">
                Bosh sahifaga qaytish →
            </a>
            <a href="{{ route('leaderboard') }}" class="px-6 py-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs sm:text-sm rounded-xl transition-all inline-block">
                🏆 Reytingni ko'rish
            </a>
        </div>
    </div>
    @endif

    @endif
</div>
