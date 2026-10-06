@props([
    'bookId' => null,
    'chapterId' => null,
    'pageType' => 'book', // 'book', 'flipbook', 'chapter', 'audio', 'quiz', 'video'
])

@auth
<div x-data="readingTracker({
        bookId: {{ $bookId ? (int) $bookId : 'null' }},
        chapterId: {{ $chapterId ? (int) $chapterId : 'null' }},
        pageType: '{{ $pageType }}',
        heartbeatUrl: '{{ route('reading.heartbeat') }}',
        csrfToken: '{{ csrf_token() }}',
        initialMinutesToday: {{ (int) (auth()->user()->dailyActivities()->where('activity_date', now('Asia/Tashkent')->toDateString())->value('minutes_read') ?? 0) }},
        initialMinutesAll: {{ (int) (auth()->user()->total_reading_minutes ?? 0) }}
    })"
    x-init="initTracker()"
    class="relative z-50">

    <!-- ── 1. Floating Live Reading / Viewing Timer Widget ── -->
    <div class="fixed bottom-5 right-5 sm:bottom-6 sm:right-6 z-40 select-none animate-fade-in"
         x-show="isWidgetVisible"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100">
        
        <div class="bg-slate-900/90 dark:bg-ink-900/95 backdrop-blur-xl border border-white/10 hover:border-amber-400/40 rounded-2xl shadow-2xl p-2.5 px-4 flex items-center gap-3 transition-all duration-300 group">
            
            <!-- Active / Paused Indicator Dot -->
            <div class="relative flex items-center justify-center shrink-0">
                <template x-if="!isPaused && !isAfk">
                    <div class="relative flex items-center justify-center">
                        <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </div>
                </template>
                <template x-if="isPaused || isAfk">
                    <span class="inline-flex rounded-full h-2.5 w-2.5 bg-amber-500 animate-pulse"></span>
                </template>
            </div>

            <!-- Timer Digits & Stats -->
            <div class="flex flex-col">
                <div class="flex items-center gap-1.5">
                    <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">
                        <span x-show="pageType === 'video'">🎬 Video dars</span>
                        <span x-show="pageType === 'audio'">🎧 Tinglash</span>
                        <span x-show="pageType === 'quiz'">🧠 Test vaqti</span>
                        <span x-show="pageType !== 'audio' && pageType !== 'quiz' && pageType !== 'video'">⏱️ Mutolaa</span>
                    </span>
                    <span x-show="isPaused || isAfk" class="text-[9px] px-1 py-0.2 rounded bg-amber-500/20 text-amber-300 font-bold uppercase">Pauza</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="font-mono text-sm sm:text-base font-black text-white tracking-tight" x-text="formatTime(sessionSeconds)">00:00</span>
                    <span class="text-[10px] font-mono text-slate-400 cursor-help" 
                          :title="'Bugun: ' + totalMinutesToday + ' daqiqa | Jami: ' + totalMinutesAll + ' daqiqa'">
                        (Bugun: <strong class="text-amber-400 font-bold" x-text="totalMinutesToday">0</strong> daq)
                    </span>
                </div>
            </div>

            <!-- Manual Pause / Resume Toggle -->
            <button type="button" 
                    @click="toggleManualPause()" 
                    :title="isPaused ? 'Davom ettirish' : 'Vaqtincha to\'xtatish (pauza)'"
                    class="p-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white transition-all active:scale-95">
                <span x-show="!isPaused && !isAfk" class="text-xs">⏸️</span>
                <span x-show="isPaused || isAfk" class="text-xs">▶️</span>
            </button>

            <!-- Points & Coins Reward Toast Notification -->
            <div x-show="showPointsNotification" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-2 scale-90"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 -translate-y-2 scale-90"
                 class="absolute -top-10 left-1/2 -translate-x-1/2 text-[11px] font-black font-mono px-3.5 py-1 rounded-full shadow-2xl whitespace-nowrap pointer-events-none bg-gradient-to-r from-amber-400 via-amber-300 to-yellow-400 text-slate-950 shadow-amber-500/50 ring-2 ring-white/60 animate-bounce-sm">
                <span class="flex items-center gap-1.5">
                    <span>🎉</span>
                    <span>+<span x-text="lastPointsAwarded || 10">10</span> BALL & +<span x-text="lastCoinsAwarded || 1">1</span> TANGA!</span>
                    <span>🪙</span>
                </span>
            </div>
        </div>
    </div>

    <!-- ── 2. AFK Inactivity Modal (5 minut harakatsizlik xavfsizligi) ── -->
    <div x-show="showAfkModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="relative w-full max-w-md bg-gradient-to-b from-[#131927] to-[#0a0e17] border border-amber-500/30 rounded-3xl p-6 sm:p-8 text-center space-y-5 shadow-2xl overflow-hidden"
             @click.outside="confirmActive()"
             x-transition:enter="transition cubic-bezier(0.34, 1.56, 0.64, 1) duration-400"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <!-- Ambient Spotlight Glow -->
            <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-64 h-64 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Pulsing 3D Hourglass Icon -->
            <div class="w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-400 flex items-center justify-center text-3xl mx-auto shadow-glow-amber animate-pulse">
                ⏳
            </div>

            <!-- Heading & Context -->
            <div class="space-y-2">
                <h3 class="text-xl sm:text-2xl font-black text-white font-manrope tracking-tight">
                    Siz shu yerdamisiz?
                </h3>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-xs mx-auto">
                    5 daqiqa davomida hech qanday harakat qayd etilmadi. Vaqt va ball hisoblash to'xtatildi. Davom ettirasizmi?
                </p>
            </div>

            <!-- Current Session Info Card -->
            <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 grid grid-cols-3 gap-2 text-center text-xs font-mono">
                <div>
                    <span class="text-slate-400 block text-[10px]">Ushbu sessiya</span>
                    <span class="text-amber-400 font-bold text-sm" x-text="formatTime(sessionSeconds)">00:00</span>
                </div>
                <div class="border-x border-white/10 px-1">
                    <span class="text-slate-400 block text-[10px]">Bugun</span>
                    <span class="text-white font-bold text-sm"><span x-text="totalMinutesToday">0</span> daq</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px]">Jami mutolaa</span>
                    <span class="text-emerald-400 font-bold text-sm"><span x-text="totalMinutesAll">0</span> daq</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-2.5 pt-2">
                <button type="button" 
                        @click="confirmActive()" 
                        class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-sm tracking-wide shadow-lg shadow-amber-500/25 transition-all active:scale-95 flex items-center justify-center gap-2">
                    <span x-show="pageType === 'video'">🎬 Ha, videoni davom ettiraman</span>
                    <span x-show="pageType === 'audio'">🎧 Ha, audioni davom ettiraman</span>
                    <span x-show="pageType === 'quiz'">🧠 Ha, testni davom ettiraman</span>
                    <span x-show="pageType !== 'video' && pageType !== 'audio' && pageType !== 'quiz'">📖 Ha, mutolaani davom ettiraman</span>
                </button>
                <button type="button" 
                        @click="showAfkModal = false; isPaused = true;" 
                        class="w-full py-2 text-xs text-slate-400 hover:text-white transition-colors">
                    Hozircha pauzada tursin
                </button>
            </div>

        </div>
    </div>

</div>

<script>
function readingTracker(config) {
    return {
        bookId: config.bookId,
        chapterId: config.chapterId,
        pageType: config.pageType,
        heartbeatUrl: config.heartbeatUrl,
        csrfToken: config.csrfToken,
        totalMinutesToday: config.initialMinutesToday || 0,
        totalMinutesAll: config.initialMinutesAll || 0,
        minutesToNextCoin: 10 - ((config.initialMinutesToday || 0) % 10),
        
        sessionSeconds: 0,
        unsentSeconds: 0,
        idleSeconds: 0,
        lastPointsAwarded: 0,
        lastCoinsAwarded: 0,
        
        isPaused: false,
        isAfk: false,
        showAfkModal: false,
        isWidgetVisible: true,
        showPointsNotification: false,
        wasMediaPlayingBeforeAfk: false,
        
        timerInterval: null,
        afkLimitSeconds: 300, // 5 minut harakatsizlik (300 soniya)
        
        initTracker() {
            // Activity events to detect user interaction
            const events = ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll', 'wheel'];
            let lastEventTime = Date.now();

            const recordActivity = () => {
                const now = Date.now();
                if (now - lastEventTime > 1500) { // Throttle activity resets to every 1.5s
                    lastEventTime = now;
                    this.idleSeconds = 0;
                    if (this.isAfk && !this.showAfkModal) {
                        this.isAfk = false;
                    }
                }
            };

            events.forEach(evt => {
                window.addEventListener(evt, recordActivity, { passive: true });
            });

            // Media listeners for video/audio elements
            const syncMedia = () => {
                const videoEl = document.querySelector('video');
                if (videoEl && !videoEl._hasTrackerListener) {
                    videoEl._hasTrackerListener = true;
                    videoEl.addEventListener('play', () => {
                        this.isPaused = false;
                        this.idleSeconds = 0;
                    });
                }
                const audioEl = document.querySelector('audio');
                if (audioEl && !audioEl._hasTrackerListener) {
                    audioEl._hasTrackerListener = true;
                    audioEl.addEventListener('play', () => {
                        this.isPaused = false;
                        this.idleSeconds = 0;
                    });
                }
            };

            syncMedia();
            setInterval(syncMedia, 3000);

            // Second ticker interval
            this.timerInterval = setInterval(() => {
                const videoEl = document.querySelector('video');
                const isVideoPlaying = videoEl && !videoEl.paused && !videoEl.ended && videoEl.readyState > 2;

                const audioEl = document.querySelector('audio');
                const isAudioPlaying = audioEl && !audioEl.paused && !audioEl.ended;

                // Media is playing -> user is actively watching/listening!
                if (isVideoPlaying || isAudioPlaying) {
                    this.idleSeconds = 0;
                }

                if (!this.isPaused && !this.isAfk) {
                    this.sessionSeconds++;
                    this.unsentSeconds++;
                    this.idleSeconds++;

                    // 5 minutes of total inactivity (neither media playing nor user touching mouse/keys)
                    if (this.idleSeconds >= this.afkLimitSeconds) {
                        this.triggerAfk();
                    }

                    // Every 60 active seconds -> Send Heartbeat to server
                    if (this.unsentSeconds >= 60) {
                        this.sendHeartbeat(1);
                    }
                } else if (this.isAfk) {
                    this.idleSeconds++;
                }
            }, 1000);

            // Page leave sync (visibilitychange / pagehide)
            window.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'hidden') {
                    if (this.unsentSeconds >= 30) {
                        this.sendHeartbeat(Math.round(this.unsentSeconds / 60) || 1, true);
                    }
                }
            });
        },

        triggerAfk() {
            this.isAfk = true;
            this.showAfkModal = true;
            this.wasMediaPlayingBeforeAfk = false;
            
            // If video is playing, pause it so user doesn't miss anything
            const videoEl = document.querySelector('video');
            if (videoEl && !videoEl.paused) {
                videoEl.pause();
                this.wasMediaPlayingBeforeAfk = true;
            }

            // If audio is playing, pause it
            const audioEl = document.querySelector('audio');
            if (audioEl && !audioEl.paused) {
                audioEl.pause();
                this.wasMediaPlayingBeforeAfk = true;
            }
        },

        confirmActive() {
            this.showAfkModal = false;
            this.isAfk = false;
            this.isPaused = false;
            this.idleSeconds = 0;

            if (this.wasMediaPlayingBeforeAfk) {
                const videoEl = document.querySelector('video');
                if (videoEl && videoEl.paused) {
                    videoEl.play().catch(() => {});
                }
                const audioEl = document.querySelector('audio');
                if (audioEl && audioEl.paused) {
                    audioEl.play().catch(() => {});
                }
                this.wasMediaPlayingBeforeAfk = false;
            }
        },

        toggleManualPause() {
            this.isPaused = !this.isPaused;
            if (!this.isPaused) {
                this.idleSeconds = 0;
                this.isAfk = false;
            }
        },

        sendHeartbeat(mins, isBeacon = false) {
            const minutesToSend = Math.max(1, Math.round(mins || 1));
            const payload = {
                book_id: this.bookId,
                chapter_id: this.chapterId,
                page_type: this.pageType,
                minutes: minutesToSend
            };

            if (isBeacon && navigator.sendBeacon) {
                const formData = new FormData();
                formData.append('_token', this.csrfToken);
                if (this.bookId) formData.append('book_id', this.bookId);
                if (this.chapterId) formData.append('chapter_id', this.chapterId);
                formData.append('page_type', this.pageType);
                formData.append('minutes', minutesToSend);
                navigator.sendBeacon(this.heartbeatUrl, formData);
                this.unsentSeconds = 0;
                return;
            }

            fetch(this.heartbeatUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.unsentSeconds = 0;
                    this.totalMinutesToday = data.total_minutes_today;
                    if (data.total_minutes_all) {
                        this.totalMinutesAll = data.total_minutes_all;
                    }
                    this.lastPointsAwarded = data.points_added || 10;
                    this.lastCoinsAwarded = data.coins_added || 1;
                    if (data.total_points) {
                        window.dispatchEvent(new CustomEvent('points-awarded', {
                            detail: { points: this.lastPointsAwarded, newTotal: data.total_points }
                        }));
                    }
                    if (data.streak !== undefined) {
                        window.dispatchEvent(new CustomEvent('streak-updated', {
                            detail: { streak: data.streak }
                        }));
                    }
                    this.triggerToast();
                }
            })
            .catch(err => console.warn('Reading tracker sync error:', err));
        },

        triggerToast() {
            this.showPointsNotification = true;
            setTimeout(() => {
                this.showPointsNotification = false;
            }, 4500);

            if (typeof window.toast === 'function') {
                window.toast({
                    type: 'success',
                    title: 'Mutolaa bonusi! 🪙',
                    message: `+${this.lastPointsAwarded || 10} ball va +${this.lastCoinsAwarded || 1} tanga hisobingizga qo'shildi!`
                });
            }
        },

        formatTime(totalSec) {
            const m = Math.floor(totalSec / 60).toString().padStart(2, '0');
            const s = (totalSec % 60).toString().padStart(2, '0');
            return `${m}:${s}`;
        }
    };
}
</script>
@endauth
