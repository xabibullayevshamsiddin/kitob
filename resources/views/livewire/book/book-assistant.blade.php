<div
    id="book-ai-assistant-root-{{ $bookId }}"
    x-data="bookAiAssistant_{{ $bookId }}()"
    x-init="initAssistant()"
    @book-ai-updated.window="onBookAiUpdated($event.detail)"
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
                        {{ __('ai.book_scope_badge') }}
                    </span>
                </div>
                <p class="text-[11px] text-mist font-mono truncate">
                    {{ __('ai.book_subtitle', ['book' => $book ? $book->title : '']) }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @auth
                <span
                    class="px-2.5 py-1 rounded-badge {{ $remainingToday > 0 ? 'bg-ink-900 border-ink-border text-mist' : 'bg-rose-500/15 border-rose-500/35 text-rose-300' }} text-[11px] font-mono inline-flex items-center gap-1.5"
                    title="Kunlik savollar limiti: {{ $remainingToday }}/{{ $dailyLimit }}. Qayta tiklanish: {{ $resetRemainingHuman }}"
                >
                    @if($remainingToday <= 0)
                        <svg class="w-3.5 h-3.5 text-rose-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    @endif
                    <span>Limit: <strong class="{{ $remainingToday > 0 ? 'text-amber-400' : 'text-rose-400 font-bold' }}">{{ $remainingToday }}</strong>/{{ $dailyLimit }}</span>
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
            <span>{{ __('ai.suggested_questions') }}</span>
        </span>
        @foreach($suggestedQuestions as $sq)
            <button
                type="button"
                @if($remainingToday <= 0)
                    disabled
                    title="{{ __('ai.limit_depleted_tooltip', ['time' => $resetRemainingHuman]) }}"
                    class="px-2.5 py-1 rounded-badge bg-ink-950/60 border border-ink-border/60 text-xs text-mist/40 font-sans shrink-0 inline-flex items-center gap-1.5 opacity-50 cursor-not-allowed"
                @else
                    wire:click="ask({{ json_encode($sq) }})"
                    wire:loading.attr="disabled"
                    class="px-2.5 py-1 rounded-badge bg-ink-950 hover:bg-amber-500/15 border border-ink-border hover:border-amber-400/50 text-xs text-paper hover:text-amber-300 font-sans transition-colors shrink-0 inline-flex items-center gap-1.5"
                @endif
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
                    <span>{{ __('site.auth.login') }}</span>
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
                        {{ __('ai.starter_title') }}
                    </h4>
                    <p class="text-xs text-mist leading-relaxed">
                        {{ __('ai.starter_desc') }}
                    </p>
                </div>
            @else
                @foreach($messages as $msg)
                    <div class="space-y-2.5 ai-message-item" wire:key="ai-msg-{{ $msg['id'] }}">
                        {{-- User Question --}}
                        <div class="flex justify-end animate-ai-bubble">
                            <div class="max-w-[85%] sm:max-w-[75%] rounded-card px-3.5 py-2.5 bg-amber-500/15 border border-amber-500/30 text-paper text-xs sm:text-sm leading-relaxed shadow-sm">
                                <p class="break-words">{{ $msg['question'] }}</p>
                                @if(!empty($msg['created_at']))
                                    <span class="block text-[10px] font-mono text-mist mt-1 text-right">{{ $msg['created_at'] }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- AI Response --}}
                        <div class="flex justify-start">
                            <div
                                class="ai-msg-wrapper max-w-[92%] sm:max-w-[85%] rounded-card px-4 py-3 border {{ $msg['refused'] ? 'bg-ink-950/90 border-amber-500/30' : 'bg-ink-950 border-ink-border' }} space-y-2.5 shadow-sm"
                                data-msg-id="{{ $msg['id'] }}"
                            >
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
                                        <span>{{ $msg['refused'] ? __('ai.refused_badge') : __('ai.title') }}</span>
                                    </span>
                                </div>

                                <div class="prose prose-invert max-w-none text-xs sm:text-sm text-paper leading-relaxed space-y-2 book-ai-math-content">
                                    {!! $msg['answer_html'] !!}
                                </div>

                                {{-- Verified Source Chips ("45-bet" / "3-bob") -> Click jumps flipbook to page --}}
                                @if(!empty($msg['sources']))
                                    <div class="book-ai-sources pt-2 border-t border-ink-border flex flex-wrap items-center gap-1.5">
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
                                                $chipLabel = (string) ($src['label'] ?? __('ai.page_num', ['page' => $targetPage]));
                                                $pdfUrl = route('books.pdf.read', $bookId) . '#page=' . max(1, $targetPage) . '&view=FitH';
                                            @endphp
                                            <div class="inline-flex items-center rounded-badge border border-amber-500/35 bg-amber-500/15 overflow-hidden text-[11px] font-mono shadow-sm">
                                                <button
                                                    type="button"
                                                    @click="jumpToBookPage({{ $targetPage > 0 ? $targetPage : 1 }})"
                                                    class="px-2.5 py-0.5 hover:bg-amber-500 text-amber-300 hover:text-ink-950 font-bold inline-flex items-center gap-1 transition-all"
                                                    title="{{ __('ai.jump_to_page', ['page' => $chipLabel]) }}"
                                                >
                                                    <span>{{ !empty($src['index']) ? '[' . $src['index'] . '] ' : '' }}{{ $chipLabel }}</span>
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <polyline points="9 18 15 12 9 6"/>
                                                    </svg>
                                                </button>
                                                <a
                                                    href="{{ $pdfUrl }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="px-1.5 py-0.5 border-l border-amber-500/30 text-amber-400 hover:text-white hover:bg-amber-500/30 transition-colors inline-flex items-center"
                                                    title="{{ __('ai.open_pdf_page', ['page' => $targetPage]) }}"
                                                >
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                                                        <polyline points="15 3 21 3 21 9"/>
                                                        <line x1="10" y1="14" x2="21" y2="3"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- Animated AI Thinking Indicator ("O'ylayapti...") --}}
            <div wire:loading wire:target="ask" class="w-full">
                <div class="flex justify-start">
                    <div
                        x-data="{
                            step: 0,
                            steps: [
                                @js(__('ai.thinking_step_1')),
                                @js(__('ai.thinking_step_2')),
                                @js(__('ai.thinking_step_3'))
                            ],
                            stepInterval: null,
                            start() {
                                this.step = 0;
                                if (this.stepInterval) clearInterval(this.stepInterval);
                                this.stepInterval = setInterval(() => {
                                    this.step = (this.step + 1) % this.steps.length;
                                }, 2200);
                            }
                        }"
                        x-init="start()"
                        class="ai-msg-wrapper max-w-[92%] sm:max-w-[85%] rounded-card px-4 py-3 bg-ink-950 border border-amber-500/35 shadow-lg shadow-amber-500/5 space-y-2.5 animate-ai-bubble"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <span class="inline-flex items-center gap-2 text-[11px] font-mono font-bold text-amber-400">
                                <span class="relative flex h-2.5 w-2.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                                </span>
                                <span>{{ __('ai.thinking') }}</span>
                            </span>

                            {{-- 3 Wave Bouncing Dots --}}
                            <div class="flex items-center gap-1" aria-hidden="true">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-bounce" style="animation-delay: 0s;"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-bounce" style="animation-delay: 0.16s;"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-bounce" style="animation-delay: 0.32s;"></span>
                            </div>
                        </div>

                        {{-- Step text with spinner --}}
                        <div class="flex items-center gap-2 text-xs text-mist font-mono py-0.5 min-h-[22px]">
                            <svg class="w-3.5 h-3.5 animate-spin text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                                <path d="M12 2a10 10 0 0110 10"/>
                            </svg>
                            <span class="transition-opacity duration-300" x-text="steps[step]"></span>
                        </div>

                        {{-- Shimmer skeleton bars --}}
                        <div class="space-y-1.5 pt-0.5" aria-hidden="true">
                            <div class="h-2 w-3/4 rounded bg-gradient-to-r from-ink-850 via-amber-500/20 to-ink-850 animate-pulse"></div>
                            <div class="h-2 w-1/2 rounded bg-gradient-to-r from-ink-850 via-amber-500/15 to-ink-850 animate-pulse" style="animation-delay: 0.2s;"></div>
                        </div>
                    </div>
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

    {{-- Input Form & Limit Lock --}}
    @auth
        @if($remainingToday <= 0)
            {{-- Persistent Lockout Bar with Countdown / Reset Time --}}
            <div class="px-4 py-3 bg-gradient-to-r from-rose-500/15 via-ink-950 to-rose-500/10 border-t border-rose-500/30 flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-btn bg-rose-500/20 border border-rose-500/40 text-rose-300 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-rose-200">
                            {{ __('ai.limit_locked_title', ['limit' => $dailyLimit]) }}
                        </p>
                        <p class="text-[11px] text-mist flex items-center gap-1.5 mt-0.5">
                            <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <span>{!! __('ai.limit_resets_in', ['time' => '<strong class="text-amber-400 font-mono">' . e($resetRemainingHuman) . '</strong>']) !!}</span>
                        </p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-badge bg-rose-500/20 border border-rose-500/40 font-mono text-[11px] text-rose-300 uppercase tracking-wider shrink-0 flex items-center gap-1">
                    <svg class="w-3 h-3 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                    </svg>
                    <span>{{ __('ai.locked_badge') }}</span>
                </span>
            </div>
        @endif

        <form
            wire:submit.prevent="ask"
            @submit.prevent="submitQuestion()"
            onsubmit="return false;"
            class="p-3.5 sm:p-4 bg-ink-950 border-t border-ink-border space-y-2 {{ $remainingToday <= 0 ? 'bg-ink-950/80 opacity-75' : '' }}"
        >
            <div class="flex items-stretch gap-2.5">
                <div class="flex-1 relative">
                    <textarea
                        wire:model.defer="question"
                        x-ref="questionInput"
                        @input="charCount = $el.value.length"
                        rows="2"
                        maxlength="{{ $maxChars }}"
                        placeholder="{{ $remainingToday <= 0 ? __('ai.limit_depleted_placeholder', ['time' => $resetRemainingHuman]) : __('ai.ask_placeholder') }}"
                        @if($remainingToday <= 0)
                            disabled
                        @else
                            @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); submitQuestion(); }"
                        @endif
                        class="ks-input w-full text-xs sm:text-sm pr-14 resize-none {{ $remainingToday <= 0 ? 'bg-ink-900/60 border-ink-border/50 text-mist/60 cursor-not-allowed select-none' : '' }}"
                    ></textarea>
                    <span
                        class="absolute bottom-2 right-2.5 text-[10px] font-mono text-mist pointer-events-none"
                        x-text="charCount + '/{{ $maxChars }}'"
                    ></span>
                </div>

                <button
                    type="button"
                    @if($remainingToday <= 0)
                        disabled
                        title="{{ __('ai.limit_depleted_tooltip', ['time' => $resetRemainingHuman]) }}"
                        class="px-4 sm:px-5 rounded-btn bg-ink-850/80 border border-ink-border/60 text-mist/40 text-xs sm:text-sm font-bold inline-flex items-center justify-center gap-1.5 shrink-0 self-stretch cursor-not-allowed shadow-none"
                    @else
                        @click="submitQuestion()"
                        :disabled="isSubmitting"
                        wire:loading.attr="disabled"
                        wire:target="ask"
                        class="ks-btn-primary px-4 sm:px-5 text-xs sm:text-sm font-bold inline-flex items-center justify-center gap-1.5 shrink-0 self-stretch disabled:opacity-50 disabled:cursor-not-allowed"
                    @endif
                >
                    @if($remainingToday <= 0)
                        <svg class="w-4 h-4 text-mist/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        <span>{{ __('ai.locked_button') }}</span>
                    @else
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="22" y1="2" x2="11" y2="13"/>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                        <span>{{ __('ai.send') }}</span>
                    @endif
                </button>
            </div>
        </form>
    @endauth

    <style>
        @keyframes aiBubbleIn {
            0% {
                opacity: 0;
                transform: translateY(12px) scale(0.985);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .animate-ai-bubble {
            animation: aiBubbleIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes aiTextReveal {
            0% {
                opacity: 0;
                transform: translateY(4px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-ai-bubble .book-ai-math-content {
            animation: aiTextReveal 0.3s ease-out forwards;
        }
    </style>

    <div wire:ignore>
        <script>
            if (typeof window.bookAiAssistantComponent !== 'function') {
                window.bookAiAssistantComponent = function(bookId) {
                    return {
                        bookId: bookId,
                        charCount: 0,
                        isSubmitting: false,
                        scrollToBottom() {
                            const box = this.$refs.messagesBox;
                            if (box) {
                                box.scrollTop = box.scrollHeight;
                            }
                        },
                        submitQuestion() {
                            if ({{ $remainingToday <= 0 ? 'true' : 'false' }}) return;
                            const input = this.$refs.questionInput;
                            const val = input ? input.value.trim() : '';
                            if (!val || this.isSubmitting) return;

                            this.isSubmitting = true;
                            try {
                                const res = this.$wire.ask(val);
                                if (res && typeof res.then === 'function') {
                                    res.then(() => {
                                        this.isSubmitting = false;
                                    }).catch(() => {
                                        this.isSubmitting = false;
                                    });
                                } else {
                                    setTimeout(() => { this.isSubmitting = false; }, 800);
                                }
                            } catch (e) {
                                this.isSubmitting = false;
                            }

                            input.value = '';
                            this.charCount = 0;
                            this.scrollToBottom();
                        },
                        jumpToBookPage(pageNum) {
                            const p = parseInt(pageNum, 10) || 1;
                            const readerEl = document.getElementById('online-reader');
                            if (readerEl) {
                                readerEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                            window.dispatchEvent(new CustomEvent('book-ai-jump-page', { detail: { page: p } }));
                            if (typeof window.fbGoToPage === 'function') {
                                setTimeout(() => window.fbGoToPage(p), 180);
                            }
                            const pdfFrame = document.getElementById('bookPdfFrame');
                            if (pdfFrame && pdfFrame.src) {
                                const base = pdfFrame.src.split('#')[0];
                                pdfFrame.src = base + '#page=' + p + '&view=FitH&pagemode=none';
                            }
                            const url = new URL(window.location.href);
                            url.searchParams.set('page', String(p));
                            window.history.replaceState({}, '', url.toString());
                        },
                        animateNewMessage() {
                            window.__bookAiSeenIds = window.__bookAiSeenIds || new Set();
                            const wrappers = this.$el.querySelectorAll('.ai-msg-wrapper[data-msg-id]');
                            wrappers.forEach((wrapper) => {
                                const msgId = wrapper.getAttribute('data-msg-id');
                                if (!msgId) return;

                                if (!window.__bookAiSeenIds.has(msgId)) {
                                    window.__bookAiSeenIds.add(msgId);
                                    wrapper.classList.add('animate-ai-bubble');

                                    const contentEl = wrapper.querySelector('.book-ai-math-content');
                                    if (contentEl && typeof window.renderBookAiMath === 'function') {
                                        window.renderBookAiMath(contentEl);
                                    }
                                }
                            });
                        },
                        initAssistant() {
                            window.__bookAiSeenIds = window.__bookAiSeenIds || new Set();
                            const wrappers = this.$el.querySelectorAll('.ai-msg-wrapper[data-msg-id]');
                            wrappers.forEach(w => {
                                const id = w.getAttribute('data-msg-id');
                                if (id) window.__bookAiSeenIds.add(id);
                            });
                            this.$nextTick(() => {
                                this.scrollToBottom();
                                if (typeof window.renderBookAiMath === 'function') {
                                    window.renderBookAiMath(this.$el);
                                }
                            });
                        },
                        onBookAiUpdated(detail) {
                            this.isSubmitting = false;
                            if (detail && detail.bookId == this.bookId) {
                                this.charCount = 0;
                                if (this.$refs.questionInput) {
                                    this.$refs.questionInput.value = '';
                                }
                                this.$nextTick(() => {
                                    this.animateNewMessage();
                                    this.scrollToBottom();
                                });
                            }
                        }
                    };
                };
            }

            function bookAiAssistant_{{ $bookId }}() {
                return window.bookAiAssistantComponent({{ (int) $bookId }});
            }
        </script>
    </div>
</div>
