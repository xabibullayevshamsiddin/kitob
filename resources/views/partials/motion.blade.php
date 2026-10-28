{{--
    Kitobxon — Motion Layer (ui-ux-pro-max: "Motion-Driven" + "Aurora UI" uslublari)
    design-system.blade.php orqali HAMMA sahifaga ulanadi. Sahifalarni alohida tahrirlash shart emas:
      • 3D WebGL Three.js Loader (Davra_books 3D Holographic Book & Quantum Vortex)
      • scroll-reveal (IntersectionObserver) + stagger + sarlavha so'zlari animatsiyasi
      • aurora fon (sekin 18s oqim) + parallax
      • kartalarda kursor-spotlight va ko'tarilish, tugmalarda sheen + ripple
      • raqam sanash (.ks-stat), scroll-progress, sahifa kirish/chiqish o'tishi
    prefers-reduced-motion yoqilgan bo'lsa JS umuman ishga tushmaydi.
    Qo'lda boshqarish: data-reveal="up|zoom|left|right", data-no-reveal.
--}}
<script src="{{ asset('js/three.min.js') }}" onerror="this.onerror=null;this.src='https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js';"></script>
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

            /* ---------- 3D WebGL Three.js Holographic Loader (Davra_books) ---------- */
            var logoUrl = @js(asset('storage/logo/photo_2026-10-26_14-48-15.jpg'));
            var logoFallback = @js(asset('images/logo.png'));
            var cur = document.createElement('div');
            cur.className = 'ks-curtain';
            cur.setAttribute('aria-hidden', 'true');
            cur.innerHTML =
                '<div class="ks-glow-orb ks-glow-1"></div>' +
                '<div class="ks-glow-orb ks-glow-2"></div>' +
                '<div class="ks-glow-orb ks-glow-3"></div>' +
                '<canvas id="ks-3d-canvas" class="ks-cur-3d-canvas"></canvas>' +
                '<div class="ks-curtain-ui">' +
                    '<div class="ks-logo-pill">' +
                        '<img src="' + logoUrl + '" onerror="this.onerror=null;this.src=\'' + logoFallback + '\';" alt="Davra_books" class="ks-logo-pill-img">' +
                        '<span class="ks-logo-pill-txt">Davra Platformasi</span>' +
                    '</div>' +
                    '<div class="ks-brand-title">' +
                        '<span>Davra</span><span class="ks-accent">_books</span>' +
                    '</div>' +
                    '<div class="ks-status-badge">' +
                        '<span class="ks-status-dot"></span>' +
                        '<span class="ks-status-text" id="ks-status-label">BILIMLAR OLAMI OCHILMOQDA...</span>' +
                        '<span class="ks-percent-text" id="ks-percent-val">0%</span>' +
                    '</div>' +
                    '<div class="ks-bar-track">' +
                        '<div class="ks-bar-fill" id="ks-bar-fill">' +
                            '<span class="ks-bar-photon"></span>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            root.appendChild(cur);

            // Three.js 3D Scene Controller
            var threeController = (function() {
                var animId = null;
                var isRunning = false;
                var canvasEl = cur.querySelector('#ks-3d-canvas');
                var percentEl = cur.querySelector('#ks-percent-val');
                var barFillEl = cur.querySelector('#ks-bar-fill');
                var statusEl = cur.querySelector('#ks-status-label');
                var phrases = [
                    'BILIMLAR OLAMI OCHILMOQDA...',
                    'SAHIFALAR VARAQLANMOQDA...',
                    'AUDIO VA KITOBLAR YUKLANMOQDA...',
                    'TAYYOR. XUSH KELIBSIZ!'
                ];
                var progress = 0;
                var progressInterval = null;

                if (typeof THREE === 'undefined') {
                    return {
                        start: function() {
                            progress = 0;
                            if (progressInterval) clearInterval(progressInterval);
                            progressInterval = setInterval(function() {
                                if (progress < 92) {
                                    progress += 3;
                                    if (percentEl) percentEl.textContent = Math.floor(progress) + '%';
                                    if (barFillEl) barFillEl.style.width = progress + '%';
                                }
                            }, 50);
                        },
                        finish: function() {
                            if (progressInterval) clearInterval(progressInterval);
                            if (percentEl) percentEl.textContent = '100%';
                            if (barFillEl) barFillEl.style.width = '100%';
                            if (statusEl) statusEl.textContent = phrases[3];
                        },
                        stop: function() { if (progressInterval) clearInterval(progressInterval); }
                    };
                }

                // 1. Scene, Camera, Renderer
                var scene = new THREE.Scene();
                var isMobile = window.innerWidth < 768;
                var camera = new THREE.PerspectiveCamera(42, window.innerWidth / window.innerHeight, 0.1, 1000);
                camera.position.set(0, 0.95, isMobile ? 7.6 : 6.7);

                var renderer = new THREE.WebGLRenderer({ canvas: canvasEl, alpha: true, antialias: true, powerPreference: 'high-performance' });
                renderer.setSize(window.innerWidth, window.innerHeight);
                renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

                // 2. Lights
                var ambientLight = new THREE.AmbientLight(0x0e243d, 2.2);
                scene.add(ambientLight);

                var pointLightCyan = new THREE.PointLight(0x00E5FF, 4.2, 14);
                pointLightCyan.position.set(3, 3, 3.5);
                scene.add(pointLightCyan);

                var pointLightGold = new THREE.PointLight(0xF59E0B, 3.8, 14);
                pointLightGold.position.set(-3, -1.5, 3);
                scene.add(pointLightGold);

                var topSpot = new THREE.DirectionalLight(0xffffff, 1.2);
                topSpot.position.set(0, 5, 4);
                scene.add(topSpot);

                // 3. Procedural Page Texture
                function makePageTexture(isLeft) {
                    var tc = document.createElement('canvas');
                    tc.width = 512; tc.height = 700;
                    var ctx = tc.getContext('2d');
                    var grad = ctx.createLinearGradient(0, 0, 512, 700);
                    grad.addColorStop(0, '#FFFFFF');
                    grad.addColorStop(0.5, '#F8FAFC');
                    grad.addColorStop(1, '#F1F5F9');
                    ctx.fillStyle = grad;
                    ctx.fillRect(0, 0, 512, 700);

                    ctx.strokeStyle = 'rgba(217, 119, 6, 0.35)';
                    ctx.lineWidth = 6;
                    ctx.strokeRect(20, 20, 472, 660);

                    ctx.strokeStyle = 'rgba(0, 168, 181, 0.25)';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(30, 30, 452, 640);

                    ctx.fillStyle = 'rgba(0, 168, 181, 0.7)';
                    ctx.fillRect(isLeft ? 60 : 70, 70, 140, 14);

                    ctx.fillStyle = 'rgba(30, 41, 59, 0.28)';
                    for (var y = 130; y < 600; y += 24) {
                        var w = (y % 48 === 0) ? 280 : (320 + Math.sin(y) * 40);
                        ctx.fillRect(isLeft ? 60 : 70, y, Math.min(w, 380), 7);
                    }

                    ctx.fillStyle = 'rgba(0, 229, 255, 0.9)';
                    ctx.font = 'bold 20px monospace';
                    ctx.fillText(isLeft ? '• 28' : '29 •', isLeft ? 60 : 420, 640);

                    var tex = new THREE.CanvasTexture(tc);
                    tex.needsUpdate = true;
                    return tex;
                }

                var leftPageTex = makePageTexture(true);
                var rightPageTex = makePageTexture(false);

                // 4. 3D Procedural Book Group
                var bookGroup = new THREE.Group();
                bookGroup.position.set(0, 1.05, 0);
                bookGroup.scale.set(0.82, 0.82, 0.82);
                scene.add(bookGroup);

                var bWidth = 1.6, bHeight = 2.2, bThick = 0.2;

                var coverMat = new THREE.MeshStandardMaterial({
                    color: 0x071526, roughness: 0.2, metalness: 0.88,
                    emissive: 0x00243b, emissiveIntensity: 0.45
                });
                var goldMat = new THREE.MeshStandardMaterial({
                    color: 0xFFD700, roughness: 0.15, metalness: 0.95,
                    emissive: 0xF59E0B, emissiveIntensity: 0.5
                });

                var coverGeom = new THREE.BoxGeometry(bWidth, bHeight, 0.04);
                var leftCover = new THREE.Mesh(coverGeom, coverMat);
                leftCover.position.set(-bWidth / 2, 0, 0);
                leftCover.rotation.y = Math.PI * 0.14;
                bookGroup.add(leftCover);

                var rightCover = new THREE.Mesh(coverGeom, coverMat);
                rightCover.position.set(bWidth / 2, 0, 0);
                rightCover.rotation.y = -Math.PI * 0.14;
                bookGroup.add(rightCover);

                var trimGeom = new THREE.BoxGeometry(bWidth * 0.96, bHeight * 0.96, 0.045);
                var leftTrim = new THREE.Mesh(trimGeom, goldMat);
                leftTrim.position.set(-bWidth / 2, 0, -0.005);
                leftTrim.rotation.y = Math.PI * 0.14;
                bookGroup.add(leftTrim);

                var spineGeom = new THREE.CylinderGeometry(0.07, 0.07, bHeight, 24);
                var spineMesh = new THREE.Mesh(spineGeom, goldMat);
                spineMesh.position.set(0, 0, -0.09);
                bookGroup.add(spineMesh);

                var leftBlockMat = new THREE.MeshStandardMaterial({
                    map: leftPageTex, roughness: 0.45, metalness: 0.1,
                    emissive: 0x00E5FF, emissiveIntensity: 0.08
                });
                var rightBlockMat = new THREE.MeshStandardMaterial({
                    map: rightPageTex, roughness: 0.45, metalness: 0.1,
                    emissive: 0xF59E0B, emissiveIntensity: 0.08
                });

                var blockGeom = new THREE.BoxGeometry(bWidth * 0.92, bHeight * 0.94, bThick * 0.85);
                var leftBlock = new THREE.Mesh(blockGeom, leftBlockMat);
                leftBlock.position.set(-bWidth * 0.48, 0, 0.08);
                leftBlock.rotation.y = Math.PI * 0.11;
                bookGroup.add(leftBlock);

                var rightBlock = new THREE.Mesh(blockGeom, rightBlockMat);
                rightBlock.position.set(bWidth * 0.48, 0, 0.08);
                rightBlock.rotation.y = -Math.PI * 0.11;
                bookGroup.add(rightBlock);

                // 5. Flipping Pages
                var flippingPages = [];
                var pageCount = 4;
                var pageGeom = new THREE.PlaneGeometry(bWidth * 0.9, bHeight * 0.92, 16, 2);
                pageGeom.translate(bWidth * 0.45, 0, 0);

                for (var i = 0; i < pageCount; i++) {
                    var isAlt = i % 2 === 0;
                    var pMat = new THREE.MeshStandardMaterial({
                        map: isAlt ? rightPageTex : leftPageTex,
                        side: THREE.DoubleSide, transparent: true, opacity: 0.94,
                        roughness: 0.35, metalness: 0.15,
                        emissive: isAlt ? 0x00E5FF : 0xF59E0B, emissiveIntensity: 0.14
                    });
                    var pageMesh = new THREE.Mesh(pageGeom, pMat);
                    pageMesh.position.set(0, 0, 0.06);
                    bookGroup.add(pageMesh);
                    flippingPages.push({ mesh: pageMesh, offset: (i / pageCount) * Math.PI, speed: 1.55 });
                }

                // 6. Golden Ribbon
                var ribbonCurve = new THREE.CatmullRomCurve3([
                    new THREE.Vector3(0, bHeight * 0.46, 0.12),
                    new THREE.Vector3(0.18, -0.1, 0.28),
                    new THREE.Vector3(0.08, -bHeight * 0.65, 0.42),
                    new THREE.Vector3(0.25, -bHeight * 0.8, 0.5)
                ]);
                var ribbonMesh = new THREE.Mesh(new THREE.TubeGeometry(ribbonCurve, 32, 0.025, 8, false), goldMat);
                bookGroup.add(ribbonMesh);

                // 7. Holographic Gyroscope Rings
                var ring1 = new THREE.Mesh(
                    new THREE.TorusGeometry(1.65, 0.012, 16, 120),
                    new THREE.MeshBasicMaterial({ color: 0x00E5FF, transparent: true, opacity: 0.85 })
                );
                ring1.rotation.x = Math.PI * 0.38;
                bookGroup.add(ring1);

                var ring2 = new THREE.Mesh(
                    new THREE.TorusGeometry(1.82, 0.009, 16, 120),
                    new THREE.MeshBasicMaterial({ color: 0xF59E0B, transparent: true, opacity: 0.75 })
                );
                ring2.rotation.y = Math.PI * 0.3;
                bookGroup.add(ring2);

                // 8. 1200 Constellation Stars
                var pCount = 1200;
                var pGeom = new THREE.BufferGeometry();
                var pPositions = new Float32Array(pCount * 3);
                var pColors = new Float32Array(pCount * 3);
                var cyanCol = new THREE.Color(0x00E5FF), goldCol = new THREE.Color(0xF59E0B), whiteCol = new THREE.Color(0xFFFFFF);

                for (var j = 0; j < pCount; j++) {
                    var rad = 1.1 + Math.random() * 3.4;
                    var theta = Math.random() * Math.PI * 2;
                    pPositions[j * 3] = Math.cos(theta) * rad;
                    pPositions[j * 3 + 1] = (Math.random() - 0.4) * 4.6;
                    pPositions[j * 3 + 2] = Math.sin(theta) * rad;
                    var roll = Math.random();
                    var c = roll > 0.55 ? cyanCol : (roll > 0.2 ? goldCol : whiteCol);
                    pColors[j * 3] = c.r; pColors[j * 3 + 1] = c.g; pColors[j * 3 + 2] = c.b;
                }
                pGeom.setAttribute('position', new THREE.BufferAttribute(pPositions, 3));
                pGeom.setAttribute('color', new THREE.BufferAttribute(pColors, 3));
                var particles = new THREE.Points(pGeom, new THREE.PointsMaterial({
                    size: 0.04, vertexColors: true, transparent: true, opacity: 0.9, blending: THREE.AdditiveBlending
                }));
                scene.add(particles);

                // Mouse / Touch Follow
                var mouseX = 0, mouseY = 0, targetX = 0, targetY = 0;
                function onPointerMove(e) {
                    var cx = e.touches ? e.touches[0].clientX : e.clientX;
                    var cy = e.touches ? e.touches[0].clientY : e.clientY;
                    mouseX = (cx / window.innerWidth - 0.5) * 2;
                    mouseY = (cy / window.innerHeight - 0.5) * 2;
                }
                window.addEventListener('mousemove', onPointerMove, { passive: true });
                window.addEventListener('touchmove', onPointerMove, { passive: true });

                window.addEventListener('resize', function() {
                    if (!isRunning) return;
                    isMobile = window.innerWidth < 768;
                    camera.aspect = window.innerWidth / window.innerHeight;
                    camera.position.z = isMobile ? 7.6 : 6.7;
                    camera.updateProjectionMatrix();
                    renderer.setSize(window.innerWidth, window.innerHeight);
                }, { passive: true });

                var clock = new THREE.Clock();

                function renderLoop() {
                    if (!isRunning) return;
                    animId = requestAnimationFrame(renderLoop);
                    var elapsed = clock.getElapsedTime();

                    // Smooth progress
                    if (progress < 94) {
                        progress += 0.85;
                        var rounded = Math.floor(progress);
                        if (percentEl) percentEl.textContent = rounded + '%';
                        if (barFillEl) barFillEl.style.width = progress + '%';
                        if (statusEl) {
                            if (progress < 30) statusEl.textContent = phrases[0];
                            else if (progress < 65) statusEl.textContent = phrases[1];
                            else statusEl.textContent = phrases[2];
                        }
                    }

                    targetX += (mouseX - targetX) * 0.06;
                    targetY += (mouseY - targetY) * 0.06;

                    bookGroup.rotation.y = Math.sin(elapsed * 0.55) * 0.32 + targetX * 0.42;
                    bookGroup.rotation.x = Math.cos(elapsed * 0.45) * 0.1 - targetY * 0.28 + 0.12;
                    bookGroup.position.y = 1.05 + Math.sin(elapsed * 1.4) * 0.09;

                    flippingPages.forEach(function(p) {
                        var cycle = (elapsed * p.speed + p.offset) % Math.PI;
                        p.mesh.rotation.y = -Math.PI * 0.14 + (Math.sin(cycle) * Math.PI * 0.74);
                        p.mesh.position.z = 0.06 + Math.sin(cycle) * 0.15;
                    });

                    ring1.rotation.z += 0.016;
                    ring1.rotation.y += 0.009;
                    ring2.rotation.z -= 0.013;
                    ring2.rotation.x += 0.011;

                    particles.rotation.y = elapsed * 0.06;
                    particles.rotation.x = Math.sin(elapsed * 0.035) * 0.12;

                    renderer.render(scene, camera);
                }

                return {
                    start: function() {
                        progress = 0;
                        if (!isRunning) {
                            isRunning = true;
                            clock.start();
                            renderLoop();
                        }
                    },
                    finish: function() {
                        progress = 100;
                        if (percentEl) percentEl.textContent = '100%';
                        if (barFillEl) barFillEl.style.width = '100%';
                        if (statusEl) statusEl.textContent = phrases[3];
                    },
                    stop: function() {
                        isRunning = false;
                        if (animId) cancelAnimationFrame(animId);
                    }
                };
            })();

            window.ksCurtain = {
                show: function () {
                    cur.classList.add('is-on');
                    threeController.start();
                    try { sessionStorage.setItem('ksNav', '1'); } catch (e) {}
                },
                hide: function () {
                    threeController.finish();
                    setTimeout(function() {
                        cur.classList.remove('is-on');
                        setTimeout(function() { threeController.stop(); }, 400);
                    }, 120);
                    try { sessionStorage.removeItem('ksNav'); } catch (e) {}
                }
            };
            try {
                if (sessionStorage.getItem('ksNav')) {
                    sessionStorage.removeItem('ksNav');
                    cur.classList.add('is-on', 'ks-instant');
                    threeController.start();
                    setTimeout(function () {
                        cur.classList.remove('ks-instant');
                        void cur.offsetWidth;
                        window.ksCurtain.hide();
                    }, 480);
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

    /* ── 3D WebGL Three.js Loader (Davra_books Holographic 3D Book & Quantum Vortex) ── */
    .ks-curtain {
        position: fixed; inset: 0; z-index: 99990;
        display: flex; flex-direction: column; align-items: center; justify-content: flex-end;
        background: radial-gradient(ellipse at 50% 40%, #08172c 0%, #040d1a 50%, #010409 100%);
        overflow: hidden; user-select: none; padding-bottom: 7vh;
        opacity: 0; visibility: hidden; pointer-events: none;
        transition: opacity .35s ease, visibility 0s linear .35s;
    }
    .ks-curtain.is-on { opacity: 1; visibility: visible; pointer-events: auto; transition: opacity .25s ease; }
    .ks-curtain.ks-instant { transition: none !important; }

    .ks-cur-3d-canvas {
        position: absolute; inset: 0; width: 100%; height: 100%;
        pointer-events: none; z-index: 1;
    }

    /* Ambient Cosmic Glow Orbs */
    .ks-glow-orb { position: absolute; border-radius: 50%; filter: blur(110px); pointer-events: none; z-index: 0; opacity: 0.65; }
    .ks-glow-1 {
        width: 45vw; height: 45vw; left: 50%; top: 40%; transform: translate(-50%, -50%);
        background: radial-gradient(circle, rgba(0, 229, 255, 0.2) 0%, rgba(0, 168, 181, 0.08) 45%, transparent 70%);
        animation: ksPulseCenter 4s ease-in-out infinite alternate;
    }
    .ks-glow-2 {
        width: 32vw; height: 32vw; left: 15%; top: 15%;
        background: radial-gradient(circle, rgba(245, 158, 11, 0.16) 0%, transparent 65%);
        animation: ksFloatGlow 7s ease-in-out infinite alternate;
    }
    .ks-glow-3 {
        width: 36vw; height: 36vw; right: 12%; bottom: 20%;
        background: radial-gradient(circle, rgba(14, 165, 233, 0.16) 0%, transparent 70%);
        animation: ksFloatGlow 9s ease-in-out infinite alternate-reverse;
    }
    @keyframes ksPulseCenter {
        0% { transform: translate(-50%, -50%) scale(0.92); opacity: 0.45; }
        100% { transform: translate(-50%, -50%) scale(1.12); opacity: 0.75; }
    }
    @keyframes ksFloatGlow {
        0% { transform: translate(0, 0); }
        100% { transform: translate(25px, -35px); }
    }

    /* HUD UI Overlay */
    .ks-curtain-ui {
        position: relative; z-index: 10;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        text-align: center; pointer-events: auto;
    }

    /* Floating Logo Pill */
    .ks-logo-pill {
        display: inline-flex; align-items: center; gap: 10px;
        padding: 5px 16px 5px 6px; margin-bottom: 14px;
        background: rgba(15, 23, 42, 0.75);
        border: 1px solid rgba(0, 229, 255, 0.35);
        border-radius: 9999px;
        box-shadow: 0 10px 30px -4px rgba(0, 229, 255, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(20px);
        animation: ksPillGlow 3s ease-in-out infinite alternate;
    }
    @keyframes ksPillGlow {
        0% { border-color: rgba(0, 229, 255, 0.35); box-shadow: 0 8px 25px -4px rgba(0, 229, 255, 0.2); }
        100% { border-color: rgba(245, 158, 11, 0.5); box-shadow: 0 12px 35px -4px rgba(245, 158, 11, 0.3); }
    }
    .ks-logo-pill-img {
        width: 26px; height: 26px; border-radius: 50%;
        object-fit: cover; border: 1px solid rgba(255, 255, 255, 0.8);
    }
    .ks-logo-pill-txt {
        font-family: 'DM Mono', monospace; font-size: 11px; font-weight: 600;
        letter-spacing: 0.12em; text-transform: uppercase;
        color: #E2E8F0;
    }

    /* Brand Title */
    .ks-brand-title {
        font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 28px; font-weight: 900;
        letter-spacing: -0.025em;
        color: #FFFFFF;
        display: flex; align-items: center; gap: 6px;
        line-height: 1;
        text-shadow: 0 0 35px rgba(0, 229, 255, 0.6), 0 4px 14px rgba(0, 0, 0, 0.9);
    }
    .ks-brand-title .ks-accent {
        background: linear-gradient(135deg, #00E5FF 0%, #38BDF8 50%, #F59E0B 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Status & Percentage Badge */
    .ks-status-badge {
        margin-top: 12px;
        display: inline-flex; align-items: center; gap: 10px;
        padding: 5px 14px;
        background: rgba(11, 22, 38, 0.7);
        border: 1px solid rgba(0, 229, 255, 0.25);
        border-radius: 9999px;
        backdrop-filter: blur(16px);
        box-shadow: 0 8px 24px -4px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.08);
    }
    .ks-status-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: #00E5FF;
        box-shadow: 0 0 12px #00E5FF;
        animation: ksBlink 1.2s ease-in-out infinite alternate;
    }
    @keyframes ksBlink {
        0% { opacity: 0.3; transform: scale(0.8); }
        100% { opacity: 1; transform: scale(1.3); }
    }
    .ks-status-text {
        font-family: 'DM Mono', monospace;
        font-size: 11px; font-weight: 600;
        letter-spacing: 0.16em; text-transform: uppercase;
        color: #94A3B8;
    }
    .ks-percent-text {
        font-family: 'DM Mono', monospace;
        font-size: 12px; font-weight: 700;
        color: #F59E0B;
        min-width: 38px; text-align: right;
    }

    /* Laser Progress Bar */
    .ks-bar-track {
        position: relative; width: 260px; height: 3px; margin-top: 16px;
        background: rgba(15, 23, 42, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 9999px; overflow: hidden;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.8), inset 0 1px 2px rgba(0,0,0,0.5);
    }
    .ks-bar-fill {
        position: absolute; left: 0; top: 0; bottom: 0; width: 0%;
        background: linear-gradient(90deg, #00A8B5 0%, #00E5FF 65%, #F59E0B 100%);
        border-radius: 9999px;
        box-shadow: 0 0 16px rgba(0, 229, 255, 0.95), 0 0 30px rgba(245, 158, 11, 0.7);
        transition: width 0.16s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .ks-bar-photon {
        position: absolute; right: -2px; top: -2px; bottom: -2px; width: 8px;
        background: #FFFFFF; border-radius: 50%;
        box-shadow: 0 0 12px 2px #FFFFFF, 0 0 22px 5px #00E5FF;
    }

    @media (max-width: 768px) {
        .ks-brand-title { font-size: 24px; }
        .ks-bar-track { width: 220px; }
        .ks-curtain { padding-bottom: 9vh; }
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
