{{-- AI Typing Keyboard Sound Effect Component --}}
<div id="ai-typing-sound-root" class="hidden" aria-hidden="true" wire:ignore>
    <audio id="aiTypingAudioElement"
           preload="auto"
           loop
           class="hidden">
        <source src="{{ asset('sounds/dragon-studio-keyboard-typing-sound-effect-335503.mp3') }}" type="audio/mpeg">
    </audio>
</div>

<script>
(function() {
    const SOUND_URL = "{{ asset('sounds/dragon-studio-keyboard-typing-sound-effect-335503.mp3') }}";
    let typingAudio = null;
    let fadeTimer = null;
    let isMuted = false;

    function getAudio() {
        if (!typingAudio) {
            const el = document.getElementById('aiTypingAudioElement');
            if (el) {
                typingAudio = el;
            } else {
                typingAudio = new Audio(SOUND_URL);
            }
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
        try {
            const audio = getAudio();
            if (fadeTimer) {
                clearInterval(fadeTimer);
                fadeTimer = null;
            }
            audio.volume = Math.max(0.05, Math.min(1.0, volume));

            if (audio.paused) {
                // Natural variance: offset slightly (0 - 2.5s) so the typing rhythm feels dynamic
                if (audio.currentTime === 0 && audio.duration > 3) {
                    audio.currentTime = Math.random() * 2.5;
                }
                const p = audio.play();
                if (p && typeof p.catch === 'function') {
                    p.catch(function(err) {
                        // Suppressed by browser autoplay policy before user interaction
                        console.debug('[AI Typing Sound] play suppressed:', err);
                    });
                }
            }
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
            const audio = typingAudio || document.getElementById('aiTypingAudioElement');
            if (!audio || audio.paused) return;

            if (fadeTimer) {
                clearInterval(fadeTimer);
                fadeTimer = null;
            }

            if (!fade) {
                audio.pause();
                audio.currentTime = 0;
                return;
            }

            const step = audio.volume / 6;
            fadeTimer = setInterval(function() {
                if (audio.volume > step) {
                    audio.volume = Math.max(0, audio.volume - step);
                } else {
                    clearInterval(fadeTimer);
                    fadeTimer = null;
                    audio.pause();
                    audio.currentTime = 0;
                    audio.volume = 0.35;
                }
            }, 30);
        } catch (e) {
            console.debug('[AI Typing Sound] stop error:', e);
        }
    };

    /**
     * Toggle sound muting
     */
    window.toggleAiTypingSoundMute = function() {
        isMuted = !isMuted;
        if (isMuted) {
            window.stopAiTypingSound(false);
        }
        return isMuted;
    };

    /**
     * Attach Livewire listeners for BookAssistant
     */
    function attachLivewireAiHooks() {
        if (!window.Livewire || window.__aiTypingLivewireHooked) return;
        window.__aiTypingLivewireHooked = true;

        window.Livewire.hook('message.sent', function(message, component) {
            const name = (component && component.fingerprint && component.fingerprint.name) ? component.fingerprint.name : '';
            const updates = (message && message.updateQueue) ? message.updateQueue : [];
            const isAiAction = updates.some(function(u) {
                const method = u.payload ? u.payload.method : '';
                return method === 'ask' || method === 'clearHistory';
            });

            if (name.includes('book-assistant') || isAiAction) {
                window.playAiTypingSound(0.35);
            }
        });

        window.Livewire.hook('message.processed', function(message, component) {
            const name = (component && component.fingerprint && component.fingerprint.name) ? component.fingerprint.name : '';
            if (name.includes('book-assistant')) {
                setTimeout(function() {
                    window.stopAiTypingSound(true);
                }, 500);
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
