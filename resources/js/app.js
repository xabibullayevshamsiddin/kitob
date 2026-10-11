import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Load GSAP only on the landing page; its scroll scene is not needed elsewhere.
if (document.getElementById('book-reveal-section')) {
    Promise.all([import('gsap'), import('gsap/ScrollTrigger')])
        .then(([gsapModule, scrollTriggerModule]) => {
            const gsap = gsapModule.gsap || gsapModule.default;
            const ScrollTrigger = scrollTriggerModule.ScrollTrigger || scrollTriggerModule.default;

            gsap.registerPlugin(ScrollTrigger);
            window.gsap = gsap;
            window.ScrollTrigger = ScrollTrigger;
            window.dispatchEvent(new CustomEvent('kitobxon:gsap-ready'));
        })
        .catch((error) => console.error('Kitobxon home animation could not load:', error));
}
