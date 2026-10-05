@props(['book', 'floatPosition' => 'bottom-left'])

@php
    $bookMusics = $book->activeMusics;
    
    // Agar kitobga maxsus musiqa biriktirilgan bo'lsa, o'shalardan foydalanamiz
    if ($bookMusics->isNotEmpty()) {
        $playlist = $bookMusics->map(function ($m, $i) {
            return [
                'id'       => $m->id,
                'title'    => $m->title,
                'url'      => $m->file_url,
                'duration' => $m->duration ?? 0,
            ];
        })->values();
    } else {
        // Zaxira: standart yoqimli ambient tovushlar
        $playlist = collect([
            [
                'id'       => 1,
                'title'    => "Sokin Yomg'ir & Tabiat",
                'url'      => asset('sounds/ambient/rain.wav'),
                'duration' => 0,
            ],
            [
                'id'       => 2,
                'title'    => "Klassik Pianino Oromi",
                'url'      => asset('sounds/ambient/piano.wav'),
                'duration' => 0,
            ],
            [
                'id'       => 3,
                'title'    => "O'rmon Shabodasi & Qushlar",
                'url'      => asset('sounds/ambient/forest.wav'),
                'duration' => 0,
            ],
        ]);
    }

    $positionClasses = match($floatPosition) {
        'bottom-right' => 'fixed bottom-24 right-5 sm:bottom-28 sm:right-6',
        'top-right'    => 'fixed top-20 right-5 sm:top-20 sm:right-6',
        default        => 'fixed bottom-5 left-5 sm:bottom-6 sm:left-6',
    };
@endphp

<div x-data="ambientMusicPlayer({
        tracks: {{ Js::from($playlist) }},
        hasCustomMusic: {{ $bookMusics->isNotEmpty() ? 'true' : 'false' }}
     })"
     x-cloak
     class="ambient-music-scope relative"
     @keydown.window.prevent.stop.m="togglePlay()"
     @toggle-ambient-music.window="togglePlay()"
     @open-ambient-music.window="isOpen = !isOpen"
>
    <!-- Hidden HTML5 Audio Element -->
    <audio x-ref="audio"
           preload="auto"
           @timeupdate="onTimeUpdate()"
           @ended="onTrackEnded()"
           x-on:error="onAudioError()"
           class="hidden"></audio>

    <!-- ══════════════════════════════════════════════════════════════ -->
    <!-- FLOATING COMPACT PILL / TRIGGER (O'quvchi burchagida)          -->
    <!-- ══════════════════════════════════════════════════════════════ -->
    <div class="{{ $positionClasses }} z-40 print:hidden select-none">
        
        <!-- Expanded Player Card (Pleyer paneli) -->
        <div x-show="isOpen"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
             class="mb-3 w-80 sm:w-96 rounded-2xl bg-ink-950/95 border border-ink-border/80 shadow-2xl backdrop-blur-xl p-4 text-paper space-y-3.5 ring-1 ring-white/10"
             @click.outside="isOpen = false">
            
            <!-- Card Header -->
            <div class="flex items-center justify-between border-b border-ink-border/60 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-btn bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs">
                        🎵
                    </span>
                    <div>
                        <h4 class="text-xs font-bold font-serif text-paper">Mutolaa Fon Musiqasi</h4>
                        <span class="text-[10px] font-mono text-mist block">
                            {{ $bookMusics->isNotEmpty() ? $bookMusics->count() . ' ta trek' : 'Ambient ohanglar' }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button @click="isOpen = false" 
                            class="p-1 rounded-btn text-mist hover:text-paper hover:bg-white/5 transition-colors text-xs" 
                            title="Yopish">
                        ✕
                    </button>
                </div>
            </div>

            <!-- Current Track Display & Wave Animation -->
            <div class="p-3 rounded-xl bg-ink-900/90 border border-ink-border flex items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded-badge bg-amber-500/15 text-amber-400 border border-amber-500/25 shrink-0"
                              x-text="`${currentIndex + 1}/${tracks.length}`"></span>
                        <p class="text-xs font-bold text-paper truncate font-sans" x-text="currentTrack ? currentTrack.title : 'Yuklanmoqda...'"></p>
                    </div>
                    <div class="flex items-center gap-2 mt-1 font-mono text-[10px] text-mist">
                        <span x-text="formatTime(currentTime)">00:00</span>
                        <span>/</span>
                        <span x-text="formatTime(duration)">--:--</span>
                        <span x-show="isPlaying" class="text-emerald-400 font-semibold">• Ijro etilmoqda</span>
                        <span x-show="!isPlaying" class="text-mist">• To'xtatilgan</span>
                    </div>
                </div>

                <!-- Animated Equalizer Bars -->
                <div class="flex items-end gap-1 h-6 shrink-0 px-2">
                    <span class="w-1 bg-amber-400 rounded-full transition-all duration-150"
                          :style="isPlaying ? `height: ${Math.max(4, Math.random() * 22)}px` : 'height: 4px'"></span>
                    <span class="w-1 bg-amber-400 rounded-full transition-all duration-150"
                          :style="isPlaying ? `height: ${Math.max(6, Math.random() * 24)}px` : 'height: 6px'"></span>
                    <span class="w-1 bg-amber-400 rounded-full transition-all duration-150"
                          :style="isPlaying ? `height: ${Math.max(4, Math.random() * 20)}px` : 'height: 4px'"></span>
                    <span class="w-1 bg-amber-400 rounded-full transition-all duration-150"
                          :style="isPlaying ? `height: ${Math.max(8, Math.random() * 24)}px` : 'height: 8px'"></span>
                </div>
            </div>

            <!-- Progress Bar (Seeker) -->
            <div class="space-y-1">
                <div class="relative w-full h-1.5 bg-ink-800 rounded-full cursor-pointer overflow-hidden group"
                     @click="seek($event)">
                    <div class="h-full bg-gradient-to-r from-amber-500 to-amber-400 rounded-full transition-all"
                         :style="`width: ${progressPercent}%`"></div>
                </div>
            </div>

            <!-- Playback Controls (Prev, Play/Pause, Next, Loop) -->
            <div class="flex items-center justify-between gap-2 pt-1">
                <!-- Loop Button -->
                <button @click="toggleLoop()" 
                        class="p-2 rounded-xl transition-colors text-xs font-mono"
                        :class="isLooping ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-mist hover:text-paper hover:bg-white/5'"
                        :title="isLooping ? 'Aylanma ijro: Bitta trek takrorlanadi' : 'Aylanma ijro: O\'chirilgan'">
                    <span x-show="!isLooping">🔁</span>
                    <span x-show="isLooping" class="font-bold">🔂</span>
                </button>

                <!-- Center Controls (Prev, Play, Next) -->
                <div class="flex items-center gap-2">
                    <!-- Prev -->
                    <button @click="prevTrack()" 
                            class="p-2.5 rounded-xl bg-ink-900 border border-ink-border text-paper hover:text-amber-400 hover:border-amber-400/40 transition-colors"
                            title="Oldingi musiqa">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 6h2v12H6zm3.5 6 8.5 6V6z"/></svg>
                    </button>

                    <!-- Play / Pause Button -->
                    <button @click="togglePlay()" 
                            class="p-3.5 rounded-2xl bg-amber-500 text-ink-950 hover:bg-amber-400 transition-all shadow-lg shadow-amber-500/30 active:scale-95 font-bold"
                            :title="isPlaying ? 'Musiqani to\'xtatish (Pauza)' : 'Musiqani yoqish (Ijro)'">
                        <svg x-show="!isPlaying" class="w-5 h-5 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        <svg x-show="isPlaying" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                    </button>

                    <!-- Next -->
                    <button @click="nextTrack()" 
                            class="p-2.5 rounded-xl bg-ink-900 border border-ink-border text-paper hover:text-amber-400 hover:border-amber-400/40 transition-colors"
                            title="Keyingi musiqa">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="m6 18 8.5-6L6 6v12zM16 6v12h2V6h-2z"/></svg>
                    </button>
                </div>

                <!-- Playlist toggle inside card -->
                <button @click="showPlaylist = !showPlaylist"
                        class="p-2 rounded-xl transition-colors text-xs font-mono"
                        :class="showPlaylist ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-mist hover:text-paper hover:bg-white/5'"
                        title="Pleylistni ko'rish">
                    📜
                </button>
            </div>

            <!-- Volume Slider & Mute Toggle -->
            <div class="flex items-center gap-3 pt-1 border-t border-ink-border/50 text-xs">
                <button @click="toggleMute()" 
                        class="text-mist hover:text-amber-400 transition-colors text-sm shrink-0"
                        :title="isMuted ? 'Ovozni yoqish' : 'Ovozni o\'chirish'">
                    <span x-show="!isMuted && volume > 0.5">🔊</span>
                    <span x-show="!isMuted && volume <= 0.5 && volume > 0">🔉</span>
                    <span x-show="isMuted || volume == 0">🔇</span>
                </button>
                <div class="flex-1 flex items-center">
                    <input type="range" 
                           min="0" 
                           max="1" 
                           step="0.02" 
                           x-model="volume" 
                           @input="setVolume(volume)"
                           class="w-full h-1.5 bg-ink-800 rounded-lg appearance-none cursor-pointer accent-amber-500">
                </div>
                <span class="text-[10px] font-mono text-mist w-8 text-right" x-text="`${Math.round(volume * 100)}%`"></span>
            </div>

            <!-- Playlist Selector Dropdown / View -->
            <div x-show="showPlaylist"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 max-h-0"
                 x-transition:enter-end="opacity-100 max-h-60"
                 class="space-y-1.5 pt-2 border-t border-ink-border max-h-48 overflow-y-auto pr-1 font-sans">
                <p class="text-[10px] font-mono text-mist uppercase font-semibold">Pleylist treklari:</p>
                <template x-for="(t, idx) in tracks" :key="t.id || idx">
                    <div @click="selectTrack(idx)"
                         class="p-2 rounded-xl border flex items-center justify-between gap-2 cursor-pointer transition-colors"
                         :class="currentIndex === idx 
                            ? 'bg-amber-500/15 border-amber-500/40 text-amber-300' 
                            : 'bg-ink-900 border-ink-border/60 hover:border-amber-400/30 text-paper'">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-5 h-5 rounded-btn bg-ink-950 text-[10px] font-mono flex items-center justify-center font-bold"
                                  :class="currentIndex === idx ? 'text-amber-400' : 'text-mist'"
                                  x-text="idx + 1"></span>
                            <span class="text-xs truncate font-medium" x-text="t.title"></span>
                        </div>
                        <span x-show="currentIndex === idx && isPlaying" class="text-xs text-amber-400 animate-pulse">▶</span>
                    </div>
                </template>
            </div>

            <p class="text-[9px] text-center text-mist/70 font-mono">
                Maslahat: Klaviaturadagi <kbd class="px-1 py-0.5 rounded bg-ink-900 border border-ink-border text-paper">M</kbd> tugmasi orqali musiqani yoqish/o'chirish mumkin
            </p>
        </div>

        <!-- Floating Pill Trigger Button -->
        <button @click="isOpen = !isOpen"
                class="flex items-center gap-2 px-3.5 py-2 rounded-full border shadow-2xl backdrop-blur-md transition-all active:scale-95 group"
                :class="isPlaying 
                    ? 'bg-amber-500 text-ink-950 border-amber-400 ring-2 ring-amber-400/40 font-bold' 
                    : 'bg-ink-950/90 text-paper border-ink-border hover:border-amber-400/50 hover:bg-ink-900'">
            
            <!-- Animated Sound waves or Music Note -->
            <span class="text-base" :class="isPlaying ? 'animate-bounce' : 'group-hover:scale-110 transition-transform'">
                🎵
            </span>

            <div class="text-left hidden sm:block">
                <span class="block text-xs font-semibold leading-tight truncate max-w-[140px]"
                      x-text="isPlaying ? (currentTrack ? currentTrack.title : 'Musiqa ijro...') : 'Fon musiqasi'"></span>
                <span class="block text-[9px] font-mono"
                      :class="isPlaying ? 'text-ink-900 font-bold' : 'text-mist'"
                      x-text="isPlaying ? 'Tinglanmoqda' : 'Yoqish uchun bosing'"></span>
            </div>

            <!-- Quick Play/Pause Mini Toggle Button -->
            <span @click.stop="togglePlay()" 
                  class="ml-1 w-6 h-6 rounded-full flex items-center justify-center text-xs transition-colors"
                  :class="isPlaying ? 'bg-ink-950/20 text-ink-950 hover:bg-ink-950/40' : 'bg-amber-500 text-ink-950 hover:bg-amber-400 font-bold'"
                  :title="isPlaying ? 'Pauza' : 'Ijro'">
                <span x-show="!isPlaying">▶</span>
                <span x-show="isPlaying">⏸</span>
            </span>
        </button>

    </div>
</div>

<script>
function ambientMusicPlayer(config) {
    return {
        tracks: config.tracks || [],
        currentIndex: 0,
        isPlaying: false,
        isMuted: false,
        isLooping: false,
        isOpen: false,
        showPlaylist: false,
        volume: parseFloat(localStorage.getItem('kitob_ambient_vol')) || 0.45,
        currentTime: 0,
        duration: 0,
        progressPercent: 0,

        get currentTrack() {
            return this.tracks[this.currentIndex] || null;
        },

        init() {
            const savedTrack = parseInt(localStorage.getItem('kitob_ambient_track')) || 0;
            if (savedTrack >= 0 && savedTrack < this.tracks.length) {
                this.currentIndex = savedTrack;
            }
            this.isLooping = localStorage.getItem('kitob_ambient_loop') === 'true';

            this.$nextTick(() => {
                const audio = this.$refs.audio;
                if (audio) {
                    audio.volume = this.volume;
                    this.loadTrack(this.currentIndex, false);
                }
            });
        },

        loadTrack(index, autoPlay = true) {
            if (!this.tracks[index]) return;
            this.currentIndex = index;
            localStorage.setItem('kitob_ambient_track', index);

            const audio = this.$refs.audio;
            if (!audio) return;

            audio.src = this.tracks[index].url;
            audio.load();

            if (autoPlay) {
                audio.play().then(() => {
                    this.isPlaying = true;
                }).catch(e => {
                    console.warn('Autoplay prevented or audio load issue:', e);
                    this.isPlaying = false;
                });
            }
        },

        togglePlay() {
            const audio = this.$refs.audio;
            if (!audio) return;

            if (this.isPlaying) {
                audio.pause();
                this.isPlaying = false;
            } else {
                if (!audio.src || audio.src === window.location.href) {
                    this.loadTrack(this.currentIndex, true);
                } else {
                    audio.play().then(() => {
                        this.isPlaying = true;
                    }).catch(e => {
                        console.warn('Playback error:', e);
                        this.loadTrack(this.currentIndex, true);
                    });
                }
            }
        },

        selectTrack(index) {
            this.loadTrack(index, true);
        },

        nextTrack() {
            const next = (this.currentIndex + 1) % this.tracks.length;
            this.loadTrack(next, true);
        },

        prevTrack() {
            const prev = (this.currentIndex - 1 + this.tracks.length) % this.tracks.length;
            this.loadTrack(prev, true);
        },

        onTrackEnded() {
            if (this.isLooping) {
                const audio = this.$refs.audio;
                audio.currentTime = 0;
                audio.play();
            } else {
                this.nextTrack();
            }
        },

        toggleLoop() {
            this.isLooping = !this.isLooping;
            localStorage.setItem('kitob_ambient_loop', this.isLooping);
        },

        setVolume(val) {
            this.volume = parseFloat(val);
            localStorage.setItem('kitob_ambient_vol', this.volume);
            const audio = this.$refs.audio;
            if (audio) {
                audio.volume = this.volume;
                audio.muted = (this.volume === 0);
                this.isMuted = audio.muted;
            }
        },

        toggleMute() {
            const audio = this.$refs.audio;
            if (!audio) return;

            if (this.isMuted) {
                audio.muted = false;
                this.isMuted = false;
                if (this.volume === 0) this.setVolume(0.3);
            } else {
                audio.muted = true;
                this.isMuted = true;
            }
        },

        onTimeUpdate() {
            const audio = this.$refs.audio;
            if (!audio) return;

            this.currentTime = audio.currentTime || 0;
            this.duration = audio.duration || 0;
            if (this.duration > 0) {
                this.progressPercent = Math.min(100, (this.currentTime / this.duration) * 100);
            }
        },

        seek(e) {
            const audio = this.$refs.audio;
            if (!audio || !this.duration) return;

            const rect = e.currentTarget.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const pct = Math.max(0, Math.min(1, clickX / rect.width));
            audio.currentTime = pct * this.duration;
            this.onTimeUpdate();
        },

        onAudioError() {
            this.isPlaying = false;
        },

        formatTime(sec) {
            if (!sec || isNaN(sec) || sec <= 0) return '00:00';
            const m = Math.floor(sec / 60).toString().padStart(2, '0');
            const s = Math.floor(sec % 60).toString().padStart(2, '0');
            return `${m}:${s}`;
        }
    };
}
</script>
