{{-- AI Typing Keyboard Sound Effect Component --}}
<script>
(function() {
    const SOUND_URL = "{{ asset('sounds/dragon-studio-keyboard-typing-sound-effect-335503.mp3') }}";
    let typingAudio = null;
    let fadeTimer = null;
    let safetyTimer = null;
    let isMuted = false;

    try {
        isMuted = localStorage.getItem('ai_typing_sound_muted') === 'true';
    } catch(e) {}

    function getAudio() {
        if (!typingAudio) {
            typingAudio = new Audio(SOUND_URL);
            typingAudio.loop = true;
            typingAudio.volume = 0.35;
            typingAudio.preload = 'auto';
        }
        return typingAudio;
    }

    /**
     * Start playing the keyboard typing sound effect
     * @param {number} volume - Volume between 0.0 and 1.0 (default 0.35)
     */
    window.playAiTypingSound = function(volume = 0.35) {
        if (isMuted) return;
        if (window.KitobSound && !window.KitobSound.isEnabled()) return;
        try {
            const audio = getAudio();
            if (fadeTimer) {
                clearInterval(fadeTimer);
                fadeTimer = null;
            }
            if (safetyTimer) {
                clearTimeout(safetyTimer);
                safetyTimer = null;
            }

            let effectiveVolume = volume;
            if (window.KitobSound && typeof window.KitobSound.getVolume === 'function') {
                const masterVol = window.KitobSound.getVolume();
                if (masterVol <= 0.001) return;
                effectiveVolume = volume * (masterVol / 0.20);
            }
            if (effectiveVolume <= 0.001) return;
            audio.volume = Math.max(0, Math.min(1.0, effectiveVolume));

            if (audio.paused) {
                if (audio.currentTime === 0 && audio.duration > 3) {
                    audio.currentTime = Math.random() * 2.5;
                }
                const p = audio.play();
                if (p && typeof p.catch === 'function') {
                    p.catch(function(err) {
                        console.debug('[AI Typing Sound] play suppressed:', err);
                    });
                }
            }

            // Safety timer: maximum 20 seconds, guarantees audio will NEVER loop indefinitely
            safetyTimer = setTimeout(function() {
                window.stopAiTypingSound(true);
            }, 20000);
        } catch (e) {
            console.debug('[AI Typing Sound] play error:', e);
        }
    };

    /**
     * Stop the keyboard typing sound effect with an optional smooth fade-out
     * @param {boolean} fade - Whether to fade out smoothly over ~180ms
     */
    window.stopAiTypingSound = function(fade = true) {
        try {
            if (safetyTimer) {
                clearTimeout(safetyTimer);
                safetyTimer = null;
            }

            if (!typingAudio || typingAudio.paused) return;

            if (fadeTimer) {
                clearInterval(fadeTimer);
                fadeTimer = null;
            }

            if (!fade) {
                typingAudio.pause();
                typingAudio.currentTime = 0;
                return;
            }

            const step = typingAudio.volume / 6;
            fadeTimer = setInterval(function() {
                if (typingAudio.volume > step) {
                    typingAudio.volume = Math.max(0, typingAudio.volume - step);
                } else {
                    clearInterval(fadeTimer);
                    fadeTimer = null;
                    typingAudio.pause();
                    typingAudio.currentTime = 0;
                    typingAudio.volume = 0.35;
                }
            }, 30);
        } catch (e) {
            console.debug('[AI Typing Sound] stop error:', e);
        }
    };

    /**
     * Check if sound is muted
     */
    window.isAiTypingSoundMuted = function() {
        return isMuted;
    };

    /**
     * Toggle sound muting and remember preference
     */
    window.toggleAiTypingSoundMute = function() {
        isMuted = !isMuted;
        try {
            localStorage.setItem('ai_typing_sound_muted', isMuted ? 'true' : 'false');
        } catch(e) {}

        if (isMuted) {
            window.stopAiTypingSound(false);
        }

        window.dispatchEvent(new CustomEvent('ai-typing-mute-changed', { detail: { isMuted: isMuted } }));

        if (window.showToast) {
            window.showToast(isMuted ? "AI klaviatura ovozi o'chirildi 🔇" : "AI klaviatura ovozi yoqildi 🔊", "info");
        }

        return isMuted;
    };

    // Ensure audio is stopped on page refresh, unload, or tab switch
    window.addEventListener('beforeunload', function() {
        window.stopAiTypingSound(false);
    });
    window.addEventListener('pagehide', function() {
        window.stopAiTypingSound(false);
    });
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            window.stopAiTypingSound(false);
        }
    });
    window.addEventListener('kitob-sound-preference-changed', function(e) {
        if (!e.detail || !e.detail.enabled || (typeof e.detail.volume === 'number' && e.detail.volume <= 0.001)) {
            window.stopAiTypingSound(true);
        } else if (typingAudio && !typingAudio.paused && typeof e.detail.volume === 'number') {
            const masterVol = e.detail.volume;
            typingAudio.volume = Math.max(0, Math.min(1.0, 0.35 * (masterVol / 0.20)));
        }
    });

    /**
     * Attach Livewire listeners for BookAssistant
     */
    function attachLivewireAiHooks() {
        if (!window.Livewire || window.__aiTypingLivewireHooked) return;
        window.__aiTypingLivewireHooked = true;

        window.Livewire.hook('message.sent', function(message, component) {
            const updates = (message && message.updateQueue) ? message.updateQueue : [];
            const isAskAction = updates.some(function(u) {
                return u.payload && u.payload.method === 'ask';
            });

            // ONLY play if the user is explicitly executing an 'ask' method
            if (isAskAction) {
                window.playAiTypingSound(0.35);
            }
        });

        window.Livewire.hook('message.processed', function(message, component) {
            const updates = (message && message.updateQueue) ? message.updateQueue : [];
            const isAskAction = updates.some(function(u) {
                return u.payload && (u.payload.method === 'ask' || u.payload.method === 'clearHistory');
            });
            const name = (component && component.fingerprint && component.fingerprint.name) ? component.fingerprint.name : '';

            if (isAskAction || name.includes('book-assistant')) {
                setTimeout(function() {
                    window.stopAiTypingSound(true);
                }, 400);
            }
        });

        window.Livewire.hook('message.failed', function() {
            window.stopAiTypingSound(false);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachLivewireAiHooks);
    } else {
        attachLivewireAiHooks();
    }
    document.addEventListener('livewire:load', attachLivewireAiHooks);
})();
</script>
