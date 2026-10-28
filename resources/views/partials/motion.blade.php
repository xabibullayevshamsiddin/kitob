{{--
    Kitobxon — Motion Layer (ui-ux-pro-max: "Motion-Driven" + "Aurora UI" uslublari)
    design-system.blade.php orqali HAMMA sahifaga ulanadi. Sahifalarni alohida tahrirlash shart emas:
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

            /* ---------- Brendlangan loader (yangi Davra_books logosi bilan) — barcha sahifa uchun yagona ---------- */
            var logoUrl = @js(asset('storage/logo/photo_2026-10-26_14-48-15.jpg'));
            var logoFallback = @js(asset('images/logo.png'));
            var brandText = 'Davra_books';
            var word = brandText.split('').map(function (ch, n) {
                var isAccent = n >= 5; // '_books'
                return '<span class="' + (isAccent ? 'ks-cur-accent' : '') + '" style="--n:' + n + '">' + ch + '</span>';
            }).join('');

            var cur = document.createElement('div');
            cur.className = 'ks-curtain';
            cur.setAttribute('aria-hidden', 'true');
            cur.innerHTML =
                '<div class="ks-cur-glow"><i></i><i></i><i></i></div>' +
                '<div class="ks-cur-inner">' +
                  '<div class="ks-cur-logo-wrap">' +
                    '<span class="ks-cur-ring ks-cur-ring-1"></span>' +
                    '<span class="ks-cur-ring ks-cur-ring-2"></span>' +
                    '<span class="ks-cur-ring ks-cur-ring-3"></span>' +
                    '<div class="ks-cur-logo-card">' +
                      '<img src="' + logoUrl + '" onerror="this.onerror=null;this.src=\'' + logoFallback + '\';" alt="Davra_books" class="ks-cur-logo-img">' +
                    '</div>' +
                  '</div>' +
                  '<div class="ks-cur-word">' + word + '</div>' +
                  '<div class="ks-cur-sub"><span>Yuklanmoqda</span><em></em></div>' +
                  '<div class="ks-cur-bar"></div>' +
                '</div>';
            root.appendChild(cur);   // <html> ichida — body opacity'dan mustaqil
            window.ksCurtain = {
                show: function () { cur.classList.add('is-on'); try { sessionStorage.setItem('ksNav', '1'); } catch (e) {} },
                hide: function () { cur.classList.remove('is-on'); try { sessionStorage.removeItem('ksNav'); } catch (e) {} }
            };
            try {   // oldingi sahifadan o'tib kelgan bo'lsak: loader ko'rinib turadi va yumshoq yo'qoladi
                if (sessionStorage.getItem('ksNav')) {
                    sessionStorage.removeItem('ksNav');
                    cur.classList.add('is-on', 'ks-instant');
                    setTimeout(function () {
                        cur.classList.remove('ks-instant');
                        void cur.offsetWidth;
                        cur.classList.remove('is-on');
                    }, 450);
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

    /* ── Loader (curtain): Davra_books yangi logosi va to'lqinlari ── */
    .ks-curtain {
        position: fixed; inset: 0; z-index: 9990; display: grid; place-items: center; overflow: hidden;
        background: radial-gradient(ellipse at 50% 45%, #0B1626 0%, #060D17 55%, #03060B 100%);
        opacity: 0; visibility: hidden; pointer-events: none;
        transition: opacity .3s ease, visibility 0s linear .3s;
    }
    .ks-curtain.is-on { opacity: 1; visibility: visible; pointer-events: auto; transition: opacity .22s ease; }
    .ks-curtain.ks-instant { transition: none !important; }
    .ks-cur-glow i { position: absolute; border-radius: 50%; filter: blur(85px); }
    .ks-cur-glow i:nth-child(1) { width: 44vmax; height: 44vmax; left: 4%; top: 2%; background: radial-gradient(circle, rgba(0, 168, 181, 0.22), transparent 65%); animation: ksDriftA 10s ease-in-out infinite alternate; }
    .ks-cur-glow i:nth-child(2) { width: 38vmax; height: 38vmax; right: 4%; bottom: 0; background: radial-gradient(circle, rgba(12, 35, 64, 0.55), rgba(0, 168, 181, 0.14) 45%, transparent 68%); animation: ksDriftB 12s ease-in-out infinite alternate; }
    .ks-cur-glow i:nth-child(3) { width: 22vmax; height: 22vmax; left: 50%; top: 45%; transform: translate(-50%, -50%); background: radial-gradient(circle, rgba(0, 229, 255, 0.14), transparent 70%); animation: ksCenterPulse 3.5s ease-in-out infinite alternate; }
    @keyframes ksCenterPulse { 0% { opacity: 0.5; transform: translate(-50%, -50%) scale(0.9); } 100% { opacity: 1; transform: translate(-50%, -50%) scale(1.15); } }
    .ks-cur-inner { position: relative; text-align: center; transform: translateY(-2vh); }

    /* Logo badge & animated soundwave rings (logo o'ngidagi to'lqinlar ramzi) */
    .ks-cur-logo-wrap { position: relative; width: 104px; height: 104px; margin: 0 auto 24px; display: grid; place-items: center; }
    .ks-cur-ring {
        position: absolute; inset: -5px; border-radius: 30px; border: 1.5px solid rgba(0, 229, 255, 0.55);
        pointer-events: none; z-index: 1; opacity: 0;
        animation: ksRingPulse 2.8s cubic-bezier(0.16, 1, 0.3, 1) infinite;
    }
    .ks-cur-ring-1 { animation-delay: 0s; }
    .ks-cur-ring-2 { animation-delay: 0.9s; }
    .ks-cur-ring-3 { animation-delay: 1.8s; }
    @keyframes ksRingPulse {
        0% { transform: scale(0.88); opacity: 0.85; border-color: rgba(0, 229, 255, 0.7); }
        50% { opacity: 0.35; }
        100% { transform: scale(1.6); opacity: 0; border-color: rgba(0, 168, 181, 0); }
    }
    .ks-cur-logo-card {
        position: relative; width: 90px; height: 90px; padding: 6px; border-radius: 24px; background: #FFFFFF;
        border: 1.5px solid rgba(0, 168, 181, 0.5);
        box-shadow: 0 16px 42px -8px rgba(0, 168, 181, 0.55), 0 0 28px -2px rgba(0, 229, 255, 0.4), inset 0 0 0 1px rgba(255, 255, 255, 0.95);
        display: flex; align-items: center; justify-content: center; z-index: 2;
        animation: ksLogoLevitate 3.2s ease-in-out infinite alternate;
    }
    @keyframes ksLogoLevitate {
        0% { transform: translateY(0) scale(1); box-shadow: 0 16px 42px -8px rgba(0, 168, 181, 0.5), 0 0 24px rgba(0, 229, 255, 0.35); }
        100% { transform: translateY(-8px) scale(1.03); box-shadow: 0 24px 52px -10px rgba(0, 168, 181, 0.7), 0 0 36px rgba(0, 229, 255, 0.6); }
    }
    .ks-cur-logo-img { width: 100%; height: 100%; object-fit: contain; border-radius: 18px; pointer-events: none; user-select: none; }

    .ks-cur-word {
        font-family: var(--font-display, "Plus Jakarta Sans", system-ui, sans-serif);
        font-weight: 700; font-size: clamp(1.85rem, 5.5vw, 2.5rem); letter-spacing: -0.02em; color: #F8FAFC; line-height: 1.2;
    }
    .ks-cur-word span { display: inline-block; animation: ksLetter 2.2s ease-in-out infinite; animation-delay: calc(var(--n) * .07s); }
    .ks-cur-word span.ks-cur-accent { color: #00E5FF; }
    @keyframes ksLetter {
        0%, 60%, 100% { transform: translateY(0); }
        30% { transform: translateY(-6px); color: #2DD4BF; text-shadow: 0 0 24px rgba(45, 212, 191, 0.8), 0 0 12px rgba(0, 229, 255, 0.6); }
    }
    .ks-cur-sub {
        margin-top: 10px; font-family: "DM Mono", ui-monospace, monospace; font-size: 11px; font-weight: 600;
        letter-spacing: .28em; text-transform: uppercase; color: #7E9AB8;
    }
    .ks-cur-sub em { display: inline-block; width: 1.6em; text-align: left; font-style: normal; color: #00E5FF; }
    .ks-cur-sub em::after { content: ""; animation: ksDots 1.3s steps(1) infinite; }
    @keyframes ksDots { 0% { content: ""; } 25% { content: "."; } 50% { content: ".."; } 75% { content: "..."; } }
    .ks-cur-bar {
        position: relative; width: 210px; height: 3px; margin: 22px auto 0; border-radius: 9999px;
        background: rgba(12, 35, 64, 0.85); border: 1px solid rgba(0, 168, 181, 0.25); overflow: hidden;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.4);
    }
    .ks-cur-bar::after {
        content: ""; position: absolute; inset: 0 auto 0 0; width: 42%; border-radius: 9999px;
        background: linear-gradient(90deg, #00A8B5 0%, #00E5FF 60%, #FFFFFF 100%);
        box-shadow: 0 0 16px rgba(0, 229, 255, 0.9), 0 0 30px rgba(0, 168, 181, 0.5);
        animation: ksBar 1.25s cubic-bezier(.65, 0, .35, 1) infinite;
    }
    @keyframes ksBar { 0% { transform: translateX(-120%); } 100% { transform: translateX(290%); } }
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
