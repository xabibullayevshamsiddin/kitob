import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

if (!window.Alpine) {
    Alpine.plugin(collapse);
    window.Alpine = Alpine;
    document.dispatchEvent(new CustomEvent('alpine:init'));
    Alpine.start();
}

// Load GSAP only on the landing page; its scroll scene is not needed elsewhere.
if (document.getElementById('book-reveal-section') || document.getElementById('scrolly-book-stage')) {
    Promise.all([import('gsap'), import('gsap/ScrollTrigger')])
        .then(([gsapModule, scrollTriggerModule]) => {
            const gsap = gsapModule.gsap || gsapModule.default;
            const ScrollTrigger = scrollTriggerModule.ScrollTrigger || scrollTriggerModule.default;

            gsap.registerPlugin(ScrollTrigger);
            gsap.config({ nullTargetWarn: false });
            window.gsap = gsap;
            window.ScrollTrigger = ScrollTrigger;
            window.dispatchEvent(new CustomEvent('kitobxon:gsap-ready'));

            if (typeof window.initHomePageAnimations === 'function') {
                window.initHomePageAnimations();
            }
        })
        .catch((error) => console.error('Kitobxon home animation could not load:', error));
}
