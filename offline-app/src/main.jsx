import React from 'react';
import { createRoot } from 'react-dom/client';
import '@fontsource/spectral/400.css';
import '@fontsource/spectral/700.css';
import '@fontsource/dm-sans/400.css';
import '@fontsource/dm-sans/500.css';
import '@fontsource/dm-sans/700.css';
import '@fontsource/dm-mono/400.css';
import './style.css';

const quotes = [
  'Kitob — qalbning sokin suhbatdoshi.', 'Har bir sahifa yangi bir yo‘l ochadi.',
  'Mutolaa — fikrga qanot, qalbga osoyishtalik.', 'Yaxshi kitob vaqtni emas, dunyoqarashni o‘zgartiradi.',
  'Bilimning eng go‘zal yo‘li — sabr bilan o‘qish.', 'Kitob bilan o‘tgan lahza hech qachon yo‘qolmaydi.',
  'Sahifalar orasida o‘zingizni ham topasiz.', 'O‘qish — uzoq manzilga sokin sayohat.',
  'Har bir bob tafakkurga yangi eshikdir.', 'Yaxshi fikr yaxshi kitobdan boshlanadi.',
  'Kitobxonning yo‘li hech qachon berk ko‘cha emas.', 'Bir sahifa ham katta o‘zgarishga sabab bo‘lishi mumkin.',
  'Sokinlikda o‘qilgan so‘z qalbda uzoq yashaydi.', 'Kitob — avlodlar orasidagi ko‘prik.',
  'O‘qigan sari olam kengayadi.', 'Mutolaa — o‘z ustingizda ishlashning eng sokin shakli.',
  'So‘zlar tugaydi, ulardan qolgan ma’no qoladi.', 'Har bir kitob yangi nigoh hadya qiladi.',
  'Aql o‘qish bilan charxlanadi, qalb esa ma’no bilan.', 'Bugungi bir bob — ertangi teran fikr.'
];

const appBase = new URL('../', window.location.href);
const pingUrl = new URL('ping', appBase);
const safeReturnUrl = () => {
  const raw = new URLSearchParams(location.search).get('from') || '/';
  try {
    const candidate = new URL(raw, location.origin);
    if (candidate.origin !== location.origin) return new URL('./', appBase).href;
    return candidate.href;
  } catch { return new URL('./', appBase).href; }
};

function App() {
  const params = new URLSearchParams(location.search);
  const slow = params.get('reason') === 'slow';
  const [online, setOnline] = React.useState(navigator.onLine);
  const [checking, setChecking] = React.useState(false);
  const [quote] = React.useState(() => quotes[Math.floor(Math.random() * quotes.length)]);
  const [tilt, setTilt] = React.useState({ x: 0, y: 0 });
  const lowPower = (navigator.hardwareConcurrency || 4) < 4 || (navigator.deviceMemory && navigator.deviceMemory < 4);
  const [particles] = React.useState(() => Array.from({ length: lowPower ? 8 : Math.min(32, (navigator.hardwareConcurrency || 4) * 2) }, (_, i) => ({
    left: `${(i * 43 + 7) % 100}%`, delay: `${(i * 0.71) % 12}s`, duration: `${12 + (i % 9)}s`, size: `${2 + (i % 3)}px`,
  })));
  const retryTimer = React.useRef(null);
  const attempt = React.useRef(0);

  const ping = React.useCallback(async (manual = false) => {
    setChecking(true);
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 3000);
    try {
      const response = await fetch(pingUrl, { method: 'GET', cache: 'no-store', signal: controller.signal, credentials: 'omit' });
      if (!response.ok) throw new Error('ping failed');
      const wasOffline = !online;
      setOnline(true); attempt.current = 0;
      if (retryTimer.current) clearTimeout(retryTimer.current);
      if (wasOffline && window.toast) window.toast({ type: 'success', message: 'Internet aloqasi tiklandi.' });
      if (manual) window.location.replace(safeReturnUrl());
    } catch {
      setOnline(false);
      const delays = [3000, 6000, 12000, 30000];
      const wait = delays[Math.min(attempt.current, delays.length - 1)];
      attempt.current += 1;
      clearTimeout(retryTimer.current);
      retryTimer.current = setTimeout(() => ping(false), wait);
    } finally { clearTimeout(timeout); setChecking(false); }
  }, [online]);

  React.useEffect(() => {
    const onOnline = () => ping(false);
    const onOffline = () => setOnline(false);
    const onVisibility = () => { if (!document.hidden) ping(false); };
    window.addEventListener('online', onOnline); window.addEventListener('offline', onOffline);
    document.addEventListener('visibilitychange', onVisibility);
    const livewire = () => { setOnline(false); ping(false); };
    document.addEventListener('livewire:load', () => {});
    document.addEventListener('message.failed', livewire);
    const syncTheme = (event) => {
      try {
        const theme = event?.detail?.theme || localStorage.getItem('kitob_theme');
        document.documentElement.classList.toggle('light', theme === 'light');
        document.documentElement.classList.toggle('dark', theme !== 'light');
        const color = document.querySelector('meta[name="theme-color"]');
        if (color) color.content = theme === 'light' ? '#F3EFE7' : '#07090E';
      } catch (_) {}
    };
    const onVisibilityChange = () => document.documentElement.classList.toggle('is-hidden', document.hidden);
    window.addEventListener('theme-changed', syncTheme);
    window.addEventListener('storage', syncTheme);
    document.addEventListener('visibilitychange', onVisibilityChange);
    document.documentElement.classList.toggle('low-power', lowPower);
    onVisibilityChange();
    ping(false);
    return () => {
      window.removeEventListener('online', onOnline); window.removeEventListener('offline', onOffline);
      document.removeEventListener('visibilitychange', onVisibility); document.removeEventListener('message.failed', livewire);
      window.removeEventListener('theme-changed', syncTheme); window.removeEventListener('storage', syncTheme);
      document.removeEventListener('visibilitychange', onVisibilityChange);
      clearTimeout(retryTimer.current);
    };
  }, [ping]);

  const onPointer = (event) => {
    if (matchMedia('(prefers-reduced-motion: reduce)').matches || (navigator.hardwareConcurrency || 4) < 4) return;
    const box = event.currentTarget.getBoundingClientRect();
    setTilt({ x: ((event.clientX - box.left) / box.width - .5) * 12, y: ((event.clientY - box.top) / box.height - .5) * -9 });
  };

  return <main className="screen" onPointerMove={onPointer} onPointerLeave={() => setTilt({ x: 0, y: 0 })}>
    <div className="glow" aria-hidden="true"/><div className="particle-field" aria-hidden="true">{particles.map((p,i)=><i key={i} style={{left:p.left,animationDelay:p.delay,animationDuration:p.duration,width:p.size,height:p.size}}/>)}</div>
      <header className="topline"><a href={new URL('./', appBase)} className="brand"><span className="brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 5.5c3.2-1.2 6.2-.7 9 1.2v13c-2.8-1.9-5.8-2.4-9-1.2zM21 5.5c-3.2-1.2-6.2-.7-9 1.2v13c2.8-1.9 5.8-2.4 9-1.2z"/></svg></span><span>KITOBXON</span></a><span className={`connection ${online ? 'is-online' : ''}`}><i/>{online ? 'Aloqa mavjud' : slow ? 'Aloqa sekinlashdi' : 'Oflayn rejim'}</span></header>
    <section className="content" aria-labelledby="title">
      <div className="book-stage" style={{'--tilt-x':`${tilt.x}deg`,'--tilt-y':`${tilt.y}deg`}} aria-label="Kitob maketi">
        <div className="book-shadow"/><div className="book" aria-hidden="true"><div className="page page-back"/><div className="page page-middle"/><div className="page page-front"><div className="page-rule"/><div className="page-eyebrow">KITOBXON KUTUBXONASI</div><div className="page-title">Mutolaa<br/>sokinlikdan<br/>boshlanadi</div><div className="page-mark">✦</div><div className="page-foot">BIR SAHIFA • BIR OLAM</div></div><div className="cover"><div className="cover-stamp">KITOBXON<br/>2026</div><div className="cover-art"><span/><span/><span/></div><div className="cover-title">KITOB<br/>VA TAFAKKUR</div><div className="cover-rule"/><div className="cover-subtitle">mutolaa kundaligi</div></div><div className="spine"/></div>
      </div>
      <div className="copy"><p className="eyebrow">KITOBXON · OFLAYN HOLAT</p><h1 id="title">Kitobxon biroz <em>sukutda.</em></h1><p className="lead">{slow ? 'Aloqa kutilganidan sekinroq. Sahifani yuklashga yana urinib ko‘ring.' : 'Internet aloqasi uzildi. Tarmoq tiklangach, o‘qiyotgan sahifangizga qaytasiz.'}</p><blockquote>“{quote}”</blockquote><div className="actions"><button className="primary" onClick={()=>ping(true)} disabled={checking}>{checking ? <><span className="spinner"/>Tekshirilmoqda…</> : 'Qayta urinish'}</button><a className="secondary" href={safeReturnUrl()}>Oldingi sahifaga qaytish <span aria-hidden="true">↗</span></a></div><p className="hint">Javoblaringiz va o‘qish holatingiz saqlangan bo‘lishi mumkin. Aloqa tiklangach, sahifani yangilang.</p></div>
    </section>
    <footer className="footer"><span>SAHIFALAR SHOSHILMAYDI.</span><span>QALB HAM.</span></footer>
  </main>;
}

createRoot(document.getElementById('root')).render(<App/>);
