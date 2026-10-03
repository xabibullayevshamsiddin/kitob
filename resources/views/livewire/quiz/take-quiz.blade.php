<div class="max-w-4xl mx-auto space-y-6 pb-20">

    {{-- ── Top Navigation & Book Info ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl bg-slate-900/60 border border-white/[0.08] backdrop-blur-md">
        <div class="flex items-center gap-3.5">
            <a href="{{ route('books.show', $book->slug) }}" class="shrink-0 group">
                <img src="{{ $book->cover_url }}" 
                     alt="{{ $book->title }}" 
                     class="w-12 h-16 object-cover rounded-lg shadow-md border border-white/10 group-hover:scale-105 transition-transform"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($book->title) }}&size=200&background=1e1b4b&color=fff&bold=true'">
            </a>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-amber-500/15 text-amber-400 border border-amber-500/25">
                        {{ $book->week_number ? "{$book->week_number}-hafta" : 'Kitob Testi' }}
                    </span>
                    @if($quizDifficulty)
                        @php
                            $diffLabels = [
                                'easy'   => ['label' => 'Oson',   'class' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/25'],
                                'medium' => ['label' => 'O\'rta',  'class' => 'bg-amber-500/15 text-amber-400 border-amber-500/25'],
                                'hard'   => ['label' => 'Qiyin',  'class' => 'bg-rose-500/15 text-rose-400 border-rose-500/25'],
                            ];
                            $dc = $diffLabels[$quizDifficulty] ?? ['label' => $quizDifficulty, 'class' => 'bg-slate-800 text-slate-300 border-white/10'];
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $dc['class'] }}">
                            {{ $dc['label'] }}
                        </span>
                    @endif
                </div>
                <h1 class="text-base sm:text-lg font-bold text-white leading-tight">
                    «{{ $book->title }}» — <span class="text-amber-400">{{ $quizTitle ?? 'Bilim Sinovi' }}</span>
                </h1>
                <p class="text-xs text-slate-400">{{ $book->author }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-center">
            @if(auth()->check() && auth()->user()->isAdminOrTeacher())
                <a href="{{ route('teacher.quizzes.create', ['book_id' => $book->id]) }}" 
                   class="px-3 py-1.5 rounded-xl bg-indigo-600/20 hover:bg-indigo-600/30 border border-indigo-500/30 text-indigo-300 text-xs font-semibold transition-colors flex items-center gap-1.5"
                   title="Ushbu kitobga yangi test qo'shish">
                    <span>+ Yangi test qo'shish</span>
                </a>
            @endif
            <a href="{{ route('books.show', $book->slug) }}" 
               class="px-3.5 py-1.5 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] border border-white/10 text-xs font-semibold text-slate-300 hover:text-white transition-all flex items-center gap-1">
                <span>← Chiqish</span>
            </a>
        </div>
    </div>

    {{-- ── Agar bir nechta test mavjud bo'lsa: Test Tanlash Tabilari ── --}}
    @if($allQuizzes->count() > 1)
        <div class="p-3 rounded-2xl bg-slate-900/40 border border-white/[0.06] flex items-center gap-2 overflow-x-auto no-scrollbar">
            <span class="text-xs text-slate-400 font-semibold pl-2 shrink-0">Barcha testlar:</span>
            <div class="flex items-center gap-2">
                @foreach($allQuizzes as $qItem)
                    <button type="button" 
                            wire:click="selectQuiz({{ $qItem->id }})"
                            class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all shrink-0 flex items-center gap-2 border {{ (int)$quizId === (int)$qItem->id ? 'bg-amber-500 text-ink-950 font-bold border-amber-400 shadow-lg shadow-amber-500/20' : 'bg-slate-800/80 hover:bg-slate-800 text-slate-300 border-white/[0.08]' }}">
                        <span>🎯 {{ $qItem->title }}</span>
                        <span class="px-1.5 py-0.2 rounded-md text-[10px] {{ (int)$quizId === (int)$qItem->id ? 'bg-ink-950/20 text-ink-950' : 'bg-slate-700 text-slate-300' }}">
                            {{ $qItem->questions_count }} savol
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Bo'sh holat: Test savollari mavjud emas ── --}}
    @if (count($questions) === 0)
        <div class="p-12 sm:p-16 rounded-3xl bg-slate-900/80 border border-white/10 text-center space-y-4 shadow-2xl backdrop-blur-md">
            <div class="w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-3xl mx-auto shadow-inner">
                🧠
            </div>
            <h2 class="text-lg font-bold text-white">
                @if($quizTitle)
                    «{{ $quizTitle }}» uchun hozircha savollar kiritilmagan
                @else
                    Bu kitob uchun hozircha test savollari yuklanmagan
                @endif
            </h2>
            <p class="text-xs sm:text-sm text-slate-400 max-w-md mx-auto leading-relaxed">
                Ushbu kitob yoki test bo'yicha savol-javoblar tez orada kiritiladi. O'qituvchilar yoki admin yangi savollar yuklashi mumkin.
            </p>

            <div class="pt-4 flex flex-wrap items-center justify-center gap-3">
                @if(auth()->check() && auth()->user()->isAdminOrTeacher())
                    @if($quizId)
                        <a href="{{ route('teacher.quizzes.show', $quizId) }}" 
                           class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-lg shadow-indigo-600/30">
                            ✍️ Ushbu testga savol qo'shish
                        </a>
                    @else
                        <a href="{{ route('teacher.quizzes.create', ['book_id' => $book->id]) }}" 
                           class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-lg shadow-indigo-600/30">
                            + Yangi test yaratish
                        </a>
                    @endif
                @endif

                <a href="{{ route('books.show', $book->slug) }}" 
                   class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs border border-white/10 transition-colors">
                    📖 Kitob sahifasiga qaytish
                </a>
            </div>
        </div>

    @else

        {{-- ── 1. ACTIVE QUIZ CARD (SAVOL TOPSHIRISH QISMI) ── --}}
        @if (!$finished)
            <div class="p-6 sm:p-10 rounded-3xl bg-slate-900/90 border border-white/[0.08] shadow-2xl backdrop-blur-md space-y-6 relative overflow-hidden">
                {{-- Progress Bar --}}
                <div class="absolute top-0 inset-x-0 h-1.5 bg-slate-800">
                    @php
                        $progressPercent = count($questions) > 0 ? (($currentQuestion + 1) / count($questions)) * 100 : 0;
                    @endphp
                    <div class="h-full bg-gradient-to-r from-amber-500 to-indigo-500 transition-all duration-300 ease-out" 
                         style="width: {{ $progressPercent }}%;"></div>
                </div>

                {{-- Header info --}}
                <div class="flex items-center justify-between text-xs font-semibold text-slate-400 border-b border-white/[0.06] pb-4">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-lg bg-slate-800 border border-white/10 text-white font-mono font-bold">
                            Savol {{ $currentQuestion + 1 }} / {{ count($questions) }}
                        </span>
                        @if($timeLimitMinutes)
                            <span class="text-slate-400 font-mono text-[11px]">
                                ⏱ {{ $timeLimitMinutes }} daqiqa
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-lg bg-amber-500/10 border border-amber-500/25 text-amber-400 font-bold font-mono">
                            +{{ $questions[$currentQuestion]['points'] ?? 10 }} Ball
                        </span>
                        @if($attemptCount > 0)
                            <span class="text-[11px] text-slate-500">
                                ({{ $attemptCount }}-urinish)
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Savol matni --}}
                <div class="py-2">
                    <h2 class="text-lg sm:text-xl font-bold text-white leading-relaxed">
                        {{ $questions[$currentQuestion]['text'] }}
                    </h2>
                </div>

                {{-- Variantlar ro'yxati --}}
                <div class="space-y-3 pt-2">
                    @php
                        $optionLetters = ['A', 'B', 'C', 'D', 'E', 'F'];
                    @endphp

                    @foreach ($questions[$currentQuestion]['options'] as $idx => $opt)
                        @php
                            $isSelected = $selectedOption !== null && (int)$selectedOption === (int)$opt['id'];
                            $letter = $optionLetters[$idx] ?? ($idx + 1);
                        @endphp
                        <button type="button" 
                                wire:click="$set('selectedOption', {{ $opt['id'] }})"
                                class="w-full p-4 rounded-2xl border text-left text-xs sm:text-sm font-medium transition-all flex items-center justify-between gap-4 group cursor-pointer {{ $isSelected ? 'bg-gradient-to-r from-amber-500/20 to-indigo-600/20 border-amber-400 text-white shadow-lg shadow-amber-500/10' : 'bg-slate-800/60 hover:bg-slate-800 border-white/[0.08] text-slate-300 hover:border-slate-600' }}">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-xl flex items-center justify-center font-mono font-bold text-xs shrink-0 transition-colors {{ $isSelected ? 'bg-amber-400 text-ink-950 shadow-md' : 'bg-slate-700/80 text-slate-300 group-hover:bg-slate-600' }}">
                                    {{ $letter }}
                                </span>
                                <span class="leading-relaxed {{ $isSelected ? 'font-bold text-white' : '' }}">
                                    {{ $opt['text'] }}
                                </span>
                            </div>

                            <span class="w-6 h-6 rounded-full border flex items-center justify-center text-xs shrink-0 transition-all {{ $isSelected ? 'border-amber-400 bg-amber-400 text-ink-950 font-black scale-110' : 'border-slate-600 text-transparent' }}">
                                ✓
                            </span>
                        </button>
                    @endforeach
                </div>

                {{-- Izoh yoki yordamchi maslahat (agar feedback mavjud bo'lsa) --}}
                @if (isset($feedback[$currentQuestion]) && $feedback[$currentQuestion]['explanation'])
                    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-300 leading-relaxed flex items-start gap-2.5">
                        <span class="text-base shrink-0">💡</span>
                        <span>{{ $feedback[$currentQuestion]['explanation'] }}</span>
                    </div>
                @endif

                {{-- Navigation Buttons --}}
                <div class="pt-6 flex items-center justify-between border-t border-white/[0.06]">
                    <div class="text-xs text-slate-500">
                        @if($selectedOption === null)
                            <span class="text-amber-400/80">Javob variantini tanlang</span>
                        @else
                            <span class="text-emerald-400 font-semibold">Variant tanlandi ✓</span>
                        @endif
                    </div>

                    <button type="button" 
                            wire:click="nextQuestion" 
                            @if($selectedOption === null) disabled @endif
                            class="px-6 py-3 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-ink-950 font-bold text-xs sm:text-sm uppercase tracking-wider rounded-xl shadow-lg shadow-amber-500/20 transition-all disabled:opacity-40 disabled:cursor-not-allowed active:scale-95 flex items-center gap-2">
                        <span>{{ $currentQuestion === count($questions) - 1 ? 'Testni yakunlash' : 'Keyingi savol' }}</span>
                        <span>→</span>
                    </button>
                </div>
            </div>
        @endif

        {{-- ── 2. QUIZ RESULT CARD (NATIJALAR VA TAHLIL) ── --}}
        @if ($finished)
            <div class="p-8 sm:p-12 rounded-3xl bg-slate-900/90 border border-white/[0.08] shadow-2xl backdrop-blur-md space-y-8 animate-scale-in">
                
                {{-- Natija Header --}}
                <div class="text-center space-y-3">
                    <span class="text-6xl inline-block animate-bounce">
                        {{ $percent >= 80 ? '🎉' : ($percent >= 50 ? '👏' : '💪') }}
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black text-white">
                        {{ $percent >= 80 ? 'Ajoyib natija! Barakalla!' : ($percent >= 50 ? 'Yaxshi natija!' : 'Yana harakat qiling!') }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-400 max-w-md mx-auto">
                        Siz «{{ $book->title }}» kitobi bo'yicha <strong>{{ count($questions) }} ta savolga</strong> javob berdingiz.
                    </p>
                </div>

                {{-- Ballar bloki --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-xl mx-auto">
                    <div class="p-4 rounded-2xl bg-slate-800/80 border border-white/[0.08] text-center">
                        <span class="text-xs text-slate-400 block mb-1">To'plangan ball</span>
                        <span class="text-2xl font-black text-white font-mono">{{ $score }} <span class="text-xs text-slate-500 font-normal">/ {{ $maxScore }}</span></span>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-800/80 border border-white/[0.08] text-center">
                        <span class="text-xs text-slate-400 block mb-1">Aniqlik darajasi</span>
                        <span class="text-2xl font-black {{ $percent >= 70 ? 'text-emerald-400' : ($percent >= 50 ? 'text-amber-400' : 'text-rose-400') }} font-mono">
                            {{ round($percent) }}%
                        </span>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-800/80 border border-white/[0.08] text-center">
                        <span class="text-xs text-slate-400 block mb-1">Qo'shilgan ball</span>
                        <span class="text-2xl font-black text-amber-400 font-mono">+{{ $pointsAwarded }}</span>
                    </div>
                </div>

                {{-- Ball statusi xabari --}}
                <div class="text-center">
                    @if ($pointsAwarded > 0)
                        <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 text-xs font-semibold">
                            <span>⭐️</span>
                            <span>{{ $pointsAwarded }} ball profilingizga qo'shildi va peshqadamlar reytingida yangilandi!</span>
                        </div>
                    @elseif ($alreadyHadFullPoints)
                        <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-amber-500/10 border border-amber-500/25 text-amber-400 text-xs font-semibold">
                            <span>ℹ️</span>
                            <span>Siz bu testdan avval to'liq ball olgansiz — takroriy urinishlar uchun ball hisoblanmaydi.</span>
                        </div>
                    @else
                        <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-slate-800 border border-white/10 text-slate-400 text-xs">
                            <span>💡</span>
                            <span>Kitob boblarini qaytadan mutolaa qilib, testni takror topshirishingiz mumkin.</span>
                        </div>
                    @endif
                </div>

                {{-- Savollar tahlili (Review) --}}
                @if(count($feedback) > 0)
                    <div class="space-y-4 pt-4 border-t border-white/[0.08]">
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <span>📋</span> Savollar tahlili va to'g'ri javoblar
                        </h3>

                        <div class="space-y-3">
                            @foreach($feedback as $idx => $fb)
                                <div class="p-4 rounded-2xl border {{ $fb['correct'] ? 'bg-emerald-500/5 border-emerald-500/20' : 'bg-rose-500/5 border-rose-500/20' }} space-y-2">
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="text-xs sm:text-sm font-bold text-white">
                                            {{ $idx + 1 }}. {{ $fb['question_text'] ?? "Savol #".($idx+1) }}
                                        </p>
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold shrink-0 {{ $fb['correct'] ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                                            {{ $fb['correct'] ? '✓ To\'g\'ri' : '✗ Xato' }}
                                        </span>
                                    </div>

                                    <div class="text-xs space-y-1 text-slate-400">
                                        @if($fb['selected_text'])
                                            <p>Sizning javobingiz: <strong class="{{ $fb['correct'] ? 'text-emerald-400' : 'text-rose-400' }}">{{ $fb['selected_text'] }}</strong></p>
                                        @endif
                                        @if(!$fb['correct'] && $fb['correct_text'])
                                            <p>To'g'ri javob: <strong class="text-emerald-400">{{ $fb['correct_text'] }}</strong></p>
                                        @endif
                                        @if(!empty($fb['explanation']))
                                            <p class="text-amber-300/90 pt-1 text-[11px] leading-relaxed">
                                                💡 {{ $fb['explanation'] }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Action Tugmalari --}}
                <div class="pt-6 flex flex-wrap items-center justify-center gap-3 border-t border-white/[0.08]">
                    <button type="button" 
                            wire:click="restartQuiz" 
                            class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider transition-all shadow-md active:scale-95">
                        🔄 Qaytadan topshirish
                    </button>

                    <a href="{{ route('books.show', $book->slug) }}" 
                       class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs border border-white/10 transition-colors">
                        📖 Kitobga qaytish
                    </a>

                    <a href="{{ route('leaderboard') }}" 
                       class="px-5 py-2.5 rounded-xl bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 font-semibold text-xs transition-colors">
                        🏆 Peshqadamlar reytingi
                    </a>
                </div>

            </div>
        @endif

    @endif

    {{-- Reading Tracker & AFK Modal --}}
    @include('components.reading-tracker', [
        'bookId' => $book->id,
        'chapterId' => null,
        'pageType' => 'quiz'
    ])

</div>
