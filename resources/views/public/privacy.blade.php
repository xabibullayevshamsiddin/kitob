<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark" x-data="{ mobileMenu: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kitobxon platformasi maxfiylik siyosati: shaxsiy ma'lumotlar xavfsizligi, mutolaa tahlili, ovozli xabarlar va ma'lumotlarni himoya qilish standartlari.">
    <title>{{ __('site.nav.privacy') }} — Kitobxon</title>

    @include('partials.design-system')


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
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex flex-col items-center text-center space-y-4">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-pill bg-gold/10 border border-gold/25 text-gold text-xs font-mono font-semibold tracking-wider uppercase">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>MAXFIYLIK VA MA'LUMOTLAR HIMOYA STANDARTI</span>
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-serif font-bold text-paper tracking-tight leading-[1.1] max-w-4xl">
                    Maxfiylik Siyosati va <br class="hidden sm:inline">
                    <span class="text-gold italic">Shaxsiy Ma'lumotlar Kafolati</span>
                </h1>

                <p class="text-sm sm:text-base text-mist max-w-2xl leading-relaxed">
                    Kitobxon sizning shaxsiy daxlsizligingizni va xavfsizligingizni qadrlaydi. Mazkur hujjat ma'lumotlaringiz qanday to'planishi, nima maqsadda ishlatilishi va qanday qat'iy himoyalanishini ochiq-oydin tushuntiradi.
                </p>

                <!-- Security Trust Badges -->
                <div class="pt-4 flex flex-wrap items-center justify-center gap-3 text-xs">
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-btn bg-ink-900 border border-ink-border text-paper-muted">
                        <svg class="w-4 h-4 text-gold shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span class="text-gold font-bold">Shifrlash:</span>
                        <span class="font-mono text-mist">256-bit SSL / Bcrypt Hash</span>
                    </div>
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-btn bg-ink-900 border border-ink-border text-paper-muted">
                        <svg class="w-4 h-4 text-gold shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        <span class="text-gold font-bold">Reklama siyosati:</span>
                        <span class="text-mist">Ma'lumotlar sotilmaydi</span>
                    </div>
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-btn bg-ink-900 border border-ink-border text-paper-muted">
                        <svg class="w-4 h-4 text-gold shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="text-gold font-bold">Yangilangan:</span>
                        <span class="font-mono text-mist">2026-yil</span>
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
                <div class="p-5 rounded-panel bg-ink-900 border border-ink-border flex flex-col justify-between spotlight-card">
                    <div class="w-10 h-10 rounded-btn bg-gold/10 border border-gold/20 text-gold flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-paper mb-1">To'liq Shifrlash</h3>
                        <p class="text-xs text-mist leading-relaxed">
                            Barcha parollar va hisob ma'lumotlari qaytarib bo'lmaydigan xesh algoritmlari bilan himoyalangan.
                        </p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="p-5 rounded-panel bg-ink-900 border border-ink-border flex flex-col justify-between spotlight-card">
                    <div class="w-10 h-10 rounded-btn bg-gold/10 border border-gold/20 text-gold flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-paper mb-1">Faqat Ta'limiy Maqsad</h3>
                        <p class="text-xs text-mist leading-relaxed">
                            O'qish progressi va daqiqalaringiz faqat sizga tavsiyalar berish va shaxsiy o'sishni hisoblash uchun yuritiladi.
                        </p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="p-5 rounded-panel bg-ink-900 border border-ink-border flex flex-col justify-between spotlight-card">
                    <div class="w-10 h-10 rounded-btn bg-gold/10 border border-gold/20 text-gold flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-paper mb-1">Ovoz & Chat Himoyasi</h3>
                        <p class="text-xs text-mist leading-relaxed">
                            Ovozli xabarlar va chat yozishmalari faqat hamjamiyat xavfsizligini ta'minlash maqsadida saqlanadi.
                        </p>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="p-5 rounded-panel bg-ink-900 border border-ink-border flex flex-col justify-between spotlight-card">
                    <div class="w-10 h-10 rounded-btn bg-gold/10 border border-gold/20 text-gold flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-paper mb-1">O'chirish Huquqi</h3>
                        <p class="text-xs text-mist leading-relaxed">
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
        <article class="p-8 sm:p-10 rounded-panel bg-ink-900 border border-ink-border space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-btn bg-gold/10 text-gold border border-gold/25 flex items-center justify-center font-mono font-bold text-sm">
                    01
                </span>
                <h2 class="text-xl font-serif font-bold text-paper">Biz Qanday Ma'lumotlarni Yig'amiz?</h2>
            </div>
            <div class="text-sm text-paper-muted leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    Platformadan to'laqonli foydalanishingiz uchun quyidagi toifadagi ma'lumotlar qayta ishlanadi:
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                    <div class="p-4 rounded-panel bg-ink-950/80 border border-ink-border">
                        <h4 class="font-bold text-xs uppercase tracking-wider text-gold mb-1.5 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Ro'yxatdan o'tish ma'lumotlari</span>
                        </h4>
                        <p class="text-xs text-mist leading-relaxed">
                            Ismingiz, tanlagan foydalanuvchi nomingiz (username), elektron pochta manzilingiz va xavfsiz shifrlangan parolingiz.
                        </p>
                    </div>
                    <div class="p-4 rounded-panel bg-ink-950/80 border border-ink-border">
                        <h4 class="font-bold text-xs uppercase tracking-wider text-gold mb-1.5 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <span>Mutolaa & Faollik statistikasi</span>
                        </h4>
                        <p class="text-xs text-mist leading-relaxed">
                            O'qilgan kitoblar, boblar, audio eshitish vaqti, test natijalari, ballar, ketma-ketlik (streak) va nishonlar.
                        </p>
                    </div>
                    <div class="p-4 rounded-panel bg-ink-950/80 border border-ink-border">
                        <h4 class="font-bold text-xs uppercase tracking-wider text-gold mb-1.5 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            <span>Jamoaviy Muloqot Yozuvlari</span>
                        </h4>
                        <p class="text-xs text-mist leading-relaxed">
                            Global chat va guruhlarda siz tomoningizdan jo'natilgan matnli va ovozli xabarlar, fikrlar va javoblar.
                        </p>
                    </div>
                    <div class="p-4 rounded-panel bg-ink-950/80 border border-ink-border">
                        <h4 class="font-bold text-xs uppercase tracking-wider text-gold mb-1.5 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>
                            <span>Texnik va Tizim Ma'lumotlari</span>
                        </h4>
                        <p class="text-xs text-mist leading-relaxed">
                            Tizimga kirish vaqti, IP-manzil, brauzer turi va operatsion tizim (xavfsizlik va nosozliklarni aniqlash uchun).
                        </p>
                    </div>
                </div>
            </div>
        </article>

        <!-- 02. Ma'lumotlardan qanday maqsadda foydalanamiz? -->
        <article class="p-8 sm:p-10 rounded-panel bg-ink-900 border border-ink-border space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-btn bg-gold/10 text-gold border border-gold/25 flex items-center justify-center font-mono font-bold text-sm">
                    02
                </span>
                <h2 class="text-xl font-serif font-bold text-paper">Ma'lumotlardan Qanday Maqsadda Foydalaniladi?</h2>
            </div>
            <div class="text-sm text-paper-muted leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    Yig'ilgan ma'lumotlar faqatgina quyidagi aniq va qonuniy maqsadlar uchun ishlatiladi:
                </p>
                <ul class="space-y-2 list-disc list-inside text-xs sm:text-sm text-paper-muted">
                    <li><strong class="text-paper">Shaxsiylashtirilgan o'quv tajribasi:</strong> Kitobxon qaysi bobda to'xtaganini eslab qolish, haftalik mutolaa rejasini yuritish va mos kitoblarni tavsiya qilish;</li>
                    <li><strong class="text-paper">Reyting va peshqadamlar hisobi:</strong> O'qilgan daqiqalar va test ballari asosida platforma bo'yicha adolatli reytingni shakllantirish;</li>
                    <li><strong class="text-paper">Hamjamiyat xavfsizligi:</strong> Qoidabuzarlik, haqorat va firibgarlik holatlarini aniqlash, foydalanuvchilar shikoyatlarini xolisona tekshirish;</li>
                    <li><strong class="text-paper">Xizmat ko'rsatish sifati:</strong> Platformaning ishlash tezligini oshirish, nosozliklarni tuzatish va yangi imkoniyatlarni joriy etish.</li>
                </ul>
            </div>
        </article>

        <!-- 03. Ovozli Xabarlar va Chat Maxfiyligi -->
        <article class="p-8 sm:p-10 rounded-panel bg-ink-900 border border-gold/30 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-btn bg-gold/10 text-gold border border-gold/30 flex items-center justify-center font-mono font-bold text-sm">
                    03
                </span>
                <h2 class="text-xl font-serif font-bold text-paper">Ovozli Xabarlar va Chat Yozishmalari Maxfiyligi</h2>
            </div>
            <div class="text-sm text-paper-muted leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    3.1. <strong class="text-paper">Ovozli xabarlar (Voice Notes):</strong> Siz yozib olgan audio xabarlar xavfsiz bulutli xotirada saqlanadi va faqatgina tegishli chat yoki guruh a'zolariga eshitish uchun taqdim etiladi.
                </p>
                <p>
                    3.2. <strong class="text-paper">Moderatsiya ko'rigi:</strong> Chat xabarlari maxfiy bo'lib, unga faqat bir holatda — boshqa foydalanuvchi tomonidan xabarga shikoyat yuborilgandagina ma'muriyat tomonidan ko'rib chiqiladi.
                </p>
                <p>
                    3.3. Foydalanuvchi o'zi yuborgan xabarlarni o'chirish huquqiga ega bo'lib, o'chirilgan xabarlar umumiy lentadan darhol olib tashlanadi.
                </p>
            </div>
        </article>

        <!-- 04. Cookie fayllari va Mahalliy Xotira -->
        <article class="p-8 sm:p-10 rounded-panel bg-ink-900 border border-ink-border space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-btn bg-gold/10 text-gold border border-gold/25 flex items-center justify-center font-mono font-bold text-sm">
                    04
                </span>
                <h2 class="text-xl font-serif font-bold text-paper">Cookie Fayllari va Mahalliy Xotira (Local Storage)</h2>
            </div>
            <div class="text-sm text-paper-muted leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    Kitobxon foydalanuvchi qulayligi uchun eng zarur bo'lgan texnik Cookie fayllaridan foydalanadi:
                </p>
                <div class="space-y-2 text-xs text-paper-muted">
                    <div class="p-3 rounded-btn bg-ink-950/80 border border-ink-border flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        <div>
                            <span class="text-gold font-bold">Kirish sessiyasi:</span>
                            <span>Har safar sahifani yangilaganda qayta parol so'ramasligi uchun xavfsiz sessiya kaliti saqlanadi.</span>
                        </div>
                    </div>
                    <div class="p-3 rounded-btn bg-ink-950/80 border border-ink-border flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        <div>
                            <span class="text-gold font-bold">Til sozlamasi:</span>
                            <span>Siz tanlagan interfeys tili (O'zbek, Rus, Ingliz) eslab qolinadi.</span>
                        </div>
                    </div>
                    <div class="p-3 rounded-btn bg-ink-950/80 border border-ink-border flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-gold shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                        <div>
                            <span class="text-gold font-bold">Mutolaa joyi:</span>
                            <span>Kitobni qaysi sahifada o'qiyotganingiz brauzeringiz xotirasida saqlanib, qayta ochganda shu yerdan davom ettiriladi.</span>
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <!-- 05. Ma'lumotlarni Uchinchi Shaxslarga Berilmasligi -->
        <article class="p-8 sm:p-10 rounded-panel bg-ink-900 border border-ink-border space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-btn bg-gold/10 text-gold border border-gold/25 flex items-center justify-center font-mono font-bold text-sm">
                    05
                </span>
                <h2 class="text-xl font-serif font-bold text-paper">Ma'lumotlarning Uchinchi Shaxslarga Berilmasligi</h2>
            </div>
            <div class="text-sm text-paper-muted leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    5.1. <strong class="text-paper">Qat'iy taqiq:</strong> Kitobxon foydalanuvchilarning shaxsiy ma'lumotlarini, telefon raqamlarini yoki elektron pochta manzillarini hech qanday tijorat tashkilotlariga yoki reklama beruvchilarga sotmaydi, ijaraga bermaydi va alishmaydi.
                </p>
                <p>
                    5.2. <strong class="text-paper">Qonuniy istisno:</strong> Ma'lumotlar faqatgina qonunchilikda belgilangan tartibda, vakolatli huquqni muhofaza qiluvchi organlarning rasmiy qonuniy so'rovlari asosida taqdim etilishi mumkin.
                </p>
            </div>
        </article>

        <!-- 06. Foydalanuvchining Huquqlari -->
        <article class="p-8 sm:p-10 rounded-panel bg-ink-900 border border-ink-border space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-btn bg-gold/10 text-gold border border-gold/25 flex items-center justify-center font-mono font-bold text-sm">
                    06
                </span>
                <h2 class="text-xl font-serif font-bold text-paper">Foydalanuvchining Qonuniy Huquqlari</h2>
            </div>
            <div class="text-sm text-paper-muted leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    Har bir ro'yxatdan o'tgan foydalanuvchi quyidagi huquqlarga ega:
                </p>
                <ul class="space-y-2 list-disc list-inside text-xs sm:text-sm text-paper-muted">
                    <li><strong class="text-paper">Ma'lumotlarni ko'rish va o'zgartirish:</strong> Shaxsiy profilingiz, ismingiz, parolingiz va rasmingizni «Sozlamalar» orqali istalgan paytda tahrirlash;</li>
                    <li><strong class="text-paper">Maxfiylik darajasini boshqarish:</strong> Profilingiz boshqalarga qanchalik ko'rinishini sozlash;</li>
                    <li><strong class="text-paper">Akkauntni to'liq o'chirish:</strong> Profilingizni va platformadagi barcha shaxsiy ma'lumotlaringizni butunlay o'chirib yuborishni talab qilish;</li>
                    <li><strong class="text-paper">Savol va e'tiroz bildirish:</strong> Ma'lumotlar xavfsizligi bo'yicha ma'muriyatdan to'liq axborot talab qilish.</li>
                </ul>
            </div>
        </article>

        <!-- 07. Xavfsizlik Kafolatlari va Bog'lanish -->
        <article class="p-8 sm:p-10 rounded-panel bg-ink-900 border border-ink-border space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-btn bg-gold/10 text-gold border border-gold/25 flex items-center justify-center font-mono font-bold text-sm">
                    07
                </span>
                <h2 class="text-xl font-serif font-bold text-paper">Siyosatning Yangilanishi va Aloqa</h2>
            </div>
            <div class="text-sm text-paper-muted leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    7.1. Kitobxon ushbu Maxfiylik Siyosatini texnologik va qonunchilik talablariga mos ravishda vaqti-vaqti bilan yangilab boradi. Barcha muhim o'zgarishlar ushbu sahifada e'lon qilinadi.
                </p>
                <p>
                    7.2. Maxfiylik va ma'lumotlar xavfsizligi bo'yicha har qanday savolingiz bo'lsa, bizning qo'llab-quvvatlash xizmatimizga bevosita murojaat qilishingiz mumkin.
                </p>
            </div>
        </article>

        <!-- CTA Contact Block -->
        <div class="p-8 sm:p-10 rounded-panel bg-ink-900 border border-gold/25 text-center space-y-4 shadow-card-depth">
            <div class="w-12 h-12 rounded-btn bg-gold/10 border border-gold/20 text-gold flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <h3 class="text-xl sm:text-2xl font-serif font-bold text-paper">Ma'lumotlar xavfsizligi bo'yicha savollaringiz bormi?</h3>
            <p class="text-xs sm:text-sm text-mist max-w-xl mx-auto leading-relaxed">
                Biz sizning daxlsizligingizni himoya qilishga to'liq tayyormiz. Ma'muriyat bilan bog'lanish uchun Aloqa sahifasidan foydalaning.
            </p>
            <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('contact') }}" class="ks-btn-gold px-6 py-3">
                    Aloqa sahifasiga o'tish
                </a>
                <a href="{{ route('terms') }}" class="ks-btn-ghost px-6 py-3">
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

    <!-- ── Universal Toast Notification Container ── -->
    <x-toast-container />

    <!-- ── Mobile Bottom Navigation Bar ── -->
    <x-nav.mobile-bottom-bar />
</body>
</html>
