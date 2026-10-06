{{-- ── Reusable Book Share Modal (Ulashish Modali) ── --}}
<div x-data="bookShareModal()"
     x-cloak
     @open-book-share.window="open($event.detail)"
     class="relative z-50">

    <!-- Backdrop Overlay -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-ink-950/80 backdrop-blur-md"
         @click="close()"></div>

    <!-- Modal Container -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none select-none">

        <div class="pointer-events-auto w-full max-w-md rounded-2xl bg-ink-900/95 border border-ink-border/80 shadow-[0_20px_60px_rgba(0,0,0,0.8)] backdrop-blur-2xl p-5 sm:p-6 text-paper space-y-5 ring-1 ring-white/10"
             @click.outside="close()">

            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-ink-border/70 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-btn bg-amber-500/15 border border-amber-500/25 text-amber-400 flex items-center justify-center text-sm">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="18" cy="5" r="3"/>
                            <circle cx="6" cy="12" r="3"/>
                            <circle cx="18" cy="19" r="3"/>
                            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold font-serif text-paper">Kitobni ulashish</h3>
                        <p class="text-[11px] font-mono text-mist">Do'stlaringiz bilan qimmatli mutolaani baham ko'ring</p>
                    </div>
                </div>

                <button @click="close()"
                        class="w-7 h-7 rounded-btn text-mist hover:text-paper hover:bg-white/5 flex items-center justify-center transition-colors text-sm"
                        title="Yopish">
                    ✕
                </button>
            </div>

            <!-- Book Card Mini Preview -->
            <div class="p-3 rounded-xl bg-ink-950/80 border border-ink-border flex items-center gap-3">
                <div class="w-11 aspect-[2/3] rounded-md overflow-hidden bg-ink-900 border border-ink-border shrink-0 shadow">
                    <img :src="book.coverUrl || '{{ asset('assets/images/default-cover.jpg') }}'"
                         :alt="book.title"
                         class="w-full h-full object-cover">
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="text-xs sm:text-sm font-bold font-serif text-paper truncate" x-text="book.title || 'Kitob nomi'"></h4>
                    <p class="text-[11px] font-sans text-mist truncate mt-0.5" x-text="book.author || 'Muallif'"></p>
                    <span class="inline-block mt-1 px-1.5 py-0.5 rounded-badge bg-amber-500/10 border border-amber-500/20 text-amber-400 font-mono text-[9px] uppercase tracking-wider">
                        Kitobxon Platformasi
                    </span>
                </div>
            </div>

            <!-- Social Share Channels Grid -->
            <div class="space-y-2">
                <span class="text-[11px] font-mono uppercase tracking-wider text-mist block">Ijtimoiy tarmoqlar:</span>
                <div class="grid grid-cols-4 gap-2">
                    <!-- Telegram -->
                    <button @click="shareTo('telegram')"
                            class="flex flex-col items-center justify-center gap-1.5 p-2.5 rounded-xl bg-ink-950 hover:bg-[#229ED9]/15 border border-ink-border hover:border-[#229ED9]/40 text-mist hover:text-[#229ED9] transition-all group">
                        <div class="w-8 h-8 rounded-full bg-[#229ED9]/15 text-[#229ED9] flex items-center justify-center transition-transform group-hover:scale-110">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.75-.55 2.92-1.27 4.86-2.11 5.83-2.52 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06-.01.19-.03.32z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-mono">Telegram</span>
                    </button>

                    <!-- WhatsApp -->
                    <button @click="shareTo('whatsapp')"
                            class="flex flex-col items-center justify-center gap-1.5 p-2.5 rounded-xl bg-ink-950 hover:bg-[#25D366]/15 border border-ink-border hover:border-[#25D366]/40 text-mist hover:text-[#25D366] transition-all group">
                        <div class="w-8 h-8 rounded-full bg-[#25D366]/15 text-[#25D366] flex items-center justify-center transition-transform group-hover:scale-110">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 012.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.196 8.196 0 01-1.26-4.36c0-4.54 3.7-8.24 8.24-8.24m4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.24-.74-.66-1.24-1.48-1.39-1.73-.14-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.13-.15.17-.25.25-.42.08-.17.04-.32-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.78.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.06-.1-.23-.17-.48-.29z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-mono">WhatsApp</span>
                    </button>

                    <!-- Twitter / X -->
                    <button @click="shareTo('twitter')"
                            class="flex flex-col items-center justify-center gap-1.5 p-2.5 rounded-xl bg-ink-950 hover:bg-white/10 border border-ink-border hover:border-white/30 text-mist hover:text-paper transition-all group">
                        <div class="w-8 h-8 rounded-full bg-white/10 text-paper flex items-center justify-center transition-transform group-hover:scale-110">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-mono">X (Twitter)</span>
                    </button>

                    <!-- Native / Ko'proq -->
                    <button @click="shareNative()"
                            class="flex flex-col items-center justify-center gap-1.5 p-2.5 rounded-xl bg-ink-950 hover:bg-amber-500/15 border border-ink-border hover:border-amber-500/30 text-mist hover:text-amber-400 transition-all group">
                        <div class="w-8 h-8 rounded-full bg-amber-500/15 text-amber-400 flex items-center justify-center transition-transform group-hover:scale-110">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-mono">Boshqalar</span>
                    </button>
                </div>
            </div>

            <!-- Direct Link Copy Input -->
            <div class="space-y-1.5">
                <span class="text-[11px] font-mono uppercase tracking-wider text-mist block">Havola orqali ulashish:</span>
                <div class="flex items-center gap-2 p-1.5 rounded-xl bg-ink-950 border border-ink-border">
                    <input type="text"
                           readonly
                           :value="book.url"
                           class="flex-1 bg-transparent border-0 px-2.5 py-1 text-xs font-mono text-mist focus:outline-none focus:ring-0 truncate"
                           @click="$event.target.select()">
                    
                    <button @click="copyLink()"
                            class="px-3.5 py-1.5 rounded-btn bg-amber-400 hover:bg-amber-300 text-ink-950 font-bold text-xs flex items-center gap-1.5 shadow transition-all active:scale-95 shrink-0">
                        <span x-show="!copied">Nusxalash</span>
                        <span x-show="copied" class="text-ink-950 font-bold flex items-center gap-1">
                            ✓ Nusxalandi
                        </span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('bookShareModal', () => ({
        isOpen: false,
        copied: false,
        book: {
            title: '',
            author: '',
            url: '',
            coverUrl: '',
            description: ''
        },

        open(data = {}) {
            this.book = {
                title: data.title || document.title,
                author: data.author || '',
                url: data.url || window.location.href,
                coverUrl: data.coverUrl || '',
                description: data.description || 'Kitobxon platformasida ushbu asarni mutolaa qiling!'
            };
            this.copied = false;
            this.isOpen = true;
        },

        close() {
            this.isOpen = false;
        },

        shareNative() {
            if (navigator.share) {
                navigator.share({
                    title: this.book.title,
                    text: `${this.book.title} — ${this.book.author}\nKitobxon platformasida mutolaa qiling!`,
                    url: this.book.url
                }).catch(() => {});
            } else {
                this.copyLink();
            }
        },

        shareTo(network) {
            const text = encodeURIComponent(`📚 «${this.book.title}» (${this.book.author})\nKitobxon platformasida o'qing va tinglang:`);
            const url = encodeURIComponent(this.book.url);
            let shareUrl = '';

            switch(network) {
                case 'telegram':
                    shareUrl = `https://t.me/share/url?url=${url}&text=${text}`;
                    break;
                case 'whatsapp':
                    shareUrl = `https://api.whatsapp.com/send?text=${text}%20${url}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${text}`;
                    break;
            }

            if (shareUrl) {
                window.open(shareUrl, '_blank', 'noopener,noreferrer,width=600,height=500');
            }
        },

        copyLink() {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(this.book.url).then(() => {
                    this.copied = true;
                    if (window.showToast) {
                        window.showToast('Kitob havolasi nusxalandi!', 'success');
                    }
                    setTimeout(() => { this.copied = false; }, 2500);
                }).catch(() => {
                    this.fallbackCopy();
                });
            } else {
                this.fallbackCopy();
            }
        },

        fallbackCopy() {
            const input = document.createElement('input');
            input.value = this.book.url;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            this.copied = true;
            if (window.showToast) {
                window.showToast('Kitob havolasi nusxalandi!', 'success');
            }
            setTimeout(() => { this.copied = false; }, 2500);
        }
    }));
});

// Helper available anywhere globally
window.openBookShare = function(bookData = {}) {
    window.dispatchEvent(new CustomEvent('open-book-share', { detail: bookData }));
};
</script>
