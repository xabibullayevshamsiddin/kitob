<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark" x-data="{ mobileMenu: false, activeTab: 'all' }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kitobxon platformasi foydalanish shartlari, jamoat standartlari, qoidabuzarliklar va ban choralari to'g'risida rasmiy ma'lumotnoma.">
    <title>{{ __('site.nav.terms') }} va Hamjamiyat Qoidalari — Kitobxon</title>

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
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/25 text-amber-400 text-xs font-mono font-semibold tracking-wider uppercase">
                    <span>✦</span>
                    <span>RASMIY HUJJAT VA HAMJAMIYAT QOIDALARI</span>
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.1] max-w-4xl">
                    Foydalanish Shartlari va <br class="hidden sm:inline">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-amber-200 to-amber-500">Hamjamiyat Standartlari</span>
                </h1>

                <p class="text-sm sm:text-base text-slate-400 max-w-2xl leading-relaxed">
                    Kitobxon — sifatli kitob mutolaasi, intellektual rivojlanish va do'stona muloqot maydonidir. Mazkur me'yorlar barcha kitobxonlar xavfsizligi, adolatli baholash va sog'lom muhitni ta'minlash uchun xizmat qiladi.
                </p>

                <!-- Info Badges -->
                <div class="pt-4 flex flex-wrap items-center justify-center gap-3 text-xs">
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-ink-900/80 border border-white/10 text-slate-300">
                        <span class="text-amber-400 font-bold">📅 Kuchga kirish:</span>
                        <span class="font-mono text-slate-400">2026-yil (Amaldagi tahrir)</span>
                    </div>
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-ink-900/80 border border-white/10 text-slate-300">
                        <span class="text-emerald-400 font-bold">👥 Qamrovi:</span>
                        <span class="text-slate-400">Barcha a'zolar va mehmonlar</span>
                    </div>
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-ink-900/80 border border-white/10 text-slate-300">
                        <span class="text-indigo-400 font-bold">⚖️ Nazorat:</span>
                        <span class="text-slate-400">24/7 Moderatsiya & Adminlar</span>
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
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-xl mb-3">
                        📖
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1">Mualliflik & Mutolaa</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Barcha kitoblar, audio va video darsliklar faqat shaxsiy ma'rifiy mutolaa uchun beriladi.
                        </p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="p-5 rounded-2xl bg-ink-900/90 border border-white/10 flex flex-col justify-between spotlight-card">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl mb-3">
                        💬
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1">Hurmatli Muloqot</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Chat va guruhlarda so'kinish, haqorat va kamsitish qat'iyan taqiqlanadi (0-tolerantlik).
                        </p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="p-5 rounded-2xl bg-ink-900/90 border border-white/10 flex flex-col justify-between spotlight-card">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center text-xl mb-3">
                        🚫
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1">Adolatli Ban Tizimi</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Qoidabuzarlik darajasiga qarab 1 soatdan to doimiy (butun umrga) muddatgacha ban beriladi.
                        </p>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="p-5 rounded-2xl bg-ink-900/90 border border-white/10 flex flex-col justify-between spotlight-card">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-xl mb-3">
                        🚩
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1">Shikoyat & Apellyatsiya</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Har bir xabarda tezkor «🚩 Shikoyat» tugmasi bor. Nohaqlik bo'lsa qayta ko'rib chiqiladi.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Main Terms & Guidelines Content ── -->
    <main class="py-16 max-w-5xl mx-auto px-6 space-y-12">

        <!-- 1-Modda: Umumiy Qoidalar -->
        <article class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/25 flex items-center justify-center font-mono font-bold text-sm">
                    01
                </span>
                <h2 class="text-xl font-bold text-white">Umumiy Qoidalar va Shartlarni Qabul Qilish</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    1.1. Mazkur «Foydalanish shartlari va Hamjamiyat qoidalari» (keyingi o'rinlarda — <em>Qoidalar</em>) <strong>Kitobxon</strong> veb-platformasidan (keyingi o'rinlarda — <em>Platforma</em>) foydalanish tartibini belgilaydi.
                </p>
                <p>
                    1.2. Platformada ro'yxatdan o'tish, shaxsiy kabinet yaratish yoki uning har qanday xizmatlaridan (mutolaa, audio eshitish, video ko'rish, test topshirish, chatlarda qatnashish) foydalanish orqali siz ushbu shartlarga to'liq va so'zsiz rozilik bildirasiz.
                </p>
                <p>
                    1.3. Agar siz mazkur qoidalarning qaysidir bandiga rozi bo'lmasangiz, platformadan foydalanishni to'xtatishingiz va o'z hisobingizni o'chirish huquqiga egasiz.
                </p>
            </div>
        </article>

        <!-- 2-Modda: Akkaunt Xavfsizligi -->
        <article class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/25 flex items-center justify-center font-mono font-bold text-sm">
                    02
                </span>
                <h2 class="text-xl font-bold text-white">Akkaunt Xavfsizligi va Foydalanuvchi Mas'uliyati</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    2.1. Foydalanuvchi ro'yxatdan o'tishda to'g'ri va ishonchli ma'lumotlarni kiritishi shart. Yolg'on shaxs nomidan yoki birovning obro'siga putur yetkazish maqsadida profil ochish taqiqlanadi.
                </p>
                <p>
                    2.2. Foydalanuvchi o'z paroli va hisob qaydnomasi xavfsizligi uchun to'liq shaxsan javobgardir. Hisobingiz orqali amalga oshirilgan barcha xabarlar va harakatlar sizning nomingizdan deb hisoblanadi.
                </p>
                <p>
                    2.3. Bitta shaxs tomonidan reyting yoki ballarni sun'iy oshirish (cheat) maqsadida bir nechta parallel akkauntlar ochishi (multi-account) taqiqlanadi. Aniqlangan ko'p sonli soxta akkauntlar ogohlantirishsiz o'chiriladi.
                </p>
            </div>
        </article>

        <!-- 3-Modda: Mualliflik Huquqlari -->
        <article class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/25 flex items-center justify-center font-mono font-bold text-sm">
                    03
                </span>
                <h2 class="text-xl font-bold text-white">Mualliflik Huquqlari va Intellektual Mulk</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    3.1. Kitobxon platformasida taqdim etilayotgan elektron kitoblar (PDF/ePub), audio darslar, tahliliy videoroliklar va savol-javoblar intellektual mulk to'g'risidagi qonunchilik bilan himoyalanadi.
                </p>
                <p>
                    3.2. Ushbu materiallardan faqatgina shaxsiy ta'lim, mutolaa va notijorat maqsadlarda foydalanishga ruxsat beriladi.
                </p>
                <p>
                    3.3. Platformadagi resurslarni muallif va platforma ma'muriyatining yozma roziligisiz boshqa saytlarda pullik yoki bepul qayta tarqatish, tijoriy maqsadlarda sotish qat'iyan man etiladi.
                </p>
            </div>
        </article>

        <!-- 4-Modda: Muloqot Odobi -->
        <article class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/25 flex items-center justify-center font-mono font-bold text-sm">
                    04
                </span>
                <h2 class="text-xl font-bold text-white">Muloqot Madaniyati, Global Chat va Guruhlar Odobi</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    4.1. Kitobxon hamjamiyatida <strong>o'zaro hurmat</strong> birlamchi qoidadir. Fikrlar xilma-xilligi qo'llab-quvvatlanadi, biroq fikr bildirish hech qachon shaxsiyatga tajovuzga aylanmasligi lozim.
                </p>
                <p>
                    4.2. Global chat va guruhlarda so'kinish, haqorat, behayo so'zlar, boshqa millat, din yoki jins vakillarini kamsitishga qaratilgan har qanday bayonotlar taqiqlanadi.
                </p>
                <p>
                    4.3. Ovozli xabarlar (Voice message): Ovozli xabarlarda qasddan shovqin chiqarish, baqirish, musiqa eshittirish yoki provokatsion nutq so'zlash cheklovlarga sabab bo'ladi.
                </p>
            </div>
        </article>

        <!-- ── 🔥 5-MODDA: ASOSIY BAN ME'YORLARI JADVALI 🔥 ── -->
        <article class="p-8 sm:p-10 rounded-3xl bg-gradient-to-b from-ink-900/90 via-ink-900/80 to-ink-950 border border-amber-500/30 shadow-2xl relative overflow-hidden">
            <!-- Glow effect -->
            <div class="absolute -top-24 -right-24 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/40 flex items-center justify-center font-bold text-lg">
                        ⚖️
                    </span>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-white">05. Qoidabuzarliklar va Jazo Choralari (Ban Muddatlari)</h2>
                        <p class="text-xs text-amber-400/80 mt-0.5 font-mono">Qaysi qoidabuzarlikka qancha muddatga ban beriladi?</p>
                    </div>
                </div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-bold">
                    <span>🚫 5 ta intizomiy daraja</span>
                </div>
            </div>

            <div class="mt-6 text-sm text-slate-300 leading-relaxed mb-6">
                Platforma ma'muriyati va moderatorlari tomonidan qo'llaniladigan jazo choralari adolatli, mutanosib va shaffof bo'lib, quyidagi qat'iy standartlar asosida amalga oshiriladi:
            </div>

            <!-- Ban Tiers Grid -->
            <div class="space-y-4">

                <!-- Tier 1: 1 Soat -->
                <div class="p-5 sm:p-6 rounded-2xl bg-ink-950/80 border border-amber-500/30 hover:border-amber-500/60 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2.5 py-1 rounded-lg bg-amber-500/20 text-amber-300 font-mono font-black text-xs border border-amber-500/30">
                                ⏱️ 1 SOAT
                            </span>
                            <h3 class="text-sm sm:text-base font-bold text-white">1-Daraja: Yengil Tartibbuzarlik va Spam (Sovutish muddati)</h3>
                        </div>
                        <span class="text-[11px] font-mono text-slate-400 bg-slate-800/80 px-2 py-0.5 rounded border border-slate-700">
                            Vaqtincha cheklov
                        </span>
                    </div>
                    <ul class="text-xs sm:text-sm text-slate-300 space-y-2 list-disc list-inside leading-relaxed">
                        <li><strong>Spam va Flood:</strong> Chat yoki guruhda ketma-ket bir xil xabarlar, emojilar yoki stikerlarni tinimsiz jo'natish;</li>
                        <li><strong>Baqirib yozish (CAPS LOCK):</strong> Xabarlarni qasddan faqat katta harflar bilan yoki ma'nosiz belgilar to'plami bilan to'ldirish;</li>
                        <li><strong>Ovozli xabardagi shovqin:</strong> Mikrofonga qasddan puflash, baland shovqin yoki quloqni og'rituvchi tovushlar yozish;</li>
                        <li><strong>Mavzudan tashqari mayda havolalar:</strong> Shaxsiy kanal, bot yoki boshqa loyihalarni ruxsatsiz targ'ib qilish.</li>
                    </ul>
                    <div class="mt-3 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-amber-400/90 font-mono">
                        <span>⚡ Maqsad: Foydalanuvchini tinchlantirish va ogohlantirish</span>
                        <span class="text-slate-400">1 soatdan so'ng cheklov avtomatik yechiladi</span>
                    </div>
                </div>

                <!-- Tier 2: 1 Kun (24 soat) -->
                <div class="p-5 sm:p-6 rounded-2xl bg-ink-950/80 border border-orange-500/30 hover:border-orange-500/60 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2.5 py-1 rounded-lg bg-orange-500/20 text-orange-300 font-mono font-black text-xs border border-orange-500/30">
                                📅 1 KUN (24 SOAT)
                            </span>
                            <h3 class="text-sm sm:text-base font-bold text-white">2-Daraja: Odobsizlik, Shaxsiyatga Tegish va Provokatsiya</h3>
                        </div>
                        <span class="text-[11px] font-mono text-slate-400 bg-slate-800/80 px-2 py-0.5 rounded border border-slate-700">
                            Birinchi jiddiy chora
                        </span>
                    </div>
                    <ul class="text-xs sm:text-sm text-slate-300 space-y-2 list-disc list-inside leading-relaxed">
                        <li><strong>Birinchi marotaba haqorat:</strong> Boshqa foydalanuvchiga nisbatan so'kinish yoki haqoratli so'z ishlatish;</li>
                        <li><strong>Shaxsiyatga tajovuz:</strong> Suhbatdoshning intellekti, tashqi ko'rinishi yoki shaxsiy xususiyatlarini kamsitish;</li>
                        <li><strong>Janjal qo'zg'ash (Trolling):</strong> Chat ishtirokchilarini ataylab asabiylashtirish va provokatsion bahslar uyushtirish;</li>
                        <li><strong>Moderator talabiga bo'ysunmaslik:</strong> Xulq-atvorni to'g'rilash haqidagi ogohlantirishlarni mensimaslik.</li>
                    </ul>
                    <div class="mt-3 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-orange-400/90 font-mono">
                        <span>⚡ Maqsad: Tartibni tiklash va jiddiy intizomiy dars berish</span>
                        <span class="text-slate-400">24 soatlik to'liq blok</span>
                    </div>
                </div>

                <!-- Tier 3: 1 Hafta (7 kun) -->
                <div class="p-5 sm:p-6 rounded-2xl bg-ink-950/80 border border-rose-500/40 hover:border-rose-500/70 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2.5 py-1 rounded-lg bg-rose-500/20 text-rose-300 font-mono font-black text-xs border border-rose-500/30">
                                🗓️ 1 HAFTA (7 KUN)
                            </span>
                            <h3 class="text-sm sm:text-base font-bold text-white">3-Daraja: Takroriy Qoidabuzarlik, Ommaviy Nizo va Shaxsiy Doxxing</h3>
                        </div>
                        <span class="text-[11px] font-mono text-slate-400 bg-slate-800/80 px-2 py-0.5 rounded border border-slate-700">
                            Og'ir qoidabuzarlik
                        </span>
                    </div>
                    <ul class="text-xs sm:text-sm text-slate-300 space-y-2 list-disc list-inside leading-relaxed">
                        <li><strong>Takroriy so'kinish:</strong> Avval 1 kunlik ban olgan foydalanuvchining yana qoidani buzishi;</li>
                        <li><strong>Ommaviy mojaro (Flame War):</strong> Guruh yoki umumiy chatda ataylab bir necha kishini tortishuvga tortish, tartibsizlik;</li>
                        <li><strong>Shaxsiy ma'lumotlarni oshkor qilish (Doxxing):</strong> Boshqa birovning telefon raqami, fotosurati yoki yopiq yozishmalarini ruxsatsiz tarqatish;</li>
                        <li><strong>Firibgarlik va fishing:</strong> Foydalanuvchilarni aldashga qaratilgan havolalar, pullik o'yinlar yoki firibgar botlarni jo'natish.</li>
                    </ul>
                    <div class="mt-3 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-rose-400/90 font-mono">
                        <span>⚡ Maqsad: Jamoani zarardan to'liq ihotalash</span>
                        <span class="text-slate-400">7 kun davomida chat to'xtatiladi</span>
                    </div>
                </div>

                <!-- Tier 4: 1 Oy (30 kun) -->
                <div class="p-5 sm:p-6 rounded-2xl bg-ink-950/80 border border-purple-500/40 hover:border-purple-500/70 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2.5 py-1 rounded-lg bg-purple-500/20 text-purple-300 font-mono font-black text-xs border border-purple-500/30">
                                🌕 1 OY (30 KUN)
                            </span>
                            <h3 class="text-sm sm:text-base font-bold text-white">4-Daraja: Qasddan Nafrat Qo'zg'ash, Tuhmat va Tizim Manipulyatsiyasi</h3>
                        </div>
                        <span class="text-[11px] font-mono text-slate-400 bg-slate-800/80 px-2 py-0.5 rounded border border-slate-700">
                            O'ta og'ir holat
                        </span>
                    </div>
                    <ul class="text-xs sm:text-sm text-slate-300 space-y-2 list-disc list-inside leading-relaxed">
                        <li><strong>Nafrat uyg'otuvchi nutq (Hate speech):</strong> Milliy, diniy, etnik yoki irqiy kamsitish, adovat qo'zg'ash;</li>
                        <li><strong>Tuhmat va shantaj:</strong> O'qituvchilar, kitob mualliflari yoki ma'muriyat xodimlariga asossiz tuhmat qilish, tahdid solish;</li>
                        <li><strong>Tizimni aldash (Cheat/Bot):</strong> Ballar, tangalar yoki kitob o'qish vaqtini sun'iy oshiruvchi skriptlar ishlatish;</li>
                        <li><strong>Tizimli tartibsizlik:</strong> Bir nechta ogohlantirishlarga qaramay, platforma qoidalarini ataylab mensimaslik.</li>
                    </ul>
                    <div class="mt-3 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-purple-400/90 font-mono">
                        <span>⚡ Maqsad: Uzoq muddatli cheklov va reytingni bekor qilish</span>
                        <span class="text-slate-400">30 kunlik qat'iy cheklov</span>
                    </div>
                </div>

                <!-- Tier 5: Doimiy / Butun umrga -->
                <div class="p-5 sm:p-6 rounded-2xl bg-gradient-to-r from-red-950/80 to-ink-950 border border-red-500/50 hover:border-red-500 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2.5 py-1 rounded-lg bg-red-600 text-white font-mono font-black text-xs shadow-lg shadow-red-600/30">
                                ⛔ DOIMIY / BUTUN UMRGA
                            </span>
                            <h3 class="text-sm sm:text-base font-black text-red-300">5-Daraja: Qonunbuzarlik, Kiberhujum va Qat'iy Cheklov</h3>
                        </div>
                        <span class="text-[11px] font-mono text-red-400 bg-red-500/10 px-2 py-0.5 rounded border border-red-500/25">
                            Tiklab bo'lmaydigan jazo
                        </span>
                    </div>
                    <ul class="text-xs sm:text-sm text-red-100/90 space-y-2 list-disc list-inside leading-relaxed">
                        <li><strong>Taqiqlangan materiallar:</strong> Ekstremizm, terrorizm, pornografiya, giyohvandlik yoki zo'ravonlikni targ'ib qiluvchi har qanday kontent;</li>
                        <li><strong>Kiberhujumlar va xakerlik:</strong> Platforma serverlariga SQL injection, XSS, DDoS hujumlari yoki bazaga noqonuniy kirishga urinishlar;</li>
                        <li><strong>Moliyaviy firibgarlik:</strong> Boshqa foydalanuvchilarning hisoblarini o'g'irlash yoki ulardan pul undirishga urinish;</li>
                        <li><strong>Doimiy sabotaj:</strong> Avvalgi barcha choralardan to'g'ri xulosa chiqarmasdan, destruktiv harakatlarni to'xtatmaslik.</li>
                    </ul>
                    <div class="mt-3 pt-3 border-t border-red-500/20 flex flex-col sm:flex-row items-start sm:items-center justify-between text-xs text-red-300 font-mono gap-1">
                        <span>⚡ Oqibat: Akkaunt butunlay o'chiriladi, to'plangan barcha ballar va yutuqlar bekor qilinadi.</span>
                        <span class="text-red-400 font-bold">Qayta tiklanmaydi</span>
                    </div>
                </div>

            </div>

            <!-- Note on Ban Execution -->
            <div class="mt-6 p-4 rounded-xl bg-slate-900/90 border border-slate-800 text-xs text-slate-400 leading-relaxed flex items-start gap-3">
                <span class="text-base text-amber-400">💡</span>
                <div>
                    <strong class="text-slate-200">Eslatma:</strong> Ban muddati mobaynida foydalanuvchi kitoblarni o'qishi mumkin, biroq global chat, guruhlarda xabar yozish, ovozli xabar yuborish va fikr qoldirish imkoniyati butunlay to'xtatiladi.
                </div>
            </div>
        </article>

        <!-- 6-Modda: Shikoyat va Apellyatsiya -->
        <article class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/25 flex items-center justify-center font-mono font-bold text-sm">
                    06
                </span>
                <h2 class="text-xl font-bold text-white">Shikoyat Qilish Tartibi va Apellyatsiya Huquqi</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    6.1. Har qanday foydalanuvchi jamoat maydonlarida qoidabuzarlikni ko'rganda, xabar yonidagi <strong>«🚩 Shikoyat»</strong> tugmasini bosish orqali ma'muriyatga tezkor xabar yuborish huquqiga ega.
                </p>
                <p>
                    6.2. Shikoyatda nojo'ya xabar matni, qoidabuzar foydalanuvchi ID raqami va havola avtomatik ravishda qayd etiladi va 24 soat ichida moderatorlar tomonidan ko'rib chiqiladi.
                </p>
                <p>
                    6.3. Agar foydalanuvchi o'ziga nisbatan asossiz yoki nohaq ban berilgan deb hisoblasa, u <a href="{{ route('contact') }}" class="text-amber-400 underline hover:text-amber-300 font-semibold">Aloqa bo'limi</a> orqali o'z vaziyatini tushuntirib apellyatsiya berishi mumkin. Bosh administratorlar murojaatni qayta tekshirib, xatolik aniqlansa, banni bekor qilishga kafolat beradi.
                </p>
            </div>
        </article>

        <!-- 7-Modda: Yakuniy Qoidalar -->
        <article class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/25 flex items-center justify-center font-mono font-bold text-sm">
                    07
                </span>
                <h2 class="text-xl font-bold text-white">Shartlarning O'zgarishi va Yakuniy Qoidalar</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>
                    7.1. Kitobxon ma'muriyati mazkur Shartlarni bir tomonlama tartibda o'zgartirish yoki to'ldirish huquqini o'zida saqlab qoladi.
                </p>
                <p>
                    7.2. Yangi tahrirdagi shartlar ushbu sahifada e'lon qilingan paytdan boshlab darhol kuchga kiradi.
                </p>
            </div>
        </article>

        <!-- CTA Contact Block -->
        <div class="p-8 sm:p-10 rounded-3xl bg-gradient-to-r from-ink-900 via-slate-900 to-ink-900 border border-white/10 text-center space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-2xl mx-auto">
                📬
            </div>
            <h3 class="text-xl font-black text-white">Savollaringiz yoki takliflaringiz bormi?</h3>
            <p class="text-xs sm:text-sm text-slate-400 max-w-xl mx-auto leading-relaxed">
                Qoidalar, apellyatsiyalar yoki hamkorlik masalalari bo'yicha biz bilan to'g'ridan-to'g'ri bog'lanishingiz mumkin.
            </p>
            <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('contact') }}" class="px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-ink-950 font-bold text-xs uppercase tracking-wider shadow-lg shadow-amber-500/20 transition-all active:scale-95">
                    Aloqa sahifasiga o'tish
                </a>
                <a href="{{ route('home') }}" class="px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition-colors border border-slate-700">
                    Bosh sahifaga qaytish
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
