{{--
    Kitobxon — Yagona Mayin Animatsiyalar Qatlami (Sitewide Motion System)
    MASTER Design System v1.2 — Sokin, kitobxonlikka mos, tabiiy harakatlar.
    Vite orqali yuklanadigan resources/css/motion.css va resources/js/motion.js bilan to'liq integratsiya.
--}}
<script>
    (function () {
        'use strict';
        var root = document.documentElement;

        // Aniq atribut yoki URL orqali tinch/zich hududni aniqlash
        var path = location.pathname;
        var hasCalmAttr = root.hasAttribute('data-motion-calm') || root.getAttribute('data-motion-zone') === 'calm';
        var isCalmPath  = /\/(live|reader|read|chapter)(\/|$)/i.test(path);
        var isCalm      = hasCalmAttr || isCalmPath;

        var hasDenseAttr = root.hasAttribute('data-motion-dense') || root.getAttribute('data-motion-zone') === 'dense';
        var isDensePath  = /\/(admin|teacher)(\/|$)/i.test(path);
        var isDense      = hasDenseAttr || isDensePath;

        root.classList.add('ks-motion');
        if (isDense) root.classList.add('ks-dense');
        if (isCalm)  root.classList.add('ks-calm');

        function initDecor() {
            var body = document.body;
            if (!body) return;

            // ── Brendlangan Mayin Parda Loader (Davra_books) ──
            var curtainEl = document.getElementById('ks-curtain-layer');
            var logoUrl = @js(asset('storage/logo/photo_2026-10-26_14-48-15.jpg'));
            var logoFallback = @js(asset('images/logo.png'));

            if (!curtainEl && !isCalm) {
                curtainEl = document.createElement('div');
                curtainEl.id = 'ks-curtain-layer';
                curtainEl.className = 'ks-curtain';
                curtainEl.setAttribute('aria-hidden', 'true');
                curtainEl.innerHTML = 
                    '<div class="ks-cur-backdrop"></div>' +
                    '<div class="ks-cur-glow ks-cur-glow-teal"></div>' +
                    '<div class="ks-cur-glow ks-cur-glow-gold"></div>' +
                    '<div class="ks-cur-stage">' +
                        '<div class="ks-cur-logo-box">' +
                            '<div class="ks-cur-halo"></div>' +
                            '<div class="ks-cur-logo-badge">' +
                                '<img src="' + logoUrl + '" onerror="this.onerror=null;this.src=\'' + logoFallback + '\';" alt="Davra_books" class="ks-cur-logo-img" />' +
                            '</div>' +
                        '</div>' +
                        '<div class="ks-cur-brand">' +
                            '<span class="ks-cur-title">Davra_books</span>' +
                            '<span class="ks-cur-sub">{{ __("site.common.platform") ?? "Platformasi" }}</span>' +
                        '</div>' +
                        '<div class="ks-cur-waves">' +
                            '<i></i><i></i><i></i><i></i><i></i>' +
                        '</div>' +
                        '<div class="ks-cur-line-track">' +
                            '<div class="ks-cur-line-shimmer"></div>' +
                        '</div>' +
                        '<div class="ks-cur-status-text" data-ks-loading-text>{{ __("site.common.loading") ?? "Yuklanmoqda..." }}</div>' +
                    '</div>';
                body.appendChild(curtainEl);
            }

            // ── Debounced, Haqiqiy Yuklanishga Bog'langan ksCurtain Boshqaruvchisi ──
            var showTimeout = null;
            var safetyTimeout = null;
            var isCurtainVisible = false;

            window.ksCurtain = {
                show: function (options) {
                    if (!curtainEl) return;
                    // Agar foydalanuvchi minimal harakatni tanlagan bo'lsa ham indikator ko'rinadi (animatsiyasiz)
                    if (showTimeout) clearTimeout(showTimeout);
                    if (safetyTimeout) clearTimeout(safetyTimeout);

                    var delay = (options && typeof options.delay === 'number') ? options.delay : 140;

                    showTimeout = setTimeout(function () {
                        isCurtainVisible = true;
                        curtainEl.classList.add('is-on');

                        // 5.5 soniyalik xavfsizlik chegarasi: hech qachon foydalanuvchini yopiq parda ortida qoldirmaydi
                        safetyTimeout = setTimeout(function () {
                            if (isCurtainVisible) {
                                window.ksCurtain.hide();
                            }
                        }, 5500);
                    }, delay);
                },
                hide: function () {
                    if (showTimeout) { clearTimeout(showTimeout); showTimeout = null; }
                    if (safetyTimeout) { clearTimeout(safetyTimeout); safetyTimeout = null; }
                    isCurtainVisible = false;
                    if (curtainEl) {
                        curtainEl.classList.remove('is-on');
                    }
                },
                isVisible: function () {
                    return isCurtainVisible;
                }
            };

            // Scroll progress chizig'i (faqat kitob o'qish/jonli efir bo'lmagan sahifalarda)
            if (!isCalm && !document.querySelector('.ks-progress')) {
                var bar = document.createElement('div');
                bar.className = 'ks-progress';
                bar.setAttribute('aria-hidden', 'true');
                body.appendChild(bar);

                var header = document.getElementById('site-header');
                var ticking = false;

                var onScroll = function () {
                    if (ticking) return;
                    ticking = true;
                    requestAnimationFrame(function () {
                        var h = document.documentElement.scrollHeight - window.innerHeight;
                        var y = window.scrollY || 0;
                        if (h > 0) {
                            bar.style.transform = 'scaleX(' + Math.min(1, y / h) + ')';
                        }
                        if (header) {
                            header.classList.toggle('ks-scrolled', y > 24);
                        }
                        ticking = false;
                    });
                };

                window.addEventListener('scroll', onScroll, { passive: true });
                onScroll();
            }

            // Sahifa navigatsiyasida (bfcache / back-forward) loaderni darhol yashirish
            window.addEventListener('pageshow', function () {
                if (window.ksCurtain) window.ksCurtain.hide();
            });

            // Brauzer orqaga/oldinga navigatsiyasida tozalash
            window.addEventListener('popstate', function () {
                if (window.ksCurtain) window.ksCurtain.hide();
            });

            // Sahifani tark etayotganda yumshoq o'tish indikatori (faqat tashqi bo'lmagan amallarda)
            window.addEventListener('beforeunload', function (e) {
                if (window.ksCurtain && !(window.KitobMotion && window.KitobMotion.isReduced())) {
                    window.ksCurtain.show({ delay: 180 });
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initDecor);
        } else {
            initDecor();
        }
    })();
</script>

<style>
    /* ── Scroll progress chizig'i ── */
    .ks-progress {
        position: fixed; top: 0; left: 0; width: 100%; height: 2.5px; z-index: 9999; pointer-events: none;
        transform: scaleX(0); transform-origin: 0 50%;
        background: linear-gradient(90deg, #00A8B5 0%, #00E5FF 50%, #F59E0B 100%);
        box-shadow: 0 0 10px rgba(0, 168, 181, 0.55);
        transition: transform 60ms linear;
    }
    #site-header { transition: box-shadow var(--ks-dur-normal) var(--ks-ease), background-color var(--ks-dur-normal); }
    #site-header.ks-scrolled { box-shadow: 0 10px 30px -10px rgba(0,0,0,.7); }

    /* ── Brendlangan Mayin Parda Loader (Davra_books) ── */
    .ks-curtain {
        position: fixed; inset: 0; z-index: 99990;
        display: flex; align-items: center; justify-content: center;
        opacity: 0; pointer-events: none;
        transition: opacity 220ms cubic-bezier(0.22, 1, 0.36, 1);
    }
    .ks-curtain.is-on {
        opacity: 1; pointer-events: auto;
    }

    .ks-cur-backdrop {
        position: absolute; inset: 0;
        background: rgba(7, 18, 32, 0.88);
        backdrop-filter: blur(20px) saturate(140%);
        -webkit-backdrop-filter: blur(20px) saturate(140%);
    }
    html.light:not(.force-dark) .ks-cur-backdrop {
        background: rgba(255, 255, 255, 0.92);
    }

    /* Ambient Glow Pods */
    .ks-cur-glow {
        position: absolute; border-radius: 50%; pointer-events: none; filter: blur(60px); opacity: 0.35;
        width: 320px; height: 320px;
    }
    .ks-cur-glow-teal {
        background: radial-gradient(circle, rgba(0, 168, 181, 0.6) 0%, transparent 70%);
        top: 25%; left: 30%;
        animation: ksGlowPulse 4s ease-in-out infinite alternate;
    }
    .ks-cur-glow-gold {
        background: radial-gradient(circle, rgba(245, 158, 11, 0.45) 0%, transparent 70%);
        bottom: 25%; right: 30%;
        animation: ksGlowPulse 4.5s ease-in-out infinite alternate-reverse;
    }
    @keyframes ksGlowPulse {
        0% { transform: scale(0.9) translate(-10px, -10px); opacity: 0.25; }
        100% { transform: scale(1.1) translate(10px, 10px); opacity: 0.45; }
    }

    /* Markaziy bosqich (Stage) */
    .ks-cur-stage {
        position: relative; z-index: 2;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        text-align: center; padding: 2rem;
        transform: translateY(6px) scale(0.98);
        transition: transform 260ms cubic-bezier(0.22, 1, 0.36, 1);
    }
    .ks-curtain.is-on .ks-cur-stage {
        transform: translateY(0) scale(1);
    }

    /* Logotip qutisi */
    .ks-cur-logo-box {
        position: relative; width: 68px; height: 68px; margin-bottom: 14px;
        display: flex; align-items: center; justify-content: center;
    }
    .ks-cur-halo {
        position: absolute; inset: -8px; border-radius: 24px;
        background: linear-gradient(135deg, rgba(0, 168, 181, 0.4), rgba(245, 158, 11, 0.3));
        filter: blur(12px); opacity: 0.7;
        animation: ksHaloBreath 2.4s ease-in-out infinite alternate;
    }
    @keyframes ksHaloBreath {
        0% { transform: scale(0.92); opacity: 0.4; }
        100% { transform: scale(1.08); opacity: 0.85; }
    }
    .ks-cur-logo-badge {
        position: relative; width: 64px; height: 64px; border-radius: 18px;
        overflow: hidden; padding: 3px;
        background: #FFFFFF;
        border: 1.5px solid rgba(0, 168, 181, 0.5);
        box-shadow: 0 10px 25px -4px rgba(0, 0, 0, 0.4), inset 0 1px 1px rgba(255, 255, 255, 0.9);
    }
    .ks-cur-logo-img {
        width: 100%; height: 100%; object-fit: contain; border-radius: 14px;
    }

    /* Brend nomi */
    .ks-cur-brand {
        display: flex; flex-direction: column; align-items: center; margin-bottom: 10px;
    }
    .ks-cur-title {
        font-family: 'Cinzel', 'Playfair Display', Georgia, serif;
        font-size: 20px; font-weight: 700;
        letter-spacing: -0.01em;
        color: #F8FAFC;
    }
    html.light:not(.force-dark) .ks-cur-title {
        color: #0C1C36;
    }
    .ks-cur-sub {
        font-family: 'DM Mono', ui-monospace, monospace;
        font-size: 9px; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.22em;
        color: #94A3B8; margin-top: 2px;
    }
    html.light:not(.force-dark) .ks-cur-sub {
        color: #64748B;
    }

    /* Ovoz / varaqlash to'lqinlari */
    .ks-cur-waves {
        display: inline-flex; align-items: center; gap: 4px;
        height: 16px; margin-bottom: 14px;
    }
    .ks-cur-waves i {
        display: block; width: 2.5px; border-radius: 2px;
        background: linear-gradient(180deg, #00A8B5 0%, #00E5FF 100%);
        animation: ksWavePulse 1.1s ease-in-out infinite alternate;
    }
    .ks-cur-waves i:nth-child(1) { height: 6px; animation-delay: 0.0s; }
    .ks-cur-waves i:nth-child(2) { height: 14px; animation-delay: 0.2s; }
    .ks-cur-waves i:nth-child(3) { height: 10px; animation-delay: 0.4s; }
    .ks-cur-waves i:nth-child(4) { height: 16px; animation-delay: 0.15s; }
    .ks-cur-waves i:nth-child(5) { height: 8px; animation-delay: 0.3s; }
    @keyframes ksWavePulse {
        0% { transform: scaleY(0.4); opacity: 0.4; }
        100% { transform: scaleY(1.2); opacity: 1; }
    }

    /* Minimalist yorug'lik chizig'i (Laser track) */
    .ks-cur-line-track {
        position: relative; width: 150px; height: 2px;
        background: rgba(255, 255, 255, 0.12);
        border-radius: 9999px; overflow: hidden;
    }
    html.light:not(.force-dark) .ks-cur-line-track {
        background: rgba(12, 28, 54, 0.12);
    }
    .ks-cur-line-shimmer {
        position: absolute; left: 0; top: 0; bottom: 0; width: 45%;
        background: linear-gradient(90deg, transparent 0%, #00A8B5 40%, #00E5FF 70%, #F59E0B 100%);
        border-radius: 9999px;
        box-shadow: 0 0 10px #00E5FF;
        animation: ksShimmer 1.4s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    }
    @keyframes ksShimmer {
        0% { transform: translateX(-120%); }
        100% { transform: translateX(240%); }
    }

    .ks-cur-status-text {
        margin-top: 10px; font-family: "DM Mono", ui-monospace, monospace; font-size: 11px; font-weight: 500;
        letter-spacing: 0.16em; text-transform: uppercase; color: #94A3B8;
    }
    html.light:not(.force-dark) .ks-cur-status-text {
        color: #64748B;
    }

    /* ── Reduced Motion / Minimal Rejim ── */
    html[data-motion="minimal"] .ks-cur-glow,
    html[data-motion="minimal"] .ks-cur-halo,
    html[data-motion="minimal"] .ks-cur-waves i,
    @media (prefers-reduced-motion: reduce) {
        .ks-cur-glow, .ks-cur-halo { display: none !important; }
        .ks-cur-waves i { animation: none !important; transform: none !important; opacity: 0.7 !important; }
        .ks-cur-line-shimmer { animation: none !important; width: 100% !important; transform: none !important; }
        .ks-cur-stage { transform: none !important; transition: none !important; }
        .ks-curtain { transition: opacity 100ms linear !important; }
    }
</style>
