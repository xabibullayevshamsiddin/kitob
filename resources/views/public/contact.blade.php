<!DOCTYPE html>
<html lang="uz" x-data="{ darkMode: false, mobileMenu: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kitobxon bilan bog'laning. Savollar, takliflar va hamkorlik uchun murojaat qiling.">
    <title>Aloqa — Kitobxon 📚</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                        serif: ['Merriweather', 'ui-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out forwards',
                        'slide-up': 'slideUp 0.7s ease-out forwards',
                        'float': 'float 3s ease-in-out infinite',
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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
        .nav-link { position: relative; }
        .nav-link::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 0; height: 2px; background: linear-gradient(90deg, #4f46e5, #f59e0b); transition: width 0.3s ease; }
        .nav-link:hover::after { width: 100%; }
        .observe-animate { opacity: 0; transform: translateY(30px); transition: all 0.7s ease-out; }
        .observe-animate.visible { opacity: 1; transform: translateY(0); }
        .observe-delay-1 { transition-delay: 0.1s; }
        .observe-delay-2 { transition-delay: 0.2s; }
        .observe-delay-3 { transition-delay: 0.3s; }
        .input-field {
            width: 100%;
            padding: 0.875rem 1.25rem;
            background: rgb(248 250 252);
            border: 2px solid rgb(226 232 240);
            border-radius: 0.875rem;
            font-size: 0.9375rem;
            color: rgb(15 23 42);
            transition: all 0.2s ease;
            outline: none;
        }
        .dark .input-field {
            background: rgb(30 41 59);
            border-color: rgb(51 65 85);
            color: rgb(226 232 240);
        }
        .input-field:focus {
            border-color: rgb(99 102 241);
            box-shadow: 0 0 0 4px rgba(99,102,241,0.1);
        }
        .dark .input-field:focus {
            border-color: rgb(99 102 241);
        }
        .input-field::placeholder { color: rgb(148 163 184); }
    </style>
</head>
<body class="font-sans bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 transition-colors duration-300 overflow-x-hidden">

<!-- ========== HEADER ========== -->
<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 20">
    <div :class="scrolled ? 'glass shadow-soft' : 'bg-transparent'" class="transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-20">
                <a href="/" class="flex items-center gap-2 group">
                    <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-violet-600 rounded-xl flex items-center justify-center shadow-glow group-hover:scale-110 transition-transform duration-300">📚</div>
                    <span class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Kitob<span class="text-indigo-600">xon</span></span>
                </a>
                <nav class="hidden lg:flex items-center gap-8">
                    <a href="/" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">Bosh sahifa</a>
                    <a href="/books" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">Kitoblar</a>
                    <a href="/about" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">Haqimizda</a>
                    <a href="/contact" class="nav-link text-indigo-600 dark:text-indigo-400 font-semibold transition-colors duration-200">Aloqa</a>
                    <a href="/faq" class="nav-link text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium transition-colors duration-200">FAQ</a>
                </nav>
                <div class="hidden lg:flex items-center gap-3">
                    <button @click="darkMode = !darkMode" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors duration-200">
                        <span x-show="!darkMode">🌙</span><span x-show="darkMode">☀️</span>
                    </button>
                    <a href="/login" class="px-4 py-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 transition-all duration-200">Kirish</a>
                    <a href="/register" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 rounded-xl hover:from-indigo-700 hover:to-violet-700 shadow-glow hover:shadow-none transition-all duration-300 hover:scale-105">Ro'yxatdan o'tish</a>
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
                <a href="/" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">Bosh sahifa</a>
                <a href="/books" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">Kitoblar</a>
                <a href="/about" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">Haqimizda</a>
                <a href="/contact" class="px-4 py-3 rounded-xl bg-indigo-50 dark:bg-indigo-950 font-semibold text-indigo-600 dark:text-indigo-400 transition-colors">Aloqa</a>
                <a href="/faq" class="px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 font-medium text-slate-700 dark:text-slate-300 transition-colors">FAQ</a>
                <div class="flex gap-3 pt-2 border-t border-slate-200 dark:border-slate-700">
                    <a href="/login" class="flex-1 py-2.5 text-center font-semibold text-indigo-600 border border-indigo-200 dark:border-indigo-800 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-950 transition-colors">Kirish</a>
                    <a href="/register" class="flex-1 py-2.5 text-center font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-600 rounded-xl transition-all">Ro'yxatdan o'tish</a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- ========== HERO ========== -->
<section class="relative pt-32 pb-16 overflow-hidden">
    <div class="absolute inset-0" style="background: radial-gradient(ellipse 80% 80% at 50% -10%, rgba(79,70,229,0.15) 0%, transparent 70%);"></div>
    <div class="absolute top-32 right-[10%] text-4xl opacity-15 animate-float">💬</div>
    <div class="absolute bottom-10 left-[15%] text-3xl opacity-10 animate-float" style="animation-delay:1.5s;">📧</div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-50 dark:bg-indigo-950 border border-indigo-100 dark:border-indigo-900 text-indigo-700 dark:text-indigo-300 text-sm font-semibold mb-6 animate-fade-in">
            💬 Biz bilan bog'laning
        </div>
        <h1 class="text-5xl lg:text-6xl font-black text-slate-900 dark:text-white leading-tight mb-6 animate-slide-up">
            Savolingiz bormi?<br><span class="gradient-text">Yozing!</span>
        </h1>
        <p class="text-xl text-slate-500 dark:text-slate-400 leading-relaxed max-w-2xl mx-auto animate-slide-up" style="animation-delay:0.2s;">
            Har qanday savol, taklif yoki hamkorlik bo'yicha murojaat uchun tayyor turibmiz. 24 soat ichida javob beramiz.
        </p>
    </div>
</section>

<!-- ========== SUCCESS MESSAGE ========== -->
@if(session('success'))
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 mb-6" x-data="{ show: true }">
    <div x-show="show" x-transition
         class="flex items-start gap-4 p-5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-2xl shadow-soft">
        <div class="w-10 h-10 bg-green-100 dark:bg-green-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="font-bold text-green-800 dark:text-green-300">Xabaringiz yuborildi!</h3>
            <p class="text-green-700 dark:text-green-400 text-sm mt-0.5">{{ session('success') }} Tez orada siz bilan bog'lanamiz.</p>
        </div>
        <button @click="show = false" class="text-green-500 hover:text-green-700 dark:hover:text-green-300 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>
@endif

<!-- ========== MAIN CONTENT ========== -->
<section class="pb-24 lg:pb-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

            <!-- Contact Info Cards — LEFT -->
            <div class="space-y-6 order-2 lg:order-1">

                <!-- Response time notice -->
                <div class="observe-animate p-5 bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100 dark:border-indigo-900 rounded-2xl flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-xl flex items-center justify-center text-2xl flex-shrink-0">⚡</div>
                    <div>
                        <p class="font-bold text-indigo-800 dark:text-indigo-300 text-sm">Tez javob</p>
                        <p class="text-indigo-600 dark:text-indigo-400 text-xs mt-0.5">Odatda 2–4 soat ichida javob beramiz</p>
                    </div>
                </div>

                <!-- Contact Items -->
                @php
                    $contacts = [
                        ['icon'=>'📧','title'=>'Email','detail'=>'info@kitobxon.uz','sub'=>'Ish kunlari 09:00–18:00','color'=>'indigo','href'=>'mailto:info@kitobxon.uz'],
                        ['icon'=>'📞','title'=>'Telefon','detail'=>'+998 71 000-00-00','sub'=>'Dushanba–Juma, 09:00–18:00','color'=>'green','href'=>'tel:+998710000000'],
                        ['icon'=>'📍','title'=>'Manzil','detail'=>'Toshkent, O\'zbekiston','sub'=>'Amir Temur ko\'chasi, 108-uy','color'=>'amber','href'=>'#'],
                        ['icon'=>'💬','title'=>'Telegram','detail'=>'@kitobxon_uz','sub'=>'Eng tez javob shu yerda','color'=>'cyan','href'=>'https://t.me/kitobxon_uz'],
                    ];
                @endphp

                @foreach($contacts as $c)
                <div class="observe-animate observe-delay-{{ $loop->iteration }} group">
                    <a href="{{ $c['href'] }}"
                       class="flex items-center gap-4 p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-soft hover:border-indigo-200 dark:hover:border-indigo-700 hover:shadow-glow transition-all duration-300">
                        <div class="w-12 h-12 bg-{{ $c['color'] }}-50 dark:bg-{{ $c['color'] }}-950/50 rounded-xl flex items-center justify-center text-2xl flex-shrink-0 group-hover:scale-110 transition-transform duration-300">{{ $c['icon'] }}</div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ $c['title'] }}</p>
                            <p class="font-bold text-slate-900 dark:text-white truncate">{{ $c['detail'] }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $c['sub'] }}</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 ml-auto flex-shrink-0 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                @endforeach

                <!-- Social Links -->
                <div class="observe-animate observe-delay-4 p-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-soft">
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Ijtimoiy tarmoqlar</p>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach([
                            ['icon'=>'🐦','name'=>'Twitter','href'=>'#','color'=>'sky'],
                            ['icon'=>'📘','name'=>'Facebook','href'=>'#','color'=>'blue'],
                            ['icon'=>'📸','name'=>'Instagram','href'=>'#','color'=>'pink'],
                            ['icon'=>'▶️','name'=>'YouTube','href'=>'#','color'=>'red'],
                        ] as $social)
                        <a href="{{ $social['href'] }}"
                           class="flex items-center gap-2 px-3 py-2.5 bg-slate-50 dark:bg-slate-700 hover:bg-{{ $social['color'] }}-50 dark:hover:bg-{{ $social['color'] }}-950/30 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-{{ $social['color'] }}-200 dark:hover:border-{{ $social['color'] }}-800 transition-all duration-200 group">
                            <span class="text-lg">{{ $social['icon'] }}</span>
                            <span class="text-sm font-medium text-slate-600 dark:text-slate-300 group-hover:text-{{ $social['color'] }}-700 dark:group-hover:text-{{ $social['color'] }}-400 transition-colors">{{ $social['name'] }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                <!-- Office Hours -->
                <div class="observe-animate p-5 bg-gradient-to-br from-slate-50 to-indigo-50 dark:from-slate-800 dark:to-indigo-950/20 rounded-2xl border border-slate-100 dark:border-slate-700">
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                        🕐 Ish vaqtlari
                    </p>
                    <div class="space-y-2 text-sm">
                        @foreach([
                            ['Dushanba — Juma','09:00 — 18:00',true],
                            ['Shanba','10:00 — 14:00',true],
                            ['Yakshanba','Dam olish kuni',false],
                        ] as $hour)
                        <div class="flex justify-between">
                            <span class="text-slate-600 dark:text-slate-400">{{ $hour[0] }}</span>
                            <span class="{{ $hour[2] ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' }} font-semibold">{{ $hour[1] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Contact Form — RIGHT (spans 2 columns) -->
            <div class="lg:col-span-2 order-1 lg:order-2 observe-animate">
                <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-soft p-8 lg:p-10">

                    <div class="mb-8">
                        <h2 class="text-3xl font-black text-slate-900 dark:text-white mb-2">Xabar yuboring</h2>
                        <p class="text-slate-500 dark:text-slate-400">Barcha maydonlarni to'ldiring va biz siz bilan tez orada bog'lanamiz.</p>
                    </div>

                    <form method="POST" action="/contact" x-data="contactForm()" @submit.prevent="submitForm">
                        @csrf

                        <!-- Name & Email Row -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    Ismingiz <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text"
                                           name="name"
                                           x-model="form.name"
                                           placeholder="Ismingiz"
                                           required
                                           class="input-field"
                                           :class="errors.name ? 'border-red-400 dark:border-red-500' : ''">
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">👤</div>
                                </div>
                                <p x-show="errors.name" x-text="errors.name" class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                </p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="email"
                                           name="email"
                                           x-model="form.email"
                                           placeholder="email@misol.com"
                                           required
                                           class="input-field"
                                           :class="errors.email ? 'border-red-400 dark:border-red-500' : ''">
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">📧</div>
                                </div>
                                <p x-show="errors.email" x-text="errors.email" class="text-red-500 text-xs mt-1.5"></p>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Telefon raqam <span class="text-slate-400 font-normal">(ixtiyoriy)</span>
                            </label>
                            <div class="relative">
                                <input type="tel"
                                       name="phone"
                                       x-model="form.phone"
                                       placeholder="+998 90 000-00-00"
                                       class="input-field">
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">📞</div>
                            </div>
                        </div>

                        <!-- Subject -->
                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Mavzu <span class="text-red-500">*</span>
                            </label>
                            <select name="subject"
                                    x-model="form.subject"
                                    required
                                    class="input-field appearance-none cursor-pointer">
                                <option value="" disabled selected>Mavzuni tanlang...</option>
                                <option value="general">Umumiy savol</option>
                                <option value="technical">Texnik muammo</option>
                                <option value="partnership">Hamkorlik taklifi</option>
                                <option value="content">Kontent bo'yicha</option>
                                <option value="billing">To'lov masalasi</option>
                                <option value="feedback">Taklif va fikrlar</option>
                                <option value="other">Boshqa</option>
                            </select>
                        </div>

                        <!-- Message -->
                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Xabar <span class="text-red-500">*</span>
                            </label>
                            <textarea name="message"
                                      x-model="form.message"
                                      rows="6"
                                      placeholder="Xabaringizni bu yerga yozing. Imkon qadar batafsil yozing — bu tezroq yordam berishga imkon beradi."
                                      required
                                      class="input-field resize-none"
                                      :class="errors.message ? 'border-red-400 dark:border-red-500' : ''"></textarea>
                            <div class="flex justify-between mt-1.5">
                                <p x-show="errors.message" x-text="errors.message" class="text-red-500 text-xs"></p>
                                <p class="text-xs text-slate-400 ml-auto" x-text="form.message.length + '/1000'"></p>
                            </div>
                        </div>

                        <!-- Privacy Checkbox -->
                        <div class="mb-7">
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <div class="relative mt-0.5">
                                    <input type="checkbox"
                                           name="agree"
                                           x-model="form.agree"
                                           required
                                           class="sr-only peer">
                                    <div class="w-5 h-5 rounded-md border-2 border-slate-300 dark:border-slate-600 peer-checked:bg-indigo-600 peer-checked:border-indigo-600 transition-all duration-200 flex items-center justify-center">
                                        <svg x-show="form.agree" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                                <span class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                                    <a href="/privacy" class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">Maxfiylik siyosati</a> va
                                    <a href="/terms" class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">Foydalanish shartlari</a>
                                    bilan tanishib, rozilik beraman.
                                </span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                                :disabled="loading"
                                class="w-full py-4 px-8 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold text-lg rounded-2xl shadow-glow hover:shadow-none transition-all duration-300 hover:scale-[1.02] hover:-translate-y-0.5 flex items-center justify-center gap-3">
                            <template x-if="!loading">
                                <span class="flex items-center gap-2">
                                    📨 Xabar Yuborish
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </span>
                            </template>
                            <template x-if="loading">
                                <span class="flex items-center gap-2">
                                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Yuborilmoqda...
                                </span>
                            </template>
                        </button>

                        <!-- Laravel Validation Errors -->
                        @if($errors->any())
                        <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl">
                            <p class="font-bold text-red-800 dark:text-red-300 mb-2">Quyidagi xatoliklar aniqlandi:</p>
                            <ul class="space-y-1">
                                @foreach($errors->all() as $error)
                                <li class="text-sm text-red-700 dark:text-red-400 flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full flex-shrink-0"></span>
                                    {{ $error }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </form>
                </div>

                <!-- FAQ Hint -->
                <div class="mt-6 p-5 bg-amber-50 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-900 rounded-2xl flex items-center gap-4">
                    <div class="text-3xl flex-shrink-0">💡</div>
                    <div>
                        <p class="font-bold text-amber-800 dark:text-amber-300 text-sm">Tez-tez so'raladigan savollar</p>
                        <p class="text-amber-700 dark:text-amber-400 text-xs mt-0.5">Yozishdan oldin <a href="/faq" class="underline font-semibold">FAQ sahifamizni</a> ko'ring — javob allaqachon u yerda bo'lishi mumkin!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== MAP SECTION ========== -->
<section class="py-16 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 observe-animate">
        <div class="text-center mb-10">
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">Bizni toping</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-2">Toshkent shahrida joylashganmiz</p>
        </div>
        <div class="h-64 bg-gradient-to-br from-indigo-50 to-violet-50 dark:from-indigo-950/30 dark:to-violet-950/30 rounded-3xl border border-indigo-100 dark:border-indigo-900 flex items-center justify-center overflow-hidden relative">
            <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle, #4f46e5 1px, transparent 1px); background-size: 30px 30px;"></div>
            <div class="text-center relative z-10">
                <div class="text-6xl mb-4 animate-float">📍</div>
                <p class="font-bold text-indigo-700 dark:text-indigo-300 text-lg">Amir Temur ko'chasi, 108</p>
                <p class="text-slate-500 dark:text-slate-400">Toshkent, O'zbekiston</p>
                <a href="https://maps.google.com" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors duration-200">
                    🗺️ Xaritada ko'rish
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ========== FOOTER ========== -->
<footer class="bg-slate-900 dark:bg-slate-950 text-slate-400 border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">
            <div>
                <a href="/" class="flex items-center gap-2 mb-4">
                    <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-violet-600 rounded-xl flex items-center justify-center">📚</div>
                    <span class="text-xl font-black text-white">Kitob<span class="text-indigo-400">xon</span></span>
                </a>
                <p class="text-slate-500 text-sm leading-relaxed">O'zbek tilidagi eng yaxshi onlayn kitob o'qish platformasi.</p>
            </div>
            <div>
                <h4 class="font-bold text-white mb-4">Platforma</h4>
                <ul class="space-y-2.5">
                    @foreach([['Kitoblar','/books'],['Haqimizda','/about'],['FAQ','/faq'],['Aloqa','/contact']] as $link)
                    <li><a href="{{ $link[1] }}" class="text-slate-500 hover:text-indigo-400 transition-colors text-sm">{{ $link[0] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-white mb-4">Huquqiy</h4>
                <ul class="space-y-2.5">
                    @foreach([['Maxfiylik','/privacy'],['Shartlar','/terms'],['Cookie','/cookies']] as $link)
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
            <div class="flex gap-5">
                <a href="/privacy" class="text-slate-600 hover:text-indigo-400 text-sm transition-colors">Maxfiylik</a>
                <a href="/terms" class="text-slate-600 hover:text-indigo-400 text-sm transition-colors">Shartlar</a>
                <a href="/contact" class="text-slate-600 hover:text-indigo-400 text-sm transition-colors">Aloqa</a>
            </div>
        </div>
    </div>
</footer>

<script>
    function contactForm() {
        return {
            form: { name: '', email: '', phone: '', subject: '', message: '', agree: false },
            errors: {},
            loading: false,

            validate() {
                this.errors = {};
                if (!this.form.name.trim() || this.form.name.trim().length < 2) {
                    this.errors.name = 'Ism kamida 2 ta belgi bo\'lishi kerak.';
                }
                if (!this.form.email.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
                    this.errors.email = 'To\'g\'ri email manzil kiriting.';
                }
                if (!this.form.message.trim() || this.form.message.trim().length < 10) {
                    this.errors.message = 'Xabar kamida 10 ta belgi bo\'lishi kerak.';
                }
                return Object.keys(this.errors).length === 0;
            },

            async submitForm() {
                if (!this.validate()) return;
                if (!this.form.agree) {
                    alert('Iltimos, maxfiylik siyosati va foydalanish shartlariga rozilik bering.');
                    return;
                }
                this.loading = true;
                // Submit the real form
                this.$el.submit();
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('visible'); });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        document.querySelectorAll('.observe-animate').forEach(el => observer.observe(el));
    });
</script>

</body>
</html>
