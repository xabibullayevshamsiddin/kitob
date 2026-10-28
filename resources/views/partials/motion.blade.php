{{--
    Kitobxon — Motion Layer (ui-ux-pro-max: "Motion-Driven" + "Aurora UI" uslublari)
    design-system.blade.php orqali HAMMA sahifaga ulanadi. Sahifalarni alohida tahrirlash shart emas:
      • Brendlangan zamonaviy minimalist loader (Davra_books logosi va to'lqinlari)
      • scroll-reveal (IntersectionObserver) + stagger + sarlavha so'zlari animatsiyasi
      • aurora fon (sekin 18s oqim) + parallax
      • kartalarda kursor-spotlight va ko'tarilish, tugmalarda sheen + ripple
      • raqam sanash (.ks-stat), scroll-progress, sahifa kirish/chiqish o'tishi
    prefers-reduced-motion yoqilgan bo'lsa JS umuman ishga tushmaydi.
    Qo'lda boshqarish: data-reveal="up|zoom|left|right", data-no-reveal.
--}}
<script>
    (function () {
        var root = document.documentElement;
        var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduce || window.__ksMotionInit) return;
        window.__ksMotionInit = true;

        var path = location.pathname;
        var calm  = /\/(live|reader|read|chapter)(\/|$)/i.test(path);   // jonli efir / o'qish: yengil rejim
        var dense = /\/(admin|teacher)(\/|$)/i.test(path);               // admin/o'qituvchi: DENSITY 7, kam harakat

        root.classList.add('ks-motion');
        if (dense) root.classList.add('ks-dense');
        if (!calm) {
            root.classList.add('ks-pre');
            setTimeout(function () { root.classList.remove('ks-pre'); }, 1800); // xavfsizlik
        }

        function init() {
            var body = document.body;
            var aur = null;

            /* ---------- Brendlangan nafis minimalist loader (Davra_books) ---------- */
            var logoUrl = @js(asset('storage/logo/photo_2026-10-26_14-48-15.jpg'));
            var logoFallback = @js(asset('images/logo.png'));
            var cur = document.createElement('div');
            cur.className = 'ks-curtain';
            cur.setAttribute('aria-hidden', 'true');
            cur.innerHTML =
                '<div class="ks-cur-glow ks-cur-glow-teal"></div>' +
                '<div class="ks-cur-glow ks-cur-glow-gold"></div>' +
                '<div class="ks-cur-stage">' +
                    '<div class="ks-cur-logo-box">' +
                        '<div class="ks-cur-halo"></div>' +
                        '<div class="ks-cur-halo-2"></div>' +
                        '<div class="ks-cur-logo-badge">' +
                            '<img src="' + logoUrl + '" onerror="this.onerror=null;this.src=\'' + logoFallback + '\';" alt="Davra_books" class="ks-cur-logo-img">' +
                        '</div>' +
                    '</div>' +
                    '<div class="ks-cur-title">' +
                        '<span>Davra</span><em>_books</em>' +
                    '</div>' +
                    '<div class="ks-cur-waves">' +
                        '<i></i><i></i><i></i><i></i><i></i>' +
                    '</div>' +
                    '<div class="ks-cur-line-track">' +
                        '<div class="ks-cur-line-shimmer"></div>' +
                    '</div>' +
                    '<div class="ks-cur-sub">Yuklanmoqda</div>' +
                '</div>';
            root.appendChild(cur);

            window.ksCurtain = {
                show: function () {
                    cur.classList.add('is-on');
                    try { sessionStorage.setItem('ksNav', '1'); } catch (e) {}
                },
                hide: function () {
                    cur.classList.remove('is-on');
                    try { sessionStorage.removeItem('ksNav'); } catch (e) {}
                }
            };
            try {
                if (sessionStorage.getItem('ksNav')) {
                    sessionStorage.removeItem('ksNav');
                    cur.classList.add('is-on', 'ks-instant');
                    setTimeout(function () {
                        cur.classList.remove('ks-instant');
                        void cur.offsetWidth;
                        cur.classList.remove('is-on');
                    }, 400);
                }
            } catch (e) {}

            /* ---------- Aurora fon ---------- */
            if (!calm && !dense) {
                aur = document.createElement('div');
                aur.className = 'ks-aurora';
                aur.setAttribute('aria-hidden', 'true');
                aur.innerHTML = '<i></i><i></i><i></i>';
                body.insertBefore(aur, body.firstChild);
            }

            /* ---------- Scroll progress ---------- */
            var bar = document.createElement('div');
            bar.className = 'ks-progress';
            bar.setAttribute('aria-hidden', 'true');
            body.appendChild(bar);
            var header = document.getElementById('site-header');
            var ticking = false;
            function onScroll() {
                if (ticking) return;
                ticking = true;
                requestAnimationFrame(function () {
                    var h = document.documentElement.scrollHeight - innerHeight;
                    var y = window.scrollY || 0;
                    bar.style.transform = 'scaleX(' + (h > 0 ? Math.min(1, y / h) : 0) + ')';
                    if (aur) aur.style.transform = 'translate3d(0,' + (-y * 0.07) + 'px,0)';
                    if (header) header.classList.toggle('ks-scrolled', y > 24);
                    ticking = false;
                });
            }
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();

            /* ---------- Scroll-reveal ---------- */
            if (!calm && 'IntersectionObserver' in window) {
                var EXCL = '[x-show],[x-cloak],[data-no-reveal],.fixed,.sticky,dialog,[role="dialog"],[wire\\:loading],[wire\\:poll]';
                var TSEL = 'section, article, aside, .grid > *, .ks-card, .ks-panel, [class*="rounded-panel"], [data-reveal]';
                var SKIPTAG = { SCRIPT: 1, STYLE: 1, TEMPLATE: 1, TR: 1, TD: 1, TH: 1, LI: 1, OPTION: 1, SVG: 1 };

                function allowed(el) {
                    if (el.nodeType !== 1 || SKIPTAG[el.tagName]) return false;
                    if (el.matches(EXCL) || el.closest(EXCL)) return false;
                    if (!el.hasAttribute('data-reveal') && el.closest('header,nav')) return false;
                    return true;
                }
                function depth(el) {
                    var n = 0, p = el.parentElement;
                    while (p) { if (p.hasAttribute && p.hasAttribute('data-ks')) n++; p = p.parentElement; }
                    return n;
                }

                var io = new IntersectionObserver(function (entries) {
                    var vis = entries.filter(function (e) { return e.isIntersecting; });
                    vis.sort(function (a, b) {
                        return (a.boundingClientRect.top - b.boundingClientRect.top) || (a.boundingClientRect.left - b.boundingClientRect.left);
                    });
                    vis.forEach(function (e, i) {
                        var el = e.target;
                        io.unobserve(el);
                        el.style.setProperty('--ks-d', Math.min(i, 8) * 70 + 'ms');
                        el.classList.add('ks-in');
                        var done = function () {
                            el.removeAttribute('data-ks');
                            el.classList.remove('ks-in');
                            el.style.removeProperty('--ks-d');
                        };
                        el.addEventListener('animationend', function (ev) { if (ev.target === el) done(); });
                        setTimeout(done, 2600);
                    });
                }, { rootMargin: '0px 0px -6% 0px', threshold: 0.01 });

                var ioStat = new IntersectionObserver(function (entries) {
                    entries.forEach(function (e) {
                        if (!e.isIntersecting) return;
                        ioStat.unobserve(e.target);
                        countUp(e.target);
                    });
                }, { threshold: 0.4 });

                function countUp(n) {
                    var txt = n.textContent.trim();
                    if (!/^\d[\d\s,]*$/.test(txt)) return;
                    var target = parseInt(txt.replace(/[\s,]/g, ''), 10);
                    if (!target || target > 1e9) return;
                    var sep = /[,\s]/.test(txt), t0 = performance.now(), dur = 1300;
                    (function step(t) {
                        var p = Math.min(1, (t - t0) / dur), v = Math.round(target * (1 - Math.pow(1 - p, 4)));
                        n.textContent = sep ? v.toLocaleString('en-US') : String(v);
                        if (p < 1) requestAnimationFrame(step);
                    })(t0);
                }

                function mark(scope) {
                    var list = [];
                    if (scope.matches && scope.matches(TSEL)) list.push(scope);
                    list = list.concat([].slice.call(scope.querySelectorAll(TSEL)));
                    list.forEach(function (el) {
                        if (el.__ks || !allowed(el) || depth(el) >= 3) return;
                        el.__ks = 1;
                        var v = el.getAttribute('data-reveal') || (el.parentElement && el.parentElement.classList.contains('grid') ? 'zoom' : 'up');
                        el.setAttribute('data-ks', v);
                        io.observe(el);
                    });
                    [].slice.call(scope.querySelectorAll('.ks-stat:not([x-text])')).forEach(function (n) {
                        if (n.__st) return; n.__st = 1; ioStat.observe(n);
                    });
                }
                mark(body);

                var pending = [], scheduled = false;
                new MutationObserver(function (muts) {
                    muts.forEach(function (m) {
                        m.addedNodes.forEach(function (n) { if (n.nodeType === 1) pending.push(n); });
                    });
                    if (scheduled || !pending.length) return;
                    scheduled = true;
                    requestAnimationFrame(function () {
                        var batch = pending; pending = []; scheduled = false;
                        batch.forEach(function (n) { if (document.body.contains(n)) mark(n); });
                    });
                }).observe(body, { childList: true, subtree: true });
            }

            /* ---------- Birinchi H1: so'zma-so'z paydo bo'lish ---------- */
            if (!calm && !dense) {
                var h = document.querySelector('main h1, h1');
                if (h && !h.closest('[wire\\:id],[data-no-reveal]') && h.getBoundingClientRect().top < innerHeight) {
                    var i = 0;
                    (function walk(n) {
                        [].slice.call(n.childNodes).forEach(function (c) {
                            if (c.nodeType === 3) {
                                if (!c.textContent.trim()) return;
                                var f = document.createDocumentFragment();
                                c.textContent.split(/(\s+)/).forEach(function (p) {
                                    if (p === '') return;
                                    if (/^\s+$/.test(p)) { f.appendChild(document.createTextNode(p)); return; }
                                    var s = document.createElement('span');
                                    s.className = 'ks-w';
                                    s.style.setProperty('--wd', (140 + i * 75) + 'ms');
                                    s.textContent = p; i++;
                                    f.appendChild(s);
                                });
                                c.parentNode.replaceChild(f, c);
                            } else if (c.nodeType === 1 && c.tagName !== 'BR' && c.tagName.toLowerCase() !== 'svg') {
                                walk(c);
                            }
                        });
                    })(h);
                }
            }

            /* ---------- Kartalarda kursor-spotlight ---------- */
            var PSEL = '.ks-card,.ks-panel,.spotlight-card,[class*="rounded-panel"],[class*="rounded-card"]';
            document.addEventListener('pointermove', function (e) {
                if (e.pointerType === 'touch' || !e.target.closest) return;
                var t = e.target.closest(PSEL);
                if (!t) return;
                var r = t.getBoundingClientRect();
                t.style.setProperty('--mx', (e.clientX - r.left) + 'px');
                t.style.setProperty('--my', (e.clientY - r.top) + 'px');
            }, { passive: true });

            /* ---------- Tugma ripple ---------- */
            document.addEventListener('pointerdown', function (e) {
                var b = e.target.closest && e.target.closest('[class*="ks-btn"]');
                if (!b) return;
                var r = b.getBoundingClientRect(), s = Math.max(r.width, r.height) * 2;
                var el = document.createElement('span');
                el.className = 'ks-ripple';
                el.style.cssText = 'width:' + s + 'px;height:' + s + 'px;left:' + (e.clientX - r.left - s / 2) + 'px;top:' + (e.clientY - r.top - s / 2) + 'px';
                b.appendChild(el);
                setTimeout(function () { el.remove(); }, 650);
            });

            /* ---------- Sahifadan chiqish o'tishi ---------- */
            if (!calm) {
                document.addEventListener('click', function (e) {
                    if (e.defaultPrevented || e.button || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || !e.target.closest) return;
                    var a = e.target.closest('a[href]');
                    if (!a || (a.target && a.target !== '_self') || a.hasAttribute('download')) return;
                    var href = a.getAttribute('href') || '';
                    if (href.charAt(0) === '#' || /^(mailto|tel|javascript):/i.test(href)) return;
                    var u; try { u = new URL(a.href, location.href); } catch (x) { return; }
                    if (u.origin !== location.origin) return;
                    if (u.pathname === location.pathname && u.search === location.search) return;
                    if (/\.(pdf|zip|mp3|mp4|epub|docx?|png|jpe?g|webp|gif)(\?|$)/i.test(u.pathname) || /\/(storage|download|logout)(\/|$)/i.test(u.pathname)) return;
                    for (var k = 0; k < a.attributes.length; k++) {
                        var n = a.attributes[k].name;
                        if (n.indexOf('wire:') === 0 || n.indexOf('@') === 0 || n.indexOf('x-on') === 0 || n === 'data-no-leave') return;
                    }
                    e.preventDefault();
                    window.ksCurtain.show();
                    try { sessionStorage.setItem('ksNav', '1'); } catch (x) {}
                    setTimeout(function () { location.href = a.href; }, 420);
                    setTimeout(function () { window.ksCurtain.hide(); }, 3500);
                });
                window.addEventListener('pageshow', function (e) { if (e.persisted) window.ksCurtain.hide(); });
            }

            /* ---------- Kirish: body paydo bo'lishi ---------- */
            requestAnimationFrame(function () {
                requestAnimationFrame(function () { root.classList.remove('ks-pre'); });
            });
        }

        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
        else init();
    })();
</script>

<style>
    :root { --ks-ease: cubic-bezier(0.16, 1, 0.3, 1); --ks-teal: #2DD4BF; --ks-coral: #FB7185; }

    /* ── Sahifa kirish / chiqish ── */
    html, html.ks-pre, html.ks-leave { background-color: #07090E; }
    html.light:not(.force-dark), html.light:not(.force-dark).ks-pre, html.light:not(.force-dark).ks-leave { background-color: #F3EFE7; }
    html.ks-motion body { transition: opacity .5s ease; }
    html.ks-pre body { opacity: 0; }
    html.ks-leave body { opacity: 0; transition-duration: .17s; }

    /* ── Scroll-reveal. Oxirida transform QOLMAYDI (fixed bolalar buzilmasligi uchun) ── */
    html.ks-motion [data-ks] { opacity: 0; }
    html.ks-motion [data-ks].ks-in { opacity: 1; animation: ksRevUp .85s var(--ks-ease) var(--ks-d, 0ms) backwards; }
    html.ks-motion [data-ks="zoom"].ks-in  { animation-name: ksRevZoom; }
    html.ks-motion [data-ks="left"].ks-in  { animation-name: ksRevLeft; }
    html.ks-motion [data-ks="right"].ks-in { animation-name: ksRevRight; }
    html.ks-dense [data-ks].ks-in { animation-duration: .4s; }
    @keyframes ksRevUp    { from { opacity: 0; transform: translate3d(0, 30px, 0); } }
    @keyframes ksRevZoom  { from { opacity: 0; transform: translate3d(0, 18px, 0) scale(.94); } }
    @keyframes ksRevLeft  { from { opacity: 0; transform: translate3d(-36px, 0, 0); } }
    @keyframes ksRevRight { from { opacity: 0; transform: translate3d(36px, 0, 0); } }

    /* ── Sarlavha so'zlari ── */
    .ks-w { display: inline-block; animation: ksWord .9s var(--ks-ease) var(--wd, 0ms) backwards; }
    @keyframes ksWord { from { opacity: 0; transform: translate3d(0, .55em, 0) rotate(2deg); filter: blur(6px); } }
    h1 [class*="text-gold"] .ks-w, h1 [class*="text-amber"] .ks-w {
        background: linear-gradient(100deg, #F59E0B 10%, #FDE68A 40%, #FB7185 62%, #F59E0B 90%);
        background-size: 220% 100%;
        -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent;
        animation: ksWord .9s var(--ks-ease) var(--wd, 0ms) backwards, ksShine 7s linear 1.2s infinite;
    }
    @keyframes ksShine { to { background-position: -220% 0; } }

    /* ── Aurora fon (Aurora UI: 8–18s sekin oqim, komplementar: amber–teal–coral) ── */
    .ks-aurora { position: fixed; inset: -10vh 0; z-index: 0; pointer-events: none; overflow: hidden; mix-blend-mode: screen; opacity: .6; }
    .ks-aurora i { position: absolute; border-radius: 50%; filter: blur(90px); will-change: transform; }
    .ks-aurora i:nth-child(1) { width: 46vw; height: 46vw; left: -12vw; top: -8vw;  background: radial-gradient(circle, rgba(245,158,11,.30), transparent 66%); animation: ksDriftA 18s ease-in-out infinite alternate; }
    .ks-aurora i:nth-child(2) { width: 40vw; height: 40vw; right: -10vw; top: 18vh;  background: radial-gradient(circle, rgba(45,212,191,.20), transparent 66%); animation: ksDriftB 22s ease-in-out infinite alternate; }
    .ks-aurora i:nth-child(3) { width: 36vw; height: 36vw; left: 28vw; bottom: -14vw; background: radial-gradient(circle, rgba(251,113,133,.20), transparent 66%); animation: ksDriftC 26s ease-in-out infinite alternate; }
    @keyframes ksDriftA { to { transform: translate3d(14vw, 12vh, 0) scale(1.18); } }
    @keyframes ksDriftB { to { transform: translate3d(-16vw, -10vh, 0) scale(1.1); } }
    @keyframes ksDriftC { to { transform: translate3d(-12vw, -14vh, 0) scale(1.22); } }
    @media (max-width: 768px) { .ks-aurora i { filter: blur(60px); } .ks-aurora { opacity: .45; } }

    /* ── Brendlangan nafis minimalist loader (Davra_books) ── */
    .ks-curtain {
        position: fixed; inset: 0; z-index: 99990;
        display: flex; align-items: center; justify-content: center;
        background: radial-gradient(ellipse at 50% 46%, #0b1526 0%, #060b14 55%, #030509 100%);
        overflow: hidden; user-select: none;
        opacity: 0; visibility: hidden; pointer-events: none;
        transition: opacity .3s ease, visibility 0s linear .3s;
    }
    .ks-curtain.is-on { opacity: 1; visibility: visible; pointer-events: auto; transition: opacity .22s ease; }
    .ks-curtain.ks-instant { transition: none !important; }

    /* Mayin fon nuri (ortiqcha yuklamasiz) */
    .ks-curtain .ks-cur-glow { position: absolute; border-radius: 50%; filter: blur(90px); pointer-events: none; opacity: 0.35; }
    .ks-curtain .ks-cur-glow-teal {
        width: 380px; height: 380px; left: 50%; top: 48%; transform: translate(-50%, -50%);
        background: radial-gradient(circle, rgba(0, 168, 181, 0.45) 0%, rgba(0, 229, 255, 0.15) 40%, transparent 70%);
        animation: ksBreathe 4s ease-in-out infinite alternate;
    }
    .ks-curtain .ks-cur-glow-gold {
        width: 260px; height: 260px; left: 50%; top: 48%; transform: translate(-50%, -50%);
        background: radial-gradient(circle, rgba(245, 158, 11, 0.25) 0%, transparent 65%);
        animation: ksBreathe 5s ease-in-out infinite alternate-reverse;
    }
    @keyframes ksBreathe {
        0% { transform: translate(-50%, -50%) scale(0.92); opacity: 0.25; }
        100% { transform: translate(-50%, -50%) scale(1.08); opacity: 0.45; }
    }

    /* Markaziy sahna */
    .ks-curtain .ks-cur-stage {
        position: relative; z-index: 10;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        text-align: center;
    }

    /* Logotip va uning atrofidagi nafis aylanuvchi chiziqlar */
    .ks-curtain .ks-cur-logo-box {
        position: relative; width: 88px; height: 88px; margin-bottom: 22px;
        display: flex; align-items: center; justify-content: center;
    }
    .ks-curtain .ks-cur-halo {
        position: absolute; inset: -6px; border-radius: 28px;
        border: 1.5px solid transparent;
        border-top-color: rgba(0, 229, 255, 0.7);
        border-right-color: rgba(245, 158, 11, 0.45);
        animation: ksSpin 3.5s linear infinite;
    }
    .ks-curtain .ks-cur-halo-2 {
        position: absolute; inset: -12px; border-radius: 34px;
        border: 1px dashed rgba(0, 168, 181, 0.25);
        animation: ksSpinRev 8s linear infinite;
    }
    @keyframes ksSpin { to { transform: rotate(360deg); } }
    @keyframes ksSpinRev { to { transform: rotate(-360deg); } }

    .ks-curtain .ks-cur-logo-badge {
        position: relative; width: 82px; height: 82px; padding: 6px;
        background: #FFFFFF; border-radius: 24px;
        box-shadow: 0 16px 36px -8px rgba(0, 168, 181, 0.35), 0 0 20px -2px rgba(0, 229, 255, 0.25);
        display: flex; align-items: center; justify-content: center;
        animation: ksFloat 3s ease-in-out infinite alternate;
    }
    @keyframes ksFloat {
        0% { transform: translateY(0); }
        100% { transform: translateY(-4px); }
    }
    .ks-curtain .ks-cur-logo-img { width: 100%; height: 100%; object-fit: contain; border-radius: 18px; pointer-events: none; user-select: none; }

    /* Brend sarlavhasi */
    .ks-curtain .ks-cur-title {
        font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 24px; font-weight: 700;
        letter-spacing: -0.015em;
        color: #F8FAFC;
        display: flex; align-items: center; gap: 2px;
        margin-bottom: 8px;
    }
    .ks-curtain .ks-cur-title span { color: #FFFFFF; }
    .ks-curtain .ks-cur-title em { font-style: normal; color: #00E5FF; }

    /* Tovush to'lqinlari (logotip o'ngidagi to'lqinlar ramzi) */
    .ks-curtain .ks-cur-waves {
        display: inline-flex; align-items: center; gap: 3.5px;
        margin-bottom: 16px; height: 16px;
    }
    .ks-curtain .ks-cur-waves i {
        display: block; width: 2.5px; height: 4px; border-radius: 2px;
        background: linear-gradient(180deg, #00E5FF 0%, #00A8B5 100%);
        animation: ksWave 1.2s ease-in-out infinite alternate;
    }
    .ks-curtain .ks-cur-waves i:nth-child(1) { animation-delay: 0.0s; height: 6px; }
    .ks-curtain .ks-cur-waves i:nth-child(2) { animation-delay: 0.2s; height: 14px; }
    .ks-curtain .ks-cur-waves i:nth-child(3) { animation-delay: 0.4s; height: 10px; }
    .ks-curtain .ks-cur-waves i:nth-child(4) { animation-delay: 0.1s; height: 16px; }
    .ks-curtain .ks-cur-waves i:nth-child(5) { animation-delay: 0.3s; height: 8px; }
    @keyframes ksWave {
        0% { transform: scaleY(0.4); opacity: 0.4; }
        100% { transform: scaleY(1.2); opacity: 1; }
    }

    /* Minimalist yorug'lik chizig'i */
    .ks-curtain .ks-cur-line-track {
        position: relative; width: 160px; height: 2px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 9999px; overflow: hidden;
    }
    .ks-curtain .ks-cur-line-shimmer {
        position: absolute; left: 0; top: 0; bottom: 0; width: 45%;
        background: linear-gradient(90deg, transparent 0%, #00A8B5 30%, #00E5FF 70%, #F59E0B 100%);
        border-radius: 9999px;
        box-shadow: 0 0 10px #00E5FF;
        animation: ksShimmer 1.4s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    }
    @keyframes ksShimmer {
        0% { transform: translateX(-120%); }
        100% { transform: translateX(240%); }
    }

    .ks-curtain .ks-cur-sub {
        margin-top: 10px; font-family: "DM Mono", ui-monospace, monospace; font-size: 11px; font-weight: 500;
        letter-spacing: 0.22em; text-transform: uppercase; color: #64748B;
    }

    @media (prefers-reduced-motion: reduce) { .ks-curtain { display: none !important; } }

    /* ── Scroll progress ── */
    .ks-progress {
        position: fixed; top: 0; left: 0; width: 100%; height: 2px; z-index: 9999; pointer-events: none;
        transform: scaleX(0); transform-origin: 0 50%;
        background: linear-gradient(90deg, #F59E0B, var(--ks-coral), var(--ks-teal));
        box-shadow: 0 0 12px rgba(245,158,11,.55);
    }
    #site-header { transition: box-shadow .4s var(--ks-ease), background-color .4s; }
    #site-header.ks-scrolled { box-shadow: 0 14px 34px -14px rgba(0,0,0,.8); }

    /* ── Kartalar: kursor-spotlight + ko'tarilish ── */
    html.ks-motion body .ks-card, html.ks-motion body .ks-panel,
    html.ks-motion body [class*="rounded-panel"], html.ks-motion body [class*="rounded-card"] {
        transition: transform .5s var(--ks-ease), border-color .3s, box-shadow .45s var(--ks-ease), background-color .3s;
    }
    html.ks-motion body .ks-card:not([class*="bg-gradient"]):hover,
    html.ks-motion body .ks-panel:not([class*="bg-gradient"]):hover,
    html.ks-motion body [class*="rounded-panel"]:not([class*="bg-gradient"]):hover,
    html.ks-motion body [class*="rounded-card"]:not([class*="bg-gradient"]):hover {
        background-image: radial-gradient(420px circle at var(--mx, 50%) var(--my, 50%), rgba(245,158,11,.10), transparent 62%);
        border-color: rgba(245,158,11,.28);
    }
    html.ks-motion body a[class*="rounded-panel"]:hover, html.ks-motion body a[class*="rounded-card"]:hover,
    html.ks-motion body .ks-lift:hover, html.ks-motion body .spotlight-card:hover {
        transform: translate3d(0, -5px, 0);
        border-color: rgba(245,158,11,.38);
        box-shadow: 0 24px 46px -18px rgba(0,0,0,.8), 0 0 38px -14px rgba(245,158,11,.38);
    }
    .spotlight-card svg, a[class*="rounded-panel"] svg { transition: transform .5s var(--ks-ease); }
    .spotlight-card:hover svg, a[class*="rounded-panel"]:hover svg { transform: scale(1.14) rotate(-6deg); }

    /* ── Tugmalar: sheen + ripple ── */
    [class*="ks-btn"]:not(.absolute):not(.fixed):not(.sticky) { position: relative; overflow: hidden; isolation: isolate; }
    .ks-btn-gold::after, .ks-btn-primary::after {
        content: ""; position: absolute; inset: 0; z-index: -1; pointer-events: none;
        background: linear-gradient(105deg, transparent 32%, rgba(255,255,255,.38) 50%, transparent 68%);
        transform: translateX(-130%);
        animation: ksSheen 5.5s ease-in-out 1.5s infinite;
    }
    .ks-btn-gold:hover::after, .ks-btn-primary:hover::after { animation: none; transform: translateX(130%); transition: transform .7s var(--ks-ease); }
    @keyframes ksSheen { 0%, 70% { transform: translateX(-130%); } 100% { transform: translateX(130%); } }
    .ks-ripple { position: absolute; border-radius: 50%; pointer-events: none; background: rgba(255,255,255,.34); transform: scale(0); animation: ksRipple .6s ease-out forwards; }
    @keyframes ksRipple { to { transform: scale(1); opacity: 0; } }
    .ks-btn-gold, .ks-btn-primary { transition: transform .3s var(--ks-ease), background-color .2s, box-shadow .3s; }
    .ks-btn-gold:hover { box-shadow: 0 10px 28px -8px rgba(245,158,11,.55); transform: translate3d(0,-2px,0); }
    .ks-btn-primary:hover { box-shadow: 0 10px 28px -8px rgba(193,57,43,.6); transform: translate3d(0,-2px,0); }

    /* ── Formalar: fokus nuri ── */
    input:focus-visible, textarea:focus-visible, select:focus-visible { box-shadow: 0 0 0 4px rgba(245,158,11,.14); }

    /* ── Yordamchi: suzuvchi element ── */
    .ks-float { animation: ksFloat 6s ease-in-out infinite; }
    @keyframes ksFloat { 50% { transform: translate3d(0, -10px, 0); } }

    @media (prefers-reduced-motion: reduce) {
        .ks-aurora, .ks-progress, .ks-ripple { display: none !important; }
        [data-ks] { opacity: 1 !important; }
    }
</style>
