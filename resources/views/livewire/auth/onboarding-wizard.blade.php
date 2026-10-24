<div>
    <!-- Stepper Indicator -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-amber-600 dark:text-amber-400 font-mono uppercase tracking-wider">Qadam {{ $currentStep }} / {{ $totalSteps }}</span>
            <span class="text-xs font-semibold text-mist">
                @if($currentStep === 1) O'qish joyi
                @elseif($currentStep === 2) Asosiy maqsad
                @else Profilni bezash @endif
            </span>
        </div>
        <div class="w-full bg-ink-800 rounded-full h-2 overflow-hidden border border-ink-border">
            <div class="bg-gradient-to-r from-amber-500 to-amber-400 h-2 rounded-full transition-all duration-500 ease-out"
                 style="width: {{ ($currentStep / $totalSteps) * 100 }}%"></div>
        </div>
    </div>

    <!-- Step 1: O'qish joyi -->
    @if ($currentStep === 1)
        <div class="animate-fade-in">
            <div class="text-center mb-6">
                <h2 class="text-xl font-extrabold font-serif text-paper tracking-tight">Kitoblarni ko'pincha qayerda o'qiysiz?</h2>
                <p class="text-xs text-mist mt-1">Bu sizga mos o'qish tartibini shakllantirishga yordam beradi</p>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-6">
                @php
                    $places = [
                        ['id' => 'home', 'label' => 'Uyda', 'icon' => '🏡', 'desc' => 'Tinch va osoyishta muhitda'],
                        ['id' => 'office', 'label' => 'Ishxonada', 'icon' => '💼', 'desc' => 'Tanaffus va bo\'sh vaqtda'],
                        ['id' => 'university', 'label' => 'Universitetda', 'icon' => '🎓', 'desc' => 'Dars oralig\'ida'],
                        ['id' => 'library', 'label' => 'Kutubxonada', 'icon' => '📚', 'desc' => 'Fokuslangan ilmiy muhitda'],
                        ['id' => 'travel', 'label' => 'Yo\'lda', 'icon' => '🎧', 'desc' => 'Metro, transport yoki mashinada'],
                        ['id' => 'other', 'label' => 'Boshqa joyda', 'icon' => '✨', 'desc' => 'Har xil qulay joylarda'],
                    ];
                @endphp

                @foreach ($places as $place)
                    <button type="button" wire:click="selectPlace('{{ $place['id'] }}')"
                        class="p-4 rounded-2xl border text-left transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] {{ $reading_place === $place['id'] ? 'bg-amber-500/15 border-amber-500 ring-1 ring-amber-500' : 'bg-ink-950 border-ink-border hover:border-amber-500/40' }}">
                        <span class="text-2xl block mb-2">{{ $place['icon'] }}</span>
                        <span class="text-sm font-bold text-paper block leading-tight">{{ $place['label'] }}</span>
                        <span class="text-[11px] text-mist block mt-1 leading-snug">{{ $place['desc'] }}</span>
                    </button>
                @endforeach
            </div>

            @error('reading_place')
                <p class="text-rose-500 dark:text-rose-400 text-xs text-center mb-4">{{ $error }}</p>
            @enderror
        </div>
    @endif

    <!-- Step 2: O'qishdan maqsad -->
    @if ($currentStep === 2)
        <div class="animate-fade-in">
            <div class="text-center mb-6">
                <h2 class="text-xl font-extrabold font-serif text-paper tracking-tight">Kitob o'qishdan asosiy maqsadingiz nima?</h2>
                <p class="text-xs text-mist mt-1">Maqsadingizga qarab shaxsiy kitoblar tavsiya qilinadi</p>
            </div>

            <div class="space-y-2.5 mb-6">
                @php
                    $goals = [
                        ['id' => 'knowledge', 'label' => 'Yangi bilim va dunyoqarash', 'icon' => '💡', 'desc' => 'Ilm-fan va falsafiy g\'oyalar'],
                        ['id' => 'personal_dev', 'label' => 'Shaxsiy rivojlanish', 'icon' => '🚀', 'desc' => 'Vaqtni boshqarish, odatlar, intizom'],
                        ['id' => 'career', 'label' => 'Kasbiy va biznes rivojlanish', 'icon' => '📈', 'desc' => 'Daromad, liderlik, kasbiy mahorat'],
                        ['id' => 'language', 'label' => 'Til o\'rganish va so\'z boyligi', 'icon' => '🗣️', 'desc' => 'Nutq va so\'zlashuv qobiliyati'],
                        ['id' => 'exam_prep', 'label' => 'Imtihonga tayyorlanish', 'icon' => '🎯', 'desc' => 'Akademik natijalar uchun'],
                        ['id' => 'spiritual', 'label' => 'Ruhiy xotirjamlik', 'icon' => '🌿', 'desc' => 'Stressdan xalos bo\'lish va tafakkur'],
                        ['id' => 'other', 'label' => 'Boshqa maqsad', 'icon' => '🌟', 'desc' => 'O\'z qiziqishlarim bo\'yicha'],
                    ];
                @endphp

                @foreach ($goals as $goal)
                    <button type="button" wire:click="selectGoal('{{ $goal['id'] }}')"
                        class="w-full p-3.5 rounded-2xl border text-left flex items-center justify-between transition-all duration-200 hover:scale-[1.01] active:scale-[0.99] {{ $reading_goal === $goal['id'] ? 'bg-amber-500/15 border-amber-500' : 'bg-ink-950 border-ink-border hover:border-amber-500/40' }}">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">{{ $goal['icon'] }}</span>
                            <div>
                                <span class="text-sm font-bold text-paper block">{{ $goal['label'] }}</span>
                                <span class="text-[11px] text-mist block">{{ $goal['desc'] }}</span>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-mist" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                @endforeach
            </div>

            <div class="flex justify-between items-center">
                <button type="button" wire:click="prevStep" class="text-xs text-mist hover:text-paper font-mono">← Ortga</button>
            </div>
        </div>
    @endif

    <!-- Step 3: Profil va yakunlash -->
    @if ($currentStep === 3)
        <div class="animate-fade-in">
            <div class="text-center mb-6">
                <div class="inline-flex p-3 rounded-2xl bg-amber-500/10 text-amber-500 text-2xl mb-2">🎁</div>
                <h2 class="text-xl font-extrabold font-serif text-paper tracking-tight">Deyarli tayyor!</h2>
                <p class="text-xs text-mist mt-1">Yakunlang va darhol <strong class="text-amber-600 dark:text-amber-400 font-bold">+50 xush kelibsiz balliga</strong> ega bo'ling!</p>
            </div>

            <div class="space-y-4 mb-6">
                <!-- Avatar Upload -->
                <div>
                    <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-2">Profil suratingiz (ixtiyoriy)</label>
                    <div class="flex items-center gap-4">
                        <div class="relative w-16 h-16 rounded-2xl overflow-hidden bg-ink-950 border border-ink-border flex items-center justify-center shrink-0">
                            @if ($avatar)
                                <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-2xl">👤</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <input type="file" wire:model="avatar" accept="image/*" class="text-xs text-mist file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-500 file:text-ink-950 hover:file:bg-amber-400 file:cursor-pointer">
                            <span wire:loading wire:target="avatar" class="text-[11px] text-amber-600 dark:text-amber-400 block mt-1">Rasm yuklanmoqda...</span>
                            @error('avatar') <p class="text-rose-500 dark:text-rose-400 text-xs mt-1">{{ $error }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Bio -->
                <div>
                    <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-1.5">O'zingiz haqingizda qisqacha</label>
                    <textarea wire:model.defer="bio" rows="3" placeholder="Sevimli kitoblaringiz yoki kasbingiz haqida 1-2 jumla..."
                        class="w-full px-4 py-2.5 bg-ink-950 border border-ink-border text-paper rounded-xl focus:ring-2 focus:ring-amber-500 text-sm placeholder-mist resize-none"></textarea>
                    @error('bio') <p class="text-rose-500 dark:text-rose-400 text-xs mt-1">{{ $error }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-between gap-4">
                <button type="button" wire:click="prevStep" class="text-xs text-mist hover:text-paper font-mono">← Ortga</button>
                <button type="button" wire:click="finish"
                    class="ks-btn-primary px-6 py-3 font-bold rounded-xl text-sm shadow-lg transition-all transform active:scale-95 flex items-center gap-2">
                    <span>Platformaga kirish</span>
                    <span>🎉</span>
                </button>
            </div>
        </div>
    @endif
</div>
