<div>
    @if($isEdit)
        <div class="mb-4">
            <a href="{{ route('settings') }}" class="inline-flex items-center gap-1.5 text-xs text-mist hover:text-amber-400 font-mono transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Sozlamalarga qaytish</span>
            </a>
        </div>
    @endif

    <!-- Stepper Indicator -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-amber-500 font-mono uppercase tracking-wider">
                Qadam {{ $currentStep }} / {{ $totalSteps }}
            </span>
            <span class="text-xs font-semibold text-mist">
                @if($currentStep === 1) O'qish joyi
                @elseif($currentStep === 2) Asosiy maqsad
                @elseif($currentStep === 3) Mutolaa tartibi va janrlar
                @else Profil ma'lumotlari @endif
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
                <h2 class="text-xl sm:text-2xl font-bold font-serif text-paper tracking-tight">Kitoblarni ko'pincha qayerda o'qiysiz?</h2>
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
                        class="p-4 rounded-2xl border text-left transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] {{ $reading_place === $place['id'] ? 'bg-amber-500/15 border-amber-500 text-paper ring-1 ring-amber-500/50 shadow-sm' : 'bg-ink-950 border-ink-border text-paper hover:border-amber-500/40' }}">
                        <span class="text-2xl block mb-2">{{ $place['icon'] }}</span>
                        <span class="text-sm font-bold text-paper block leading-tight">{{ $place['label'] }}</span>
                        <span class="text-[11px] text-mist block mt-1 leading-snug">{{ $place['desc'] }}</span>
                    </button>
                @endforeach
            </div>

            @error('reading_place')
                <p class="text-rose-400 text-xs text-center mb-4">{{ $message }}</p>
            @enderror

            @if($reading_place)
                <div class="flex justify-end pt-2 border-t border-ink-border">
                    <button type="button" wire:click="nextStep" class="ks-btn-primary py-2 px-5 text-xs font-mono font-bold">Keyingi qadam →</button>
                </div>
            @endif
        </div>
    @endif

    <!-- Step 2: O'qishdan maqsad -->
    @if ($currentStep === 2)
        <div class="animate-fade-in">
            <div class="text-center mb-6">
                <h2 class="text-xl sm:text-2xl font-bold font-serif text-paper tracking-tight">Kitob o'qishdan asosiy maqsadingiz nima?</h2>
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
                        class="w-full p-3.5 rounded-2xl border text-left flex items-center justify-between transition-all duration-200 hover:scale-[1.01] active:scale-[0.99] {{ $reading_goal === $goal['id'] ? 'bg-amber-500/15 border-amber-500 text-paper ring-1 ring-amber-500/50 shadow-sm' : 'bg-ink-950 border-ink-border text-paper hover:border-amber-500/40' }}">
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

            @error('reading_goal')
                <p class="text-rose-400 text-xs text-center mb-4">{{ $message }}</p>
            @enderror

            <div class="flex justify-between items-center pt-2 border-t border-ink-border">
                <button type="button" wire:click="prevStep" class="ks-btn-ghost py-2 px-4 text-xs font-mono">← Ortga</button>
                @if($reading_goal)
                    <button type="button" wire:click="nextStep" class="ks-btn-primary py-2 px-5 text-xs font-mono font-bold">Keyingi qadam →</button>
                @endif
            </div>
        </div>
    @endif

    <!-- Step 3: Mutolaa rejasi va janrlar -->
    @if ($currentStep === 3)
        <div class="animate-fade-in space-y-6">
            <div class="text-center">
                <h2 class="text-xl sm:text-2xl font-bold font-serif text-paper tracking-tight">Mutolaa tartibi va qiziqishlaringiz</h2>
                <p class="text-xs text-mist mt-1">Kundalik me'yor, asosiy til va sevimli kitob janrlaringizni tanlang</p>
            </div>

            <!-- 1. Kunlik mutolaa me'yori -->
            <div class="space-y-2">
                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-amber-500">
                    ⏱️ Kunlik mutolaa me'yori: <span class="text-paper">{{ $daily_reading_minutes }} daqiqa</span>
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    @php
                        $minuteOptions = [
                            ['min' => 15, 'label' => '15 daq', 'desc' => 'Boshlovchi', 'icon' => '⚡'],
                            ['min' => 30, 'label' => '30 daq', 'desc' => 'Oltin me\'yor', 'icon' => '🎯'],
                            ['min' => 45, 'label' => '45 daq', 'desc' => 'Faol kitobxon', 'icon' => '🔥'],
                            ['min' => 60, 'label' => '60 daq', 'desc' => 'Intensiv', 'icon' => '👑'],
                        ];
                    @endphp
                    @foreach($minuteOptions as $opt)
                        <button type="button" wire:click="setMinutes({{ $opt['min'] }})"
                            class="p-3 rounded-xl border text-center transition-all duration-150 {{ $daily_reading_minutes === $opt['min'] ? 'bg-amber-500/20 border-amber-500 text-paper ring-1 ring-amber-500/50 shadow-sm' : 'bg-ink-950 border-ink-border text-paper hover:border-amber-500/40' }}">
                            <span class="text-base block mb-1">{{ $opt['icon'] }}</span>
                            <span class="text-xs font-bold block">{{ $opt['label'] }}</span>
                            <span class="text-[10px] text-mist block">{{ $opt['desc'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 2. Asosiy mutolaa tili -->
            <div class="space-y-2">
                <label class="block text-xs font-mono font-bold uppercase tracking-wider text-amber-500">
                    🌐 Asosiy mutolaa tili
                </label>
                <div class="grid grid-cols-3 gap-2.5">
                    @php
                        $locales = [
                            ['code' => 'uz', 'label' => 'O‘zbekcha', 'flag' => '🇺🇿'],
                            ['code' => 'ru', 'label' => 'Русский', 'flag' => '🇷🇺'],
                            ['code' => 'en', 'label' => 'English', 'flag' => '🇬🇧'],
                        ];
                    @endphp
                    @foreach($locales as $loc)
                        <button type="button" wire:click="setLocale('{{ $loc['code'] }}')"
                            class="p-2.5 rounded-xl border text-center transition-all duration-150 {{ $preferred_locale === $loc['code'] ? 'bg-amber-500/20 border-amber-500 text-paper ring-1 ring-amber-500/50 shadow-sm' : 'bg-ink-950 border-ink-border text-paper hover:border-amber-500/40' }}">
                            <span class="text-base mr-1">{{ $loc['flag'] }}</span>
                            <span class="text-xs font-bold">{{ $loc['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 3. Sevimli janrlar -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-mono font-bold uppercase tracking-wider text-amber-500">
                        📚 Sevimli janrlaringiz
                    </label>
                    <span class="text-[11px] font-mono text-mist">{{ count($favorite_genres) }}/5 tanlandi</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @php
                        $genresList = [
                            ['id' => 'Badiiy adabiyot', 'label' => 'Badiiy adabiyot', 'icon' => '📖'],
                            ['id' => 'Shaxsiy rivojlanish', 'label' => 'Shaxsiy rivojlanish', 'icon' => '🚀'],
                            ['id' => 'Biznes va moliya', 'label' => 'Biznes va moliya', 'icon' => '💼'],
                            ['id' => 'Psixologiya', 'label' => 'Psixologiya', 'icon' => '🧠'],
                            ['id' => 'Ilm-fan va texnologiya', 'label' => 'Ilm-fan va IT', 'icon' => '🔬'],
                            ['id' => 'Tarix va biografiya', 'label' => 'Tarix va biografiya', 'icon' => '🏛️'],
                            ['id' => 'Falsafa va ma\'naviyat', 'label' => 'Falsafa va tafakkur', 'icon' => '🌿'],
                            ['id' => 'Detektiv va triller', 'label' => 'Detektiv va triller', 'icon' => '🕵️'],
                            ['id' => 'Xorijiy tillar', 'label' => 'Til o‘rganish', 'icon' => '🗣️'],
                        ];
                    @endphp
                    @foreach($genresList as $g)
                        @php
                            $isSelected = in_array($g['id'], $favorite_genres, true);
                        @endphp
                        <button type="button" wire:click="toggleGenre('{{ $g['id'] }}')"
                            class="p-2.5 rounded-xl border text-left flex items-center gap-2 transition-all duration-150 {{ $isSelected ? 'bg-amber-500/20 border-amber-500 text-paper ring-1 ring-amber-500/50 shadow-sm' : 'bg-ink-950 border-ink-border text-paper hover:border-amber-500/40' }}">
                            <span class="text-base shrink-0">{{ $g['icon'] }}</span>
                            <span class="text-xs font-semibold truncate">{{ $g['label'] }}</span>
                            @if($isSelected)
                                <span class="ml-auto text-amber-400 font-bold text-xs shrink-0">✓</span>
                            @endif
                        </button>
                    @endforeach
                </div>
                @error('daily_reading_minutes') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                @error('preferred_locale') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-between items-center pt-2 border-t border-ink-border">
                <button type="button" wire:click="prevStep" class="ks-btn-ghost py-2 px-4 text-xs font-mono">← Ortga</button>
                <button type="button" wire:click="saveStepThree" class="ks-btn-primary py-2 px-5 text-xs font-mono font-bold">Keyingi qadam →</button>
            </div>
        </div>
    @endif

    <!-- Step 4: Profil va yakunlash -->
    @if ($currentStep === 4)
        <div class="animate-fade-in">
            <div class="text-center mb-6">
                <div class="inline-flex p-3 rounded-2xl bg-amber-500/10 text-amber-500 text-2xl mb-2">
                    {{ $isEdit ? '✨' : '🎁' }}
                </div>
                <h2 class="text-xl sm:text-2xl font-bold font-serif text-paper tracking-tight">
                    {{ $isEdit ? 'Profil ma\'lumotlari' : 'Deyarli tayyor!' }}
                </h2>
                <p class="text-xs text-mist mt-1">
                    @if($isEdit)
                        O'zingiz haqingizda qisqacha ma'lumot va profil rasmingizni yangilang
                    @else
                        Yakunlang va darhol <strong class="text-amber-500 font-bold">+50 xush kelibsiz balliga</strong> ega bo'ling!
                    @endif
                </p>
            </div>

            <div class="space-y-4 mb-6">
                <!-- Avatar Upload -->
                <div>
                    <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-2">Profil suratingiz (ixtiyoriy)</label>
                    <div class="flex items-center gap-4">
                        <div class="relative w-16 h-16 rounded-2xl overflow-hidden bg-ink-950 border border-ink-border flex items-center justify-center shrink-0">
                            @if ($avatar)
                                <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
                            @elseif(auth()->user()?->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-2xl">👤</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" wire:model="avatar" accept="image/*" class="w-full text-xs text-mist file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-500 file:text-ink-950 hover:file:bg-amber-400 file:cursor-pointer">
                            <span wire:loading wire:target="avatar" class="text-[11px] text-amber-500 block mt-1 font-mono">Rasm yuklanmoqda...</span>
                            @error('avatar') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Bio -->
                <div>
                    <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-1.5">O'zingiz haqingizda qisqacha</label>
                    <textarea wire:model.defer="bio" rows="3" placeholder="Sevimli kitoblaringiz yoki kasbingiz haqida 1-2 jumla..."
                        class="ks-input w-full px-4 py-2.5 rounded-xl text-sm placeholder-mist resize-none focus:ring-2 focus:ring-amber-500"
                        style="background-color: var(--ink-950, #07090E); color: var(--paper, #F0EDE6); border: 1px solid var(--ink-border, #1F293D);"></textarea>
                    @error('bio') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-between gap-4 pt-2 border-t border-ink-border">
                <button type="button" wire:click="prevStep" class="ks-btn-ghost py-2.5 px-4 text-xs font-mono">← Ortga</button>
                <button type="button" wire:click="finish"
                    class="ks-btn-primary py-2.5 px-6 font-bold rounded-xl text-xs sm:text-sm shadow-lg transition-all transform active:scale-95 flex items-center gap-2">
                    <span>{{ $isEdit ? 'Rejani saqlash' : 'Platformaga kirish' }}</span>
                    <span>{{ $isEdit ? '💾' : '🎉' }}</span>
                </button>
            </div>
        </div>
    @endif
</div>
