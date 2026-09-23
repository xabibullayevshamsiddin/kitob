<div class="max-w-4xl mx-auto h-[calc(100vh-8rem)] flex flex-col bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-soft overflow-hidden"
     x-on:ai-scroll-bottom.window="setTimeout(() => { const c = document.getElementById('ai-container'); if (c) c.scrollTop = c.scrollHeight; }, 100)">

    <!-- Header -->
    <div class="p-4 sm:px-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white flex items-center justify-center text-xl shadow-md shadow-indigo-500/20">
                🤖
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">AI Kitob Maslahatchisi</h2>
                <span class="text-[11px] text-indigo-500 dark:text-indigo-400 font-semibold">Joriy hafta kitobi bo'yicha ekspert</span>
            </div>
        </div>
        <span class="hidden sm:block text-xs px-2.5 py-1 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-semibold rounded-lg">
            Suhbat tarixi saqlanadi
        </span>
    </div>

    <!-- Messages Container -->
    <div id="ai-container" class="flex-1 p-4 sm:p-6 overflow-y-auto space-y-4">
        @forelse ($history as $i => $m)
            <div class="flex items-start gap-3 {{ $m['role'] === 'user' ? 'flex-row-reverse' : '' }}" wire:key="ai-{{ $i }}-{{ md5($m['text']) }}">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-sm shrink-0 {{ $m['role'] === 'assistant' ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-white' }}">
                    {{ $m['role'] === 'assistant' ? '🤖' : '👤' }}
                </div>
                <div class="p-4 rounded-3xl text-xs sm:text-sm leading-relaxed max-w-xl shadow-sm whitespace-pre-line {{ $m['role'] === 'assistant' ? 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-100' : 'bg-indigo-600 text-white' }}">
                    {{ $m['text'] }}
                </div>
            </div>
        @empty
            <div class="text-center py-12 space-y-2">
                <span class="text-4xl block">🤖</span>
                <p class="text-sm text-slate-400">Salom! Kitob bo'yicha savol bering — masalan: «4 ta asosiy qonun nima?»</p>
            </div>
        @endforelse

        @if ($loading)
            <div class="flex items-center gap-2 text-xs text-indigo-500 italic p-2">
                <span class="animate-bounce">●</span>
                <span class="animate-bounce" style="animation-delay: 0.2s">●</span>
                <span class="animate-bounce" style="animation-delay: 0.4s">●</span>
                <span>AI javob tayyorlamoqda...</span>
            </div>
        @endif
    </div>

    <!-- Input Box -->
    <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
        <form wire:submit="ask" class="flex items-center gap-3">
            <input type="text" wire:model="query" placeholder="Kitob bo'yicha savol bering (masalan: 4 ta asosiy qonun nima?)..." maxlength="500"
                class="flex-1 px-4 py-3 bg-slate-100 dark:bg-slate-800 border-none rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500">
            <button type="submit" @disabled($loading) class="px-5 py-3 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-md shadow-indigo-600/25 transition-all flex items-center gap-1.5 shrink-0">
                <span>So'rash</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </button>
        </form>
        @error('query') <p class="text-rose-500 text-xs mt-2">{{ $message }}</p> @enderror
    </div>
</div>
