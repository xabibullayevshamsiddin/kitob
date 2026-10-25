<script>
    // Universal Voice Note Player for Alpine.js & Livewire
    (function() {
        if (window.__voicePlayerDefined) return;
        window.__voicePlayerDefined = true;

        window.voicePlayer = function(audioUrl, initialDuration) {
            return {
                playing: false,
                progress: 0,
                duration: initialDuration || 0,
                currentTime: 0,
                audio: null,
                init() {
                    this.setupAudio();
                },
                setupAudio() {
                    if (this.audio) return;
                    this.audio = new Audio(audioUrl);
                    this.audio.preload = 'metadata';
                    this.audio.addEventListener('loadedmetadata', () => {
                        if (!this.duration || isNaN(this.duration) || this.duration === 0) {
                            this.duration = Math.round(this.audio.duration) || 0;
                        }
                    });
                    this.audio.addEventListener('timeupdate', () => {
                        this.currentTime = Math.round(this.audio.currentTime);
                        if (this.duration > 0) {
                            this.progress = Math.min(100, (this.audio.currentTime / this.duration) * 100);
                        }
                    });
                    this.audio.addEventListener('ended', () => {
                        this.playing = false;
                        this.progress = 0;
                        this.currentTime = 0;
                    });
                    this.audio.addEventListener('pause', () => {
                        this.playing = false;
                    });
                    this.audio.addEventListener('play', () => {
                        this.playing = true;
                    });
                },
                toggle() {
                    this.setupAudio();
                    if (this.playing) {
                        this.audio.pause();
                        this.playing = false;
                    } else {
                        // Pause any other playing voice notes or audio elements
                        document.querySelectorAll('audio').forEach(a => {
                            if (a !== this.audio) {
                                try { a.pause(); } catch(e) {}
                            }
                        });
                        if (window.__currentPlayingVoice && window.__currentPlayingVoice !== this.audio) {
                            try { window.__currentPlayingVoice.pause(); } catch(e) {}
                        }
                        window.__currentPlayingVoice = this.audio;

                        const playPromise = this.audio.play();
                        if (playPromise !== undefined) {
                            playPromise.then(() => {
                                this.playing = true;
                            }).catch(err => {
                                console.warn('Voice playback failed:', err);
                                this.playing = false;
                            });
                        }
                    }
                },
                seek(event) {
                    this.setupAudio();
                    const el = event.currentTarget;
                    const r = el.getBoundingClientRect();
                    const p = Math.max(0, Math.min(1, (event.clientX - r.left) / r.width));
                    if (this.duration > 0) {
                        this.audio.currentTime = p * this.duration;
                        this.progress = p * 100;
                    }
                },
                formatTime(sec) {
                    const s = Math.max(0, sec || 0);
                    const m = Math.floor(s / 60);
                    const rem = Math.floor(s % 60);
                    return (m < 10 ? '0' : '') + m + ':' + (rem < 10 ? '0' : '') + rem;
                }
            };
        };

        window.fallbackVoiceToggle = function(btn) {
            const root = btn ? btn.closest('[x-data]') : null;
            if (!root) return;
            if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                try {
                    window.Alpine.initTree(root);
                } catch(e) {}
            }
            if (root._x_dataStack && root._x_dataStack[0] && typeof root._x_dataStack[0].toggle === 'function') {
                root._x_dataStack[0].toggle();
            }
        };

        function registerLivewireAlpineHooks() {
            if (window.Livewire && window.Livewire.hook) {
                window.Livewire.hook('message.processed', (message, component) => {
                    if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                        const targetEl = (component && component.el) ? component.el : document.body;
                        try {
                            window.Alpine.initTree(targetEl);
                        } catch(e) {}
                    }
                });
            }
        }

        if (window.Livewire) {
            registerLivewireAlpineHooks();
        } else {
            document.addEventListener('livewire:load', registerLivewireAlpineHooks);
        }
    })();
</script>
