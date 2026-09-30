<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ __('site.faq.sub') }}">
    <title>{{ __('site.faq.badge') }} — Kitobxon</title>

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

    <!-- GSAP & ScrollTrigger for Animations -->
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
        .faq-item {
            position: relative;
            background: rgba(17, 23, 38, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.25rem;
            overflow: hidden;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .faq-item:hover {
            border-color: rgba(251, 191, 36, 0.35);
        }
        .faq-item.active {
            border-color: rgba(251, 191, 36, 0.45);
            background: rgba(20, 28, 48, 0.9);
            box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.6), 0 0 24px -2px rgba(251, 191, 36, 0.15);
        }

        /* Ambient floating orbs */
        @keyframes orbDrift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(25px, -15px) scale(1.05); }
        }
        .orb-animate-1 { animation: orbDrift 15s ease-in-out infinite; }
        .orb-animate-2 { animation: orbDrift 20s ease-in-out infinite reverse; }
    </style>
</head>
<body class="bg-ink-950 text-slate-200 font-sans selection:bg-amber-400 selection:text-ink-950 antialiased min-h-screen relative overflow-x-hidden">

    <!-- ── Page Transition & Loader ── -->
    @include('components.page-loader')

    <!-- Ambient Glows -->
    <div class="fixed top-[-100px] left-1/2 -translate-x-1/2 w-[1000px] h-[480px] bg-gradient-to-b from-amber-500/12 via-indigo-600/6 to-transparent rounded-full blur-[140px] pointer-events-none -z-10 orb-animate-1"></div>
    <div class="fixed bottom-[-100px] right-[-100px] w-[600px] h-[600px] bg-indigo-900/12 rounded-full blur-[150px] pointer-events-none -z-10 orb-animate-2"></div>

    <div id="smooth-page-wrapper">
    <!-- ── Header ── -->
    <x-nav.main-header />

    <!-- ── Main Content (Savol-Javoblar) ── -->
    <main class="py-14 md:py-20 noise-bg"
          x-data="{
              openItem: 1,
              searchQuery: '',
              activeCat: 'all',
              toggle(id) {
                  this.openItem = (this.openItem === id ? null : id);
              },
              matches(q, c, text) {
                  const matchCat = (this.activeCat === 'all' || this.activeCat === c);
                  if (!matchCat) return false;
                  if (!this.searchQuery.trim()) return true;
                  const query = this.searchQuery.toLowerCase().trim();
                  return (q.toLowerCase().includes(query) || text.toLowerCase().includes(query));
              }
          }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            
            <!-- Sarlavha qismi -->
            <div class="faq-header text-center space-y-4 mb-10">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-400/10 border border-amber-400/25 text-amber-400 font-mono text-xs tracking-wider">
                    {{ __('site.faq.badge') }}
                </span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    {{ __('site.faq.title_1') }}<span class="text-amber-400 italic font-serif">{{ __('site.faq.title_2') }}</span>{{ __('site.faq.title_3') }}
                </h1>
                <p class="text-slate-400 text-xs sm:text-sm max-w-xl mx-auto leading-relaxed">
                    {{ __('site.faq.sub') }}
                </p>
            </div>

            <!-- Qidiruv maydoni -->
            <div class="mb-6 relative max-w-xl mx-auto">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 text-sm">
                        🔍
                    </span>
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="{{ __('site.faq.search_ph') }}"
                           class="w-full pl-11 pr-10 py-3 bg-ink-900/80 border border-white/10 rounded-2xl text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-400/60 focus:ring-2 focus:ring-amber-400/20 backdrop-blur-xl transition-all shadow-card-depth">
                    <button x-show="searchQuery.length > 0" 
                            @click="searchQuery = ''"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white text-xs font-bold"
                            style="display: none;">
                        ✕
                    </button>
                </div>
            </div>

            <!-- Kategoriya Filterlari -->
            <div class="flex items-center justify-center flex-wrap gap-2 mb-10 text-xs">
                <button @click="activeCat = 'all'"
                        :class="activeCat === 'all' ? 'bg-amber-400 text-ink-950 font-bold shadow-md shadow-amber-400/20' : 'bg-white/5 text-slate-300 hover:bg-white/10 border border-white/10'"
                        class="px-4 py-2 rounded-xl transition-all">
                    {{ __('site.faq.cat_all') }}
                </button>
                <button @click="activeCat = 'books'"
                        :class="activeCat === 'books' ? 'bg-amber-400 text-ink-950 font-bold shadow-md shadow-amber-400/20' : 'bg-white/5 text-slate-300 hover:bg-white/10 border border-white/10'"
                        class="px-4 py-2 rounded-xl transition-all">
                    {{ __('site.faq.cat_books') }}
                </button>
                <button @click="activeCat = 'points'"
                        :class="activeCat === 'points' ? 'bg-amber-400 text-ink-950 font-bold shadow-md shadow-amber-400/20' : 'bg-white/5 text-slate-300 hover:bg-white/10 border border-white/10'"
                        class="px-4 py-2 rounded-xl transition-all">
                    {{ __('site.faq.cat_points') }}
                </button>
                <button @click="activeCat = 'groups'"
                        :class="activeCat === 'groups' ? 'bg-amber-400 text-ink-950 font-bold shadow-md shadow-amber-400/20' : 'bg-white/5 text-slate-300 hover:bg-white/10 border border-white/10'"
                        class="px-4 py-2 rounded-xl transition-all">
                    {{ __('site.faq.cat_groups') }}
                </button>
                <button @click="activeCat = 'ai'"
                        :class="activeCat === 'ai' ? 'bg-amber-400 text-ink-950 font-bold shadow-md shadow-amber-400/20' : 'bg-white/5 text-slate-300 hover:bg-white/10 border border-white/10'"
                        class="px-4 py-2 rounded-xl transition-all">
                    {{ __('site.faq.cat_ai') }}
                </button>
            </div>

            <!-- Accordion Ro'yxati -->
            <div class="space-y-4">
                
                <!-- 1. Mutolaa va Kitoblar -->
                <div x-show="matches('Kitobxon platformasi qanday ishlaydi va undan qanday foydalaniladi?', 'books', 'har hafta dushanba mutolaa audio matn test yakshanba')" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 1 }">
                    <button @click="toggle(1)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-white flex items-center gap-2.5">
                            <span class="text-amber-400">📖</span>
                            Kitobxon platformasi qanday ishlaydi va undan qanday foydalaniladi?
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center text-amber-400 font-bold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-amber-400/20 text-amber-300': openItem === 1 }">
                            ▼
                        </div>
                    </button>
                    <div x-show="openItem === 1" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p>Kitobxon — bu muntazam kitob o'qish odatini shakllantirishga mo'ljallangan intellektual ekotizimdir.</p>
                        <ul class="list-disc list-inside space-y-1 text-slate-400 pl-1">
                            <li><strong class="text-white">Kitoblar katalogi:</strong> Badiiy, shaxsiy rivojlanish, biznes va ilmiy asarlarni matn yoki audio formatda mutolaa qilasiz.</li>
                            <li><strong class="text-white">Boblar ketma-ketligi:</strong> Asarlar bobma-bob beriladi, o'qish progressi avtomatik saqlanib boradi.</li>
                            <li><strong class="text-white">Bilimlarni tekshirish:</strong> Har bir kitob yakunida interaktiv testlar orqali o'rganganlaringizni mustahkamlab, ball to'playsiz.</li>
                        </ul>
                    </div>
                </div>

                <!-- 2. Bepul foydalanish -->
                <div x-show="matches('Platformadan foydalanish bepulmi yoki obuna talab qilinadimi?', 'books', 'bepul obuna tolov kitob oqish')" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 2 }">
                    <button @click="toggle(2)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-white flex items-center gap-2.5">
                            <span class="text-amber-400">💳</span>
                            Platformadan foydalanish bepulmi yoki obuna talab qilinadimi?
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center text-amber-400 font-bold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-amber-400/20 text-amber-300': openItem === 2 }">
                            ▼
                        </div>
                    </button>
                    <div x-show="openItem === 2" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4">
                        Ha, asosiy kitoblarni matn va audio formatda mutolaa qilish, umumiy chatda muloqot qilish, reytingda qatnashish va testlarni yechish barcha ro'yxatdan o'tgan kitobxonlar uchun mutlaqo bepul. Ro'yxatdan o'tmasdan turib esa kitoblar katalogi va platforma tavsifini ko'rishingiz mumkin.
                    </div>
                </div>

                <!-- 3. Audio kitoblar -->
                <div x-show="matches('Audio kitoblarni fonda (ekran o\'chiq holatda) eshitsa bo\'ladimi?', 'books', 'audio pleyer fon tinglash eshitish')" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 3 }">
                    <button @click="toggle(3)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-white flex items-center gap-2.5">
                            <span class="text-amber-400">🎧</span>
                            Audio kitoblarni fonda (ekran o'chiq holatda) eshitsa bo'ladimi?
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center text-amber-400 font-bold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-amber-400/20 text-amber-300': openItem === 3 }">
                            ▼
                        </div>
                    </button>
                    <div x-show="openItem === 3" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4">
                        Ha. Platformaning o'rnatilgan audio pleyeri mobil brauzerlar va kompyuterlarda fon rejimini qo'llab-quvvatlaydi. Boshqa ilovaga o'tsangiz yoki brauzerni minimallashtirsangiz ham audio ijro etilishda davom etadi.
                    </div>
                </div>

                <!-- 4. Ballar va Tangalar -->
                <div x-show="matches('Ballar (Points) va Tangalar (Coins) nima uchun beriladi va qayerda ishlatiladi?', 'points', 'ball tanga coin points mukofot reyting test mutolaa')" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 4 }">
                    <button @click="toggle(4)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-white flex items-center gap-2.5">
                            <span class="text-amber-400">🪙</span>
                            Ballar (Points) va Tangalar (Coins) nima uchun beriladi va qayerda ishlatiladi?
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center text-amber-400 font-bold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-amber-400/20 text-amber-300': openItem === 4 }">
                            ▼
                        </div>
                    </button>
                    <div x-show="openItem === 4" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p><strong class="text-white">Ballar (Total Points):</strong> Sizning platformadagi umumiy obro'yingiz va ilmiy darajangiz ko'rsatkichi. Ular orqali umumiy, oylik va haftalik Reyting (Leaderboard) peshqadamlari aniqlanadi.</p>
                        <p><strong class="text-white">Qanday to'planadi:</strong></p>
                        <ul class="list-disc list-inside space-y-1 text-slate-400 pl-1">
                            <li>Har 10 daqiqa kitob o'qish yoki audio eshitish uchun: <span class="text-amber-400 font-semibold">+10 ball</span></li>
                            <li>Boblar bo'yicha test savollarini to'g'ri yechish orqali test ballari</li>
                            <li>Har kungi uzluksiz streak zanjirini davom ettirish bonuslari</li>
                        </ul>
                    </div>
                </div>

                <!-- 5. Streak nima -->
                <div x-show="matches('Streak (kunlik olov) nima va u qachon o\'chib ketadi?', 'points', 'streak olov kunlik zanjir vaqt soat 23:59')" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 5 }">
                    <button @click="toggle(5)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-white flex items-center gap-2.5">
                            <span class="text-amber-400">🔥</span>
                            Streak (kunlik olov) nima va u qachon o'chib ketadi?
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center text-amber-400 font-bold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-amber-400/20 text-amber-300': openItem === 5 }">
                            ▼
                        </div>
                    </button>
                    <div x-show="openItem === 5" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p>Streak — bu sizning har kuni uzluksiz kitob mutolaa qilayotganingizni ko'rsatuvchi zanjir timsoli.</p>
                        <p>Har kuni kamida 10 daqiqa mutolaa qilsangiz, olov yonadi va ko'rsatkich +1 kunga oshadi. Agar Toshkent vaqti bilan sutka yakunigacha (23:59) kitob o'qilmasa, streak zanjiri uziladi va olov nolga tushadi.</p>
                    </div>
                </div>

                <!-- 6. Reyting (Leaderboard) -->
                <div x-show="matches('Reyting (Leaderboard) qanday ishlaydi va o\'rinlar qanday yangilanadi?', 'points', 'reyting leaderboard orin peshqadam snapshot kunlik haftalik oylik')" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 6 }">
                    <button @click="toggle(6)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-white flex items-center gap-2.5">
                            <span class="text-amber-400">📊</span>
                            Reyting (Leaderboard) qanday ishlaydi va o'rinlar qanday yangilanadi?
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center text-amber-400 font-bold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-amber-400/20 text-amber-300': openItem === 6 }">
                            ▼
                        </div>
                    </button>
                    <div x-show="openItem === 6" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p>Reyting sahifasida foydalanuvchilar to'plagan ballari bo'yicha ketma-ket saralanadi. Tizim 4 xil davr bo'yicha taqsimlangan:</p>
                        <ul class="list-disc list-inside space-y-1 text-slate-400 pl-1">
                            <li><strong class="text-white">Bugun:</strong> Faqat bugungi kun davomida to'plangan faol ballar;</li>
                            <li><strong class="text-white">Ushbu hafta:</strong> Haftalik yetakchilar;</li>
                            <li><strong class="text-white">Ushbu oy:</strong> Oylik jadval;</li>
                            <li><strong class="text-white">Barcha vaqtlar:</strong> Platformadagi jami to'plangan eng yuqori ballar egalari.</li>
                        </ul>
                        <p class="pt-1">Top-3 o'rindagi kitobxonlar maxsus oltin, kumush va bronza toj nishonlari bilan taqdirlanadi.</p>
                    </div>
                </div>

                <!-- 7. Guruhlar va Yopiq guruhlar -->
                <div x-show="matches('Guruhlar nima va yopiq (parolli) guruhga qanday qo\'shilish mumkin?', 'groups', 'guruh yopiq parol maxfiy shaxsiy klub join kod')" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 7 }">
                    <button @click="toggle(7)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-white flex items-center gap-2.5">
                            <span class="text-amber-400">🔒</span>
                            Guruhlar nima va yopiq (parolli) guruhga qanday qo'shilish mumkin?
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center text-amber-400 font-bold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-amber-400/20 text-amber-300': openItem === 7 }">
                            ▼
                        </div>
                    </button>
                    <div x-show="openItem === 7" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p><strong class="text-white">Ochiq guruhlar:</strong> Barcha foydalanuvchilar to'g'ridan-to'g'ri "Qo'shilish" tugmasi orqali a'zo bo'la oladilar.</p>
                        <p><strong class="text-white">Yopiq guruhlar:</strong> Faqat guruh yaratuvchisi o'rnatgan maxfiy parolni kiritish orqali a'zo bo'linadi. Agar siz guruhga qo'shilmoqchi bo'lsangiz, «🔒 Parol bilan kirish» tugmasini bosib, uning adminidan olingan parolni kiritishingiz kifoya.</p>
                    </div>
                </div>

                <!-- 8. Guruh ochish limitlari -->
                <div x-show="matches('Bir foydalanuvchi nechta guruh ocha oladi (guruh limitlari)?', 'groups', 'limit guruh yaratish oddiy teacher admin nechta kvota')" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 8 }">
                    <button @click="toggle(8)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-white flex items-center gap-2.5">
                            <span class="text-amber-400">⚖️</span>
                            Bir foydalanuvchi nechta guruh ocha oladi (guruh ochish limitlari)?
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center text-amber-400 font-bold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-amber-400/20 text-amber-300': openItem === 8 }">
                            ▼
                        </div>
                    </button>
                    <div x-show="openItem === 8" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p>Platformada sifatni va tartibni ta'minlash maqsadida guruh ochish bo'yicha aniq chegaralar mavjud:</p>
                        <ul class="list-disc list-inside space-y-1 text-slate-400 pl-1">
                            <li><strong class="text-white">Oddiy foydalanuvchi (Kitobxon):</strong> ko'pi bilan <span class="text-amber-400 font-bold">1 ta</span> guruh</li>
                            <li><strong class="text-white">Ustoz (Teacher):</strong> ko'pi bilan <span class="text-amber-400 font-bold">2 ta</span> guruh</li>
                            <li><strong class="text-white">Administrator:</strong> ko'pi bilan <span class="text-amber-400 font-bold">3 ta</span> guruh</li>
                        </ul>
                        <p class="pt-1">Joriy guruhlaringiz soni sahifa sarlavhasida (masalan, «📊 Guruhlaringiz: 1 / 1») ko'rinib turadi. Limitga yetganingizda yangi guruh ochish uchun avvalgisini o'chirishingiz kerak bo'ladi.</p>
                    </div>
                </div>

                <!-- 9. Guruhni o'chirish huquqi -->
                <div x-show="matches('Guruhni kimlar o\'chira oladi va bu qanday amalga oshiriladi?', 'groups', 'ochirish delete guruh yaratuvchi admin huquq')" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 9 }">
                    <button @click="toggle(9)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-white flex items-center gap-2.5">
                            <span class="text-amber-400">🗑️</span>
                            Guruhni kimlar o'chira oladi va bu qanday amalga oshiriladi?
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center text-amber-400 font-bold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-amber-400/20 text-amber-300': openItem === 9 }">
                            ▼
                        </div>
                    </button>
                    <div x-show="openItem === 9" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p>Guruhni faqat <strong class="text-white">uni yaratgan foydalanuvchi</strong> yoki <strong class="text-white">tizim administratori</strong> o'chira oladi. Oddiy a'zolarda bu tugma ko'rinmaydi va ular faqat guruhni tark etishlari mumkin.</p>
                        <p>O'chirish tugmasi bosilganda tasdiqlash oynasi chiqadi va tasdiqlangach, guruhning barcha xabarlari va a'zolik ma'lumotlari butunlay xavfsiz tarzda tozalanadi.</p>
                    </div>
                </div>

                <!-- 10. Umumiy Chat qoidalari -->
                <div x-show="matches('Umumiy Chatda kimlar yozishi va xabarlarni kimlar o\'chira oladi?', 'groups', 'global chat umumiy xabar ochirish admin limit belgilar')" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 10 }">
                    <button @click="toggle(10)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-white flex items-center gap-2.5">
                            <span class="text-amber-400">💬</span>
                            Umumiy Chatda kimlar yozishi va xabarlarni kimlar o'chira oladi?
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center text-amber-400 font-bold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-amber-400/20 text-amber-300': openItem === 10 }">
                            ▼
                        </div>
                    </button>
                    <div x-show="openItem === 10" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p>Ro'yxatdan o'tgan har bir kitobxon umumiy chatda muloqot qilishi mumkin. Chatda o'zingiz yozgan xabarlar o'ng tomonda, boshqalarniki esa chap tomonda tartibli ko'rinadi.</p>
                        <p><strong class="text-white">Xabarni o'chirish:</strong> Har bir foydalanuvchi o'z xabarining yonidagi o'chirish belgisini bosib, uni o'chira oladi. Tizim administratori esa umumiy tartibni saqlash maqsadida istalgan foydalanuvchining noo'rin xabarini o'chirish vakolatiga ega.</p>
                    </div>
                </div>

                <!-- 11. AI Kitob Maslahatchisi -->
                <div x-show="matches('AI Kitob Maslahatchisi qanday vazifani bajaradi?', 'ai', 'ai maslahatchi suniy intellekt xulosa savol tahlil')" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 11 }">
                    <button @click="toggle(11)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-white flex items-center gap-2.5">
                            <span class="text-amber-400">🤖</span>
                            AI Kitob Maslahatchisi qanday vazifani bajaradi?
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center text-amber-400 font-bold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-amber-400/20 text-amber-300': openItem === 11 }">
                            ▼
                        </div>
                    </button>
                    <div x-show="openItem === 11" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p>Sun'iy intellekt haftalik kitobning barcha boblari va g'oyalari bo'yicha maxsus o'qitilgan. Siz asar mutolaasi davomida tushunmagan jumlalaringizni so'rashingiz, boblarning asosiy xulosalarini olishingiz yoki qahramonlar xatti-harakatlarini tahlil qildirishingiz mumkin.</p>
                        <p>AI bir necha soniya ichida o'zbek tilida savodli va aniq javob qaytaradi.</p>
                    </div>
                </div>

                <!-- 12. Jonli Efirlar -->
                <div x-show="matches('Jonli Efirlarga qanday qo\'shilish mumkin va unda qanday qatnashiladi?', 'ai', 'jonli efir stream live ustoz efir zal mikrofon ovoz')" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 12 }">
                    <button @click="toggle(12)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-white flex items-center gap-2.5">
                            <span class="text-amber-400">🔴</span>
                            Jonli Efirlarga qanday qo'shilish mumkin va unda qanday qatnashiladi?
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center text-amber-400 font-bold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-amber-400/20 text-amber-300': openItem === 12 }">
                            ▼
                        </div>
                    </button>
                    <div x-show="openItem === 12" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p>Hafta yakunida «Jonli Efirlar» sahifasida qizil «🔴 Jonli» belgisi yonadi. Xonaga kirish tugmasini bosib to'g'ridan-to'g'ri ustoz ma'ruzasiga ulanishingiz mumkin.</p>
                        <p>Efir moderatori (ustoz) efir xususiyatiga qarab tomoshabinlar uchun huquqlarni belgilaydi (faqat chatda yozish, ovozli savol berish yoki tinglash rejimi). O'tkazib yuborilgan efirlarni esa keyinroq yozuvini qayta ko'rishingiz mumkin.</p>
                    </div>
                </div>

                <!-- 13. Parol va Profil Sozlamalari -->
                <div x-show="matches('Parolni yoki profil ma\'lumotlarini qanday o\'zgartirish mumkin?', 'books', 'parol profil sozlamalar username email avatar rasm')" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 13 }">
                    <button @click="toggle(13)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-white flex items-center gap-2.5">
                            <span class="text-amber-400">⚙️</span>
                            Parolni yoki profil ma'lumotlarini qanday o'zgartirish mumkin?
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center text-amber-400 font-bold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-amber-400/20 text-amber-300': openItem === 13 }">
                            ▼
                        </div>
                    </button>
                    <div x-show="openItem === 13" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4">
                        Tizimga kirganingizdan so'ng, yuqori o'ng burchakdagi profilingiz ustiga bosib, «Sozlamalar» sahifasiga o'tasiz. U yerda ismingiz, username, profilingiz bio qismi, xavfsizlik parolingiz hamda avatar rasmingizni yangilashingiz mumkin.
                    </div>
                </div>

            </div>

            <!-- Qidiruv natijasi topilmagan holat -->
            <div x-show="searchQuery.trim().length > 0 && !document.querySelectorAll('.faq-item:not([style*=\'display: none\'])').length"
                 x-cloak
                 class="text-center py-12 p-8 rounded-3xl bg-ink-900/60 border border-white/5 space-y-3">
                <span class="text-3xl block">🔍</span>
                <h4 class="text-base font-bold text-white">{{ __('site.faq.no_results_t') }}</h4>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">
                    {{ __('site.faq.no_results_s') }}
                </p>
                <button @click="searchQuery = ''; activeCat = 'all'"
                        class="px-4 py-2 bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 text-xs font-bold rounded-xl transition-colors">
                    {{ __('site.faq.clear_filter') }}
                </button>
            </div>

            <!-- Still Have Questions Banner -->
            <div class="faq-cta-card mt-16 p-8 sm:p-10 rounded-3xl bg-ink-900/90 border border-white/10 text-center space-y-4 shadow-card-depth">
                <div class="w-12 h-12 rounded-2xl bg-amber-400/10 border border-amber-400/20 text-amber-400 flex items-center justify-center text-2xl mx-auto">
                    💡
                </div>
                <h3 class="text-xl sm:text-2xl font-bold text-white">{{ __('site.faq.cta_title') }}</h3>
                <p class="text-xs sm:text-sm text-slate-400 max-w-md mx-auto leading-relaxed">
                    {{ __('site.faq.cta_sub') }}
                </p>
                <div class="pt-2">
                    <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 px-7 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider transition-all duration-200 active:scale-95 shadow-md shadow-amber-500/25 hover:shadow-glow-amber">
                        <span>{{ __('site.faq.cta_btn') }}</span>
                        <span>→</span>
                    </a>
                </div>
            </div>

        </div>
    </main>

    <!-- ── Footer ── -->
    <footer class="border-t border-white/[0.07] bg-ink-950 py-10 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <span class="font-bold text-white text-sm">Kitobxon</span>
                <span>•</span>
                <span>© {{ date('Y') }} {{ __('site.common.all_rights') }}</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}" class="hover:text-slate-300 transition-colors">{{ __('site.common.back_home') }}</a>
                <a href="{{ route('books.public') }}" class="hover:text-slate-300 transition-colors">{{ __('site.nav.books') }}</a>
                <a href="{{ route('about') }}" class="hover:text-slate-300 transition-colors">{{ __('site.nav.about') }}</a>
                <a href="{{ route('contact') }}" class="hover:text-slate-300 transition-colors">{{ __('site.nav.contact') }}</a>
            </div>
        </div>
    </footer>
    </div>

    <!-- ── Advanced Motion Script ── -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof gsap !== 'undefined') {
                gsap.registerPlugin(ScrollTrigger);

                // FAQ Header Entrance
                gsap.fromTo('.faq-header',
                    { opacity: 0, y: 30 },
                    { opacity: 1, y: 0, duration: 0.8, ease: "power3.out" }
                );

                // FAQ Accordion Cards Entrance
                gsap.fromTo('.faq-card',
                    { opacity: 0, y: 25 },
                    {
                        opacity: 1,
                        y: 0,
                        duration: 0.6,
                        stagger: 0.06,
                        ease: "power3.out",
                        clearProps: "transform,scale"
                    }
                );
            }
        });
    </script>

</body>
</html>
