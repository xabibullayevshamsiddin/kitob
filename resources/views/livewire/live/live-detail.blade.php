<div class="max-w-4xl mx-auto space-y-8 pb-16">

    <div class="flex items-center gap-3">
        <a href="{{ route('live.index') }}" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">←</a>
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-manrope">{{ $event->title }}</h1>
            <p class="text-xs text-slate-400">
                {{ $event->scheduled_at?->timezone('Asia/Tashkent')->format('d M Y, H:i') }}
                @if ($event->book) • 📖 {{ $event->book->title }} @endif
            </p>
        </div>
        <span class="ml-auto px-3 py-1 rounded-full text-xs font-bold {{ $event->is_live ? 'bg-rose-500/20 text-rose-500' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
            {{ $event->is_live ? '🔴 EFIRDA' : ucfirst($event->status) }}
        </span>
    </div>

    @if (session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <!-- Stream Area -->
    <div class="p-8 rounded-3xl bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800 text-white text-center space-y-5">
        @if ($event->is_live && $event->stream_url)
            <a href="{{ $event->stream_url }}" target="_blank" rel="noopener"
               class="inline-block px-6 py-3 bg-rose-600 hover:bg-rose-500 text-white font-bold text-sm rounded-xl shadow-md shadow-rose-600/30 transition-all">
                🔴 Efirga qo'shilish
            </a>
        @elseif ($event->status === 'ended' && $event->replay_url)
            <a href="{{ $event->replay_url }}" target="_blank" rel="noopener"
               class="inline-block px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm rounded-xl shadow-md transition-all">
                ▶ Yozuvni ko'rish
            </a>
        @else
            <span class="text-5xl block">📺</span>
            <p class="text-sm text-slate-400">
                {{ $event->is_live ? 'Efir havolasi tez orada qo\'shiladi.' : 'Efir boshlanishiga oz qoldi — savolingizni oldindan yuboring!' }}
            </p>
        @endif

        @if ($event->description)
            <p class="text-xs text-slate-400 max-w-lg mx-auto leading-relaxed">{{ $event->description }}</p>
        @endif
    </div>

    <!-- Ask a question -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-4">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white">🎤 Savolingizni yuboring</h3>
        <form wire:submit="submitQuestion" class="flex flex-col sm:flex-row gap-2">
            <input type="text" wire:model="question" placeholder="Savolingiz..." maxlength="300"
                class="flex-1 px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500">
            <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-md transition-all shrink-0">
                Yuborish
            </button>
        </form>
        @error('question') <p class="text-rose-500 text-xs">{{ $message }}</p> @enderror

        @if ($myQuestions->isNotEmpty())
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
                @foreach ($myQuestions as $q)
                    <div class="flex items-center justify-between text-xs" wire:key="mq-{{ $q->id }}">
                        <span class="text-slate-600 dark:text-slate-300 truncate max-w-md">{{ $q->question }}</span>
                        <span class="{{ $q->is_answered ? 'text-emerald-500' : ($q->is_selected ? 'text-amber-500' : 'text-slate-400') }} font-semibold shrink-0">
                            {{ $q->is_answered ? '✓ Javob berildi' : ($q->is_selected ? '★ Tanlangan' : 'Kutilmoqda') }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Selected questions -->
    @if ($selectedQuestions->isNotEmpty())
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-3">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">⭐ Muallif tanlagan savollar</h3>
            @foreach ($selectedQuestions as $q)
                <div class="flex items-start gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/50" wire:key="sq-{{ $q->id }}">
                    <img src="{{ $q->user->avatar_url }}" class="w-8 h-8 rounded-lg object-cover shrink-0" alt="">
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $q->user->name }}</p>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">{{ $q->question }}</p>
                    </div>
                    @if ($q->is_answered)
                        <span class="text-[10px] text-emerald-500 font-bold shrink-0">✓ Javob berildi</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
