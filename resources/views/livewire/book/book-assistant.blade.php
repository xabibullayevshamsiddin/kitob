<div
    id="book-ai-assistant-root-{{ $bookId }}"
    x-data="{
        charCount: 0,
        scrollToBottom() {
            const box = this.$refs.messagesBox;
            if (box) {
                box.scrollTop = box.scrollHeight;
            }
            if (typeof window.renderBookAiMath === 'function') {
                window.renderBookAiMath(this.$el);
            }
        },
        jumpToBookPage(pageNum) {
            const p = parseInt(pageNum, 10) || 1;
            const readerEl = document.getElementById('online-reader');
            if (readerEl) {
                readerEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            window.dispatchEvent(new CustomEvent('book-ai-jump-page', { detail: { page: p } }));
            if (typeof window.fbGoToPage === 'function') {
                setTimeout(() => window.fbGoToPage(p), 220);
            } else {
                const url = new URL(window.location.href);
                url.searchParams.set('page', String(p));
                window.history.replaceState({}, '', url.toString());
            }
        }
    }"
    x-init="$nextTick(() => scrollToBottom())"
    @book-ai-updated.window="if ($event.detail && $event.detail.bookId == {{ $bookId }}) { charCount = 0; if ($refs.questionInput) { $refs.questionInput.value = ''; } $nextTick(() => scrollToBottom()); }"
    class="rounded-card border border-ink-border bg-ink-900 text-paper shadow-xl overflow-hidden"
>
    {{-- Screen-reader live announcement --}}
    <div class="sr-only" aria-live="polite" aria-atomic="true">
        {{ $lastAnnouncement }}
    </div>

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3.5 sm:px-5 bg-ink-950/90 border-b border-ink-border">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 rounded-btn bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3l1.912 5.813a2 2 0 001.275 1.275L21 12l-5.813 1.912a2 2 0 00-1.275 1.275L12 21l-1.912-5.813a2 2 0 00-1.275-1.275L3 12l5.813-1.912a2 2 0 001.275-1.275L12 3z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-sm sm:text-base font-bold font-serif text-paper truncate">
                        {{ __('ai.title') }}
                    </h3>
                    <span class="px-2 py-0.5 rounded-badge bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[10px] font-mono font-bold uppercase tracking-wider">
                        Faqat shu kitob bo'yicha
                    </span>
                </div>
                <p class="text-[11px] text-mist font-mono truncate">
                    «{{ $book ? $book->title : '' }}» matniga asoslangan aniq javoblar va bet ko'rsatkichlari
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @auth
                <span class="px-2.5 py-1 rounded-badge bg-ink-900 border border-ink-border text-[11px] font-mono text-mist" title="Kunlik savollar limiti (Asia/Tashkent)">
                    Limit: <strong class="text-amber-400">{{ $remainingToday }}</strong>/{{ $dailyLimit }}
                </span>

                @if($messages->isNotEmpty())
                    <button
                        type="button"
                        wire:click="clearHistory"
                        wire:loading.attr="disabled"
                        class="px-2.5 py-1.5 rounded-btn bg-ink-900 hover:bg-rose-500/15 border border-ink-border hover:border-rose-500/40 text-xs font-sans text-mist hover:text-rose-300 inline-flex items-center gap-1.5 transition-colors"
                        title="{{ __('ai.clear_history') }}"
                    >
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                        </svg>
                        <span>{{ __('ai.clear_history') }}</span>
                    </button>
                @endif
            @endauth
        </div>
    </div>

    {{-- Starter / Suggested Questions Bar --}}
    <div class="px-4 py-2.5 sm:px-5 bg-ink-950/40 border-b border-ink-border flex items-center gap-2 overflow-x-auto">
        <span class="text-[11px] font-mono text-mist shrink-0 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/>
                <path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <span>Tayyor savollar:</span>
        </span>
        @foreach($suggestedQuestions as $sq)
            <button
                type="button"
                wire:click="ask({{ json_encode($sq) }})"
                wire:loading.attr="disabled"
                class="px-2.5 py-1 rounded-badge bg-ink-950 hover:bg-amber-500/15 border border-ink-border hover:border-amber-400/50 text-xs text-paper hover:text-amber-300 font-sans transition-colors shrink-0 inline-flex items-center gap-1.5"
            >
                <span>{{ $sq }}</span>
                <svg class="w-3 h-3 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                </svg>
            </button>
        @endforeach
    </div>

    {{-- Messages Feed --}}
    <div
        x-ref="messagesBox"
        class="p-4 sm:p-5 space-y-4 max-h-[440px] min-h-[240px] overflow-y-auto bg-ink-900/60"
    >
        @guest
            <div class="py-10 text-center space-y-3">
                <div class="w-11 h-11 rounded-btn bg-amber-500/10 border border-amber-500/25 text-amber-400 flex items-center justify-center mx-auto">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                </div>
                <p class="text-sm font-serif font-bold text-paper">{{ __('ai.unauthorized') }}</p>
                <a href="{{ route('login') }}" class="ks-btn-primary inline-flex items-center gap-2 text-xs py-2 px-4">
                    <span>Tizimga kirish</span>
                </a>
            </div>
        @else
            @if($messages->isEmpty())
                <div class="py-8 text-center space-y-3 max-w-lg mx-auto">
                    <div class="w-11 h-11 rounded-btn bg-amber-500/10 border border-amber-500/25 text-amber-400 flex items-center justify-center mx-auto">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/>
                            <path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/>
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold font-serif text-paper">
                        Kitob bo'yicha istalgan savolingizni bering
                    </h4>
                    <p class="text-xs text-mist leading-relaxed">
                        AI yordamchi faqat ushbu kitobning sahifalari va boblariga tayanib javob beradi hamda javob topilgan aniq bet raqamini ko'rsatadi.
                    </p>
                </div>
            @else
                @foreach($messages as $msg)
                    <div class="space-y-2.5" wire:key="ai-msg-{{ $msg['id'] }}">
                        {{-- User Question --}}
                        <div class="flex justify-end">
                            <div class="max-w-[85%] sm:max-w-[75%] rounded-card px-3.5 py-2.5 bg-amber-500/15 border border-amber-500/30 text-paper text-xs sm:text-sm leading-relaxed">
                                <p class="break-words">{{ $msg['question'] }}</p>
                                @if(!empty($msg['created_at']))
                                    <span class="block text-[10px] font-mono text-mist mt-1 text-right">{{ $msg['created_at'] }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- AI Response --}}
                        <div class="flex justify-start">
                            <div class="max-w-[92%] sm:max-w-[85%] rounded-card px-4 py-3 border {{ $msg['refused'] ? 'bg-ink-950/90 border-amber-500/30' : 'bg-ink-950 border-ink-border' }} space-y-2.5">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-mono font-bold {{ $msg['refused'] ? 'text-amber-400' : 'text-emerald-400' }}">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            @if($msg['refused'])
                                                <circle cx="12" cy="12" r="10"/>
                                                <line x1="12" y1="8" x2="12" y2="12"/>
                                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                                            @else
                                                <path d="M12 3l1.912 5.813a2 2 0 001.275 1.275L21 12l-5.813 1.912a2 2 0 00-1.275 1.275L12 21l-1.912-5.813a2 2 0 00-1.275-1.275L3 12l5.813-1.912a2 2 0 001.275-1.275L12 3z"/>
                                            @endif
                                        </svg>
                                        <span>{{ $msg['refused'] ? 'Cheklov / Kitob doirasi' : __('ai.title') }}</span>
                                    </span>
                                </div>

                                <div class="prose prose-invert max-w-none text-xs sm:text-sm text-paper leading-relaxed space-y-2 book-ai-math-content">
                                    {!! $msg['answer_html'] !!}
                                </div>

                                {{-- Verified Source Chips ("45-bet" / "3-bob") -> Click jumps flipbook to page --}}
                                @if(!empty($msg['sources']))
                                    <div class="pt-2 border-t border-ink-border flex flex-wrap items-center gap-1.5">
                                        <span class="text-[11px] font-mono text-mist inline-flex items-center gap-1 mr-1">
                                            <svg class="w-3 h-3 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path d="M4 19.5A2.5 2.5 0 016.5 17H20"/>
                                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>
                                            </svg>
                                            <span>{{ __('ai.sources_label') }}:</span>
                                        </span>

                                        @foreach($msg['sources'] as $src)
                                            @php
                                                $targetPage = (int) ($src['page_start'] ?? 1);
                                                $chipLabel = (string) ($src['label'] ?? ($targetPage . '-bet'));
                                            @endphp
                                            <button
                                                type="button"
                                                @click="jumpToBookPage({{ $targetPage > 0 ? $targetPage : 1 }})"
                                                class="px-2.5 py-0.5 rounded-badge bg-amber-500/15 hover:bg-amber-500 text-amber-300 hover:text-ink-950 border border-amber-500/30 text-[11px] font-mono font-bold inline-flex items-center gap-1 transition-all"
                                                title="Flipbook'da {{ $chipLabel }}ga o'tish"
                                            >
                                                <span>[{{ $src['index'] ?? $loop->iteration }}] {{ $chipLabel }}</span>
                                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <polyline points="9 18 15 12 9 6"/>
                                                </svg>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- Loading Indicator --}}
            <div wire:loading wire:target="ask" class="w-full">
                <div class="flex items-center gap-2.5 p-3 rounded-card bg-ink-950 border border-amber-500/30 text-xs text-amber-300 font-mono">
                    <svg class="w-4 h-4 animate-spin text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                        <path d="M12 2a10 10 0 0110 10"/>
                    </svg>
                    <span>Kitob sahifalaridan mos bo'laklar qidirilmoqda va javob tayyorlanmoqda...</span>
                </div>
            </div>

            {{-- Error State --}}
            @if($errorMessage)
                <div role="alert" class="p-3 rounded-card bg-rose-500/10 border border-rose-500/35 text-xs text-rose-300 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-rose-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span>{{ $errorMessage }}</span>
                </div>
            @endif
        @endguest
    </div>

    {{-- Input Form --}}
    @auth
        <form wire:submit.prevent="ask" class="p-3.5 sm:p-4 bg-ink-950 border-t border-ink-border space-y-2">
            <div class="flex items-end gap-2">
                <div class="flex-1 relative">
                    <textarea
                        wire:model.defer="question"
                        x-ref="questionInput"
                        @input="charCount = $el.value.length"
                        rows="2"
                        maxlength="{{ $maxChars }}"
                        placeholder="{{ __('ai.ask_placeholder') }}"
                        @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); $wire.set('question', $el.value); $wire.ask(); }"
                        class="ks-input w-full text-xs sm:text-sm pr-14 resize-none"
                    ></textarea>
                    <span
                        class="absolute bottom-2 right-2.5 text-[10px] font-mono text-mist pointer-events-none"
                        x-text="charCount + '/{{ $maxChars }}'"
                    ></span>
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="ask"
                    class="ks-btn-primary py-2.5 px-4 text-xs font-bold inline-flex items-center gap-1.5 shrink-0 h-10"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="22" y1="2" x2="11" y2="13"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                    <span>{{ __('ai.send') }}</span>
                </button>
            </div>
        </form>
    @endauth
</div>
