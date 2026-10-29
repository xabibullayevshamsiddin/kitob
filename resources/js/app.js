import './bootstrap';
import './sound-manager';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

if (!window.Alpine) {
    Alpine.plugin(collapse);
    window.Alpine = Alpine;
    document.dispatchEvent(new CustomEvent('alpine:init'));
    Alpine.start();
}

// Automatically re-initialize Alpine on Livewire DOM updates
function hookLivewireAlpine() {
    if (window.Livewire && window.Livewire.hook) {
        window.Livewire.hook('message.processed', (message, component) => {
            if (window.Alpine && typeof window.Alpine.initTree === 'function' && component && component.el) {
                try {
                    window.Alpine.initTree(component.el);
                } catch (e) {}
            }
        });
    }
}
if (window.Livewire) {
    hookLivewireAlpine();
} else {
    document.addEventListener('livewire:load', hookLivewireAlpine);
}

// Load GSAP & StPageFlip only on the landing page; its scroll scene is not needed elsewhere.
if (document.getElementById('book-reveal-section') || document.getElementById('scrolly-book-stage')) {
    Promise.all([import('gsap'), import('gsap/ScrollTrigger'), import('page-flip')])
        .then(([gsapModule, scrollTriggerModule, pageFlipModule]) => {
            const gsap = gsapModule.gsap || gsapModule.default;
            const ScrollTrigger = scrollTriggerModule.ScrollTrigger || scrollTriggerModule.default;
            const PageFlip = pageFlipModule.PageFlip || (pageFlipModule.default && pageFlipModule.default.PageFlip) || pageFlipModule.default;

            gsap.registerPlugin(ScrollTrigger);
            gsap.config({ nullTargetWarn: false });
            window.gsap = gsap;
            window.ScrollTrigger = ScrollTrigger;
            window.PageFlip = PageFlip;
            window.dispatchEvent(new CustomEvent('kitobxon:gsap-ready'));

            if (typeof window.initHomePageAnimations === 'function') {
                window.initHomePageAnimations();
            }
        })
        .catch((error) => console.error('Kitobxon home animation could not load:', error));
}
