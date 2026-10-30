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

            // Xavfsiz, engil ksCurtain obyekti (mavjud pjax/yuklashlar uchun orqaga moslik)
            var curtainEl = document.getElementById('ks-curtain-layer');
            if (!curtainEl && !isCalm) {
                curtainEl = document.createElement('div');
                curtainEl.id = 'ks-curtain-layer';
                curtainEl.className = 'ks-curtain';
                curtainEl.setAttribute('aria-hidden', 'true');
                curtainEl.innerHTML = 
                    '<div class="ks-cur-stage">' +
                        '<div class="ks-cur-line-track">' +
                            '<div class="ks-cur-line-shimmer"></div>' +
                        '</div>' +
                    '</div>';
                body.appendChild(curtainEl);
            }

            var curtainTimer = null;
            window.ksCurtain = {
                show: function () {
                    if (!curtainEl || (window.KitobMotion && window.KitobMotion.isReduced())) return;
                    curtainEl.classList.add('is-on');
                    if (curtainTimer) clearTimeout(curtainTimer);
                    curtainTimer = setTimeout(function () {
                        if (curtainEl) curtainEl.classList.remove('is-on');
                    }, 2500); // Har doim avtomatik tozalash kafolati
                },
                hide: function () {
                    if (curtainTimer) { clearTimeout(curtainTimer); curtainTimer = null; }
                    if (curtainEl) curtainEl.classList.remove('is-on');
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

            // Sahifa navigatsiyasida (bfcache) tozalash
            window.addEventListener('pageshow', function (e) {
                if (window.ksCurtain) window.ksCurtain.hide();
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
    /* ── Scroll progress ── */
    .ks-progress {
        position: fixed; top: 0; left: 0; width: 100%; height: 2px; z-index: 9999; pointer-events: none;
        transform: scaleX(0); transform-origin: 0 50%;
        background: linear-gradient(90deg, #F59E0B, #FB7185, #2DD4BF);
        box-shadow: 0 0 10px rgba(245, 158, 11, 0.45);
        transition: transform 60ms linear;
    }
    #site-header { transition: box-shadow var(--ks-dur-normal) var(--ks-ease), background-color var(--ks-dur-normal); }
    #site-header.ks-scrolled { box-shadow: 0 10px 30px -10px rgba(0,0,0,.7); }

    /* ── Nafis, engil parda loader (faqat zarur amallarda chaqiriladi) ── */
    .ks-curtain {
        position: fixed; inset: 0; z-index: 99990;
        display: flex; align-items: center; justify-content: center;
        background: rgba(7, 9, 14, 0.75);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        opacity: 0; visibility: hidden; pointer-events: none;
        transition: opacity var(--ks-dur-fast) var(--ks-ease), visibility 0s linear var(--ks-dur-fast);
    }
    .ks-curtain.is-on {
        opacity: 1; visibility: visible; pointer-events: auto;
        transition: opacity var(--ks-dur-fast) var(--ks-ease);
    }
    .ks-curtain .ks-cur-stage {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .ks-curtain .ks-cur-line-track {
        position: relative; width: 140px; height: 2.5px;
        background: rgba(255, 255, 255, 0.12);
        border-radius: 9999px; overflow: hidden;
    }
    .ks-curtain .ks-cur-line-shimmer {
        position: absolute; left: 0; top: 0; bottom: 0; width: 40%;
        background: linear-gradient(90deg, transparent 0%, #00E5FF 50%, #F59E0B 100%);
        border-radius: 9999px;
        box-shadow: 0 0 8px #00E5FF;
        animation: ksShimmer 1.1s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    }
    @keyframes ksShimmer {
        0%   { transform: translateX(-120%); }
        100% { transform: translateX(260%); }
    }

    @media (prefers-reduced-motion: reduce) {
        .ks-progress, .ks-curtain { display: none !important; }
    }
</style>
