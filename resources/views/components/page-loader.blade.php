<!-- ── Kitobxon Seamless Page Transition (PJAX) ──
     Vizual qism (kitob varaqlayotgan loader) partials/motion.blade.php ichida: window.ksCurtain.show()/hide() -->
<div id="page-loader-root" class="pointer-events-none select-none"></div>

<script>
(function() {
    const topBar = null;   // eski chiziq o'rniga ks-progress va ksCurtain ishlatiladi
    const curtain = null;
    let isTransitioning = false;

    // Helper: Initialize all interactions & GSAP animations across any page
    function initAllPageInteractions() {
        if (typeof gsap === 'undefined') return;

        if (typeof ScrollTrigger !== 'undefined') {
            ScrollTrigger.refresh();
        }

        // Initialize Scrollytelling Book Reveal if present on page
        if (typeof window.initBookRevealSection === 'function') {
            window.initBookRevealSection();
        }

        // 1. Header entrance
        const header = document.getElementById('site-header');
        if (header) {
            gsap.fromTo(header,
                { y: -25, opacity: 0 },
                { y: 0, opacity: 1, duration: 0.7, ease: "power3.out" }
            );
        }

        // 2. Hero & Headings Stagger Reveal
        const heroItems = document.querySelectorAll('.hero-anim-item, .about-hero-item, .contact-header, .faq-header, .books-header, .error-anim-item');
        if (heroItems.length > 0) {
            gsap.fromTo(heroItems,
                { opacity: 0, y: 35, filter: 'blur(8px)', scale: 0.97 },
                {
                    opacity: 1,
                    y: 0,
                    filter: 'blur(0px)',
                    scale: 1,
                    duration: 0.85,
                    stagger: 0.1,
                    ease: "power4.out",
                    clearProps: "transform,scale,filter"
                }
            );
        }

        // 3. Bento Grid Cards (Home)
        const bentoCards = document.querySelectorAll('.bento-card');
        if (bentoCards.length > 0) {
            gsap.fromTo(bentoCards,
                { opacity: 0, y: 45, scale: 0.96 },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.8,
                    stagger: 0.12,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: '#features',
                        start: "top 80%",
                    },
                    clearProps: "transform,scale"
                }
            );
        }

        // 4. Step Cards (Home)
        const stepCards = document.querySelectorAll('.step-card');
        if (stepCards.length > 0) {
            gsap.fromTo(stepCards,
                { opacity: 0, y: 40, scale: 0.96 },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.75,
                    stagger: 0.14,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: '.step-card',
                        start: "top 85%",
                    },
                    clearProps: "transform,scale"
                }
            );
        }

        // 5. About Stats & Values Cards
        const statCards = document.querySelectorAll('.about-stat-card, .value-card, .team-card');
        if (statCards.length > 0) {
            gsap.fromTo(statCards,
                { opacity: 0, y: 35, scale: 0.96 },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.75,
                    stagger: 0.1,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: statCards[0],
                        start: "top 85%",
                    },
                    clearProps: "transform,scale"
                }
            );
        }

        // 6. Contact Form & Info Cards
        const contactForm = document.querySelector('.contact-form-col');
        const contactInfo = document.querySelectorAll('.contact-info-card');
        if (contactForm) {
            gsap.fromTo(contactForm,
                { opacity: 0, x: -35, scale: 0.98 },
                { opacity: 1, x: 0, scale: 1, duration: 0.8, ease: "power3.out", clearProps: "transform,scale" }
            );
        }
        if (contactInfo.length > 0) {
            gsap.fromTo(contactInfo,
                { opacity: 0, x: 35, scale: 0.98 },
                { opacity: 1, x: 0, scale: 1, duration: 0.8, stagger: 0.12, ease: "power3.out", clearProps: "transform,scale" }
            );
        }

        // 7. FAQ Cards
        const faqCards = document.querySelectorAll('.faq-card');
        if (faqCards.length > 0) {
            gsap.fromTo(faqCards,
                { opacity: 0, y: 30, scale: 0.97 },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.7,
                    stagger: 0.08,
                    ease: "power3.out",
                    clearProps: "transform,scale"
                }
            );
        }

        // 8. Book Cards (Catalog)
        const bookCards = document.querySelectorAll('.book-card');
        if (bookCards.length > 0) {
            gsap.fromTo(bookCards,
                { opacity: 0, y: 40, scale: 0.96 },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.75,
                    stagger: 0.08,
                    ease: "power3.out",
                    clearProps: "transform,scale"
                }
            );
        }

        // 9. Rolling Number Counters
        if (typeof ScrollTrigger !== 'undefined') {
            document.querySelectorAll('.counter-element').forEach(el => {
                const target = parseInt(el.getAttribute('data-target') || 0, 10);
                ScrollTrigger.create({
                    trigger: el,
                    start: "top 90%",
                    once: true,
                    onEnter: () => {
                        const obj = { count: 0 };
                        gsap.to(obj, {
                            count: target,
                            duration: 1.8,
                            ease: "power2.out",
                            onUpdate: () => {
                                el.textContent = Math.floor(obj.count).toLocaleString('en-US');
                            }
                        });
                    }
                });
            });
        }

        // 10. Dynamic Cursor Spotlight Glow
        document.querySelectorAll('.spotlight-card, .error-card').forEach(card => {
            card.addEventListener('mousemove', e => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                card.style.setProperty('--mouse-x', `${x}px`);
                card.style.setProperty('--mouse-y', `${y}px`);
            });
        });

        // 11. 3D Tilt Cards
        const tiltCards = document.querySelectorAll('#hero-tilt-card, #error-tilt-card');
        tiltCards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                const rotX = -(y / rect.height) * 14;
                const rotY = (x / rect.width) * 14;
                card.style.transform = `perspective(1000px) rotateX(${rotX}deg) rotateY(${rotY}deg) scale3d(1.02, 1.02, 1.02)`;
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
            });
        });

        // 12. Re-init Alpine tree if available
        if (window.Alpine) {
            const wrapper = document.getElementById('smooth-page-wrapper');
            if (wrapper) {
                window.Alpine.initTree(wrapper);
            }
        }
    }

    // Helper: Update active link states in header navigation
    function updateActiveNavLinks(targetUrl) {
        try {
            const urlObj = new URL(targetUrl, window.location.origin);
            const currentPath = urlObj.pathname;
            document.querySelectorAll('nav a').forEach(a => {
                const aPath = new URL(a.href, window.location.origin).pathname;
                if (aPath === currentPath) {
                    a.classList.add('text-amber-400', 'font-semibold');
                    a.classList.remove('text-slate-300');
                } else {
                    a.classList.remove('text-amber-400', 'font-semibold');
                    a.classList.add('text-slate-300');
                }
            });
        } catch(e) {}
    }

    // Core: Seamless Soft PJAX Transition
    async function seamlessNavigateTo(url, pushState = true) {
        if (isTransitioning) return;
        isTransitioning = true;

        const currentWrapper = document.getElementById('smooth-page-wrapper');

        // 1. Kitob varaqlovchi loader'ni ko'rsat
        if (window.ksCurtain) window.ksCurtain.show();

        try {
            const [response] = await Promise.all([
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }),
                new Promise(r => setTimeout(r, 650)) // loader animatsiyasi ko'rinib ulgurishi uchun
            ]);

            if (!response.ok) {
                window.location.href = url;
                return;
            }

            const html = await response.text();
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');

            const newWrapper = newDoc.getElementById('smooth-page-wrapper');
            if (!newWrapper || !currentWrapper) {
                // If target page doesn't have smooth-wrapper, navigate normally
                window.location.href = url;
                return;
            }

            // 3. Clean up old ScrollTriggers before swapping DOM
            if (typeof ScrollTrigger !== 'undefined') {
                ScrollTrigger.getAll().forEach(t => t.kill());
            }

            currentWrapper.innerHTML = newWrapper.innerHTML;

            // 4. Update title & URL
            document.title = newDoc.title;
            if (pushState) {
                window.history.pushState({ url }, newDoc.title, url);
            }
            window.scrollTo({ top: 0, left: 0, behavior: 'instant' });

            // 5. Update nav active links
            updateActiveNavLinks(url);

            // 6. Re-initialize interactive animations on new DOM
            initAllPageInteractions();

            // 7. Loader'ni yashir — yangi sahifa kartalari scroll-reveal bilan paydo bo'ladi
            if (window.ksCurtain) window.ksCurtain.hide();
            isTransitioning = false;

        } catch (err) {
            // Fallback on any network or parse failure
            window.location.href = url;
        }
    }

    // Intercept clicks on links for seamless page transitions
    // Guard: Only activate PJAX on pages that have #smooth-page-wrapper (public pages).
    // Dashboard, auth, and other layout pages must not intercept clicks.
    document.addEventListener('click', (e) => {
        // ── PJAX Guard ─────────────────────────────────────────────────────
        // If the CURRENT page doesn't have the smooth wrapper, skip interception.
        if (!document.getElementById('smooth-page-wrapper')) return;
        // ───────────────────────────────────────────────────────────────────

        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href) return;

        // Skip non-standard clicks
        if (
            link.target === '_blank' ||
            href.startsWith('#') ||
            href.startsWith('javascript:') ||
            href.startsWith('mailto:') ||
            href.startsWith('tel:') ||
            link.hasAttribute('download') ||
            link.hasAttribute('data-no-smooth') ||
            e.ctrlKey || e.metaKey || e.shiftKey || e.altKey ||
            e.button !== 0
        ) {
            return;
        }

        // Verify if same domain URL
        try {
            const targetUrl = new URL(link.href, window.location.origin);
            if (targetUrl.origin !== window.location.origin) return;

            // Don't trigger if it's the exact same page
            if (targetUrl.href === window.location.href) {
                return;
            }

            e.preventDefault();
            seamlessNavigateTo(targetUrl.href, true);
        } catch (err) {}
    });

    // Handle browser Back / Forward buttons seamlessly
    window.addEventListener('popstate', (e) => {
        // Only use PJAX if the current page supports it
        if (!document.getElementById('smooth-page-wrapper')) return;
        seamlessNavigateTo(window.location.href, false);
    });

    // Initial page load finish
    window.addEventListener('DOMContentLoaded', () => {
        if (curtain) {
            curtain.style.opacity = '0';
            curtain.style.pointerEvents = 'none';
        }
        if (topBar) {
            topBar.style.width = '100%';
            setTimeout(() => {
                topBar.style.opacity = '0';
                setTimeout(() => { topBar.style.width = '0%'; }, 300);
            }, 150);
        }
        initAllPageInteractions();
    });

    // Safety fallback
    window.addEventListener('pageshow', (event) => {
        if (curtain) {
            curtain.style.opacity = '0';
            curtain.style.pointerEvents = 'none';
        }
        if (topBar) {
            topBar.style.opacity = '0';
            topBar.style.width = '0%';
        }
        isTransitioning = false;
    });

})();
</script>
