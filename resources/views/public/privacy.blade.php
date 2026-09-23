<!DOCTYPE html>
<html lang="uz" class="scroll-smooth dark" x-data="{ darkMode: true, mobileMenu: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maxfiylik Siyosati — Kitobxon</title>
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
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-ink-950 text-slate-200 font-sans antialiased min-h-screen">
    <header class="sticky top-0 z-50 w-full backdrop-blur-xl bg-ink-950/80 border-b border-white/[0.07]">
        <div class="max-w-7xl mx-auto px-6 h-[70px] flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-ink-950 font-black text-xl">📖</div>
                <span class="font-bold text-white text-base">Kitobxon</span>
            </a>
            <a href="{{ route('home') }}" class="text-xs font-mono text-amber-400 hover:underline">← Bosh sahifaga qaytish</a>
        </div>
    </header>

    <main class="py-16 max-w-4xl mx-auto px-6 space-y-8">
        <div>
            <span class="text-xs font-mono text-amber-400 uppercase tracking-widest">✦ MAXFIYLIK VA XAVFSIZLIK</span>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white mt-2">Maxfiylik Siyosati</h1>
            <p class="text-xs text-slate-400 mt-1">Oxirgi yangilanish: 2026-yil</p>
        </div>

        <div class="prose prose-invert max-w-none text-sm text-slate-300 space-y-6 leading-relaxed">
            <p>1. <strong>Ma'lumotlar xavfsizligi:</strong> Sizning shaxsiy ma'lumotlaringiz (ism, email, mutolaa daqiqalari va statistikangiz) qat'iy shifrlangan holda saqlanadi va uchinchi shaxslarga berilmaydi.</p>
            <p>2. <strong>Mutolaa tahlili:</strong> O'qish progressi va streak ko'rsatkichlari faqat sizga shaxsiy statistika va tavsiyalar berish uchun ishlatiladi.</p>
            <p>3. <strong>Xavfsizlik:</strong> Parollar zamonaviy xesh algoritmlari yordamida himoyalangan.</p>
        </div>
    </main>
</body>
</html>
