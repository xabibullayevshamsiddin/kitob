<div class="max-w-5xl mx-auto space-y-8 pb-16">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-manrope">Muallif bilan Jonli Efir</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Har hafta oxirida kitob tahlili, jonli video-muloqot va savol-javoblar</p>
        </div>

        @auth
            <button wire:click="openStudioModal"
                class="inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl bg-gradient-to-r from-rose-600 via-rose-500 to-amber-500 hover:from-rose-500 hover:to-amber-400 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-rose-600/30 active:scale-95 transition-all group shrink-0">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
                </span>
                <span>🎙️ Jonli Efir Boshlash</span>
            </button>
        @else
            <a href="{{ route('login') }}" class="text-xs font-bold text-rose-500 hover:underline flex items-center gap-1">
                Efir boshlash uchun kiring →
            </a>
        @endauth
    </div>

    @if (session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm font-semibold flex items-center gap-2">
            <span>✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-sm font-semibold flex items-center gap-2">
            <span>⚠</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- ── EFIRNI SOZLASH VA BOSHLASH MODALI ── -->
    @if($showStudioModal)
        <div class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Fullscreen Dark Blur Backdrop covering whole screen including header -->
            <div class="fixed inset-0 bg-black/85 backdrop-blur-md transition-opacity" wire:click="closeStudioModal"></div>

            <!-- Centering container with scrolling padding -->
            <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-center">
                <div class="relative w-full max-w-2xl transform rounded-3xl bg-ink-900 border border-white/10 shadow-2xl text-left my-8 overflow-hidden z-10"
                     @click.stop>

                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-white/10 p-5 sm:p-6 bg-white/[0.02]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-rose-500 to-amber-500 flex items-center justify-center text-white text-lg shadow-md shadow-rose-500/20 shrink-0">
                                🎙️
                            </div>
                            <div>
                                <h2 class="text-base sm:text-lg font-black text-white font-manrope">Yangi Jonli Efirni Sozlash</h2>
                                <p class="text-xs text-slate-400">Ruxsatlar va efir ma'lumotlarini belgilang</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeStudioModal" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Form Body with max-h and scroll if needed on short screens -->
                    <form wire:submit.prevent="startLiveStream" class="p-5 sm:p-6 space-y-4 max-h-[calc(85vh-130px)] overflow-y-auto">
                        <!-- Title -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                Efir Mavzusi / Sarlavhasi <span class="text-rose-400">*</span>
                            </label>
                            <input type="text" wire:model.defer="newTitle" placeholder="Masalan: 1-Bob Tahlili va Jonli Savol-Javob..."
                                class="w-full px-4 py-2.5 bg-ink-950 border border-white/10 rounded-xl text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            @error('newTitle') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Book Select -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                Bog'langan Kitob (ixtiyoriy)
                            </label>
                            <select wire:model.defer="newBookId"
                                class="w-full px-4 py-2.5 bg-ink-950 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                                <option value="">-- Umumiy efir (kitob bog'lanmagan) --</option>
                                @foreach($books as $b)
                                    <option value="{{ $b->id }}">{{ $b->title }} ({{ $b->author }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                Qisqacha Tavsif
                            </label>
                            <textarea wire:model.defer="newDescription" rows="2" placeholder="Ushbu efirda qaysi mavzular ko'rib chiqiladi..."
                                class="w-full px-4 py-2 bg-ink-950 border border-white/10 rounded-xl text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                        </div>

                        <!-- Permission Mode Selector -->
                        <div class="space-y-2 pt-1">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                                ⚙️ Tashrif buyuruvchilar (O'quvchilar) ruxsat rejimi
                            </label>
                            <p class="text-xs text-slate-400 mb-2">Efir davomida kirgan o'quvchilar nima qila olishini belgilang:</p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <!-- Option 1: Both -->
                                <label class="relative flex items-start gap-3 p-3 rounded-2xl border cursor-pointer transition-all {{ $newPermissionMode === 'both' ? 'bg-amber-400/10 border-amber-400/50 ring-1 ring-amber-400/30' : 'bg-ink-950 border-white/10 hover:border-white/20' }}">
                                    <input type="radio" wire:model="newPermissionMode" value="both" class="mt-0.5 text-amber-500 focus:ring-amber-500">
                                    <div class="text-xs">
                                        <span class="font-bold text-white block">✨ Ikkalasi ham mumkin</span>
                                        <span class="text-slate-400 text-[11px] block mt-0.5">Chatda yozish va mikrofon orqali ovozli savol berish ochiq</span>
                                    </div>
                                </label>

                                <!-- Option 2: Chat only -->
                                <label class="relative flex items-start gap-3 p-3 rounded-2xl border cursor-pointer transition-all {{ $newPermissionMode === 'chat_only' ? 'bg-amber-400/10 border-amber-400/50 ring-1 ring-amber-400/30' : 'bg-ink-950 border-white/10 hover:border-white/20' }}">
                                    <input type="radio" wire:model="newPermissionMode" value="chat_only" class="mt-0.5 text-amber-500 focus:ring-amber-500">
                                    <div class="text-xs">
                                        <span class="font-bold text-white block">💬 Faqat yoza olsin</span>
                                        <span class="text-slate-400 text-[11px] block mt-0.5">Faqat yozma chat ochiq, mikrofonlar o'chirilgan</span>
                                    </div>
                                </label>

                                <!-- Option 3: Voice only -->
                                <label class="relative flex items-start gap-3 p-3 rounded-2xl border cursor-pointer transition-all {{ $newPermissionMode === 'voice_only' ? 'bg-amber-400/10 border-amber-400/50 ring-1 ring-amber-400/30' : 'bg-ink-950 border-white/10 hover:border-white/20' }}">
                                    <input type="radio" wire:model="newPermissionMode" value="voice_only" class="mt-0.5 text-amber-500 focus:ring-amber-500">
                                    <div class="text-xs">
                                        <span class="font-bold text-white block">🎙️ Faqat gapira olsin</span>
                                        <span class="text-slate-400 text-[11px] block mt-0.5">Ovozli navbat ochiq, yozma chat yopiq</span>
                                    </div>
                                </label>

                                <!-- Option 4: View only -->
                                <label class="relative flex items-start gap-3 p-3 rounded-2xl border cursor-pointer transition-all {{ $newPermissionMode === 'view_only' ? 'bg-amber-400/10 border-amber-400/50 ring-1 ring-amber-400/30' : 'bg-ink-950 border-white/10 hover:border-white/20' }}">
                                    <input type="radio" wire:model="newPermissionMode" value="view_only" class="mt-0.5 text-amber-500 focus:ring-amber-500">
                                    <div class="text-xs">
                                        <span class="font-bold text-white block">🔒 Ikkalasi ham mumkin emas</span>
                                        <span class="text-slate-400 text-[11px] block mt-0.5">Faqat ma'ruza: chat va ovoz to'liq cheklangan</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Modal Actions Footer (Pinned) -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
                            <button type="button" wire:click="closeStudioModal"
                                class="px-5 py-2.5 rounded-xl border border-white/10 text-xs font-bold text-slate-300 hover:bg-white/5 hover:text-white transition-colors">
                                Bekor qilish
                            </button>
                            <button type="submit"
                                class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 via-rose-500 to-amber-500 hover:from-rose-500 hover:to-amber-400 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-rose-600/30 active:scale-95 transition-all">
                                🔴 Efirni Boshlash →
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <!-- ── EFIRLAR RO'YXATI ── -->
    @if ($upcoming->isEmpty())
        <div class="p-12 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft text-center space-y-4">
            <span class="text-5xl block animate-bounce">📺</span>
            <h2 class="text-base font-bold text-slate-900 dark:text-white">Hozircha faol efir yo'q</h2>
            <p class="text-xs text-slate-400 max-w-sm mx-auto">Ustozlar yangi jonli efir boshlaganida ushbu sahifada paydo bo'ladi. Siz ham "Jonli Efir Boshlash" tugmasi orqali dars o'tkazishingiz mumkin.</p>
        </div>
    @else
        @foreach ($upcoming as $event)
            <div class="p-8 sm:p-10 rounded-3xl bg-gradient-to-br {{ $event->is_live ? 'from-rose-950 via-slate-900 to-slate-900 border-rose-900/50 ring-1 ring-rose-500/20' : 'from-indigo-950 via-slate-900 to-slate-900 border-indigo-800/40' }} border text-white shadow-2xl relative overflow-hidden space-y-5" wire:key="event-{{ $event->id }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full {{ $event->is_live ? 'bg-rose-500/20 text-rose-400' : 'bg-indigo-500/20 text-indigo-300' }} text-xs font-bold uppercase tracking-wider">
                        <span class="w-2 h-2 rounded-full {{ $event->is_live ? 'bg-rose-500 animate-ping' : 'bg-indigo-400' }}"></span>
                        <span>{{ $event->is_live ? 'HOZIR EFIRDA' : 'Navbatdagi efir: ' . $event->scheduled_at?->timezone('Asia/Tashkent')->format('d M, H:i') }}</span>
                    </div>

                    <!-- Permission mode badge -->
                    <span class="text-[11px] font-mono px-2.5 py-1 rounded-lg bg-white/5 border border-white/10 text-slate-300">
                        @if($event->permission_mode === 'chat_only')
                            💬 Faqat Chat
                        @elseif($event->permission_mode === 'voice_only')
                            🎙️ Faqat Ovoz
                        @elseif($event->permission_mode === 'view_only')
                            🔒 Faqat Ma'ruza
                        @else
                            ✨ Chat & Ovoz
                        @endif
                    </span>
                </div>

                <h2 class="text-2xl sm:text-4xl font-black font-manrope leading-tight">{{ $event->title }}</h2>

                @if ($event->description)
                    <p class="text-xs sm:text-sm text-slate-300 max-w-xl leading-relaxed">{{ $event->description }}</p>
                @endif

                <div class="flex flex-wrap items-center gap-4 text-xs">
                    @if ($event->book)
                        <span class="text-amber-400 font-semibold">📖 {{ $event->book->title }}</span>
                    @endif
                    @if ($event->hostUser)
                        <span class="text-slate-400">Ustoz: <strong class="text-white">{{ $event->hostUser->name }}</strong></span>
                    @endif
                </div>

                <div class="pt-2">
                    <a href="{{ route('live.show', $event) }}"
                       class="inline-flex items-center gap-2 px-6 py-3.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-rose-600/30 transition-all hover:scale-105 active:scale-95">
                        <span>🔴 Efir zaliga kirish</span>
                        <span>→</span>
                    </a>
                </div>
            </div>
        @endforeach

        <!-- Question submission box -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">🎤 Muallifga oldindan savol yuborish</h3>
            <form wire:submit.prevent="submitQuestion" class="flex flex-col sm:flex-row gap-2">
                <input type="text" wire:model.defer="question" placeholder="Savolingizni yozing..." maxlength="300"
                    class="flex-1 px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-rose-500 focus:outline-none">
                <button type="submit" wire:loading.attr="disabled"
                    class="px-5 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-md transition-all disabled:opacity-50 shrink-0">
                    Yuborish
                </button>
            </form>
            @error('question') <p class="text-rose-500 text-xs">{{ $message }}</p> @enderror

            @if ($myQuestions->isNotEmpty())
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sizning savollaringiz</span>
                    @foreach ($myQuestions as $q)
                        <div class="flex items-center justify-between text-xs" wire:key="q-{{ $q->id }}">
                            <span class="text-slate-600 dark:text-slate-300 truncate max-w-md">{{ $q->question }}</span>
                            <span class="{{ $q->is_answered ? 'text-emerald-500' : ($q->is_selected ? 'text-amber-500' : 'text-slate-400') }} shrink-0 font-semibold">
                                {{ $q->is_answered ? '✓ Javob berildi' : ($q->is_selected ? '★ Tanlangan' : 'Kutilmoqda') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

</div>
