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
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
            <div class="relative w-full max-w-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl overflow-hidden p-6 sm:p-8 space-y-6 animate-scale-in"
                 @click.away="$wire.closeStudioModal()">

                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-rose-500 to-amber-500 flex items-center justify-center text-white text-lg shadow-md">
                            🎙️
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900 dark:text-white font-manrope">Yangi Jonli Efirni Sozlash</h2>
                            <p class="text-xs text-slate-400">Ruxsatlar va efir ma'lumotlarini belgilang</p>
                        </div>
                    </div>
                    <button wire:click="closeStudioModal" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        ✕
                    </button>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="startLiveStream" class="space-y-5">
                    <!-- Title -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                            Efir Mavzusi / Sarlavhasi <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model.defer="newTitle" placeholder="Masalan: 1-Bob Tahlili va Jonli Savol-Javob..."
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        @error('newTitle') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Book Select (Optional) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                            Bog'langan Kitob (ixtiyoriy)
                        </label>
                        <select wire:model.defer="newBookId"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
                            <option value="">-- Umumiy efir (kitob bog'lanmagan) --</option>
                            @foreach($books as $b)
                                <option value="{{ $b->id }}">{{ $b->title }} ({{ $b->author }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1.5">
                            Qisqacha Tavsif
                        </label>
                        <textarea wire:model.defer="newDescription" rows="2" placeholder="Ushbu efirda qaysi mavzular ko'rib chiqiladi..."
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
                    </div>

                    <!-- ── PERMISSION MODE (Tashrif buyuruvchilar huquqi) ── -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                            ⚙️ Tashrif buyuruvchilar (O'quvchilar) ruxsat rejimi
                        </label>
                        <p class="text-xs text-slate-400 mb-2">Efir davomida kirgan o'quvchilar nima qila olishini belgilang:</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Option 1: Both -->
                            <label class="relative flex items-start gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all {{ $newPermissionMode === 'both' ? 'bg-indigo-50/70 dark:bg-indigo-950/40 border-indigo-500 ring-2 ring-indigo-500/20' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                                <input type="radio" wire:model="newPermissionMode" value="both" class="mt-1 text-indigo-600 focus:ring-indigo-500">
                                <div class="text-xs">
                                    <span class="font-bold text-slate-900 dark:text-white block">✨ Ikkalasi ham mumkin</span>
                                    <span class="text-slate-500 dark:text-slate-400 text-[11px] block mt-0.5">Chatda yozish va mikrofon orqali ovozli savol berish ochiq</span>
                                </div>
                            </label>

                            <!-- Option 2: Chat only -->
                            <label class="relative flex items-start gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all {{ $newPermissionMode === 'chat_only' ? 'bg-indigo-50/70 dark:bg-indigo-950/40 border-indigo-500 ring-2 ring-indigo-500/20' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                                <input type="radio" wire:model="newPermissionMode" value="chat_only" class="mt-1 text-indigo-600 focus:ring-indigo-500">
                                <div class="text-xs">
                                    <span class="font-bold text-slate-900 dark:text-white block">💬 Faqat yoza olsin</span>
                                    <span class="text-slate-500 dark:text-slate-400 text-[11px] block mt-0.5">Faqat yozma chat ochiq, mikrofonlar o'chirilgan</span>
                                </div>
                            </label>

                            <!-- Option 3: Voice only -->
                            <label class="relative flex items-start gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all {{ $newPermissionMode === 'voice_only' ? 'bg-indigo-50/70 dark:bg-indigo-950/40 border-indigo-500 ring-2 ring-indigo-500/20' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                                <input type="radio" wire:model="newPermissionMode" value="voice_only" class="mt-1 text-indigo-600 focus:ring-indigo-500">
                                <div class="text-xs">
                                    <span class="font-bold text-slate-900 dark:text-white block">🎙️ Faqat gapira olsin</span>
                                    <span class="text-slate-500 dark:text-slate-400 text-[11px] block mt-0.5">Ovozli navbat ochiq, yozma chat yopiq</span>
                                </div>
                            </label>

                            <!-- Option 4: View only -->
                            <label class="relative flex items-start gap-3 p-3.5 rounded-2xl border cursor-pointer transition-all {{ $newPermissionMode === 'view_only' ? 'bg-indigo-50/70 dark:bg-indigo-950/40 border-indigo-500 ring-2 ring-indigo-500/20' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                                <input type="radio" wire:model="newPermissionMode" value="view_only" class="mt-1 text-indigo-600 focus:ring-indigo-500">
                                <div class="text-xs">
                                    <span class="font-bold text-slate-900 dark:text-white block">🔒 Ikkalasi ham mumkin emas</span>
                                    <span class="text-slate-500 dark:text-slate-400 text-[11px] block mt-0.5">Faqat ma'ruza / monolog: chat va ovoz to'liq cheklangan</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="closeStudioModal"
                            class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            Bekor qilish
                        </button>
                        <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-amber-500 hover:from-rose-500 hover:to-amber-400 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-rose-600/25 active:scale-95 transition-all">
                            🔴 Efirni Boshlash →
                        </button>
                    </div>
                </form>

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

    <!-- Ended events -->
    @if ($ended->isNotEmpty())
        <div class="space-y-3 pt-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">O'tgan efirlar</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach ($ended as $event)
                    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 flex items-center justify-between gap-3" wire:key="ended-{{ $event->id }}">
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $event->title }}</h4>
                            <span class="text-[11px] text-slate-400">{{ $event->scheduled_at?->timezone('Asia/Tashkent')->format('d M Y') }}</span>
                        </div>
                        <a href="{{ route('live.show', $event) }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline shrink-0">
                            Zalga kirish →
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
