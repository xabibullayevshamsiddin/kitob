<!DOCTYPE html>
<html lang="uz" x-data="{ darkMode: false, mobileMenu: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kitobxon haqida — Bizning missiya, jamoa va platforma statistikasi.">
    <title>Haqimizda — Kitobxon 📚</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                        serif: ['Merriweather', 'ui-serif', 'Georgia'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out forwards',
                        'slide-up': 'slideUp 0.7s ease-out forwards',
                        'float': 'float 3s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4,0,0.6,1) infinite',
                    },
                    keyframes: {
                        fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                        slideUp: { '0%': { opacity: '0', transform: 'translateY(40px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                        float: { '0%,100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-12px)' } },
                    },
                    boxShadow: {
                        'soft': '0 4px 24px -4px rgba(79,70,229,0.12)',
                        'glow': '0 0 40px rgba(79,70,229,0.25)',
                    },
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Merriweather:wght@400;700;900&display=swap" rel="stylesheet">

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        .gradient-text {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 40%, #f59e0b 100%);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradient 4s ease infinite;
        }
        @keyframes gradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .glass {
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.5);
        }
        .dark .glass {
            background: rgba(30,27,75,0.6);
            border: 1px solid rgba(79,70,229,0.2);
        }

        .card-hover { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card-hover:hover { transform: translateY(-6px); box-shadow: 0 20px 60px -10px rgba(79,70,229,0.2); }

        .nav-link { position: relative; }
        .nav-link::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 0; height: 2px; background: linear-gradient(90deg, #4f46e5, #f59e0b); transition: width 0.3s ease; }
        .nav-link:hover::after { width: 100%; }

        .observe-animate { opacity: 0; transform: translateY(30px); transition: all 0.7s ease-out; }
        .observe-animate.visible { opacity: 1; transform: translateY(0); }
        .observe-delay-1 { transition-delay: 0.1s; }
        .observe-delay-2 { transition-delay: 0.2s; }
        .observe-delay-3 { transition-delay: 0.3s; }
        .observe-delay-4 { transition-delay: 0.4s; }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1px;
            top: 28px;
            bottom: -28px;
            width: 2px;
            background: linear-gradient(180deg, #4f46e5 0%, transparent 100%);
        }
        .timeline-item:last-child::before { display: none; }
    </style>
</head>
<body class="font-sans bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 transition-colors duration-300 overflow-x-hidden">

<!-- ========== HEADER ========== -->
<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 20">
    <div :class="scrolled ? 'glass shadow-soft' : 'bg-transparent'" class="transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-20">
                <a href="{{ url('/') }}" class="flex items-center gap-2 group">
                    <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-violet-600 rounded-xl flex items-center justify-center shadow-glow group-hover:scale-110 transition-transform duration-300">📚</div>
                    <span class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Kitob<span class="text-indigo-600">xon</span></span>
                </a>

                <nav class="hidden lg:flex items-center gap-8">
                    <a href="{{ url('/') }}" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">Bosh sahifa</a>
                    <a href="{{ url('books') }}" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">Kitoblar</a>
                    <a href="{{ url('about') }}" class="nav-link text-indigo-600 dark:text-indigo-400 font-semibold transition-colors duration-200">Haqimizda</a>
                    <a href="{{ url('contact') }}" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">Aloqa</a>
                    <a href="{{ url('faq') }}" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">FAQ</a>
                </nav>

                <div class="hidden lg:flex items-center gap-3">
                    <button @click="darkMode = !darkMode" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors duration-200">
                        <span x-show="!darkMode">🌙</span>
                        <span x-show="darkMode">☀️</span>
                    </button>
                    <a href="{{ url('login') }}" class="px-4 py-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 transition-all duration-200">Kirish</a>
                    <a href="{{ url('register') }}" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 rounded-xl hover:from-indigo-700 hover:to-violet-700 shadow-glow hover:shadow-none transition-all duration-300 hover:scale-105">Ro'yxatdan o'tish</a>
                </div>

                <button @click="mobileMenu = !mobileMenu" class="lg:hidden w-10 h-10 flex flex-col items-center justify-center gap-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <span :class="mobileMenu ? 'rotate-45 translate-y-2' : ''" class="w-5 h-0.5 bg-slate-700 dark:bg-slate-300 transition-transform duration-300"></span>
                    <span :class="mobileMenu ? 'opacity-0' : ''" class="w-5 h-0.5 bg-slate-700 dark:bg-slate-300 transition-opacity duration-300"></span>
                    <span :class="mobileMenu ? '-rotate-45 -translate-y-2' : ''" class="w-5 h-0.5 bg-slate-700 dark:bg-slate-300 transition-transform duration-300"></span>
                </button>
            </div>
        </div>

        <div x-show="mobileMenu" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="lg:hidden glass border-t border-white/20 dark:border-slate-700/50">
            <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col gap-2">
                <a href="{{ url('/') }}" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">Bosh sahifa</a>
                <a href="{{ url('books') }}" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">Kitoblar</a>
                <a href="{{ url('about') }}" class="px-4 py-3 rounded-xl bg-indigo-50 dark:bg-indigo-950 font-semibold text-indigo-600 dark:text-indigo-400 transition-colors">Haqimizda</a>
                <a href="{{ url('contact') }}" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">Aloqa</a>
                <a href="{{ url('faq') }}" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">FAQ</a>
                <div class="flex gap-3 pt-2 border-t border-slate-200 dark:border-slate-700">
                    <a href="{{ url('login') }}" class="flex-1 py-2.5 text-center font-semibold text-indigo-600 border border-indigo-200 dark:border-indigo-800 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 transition-colors">Kirish</a>
                    <a href="{{ url('register') }}" class="flex-1 py-2.5 text-center font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 rounded-xl transition-all">Ro'yxatdan o'tish</a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- ========== HERO / PAGE HEADER ========== -->
<section class="relative pt-32 pb-20 overflow-hidden">
    <div class="absolute inset-0"
         style="background: radial-gradient(ellipse 80% 80% at 50% -10%, rgba(79,70,229,0.15) 0%, transparent 70%);"></div>
    <div class="absolute inset-0 opacity-[0.03] dark:opacity-[0.05]"
         style="background-image: radial-gradient(circle, #4f46e5 1px, transparent 1px); background-size: 40px 40px;"></div>

    <!-- Floating elements -->
    <div class="absolute top-32 left-[10%] text-4xl opacity-20 animate-float" style="animation-delay:0s;">🌟</div>
    <div class="absolute top-40 right-[10%] text-3xl opacity-15 animate-float" style="animation-delay:1s;">📖</div>
    <div class="absolute bottom-10 left-[25%] text-3xl opacity-15 animate-float" style="animation-delay:2s;">💡</div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-50 dark:bg-indigo-950 border border-indigo-100 dark:border-indigo-900 text-indigo-700 dark:text-indigo-300 text-sm font-semibold mb-6 animate-fade-in">
            📚 Bizning hikoyamiz
        </div>
        <h1 class="text-5xl lg:text-6xl font-black text-slate-900 dark:text-white leading-tight mb-6 animate-slide-up">
            Biz haqimizda
        </h1>
        <p class="text-xl text-slate-500 dark:text-slate-400 leading-relaxed max-w-2xl mx-auto animate-slide-up" style="animation-delay:0.2s;">
            Kitobxon — O'zbekistonda kitob o'qish madaniyatini yangi bosqichga olib chiqish missiyasida bo'lgan jamoa.
        </p>
    </div>
</section>

<!-- ========== MISSION SECTION ========== -->
<section class="py-20 bg-white dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

            <!-- Text -->
            <div class="observe-animate">
                <span class="inline-block px-4 py-1.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 text-sm font-bold rounded-full mb-6 tracking-wide uppercase">Missiyamiz</span>
                <h2 class="text-4xl lg:text-5xl font-black text-slate-900 dark:text-white leading-tight mb-6">
                    O'qish <span class="gradient-text">odatga</span><br>
                    aylansin
                </h2>
                <p class="text-slate-500 dark:text-slate-400 text-lg leading-relaxed mb-6">
                    Biz 2023-yilda bitta orzudan yo'lga chiqdik: O'zbekistonda har bir insonni kitob o'qishga rag'batlantirib, bilim olishni osonlashtirish. Platformamiz orqali o'quvchilar nafaqat kitob o'qiydi, balki bilim almashadi, o'sadi va ilhomlantiradi.
                </p>
                <p class="text-slate-500 dark:text-slate-400 text-lg leading-relaxed mb-8">
                    Har hafta yangi kitob, har oy yangi imkoniyat. Audio, video va matn formatlarida ta'lim — barchasi bir joyda, barchasi qulay.
                </p>

                <div class="grid grid-cols-2 gap-4">
                    @foreach([
                        ['icon'=>'🎯','title'=>'Maqsad','desc'=>'Yiliga 52 kitob o\'qish'],
                        ['icon'=>'💡','title'=>'Innovatsiya','desc'=>'3 format, 1 platforma'],
                        ['icon'=>'🤝','title'=>'Hamjamiyat','desc'=>'Birgalikda o\'sish'],
                        ['icon'=>'🌍','title'=>'Miqyos','desc'=>'O\'zbek tilida global'],
                    ] as $val)
                    <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700">
                        <div class="text-2xl mb-2">{{ $val['icon'] }}</div>
                        <h4 class="font-bold text-slate-900 dark:text-white text-sm">{{ $val['title'] }}</h4>
                        <p class="text-slate-500 dark:text-slate-400 text-xs">{{ $val['desc'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Visual -->
            <div class="observe-animate observe-delay-2">
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-400/20 to-violet-400/20 rounded-3xl blur-2xl scale-105"></div>
                    <div class="relative bg-gradient-to-br from-indigo-600 to-violet-700 rounded-3xl p-10 text-white overflow-hidden">
                        <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                        <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>

                        <div class="relative">
                            <div class="text-6xl mb-6 animate-float">📚</div>
                            <blockquote class="text-2xl font-serif font-bold leading-snug mb-4 italic">
                                "Kitob o'qish — bu eng arzon sayohat va eng foydali vaqt o'tkazish usuli."
                            </blockquote>
                            <cite class="text-indigo-200 not-italic text-sm">— Kitobxon falsafasi</cite>

                            <div class="mt-8 grid grid-cols-3 gap-4 text-center">
                                @foreach([['1000+','O\'quvchi'],['50+','Kitob'],['3','Format']] as $stat)
                                <div>
                                    <p class="text-3xl font-black">{{ $stat[0] }}</p>
                                    <p class="text-indigo-200 text-xs">{{ $stat[1] }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== STATS SECTION ========== -->
<section class="py-20 bg-slate-50 dark:bg-slate-800/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 observe-animate">
            <span class="inline-block px-4 py-1.5 bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 text-sm font-bold rounded-full mb-4 tracking-wide uppercase">Raqamlarda</span>
            <h2 class="text-4xl lg:text-5xl font-black text-slate-900 dark:text-white">Platforma statistikasi</h2>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $stats = [
                    ['number'=>'1000+','label'=>'O\'quvchilar','icon'=>'👥','color'=>'indigo','desc'=>'Faol foydalanuvchilar'],
                    ['number'=>'50+','label'=>'Kitoblar','icon'=>'📚','color'=>'violet','desc'=>'Premium kontentlar'],
                    ['number'=>'3','label'=>'Formatlar','icon'=>'🎯','color'=>'amber','desc'=>'Matn, audio, video'],
                    ['number'=>'98%','label'=>'Mamnunlik','icon'=>'⭐','color'=>'green','desc'=>'O\'quvchilar baholashi'],
                    ['number'=>'40+','label'=>'Muallif','icon'=>'✍️','color'=>'pink','desc'=>'Mahalliy va xorijiy'],
                    ['number'=>'7','label'=>'Janrlar','icon'=>'🏷️','color'=>'orange','desc'=>'Keng qamrov'],
                    ['number'=>'24/7','label'=>'Xizmat','icon'=>'🌐','color'=>'cyan','desc'=>'Doim ishlaymiz'],
                    ['number'=>'Free','label'=>'Boshlash','icon'=>'🎁','color'=>'emerald','desc'=>'Bepul ro\'yxat'],
                ];
            @endphp
            @foreach($stats as $stat)
            <div class="card-hover observe-animate observe-delay-{{ ($loop->index % 4) + 1 }} p-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-soft text-center">
                <div class="text-4xl mb-3">{{ $stat['icon'] }}</div>
                <p class="text-3xl lg:text-4xl font-black text-slate-900 dark:text-white">{{ $stat['number'] }}</p>
                <p class="font-bold text-slate-700 dark:text-slate-300 mt-1">{{ $stat['label'] }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $stat['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ========== VALUES SECTION ========== -->
<section class="py-20 bg-white dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 observe-animate">
            <span class="inline-block px-4 py-1.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 text-sm font-bold rounded-full mb-4 tracking-wide uppercase">Qadriyatlarimiz</span>
            <h2 class="text-4xl lg:text-5xl font-black text-slate-900 dark:text-white">Biz nimaga ishonамиз</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @php
                $values = [
                    ['icon'=>'🌱','title'=>'Doimiy o\'sish','desc'=>'Har kuni bir oz ko\'proq bilish — bu bizning asosiy qadriyatimiz. O\'qish hayot bo\'yi davom etadi.','gradient'=>'from-green-500 to-emerald-600'],
                    ['icon'=>'🤝','title'=>'Birga rivojlanish','desc'=>'Yolg\'iz o\'qish va guruhda o\'qish — butunlay boshqa tajriba. Hamjamiyat kuchini ishlatamiz.','gradient'=>'from-indigo-500 to-violet-600'],
                    ['icon'=>'✨','title'=>'Sifat va Haqiqat','desc'=>'Faqat tasdiqlangan, sifatli va foydali kitoblar. Keraksiz narsalar bilan vaqtingizni olmaymiz.','gradient'=>'from-amber-400 to-orange-500'],
                ];
            @endphp
            @foreach($values as $value)
            <div class="card-hover observe-animate observe-delay-{{ $loop->iteration }} p-8 bg-slate-50 dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 text-center">
                <div class="w-20 h-20 bg-gradient-to-br {{ $value['gradient'] }} rounded-3xl flex items-center justify-center text-4xl mx-auto mb-6 shadow-glow">{{ $value['icon'] }}</div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white mb-3">{{ $value['title'] }}</h3>
                <p class="text-slate-500 dark:text-slate-400 leading-relaxed">{{ $value['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ========== TIMELINE ========== -->
<section class="py-20 bg-slate-50 dark:bg-slate-800/50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 observe-animate">
            <span class="inline-block px-4 py-1.5 bg-violet-50 dark:bg-violet-950 text-violet-700 dark:text-violet-300 text-sm font-bold rounded-full mb-4 tracking-wide uppercase">Tarix</span>
            <h2 class="text-4xl lg:text-5xl font-black text-slate-900 dark:text-white">Bizning sayohat</h2>
        </div>

        <div class="space-y-0">
            @php
                $timeline = [
                    ['year'=>'2023','month'=>'Yanvar','title'=>'G\'oya tug\'ildi','desc'=>'Founder Otabek Yusupov kitob o\'qishni osonlashtirish g\'oyasini qog\'ozga tushirdi.','icon'=>'💡','color'=>'indigo'],
                    ['year'=>'2023','month'=>'Mart','title'=>'Jamoa tuzildi','desc'=>'5 nafar ishtiyoqli mutaxassis birlashdi: dasturchi, dizayner, kontent yaratuvchi va marketing.','icon'=>'👥','color'=>'violet'],
                    ['year'=>'2023','month'=>'Iyun','title'=>'Beta versiya','desc'=>'50 ta taklif asosida beta test o\'tkazildi. 200+ foydalanuvchi bilan 1 oy sinovdan o\'tdi.','icon'=>'🚀','color'=>'amber'],
                    ['year'=>'2023','month'=>'Sentyabr','title'=>'Rasmiy ishga tushish','desc'=>'Kitobxon rasman ochildi! Birinchi hafta 500+ ro\'yxatdan o\'tish.','icon'=>'🎉','color'=>'green'],
                    ['year'=>'2024','month'=>'Fevral','title'=>'Audio kitoblar','desc'=>'Professional ovozli audio kitoblar formati qo\'shildi. 20+ kitob darhol audio formatda.','icon'=>'🎵','color'=>'pink'],
                    ['year'=>'2024','month'=>'May','title'=>'1000 o\'quvchi','desc'=>'Platformada 1000 ta faol o\'quvchi milodini nishonladik!','icon'=>'🏆','color'=>'amber'],
                ];
            @endphp

            @foreach($timeline as $item)
            <div class="observe-animate observe-delay-{{ ($loop->index % 3) + 1 }} relative flex gap-6 pb-10">
                <!-- Timeline line -->
                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-{{ $item['color'] }}-500 to-{{ $item['color'] }}-700 rounded-2xl flex items-center justify-center text-xl text-white shadow-glow flex-shrink-0 z-10">{{ $item['icon'] }}</div>
                    @if(!$loop->last)
                    <div class="w-0.5 flex-1 bg-gradient-to-b from-{{ $item['color'] }}-300 to-slate-200 dark:from-{{ $item['color'] }}-700 dark:to-slate-700 mt-2"></div>
                    @endif
                </div>

                <!-- Content -->
                <div class="flex-1 pb-2">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-xs font-bold text-{{ $item['color'] }}-600 dark:text-{{ $item['color'] }}-400 bg-{{ $item['color'] }}-50 dark:bg-{{ $item['color'] }}-950 px-3 py-1 rounded-full">{{ $item['month'] }} {{ $item['year'] }}</span>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white mb-1">{{ $item['title'] }}</h3>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed">{{ $item['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ========== TEAM SECTION ========== -->
<section class="py-20 bg-white dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 observe-animate">
            <span class="inline-block px-4 py-1.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 text-sm font-bold rounded-full mb-4 tracking-wide uppercase">Jamoamiz</span>
            <h2 class="text-4xl lg:text-5xl font-black text-slate-900 dark:text-white leading-tight">
                Kitobxon ortida <span class="gradient-text">kimlar bor</span>
            </h2>
            <p class="text-slate-500 dark:text-slate-400 mt-4 text-lg max-w-xl mx-auto">Ishtiyoqli, tajribali va siz uchun ishlashdan zavqlanadigan jamoa.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @php
                $team = [
                    [
                        'name'=>'Otabek Yusupov',
                        'role'=>'Asoschi & Direktor',
                        'emoji'=>'👨‍💼',
                        'bio'=>'10+ yillik texnologiya va ta\'lim sohasidagi tajriba. Kitob sevuvchi, startup tafakkurli.',
                        'gradient'=>'from-indigo-500 to-violet-600',
                        'skills'=>['Mahsulot strategiyasi','Liderlik','Biznes rivojlanish'],
                        'social'=>['linkedin'=>'#','twitter'=>'#'],
                    ],
                    [
                        'name'=>'Malika Rahimova',
                        'role'=>'Bosh Dizayner (UX/UI)',
                        'emoji'=>'👩‍🎨',
                        'bio'=>'Foydalanuvchi tajribasiga oshiq bo\'lgan dizayner. 5 yillik premium mahsulot dizayn tajribasi.',
                        'gradient'=>'from-pink-500 to-rose-600',
                        'skills'=>['UX tadqiqot','UI dizayn','Prototiplash'],
                        'social'=>['linkedin'=>'#','dribbble'=>'#'],
                    ],
                    [
                        'name'=>'Jahongir Toshmatov',
                        'role'=>'Bosh Dasturchi',
                        'emoji'=>'👨‍💻',
                        'bio'=>'Full-stack muhandis. Laravel, Vue.js va cloud arxitekturasida tajribali. Kod yozishdan zavqlanadi.',
                        'gradient'=>'from-cyan-500 to-blue-600',
                        'skills'=>['Laravel','Vue.js','DevOps'],
                        'social'=>['github'=>'#','linkedin'=>'#'],
                    ],
                    [
                        'name'=>'Dilnoza Karimova',
                        'role'=>'Kontent Direktori',
                        'emoji'=>'👩‍💼',
                        'bio'=>'Kitobsevar va matn ustasi. O\'zbek adabiyotini chuqur biladigan, 100+ kitob o\'qigan professional.',
                        'gradient'=>'from-amber-400 to-orange-500',
                        'skills'=>['Kontent strategiyasi','Tarjima','Tahrir'],
                        'social'=>['linkedin'=>'#'],
                    ],
                    [
                        'name'=>'Bekzod Islamov',
                        'role'=>'Marketing Menejeri',
                        'emoji'=>'👨‍🚀',
                        'bio'=>'Digital marketing va o\'sish strategiyasi bo\'yicha mutaxassis. Hamjamiyat qurishni sevadi.',
                        'gradient'=>'from-green-500 to-emerald-600',
                        'skills'=>['Digital marketing','SEO','Hamjamiyat'],
                        'social'=>['linkedin'=>'#','twitter'=>'#'],
                    ],
                    [
                        'name'=>'Zulfiya Mirzayeva',
                        'role'=>'Mijoz Xizmati Boshlig\'i',
                        'emoji'=>'👩‍🎓',
                        'bio'=>'O\'quvchilar baxtli bo\'lishini ta\'minlash uchun kuniga 8 soat ishlaydi. Empatiyanin o\'zi.',
                        'gradient'=>'from-violet-500 to-purple-600',
                        'skills'=>['Mijoz munosabat','O\'quv dasturi','Qo\'llab-quvvatlash'],
                        'social'=>['linkedin'=>'#'],
                    ],
                ];
            @endphp

            @foreach($team as $member)
            <div class="card-hover observe-animate observe-delay-{{ ($loop->index % 3) + 1 }} group relative bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-soft overflow-hidden">
                <!-- Top gradient bar -->
                <div class="h-2 bg-gradient-to-r {{ $member['gradient'] }}"></div>

                <div class="p-8">
                    <!-- Avatar -->
                    <div class="relative mb-6">
                        <div class="w-20 h-20 bg-gradient-to-br {{ $member['gradient'] }} rounded-3xl flex items-center justify-center text-4xl shadow-glow group-hover:scale-110 transition-transform duration-300">
                            {{ $member['emoji'] }}
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 rounded-full border-2 border-white dark:border-slate-800 flex items-center justify-center">
                            <div class="w-2 h-2 bg-white rounded-full"></div>
                        </div>
                    </div>

                    <!-- Info -->
                    <h3 class="text-xl font-black text-slate-900 dark:text-white mb-1">{{ $member['name'] }}</h3>
                    <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 mb-3">{{ $member['role'] }}</p>
                    <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed mb-5">{{ $member['bio'] }}</p>

                    <!-- Skills -->
                    <div class="flex flex-wrap gap-2 mb-5">
                        @foreach($member['skills'] as $skill)
                        <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-lg">{{ $skill }}</span>
                        @endforeach
                    </div>

                    <!-- Social Links -->
                    <div class="flex gap-2">
                        @foreach($member['social'] as $platform => $url)
                        <a href="{{ $url }}" class="w-8 h-8 bg-slate-100 dark:bg-slate-700 hover:bg-indigo-100 dark:hover:bg-indigo-900 rounded-lg flex items-center justify-center transition-colors duration-200 text-sm">
                            @if($platform === 'linkedin') 💼
                            @elseif($platform === 'twitter') 🐦
                            @elseif($platform === 'github') 🐙
                            @elseif($platform === 'dribbble') 🏀
                            @endif
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Join Team CTA -->
        <div class="mt-16 observe-animate">
            <div class="relative p-10 bg-gradient-to-br from-indigo-50 to-violet-50 dark:from-indigo-950/30 dark:to-violet-950/30 rounded-3xl border border-indigo-100 dark:border-indigo-900 text-center overflow-hidden">
                <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle, #4f46e5 1px, transparent 1px); background-size: 25px 25px;"></div>
                <div class="relative">
                    <div class="text-5xl mb-4">🤝</div>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-3">Jamoamizga qo'shiling!</h3>
                    <p class="text-slate-500 dark:text-slate-400 mb-6 max-w-md mx-auto">Biz ishtiyoqli odamlarni doim qidirамиз. Agar kitobni yaxshi ko'rsangiz va ta'limni o'zgartirmoqchi bo'lsangiz — bu siz uchun.</p>
                    <a href="{{ url('contact') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-all duration-300 hover:scale-105 shadow-glow hover:shadow-none">
                        📧 Murojaat yuborish
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== CTA SECTION ========== -->
<section class="py-20 bg-gradient-to-br from-indigo-900 via-violet-900 to-slate-900 relative overflow-hidden">
    <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 40px 40px;"></div>
    <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center observe-animate">
        <h2 class="text-4xl lg:text-5xl font-black text-white mb-6">Bizning hikoyamizning bir qismi bo'ling</h2>
        <p class="text-indigo-200 text-lg mb-8 leading-relaxed">Kitobxon faqat platforma emas — bu o'zgarish harakati. Siz ham bu harakatning bir qismisiz.</p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ url('register') }}" class="w-full sm:w-auto px-8 py-4 font-bold text-indigo-900 bg-white rounded-2xl hover:bg-indigo-50 shadow-2xl transition-all duration-300 hover:scale-105 hover:-translate-y-1">🚀 Hoziroq Boshlang</a>
            <a href="{{ url('contact') }}" class="w-full sm:w-auto px-8 py-4 font-bold text-white bg-white/10 backdrop-blur rounded-2xl border border-white/20 hover:bg-white/20 transition-all duration-300 hover:scale-105 hover:-translate-y-1">📧 Bog'laning</a>
        </div>
    </div>
</section>

<!-- ========== FOOTER ========== -->
<footer class="bg-slate-900 dark:bg-slate-950 text-slate-400 border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">
            <div>
                <a href="{{ url('/') }}" class="flex items-center gap-2 mb-4">
                    <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-violet-600 rounded-xl flex items-center justify-center">📚</div>
                    <span class="text-xl font-black text-white">Kitob<span class="text-indigo-400">xon</span></span>
                </a>
                <p class="text-slate-500 text-sm leading-relaxed">O'zbek tilidagi eng yaxshi onlayn kitob o'qish platformasi.</p>
            </div>
            <div>
                <h4 class="font-bold text-white mb-4">Platforma</h4>
                <ul class="space-y-2.5">
                    @foreach([['Kitoblar', url('books')],['Haqimizda', url('about')],['FAQ', url('faq')],['Aloqa', url('contact')]] as $link)
                    <li><a href="{{ $link[1] }}" class="text-slate-500 hover:text-indigo-400 transition-colors text-sm">{{ $link[0] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-white mb-4">Huquqiy</h4>
                <ul class="space-y-2.5">
                    @foreach([['Maxfiylik', url('privacy')],['Shartlar', url('terms')]] as $link)
                    <li><a href="{{ $link[1] }}" class="text-slate-500 hover:text-indigo-400 transition-colors text-sm">{{ $link[0] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-white mb-4">Bog'laning</h4>
                <ul class="space-y-2">
                    <li class="text-sm text-slate-500">📧 info@kitobxon.uz</li>
                    <li class="text-sm text-slate-500">📞 +998 71 000-00-00</li>
                    <li class="text-sm text-slate-500">📍 Toshkent, O'zbekiston</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-slate-600 text-sm">© {{ date('Y') }} Kitobxon. Barcha huquqlar himoyalangan.</p>
            <div class="flex items-center gap-2 text-slate-600 text-sm">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                Barcha tizimlar ishlaydi
            </div>
        </div>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) entry.target.classList.add('visible');
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        document.querySelectorAll('.observe-animate').forEach(el => observer.observe(el));
    });
</script>

</body>
</html>
