<div class="max-w-4xl mx-auto h-[calc(100vh-8rem)] flex flex-col bg-ink-900 rounded-panel border border-ink-border shadow-soft overflow-hidden"
     x-on:ai-scroll-bottom.window="setTimeout(() => { const c = document.getElementById('ai-container'); if (c) c.scrollTop = c.scrollHeight; }, 100)">

    <!-- Header -->
    <div class="p-3.5 sm:px-5 border-b border-ink-border flex items-center justify-between bg-ink-950/80">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-btn bg-amber-500/10 border border-amber-500/25 text-amber-400 flex items-center justify-center text-sm shadow-sm">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/><path d="M12 7v4"/><line x1="8" y1="16" x2="8" y2="16"/><line x1="16" y1="16" x2="16" y2="16"/></svg>
            </div>
            <div>
                <h2 class="text-xs sm:text-sm font-bold text-paper font-serif">AI Kitob Maslahatchisi</h2>
                <span class="text-[10px] text-amber-400 font-mono">Joriy hafta kitobi bo'yicha ekspert</span>
            </div>
        </div>
        <span class="hidden sm:block text-[11px] font-mono px-2 py-0.5 bg-ink-800 border border-ink-border text-mist rounded-pill">
            Suhbat tarixi saqlanadi
        </span>
    </div>

    <!-- Messages Container -->
    <div id="ai-container" class="flex-1 p-4 sm:p-5 overflow-y-auto space-y-3.5">
        @forelse ($history as $i => $m)
            <div class="flex items-start gap-2.5 {{ $m['role'] === 'user' ? 'flex-row-reverse' : '' }}" wire:key="ai-{{ $i }}-{{ md5($m['text']) }}">
                <div class="w-7 h-7 rounded-btn flex items-center justify-center text-xs shrink-0 {{ $m['role'] === 'assistant' ? 'bg-amber-500/10 border border-amber-500/25 text-amber-400' : 'bg-ink-800 border border-ink-border text-mist' }}">
                    @if($m['role'] === 'assistant')
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/><path d="M12 7v4"/></svg>
                    @else
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    @endif
                </div>
                <div class="p-3.5 rounded-panel text-xs sm:text-sm leading-relaxed max-w-xl shadow-sm whitespace-pre-line {{ $m['role'] === 'assistant' ? 'bg-ink-950/80 border border-ink-border text-paper' : 'bg-ink-800 border border-ink-border text-paper' }}">
                    {{ $m['text'] }}
                </div>
            </div>
        @empty
            <div class="text-center py-12 space-y-2">
                <div class="w-10 h-10 mx-auto rounded-btn bg-ink-800 border border-ink-border flex items-center justify-center text-amber-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/><path d="M12 7v4"/></svg>
                </div>
                <p class="text-xs font-mono text-mist">Salom! Kitob bo'yicha savol bering — masalan: «4 ta asosiy qonun nima?»</p>
            </div>
        @endforelse

        @if ($loading)
            <div class="flex items-center gap-1.5 text-xs font-mono text-amber-400 italic p-2">
                <span class="animate-bounce">●</span>
                <span class="animate-bounce" style="animation-delay: 0.2s">●</span>
                <span class="animate-bounce" style="animation-delay: 0.4s">●</span>
                <span class="ml-1">AI javob tayyorlamoqda...</span>
            </div>
        @endif
    </div>

    <!-- Input Box -->
    <div class="p-3 border-t border-ink-border bg-ink-950/80">
        <form wire:submit="ask" class="flex items-center gap-2">
            <input type="text" wire:model="query" placeholder="Kitob bo'yicha savol bering..." maxlength="500"
                class="flex-1 px-3.5 py-2 bg-ink-900 border border-ink-border rounded-btn text-xs sm:text-sm text-paper placeholder-mist focus:border-amber-400 focus:outline-none">
            <button type="submit" @disabled($loading) class="ks-btn-primary text-xs py-2 px-4 disabled:opacity-50 flex items-center gap-1.5 shrink-0">
                <span>So'rash</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </button>
        </form>
        @error('query') <p class="text-rose-300 font-mono text-xs mt-1">{{ $message }}</p> @enderror
    </div>
</div>
