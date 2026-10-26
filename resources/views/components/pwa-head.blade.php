<!-- Favicon & Brand Icons -->
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=2">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=2">
<link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon-48x48.png') }}?v=2">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/kitobxon-192.png') }}?v=2">
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/apple-touch-icon.png') }}?v=2">
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}?v=2">
<meta name="theme-color" content="#07090E">
<script>
(() => {
    const enabled = @json((bool) config('pwa.enabled', true));
    const syncThemeColor = () => {
        const meta = document.querySelector('meta[name="theme-color"]');
        if (meta) meta.content = document.documentElement.classList.contains('light') ? '#F5F8FB' : '#07090E';
    };
    syncThemeColor();
    window.addEventListener('theme-changed', syncThemeColor);
    const notify = (type, message) => {
        if (window.toast) window.toast({ type, message });
        else window.dispatchEvent(new CustomEvent('toast', { detail: { type, message } }));
    };
    const swUrl = @json(asset('sw.js'));
    const pingUrl = @json(route('pwa.ping'));
    const offlinePageUrl = @json(asset('offline/index.html'));
    if ('serviceWorker' in navigator) {
        if (enabled) {
            window.addEventListener('load', () => navigator.serviceWorker.register(swUrl).catch(() => {}));
        } else {
            navigator.serviceWorker.getRegistrations().then((registrations) => Promise.all(registrations
                .map((registration) => registration.unregister()))).catch(() => {});
        }
    }
    if (!enabled) return;

    const ping = async () => {
        if (!navigator.onLine) return false;
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), 3500);
        try {
            const response = await fetch(pingUrl, {
                method: 'GET',
                cache: 'no-store',
                signal: controller.signal,
                credentials: 'omit'
            });
            return response.status === 204 || response.ok;
        } catch (_) {
            return navigator.onLine;
        } finally {
            clearTimeout(timer);
        }
    };

    let indicator;
    let wasOffline = false;
    let hasInitialized = false;
    let probeTimer;

    const render = (offline) => {
        if (!indicator) {
            if (!document.body) {
                document.addEventListener('DOMContentLoaded', () => render(offline), { once: true });
                return;
            }
            indicator = document.createElement('aside');
            indicator.className = 'kitob-pwa-status';
            indicator.setAttribute('role', 'status');
            indicator.innerHTML = '<span class="kitob-pwa-dot" aria-hidden="true"></span><span class="kitob-pwa-label"></span><a href="#">' + @js(__('site.common.open_offline_page')) + '</a><button type="button" class="kitob-pwa-close" aria-label="' + @js(__('site.common.close')) + '">&times;</button>';
            document.body.appendChild(indicator);
            indicator.querySelector('a').href = offlinePageUrl + '?reason=offline&from=' + encodeURIComponent(location.pathname + location.search);
            indicator.querySelector('.kitob-pwa-close').addEventListener('click', () => { indicator.hidden = true; });
        }
        indicator.classList.toggle('is-offline', offline);
        indicator.querySelector('.kitob-pwa-label').textContent = offline ? @js(__('site.common.offline_no_internet')) : @js(__('site.common.offline_restored'));
        indicator.hidden = !offline;

        // Faqat oldin haqiqatdan offline bo'lib, keyin tiklangandagina bildirishnoma berish!
        // Boshlang'ich sahifa ochilishida (hasInitialized === false) aslo "Aloqa tiklandi" demaslik!
        if (hasInitialized) {
            if (offline && !wasOffline) {
                notify('warning', @js(__('site.common.offline_lost')));
            } else if (!offline && wasOffline) {
                notify('success', @js(__('site.common.offline_restored_msg')));
            }
        }
        wasOffline = offline;
        hasInitialized = true;
    };

    const check = async () => render(!(await ping()));
    window.addEventListener('online', check);
    window.addEventListener('offline', () => render(true));
    document.addEventListener('visibilitychange', () => { if (!document.hidden) check(); });
    document.addEventListener('livewire:load', () => {
        if (window.Livewire && window.Livewire.hook) {
            window.Livewire.hook('message.failed', () => {
                if (!navigator.onLine) {
                    render(true);
                }
            });
        }
    });
    check();
    probeTimer = setInterval(() => { if (!document.hidden && navigator.onLine) check(); }, 45000);
    window.addEventListener('pagehide', () => clearInterval(probeTimer), { once: true });

    const style = document.createElement('style');
    style.textContent = '.kitob-pwa-status{position:fixed;z-index:99999;left:50%;bottom:calc(76px + env(safe-area-inset-bottom,8px));transform:translateX(-50%);display:flex;align-items:center;gap:10px;max-width:calc(100vw - 32px);padding:8px 16px;border:1px solid #f59e0b66;border-radius:9999px;background:#0d111af5;backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);color:#f0ede6;box-shadow:0 10px 30px #0009,0 0 15px #f59e0b26;font:12px system-ui,-apple-system,sans-serif;animation:kitobSlideUp .35s cubic-bezier(0.16,1,0.3,1);pointer-events:auto}@media(min-width:1024px){.kitob-pwa-status{bottom:24px}}.kitob-pwa-status[hidden]{display:none}.kitob-pwa-dot{width:8px;height:8px;flex:none;border-radius:50%;background:#f59e0b;box-shadow:0 0 8px #f59e0b;animation:kitobDotPulse 2s infinite}@keyframes kitobDotPulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.4;transform:scale(1.3)}}@keyframes kitobSlideUp{from{opacity:0;transform:translate(-50%,12px)}to{opacity:1;transform:translate(-50%,0)}}.kitob-pwa-status a{color:#fbbf24;white-space:nowrap;text-decoration:underline;font-weight:500}.kitob-pwa-close{background:none;border:none;color:#8b9bad;cursor:pointer;font-size:16px;line-height:1;padding:0 0 0 4px;margin-left:2px}.kitob-pwa-close:hover{color:#f0ede6}@media(max-width:480px){.kitob-pwa-status{font-size:11.5px;padding:7px 12px;gap:8px}}';
    document.head.appendChild(style);
})();
</script>
