{{-- ── Persistent Bottom Global Audio Player (Mutolaa-Style Dock) ── --}}
<div x-data="globalAudioPlayer()"
     x-cloak
     x-show="isVisible"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-8"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-8"
     class="select-none pointer-events-none"
     @play-global-audio.window="handlePlayTrack($event.detail)"
     @pause-global-audio.window="pause()"
     @toggle-global-audio.window="togglePlay()">

    <!-- Native HTML5 Audio Element -->
    <audio x-ref="audio"
           preload="auto"
           @play="onPlay()"
           @pause="onPause()"
           @timeupdate="onTimeUpdate()"
           @loadedmetadata="onLoadedMetadata()"
           @ended="onEnded()"
           x-on:error="onError()"
           class="hidden"></audio>

    <!-- ══════════════════════════════════════════════════════════════ -->
    <!-- 1. MINIMIZED FLOATING DISC (Agar kichraytirilgan bo'lsa)        -->
    <!-- ══════════════════════════════════════════════════════════════ -->
    <div x-show="isMinimized"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="scale-50 opacity-0"
         x-transition:enter-end="scale-100 opacity-100"
         class="fixed bottom-[74px] lg:bottom-5 right-4 z-50 pointer-events-auto">
        <button @click="isMinimized = false"
                class="relative group w-13 h-13 sm:w-14 sm:h-14 rounded-full bg-ink-950/95 border border-amber-400/40 p-1 shadow-2xl backdrop-blur-xl flex items-center justify-center ring-2 ring-amber-400/20 hover:scale-105 transition-transform"
                title="Audio pleyerni ochish">
            <div class="w-full h-full rounded-full overflow-hidden relative"
                 :class="isPlaying ? 'animate-[spin_10s_linear_infinite]' : ''">
                <img :src="currentTrack.coverUrl || '{{ asset('assets/images/default-cover.jpg') }}'"
                     :alt="currentTrack.title"
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-ink-950/30"></div>
                <div class="absolute inset-1/3 rounded-full bg-amber-400 border border-ink-950 flex items-center justify-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-ink-950"></span>
                </div>
            </div>
            <!-- Play/Pause Mini Badge -->
            <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-amber-400 text-ink-950 text-[10px] font-bold flex items-center justify-center shadow">
                <span x-show="isPlaying">❚❚</span>
                <span x-show="!isPlaying">▶</span>
            </span>
        </button>
    </div>

    <!-- ══════════════════════════════════════════════════════════════ -->
    <!-- 2. EXPANDED MUTOLAA DOCK (Pastki asosiy pleyer paneli)         -->
    <!-- ══════════════════════════════════════════════════════════════ -->
    <div x-show="!isMinimized"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-6"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="fixed bottom-[68px] lg:bottom-4 inset-x-2 sm:inset-x-6 max-w-4xl lg:max-w-5xl mx-auto z-50 pointer-events-auto">

        <!-- Chapters / Playlist Drawer (Slide-up panel) -->
        <div x-show="showPlaylist"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-4 scale-98"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 scale-98"
             @click.outside="showPlaylist = false"
             class="mb-2 w-full rounded-2xl bg-ink-950/95 border border-ink-border/80 shadow-[0_20px_50px_rgba(0,0,0,0.9)] backdrop-blur-2xl p-4 text-paper max-h-80 overflow-y-auto space-y-2 ring-1 ring-white/10">

            <div class="flex items-center justify-between border-b border-ink-border/60 pb-2">
                <div class="flex items-center gap-2">
                    <span class="text-amber-400 text-xs">▤</span>
                    <h4 class="text-xs font-bold font-serif text-paper">Mavjud audio boblar</h4>
                    <span class="text-[10px] font-mono text-mist" x-text="`(${playlist.length} ta audio)`"></span>
                </div>
                <button @click="showPlaylist = false" class="text-mist hover:text-paper text-xs p-1">✕</button>
            </div>

            <div class="divide-y divide-ink-border/50">
                <template x-for="(item, idx) in playlist" :key="item.id || idx">
                    <button @click="playPlaylistItem(item)"
                            class="w-full py-2.5 px-3 rounded-xl flex items-center justify-between text-left transition-colors group"
                            :class="currentTrack.id === item.id ? 'bg-amber-500/15 border border-amber-500/30' : 'hover:bg-white/5'">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-6 h-6 rounded-btn bg-ink-900 border border-ink-border text-[10px] font-mono flex items-center justify-center shrink-0"
                                  :class="currentTrack.id === item.id ? 'text-amber-400 border-amber-400/40 font-bold' : 'text-mist'">
                                <span x-show="currentTrack.id !== item.id" x-text="idx + 1"></span>
                                <span x-show="currentTrack.id === item.id && isPlaying" class="text-amber-400 animate-pulse">♫</span>
                                <span x-show="currentTrack.id === item.id && !isPlaying" class="text-amber-400">▶</span>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold truncate group-hover:text-amber-300"
                                   :class="currentTrack.id === item.id ? 'text-amber-400' : 'text-paper'"
                                   x-text="item.title || `Audio ${idx + 1}`"></p>
                                <p x-show="item.chapter_title" class="text-[10px] font-mono text-mist truncate" x-text="item.chapter_title"></p>
                            </div>
                        </div>
                        <span class="text-[11px] font-mono text-mist shrink-0 ml-2" x-text="formatTime(item.duration)"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Sleep Timer Popup -->
        <div x-show="showSleepMenu"
             x-transition
             @click.outside="showSleepMenu = false"
             class="absolute right-14 sm:right-28 bottom-full mb-3 w-48 rounded-xl bg-ink-950/95 border border-ink-border/80 shadow-2xl backdrop-blur-2xl p-2 text-paper space-y-1 text-xs ring-1 ring-white/10 z-50">
            <div class="px-2 py-1 text-[10px] font-mono uppercase tracking-wider text-mist border-b border-ink-border/60">
                Uyqu taymeri:
            </div>
            <button @click="setSleepTimer(0)" class="w-full text-left px-2.5 py-1.5 rounded-btn hover:bg-white/5 flex items-center justify-between" :class="sleepMinutes === 0 ? 'text-amber-400 font-bold' : 'text-mist'">
                <span>O'chirilgan</span>
                <span x-show="sleepMinutes === 0">✓</span>
            </button>
            <button @click="setSleepTimer(5)" class="w-full text-left px-2.5 py-1.5 rounded-btn hover:bg-white/5 flex items-center justify-between" :class="sleepMinutes === 5 ? 'text-amber-400 font-bold' : 'text-mist'">
                <span>5 daqiqa</span>
                <span x-show="sleepMinutes === 5">✓</span>
            </button>
            <button @click="setSleepTimer(15)" class="w-full text-left px-2.5 py-1.5 rounded-btn hover:bg-white/5 flex items-center justify-between" :class="sleepMinutes === 15 ? 'text-amber-400 font-bold' : 'text-mist'">
                <span>15 daqiqa</span>
                <span x-show="sleepMinutes === 15">✓</span>
            </button>
            <button @click="setSleepTimer(30)" class="w-full text-left px-2.5 py-1.5 rounded-btn hover:bg-white/5 flex items-center justify-between" :class="sleepMinutes === 30 ? 'text-amber-400 font-bold' : 'text-mist'">
                <span>30 daqiqa</span>
                <span x-show="sleepMinutes === 30">✓</span>
            </button>
            <button @click="setSleepTimer(60)" class="w-full text-left px-2.5 py-1.5 rounded-btn hover:bg-white/5 flex items-center justify-between" :class="sleepMinutes === 60 ? 'text-amber-400 font-bold' : 'text-mist'">
                <span>60 daqiqa</span>
                <span x-show="sleepMinutes === 60">✓</span>
            </button>
            <button @click="setSleepTimer('end')" class="w-full text-left px-2.5 py-1.5 rounded-btn hover:bg-white/5 flex items-center justify-between" :class="sleepMinutes === 'end' ? 'text-amber-400 font-bold' : 'text-mist'">
                <span>Bob yakunida</span>
                <span x-show="sleepMinutes === 'end'">✓</span>
            </button>
        </div>

        <!-- Main Dock Container -->
        <div class="relative rounded-2xl bg-[#0D1017]/95 border border-white/10 shadow-[0_-12px_45px_rgba(0,0,0,0.85)] backdrop-blur-2xl p-2.5 sm:px-4 sm:py-3 text-paper ring-1 ring-white/5">

            <!-- ── Top Scrubber / Progress Bar ── -->
            <div class="absolute -top-1 inset-x-3 sm:inset-x-4 h-2 group cursor-pointer flex items-center"
                 @click="seekFromBar($event)">
                <div class="w-full h-1 group-hover:h-1.5 bg-white/15 rounded-full overflow-hidden transition-all relative">
                    <div class="h-full bg-gradient-to-r from-amber-500 to-amber-400 rounded-full transition-all duration-100"
                         :style="`width: ${progressPercent}%`"></div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 sm:gap-6 pt-0.5">

                <!-- ── LEFT: Book Cover, Title, Chapter ── -->
                <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0 max-w-[38%] sm:max-w-[32%]">
                    <!-- Thumbnail with 2:3 ratio -->
                    <div class="w-9 sm:w-11 aspect-[2/3] rounded-md overflow-hidden bg-ink-900 border border-white/10 shadow-md shrink-0 cursor-pointer"
                         @click="goToBook()">
                        <img :src="currentTrack.coverUrl || '{{ asset('assets/images/default-cover.jpg') }}'"
                             :alt="currentTrack.title"
                             class="w-full h-full object-cover">
                    </div>

                    <!-- Titles & Live Soundwaves -->
                    <div class="min-w-0 flex-1">
                        <h4 class="text-xs sm:text-sm font-bold font-serif text-paper truncate cursor-pointer hover:text-amber-400 transition-colors"
                            @click="goToBook()"
                            x-text="currentTrack.title || 'Kitob nomi'"></h4>
                        
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="text-[10px] sm:text-[11px] font-mono text-mist truncate"
                                  x-text="currentTrack.chapterTitle || currentTrack.author || 'Audio'"></span>
                            
                            <!-- Bouncing mini equalizer bars when playing -->
                            <span x-show="isPlaying" class="flex items-end gap-0.5 h-3 shrink-0">
                                <span class="w-0.5 bg-amber-400 rounded-full h-2 animate-[pulse_0.8s_infinite]"></span>
                                <span class="w-0.5 bg-amber-400 rounded-full h-3 animate-[pulse_0.6s_infinite_0.2s]"></span>
                                <span class="w-0.5 bg-amber-400 rounded-full h-1.5 animate-[pulse_0.9s_infinite_0.4s]"></span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- ── CENTER: Playback Controls (Speed, 10s back, Play/Pause, 10s fwd, Timer) ── -->
                <div class="flex items-center justify-center gap-2 sm:gap-4 shrink-0">
                    
                    <!-- Speed Button -->
                    <button @click="changeSpeed()"
                            class="px-2 py-1 rounded-btn hover:bg-white/10 text-[11px] sm:text-xs font-mono font-bold text-mist hover:text-amber-400 transition-colors"
                            title="Ijro tezligi">
                        <span x-text="`${speed}x`"></span>
                    </button>

                    <!-- 10s Rewind -->
                    <button @click="skip(-10)"
                            class="p-1.5 sm:p-2 rounded-btn text-mist hover:text-paper hover:bg-white/5 transition-colors relative"
                            title="10 soniya orqaga">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.334 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z"/>
                        </svg>
                        <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 text-[8px] font-mono text-mist font-bold">10</span>
                    </button>

                    <!-- Large Vibrant Circular Play/Pause Button -->
                    <button @click="togglePlay()"
                            class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-amber-400 hover:bg-amber-300 text-ink-950 flex items-center justify-center font-bold shadow-lg shadow-amber-400/25 active:scale-95 transition-all shrink-0"
                            :title="isPlaying ? 'To\'xtatish' : 'Tinglash'">
                        <template x-if="!isPlaying">
                            <svg class="w-5 h-5 ml-0.5 fill-current" viewBox="0 0 24 24"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                        </template>
                        <template x-if="isPlaying">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                        </template>
                    </button>

                    <!-- 10s Forward -->
                    <button @click="skip(10)"
                            class="p-1.5 sm:p-2 rounded-btn text-mist hover:text-paper hover:bg-white/5 transition-colors relative"
                            title="10 soniya oldinga">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.934 12.8a1 1 0 000-1.6l-5.334-4A1 1 0 005 8v8a1 1 0 001.6.8l5.334-4zM19.934 12.8a1 1 0 000-1.6l-5.334-4A1 1 0 0013 8v8a1 1 0 001.6.8l5.334-4z"/>
                        </svg>
                        <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 text-[8px] font-mono text-mist font-bold">10</span>
                    </button>

                    <!-- Sleep Timer Toggle Button -->
                    <button @click="showSleepMenu = !showSleepMenu"
                            class="p-1.5 sm:p-2 rounded-btn hover:bg-white/5 transition-colors relative"
                            :class="sleepRemainingSec > 0 ? 'text-amber-400 font-bold' : 'text-mist hover:text-paper'"
                            title="Uyqu taymeri">
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <span x-show="sleepRemainingSec > 0"
                              class="absolute -top-1 -right-1 text-[8px] font-mono bg-amber-400 text-ink-950 font-bold px-1 rounded-full"
                              x-text="`${Math.ceil(sleepRemainingSec / 60)}m`"></span>
                    </button>
                </div>

                <!-- ── RIGHT: Time, Volume, Chapters, Share, Close ── -->
                <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
                    
                    <!-- Time Display -->
                    <div class="hidden md:flex items-center gap-1 text-[11px] font-mono text-mist">
                        <span x-text="formatTime(currentTime)">0:00</span>
                        <span>/</span>
                        <span x-text="formatTime(duration)">0:00</span>
                    </div>

                    <!-- Volume Mute / Slider -->
                    <div class="relative flex items-center group">
                        <button @click="toggleMute()"
                                class="p-1.5 sm:p-2 rounded-btn text-mist hover:text-paper hover:bg-white/5 transition-colors"
                                :title="isMuted ? 'Ovozni yoqish' : 'Ovozni o\'chirish'">
                            <span x-show="!isMuted && volume > 0.4">🔊</span>
                            <span x-show="!isMuted && volume <= 0.4 && volume > 0">🔉</span>
                            <span x-show="isMuted || volume === 0">🔇</span>
                        </button>
                        
                        <!-- Mini volume slider on hover (desktop) -->
                        <div class="hidden lg:group-hover:flex items-center w-20 px-1 py-1">
                            <input type="range" min="0" max="1" step="0.05"
                                   :value="isMuted ? 0 : volume"
                                   @input="setVolume($event.target.value)"
                                   class="w-full h-1 bg-ink-800 accent-amber-400 rounded-full cursor-pointer">
                        </div>
                    </div>

                    <!-- Chapters / Playlist Drawer Button -->
                    <button @click="togglePlaylist()"
                            class="p-1.5 sm:p-2 rounded-btn text-mist hover:text-paper hover:bg-white/5 transition-colors relative"
                            :class="showPlaylist ? 'text-amber-400 bg-white/5' : ''"
                            title="Barcha boblar">
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <line x1="8" y1="6" x2="21" y2="6"/>
                            <line x1="8" y1="12" x2="21" y2="12"/>
                            <line x1="8" y1="18" x2="21" y2="18"/>
                            <line x1="3" y1="6" x2="3.01" y2="6"/>
                            <line x1="3" y1="12" x2="3.01" y2="12"/>
                            <line x1="3" y1="18" x2="3.01" y2="18"/>
                        </svg>
                        <span x-show="playlist.length > 1"
                              class="absolute -top-1 -right-0.5 text-[8px] font-mono px-1 rounded-full bg-ink-800 text-mist border border-ink-border"
                              x-text="playlist.length"></span>
                    </button>

                    <!-- Share Button (Mutolaa uslubida) -->
                    <button @click="shareCurrentBook()"
                            class="p-1.5 sm:p-2 rounded-btn text-mist hover:text-amber-400 hover:bg-white/5 transition-colors"
                            title="Kitobni ulashish">
                        <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="18" cy="5" r="3"/>
                            <circle cx="6" cy="12" r="3"/>
                            <circle cx="18" cy="19" r="3"/>
                            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                        </svg>
                    </button>

                    <!-- Minimize Button -->
                    <button @click="isMinimized = true"
                            class="hidden sm:inline-flex p-1.5 sm:p-2 rounded-btn text-mist hover:text-paper hover:bg-white/5 transition-colors"
                            title="Kichraytirish">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </button>

                    <!-- Close / Dismiss Button -->
                    <button @click="close()"
                            class="p-1.5 sm:p-2 rounded-btn text-mist hover:text-rose-400 hover:bg-white/5 transition-colors"
                            title="Yopish">
                        ✕
                    </button>

                </div>

            </div>
        </div>

    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('globalAudioPlayer', () => ({
        isVisible: false,
        isMinimized: false,
        isPlaying: false,
        currentTime: 0,
        duration: 0,
        speed: 1,
        speeds: [0.75, 1, 1.25, 1.5, 2],
        volume: 1,
        isMuted: false,
        showPlaylist: false,
        showSleepMenu: false,
        sleepMinutes: 0,
        sleepRemainingSec: 0,
        sleepInterval: null,

        currentTrack: {
            id: null,
            bookId: null,
            title: '',
            author: '',
            coverUrl: '',
            audioUrl: '',
            chapterTitle: '',
            shareUrl: '',
            duration: 0
        },
        playlist: [],

        init() {
            // 1. Rehydrate playback state from localStorage if exists
            this.rehydrateState();

            // 2. Window unload: save state
            window.addEventListener('beforeunload', () => {
                this.saveState();
            });

            // 3. Interval: persist every 2 seconds while playing
            setInterval(() => {
                if (this.isVisible && this.isPlaying) {
                    this.saveState();
                }
            }, 2000);
        },

        get progressPercent() {
            if (!this.duration || this.duration <= 0) return 0;
            return Math.min(100, Math.max(0, (this.currentTime / this.duration) * 100));
        },

        formatTime(sec) {
            if (!sec || sec < 0 || isNaN(sec)) return '0:00';
            const m = Math.floor(sec / 60);
            const s = Math.floor(sec % 60).toString().padStart(2, '0');
            return `${m}:${s}`;
        },

        handlePlayTrack(data) {
            if (!data || !data.audioUrl) return;

            const audioEl = this.$refs.audio;
            const isSameTrack = this.currentTrack.audioUrl === data.audioUrl;

            this.currentTrack = {
                id: data.id || null,
                bookId: data.bookId || null,
                title: data.title || 'Audio kitob',
                author: data.author || '',
                coverUrl: data.coverUrl || '',
                audioUrl: data.audioUrl,
                chapterTitle: data.chapterTitle || '',
                shareUrl: data.shareUrl || window.location.href,
                duration: data.duration || 0
            };

            this.isVisible = true;
            this.isMinimized = false;

            if (isSameTrack && audioEl.src) {
                this.togglePlay();
            } else {
                audioEl.src = data.audioUrl;
                audioEl.playbackRate = this.speed;
                audioEl.volume = this.isMuted ? 0 : this.volume;
                audioEl.currentTime = data.startTime || 0;
                
                const playPromise = audioEl.play();
                if (playPromise !== undefined) {
                    playPromise.catch(() => {
                        this.isPlaying = false;
                    });
                }
            }

            // Fetch playlist for this book if bookId is available
            if (data.bookId) {
                this.fetchBookPlaylist(data.bookId);
            }

            this.saveState();
        },

        async fetchBookPlaylist(bookId) {
            try {
                const res = await fetch(`/books/${bookId}/audios-json`);
                if (res.ok) {
                    const data = await res.json();
                    if (data && data.audios) {
                        this.playlist = data.audios.map(a => ({
                            id: a.id,
                            title: a.title,
                            duration: a.duration,
                            audioUrl: a.file_url,
                            chapter_title: a.chapter_title,
                            bookId: data.book.id,
                            bookTitle: data.book.title,
                            author: data.book.author,
                            coverUrl: data.book.cover_url,
                            shareUrl: data.book.share_url
                        }));
                    }
                }
            } catch(e) {}
        },

        playPlaylistItem(item) {
            this.handlePlayTrack({
                id: item.id,
                bookId: item.bookId || this.currentTrack.bookId,
                title: item.bookTitle || this.currentTrack.title,
                author: item.author || this.currentTrack.author,
                coverUrl: item.coverUrl || this.currentTrack.coverUrl,
                audioUrl: item.audioUrl || item.file_url,
                chapterTitle: item.title,
                shareUrl: item.shareUrl || this.currentTrack.shareUrl,
                duration: item.duration || 0
            });
            this.showPlaylist = false;
        },

        togglePlay() {
            const audioEl = this.$refs.audio;
            if (!audioEl.src) return;

            if (this.isPlaying) {
                audioEl.pause();
            } else {
                audioEl.play().catch(() => {});
            }
        },

        pause() {
            const audioEl = this.$refs.audio;
            if (audioEl) audioEl.pause();
        },

        skip(sec) {
            const audioEl = this.$refs.audio;
            if (!audioEl) return;
            audioEl.currentTime = Math.max(0, Math.min(this.duration || Infinity, audioEl.currentTime + sec));
        },

        changeSpeed() {
            const idx = this.speeds.indexOf(this.speed);
            this.speed = this.speeds[(idx + 1) % this.speeds.length];
            if (this.$refs.audio) {
                this.$refs.audio.playbackRate = this.speed;
            }
            this.saveState();
        },

        toggleMute() {
            this.isMuted = !this.isMuted;
            if (this.$refs.audio) {
                this.$refs.audio.volume = this.isMuted ? 0 : this.volume;
            }
        },

        setVolume(val) {
            this.volume = parseFloat(val);
            this.isMuted = this.volume === 0;
            if (this.$refs.audio) {
                this.$refs.audio.volume = this.volume;
            }
        },

        seekFromBar(event) {
            const rect = event.currentTarget.getBoundingClientRect();
            const clickX = event.clientX - rect.left;
            const percent = Math.max(0, Math.min(1, clickX / rect.width));
            if (this.duration && this.$refs.audio) {
                this.$refs.audio.currentTime = percent * this.duration;
            }
        },

        togglePlaylist() {
            this.showPlaylist = !this.showPlaylist;
            if (this.showPlaylist && this.playlist.length === 0 && this.currentTrack.bookId) {
                this.fetchBookPlaylist(this.currentTrack.bookId);
            }
        },

        setSleepTimer(minutes) {
            this.sleepMinutes = minutes;
            this.showSleepMenu = false;
            if (this.sleepInterval) clearInterval(this.sleepInterval);

            if (minutes === 0) {
                this.sleepRemainingSec = 0;
                return;
            }

            if (minutes === 'end') {
                this.sleepRemainingSec = Math.max(0, Math.round(this.duration - this.currentTime));
            } else {
                this.sleepRemainingSec = minutes * 60;
            }

            this.sleepInterval = setInterval(() => {
                this.sleepRemainingSec--;
                if (this.sleepRemainingSec <= 0) {
                    clearInterval(this.sleepInterval);
                    this.pause();
                    this.sleepMinutes = 0;
                    this.sleepRemainingSec = 0;
                    if (window.showToast) {
                        window.showToast('Uyqu taymeri: audio to\'xtatildi', 'info');
                    }
                }
            }, 1000);
        },

        goToBook() {
            if (this.currentTrack.shareUrl) {
                window.location.href = this.currentTrack.shareUrl;
            } else if (this.currentTrack.bookId) {
                window.location.href = `/books/${this.currentTrack.bookId}`;
            }
        },

        shareCurrentBook() {
            if (window.openBookShare) {
                window.openBookShare({
                    title: this.currentTrack.title,
                    author: this.currentTrack.author,
                    url: this.currentTrack.shareUrl || window.location.href,
                    coverUrl: this.currentTrack.coverUrl,
                    description: `${this.currentTrack.title} — ${this.currentTrack.chapterTitle || ''}`
                });
            }
        },

        close() {
            this.pause();
            this.isVisible = false;
            this.isMinimized = false;
            try {
                localStorage.removeItem('kitobxon_audio_state');
            } catch(e) {}
        },

        onPlay() {
            this.isPlaying = true;
            this.saveState();
        },

        onPause() {
            this.isPlaying = false;
            this.saveState();
        },

        onTimeUpdate() {
            if (this.$refs.audio) {
                this.currentTime = this.$refs.audio.currentTime;
            }
        },

        onLoadedMetadata() {
            if (this.$refs.audio) {
                this.duration = this.$refs.audio.duration || this.currentTrack.duration || 0;
            }
        },

        onEnded() {
            this.isPlaying = false;
            if (this.sleepMinutes === 'end') {
                this.setSleepTimer(0);
                return;
            }

            // Autoplay next chapter if in playlist
            const currentIndex = this.playlist.findIndex(p => p.id === this.currentTrack.id);
            if (currentIndex !== -1 && currentIndex + 1 < this.playlist.length) {
                this.playPlaylistItem(this.playlist[currentIndex + 1]);
            }
        },

        onError() {
            this.isPlaying = false;
        },

        saveState() {
            try {
                const state = {
                    currentTrack: this.currentTrack,
                    currentTime: this.$refs.audio ? this.$refs.audio.currentTime : this.currentTime,
                    duration: this.duration,
                    speed: this.speed,
                    volume: this.volume,
                    isMuted: this.isMuted,
                    isPlaying: this.isPlaying,
                    isVisible: this.isVisible,
                    isMinimized: this.isMinimized,
                    timestamp: Date.now()
                };
                localStorage.setItem('kitobxon_audio_state', JSON.stringify(state));
            } catch(e) {}
        },

        rehydrateState() {
            try {
                const saved = localStorage.getItem('kitobxon_audio_state');
                if (!saved) return;
                const state = JSON.parse(saved);

                // Ignore if older than 24 hours
                if (Date.now() - state.timestamp > 24 * 3600 * 1000) {
                    localStorage.removeItem('kitobxon_audio_state');
                    return;
                }

                if (state.currentTrack && state.currentTrack.audioUrl) {
                    this.currentTrack = state.currentTrack;
                    this.currentTime = state.currentTime || 0;
                    this.duration = state.duration || 0;
                    this.speed = state.speed || 1;
                    this.volume = state.volume || 1;
                    this.isMuted = state.isMuted || false;
                    this.isVisible = state.isVisible || false;
                    this.isMinimized = state.isMinimized || false;

                    const audioEl = this.$refs.audio;
                    if (audioEl) {
                        audioEl.src = state.currentTrack.audioUrl;
                        audioEl.currentTime = this.currentTime;
                        audioEl.playbackRate = this.speed;
                        audioEl.volume = this.isMuted ? 0 : this.volume;

                        // If it was playing prior to reload, attempt to resume
                        if (state.isPlaying) {
                            const p = audioEl.play();
                            if (p !== undefined) {
                                p.then(() => {
                                    this.isPlaying = true;
                                }).catch(() => {
                                    this.isPlaying = false;
                                    // Browser autoplay policy blocked: wait for first user gesture
                                    const resumeOnTouch = () => {
                                        if (this.isVisible && !this.isPlaying) {
                                            audioEl.play().then(() => { this.isPlaying = true; }).catch(() => {});
                                        }
                                        window.removeEventListener('click', resumeOnTouch);
                                        window.removeEventListener('keydown', resumeOnTouch);
                                    };
                                    window.addEventListener('click', resumeOnTouch, { once: true });
                                    window.addEventListener('keydown', resumeOnTouch, { once: true });
                                });
                            }
                        }
                    }

                    if (state.currentTrack.bookId) {
                        this.fetchBookPlaylist(state.currentTrack.bookId);
                    }
                }
            } catch(e) {}
        }
    }));
});

// Global API available from anywhere on the platform
window.playGlobalAudio = function(trackData) {
    window.dispatchEvent(new CustomEvent('play-global-audio', { detail: trackData }));
};

window.pauseGlobalAudio = function() {
    window.dispatchEvent(new CustomEvent('pause-global-audio'));
};

window.toggleGlobalAudio = function() {
    window.dispatchEvent(new CustomEvent('toggle-global-audio'));
};
</script>
