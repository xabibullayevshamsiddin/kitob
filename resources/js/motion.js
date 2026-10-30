/**
 * Kitobxon — Yagona Mayin Animatsiyalar Dvigateli (Sitewide Motion Engine)
 * MASTER Design System v1.2
 * 
 * Imkoniyatlar:
 *   - Foydalanuvchi tanlovi: "system" (tizimga mos), "gentle" (mayin), "minimal" (minimal)
 *   - prefers-reduced-motion to'liq qo'llab-quvvatlanadi
 *   - Sun'iy kechikishlar (420ms link delays) va butun ekranni to'suvchi pardalar olib tashlangan
 *   - Bir martalik section reveal (IntersectionObserver) + transformlarni tozalash (settle)
 *   - Livewire 2 bilan to'liq integratsiya (message.processed)
 *   - Chatda faqat haqiqatan yangi kelgan xabarlargina mayin kiradi, pollingda eskilar qayta jonlanmaydi
 *   - Tanga va ball hisoblagichlarini mayin aylantirish (rollCounter)
 *   - Bir martalik yutuq tabriklari deduplikatsiyasi (celebrate)
 */

(function () {
    'use strict';

    if (window.__kitobMotionInitialized) return;
    window.__kitobMotionInitialized = true;

    const STORAGE_KEY = 'kitob_motion_pref';
    const VALID_MODES = ['system', 'gentle', 'minimal'];

    // ─────────────────────────────────────────────────────────────
    // 1. Motion Mode Management
    // ─────────────────────────────────────────────────────────────
    function resolveMode() {
        const root = document.documentElement;
        let mode = root.dataset.motion;
        if (!mode || !VALID_MODES.includes(mode)) {
            try {
                mode = localStorage.getItem(STORAGE_KEY) || 'system';
            } catch (e) {
                mode = 'system';
            }
        }
        if (!VALID_MODES.includes(mode)) mode = 'system';
        root.dataset.motion = mode;
        return mode;
    }

    function isReducedMotion() {
        const mode = resolveMode();
        if (mode === 'minimal') return true;
        return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    function setMotionMode(newMode) {
        if (!VALID_MODES.includes(newMode)) newMode = 'system';
        const root = document.documentElement;
        root.dataset.motion = newMode;
        try {
            localStorage.setItem(STORAGE_KEY, newMode);
        } catch (e) {}

        window.dispatchEvent(new CustomEvent('kitob-motion-changed', {
            detail: { mode: newMode, reduced: isReducedMotion() }
        }));
    }

    // Initialize dataset early
    resolveMode();

    // Listen for OS reduced-motion changes
    if (window.matchMedia) {
        const mq = window.matchMedia('(prefers-reduced-motion: reduce)');
        if (mq.addEventListener) {
            mq.addEventListener('change', () => {
                window.dispatchEvent(new CustomEvent('kitob-motion-changed', {
                    detail: { mode: resolveMode(), reduced: isReducedMotion() }
                }));
            });
        }
    }

    // Listen for Livewire / Alpine setting change
    window.addEventListener('motion-settings-updated', function (e) {
        const mode = e.detail && e.detail.mode ? e.detail.mode : 'system';
        setMotionMode(mode);
    });

    // ─────────────────────────────────────────────────────────────
    // 2. Section Reveal Observer (One-Time, Clean Transforms)
    // ─────────────────────────────────────────────────────────────
    let revealObserver = null;
    const REVEAL_EXCLUDE = '[data-motion-zone="calm"], [data-motion-calm], .reader-content, #online-reader, [x-cloak], dialog, [role="dialog"], [wire\\:loading]';

    function initRevealObserver() {
        if (typeof IntersectionObserver === 'undefined') {
            document.querySelectorAll('.ks-reveal').forEach(el => el.classList.add('is-revealed', 'is-settled'));
            return;
        }

        revealObserver = new IntersectionObserver((entries) => {
            const visible = entries.filter(e => e.isIntersecting);
            visible.sort((a, b) => {
                const rectA = a.boundingClientRect;
                const rectB = b.boundingClientRect;
                return (rectA.top - rectB.top) || (rectA.left - rectB.left);
            });

            visible.forEach((entry, idx) => {
                const el = entry.target;
                revealObserver.unobserve(el);

                if (isReducedMotion()) {
                    el.classList.add('is-revealed', 'is-settled');
                    return;
                }

                const delay = Math.min(idx * 30, 180);
                el.style.setProperty('--ks-reveal-delay', delay + 'ms');
                el.classList.add('is-revealed');

                const cleanup = () => {
                    el.classList.add('is-settled');
                    el.style.removeProperty('--ks-reveal-delay');
                    el.removeEventListener('transitionend', cleanup);
                };

                el.addEventListener('transitionend', cleanup, { once: true });
                setTimeout(cleanup, 600); // Safety fallback
            });
        }, {
            rootMargin: '0px 0px -4% 0px',
            threshold: 0.02
        });

        scanRevealElements(document.body);
    }

    function scanRevealElements(scope) {
        if (!scope || !revealObserver) return;
        if (isReducedMotion()) {
            const items = scope.querySelectorAll ? scope.querySelectorAll('.ks-reveal:not(.is-revealed)') : [];
            items.forEach(el => el.classList.add('is-revealed', 'is-settled'));
            return;
        }

        const elements = [];
        if (scope.matches && scope.matches('.ks-reveal:not(.is-revealed)')) {
            elements.push(scope);
        }
        if (scope.querySelectorAll) {
            elements.push(...scope.querySelectorAll('.ks-reveal:not(.is-revealed)'));
        }

        elements.forEach(el => {
            if (el.closest(REVEAL_EXCLUDE)) {
                el.classList.add('is-revealed', 'is-settled');
                return;
            }
            revealObserver.observe(el);
        });
    }

    // ─────────────────────────────────────────────────────────────
    // 3. Chat Message Animation Guard (Genuine New Messages Only)
    // ─────────────────────────────────────────────────────────────
    let isInitialChatScan = true;

    function scanChatMessages() {
        const containers = document.querySelectorAll('#chat-container, #group-chat-container, [data-chat-messages]');
        if (!containers.length) return;

        containers.forEach(container => {
            const messages = container.querySelectorAll('[wire\\:key^="msg-"], .chat-message-item, [data-message-id]');
            messages.forEach(msg => {
                if (isInitialChatScan) {
                    msg.dataset.ksSeen = '1';
                } else if (!msg.dataset.ksSeen) {
                    msg.dataset.ksSeen = '1';
                    if (!isReducedMotion()) {
                        msg.classList.add('ks-chat-new');
                        setTimeout(() => msg.classList.remove('ks-chat-new'), 350);
                    }
                }
            });
        });

        if (isInitialChatScan) {
            setTimeout(() => { isInitialChatScan = false; }, 400);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 4. Smooth Counter Roll (Tabular Numbers, No Jitter)
    // ─────────────────────────────────────────────────────────────
    function rollCounter(element, targetValue, options) {
        if (!element) return;
        const opt = options || {};
        const duration = opt.duration || (isReducedMotion() ? 0 : 500);
        const prefix = opt.prefix || '';
        const suffix = opt.suffix || '';
        const useLocale = opt.locale !== false;

        const currentText = element.textContent.replace(/[^0-9.-]/g, '');
        const startValue = parseFloat(currentText) || 0;
        const target = parseFloat(targetValue) || 0;

        if (duration <= 0 || isReducedMotion()) {
            element.textContent = prefix + (useLocale ? Math.round(target).toLocaleString() : Math.round(target)) + suffix;
            return;
        }

        const startTime = performance.now();
        function update(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            // Ease-out quartic
            const ease = 1 - Math.pow(1 - progress, 4);
            const current = Math.round(startValue + (target - startValue) * ease);

            element.textContent = prefix + (useLocale ? current.toLocaleString() : current) + suffix;

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                element.textContent = prefix + (useLocale ? Math.round(target).toLocaleString() : Math.round(target)) + suffix;
                if (opt.onComplete) opt.onComplete();
            }
        }

        requestAnimationFrame(update);
    }

    // ─────────────────────────────────────────────────────────────
    // 5. Celebration Deduplication
    // ─────────────────────────────────────────────────────────────
    function celebrate(options) {
        const opt = options || {};
        const key = opt.key || 'generic';
        const sessionKey = 'kitob_cel_' + key;

        // Ensure single firing per event key within session
        try {
            if (sessionStorage.getItem(sessionKey)) return false;
            sessionStorage.setItem(sessionKey, '1');
        } catch (e) {}

        if (isReducedMotion()) return false;

        if (typeof opt.action === 'function') {
            opt.action();
            return true;
        }

        if (window.confetti && typeof window.confetti === 'function') {
            window.confetti({
                particleCount: opt.particleCount || 40,
                spread: opt.spread || 55,
                origin: opt.origin || { y: 0.75 },
                colors: opt.colors || ['#F59E0B', '#2DD4BF', '#FB7185', '#E2E8F0']
            });
            return true;
        }
        return false;
    }

    // ─────────────────────────────────────────────────────────────
    // 6. Navigation Stability (BFCache & Scroll Lock Helpers)
    // ─────────────────────────────────────────────────────────────
    window.addEventListener('pageshow', function (event) {
        // If restored from back-forward cache, reset any lingering state
        document.body.classList.remove('ks-modal-open');
        document.documentElement.classList.remove('ks-pre', 'ks-leave');
        resolveMode();
    });

    // ─────────────────────────────────────────────────────────────
    // 7. Micro-Interactions (Icons, Bells & Books)
    // ─────────────────────────────────────────────────────────────
    function popIcon(el) {
        if (!el || isReducedMotion()) return;
        el.classList.remove('ks-icon-popping');
        void el.offsetWidth;
        el.classList.add('ks-icon-popping');
        setTimeout(() => el.classList.remove('ks-icon-popping'), 350);
    }

    function ringBell() {
        const bell = document.querySelector('.header-notification-btn svg');
        if (!bell || isReducedMotion()) return;
        bell.classList.remove('ks-bell-ring');
        void bell.offsetWidth;
        bell.classList.add('ks-bell-ring');
    }

    function animateCatalogCards(scope) {
        if (!scope || isReducedMotion()) return;
        const grid = scope.querySelector('.grid, [data-catalog-grid]');
        if (!grid) return;

        const cards = grid.querySelectorAll('.ks-book:not([data-ks-animated])');
        cards.forEach((card, idx) => {
            card.dataset.ksAnimated = '1';
            const delay = Math.min(idx * 30, 180);
            card.style.animation = `ksCardFadeIn 260ms var(--ks-ease) ${delay}ms both`;
            setTimeout(() => {
                card.style.animation = '';
            }, delay + 300);
        });
    }

    window.addEventListener('bookmark-toggled', function (e) {
        if (e.detail && e.detail.target) {
            popIcon(e.detail.target);
        }
    });

    window.addEventListener('notification-received', function () {
        ringBell();
    });

    // ─────────────────────────────────────────────────────────────
    // 8. Livewire 2 Lifecycle Hooks
    // ─────────────────────────────────────────────────────────────
    function hookLivewire() {
        if (window.Livewire && window.Livewire.hook) {
            window.Livewire.hook('message.sent', function (message, component) {
                if (component && component.el) {
                    const hasGrid = component.el.querySelector('.grid, [data-catalog-grid]');
                    const isPolling = message.updateQueue && message.updateQueue.some(u =>
                        u.type === 'callMethod' && (u.payload.method === 'poll' || u.payload.method === 'ping' || u.payload.method === 'checkLive')
                    );
                    if (hasGrid && !isPolling) {
                        component.el.classList.add('ks-livewire-loading');
                    }
                }
            });

            window.Livewire.hook('message.processed', function (message, component) {
                if (component && component.el) {
                    component.el.classList.remove('ks-livewire-loading');
                    scanRevealElements(component.el);
                    animateCatalogCards(component.el);
                }
                scanChatMessages();
            });

            window.Livewire.hook('message.failed', function (message, component) {
                if (component && component.el) {
                    component.el.classList.remove('ks-livewire-loading');
                }
            });
        }
    }

    if (window.Livewire) {
        hookLivewire();
    } else {
        document.addEventListener('livewire:load', hookLivewire);
    }

    // ─────────────────────────────────────────────────────────────
    // 9. Public API Export
    // ─────────────────────────────────────────────────────────────
    window.KitobMotion = {
        getMode: resolveMode,
        setMode: setMotionMode,
        isReduced: isReducedMotion,
        rollCounter: rollCounter,
        celebrate: celebrate,
        popIcon: popIcon,
        ringBell: ringBell,
        animateCards: animateCatalogCards,
        scanReveals: function (scope) { scanRevealElements(scope || document.body); },
        scanChat: scanChatMessages
    };

    // DOM Ready Initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initRevealObserver();
            scanChatMessages();
        });
    } else {
        initRevealObserver();
        scanChatMessages();
    }
})();
