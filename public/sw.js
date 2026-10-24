/* Kitobxon PWA: network-first document loads; only generated app/static assets are cached. */
importScripts('./pwa-precache.js');

const CACHE_PREFIX = 'kitobxon-pwa-';
const CACHE_VERSION = self.KITOB_PWA_VERSION || 'dev';
const CACHE_NAME = `${CACHE_PREFIX}${CACHE_VERSION}`;
const APP_SCOPE = new URL('./', self.registration.scope);
const OFFLINE_URL = new URL('offline/index.html', APP_SCOPE);
const NAVIGATION_TIMEOUT_MS = 8000;

function isExcluded(request, url) {
    const path = url.pathname;
    if (request.method !== 'GET' || request.headers.has('range')) return true;
    if (/\/(?:livewire|broadcasting)(?:\/|$)/i.test(path)) return true;
    if (/\/live\/[^/]+\/(?:signal|signals)(?:\/|$)/i.test(path)) return true;
    if (/\/(?:admin|teacher)(?:\/|$)/i.test(path)) return true;
    if (/\/(?:tg-app|tg\/app|auth\/telegram|api\/telegram)(?:\/|$)/i.test(path)) return true;
    if (/\/ping\/?$/i.test(path)) return true;
    if (/\.(?:mp3|m4a|aac|wav|ogg|opus|mp4|webm|mov|m3u8|mpd)(?:$|\?)/i.test(path)) return true;
    if (request.destination === 'audio' || request.destination === 'video') return true;
    return false;
}

function withOfflineParams(reason, requestUrl) {
    const fallback = new URL(OFFLINE_URL);
    const current = new URL(requestUrl);
    fallback.searchParams.set('reason', reason);
    fallback.searchParams.set('from', current.pathname + current.search + current.hash);
    return fallback;
}

async function offlineResponse(reason, requestUrl) {
    const cache = await caches.open(CACHE_NAME);
    const page = await cache.match(OFFLINE_URL.href);
    if (!page) return Response.error();
    const target = withOfflineParams(reason, requestUrl);
    return Response.redirect(target.href, 302);
}

self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        const cache = await caches.open(CACHE_NAME);
        const assets = (self.KITOB_PWA_ASSETS || []).map((path) => new URL(path, APP_SCOPE).href);
        await cache.addAll(assets);
        await self.skipWaiting();
    })());
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const keys = await caches.keys();
        await Promise.all(keys.filter((key) => key.startsWith(CACHE_PREFIX) && key !== CACHE_NAME).map((key) => caches.delete(key)));
        await self.clients.claim();
    })());
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);
    if (url.origin !== self.location.origin || isExcluded(request, url)) return;

    // The cached shell must be served directly to avoid redirecting itself forever while offline.
    if (request.mode === 'navigate' && url.pathname === OFFLINE_URL.pathname) {
        event.respondWith((async () => {
            const cache = await caches.open(CACHE_NAME);
            return (await cache.match(OFFLINE_URL.href)) || fetch(request);
        })());
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith((async () => {
            let timer;
            try {
                const timeout = new Promise((_, reject) => {
                    timer = setTimeout(() => reject(new Error('navigation-timeout')), NAVIGATION_TIMEOUT_MS);
                });
                const response = await Promise.race([fetch(request), timeout]);
                clearTimeout(timer);
                return response;
            } catch (error) {
                clearTimeout(timer);
                return offlineResponse(error.message === 'navigation-timeout' ? 'slow' : 'offline', request.url);
            }
        })());
        return;
    }

    const isHashedMainAsset = /^\/build\/assets\/.+[-.][a-f0-9]{8,}\.(?:js|css|woff2?|ttf|otf|svg|png|webp|avif)$/i.test(url.pathname);
    const isOfflineAsset = url.pathname.startsWith(new URL('offline/assets/', APP_SCOPE).pathname);
    if (isHashedMainAsset || isOfflineAsset || (self.KITOB_PWA_ASSETS || []).some((path) => new URL(path, APP_SCOPE).pathname === url.pathname)) {
        event.respondWith((async () => {
            const cache = await caches.open(CACHE_NAME);
            const cached = await cache.match(request);
            if (cached) return cached;
            const response = await fetch(request);
            if (response.ok && response.type === 'basic') await cache.put(request, response.clone());
            return response;
        })());
    }
});
