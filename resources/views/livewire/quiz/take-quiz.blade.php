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
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-indigo-500/15 text-indigo-300 border border-indigo-500/25">
                        Maks. {{ $maxRewardPoints }} ball
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
            <a href="{{ route('books.show', $book->slug) }}" 
               class="px-3.5 py-1.5 rounded-xl bg-white/[0.05] hover:bg-white/[0.1] border border-white/10 text-xs font-semibold text-slate-300 hover:text-white transition-all flex items-center gap-1">
                <span>← Chiqish</span>
            </a>
        </div>
    </div>

    @if($latestAttempt)
        <div class="rounded-2xl border {{ $latestAttempt->review_status === 'pending' ? 'border-amber-500/25 bg-amber-500/10 text-amber-200' : 'border-emerald-500/20 bg-emerald-500/5 text-slate-200' }} p-4 text-sm">
            @if($latestAttempt->review_status === 'pending')
                Yozma javoblaringiz ustoz/admin tekshiruvida. Natija tayyor bo‘lgach shu sahifada ko‘rasiz.
            @elseif($latestAttempt->review_status === 'reviewed')
                Oxirgi test natijasi: <strong>{{ (float) $latestAttempt->percent }}%</strong> · {{ $latestAttempt->score }} / {{ $latestAttempt->max_score }} ball.
            @endif
        </div>
    @endif

    {{-- ── Bo'sh holat: Test savollari mavjud emas ── --}}
    @if (count($questions) === 0)
        <div class="p-12 sm:p-16 rounded-3xl bg-slate-900/80 border border-white/10 text-center space-y-4 shadow-2xl backdrop-blur-md">
            <div class="w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-3xl mx-auto shadow-inner">
                🧠
            </div>
            <h2 class="text-lg font-bold text-white">
                Bu kitob uchun hozircha test savollari kiritilmagan
            </h2>
            <p class="text-xs sm:text-sm text-slate-400 max-w-md mx-auto leading-relaxed">
                Ushbu kitob bo'yicha savol-javoblar tez orada ustozlar tomonidan yuklanadi.
            </p>

            <div class="pt-4 flex flex-wrap items-center justify-center gap-3">
                @if(auth()->check() && $book->canUserAddQuiz(auth()->user()))
                    @php
                        $quizCreateRoute = auth()->user()->isAdmin()
                            ? route('admin.quizzes.create', ['book_id' => $book->id])
                            : route('teacher.quizzes.create', ['book_id' => $book->id]);
                    @endphp
                    <a href="{{ $quizCreateRoute }}" 
                       class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-lg shadow-indigo-600/30">
                        + Ushbu kitobga test qo'shish
                    </a>
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
                            <span class="font-mono text-[11px] font-bold"
                                  x-data="{
                                      remaining: {{ (int) ($timerRemainingSeconds ?? 0) }},
                                      display: '',
                                      done: false,
                                      start() {
                                          this.tick();
                                          this._timer = setInterval(() => this.tick(), 1000);
                                      },
                                      tick() {
                                          if (this.remaining <= 0) {
                                              clearInterval(this._timer);
                                              this.display = '0:00';
                                              if (!this.done) {
                                                  this.done = true;
                                                  $wire.timeUp();
                                              }
                                              return;
                                          }
                                          this.display = Math.floor(this.remaining / 60) + ':' + String(this.remaining % 60).padStart(2, '0');
                                          this.remaining--;
                                      }
                                  }"
                                  x-init="start()"
                                  :class="remaining <= 60 ? 'text-rose-400 animate-pulse' : 'text-slate-400'">
                                ⏱ <span x-text="display">…</span>
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-lg bg-amber-500/10 border border-amber-500/25 text-amber-400 font-bold font-mono">
                            Jami: {{ $maxRewardPoints }} Ball
                        </span>
                        @if($attemptCount > 0)
                            <span class="text-[11px] text-amber-400/80 font-semibold">
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
                    @if(!empty($questions[$currentQuestion]['image']))
                        @php
                            $imageShape = $questions[$currentQuestion]['image_shape'] ?? 'rectangle';
                            $imageShapeClass = match ($imageShape) {
                                'square' => 'mx-auto mt-4 h-56 w-56 sm:h-72 sm:w-72 rounded-md',
                                'circle' => 'mx-auto mt-4 h-56 w-56 sm:h-72 sm:w-72 rounded-full',
                                'rounded' => 'mt-4 max-h-[28rem] w-auto max-w-full rounded-2xl',
                                default => 'mt-4 max-h-[28rem] w-auto max-w-full rounded-md',
                            };
                        @endphp
                        <img src="{{ $questions[$currentQuestion]['image'] }}" alt="Savolga biriktirilgan rasm" class="{{ $imageShapeClass }} border border-white/10 object-contain">
                    @endif
                </div>

                {{-- Variantlar ro'yxati --}}
                @if(($questions[$currentQuestion]['type'] ?? 'single') === 'text')
                    <div class="space-y-2 pt-2">
                        <label for="writtenAnswer" class="block text-sm font-semibold text-slate-200">Javobingiz</label>
                        <textarea id="writtenAnswer" wire:model.defer="writtenAnswer" rows="6" maxlength="5000" placeholder="Javobingizni shu yerga yozing..." class="w-full rounded-2xl border border-white/10 bg-slate-800/70 p-4 text-sm text-white placeholder-slate-500 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20"></textarea>
                        @error('writtenAnswer') <p class="text-xs text-rose-400">{{ $message }}</p> @enderror
                        <p class="text-xs text-slate-500">Yozma javob test tugagach ustoz yoki admin tomonidan tekshiriladi.</p>
                    </div>
                @else
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
                @endif

                {{-- Navigation Buttons --}}
                <div class="grid grid-cols-2 items-center gap-3 border-t border-white/[0.06] pt-5">
                    <div class="col-span-2 text-center text-xs text-slate-400">
                        @if(($questions[$currentQuestion]['type'] ?? 'single') === 'text')
                            {{ trim($writtenAnswer) !== '' ? 'Yozgan javobingiz saqlanadi; keyin qaytib tahrirlashingiz mumkin.' : 'Javob bermasdan o‘tishingiz va keyin qaytishingiz mumkin.' }}
                        @elseif($selectedOption === null)
                            Javob bermasdan keyingi savolga o‘tishingiz mumkin.
                        @else
                            <span class="text-emerald-400 font-semibold">Variant tanlandi ✓</span>
                        @endif
                    </div>

                    <button type="button" wire:click="previousQuestion" @if($currentQuestion === 0) disabled @endif
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-white/10 px-4 py-2.5 text-xs font-bold text-slate-200 transition hover:bg-white/[0.06] disabled:cursor-not-allowed disabled:opacity-40">
                        <span aria-hidden="true">←</span> Oldingi
                    </button>

                    <button type="button"
                            @if($currentQuestion === count($questions) - 1)
                                wire:click="requestFinish"
                            @else
                                wire:click="nextQuestion"
                            @endif
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-ink-950 shadow-lg shadow-amber-500/20 transition hover:from-amber-400 hover:to-amber-500 active:scale-95">
                        <span>{{ $currentQuestion === count($questions) - 1 ? 'Testni yakunlash' : 'Keyingi savol' }}</span>
                        <span>→</span>
                    </button>
                </div>

            </div>
            @if($confirmFinishOpen && !$finished)
                <div
                     wire:click.self="cancelFinish"
                     class="fixed inset-0 z-[100] flex items-center justify-center bg-black/75 p-4 backdrop-blur-sm"
                     role="presentation">
                    <section role="dialog"
                             aria-modal="true"
                             aria-labelledby="quiz-finish-title"
                             class="w-full max-w-md space-y-5 rounded-2xl border border-amber-400/20 bg-slate-900 p-6 shadow-2xl shadow-black/50">
                        <div class="flex items-start gap-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-xl text-amber-400" aria-hidden="true">!</span>
                            <div class="space-y-2">
                                <h2 id="quiz-finish-title" class="text-lg font-bold text-white">Testni yakunlashga ishonchingiz komilmi?</h2>
                                <p class="text-sm leading-relaxed text-slate-400">Javoblaringiz tekshiriladi va test natijasi saqlanadi. Javobsiz savollar 0 ball bilan qayd etiladi.</p>
                            </div>
                        </div>
                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <button type="button"
                                    wire:click="cancelFinish"
                                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-white/10 px-5 py-2.5 text-sm font-semibold text-slate-200 transition hover:bg-white/[0.06]">
                                Testga qaytish
                            </button>
                            <button type="button"
                                    wire:click="confirmFinish"
                                    class="inline-flex min-h-11 items-center justify-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-ink-950 transition hover:bg-amber-400">
                                Ha, yakunlash
                            </button>
                        </div>
                    </section>
                </div>
            @endif
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
                        {{ $pendingReview ? 'Yozma javoblar tekshiruvda' : ($percent >= 80 ? 'Ajoyib natija! Barakalla!' : ($percent >= 50 ? 'Yaxshi natija!' : 'Yana harakat qiling!')) }}
                    </h2>
                    @if($pendingReview)
                        <p class="max-w-xl mx-auto rounded-2xl border border-amber-400/20 bg-amber-400/10 px-5 py-3 text-sm text-amber-200">Javoblaringiz saqlandi. Ustoz yoki admin tekshirganidan keyin yakuniy natija va ball hisobingizga qo‘shiladi.</p>
                    @endif
                    @if($timedOut)
                        <p class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-semibold">
                            <span>⏱</span>
                            <span>Vaqt tugadi — test avtomatik yakunlandi. Javoblanmagan savollarga 0 ball berildi.</span>
                        </p>
                    @endif
                    <p class="text-xs sm:text-sm text-slate-400 max-w-md mx-auto">
                        Siz «{{ $book->title }}» kitobi bo'yicha <strong>{{ count($questions) }} ta savoldan {{ $correctCount }} tasiga</strong> to'g'ri javob berdingiz.
                    </p>
                </div>

                {{-- Ballar bloki --}}
                @if(!$pendingReview)
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-xl mx-auto">
                    <div class="p-4 rounded-2xl bg-slate-800/80 border border-white/[0.08] text-center">
                        <span class="text-xs text-slate-400 block mb-1">To'g'ri javoblar</span>
                        <span class="text-2xl font-black text-white font-mono">{{ $correctCount }} <span class="text-xs text-slate-500 font-normal">/ {{ count($questions) }}</span></span>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-800/80 border border-white/[0.08] text-center">
                        <span class="text-xs text-slate-400 block mb-1">Natija foizi</span>
                        <span class="text-2xl font-black {{ $percent >= 70 ? 'text-emerald-400' : ($percent >= 50 ? 'text-amber-400' : 'text-rose-400') }} font-mono">
                            {{ round($percent) }}%
                        </span>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-800/80 border border-white/[0.08] text-center">
                        <span class="text-xs text-slate-400 block mb-1">Ushbu urinish uchun ball</span>
                        @if($alreadyHadFullPoints)
                            <span class="text-2xl font-black text-rose-400 font-mono">0 <span class="text-xs text-slate-500 font-normal">/ {{ $maxScore }}</span></span>
                            <span class="block text-[10px] text-rose-400/90 font-bold mt-0.5">Takroriy urinish (0 ball)</span>
                        @else
                            <span class="text-2xl font-black text-amber-400 font-mono">+{{ $pointsAwarded }} <span class="text-xs text-slate-500 font-normal">/ {{ $maxScore }}</span></span>
                            <span class="block text-[10px] text-emerald-400 font-bold mt-0.5">Hisobga qo'shildi</span>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Ball statusi xabari --}}
                <div class="text-center">
                    @if($pendingReview)
                        <div class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-amber-500/10 border border-amber-500/25 text-amber-200 text-xs font-semibold">Yakuniy ball ustoz tekshiruvidan keyin beriladi.</div>
                    @elseif ($pointsAwarded > 0)
                        <div class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs font-semibold shadow-lg shadow-emerald-500/10">
                            <span class="text-lg">⭐️</span>
                            <span>+{{ $pointsAwarded }} ball hisobingizga qo'shildi va tepadagi reytingingizda animatsiya bilan yangilandi!</span>
                        </div>
                    @elseif ($alreadyHadFullPoints)
                        <div class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-300 text-xs font-semibold shadow-lg shadow-amber-500/10">
                            <span class="text-lg">⚠️</span>
                            <span>Siz ushbu testdan avval ball olgansiz. Qoidaga ko'ra, takroriy urinishda reyting balli berilmaydi (0 ball).</span>
                        </div>
                    @else
                        <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-slate-800 border border-white/10 text-slate-400 text-xs">
                            <span>💡</span>
                            <span>Test savollariga to'g'ri javob berib, ball to'plashingiz mumkin.</span>
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
                                <div class="p-4 rounded-2xl border {{ ($fb['correct'] ?? null) === true ? 'bg-emerald-500/5 border-emerald-500/20' : 'bg-slate-500/5 border-white/10' }} space-y-2">
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="text-xs sm:text-sm font-bold text-white">
                                            {{ $idx + 1 }}. {{ $fb['question_text'] ?? "Savol #".($idx+1) }}
                                        </p>
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold shrink-0 {{ ($fb['correct'] ?? null) === true ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-500/20 text-slate-300' }}">
                                            {{ ($fb['type'] ?? '') === 'text' ? 'Tekshiruv kutilmoqda' : (($fb['correct'] ?? false) ? '✓ To\'g\'ri' : '✗ Xato') }}
                                        </span>
                                    </div>

                                    <div class="text-xs space-y-1 text-slate-400">
                                        @if(($fb['type'] ?? '') === 'text')
                                            <p class="text-slate-300 whitespace-pre-wrap">Javobingiz: {{ $fb['written_answer'] ?? 'Javob berilmadi' }}</p>
                                        @elseif(!empty($fb['selected_text']))
                                            <p>Sizning javobingiz: <strong class="{{ $fb['correct'] ? 'text-emerald-400' : 'text-rose-400' }}">{{ $fb['selected_text'] }}</strong></p>
                                        @endif
                                        @if(!($fb['correct'] ?? false) && !empty($fb['correct_text']))
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

    {{-- Confetti & Celebration Script --}}
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.addEventListener('points-awarded', (e) => {
                if (typeof confetti === 'function') {
                    confetti({
                        particleCount: 80,
                        spread: 70,
                        origin: { y: 0.6 },
                        colors: ['#f59e0b', '#10b981', '#6366f1', '#ec4899', '#ffffff']
                    });
                }
            });
        });
    </script>
</div>
