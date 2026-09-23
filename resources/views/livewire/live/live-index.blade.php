<div class="max-w-5xl mx-auto space-y-8 pb-16">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-manrope">Muallif bilan Jonli Efir</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Har hafta oxirida kitob tahlili va jonli savol-javoblar</p>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-sm font-semibold">
            {{ session('error') }}
        </div>
    @endif

    @if ($upcoming->isEmpty())
        <div class="p-12 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft text-center space-y-3">
            <span class="text-5xl block">📺</span>
            <h2 class="text-base font-bold text-slate-900 dark:text-white">Rejalashtirilgan efir yo'q</h2>
            <p class="text-xs text-slate-400">Yangi jonli efirlar e'lon qilinganda shu yerda ko'rasiz.</p>
        </div>
    @else
        @foreach ($upcoming as $event)
            <div class="p-8 sm:p-10 rounded-3xl bg-gradient-to-br {{ $event->is_live ? 'from-rose-950' : 'from-indigo-950' }} via-slate-900 to-slate-900 border {{ $event->is_live ? 'border-rose-900/40' : 'border-indigo-800/40' }} text-white shadow-2xl relative overflow-hidden space-y-5" wire:key="event-{{ $event->id }}">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full {{ $event->is_live ? 'bg-rose-500/20 text-rose-400' : 'bg-indigo-500/20 text-indigo-300' }} text-xs font-bold uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full {{ $event->is_live ? 'bg-rose-500 animate-ping' : 'bg-indigo-400' }}"></span>
                    <span>{{ $event->is_live ? 'HOZIR EFIRDA' : 'Navbatdagi efir: ' . $event->scheduled_at?->timezone('Asia/Tashkent')->format('d M, H:i') }}</span>
                </div>

                <h2 class="text-2xl sm:text-4xl font-black font-manrope leading-tight">{{ $event->title }}</h2>

                @if ($event->description)
                    <p class="text-xs sm:text-sm text-slate-300 max-w-xl leading-relaxed">{{ $event->description }}</p>
                @endif

                @if ($event->book)
                    <span class="inline-block text-xs text-amber-400 font-semibold">📖 {{ $event->book->title }}</span>
                @endif

                @if ($event->is_live && $event->stream_url)
                    <a href="{{ $event->stream_url }}" target="_blank" rel="noopener"
                       class="inline-block px-6 py-3 bg-rose-600 hover:bg-rose-500 text-white font-bold text-sm rounded-xl shadow-md shadow-rose-600/30 transition-all">
                        🔴 Efirga qo'shilish
                    </a>
                @elseif ($event->is_live)
                    <p class="text-xs text-rose-300">Efir havolasi tez orada qo'shiladi.</p>
                @endif
            </div>
        @endforeach

        <!-- Question submission box -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">🎤 Muallifga oldindan savol yuborish</h3>
            <form wire:submit="submitQuestion" class="flex flex-col sm:flex-row gap-2">
                <input type="text" wire:model="question" placeholder="Savolingizni yozing..." maxlength="300"
                    class="flex-1 px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-rose-500">
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

    @if ($ended->isNotEmpty())
        <div class="space-y-3">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">O'tgan efirlar</h3>
            @foreach ($ended as $event)
                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 flex items-center justify-between gap-3" wire:key="ended-{{ $event->id }}">
                    <div class="min-w-0">
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $event->title }}</h4>
                        <span class="text-[11px] text-slate-400">{{ $event->scheduled_at?->timezone('Asia/Tashkent')->format('d M Y') }}</span>
                    </div>
                    @if ($event->replay_url)
                        <a href="{{ $event->replay_url }}" target="_blank" rel="noopener" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline shrink-0">▶ Yozuvni ko'rish</a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
