<!DOCTYPE html>
<html lang="uz" class="scroll-smooth dark" x-data="{ darkMode: true, mobileMenu: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foydalanish Shartlari — Kitobxon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'ui-sans-serif', 'system-ui'],
                        mono: ['JetBrains Mono', 'ui-monospace'],
                    },
                    colors: {
                        ink: { 950: '#07090e', 900: '#0b0f17', 800: '#111726' },
                        amber: { 400: '#fbbf24', 500: '#f59e0b' },
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .noise-bg {
            background-image: radial-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 0);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="bg-ink-950 text-slate-200 font-sans antialiased min-h-screen noise-bg">
    <header id="site-header" class="sticky top-0 z-50 w-full backdrop-blur-xl bg-ink-950/80 border-b border-white/[0.07]">
        <div class="max-w-7xl mx-auto px-6 h-[72px] flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-ink-950 font-black text-xl shadow-lg shadow-amber-500/20 group-hover:scale-105 transition-transform">📖</div>
                <span class="font-bold text-white text-base group-hover:text-amber-400 transition-colors">Kitobxon</span>
            </a>
            <a href="{{ route('home') }}" class="text-xs font-mono text-amber-400 hover:underline">← Bosh sahifaga qaytish</a>
        </div>
    </header>

    <main class="legal-content py-16 max-w-4xl mx-auto px-6 space-y-8">
        <div>
            <span class="text-xs font-mono text-amber-400 uppercase tracking-widest">✦ HUQUQIY HUJJAT</span>
            <h1 class="text-3xl sm:text-5xl font-extrabold text-white mt-2 tracking-tight">Foydalanish Shartlari</h1>
            <p class="text-xs text-slate-400 mt-1 font-mono">Oxirgi yangilanish: 2026-yil</p>
        </div>

        <div class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 text-sm text-slate-300 space-y-6 leading-relaxed">
            <p>1. <strong>Umumiy qoidalar:</strong> Kitobxon platformasida ro'yxatdan o'tish orqali siz haftalik mutolaa qoidalariga va hamjamiyat etikasiga to'liq rioya qilishga rozilik bildirasiz.</p>
            <p>2. <strong>Mualliflik huquqi:</strong> Platformadagi barcha matnlar, audio yozuvlar va video tahlillar mualliflik huquqi bilan qat'iy himoyalangan. Ularni ruxsatsiz tijoriy maqsadda nusxalash taqiqlanadi.</p>
            <p>3. <strong>Hamjamiyat va chat etikasi:</strong> Global chat va guruhlarda boshqa kitobxonlarga nisbatan hurmatsizlik, haqorat yoki reklama tarqatish taqiqlanadi va hisob cheklanishiga sabab bo'ladi.</p>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            gsap.fromTo('#site-header', { y: -25, opacity: 0 }, { y: 0, opacity: 1, duration: 0.7, ease: "power3.out" });
            gsap.fromTo('.legal-content', { opacity: 0, y: 30, filter: 'blur(6px)' }, { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.8, ease: "power4.out" });
        });
    </script>
</body>
</html>
