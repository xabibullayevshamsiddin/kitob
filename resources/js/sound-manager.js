/**
 * Kitobxon Platform — Calm & Harmonic Interface Sound System
 * 
 * Powered by native Web Audio API procedural synthesis with acoustic calibration,
 * smooth gain envelopes (zero clipping/clicking), and fallback to local WAV audio files.
 * 
 * Complies with:
 * - Autoplay policies (lazy-initialization & user gesture resumption)
 * - Cold page-load suppression
 * - Tab visibility checks (muted in background tabs)
 * - Media & Live stream suppression (muted while audiobooks, videos, or mics are active)
 * - Deduplication & event cooldowns
 * - Livewire re-render safety (listeners attached once)
 * - Kenney Interface Sounds licensing (CC0 1.0 Universal)
 */

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define([], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory();
    } else {
        root.KitobSound = factory();
    }
}(typeof self !== 'undefined' ? self : this, function () {

    // Prevent double initialization
    if (typeof window !== 'undefined' && window.KitobSound) {
        return window.KitobSound;
    }

    const STORAGE_KEY_ENABLED = 'kitob_sound_enabled';
    const STORAGE_KEY_VOLUME = 'kitob_sound_volume';
    const DEFAULT_VOLUME = 0.20; // 20% default as specified

    // Per-effect cooldowns in milliseconds
    const COOLDOWNS = {
        bookmark: 350,
        settings_saved: 600,
        quiz_correct: 200,
        quiz_wrong: 250,
        quiz_finish: 600,
        achievement: 1200,
        level_up: 4000,
        daily_bonus: 1500,
        note_saved: 350,
        group_message: 4000,
        purchase: 600,
        chat_message: 1000,
        error: 500,
    };

    // Metadata for settings & preview UI
    const SOUND_CATALOG = [
        {
            key: 'bookmark',
            name: 'Kitob saqlash va xatcho‘p',
            desc: 'Kitob saqlanganda yoki yangi xatcho‘p qo‘yilganda mayin yog‘och "pop"',
            duration: 95
        },
        {
            key: 'settings_saved',
            name: 'Sozlamalar saqlandi',
            desc: 'Sozlamalar muvaffaqiyatli saqlanganda ikki notali mayin tasdiqlash',
            duration: 300
        },
        {
            key: 'quiz_correct',
            name: 'Test: To‘g‘ri javob',
            desc: 'Testda to‘g‘ri javob tanlanganda yoqimli ko‘tariluvchi ohang',
            duration: 280
        },
        {
            key: 'quiz_wrong',
            name: 'Test: Noto‘g‘ri javob',
            desc: 'Testda xato tanlanganda muloyim va sokin past tovush',
            duration: 220
        },
        {
            key: 'quiz_finish',
            name: 'Test: Yakunlanish',
            desc: 'Test to‘liq topshirilganda qisqa yakuniy ohang',
            duration: 420
        },
        {
            key: 'achievement',
            name: 'Yutuq va nishon',
            desc: 'Nishon yoki yutuq olinganda bir soniyalik tantanali ohang',
            duration: 950
        },
        {
            key: 'level_up',
            name: 'Yangi daraja (Level Up)',
            desc: 'Yangi darajaga ko‘tarilganda bir martalik tantanali yutuq ohangi',
            duration: 950
        },
        {
            key: 'daily_bonus',
            name: 'Kunlik maqsad va bonus',
            desc: 'Kunlik maqsad yoki bonus muvaffaqiyatli olinganda qisqa tabrik ovozi',
            duration: 420
        },
        {
            key: 'note_saved',
            name: 'Shaxsiy qayd saqlandi',
            desc: 'Kitobga shaxsiy qayd yozilganda xatcho‘p ovoziga o‘xshash mayin tasdiqlash',
            duration: 140
        },
        {
            key: 'group_message',
            name: 'Ochiq guruh xabari',
            desc: 'Ochiq guruhga yangi xabar kelganda mayin bildirishnoma (takrorlanish cheklovi bilan)',
            duration: 180
        },
        {
            key: 'purchase',
            name: 'Do‘kondagi xarid',
            desc: 'Do‘kondan buyum yoki sandiq xarid qilinganda yengil tanga jarangi',
            duration: 320
        },
        {
            key: 'chat_message',
            name: 'Chat: Yangi xabar',
            desc: 'Yangi xabar kelganda mayin bildirishnoma ohangi',
            duration: 160
        },
        {
            key: 'error',
            name: 'Ogohlantirish va xatolik',
            desc: 'Muhim xatolik yuz berganda qisqa va sokin ogohlantirish',
            duration: 250
        }
    ];

    class KitobSoundManager {
        constructor() {
            this.audioCtx = null;
            this.masterGain = null;
            this.userInteracted = false;
            this.coldStart = true;
            this.lastSoundTime = 0;
            this.recentEvents = new Map();
            this.audioElementCache = new Map();

            // Load saved settings (Guest: localStorage; Authenticated: seeded server config if available)
            this.enabled = this.resolveInitialEnabled();
            this.volume = this.resolveInitialVolume();

            if (typeof window !== 'undefined') {
                this.initLifecycle();
            }
        }

        resolveInitialEnabled() {
            try {
                if (typeof window !== 'undefined' && window.__KITOB_SOUND_SERVER_CONFIG && window.__KITOB_SOUND_SERVER_CONFIG.enabled !== null) {
                    const srv = Boolean(window.__KITOB_SOUND_SERVER_CONFIG.enabled);
                    localStorage.setItem(STORAGE_KEY_ENABLED, srv ? 'true' : 'false');
                    return srv;
                }
                const local = localStorage.getItem(STORAGE_KEY_ENABLED);
                if (local !== null) {
                    return local === 'true';
                }
            } catch (e) {}
            return true;
        }

        resolveInitialVolume() {
            try {
                if (typeof window !== 'undefined' && window.__KITOB_SOUND_SERVER_CONFIG && window.__KITOB_SOUND_SERVER_CONFIG.volume !== null) {
                    const srv = parseFloat(window.__KITOB_SOUND_SERVER_CONFIG.volume);
                    if (!isNaN(srv) && srv >= 0 && srv <= 1) {
                        localStorage.setItem(STORAGE_KEY_VOLUME, srv.toString());
                        return srv;
                    }
                }
                const local = localStorage.getItem(STORAGE_KEY_VOLUME);
                if (local !== null) {
                    const parsed = parseFloat(local);
                    if (!isNaN(parsed) && parsed >= 0 && parsed <= 1) {
                        return parsed;
                    }
                }
            } catch (e) {}
            return DEFAULT_VOLUME;
        }

        initLifecycle() {
            // Cold start protection: suppress sounds for initial 1.2 seconds of page load
            setTimeout(() => {
                this.coldStart = false;
            }, 1200);

            // User gesture unlock for AudioContext
            const unlockAudio = () => {
                this.userInteracted = true;
                if (this.audioCtx && this.audioCtx.state === 'suspended') {
                    this.audioCtx.resume().catch(() => {});
                }
                window.removeEventListener('pointerdown', unlockAudio, true);
                window.removeEventListener('keydown', unlockAudio, true);
                window.removeEventListener('touchstart', unlockAudio, true);
            };

            window.addEventListener('pointerdown', unlockAudio, { capture: true, passive: true });
            window.addEventListener('keydown', unlockAudio, { capture: true, passive: true });
            window.addEventListener('touchstart', unlockAudio, { capture: true, passive: true });

            // Hook server preference updates from Livewire
            window.addEventListener('sound-settings-updated', (e) => {
                const detail = e.detail || {};
                if (typeof detail.enabled === 'boolean') {
                    this.setEnabled(detail.enabled, false);
                }
                if (typeof detail.volume === 'number') {
                    this.setVolume(detail.volume, false);
                }
            });

            // Universal event listener for custom triggers
            window.addEventListener('kitob-sound', (e) => {
                const sound = (e.detail && e.detail.sound) ? e.detail.sound : e.detail;
                if (typeof sound === 'string') {
                    this.play(sound, e.detail || {});
                }
            });

            // Action-specific Livewire / Browser events
            window.addEventListener('shop-item-bought', () => this.play('purchase'));
            window.addEventListener('chest-opened', () => this.play('purchase'));
            window.addEventListener('badge-awarded', () => this.play('achievement'));
            window.addEventListener('level-up', (e) => this.play('level_up', {
                dedupeKey: 'level_up_' + (e?.detail?.level || e?.detail?.new_level || '')
            }));
            window.addEventListener('daily-goal-completed', () => this.play('daily_bonus'));
            window.addEventListener('daily-bonus-claimed', () => this.play('daily_bonus'));
            window.addEventListener('bonus-claimed-animation', () => this.play('daily_bonus'));
            window.addEventListener('note-saved', () => this.play('note_saved'));
            window.addEventListener('group-message-received', (e) => this.play('group_message', e?.detail || {}));
            window.addEventListener('quiz-correct', () => this.play('quiz_correct'));
            window.addEventListener('quiz-wrong', () => this.play('quiz_wrong'));
            window.addEventListener('quiz-finish', () => this.play('quiz_finish'));

            // Hook toast notifications for errors & explicitly defined sounds
            window.addEventListener('toast', (e) => {
                const detail = e.detail || {};
                if (detail.sound) {
                    this.play(detail.sound, detail);
                } else if (detail.type === 'error') {
                    this.play('error', { dedupeKey: 'toast_error_' + (detail.message || '') });
                } else if (detail.type === 'level_up') {
                    this.play('level_up', detail);
                }
            });
        }

        getAudioContext() {
            if (!this.audioCtx) {
                const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                if (!AudioContextClass) return null;
                try {
                    this.audioCtx = new AudioContextClass();
                    this.masterGain = this.audioCtx.createGain();
                    this.masterGain.gain.setValueAtTime(this.volume, this.audioCtx.currentTime);
                    this.masterGain.connect(this.audioCtx.destination);
                } catch (e) {
                    return null;
                }
            }
            if (this.audioCtx && this.audioCtx.state === 'suspended' && this.userInteracted) {
                this.audioCtx.resume().catch(() => {});
            }
            return this.audioCtx;
        }

        /**
         * Check if interface sounds should be suppressed.
         * Suppresses during:
         * - User disabled or 0 volume
         * - Document hidden in background tab
         * - Cold start period (prevents page-load sound spam)
         * - Active audiobook, video, or flipbook audio playback
         * - Active live stream / mic broadcast
         */
        isSuppressed(isTest = false) {
            if (!this.enabled) return true;
            if (this.volume <= 0.001) return true;

            // When testing via settings preview button, bypass media suppression
            if (isTest) return false;

            if (this.coldStart) return true;
            if (typeof document !== 'undefined' && document.hidden) return true;

            // Check if any audio/video media is currently playing
            if (this.isMediaPlaying()) return true;

            // Check if microphone is currently recording or broadcasting
            if (this.isMicrophoneActive()) return true;

            return false;
        }

        isMediaPlaying() {
            try {
                if (typeof document === 'undefined') return false;
                const mediaEls = document.querySelectorAll('audio, video');
                for (let i = 0; i < mediaEls.length; i++) {
                    const el = mediaEls[i];
                    // Skip if previewing an audio tag specifically created by this sound manager
                    if (el.dataset && el.dataset.kitobUiSound) continue;
                    if (!el.paused && !el.ended && el.currentTime > 0 && el.volume > 0) {
                        return true;
                    }
                }
            } catch (e) {}
            return false;
        }

        isMicrophoneActive() {
            try {
                if (typeof window !== 'undefined') {
                    if (window.__isVoiceRecording || window.__isMicActive || window.__liveStudioMicActive) {
                        return true;
                    }
                }
            } catch (e) {}
            return false;
        }

        canPlay(soundName, dedupeKey = null, isTest = false) {
            if (isTest) return true;
            if (this.isSuppressed()) return false;

            const now = Date.now();

            // Global minimum interval (prevents chaotic audio overlap)
            if (now - this.lastSoundTime < 60) {
                return false;
            }

            // Deduplication and per-sound cooldown
            const key = dedupeKey || soundName;
            const lastTime = this.recentEvents.get(key) || 0;
            const cooldown = COOLDOWNS[soundName] || 350;

            if (now - lastTime < cooldown) {
                return false;
            }

            this.recentEvents.set(key, now);
            this.lastSoundTime = now;
            return true;
        }

        /**
         * Main play entry point
         * @param {string} soundName
         * @param {object} options { dedupeKey, isTest, forceAudioFile }
         */
        play(soundName, options = {}) {
            const isTest = Boolean(options.isTest);
            const dedupeKey = options.dedupeKey || null;

            if (!this.canPlay(soundName, dedupeKey, isTest)) {
                return false;
            }

            // Ensure AudioContext is available
            const ctx = this.getAudioContext();
            if (!ctx) {
                // If Web Audio API is unsupported, fall back to HTML5 Audio element
                return this.playLocalAudioFile(soundName);
            }

            // If browser autoplay policy suspended the context and no user gesture occurred yet, do not queue
            if (ctx.state === 'suspended' && !this.userInteracted && !isTest) {
                return false;
            }

            // Update master gain with current volume
            if (this.masterGain) {
                this.masterGain.gain.setValueAtTime(this.volume, ctx.currentTime);
            }

            try {
                switch (soundName) {
                    case 'bookmark':
                        this.synthBookmark(ctx);
                        break;
                    case 'settings_saved':
                        this.synthSettingsSaved(ctx);
                        break;
                    case 'quiz_correct':
                        this.synthQuizCorrect(ctx);
                        break;
                    case 'quiz_wrong':
                        this.synthQuizWrong(ctx);
                        break;
                    case 'quiz_finish':
                        this.synthQuizFinish(ctx);
                        break;
                    case 'achievement':
                        this.synthAchievement(ctx);
                        break;
                    case 'level_up':
                        this.synthLevelUp(ctx);
                        break;
                    case 'daily_bonus':
                        this.synthDailyBonus(ctx);
                        break;
                    case 'note_saved':
                        this.synthNoteSaved(ctx);
                        break;
                    case 'group_message':
                        this.synthGroupMessage(ctx);
                        break;
                    case 'purchase':
                        this.synthPurchase(ctx);
                        break;
                    case 'chat_message':
                        this.synthChatMessage(ctx);
                        break;
                    case 'error':
                        this.synthError(ctx);
                        break;
                    default:
                        // Fallback to local audio file if custom sound
                        return this.playLocalAudioFile(soundName);
                }
                return true;
            } catch (err) {
                // Fallback to local WAV file on synthesis error
                return this.playLocalAudioFile(soundName);
            }
        }

        /**
         * Test a sound directly (used by settings preview)
         */
        test(soundName = 'settings_saved') {
            this.userInteracted = true;
            const ctx = this.getAudioContext();
            if (ctx && ctx.state === 'suspended') {
                ctx.resume().catch(() => {});
            }
            return this.play(soundName, { isTest: true });
        }

        // ═══════════════════════════════════════════════════════════════════
        // PROCEDURAL ACOUSTIC SYNTHESIS ENGINES (Smooth Envelopes, Anti-Clip)
        // ═══════════════════════════════════════════════════════════════════

        /**
         * 1. Bookmark: Short, mellow wooden tap / pop (~95ms)
         */
        synthBookmark(ctx) {
            const t = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            const filter = ctx.createBiquadFilter();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(520, t);
            osc.frequency.exponentialRampToValueAtTime(210, t + 0.08);

            filter.type = 'lowpass';
            filter.frequency.setValueAtTime(1400, t);
            filter.Q.setValueAtTime(1.2, t);

            // Envelope: 3ms attack, smooth exponential decay
            gain.gain.setValueAtTime(0.0001, t);
            gain.gain.exponentialRampToValueAtTime(0.55, t + 0.003);
            gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.09);

            osc.connect(filter);
            filter.connect(gain);
            gain.connect(this.masterGain);

            osc.start(t);
            osc.stop(t + 0.095);
        }

        /**
         * 2. Settings Saved: Gentle two-tone confirmation chord (~300ms)
         */
        synthSettingsSaved(ctx) {
            const t = ctx.currentTime;

            // Note 1: E5 (659.25Hz)
            this.playSineTone(ctx, 659.25, t, 0.12, 0.40, 2200);

            // Note 2: A5 (880.00Hz)
            this.playSineTone(ctx, 880.00, t + 0.10, 0.18, 0.45, 2600);
        }

        /**
         * 3. Quiz Correct: Delicate ascending chime C5 -> E5 -> G5 (~280ms)
         */
        synthQuizCorrect(ctx) {
            const t = ctx.currentTime;
            this.playChimeNote(ctx, 523.25, t, 0.11, 0.35);        // C5
            this.playChimeNote(ctx, 659.25, t + 0.07, 0.13, 0.38); // E5
            this.playChimeNote(ctx, 783.99, t + 0.14, 0.18, 0.42); // G5
        }

        /**
         * 4. Quiz Wrong: Polite, gentle low tone (~220ms, non-annoying warm wood)
         */
        synthQuizWrong(ctx) {
            const t = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            const filter = ctx.createBiquadFilter();

            osc.type = 'triangle';
            osc.frequency.setValueAtTime(155, t);
            osc.frequency.linearRampToValueAtTime(125, t + 0.20);

            filter.type = 'lowpass';
            filter.frequency.setValueAtTime(450, t);

            gain.gain.setValueAtTime(0.0001, t);
            gain.gain.linearRampToValueAtTime(0.40, t + 0.008);
            gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.21);

            osc.connect(filter);
            filter.connect(gain);
            gain.connect(this.masterGain);

            osc.start(t);
            osc.stop(t + 0.22);
        }

        /**
         * 5. Quiz Finish: Gentle closing chord G4-C5-E5 (~420ms)
         */
        synthQuizFinish(ctx) {
            const t = ctx.currentTime;
            const freqs = [392.00, 523.25, 659.25];
            freqs.forEach((freq) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, t);

                gain.gain.setValueAtTime(0.0001, t);
                gain.gain.linearRampToValueAtTime(0.24, t + 0.008);
                gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.40);

                osc.connect(gain);
                gain.connect(this.masterGain);

                osc.start(t);
                osc.stop(t + 0.42);
            });
        }

        /**
         * 6. Achievement: Celestial harp arpeggio C5 -> G5 -> C6 -> E6 (~950ms)
         */
        synthAchievement(ctx) {
            const t = ctx.currentTime;
            const notes = [
                { freq: 523.25, delay: 0.00, dur: 0.35, gain: 0.28 }, // C5
                { freq: 783.99, delay: 0.12, dur: 0.45, gain: 0.30 }, // G5
                { freq: 1046.50, delay: 0.24, dur: 0.55, gain: 0.32 },// C6
                { freq: 1318.51, delay: 0.36, dur: 0.65, gain: 0.34 } // E6
            ];

            notes.forEach((n) => {
                this.playChimeNote(ctx, n.freq, t + n.delay, n.dur, n.gain);
            });
        }

        /**
         * 6b. Level Up: Majestic celestial fanfare C5 -> E5 -> G5 -> C6 -> E6 (~950ms)
         */
        synthLevelUp(ctx) {
            const t = ctx.currentTime;
            const notes = [
                { freq: 523.25, delay: 0.00, dur: 0.35, gain: 0.28 }, // C5
                { freq: 659.25, delay: 0.10, dur: 0.40, gain: 0.30 }, // E5
                { freq: 783.99, delay: 0.20, dur: 0.48, gain: 0.32 }, // G5
                { freq: 1046.50, delay: 0.32, dur: 0.58, gain: 0.34 },// C6
                { freq: 1318.51, delay: 0.45, dur: 0.65, gain: 0.36 } // E6
            ];

            notes.forEach((n) => {
                this.playChimeNote(ctx, n.freq, t + n.delay, n.dur, n.gain);
            });
        }

        /**
         * 6c. Daily Bonus: Joyful congratulatory marimba cascade G5 -> B5 -> D6 -> G6 (~420ms)
         */
        synthDailyBonus(ctx) {
            const t = ctx.currentTime;
            const notes = [
                { freq: 783.99, delay: 0.00, dur: 0.14, gain: 0.32 }, // G5
                { freq: 987.77, delay: 0.06, dur: 0.16, gain: 0.34 }, // B5
                { freq: 1174.66, delay: 0.12, dur: 0.20, gain: 0.36 },// D6
                { freq: 1567.98, delay: 0.18, dur: 0.26, gain: 0.38 } // G6
            ];

            notes.forEach((n) => {
                this.playChimeNote(ctx, n.freq, t + n.delay, n.dur, n.gain);
            });
        }

        /**
         * 6d. Note Saved: Gentle bookmark-like double tap with acoustic warmth (~140ms)
         */
        synthNoteSaved(ctx) {
            const t = ctx.currentTime;

            // Tap 1: 480Hz -> 300Hz (~65ms)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            const filter1 = ctx.createBiquadFilter();

            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(480, t);
            osc1.frequency.exponentialRampToValueAtTime(300, t + 0.06);

            filter1.type = 'lowpass';
            filter1.frequency.setValueAtTime(1500, t);

            gain1.gain.setValueAtTime(0.0001, t);
            gain1.gain.exponentialRampToValueAtTime(0.48, t + 0.003);
            gain1.gain.exponentialRampToValueAtTime(0.0001, t + 0.065);

            osc1.connect(filter1);
            filter1.connect(gain1);
            gain1.connect(this.masterGain);

            osc1.start(t);
            osc1.stop(t + 0.07);

            // Tap 2: 660Hz -> 420Hz (~80ms, starts at t + 0.055)
            const t2 = t + 0.055;
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            const filter2 = ctx.createBiquadFilter();

            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(660, t2);
            osc2.frequency.exponentialRampToValueAtTime(420, t2 + 0.075);

            filter2.type = 'lowpass';
            filter2.frequency.setValueAtTime(1600, t2);

            gain2.gain.setValueAtTime(0.0001, t2);
            gain2.gain.exponentialRampToValueAtTime(0.50, t2 + 0.003);
            gain2.gain.exponentialRampToValueAtTime(0.0001, t2 + 0.08);

            osc2.connect(filter2);
            filter2.connect(gain2);
            gain2.connect(this.masterGain);

            osc2.start(t2);
            osc2.stop(t2 + 0.085);
        }

        /**
         * 6e. Group Message: Soft ambient harmonic ping for open groups (~180ms)
         */
        synthGroupMessage(ctx) {
            const t = ctx.currentTime;
            this.playSineTone(ctx, 587.33, t, 0.16, 0.30, 1800); // D5
            this.playSineTone(ctx, 880.00, t + 0.03, 0.18, 0.34, 2000); // A5
        }

        /**
         * 7. Purchase: Light shimmering coin chime (~320ms)
         */
        synthPurchase(ctx) {
            const t = ctx.currentTime;
            // High crystalline double ping: B5 and E6
            this.playCoinNote(ctx, 987.77, t, 0.14, 0.26);
            this.playCoinNote(ctx, 1318.51, t + 0.08, 0.22, 0.28);
        }

        /**
         * 8. Chat Message: Soft droplet / bubble pop (~160ms)
         */
        synthChatMessage(ctx) {
            const t = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            const filter = ctx.createBiquadFilter();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(620, t);
            osc.frequency.linearRampToValueAtTime(880, t + 0.04);
            osc.frequency.exponentialRampToValueAtTime(740, t + 0.15);

            filter.type = 'lowpass';
            filter.frequency.setValueAtTime(1800, t);

            gain.gain.setValueAtTime(0.0001, t);
            gain.gain.linearRampToValueAtTime(0.42, t + 0.005);
            gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.15);

            osc.connect(filter);
            filter.connect(gain);
            gain.connect(this.masterGain);

            osc.start(t);
            osc.stop(t + 0.16);
        }

        /**
         * 9. Error: Muted, calm double pulse (~250ms)
         */
        synthError(ctx) {
            const t = ctx.currentTime;
            this.playMutedPulse(ctx, 220.00, t, 0.09, 0.35); // A3
            this.playMutedPulse(ctx, 196.00, t + 0.09, 0.13, 0.38); // G3
        }

        // ═══════════════════════════════════════════════════════════════════
        // ACOUSTIC HELPER OSCILLATORS
        // ═══════════════════════════════════════════════════════════════════

        playSineTone(ctx, freq, startTime, duration, targetGain, filterFreq = 2400) {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            const filter = ctx.createBiquadFilter();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, startTime);

            filter.type = 'lowpass';
            filter.frequency.setValueAtTime(filterFreq, startTime);

            gain.gain.setValueAtTime(0.0001, startTime);
            gain.gain.linearRampToValueAtTime(targetGain, startTime + 0.005);
            gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);

            osc.connect(filter);
            filter.connect(gain);
            gain.connect(this.masterGain);

            osc.start(startTime);
            osc.stop(startTime + duration + 0.01);
        }

        playChimeNote(ctx, freq, startTime, duration, targetGain) {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            const filter = ctx.createBiquadFilter();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, startTime);

            filter.type = 'lowpass';
            filter.frequency.setValueAtTime(3200, startTime);

            gain.gain.setValueAtTime(0.0001, startTime);
            gain.gain.linearRampToValueAtTime(targetGain, startTime + 0.004);
            gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);

            osc.connect(filter);
            filter.connect(gain);
            gain.connect(this.masterGain);

            osc.start(startTime);
            osc.stop(startTime + duration + 0.01);
        }

        playCoinNote(ctx, freq, startTime, duration, targetGain) {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            const filter = ctx.createBiquadFilter();

            osc.type = 'triangle';
            osc.frequency.setValueAtTime(freq, startTime);

            filter.type = 'bandpass';
            filter.frequency.setValueAtTime(freq, startTime);
            filter.Q.setValueAtTime(3.0, startTime);

            gain.gain.setValueAtTime(0.0001, startTime);
            gain.gain.linearRampToValueAtTime(targetGain, startTime + 0.003);
            gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);

            osc.connect(filter);
            filter.connect(gain);
            gain.connect(this.masterGain);

            osc.start(startTime);
            osc.stop(startTime + duration + 0.01);
        }

        playMutedPulse(ctx, freq, startTime, duration, targetGain) {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            const filter = ctx.createBiquadFilter();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, startTime);

            filter.type = 'lowpass';
            filter.frequency.setValueAtTime(550, startTime);

            gain.gain.setValueAtTime(0.0001, startTime);
            gain.gain.linearRampToValueAtTime(targetGain, startTime + 0.006);
            gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);

            osc.connect(filter);
            filter.connect(gain);
            gain.connect(this.masterGain);

            osc.start(startTime);
            osc.stop(startTime + duration + 0.01);
        }

        /**
         * Fallback: Plays local WAV asset if available
         */
        playLocalAudioFile(soundName) {
            if (typeof window === 'undefined' || typeof Audio === 'undefined') return false;
            try {
                let audio = this.audioElementCache.get(soundName);
                if (!audio) {
                    audio = new Audio(`/sounds/ui/${soundName}.wav`);
                    audio.dataset.kitobUiSound = 'true';
                    audio.preload = 'auto';
                    this.audioElementCache.set(soundName, audio);
                }
                audio.volume = Math.max(0, Math.min(1, this.volume));
                audio.currentTime = 0;
                const p = audio.play();
                if (p && typeof p.catch === 'function') {
                    p.catch(() => {});
                }
                return true;
            } catch (e) {
                return false;
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // USER PREFERENCE MANAGEMENT
        // ═══════════════════════════════════════════════════════════════════

        setEnabled(val, persist = true) {
            this.enabled = Boolean(val);
            if (persist) {
                try {
                    localStorage.setItem(STORAGE_KEY_ENABLED, this.enabled ? 'true' : 'false');
                } catch (e) {}
            }
            if (typeof window !== 'undefined') {
                window.dispatchEvent(new CustomEvent('kitob-sound-preference-changed', {
                    detail: { enabled: this.enabled, volume: this.volume }
                }));
            }
        }

        setVolume(val, persist = true) {
            const num = Math.max(0, Math.min(1, parseFloat(val) || 0));
            this.volume = num;
            if (this.masterGain && this.audioCtx) {
                this.masterGain.gain.setValueAtTime(this.volume, this.audioCtx.currentTime);
            }
            if (persist) {
                try {
                    localStorage.setItem(STORAGE_KEY_VOLUME, this.volume.toString());
                } catch (e) {}
            }
            if (typeof window !== 'undefined') {
                window.dispatchEvent(new CustomEvent('kitob-sound-preference-changed', {
                    detail: { enabled: this.enabled, volume: this.volume }
                }));
            }
        }

        isEnabled() {
            return this.enabled;
        }

        getVolume() {
            return this.volume;
        }

        getSoundList() {
            return SOUND_CATALOG;
        }
    }

    const instance = new KitobSoundManager();
    if (typeof window !== 'undefined') {
        window.KitobSound = instance;
    }
    return instance;
}));
