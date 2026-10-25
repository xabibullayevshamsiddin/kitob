{{--
    Universal Custom Confirmation Modal (Kitobxon Master Design System)
    Barcha joyda browserning xunuk confirm() o'rniga saytning o'z hashamatli dizayndagi modali chiqadi.
--}}
<div x-data="kitobxonConfirmManager()"
     x-cloak
     @kitobxon:confirm.window="open($event.detail)"
     @keydown.escape.window="if(isOpen) cancel()"
     class="relative z-[10000]">

    <!-- Backdrop Overlay -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-ink-950/80 backdrop-blur-md"
         @click="cancel()"></div>

    <!-- Modal Dialog -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 scale-95 translate-y-3"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-3"
         class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none select-none">

        <div class="pointer-events-auto w-full max-w-md rounded-2xl bg-ink-900 border border-ink-border/90 shadow-[0_25px_70px_rgba(0,0,0,0.85)] p-5 sm:p-6 text-paper space-y-4 ring-1 ring-white/10"
             @click.outside="cancel()"
             role="dialog"
             aria-modal="true"
             :aria-label="title">

            <!-- Modal Header -->
            <div class="flex items-start justify-between border-b border-ink-border/70 pb-3.5 gap-3">
                <div class="flex items-center gap-3">
                    <!-- Warning Icon -->
                    <template x-if="type === 'warning'">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    </template>

                    <!-- Danger Icon -->
                    <template x-if="type === 'danger'">
                        <div class="w-10 h-10 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                    </template>

                    <!-- Info Icon -->
                    <template x-if="type === 'info'">
                        <div class="w-10 h-10 rounded-xl bg-sky-500/15 border border-sky-500/30 text-sky-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </template>

                    <!-- Success Icon -->
                    <template x-if="type === 'success'">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </template>

                    <div>
                        <h3 class="text-sm sm:text-base font-bold font-serif text-paper leading-tight" x-text="title"></h3>
                        <p class="text-[11px] font-mono text-mist mt-0.5">Tasdiqlash oynasi</p>
                    </div>
                </div>

                <button type="button"
                        @click="cancel()"
                        class="w-7 h-7 rounded-btn text-mist hover:text-paper hover:bg-white/5 flex items-center justify-center transition-colors text-sm cursor-pointer shrink-0"
                        title="Yopish">
                    ✕
                </button>
            </div>

            <!-- Modal Body / Message -->
            <div class="text-xs sm:text-sm text-paper/90 leading-relaxed whitespace-pre-line bg-ink-950/70 border border-ink-border/70 rounded-xl p-3.5 sm:p-4 font-sans select-text"
                 x-text="message">
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-1">
                <button type="button"
                        @click="cancel()"
                        class="px-4 py-2.5 text-xs font-semibold rounded-btn border border-ink-border bg-ink-800 hover:bg-ink-700 text-paper transition-all cursor-pointer">
                    <span x-text="cancelText"></span>
                </button>

                <button type="button"
                        x-ref="confirmButton"
                        @click="confirm()"
                        class="px-4 py-2.5 text-xs font-bold rounded-btn transition-all cursor-pointer flex items-center gap-1.5 shadow-md"
                        :class="{
                            'bg-amber-500 hover:bg-amber-400 text-ink-950': type === 'warning',
                            'bg-rose-600 hover:bg-rose-500 text-white': type === 'danger',
                            'bg-sky-500 hover:bg-sky-400 text-ink-950': type === 'info',
                            'bg-emerald-500 hover:bg-emerald-400 text-ink-950': type === 'success'
                        }">
                    <span x-text="confirmText"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function kitobxonConfirmManager() {
        return {
            isOpen: false,
            title: 'Amalni tasdiqlang',
            message: 'Ushbu amalni bajarishni tasdiqlaysizmi?',
            confirmText: 'Tasdiqlash',
            cancelText: 'Bekor qilish',
            type: 'warning',
            resolvePromise: null,

            open(opts) {
                this.title = opts.title || 'Amalni tasdiqlang';
                this.message = opts.message || opts.text || 'Ushbu amalni bajarishni tasdiqlaysizmi?';
                this.confirmText = opts.confirmText || 'Tasdiqlash';
                this.cancelText = opts.cancelText || 'Bekor qilish';
                this.type = opts.type || 'warning';
                this.resolvePromise = opts.resolve || null;
                this.isOpen = true;

                this.$nextTick(() => {
                    if (this.$refs.confirmButton) {
                        this.$refs.confirmButton.focus();
                    }
                });
            },

            confirm() {
                const res = this.resolvePromise;
                this.isOpen = false;
                this.resolvePromise = null;
                if (typeof res === 'function') {
                    res(true);
                }
            },

            cancel() {
                const res = this.resolvePromise;
                this.isOpen = false;
                this.resolvePromise = null;
                if (typeof res === 'function') {
                    res(false);
                }
            }
        };
    }

    // Global promise-based API: await window.confirmModal({ ... })
    window.confirmModal = function(options) {
        if (typeof options === 'string') {
            options = { message: options };
        }
        return new Promise(function(resolve) {
            window.dispatchEvent(new CustomEvent('kitobxon:confirm', {
                detail: Object.assign({}, options, { resolve: resolve })
            }));
        });
    };

    // Auto-interceptor for [data-confirm] on forms & buttons + upgrade legacy confirm()
    (function() {
        function upgradeLegacyInlineConfirms() {
            try {
                document.querySelectorAll('[onsubmit*="confirm("], [onclick*="confirm("]').forEach(function(el) {
                    var attr = el.hasAttribute('onsubmit') ? 'onsubmit' : 'onclick';
                    var val = el.getAttribute(attr);
                    if (!val) return;

                    var m = val.match(/confirm\(\s*(['"`])([\s\S]*?)\1\s*\)/);
                    if (m && m[2]) {
                        var raw = m[2].replace(/\\'/g, "'").replace(/\\"/g, '"').replace(/\\n/g, '\n');
                        el.setAttribute('data-confirm', raw);

                        var lower = raw.toLowerCase();
                        if (lower.indexOf('o\'chir') !== -1 || lower.indexOf('blok') !== -1 || lower.indexOf('tark') !== -1 || lower.indexOf('uzmoq') !== -1) {
                            el.setAttribute('data-confirm-type', 'danger');
                            el.setAttribute('data-confirm-btn', 'Ha, o\'chirish');
                            el.setAttribute('data-confirm-title', 'O\'chirishni tasdiqlang');
                        } else if (lower.indexOf('texnik xizmat') !== -1) {
                            el.setAttribute('data-confirm-type', 'warning');
                            el.setAttribute('data-confirm-btn', 'Ha, rejimni yoqish');
                            el.setAttribute('data-confirm-title', 'Texnik xizmat rejimini yoqish');
                        } else {
                            el.setAttribute('data-confirm-type', 'warning');
                            el.setAttribute('data-confirm-btn', 'Tasdiqlash');
                        }

                        el.removeAttribute(attr);
                    }
                });
            } catch(e) {}
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', upgradeLegacyInlineConfirms);
        } else {
            upgradeLegacyInlineConfirms();
        }

        // Re-run on Livewire navigation/mutation
        document.addEventListener('livewire:load', upgradeLegacyInlineConfirms);
        document.addEventListener('livewire:navigated', upgradeLegacyInlineConfirms);

        // Intercept form submissions with data-confirm
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (!form || !form.getAttribute) return;

            var msg = form.getAttribute('data-confirm');
            if (msg && !form._kitobxonConfirmed) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var title = form.getAttribute('data-confirm-title') || 'Amalni tasdiqlang';
                var type = form.getAttribute('data-confirm-type') || 'warning';
                var btn = form.getAttribute('data-confirm-btn') || 'Tasdiqlash';

                window.confirmModal({
                    title: title,
                    message: msg,
                    type: type,
                    confirmText: btn
                }).then(function(confirmed) {
                    if (confirmed) {
                        form._kitobxonConfirmed = true;
                        if (typeof form.requestSubmit === 'function') {
                            form.requestSubmit();
                        } else {
                            form.submit();
                        }
                        setTimeout(function() { form._kitobxonConfirmed = false; }, 2000);
                    }
                });
            }
        }, true);

        // Intercept clicks on buttons/links with data-confirm
        document.addEventListener('click', function(e) {
            var el = e.target.closest ? e.target.closest('[data-confirm]') : null;
            if (!el) return;
            if (el.tagName === 'FORM') return; // Handled by submit listener
            if (el._kitobxonConfirmed) return; // Already confirmed

            var msg = el.getAttribute('data-confirm');
            if (!msg) return;

            e.preventDefault();
            e.stopImmediatePropagation();

            var title = el.getAttribute('data-confirm-title') || 'Amalni tasdiqlang';
            var type = el.getAttribute('data-confirm-type') || 'warning';
            var btn = el.getAttribute('data-confirm-btn') || 'Tasdiqlash';

            window.confirmModal({
                title: title,
                message: msg,
                type: type,
                confirmText: btn
            }).then(function(confirmed) {
                if (confirmed) {
                    el._kitobxonConfirmed = true;
                    if (el.tagName === 'A' && el.href) {
                        window.location.href = el.href;
                    } else if (el.type === 'submit') {
                        var parentForm = el.closest('form');
                        if (parentForm) {
                            parentForm._kitobxonConfirmed = true;
                            if (typeof parentForm.requestSubmit === 'function') {
                                parentForm.requestSubmit(el);
                            } else {
                                parentForm.submit();
                            }
                            setTimeout(function() { parentForm._kitobxonConfirmed = false; }, 2000);
                        }
                    } else {
                        el.click();
                    }
                    setTimeout(function() { el._kitobxonConfirmed = false; }, 2000);
                }
            });
        }, true);
    })();
</script>
