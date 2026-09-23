<!-- ── Kitobxon Pro Page Transition & Kinetic Loader ── -->
<div id="page-loader-root" class="pointer-events-none select-none">
    <!-- 1. Top Kinetic Laser Progress Bar -->
    <div id="top-loader-bar" 
         class="fixed top-0 left-0 h-[3px] w-0 z-[99999] pointer-events-none transition-all duration-300 ease-out"
         style="background: linear-gradient(90deg, #d97706 0%, #f59e0b 50%, #fbbf24 85%, #ffffff 100%); box-shadow: 0 0 15px rgba(245, 158, 11, 0.9), 0 0 30px rgba(245, 158, 11, 0.5);">
    </div>

    <!-- 2. Cinematic Obsidian Curtain Overlay -->
    <div id="page-transition-curtain" 
         class="fixed inset-0 z-[99990] bg-[#06080d]/85 backdrop-blur-xl flex flex-col items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300 ease-in-out">
        
        <!-- Ambient central golden glow -->
        <div class="absolute w-72 h-72 rounded-full bg-amber-500/10 blur-[80px] pointer-events-none"></div>

        <!-- Center Branded Animated Monogram -->
        <div class="relative z-10 flex flex-col items-center gap-4">
            
            <!-- Book Icon with Kinetic Pulsing Ring -->
            <div class="relative flex items-center justify-center">
                <div class="absolute w-16 h-16 rounded-2xl border border-amber-400/30 animate-ping opacity-30"></div>
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-ink-950 font-black text-2xl shadow-xl shadow-amber-500/30">
                    📖
                </div>
            </div>

            <!-- Kinetic Micro Spinner & Editorial Status -->
            <div class="flex items-center gap-3 mt-2">
                <div class="w-4 h-4 rounded-full border-2 border-amber-400/20 border-t-amber-400 animate-spin"></div>
                <span class="font-mono text-xs uppercase tracking-widest text-slate-300">
                    Yuklanmoqda...
                </span>
            </div>

        </div>
    </div>
</div>

<script>
    (function() {
        const topBar = document.getElementById('top-loader-bar');
        const curtain = document.getElementById('page-transition-curtain');
        let isNavigating = false;

        function startLoader() {
            if (isNavigating) return;
            isNavigating = true;

            if (topBar) {
                topBar.style.width = '25%';
                topBar.style.opacity = '1';
                setTimeout(() => {
                    if (isNavigating && topBar) topBar.style.width = '75%';
                }, 100);
            }

            if (curtain) {
                curtain.style.pointerEvents = 'auto';
                curtain.style.opacity = '1';
            }
        }

        function endLoader() {
            isNavigating = false;
            if (topBar) {
                topBar.style.width = '100%';
                setTimeout(() => {
                    topBar.style.opacity = '0';
                    setTimeout(() => {
                        topBar.style.width = '0%';
                    }, 300);
                }, 200);
            }

            if (curtain) {
                curtain.style.opacity = '0';
                curtain.style.pointerEvents = 'none';
            }
        }

        // Finish transition on page load
        window.addEventListener('DOMContentLoaded', () => {
            endLoader();
        });

        // Safety fallback for cached back/forward browser navigation
        window.addEventListener('pageshow', (event) => {
            endLoader();
        });

        // Global safety timeout to prevent getting stuck
        setTimeout(() => {
            endLoader();
        }, 3000);

        // Intercept standard internal clicks for smooth page transitions
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (!link) return;

            const href = link.getAttribute('href');
            if (!href) return;

            // Ignore external, target blank, hashes, javascript, mailto, tel, downloads
            if (
                link.target === '_blank' ||
                href.startsWith('#') ||
                href.startsWith('javascript:') ||
                href.startsWith('mailto:') ||
                href.startsWith('tel:') ||
                link.hasAttribute('download') ||
                e.ctrlKey || e.metaKey || e.shiftKey || e.altKey ||
                e.button !== 0
            ) {
                return;
            }

            // Verify if same domain URL
            try {
                const targetUrl = new URL(link.href, window.location.origin);
                if (targetUrl.origin !== window.location.origin) return;

                // Don't trigger if it's the exact same full URL with hash
                if (targetUrl.pathname === window.location.pathname && targetUrl.search === window.location.search && targetUrl.hash) {
                    return;
                }

                // If same page without search/hash change
                if (targetUrl.href === window.location.href) {
                    return;
                }

                e.preventDefault();
                startLoader();

                // Smooth delay to allow the cinematic animation to engage before browser unloads
                setTimeout(() => {
                    window.location.href = targetUrl.href;
                }, 220);
            } catch (err) {
                // Ignore parse errors and let default event handle
            }
        });
    })();
</script>
