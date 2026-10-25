<div class="max-w-3xl mx-auto space-y-6 pb-16">

    <div>
        <h1 class="text-2xl sm:text-3xl font-bold font-serif text-paper">{{ __('site.settings.title') }}</h1>
        <p class="text-xs font-mono text-mist mt-1">{{ __('site.settings.subtitle') }}</p>
    </div>

    <!-- ── 1. Asosiy Profil Ma'lumotlari Card ── -->
    <div class="p-6 sm:p-7 rounded-panel bg-ink-900 border border-ink-border shadow-soft space-y-5">
        <h3 class="text-sm font-bold text-paper font-serif border-b border-ink-border pb-3">{{ __('site.settings.profile_info') }}</h3>

        <!-- Avatar Preview & Upload -->
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-panel overflow-hidden border-2 border-amber-400/40 shrink-0 bg-ink-950">
                @if ($avatar)
                    <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
                @else
                    <img src="{{ auth()->user()->avatar_url }}" class="w-full h-full object-cover">
                @endif
            </div>
            <div class="flex-1">
                <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-1.5">{{ __('site.settings.avatar') }}</label>
                <input type="file" wire:model="avatar" accept="image/*"
                    class="text-xs font-mono text-mist file:mr-3 file:py-1.5 file:px-3 file:rounded-btn file:border-0 file:text-xs file:font-mono file:font-semibold file:bg-amber-400 file:text-ink-950 hover:file:bg-amber-300 file:cursor-pointer">
                <span wire:loading wire:target="avatar" class="text-[11px] font-mono text-amber-400 block mt-1">{{ __('site.settings.uploading') }}</span>
                @error('avatar') <p class="text-rose-400 font-mono text-xs mt-1">{{ $message }}</p> @enderror
                @if (auth()->user()->avatar)
                    <div class="mt-2">
                        <button type="button" wire:click="removeAvatar" wire:target="removeAvatar" wire:loading.attr="disabled"
                            wire:confirm="{{ __('site.settings.remove_avatar_confirm') }}"
                            class="inline-flex items-center gap-1.5 text-[11px] font-mono text-rose-400 hover:text-rose-300 transition-colors">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            {{ __('site.settings.remove_avatar') }}
                        </button>
                        <span wire:loading wire:target="removeAvatar" class="text-[11px] font-mono text-mist block mt-1">{{ __('site.settings.removing') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-4 pt-1">
            <!-- To'liq ism -->
            <div>
                <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-1">{{ __('site.settings.full_name') }}</label>
                <input type="text" wire:model.defer="name"
                    class="w-full px-3.5 py-2.5 bg-ink-950/80 border border-ink-border rounded-btn text-xs sm:text-sm text-paper focus:border-amber-400 focus:outline-none">
                @error('name') <p class="text-rose-400 font-mono text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Username -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-mono uppercase tracking-wider text-mist">{{ __('site.settings.username') }}</label>
                    <span class="text-[10px] text-amber-400/90 font-mono">Takrorlanmas (Unique)</span>
                </div>
                <div class="relative flex items-center w-full rounded-btn bg-ink-950/80 border border-ink-border focus-within:border-amber-400 focus-within:ring-1 focus-within:ring-amber-400 transition-colors {{ $errors->has('username') ? 'ks-input-invalid' : '' }}">
                    <div class="flex items-center pl-3.5 pr-2.5 border-r border-ink-border/60 select-none pointer-events-none text-mist font-mono text-sm font-semibold">
                        @
                    </div>
                    <input type="text" wire:model.lazy="username"
                        class="w-full bg-transparent border-0 py-2.5 pl-3 pr-3.5 text-xs sm:text-sm text-paper font-mono focus:ring-0 focus:outline-none placeholder:text-mist/40"
                        placeholder="foydalanuvchi_nomi">
                </div>
                @error('username')
                    <p class="text-rose-400 font-mono text-[11px] mt-1.5 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
                <p class="text-[11px] text-mist/70 font-mono mt-1">Foydalanuvchi nomi har bir profil uchun yagona bo'lishi shart.</p>
            </div>

            <!-- Elektron pochta (Gmail — Erkin tahrirlanadi) -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-mono uppercase tracking-wider text-mist">{{ __('site.settings.email') }} (Gmail)</label>
                    <span class="text-[10px] text-emerald-400 font-mono bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-500/20">Erkin o'zgartirish</span>
                </div>
                <div class="relative flex items-center w-full rounded-btn bg-ink-950/80 border border-ink-border focus-within:border-amber-400 focus-within:ring-1 focus-within:ring-amber-400 transition-colors {{ $errors->has('email') ? 'ks-input-invalid' : '' }}">
                    <div class="flex items-center pl-3.5 pr-2.5 border-r border-ink-border/60 select-none pointer-events-none text-mist shrink-0">
                        <svg class="w-4 h-4 text-mist" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input type="email" wire:model.defer="email"
                        class="w-full bg-transparent border-0 py-2.5 pl-3 pr-3.5 text-xs sm:text-sm text-paper font-sans focus:ring-0 focus:outline-none placeholder:text-mist/40"
                        placeholder="ali@gmail.com">
                </div>
                @error('email')
                    <p class="text-rose-400 font-mono text-[11px] mt-1.5 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
                <p class="text-[11px] text-mist/70 font-mono mt-1">Elektron pochtangizni xohlagan paytda erkin o'zgartirishingiz mumkin.</p>
            </div>

            <!-- Telefon raqami (Xavfsizlik shartlari bilan o'zgartiriladi) -->
            <div class="p-3.5 rounded-xl bg-ink-950/60 border border-ink-border space-y-2">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <label class="block text-xs font-mono uppercase tracking-wider text-mist">
                        Telefon raqam <span class="text-amber-400 font-bold">(Asosiy identifikator)</span>
                    </label>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        @if($hasVerifiedPhone)
                            <span class="text-[10px] font-mono font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-500/20">
                                ✅ Tasdiqlangan
                            </span>
                        @endif
                        @if($isTelegramLinked)
                            <span class="text-[10px] font-mono font-bold text-sky-400 bg-sky-500/10 px-2 py-0.5 rounded-full border border-sky-500/20">
                                🤖 Telegram ulangan
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-ink-800 border border-ink-border flex items-center justify-center text-amber-400 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div>
                            <p class="font-mono text-sm font-semibold text-paper">
                                {{ $phone ?: 'Telefon raqami kiritilmagan' }}
                            </p>
                            <p class="text-[10px] font-mono text-mist">Platformaga kirish va 2FA xavfsizlik raqamingiz</p>
                        </div>
                    </div>

                    <button type="button" wire:click="openPhoneModal"
                        class="px-3 py-1.5 bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 rounded-btn text-xs font-mono font-semibold transition-all inline-flex items-center gap-1.5 shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>{{ $phone ? "Raqamni o'zgartirish" : "Raqam qo'shish" }}</span>
                    </button>
                </div>
            </div>

            <!-- Bio -->
            <div>
                <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-1">{{ __('site.settings.bio') }}</label>
                <textarea rows="3" wire:model.defer="bio" placeholder="{{ __('site.settings.bio_placeholder') }}"
                    class="w-full px-3.5 py-2.5 bg-ink-950/80 border border-ink-border rounded-btn text-xs sm:text-sm text-paper focus:border-amber-400 focus:outline-none resize-none font-sans"></textarea>
                @error('bio') <p class="text-rose-400 font-mono text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-ink-border">
            <span wire:loading wire:target="save" class="text-xs font-mono text-mist">{{ __('site.settings.saving') }}</span>
            <button wire:click="save" wire:loading.attr="disabled"
                class="ks-btn-primary text-xs py-2.5 px-5">
                {{ __('site.settings.save') }}
            </button>
        </div>
    </div>

    <!-- ── 1.5. Shaxsiy Mutolaa Rejasi va Onboarding Sozlamalari ── -->
    @php
        $userProfile = auth()->user()->profile;
        $userPriv = is_array($userProfile?->privacy_settings) ? $userProfile->privacy_settings : [];
        $dailyMins = $userPriv['daily_reading_minutes'] ?? 30;
        $favGenresCount = count($userPriv['favorite_genres'] ?? []);
        $prefLoc = strtoupper($userPriv['preferred_locale'] ?? app()->getLocale());
    @endphp
    <div class="p-6 sm:p-7 rounded-panel bg-ink-900 border border-ink-border shadow-soft flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="space-y-1.5">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-lg">🧭</span>
                <h3 class="text-sm font-bold text-paper font-serif">
                    @if(app()->getLocale() === 'ru')
                        Персональный план чтения (Onboarding)
                    @elseif(app()->getLocale() === 'en')
                        Personal Reading Plan (Onboarding)
                    @else
                        Shaxsiy mutolaa rejasi (Onboarding)
                    @endif
                </h3>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                    {{ $dailyMins }} min/kun • {{ $prefLoc }}
                </span>
            </div>
            <p class="text-xs text-mist leading-relaxed">
                @if(app()->getLocale() === 'ru')
                    Настройте языки чтения, любимые жанры ({{ $favGenresCount }}), дневную норму, тему ридера и цели обучения.
                @elseif(app()->getLocale() === 'en')
                    Customize your reading languages, favorite genres ({{ $favGenresCount }}), daily target, reader theme, and goals.
                @else
                    Mutolaa tillari, sevimli janrlar ({{ $favGenresCount }} ta), kunlik me'yor, mutolaa rejimi va maqsadlaringizni qayta sozlang.
                @endif
            </p>
        </div>
        <a href="{{ route('onboarding', ['edit' => 1]) }}"
           class="px-4 py-2.5 rounded-btn bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-mono font-bold transition-all inline-flex items-center gap-2 shrink-0">
            <span>✨</span>
            <span>
                @if(app()->getLocale() === 'ru')
                    Настроить план →
                @elseif(app()->getLocale() === 'en')
                    Customize Plan →
                @else
                    Rejani sozlash →
                @endif
            </span>
        </a>
    </div>

    <!-- ── 2. Telefon Raqamini O'zgartirish Xavfsizlik Modali ── -->
    @if($showPhoneModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink-950/80 backdrop-blur-sm animate-fade-in"
             x-data @keydown.escape.window="$wire.closePhoneModal()">
            <div class="w-full max-w-md bg-ink-900 border border-ink-border rounded-panel p-6 shadow-2xl space-y-4 relative">
                
                <!-- Modal Sarlavhasi -->
                <div class="flex items-center justify-between border-b border-ink-border pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold font-serif text-paper">Telefon raqamini o'zgartirish</h3>
                            <p class="text-[10px] font-mono text-mist">Xavfsizlik shartlari asosida tasdiqlash</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closePhoneModal" class="text-mist hover:text-paper transition-colors p-1 text-lg leading-none">
                        &times;
                    </button>
                </div>

                @if(!$codeSent)
                    <!-- 1-BOSQICH: Yangi raqam va Joriy parol -->
                    <div class="space-y-3.5">
                        <div class="p-3 rounded-lg bg-amber-500/10 border border-amber-500/25 text-amber-300 text-[11px] leading-relaxed">
                            <p class="font-bold mb-0.5">🔒 Xavfsizlik talablari:</p>
                            <p>1. Hisobingizni begona shaxslar o'g'irlamasligi uchun <b>amaldagi parolingizni</b> kiritishingiz shart.</p>
                            @if($isTelegramLinked)
                                <p class="mt-1">2. Yangi raqam kiritilgach, hisobingizga ulangan <b>Telegram botga 6 xonali tasdiqlash kodi</b> yuboriladi.</p>
                            @endif
                        </div>

                        <!-- Yangi telefon raqami -->
                        <div>
                            <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-1">
                                Yangi telefon raqami <span class="text-rose-400">*</span>
                            </label>
                            <div class="relative flex items-center w-full rounded-btn bg-ink-950 border border-ink-border px-3 py-2">
                                <span class="font-mono text-xs font-semibold text-paper shrink-0 pr-2 border-r border-ink-border">+998</span>
                                <input type="tel" wire:model.defer="newPhone" autofocus
                                    class="w-full bg-transparent border-0 p-0 pl-2 text-xs sm:text-sm text-paper font-mono focus:ring-0 focus:outline-none"
                                    placeholder="90 123 45 67">
                            </div>
                            @error('newPhone') <p class="text-rose-400 font-mono text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Amaldagi parol -->
                        <div>
                            <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-1">
                                Amaldagi parolingiz <span class="text-rose-400">*</span>
                            </label>
                            <input type="password" wire:model.defer="currentPassword"
                                class="w-full px-3 py-2 bg-ink-950 border border-ink-border rounded-btn text-xs sm:text-sm text-paper focus:border-amber-400 focus:outline-none"
                                placeholder="Joriy parolingizni kiriting">
                            @error('currentPassword') <p class="text-rose-400 font-mono text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Tugmalar -->
                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-ink-border">
                            <button type="button" wire:click="closePhoneModal" class="ks-btn-ghost text-xs py-2 px-3.5">
                                Bekor qilish
                            </button>
                            <button type="button" wire:click="requestPhoneChange" wire:loading.attr="disabled"
                                class="ks-btn-primary text-xs py-2 px-4 flex items-center gap-1.5">
                                <span wire:loading wire:target="requestPhoneChange">Tekshirilmoqda...</span>
                                <span wire:loading.remove wire:target="requestPhoneChange">
                                    {{ $isTelegramLinked ? "Kodni Telegramga yuborish →" : "Raqamni saqlash" }}
                                </span>
                            </button>
                        </div>
                    </div>
                @else
                    <!-- 2-BOSQICH: Telegram tasdiqlash kodi -->
                    <div class="space-y-3.5">
                        <div class="p-3 rounded-lg bg-sky-500/10 border border-sky-500/25 text-sky-300 text-xs text-center space-y-1">
                            <p class="font-bold">📱 Telegram botga kod yuborildi!</p>
                            <p class="text-[11px] text-sky-200/80">
                                Telegram botingizga (<b>@{{ $telegramUsername ?: config('telegram.bot_username') }}</b>) 6 xonali maxsus tasdiqlash kodi yuborildi. Kodni quyida kiriting:
                            </p>
                        </div>

                        <div>
                            <label class="block text-center text-xs font-mono uppercase tracking-wider text-mist mb-1.5">
                                6 xonali tasdiqlash kodi
                            </label>
                            <input type="text" wire:model.defer="verificationCode" autofocus maxlength="6"
                                class="w-full text-center tracking-[0.4em] font-mono text-xl font-bold py-2.5 px-3 bg-ink-950 border border-ink-border rounded-btn text-paper focus:border-amber-400 focus:outline-none"
                                placeholder="••••••">
                            @error('verificationCode') <p class="text-rose-400 text-center font-mono text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Tugmalar (1 daqiqalik qulf bilan) -->
                        <div class="flex items-center justify-between gap-2 pt-2 border-t border-ink-border"
                             x-data="{
                                 cooldown: @entangle('resendCooldown').defer,
                                 timer: null,
                                 startTimer(sec) {
                                     this.cooldown = sec || 60;
                                     if (this.timer) clearInterval(this.timer);
                                     this.timer = setInterval(() => {
                                         if (this.cooldown > 0) {
                                             this.cooldown--;
                                         } else {
                                             clearInterval(this.timer);
                                         }
                                     }, 1000);
                                 },
                                 init() {
                                     if (!this.cooldown || this.cooldown <= 0) {
                                         this.cooldown = 60;
                                     }
                                     this.startTimer(this.cooldown);
                                 }
                             }"
                             @phone-code-sent.window="startTimer($event.detail.cooldown || 60)">
                            <button type="button"
                                    wire:click="requestPhoneChange"
                                    :disabled="cooldown > 0"
                                    :class="cooldown > 0
                                        ? 'text-mist/50 cursor-not-allowed pointer-events-none select-none'
                                        : 'text-amber-400 hover:underline cursor-pointer'"
                                    class="text-xs font-mono transition-colors inline-flex items-center gap-1">
                                <template x-if="cooldown > 0">
                                    <span>🔒 Qayta yuborish (<span x-text="cooldown"></span>s)</span>
                                </template>
                                <template x-if="cooldown <= 0">
                                    <span>Kod kelmadimi? Qayta yuborish ↻</span>
                                </template>
                            </button>
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="closePhoneModal" class="ks-btn-ghost text-xs py-2 px-3">
                                    Bekor qilish
                                </button>
                                <button type="button" wire:click="confirmPhoneChange" wire:loading.attr="disabled"
                                    class="ks-btn-primary text-xs py-2 px-4">
                                    <span wire:loading wire:target="confirmPhoneChange">Tasdiqlanmoqda...</span>
                                    <span wire:loading.remove wire:target="confirmPhoneChange">Tasdiqlash & Saqlash</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    @endif

    <!-- ── 3. Telegram Integratsiyasi Card ── -->
    <div class="p-6 sm:p-7 rounded-panel bg-ink-900 border border-ink-border shadow-soft space-y-4">
        <div class="flex items-center justify-between border-b border-ink-border pb-3 flex-wrap gap-2">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-btn bg-sky-500/10 border border-sky-500/20 text-sky-400 flex items-center justify-center">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.121l-6.871 4.326-2.962-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.196 1.006.128.832.926z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold font-serif text-paper">Telegram hisobi</h3>
                    <p class="text-[11px] font-mono text-mist">Tezkor kirish, 2FA kodlari va muhim bildirishnomalar</p>
                </div>
            </div>

            @if(auth()->user()->isTelegramLinked())
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-mono font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/25">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    Ulangan
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-mono font-semibold bg-mist/10 text-mist border border-ink-border">
                    Ulanmagan
                </span>
            @endif
        </div>

        @if(auth()->user()->isTelegramLinked())
            <div class="p-3.5 rounded-xl bg-ink-950/60 border border-ink-border flex items-center justify-between flex-wrap gap-3">
                <div class="space-y-0.5">
                    <p class="text-xs font-bold text-paper flex items-center gap-1.5">
                        <span>{{ auth()->user()->telegram_username ? '@' . auth()->user()->telegram_username : 'Telegram foydalanuvchisi' }}</span>
                        @if(auth()->user()->isTelegramBlocked())
                            <span class="text-[10px] text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded-full border border-rose-500/20">Bot bloklangan</span>
                        @endif
                    </p>
                    <p class="text-[10px] text-mist font-mono">ID: {{ auth()->user()->telegram_id }} • Ulandi: {{ auth()->user()->telegram_linked_at?->format('d.m.Y') }}</p>
                </div>

                <form method="POST" action="{{ route('auth.telegram.unlink') }}"
                      data-confirm="Haqiqatan ham Telegram hisobingizni uzmoqchimisiz? 2FA kirish to'xtatiladi."
                      data-confirm-title="Telegram hisobni uzish"
                      data-confirm-type="danger"
                      data-confirm-btn="Ha, uzish">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded-btn text-xs font-mono font-semibold transition-all">
                        Telegramni uzish
                    </button>
                </form>
            </div>

            <!-- Bildirishnoma sozlamalari -->
            <form method="POST" action="{{ route('settings.telegram.notifications') }}" class="pt-2 space-y-3">
                @csrf
                <input type="hidden" name="form_submitted" value="1">
                <div class="border-t border-ink-border pt-3">
                    <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                        <div>
                            <h4 class="text-xs font-mono uppercase tracking-wider text-mist">Telegram bildirishnomalarini boshqarish</h4>
                            <p class="text-[11px] text-mist/70 mt-0.5">Qaysi xabarlar Telegram bot orqali kelishini o'zingiz tanlang:</p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" wire:click="toggleTelegramPref('all_on')"
                                class="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/25 text-[11px] font-mono transition-colors">
                                🔔 Barchasini yoqish
                            </button>
                            <button type="button" wire:click="toggleTelegramPref('all_off')"
                                class="px-2.5 py-1 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/25 text-[11px] font-mono transition-colors">
                                🔕 Barchasini o'chirish
                            </button>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="flex items-start gap-3 p-2.5 rounded-xl bg-ink-950/40 border border-ink-border cursor-pointer hover:bg-ink-950/80 transition-colors">
                            <input type="checkbox" name="live_stream" value="1"
                                wire:change="toggleTelegramPref('live_stream')"
                                {{ auth()->user()->isTelegramNotificationEnabled('live_stream') ? 'checked' : '' }}
                                class="mt-0.5 w-4 h-4 rounded bg-ink-900 border-ink-border text-amber-500 focus:ring-amber-500">
                            <div class="space-y-0.5">
                                <p class="text-xs font-semibold text-paper">🔴 Obuna bo'lganlarim strim boshlaganda</p>
                                <p class="text-[11px] text-mist">Siz obuna bo'lgan ustoz yoki kitobxon jonli efir (strim) boshlagan zahoti Telegram orqali darhol xabar keladi.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-2.5 rounded-xl bg-ink-950/40 border border-ink-border cursor-pointer hover:bg-ink-950/80 transition-colors">
                            <input type="checkbox" name="ban_status" value="1"
                                wire:change="toggleTelegramPref('ban_status')"
                                {{ auth()->user()->isTelegramNotificationEnabled('ban_status') ? 'checked' : '' }}
                                class="mt-0.5 w-4 h-4 rounded bg-ink-900 border-ink-border text-amber-500 focus:ring-amber-500">
                            <div class="space-y-0.5">
                                <p class="text-xs font-semibold text-paper">🛡️ Hisob holati (Ban berilganda va Ban yechilganda)</p>
                                <p class="text-[11px] text-mist">Hisobingizga cheklov (ban) qo'yilganda yoki ban muddati tugab/yechilgan zahoti darhol Telegramga xabar keladi.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-2.5 rounded-xl bg-ink-950/40 border border-ink-border cursor-pointer hover:bg-ink-950/80 transition-colors">
                            <input type="checkbox" name="new_follower" value="1"
                                wire:change="toggleTelegramPref('new_follower')"
                                {{ auth()->user()->isTelegramNotificationEnabled('new_follower') ? 'checked' : '' }}
                                class="mt-0.5 w-4 h-4 rounded bg-ink-900 border-ink-border text-amber-500 focus:ring-amber-500">
                            <div class="space-y-0.5">
                                <p class="text-xs font-semibold text-paper">👥 Yangi obunachilar</p>
                                <p class="text-[11px] text-mist">Kimdir sizning profilingizga obuna bo'lganda bot orqali xabardor qilinadi.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-2.5 rounded-xl bg-ink-950/40 border border-ink-border cursor-pointer hover:bg-ink-950/80 transition-colors">
                            <input type="checkbox" name="streak_reminder" value="1"
                                wire:change="toggleTelegramPref('streak_reminder')"
                                {{ auth()->user()->isTelegramNotificationEnabled('streak_reminder') ? 'checked' : '' }}
                                class="mt-0.5 w-4 h-4 rounded bg-ink-900 border-ink-border text-amber-500 focus:ring-amber-500">
                            <div class="space-y-0.5">
                                <p class="text-xs font-semibold text-paper">🔥 Streak xavf ostida eslatmasi</p>
                                <p class="text-[11px] text-mist">Kechki 20:30 da bugun hali mutolaa qilmagan bo'lsangiz, streakni saqlab qolish uchun bot eslatadi.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-2.5 rounded-xl bg-ink-950/40 border border-ink-border cursor-pointer hover:bg-ink-950/80 transition-colors">
                            <input type="checkbox" name="daily_quote" value="1"
                                wire:change="toggleTelegramPref('daily_quote')"
                                {{ auth()->user()->isTelegramNotificationEnabled('daily_quote') ? 'checked' : '' }}
                                class="mt-0.5 w-4 h-4 rounded bg-ink-900 border-ink-border text-amber-500 focus:ring-amber-500">
                            <div class="space-y-0.5">
                                <p class="text-xs font-semibold text-paper">✨ Kun hikmati</p>
                                <p class="text-[11px] text-mist">Har kuni ertalab 09:00 da kitoblardan sara iqtiboslarni bot orqali qabul qilish.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-2.5 rounded-xl bg-ink-950/40 border border-ink-border cursor-pointer hover:bg-ink-950/80 transition-colors">
                            <input type="checkbox" name="coin_reward" value="1"
                                wire:change="toggleTelegramPref('coin_reward')"
                                {{ auth()->user()->isTelegramNotificationEnabled('coin_reward') ? 'checked' : '' }}
                                class="mt-0.5 w-4 h-4 rounded bg-ink-900 border-ink-border text-amber-500 focus:ring-amber-500">
                            <div class="space-y-0.5">
                                <p class="text-xs font-semibold text-paper">🪙 Tangalar va yutuqlar</p>
                                <p class="text-[11px] text-mist">Testlarni topshirganda tanga va yutuqlar haqida bildirishnoma.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-2.5 rounded-xl bg-ink-950/40 border border-ink-border cursor-pointer hover:bg-ink-950/80 transition-colors">
                            <input type="checkbox" name="quiet_hours" value="1"
                                wire:change="toggleTelegramPref('quiet_hours')"
                                {{ auth()->user()->isTelegramNotificationEnabled('quiet_hours') ? 'checked' : '' }}
                                class="mt-0.5 w-4 h-4 rounded bg-ink-900 border-ink-border text-amber-500 focus:ring-amber-500">
                            <div class="space-y-0.5">
                                <p class="text-xs font-semibold text-paper">🌙 Tungi jimjitlik rejimi (23:00 – 07:00)</p>
                                <p class="text-[11px] text-mist">Tungi vaqtda odatiy bildirishnomalarni yubormaslik (shoshilinch xabarlardan tashqari).</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit" class="ks-btn-ghost text-xs py-1.5 px-3">
                        Bildirishnoma sozlamalarini saqlash
                    </button>
                </div>
            </form>
        @else
            <div class="p-4 rounded-xl bg-ink-950/60 border border-ink-border space-y-3">
                <p class="text-xs text-mist">
                    Hisobingizga Telegram'ni ulasangiz, Telegram orqali 6 xonali xavfsiz kod bilan bir zumda kira olasiz hamda muhim bildirishnomalarni bot orqali qabul qilasiz.
                </p>

                <div class="pt-1">
                    <a href="{{ route('auth.telegram.redirect') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#24A1DE] hover:bg-[#2092c9] text-white rounded-btn text-xs font-bold shadow-md transition-all">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.121l-6.871 4.326-2.962-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.196 1.006.128.832.926z"/></svg>
                        <span>Telegram hisobini ulash</span>
                    </a>
                </div>
            </div>
        @endif
    </div>

</div>
