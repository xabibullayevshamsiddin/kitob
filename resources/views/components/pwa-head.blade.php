<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="theme-color" content="#07090E">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/apple-touch-icon.png') }}">
<script>
(() => {
    const enabled = @json((bool) config('pwa.enabled', true));
    const base = new URL(@json(url('/') . '/'), location.href);
    const syncThemeColor = () => {
        const meta = document.querySelector('meta[name="theme-color"]');
        if (meta) meta.content = document.documentElement.classList.contains('light') ? '#F3EFE7' : '#07090E';
    };
    syncThemeColor();
    window.addEventListener('theme-changed', syncThemeColor);
    const notify = (type, message) => {
        if (window.toast) window.toast({ type, message });
        else window.dispatchEvent(new CustomEvent('toast', { detail: { type, message } }));
    };
    if ('serviceWorker' in navigator) {
        if (enabled) {
            window.addEventListener('load', () => navigator.serviceWorker.register(new URL('sw.js', base), { scope: base.pathname }).catch(() => {}));
        } else {
            navigator.serviceWorker.getRegistrations().then((registrations) => Promise.all(registrations
                .filter((registration) => new URL(registration.scope).pathname === base.pathname)
                .map((registration) => registration.unregister()))).catch(() => {});
        }
    }
    if (!enabled) return;

    const ping = async () => {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), 3000);
        try {
            const response = await fetch(new URL('ping', base), { cache: 'no-store', signal: controller.signal, credentials: 'omit' });
            return response.status === 204;
        } catch (_) { return false; }
        finally { clearTimeout(timer); }
    };

    let indicator;
    let wasOffline = !navigator.onLine;
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
            indicator.innerHTML = '<span class="kitob-pwa-dot" aria-hidden="true"></span><span class="kitob-pwa-label"></span><a href="#">Offline sahifani ochish</a>';
            document.body.appendChild(indicator);
            indicator.querySelector('a').href = new URL('offline/index.html?reason=offline&from=' + encodeURIComponent(location.pathname + location.search), base).href;
        }
        indicator.classList.toggle('is-offline', offline);
        indicator.querySelector('.kitob-pwa-label').textContent = offline ? 'Internet aloqasi yo‘q' : 'Aloqa tiklandi';
        indicator.hidden = !offline;
        if (offline && !wasOffline) notify('warning', 'Internet aloqasi uzildi. Sahifani yopmay turing.');
        if (!offline && wasOffline) notify('success', 'Internet aloqasi tiklandi.');
        wasOffline = offline;
    };
    const check = async () => render(!(await ping()));
    window.addEventListener('online', check);
    window.addEventListener('offline', () => render(true));
    document.addEventListener('visibilitychange', () => { if (!document.hidden) check(); });
    document.addEventListener('livewire:load', () => {
        if (window.Livewire && window.Livewire.hook) window.Livewire.hook('message.failed', () => { render(true); check(); });
    });
    check();
    probeTimer = setInterval(() => { if (!document.hidden && navigator.onLine) check(); }, 30000);
    window.addEventListener('pagehide', () => clearInterval(probeTimer), { once: true });

    const style = document.createElement('style');
    style.textContent = '.kitob-pwa-status{position:fixed;z-index:99999;left:50%;bottom:16px;transform:translateX(-50%);display:flex;align-items:center;gap:10px;max-width:calc(100vw - 24px);padding:10px 13px;border:1px solid #f59e0b66;border-radius:8px;background:#0d111af2;color:#f0ede6;box-shadow:0 8px 28px #0007;font:13px system-ui,sans-serif}.kitob-pwa-status[hidden]{display:none}.kitob-pwa-dot{width:8px;height:8px;flex:none;border-radius:50%;background:#f59e0b}.kitob-pwa-status a{color:#fbbf24;white-space:nowrap;text-decoration:underline}@media(max-width:520px){.kitob-pwa-status{width:calc(100vw - 24px);justify-content:center;flex-wrap:wrap}}';
    document.head.appendChild(style);
})();
</script>
