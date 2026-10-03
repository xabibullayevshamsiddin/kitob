<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark" x-data="{ mobileMenu: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kitobxon platformasi maxfiylik siyosati: shaxsiy ma'lumotlar xavfsizligi, mutolaa tahlili, ovozli xabarlar va ma'lumotlarni himoya qilish standartlari.">
    <title>{{ __('site.nav.privacy') }} — Kitobxon</title>

    <!-- Tailwind CSS Play CDN -->
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
                        ink: {
                            950: '#07090e',
                            900: '#0b0f17',
                            800: '#111726',
                            700: '#1a2236',
                        },
                        amber: {
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                        },
                    },
                    boxShadow: {
                        'card-depth': '0 25px 50px -12px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.08)',
                        'glow-amber': '0 0 35px -5px rgba(245, 158, 11, 0.3)',
                    },
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- GSAP for Smooth Motion -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .noise-bg {
            background-image: radial-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 0);
            background-size: 24px 24px;
        }
        .spotlight-card {
            position: relative;
            overflow: hidden;
        }
        .spotlight-card::before {
            content: '';
            position: absolute;
            top: var(--mouse-y, -100px);
            left: var(--mouse-x, -100px);
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.12) 0%, transparent 70%);
            transform: translate(-50%, -50%);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .spotlight-card:hover::before {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-ink-950 text-slate-200 font-sans antialiased min-h-screen noise-bg selection:bg-amber-500 selection:text-ink-950">
    @include('components.page-loader')
    <div id="smooth-page-wrapper">
    <x-nav.main-header />

    <!-- ── Hero Section ── -->
    <section class="relative pt-24 pb-16 overflow-hidden border-b border-white/[0.06]">
        <!-- Ambient Glows -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-gradient-to-b from-amber-500/10 via-indigo-600/10 to-transparent rounded-full blur-[140px] pointer-events-none -z-10"></div>

        <div class="max-w-6xl mx-auto px-6">
            <div class="flex flex-col items-center text-center space-y-4">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 text-xs font-mono font-semibold tracking-wider uppercase">
                    <span>🛡️</span>
                    <span>MAXFIYLIK VA MA'LUMOTLAR HIMOYA STANDARTI</span>
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.1] max-w-4xl">
                    Maxfiylik Siyosati va <br class="hidden sm:inline">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-200 to-amber-400">Shaxsiy Ma'lumotlar Kafolati</span>
                </h1>

                <p class="text-sm sm:text-base text-slate-400 max-w-2xl leading-relaxed">
                    Kitobxon sizning shaxsiy daxlsizligingizni va xavfsizligingizni qadrlaydi. Mazkur hujjat ma'lumotlaringiz qanday to'planishi, nima maqsadda ishlatilishi va qanday qat'iy himoyalanishini ochiq-oydin tushuntiradi.
                </p>

                <!-- Security Trust Badges -->
                <div class="pt-4 flex flex-wrap items-center justify-center gap-3 text-xs">
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-ink-900/80 border border-white/10 text-slate-300">
                        <span class="text-emerald-400 font-bold">🔐 Shifrlash:</span>
                        <span class="font-mono text-slate-400">256-bit SSL / Bcrypt Hash</span>
                    </div>
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-ink-900/80 border border-white/10 text-slate-300">
                        <span class="text-amber-400 font-bold">🚫 Reklama siyosati:</span>
                        <span class="text-slate-400">Ma'lumotlar uchinchi shaxslarga sotilmaydi</span>
                    </div>
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-ink-900/80 border border-white/10 text-slate-300">
                        <span class="text-indigo-400 font-bold">📅 Yangilangan:</span>
                        <span class="font-mono text-slate-400">2026-yil</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Bento Summary Highlights ── -->
    <section class="py-12 border-b border-white/[0.06] bg-ink-900/40">
        <div class="max-w-6xl mx-auto px-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1 -->
                <div class="p-5 rounded-2xl bg-ink-900/90 border border-white/10 flex flex-col justify-between spotlight-card">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl mb-3">
                        🔒
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1">To'liq Shifrlash</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Barcha parollar va hisob ma'lumotlari qaytarib bo'lmaydigan xesh algoritmlari bilan himoyalangan.
                        </p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="p-5 rounded-2xl bg-ink-900/90 border border-white/10 flex flex-col justify-between spotlight-card">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-xl mb-3">
                        📚
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1">Faqat Ta'limiy Maqsad</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            O'qish progressi va daqiqalaringiz faqat sizga tavsiyalar berish va shaxsiy o'sishni hisoblash uchun yuritiladi.
                        </p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="p-5 rounded-2xl bg-ink-900/90 border border-white/10 flex flex-col justify-between spotlight-card">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-xl mb-3">
                        🎙️
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1">Ovoz & Chat Himoyasi</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Ovozli xabarlar va chat yozishmalari faqat hamjamiyat xavfsizligini ta'minlash maqsadida saqlanadi.
                        </p>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="p-5 rounded-2xl bg-ink-900/90 border border-white/10 flex flex-col justify-between spotlight-card">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center text-xl mb-3">
                        🗑️
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1">O'chirish Huquqi</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Istalgan vaqtda shaxsiy hisobingizni va barcha yozuvlarni butunlay o'chirishni talab qilish huquqiga egasiz.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Main Privacy Content ── -->
    <main class="py-16 max-w-5xl mx-auto px-6 space-y-10">

        <!-- 01. Qanday ma'lumotlar to'planadi? -->
        <article class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 flex items-center justify-center font-mono font-bold text-sm">
                    01
                </span>
                <h2 class="text-xl font-bold text-white">Biz Qanday Ma'lumotlarni Yig'amiz?</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    Platformadan to'laqonli foydalanishingiz uchun quyidagi toifadagi ma'lumotlar qayta ishlanadi:
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                    <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800">
                        <h4 class="font-bold text-white text-xs uppercase tracking-wider text-emerald-400 mb-1.5">👤 Ro'yxatdan o'tish ma'lumotlari</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Ismingiz, tanlagan foydalanuvchi nomingiz (username), elektron pochta manzilingiz va xavfsiz shifrlangan parolingiz.
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800">
                        <h4 class="font-bold text-white text-xs uppercase tracking-wider text-amber-400 mb-1.5">📖 Mutolaa & Faollik statistikasi</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            O'qilgan kitoblar, boblar, audio eshitish vaqti, test natijalari, ballar, ketma-ketlik (streak) va nishonlar.
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800">
                        <h4 class="font-bold text-white text-xs uppercase tracking-wider text-indigo-400 mb-1.5">💬 Jamoaviy Muloqot Yozuvlari</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Global chat va guruhlarda siz tomoningizdan jo'natilgan matnli va ovozli xabarlar, fikrlar va javoblar.
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800">
                        <h4 class="font-bold text-white text-xs uppercase tracking-wider text-purple-400 mb-1.5">⚙️ Texnik va Tizim Ma'lumotlari</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Tizimga kirish vaqti, IP-manzil, brauzer turi va operatsion tizim (xavfsizlik va nosozliklarni aniqlash uchun).
                        </p>
                    </div>
                </div>
            </div>
        </article>

        <!-- 02. Ma'lumotlardan qanday maqsadda foydalanamiz? -->
        <article class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 flex items-center justify-center font-mono font-bold text-sm">
                    02
                </span>
                <h2 class="text-xl font-bold text-white">Ma'lumotlardan Qanday Maqsadda Foydalaniladi?</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    Yig'ilgan ma'lumotlar faqatgina quyidagi aniq va qonuniy maqsadlar uchun ishlatiladi:
                </p>
                <ul class="space-y-2 list-disc list-inside text-xs sm:text-sm text-slate-300">
                    <li><strong>Shaxsiylashtirilgan o'quv tajribasi:</strong> Kitobxon qaysi bobda to'xtaganini eslab qolish, haftalik mutolaa rejasini yuritish va mos kitoblarni tavsiya qilish;</li>
                    <li><strong>Reyting va peshqadamlar hisobi:</strong> O'qilgan daqiqalar va test ballari asosida platforma bo'yicha adolatli reytingni shakllantirish;</li>
                    <li><strong>Hamjamiyat xavfsizligi:</strong> Qoidabuzarlik, haqorat va firibgarlik holatlarini aniqlash, foydalanuvchilar shikoyatlarini xolisona tekshirish;</li>
                    <li><strong>Xizmat ko'rsatish sifati:</strong> Platformaning ishlash tezligini oshirish, nosozliklarni tuzatish va yangi imkoniyatlarni joriy etish.</li>
                </ul>
            </div>
        </article>

        <!-- 03. Ovozli Xabarlar va Chat Maxfiyligi -->
        <article class="p-8 sm:p-10 rounded-3xl bg-gradient-to-r from-ink-900/90 via-slate-900/90 to-ink-900/90 border border-amber-500/30 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center font-mono font-bold text-sm">
                    03
                </span>
                <h2 class="text-xl font-bold text-white">Ovozli Xabarlar va Chat Yozishmalari Maxfiyligi</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    3.1. <strong>Ovozli xabarlar (Voice Notes):</strong> Siz yozib olgan audio xabarlar xavfsiz bulutli xotirada saqlanadi va faqatgina tegishli chat yoki guruh a'zolariga eshitish uchun taqdim etiladi.
                </p>
                <p>
                    3.2. <strong>Moderatsiya ko'rigi:</strong> Chat xabarlari maxfiy bo'lib, unga faqat bir holatda — boshqa foydalanuvchi tomonidan xabarga <em>«🚩 Shikoyat»</em> yuborilgandagina ma'muriyat tomonidan ko'rib chiqiladi.
                </p>
                <p>
                    3.3. Foydalanuvchi o'zi yuborgan xabarlarni o'chirish huquqiga ega bo'lib, o'chirilgan xabarlar umumiy lentadan darhol olib tashlanadi.
                </p>
            </div>
        </article>

        <!-- 04. Cookie fayllari va Mahalliy Xotira -->
        <article class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 flex items-center justify-center font-mono font-bold text-sm">
                    04
                </span>
                <h2 class="text-xl font-bold text-white">Cookie Fayllari va Mahalliy Xotira (Local Storage)</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    Kitobxon foydalanuvchi qulayligi uchun eng zarur bo'lgan texnik Cookie fayllaridan foydalanadi:
                </p>
                <div class="space-y-2 text-xs text-slate-300">
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800 flex items-start gap-2.5">
                        <span class="text-emerald-400 font-bold">🔑 Kirish sessiyasi:</span>
                        <span>Har safar sahifani yangilaganda qayta parol so'ramasligi uchun xavfsiz sessiya kaliti saqlanadi.</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800 flex items-start gap-2.5">
                        <span class="text-amber-400 font-bold">🌐 Til sozlamasi:</span>
                        <span>Siz tanlagan interfeys tili (O'zbek, Rus, Ingliz) eslab qolinadi.</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800 flex items-start gap-2.5">
                        <span class="text-indigo-400 font-bold">📖 Mutolaa joyi:</span>
                        <span>Kitobni qaysi sahifada o'qiyotganingiz brauzeringiz xotirasida saqlanib, qayta ochganda shu yerdan davom ettiriladi.</span>
                    </div>
                </div>
            </div>
        </article>

        <!-- 05. Ma'lumotlarni Uchinchi Shaxslarga Berilmasligi -->
        <article class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 flex items-center justify-center font-mono font-bold text-sm">
                    05
                </span>
                <h2 class="text-xl font-bold text-white">Ma'lumotlarning Uchinchi Shaxslarga Berilmasligi</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    5.1. <strong>Qat'iy taqiq:</strong> Kitobxon foydalanuvchilarning shaxsiy ma'lumotlarini, telefon raqamlarini yoki elektron pochta manzillarini hech qanday tijorat tashkilotlariga yoki reklama beruvchilarga sotmaydi, ijaraga bermaydi va alishmaydi.
                </p>
                <p>
                    5.2. <strong>Qonuniy istisno:</strong> Ma'lumotlar faqatgina qonunchilikda belgilangan tartibda, vakolatli huquqni muhofaza qiluvchi organlarning rasmiy qonuniy so'rovlari asosida taqdim etilishi mumkin.
                </p>
            </div>
        </article>

        <!-- 06. Foydalanuvchining Huquqlari -->
        <article class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 flex items-center justify-center font-mono font-bold text-sm">
                    06
                </span>
                <h2 class="text-xl font-bold text-white">Foydalanuvchining Qonuniy Huquqlari</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    Har bir ro'yxatdan o'tgan foydalanuvchi quyidagi huquqlarga ega:
                </p>
                <ul class="space-y-2 list-disc list-inside text-xs sm:text-sm text-slate-300">
                    <li><strong>Ma'lumotlarni ko'rish va o'zgartirish:</strong> Shaxsiy profilingiz, ismingiz, parolingiz va rasmingizni «Sozlamalar» orqali istalgan paytda tahrirlash;</li>
                    <li><strong>Maxfiylik darajasini boshqarish:</strong> Profilingiz boshqalarga qanchalik ko'rinishini sozlash;</li>
                    <li><strong>Akkauntni to'liq o'chirish:</strong> Profilingizni va platformadagi barcha shaxsiy ma'lumotlaringizni butunlay o'chirib yuborishni talab qilish;</li>
                    <li><strong>Savol va e'tiroz bildirish:</strong> Ma'lumotlar xavfsizligi bo'yicha ma'muriyatdan to'liq axborot talab qilish.</li>
                </ul>
            </div>
        </article>

        <!-- 07. Xavfsizlik Kafolatlari va Bog'lanish -->
        <article class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 flex items-center justify-center font-mono font-bold text-sm">
                    07
                </span>
                <h2 class="text-xl font-bold text-white">Siyosatning Yangilanishi va Aloqa</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    7.1. Kitobxon ushbu Maxfiylik Siyosatini texnologik va qonunchilik talablariga mos ravishda vaqti-vaqti bilan yangilab boradi. Barcha muhim o'zgarishlar ushbu sahifada e'lon qilinadi.
                </p>
                <p>
                    7.2. Maxfiylik va ma'lumotlar xavfsizligi bo'yicha har qanday savolingiz bo'lsa, bizning qo'llab-quvvatlash xizmatimizga bevosita murojaat qilishingiz mumkin.
                </p>
            </div>
        </article>

        <!-- CTA Contact Block -->
        <div class="p-8 sm:p-10 rounded-3xl bg-gradient-to-r from-ink-900 via-slate-900 to-ink-900 border border-emerald-500/25 text-center space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center text-2xl mx-auto">
                🛡️
            </div>
            <h3 class="text-xl font-black text-white">Ma'lumotlar xavfsizligi bo'yicha savollaringiz bormi?</h3>
            <p class="text-xs sm:text-sm text-slate-400 max-w-xl mx-auto leading-relaxed">
                Biz sizning daxlsizligingizni himoya qilishga to'liq tayyormiz. Ma'muriyat bilan bog'lanish uchun Aloqa sahifasidan foydalaning.
            </p>
            <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('contact') }}" class="px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-ink-950 font-bold text-xs uppercase tracking-wider shadow-lg shadow-emerald-500/20 transition-all active:scale-95">
                    Aloqa sahifasiga o'tish
                </a>
                <a href="{{ route('terms') }}" class="px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition-colors border border-slate-700">
                    Foydalanish shartlari
                </a>
            </div>
        </div>

    </main>

    <!-- ── Universal Footer ── -->
    <x-nav.main-footer />
    </div>

    <!-- Motion Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof gsap !== 'undefined') {
                gsap.fromTo('#site-header', { y: -25, opacity: 0 }, { y: 0, opacity: 1, duration: 0.7, ease: "power3.out" });
            }

            // Spotlight card mouse tracking
            document.querySelectorAll('.spotlight-card').forEach(card => {
                card.addEventListener('mousemove', e => {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    card.style.setProperty('--mouse-x', `${x}px`);
                    card.style.setProperty('--mouse-y', `${y}px`);
                });
            });
        });
    </script>
</body>
</html>
