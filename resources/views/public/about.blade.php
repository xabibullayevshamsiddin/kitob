<!DOCTYPE html>
<html lang="uz" class="scroll-smooth dark" x-data="{ darkMode: true, mobileMenu: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kitobxon haqida — Bizning missiya, qadriyatlar va jamoa.">
    <title>Biz haqimizda — Kitobxon</title>

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

    <!-- GSAP & ScrollTrigger for Pro-level Physics Animations -->
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
            background: rgba(17, 23, 38, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.5rem;
            overflow: hidden;
            transition: border-color 0.4s ease, box-shadow 0.4s ease, transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: transform;
        }
        .spotlight-card::before {
            content: '';
            position: absolute;
            top: var(--mouse-y, -1000px);
            left: var(--mouse-x, -1000px);
            width: 360px;
            height: 360px;
            background: radial-gradient(circle, rgba(251, 191, 36, 0.15) 0%, transparent 70%);
            transform: translate(-50%, -50%);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }
        .spotlight-card:hover::before {
            opacity: 1;
        }
        .spotlight-card:hover {
            border-color: rgba(251, 191, 36, 0.4);
            transform: translateY(-5px);
            box-shadow: 0 20px 45px -15px rgba(0, 0, 0, 0.7), 0 0 25px -5px rgba(251, 191, 36, 0.18);
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

    <!-- ── Header ── -->
    <header id="site-header" class="sticky top-0 z-50 w-full backdrop-blur-xl bg-ink-950/80 border-b border-white/[0.07]">
        <div class="max-w-7xl mx-auto px-6 h-[72px] flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-ink-950 font-black text-xl shadow-lg shadow-amber-500/20 group-hover:scale-105 group-hover:rotate-3 transition-all duration-300">
                    📖
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-white text-base tracking-tight group-hover:text-amber-400 transition-colors">Kitobxon</span>
                    <span class="font-mono text-[10px] text-slate-400 uppercase tracking-widest">Platforma</span>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-300">
                <a href="{{ route('home') }}" class="hover:text-amber-400 transition-colors">Bosh sahifa</a>
                <a href="{{ route('books.public') }}" class="hover:text-amber-400 transition-colors">Kitoblar</a>
                <a href="{{ route('about') }}" class="text-amber-400 font-semibold">Biz haqimizda</a>
                <a href="{{ route('faq') }}" class="hover:text-amber-400 transition-colors">FAQ</a>
                <a href="{{ route('contact') }}" class="hover:text-amber-400 transition-colors">Aloqa</a>
            </nav>

            <div class="hidden sm:flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-amber-500 text-ink-950 font-bold text-xs uppercase tracking-wider hover:bg-amber-400 active:scale-95 transition-all shadow-md shadow-amber-500/20">
                        Boshqaruv paneli →
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-slate-300 hover:text-white transition-colors">Kirish</a>
                    <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-xl bg-white text-ink-950 font-bold text-xs uppercase tracking-wider hover:bg-slate-200 active:scale-95 transition-all shadow-md">Ro'yxatdan o'tish</a>
                @endauth
            </div>

            <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-lg text-slate-400 hover:text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenu" x-cloak @click.away="mobileMenu = false" class="md:hidden border-b border-white/10 bg-ink-900/95 px-6 py-4 space-y-3">
            <a href="{{ route('home') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Bosh sahifa</a>
            <a href="{{ route('books.public') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Kitoblar</a>
            <a href="{{ route('about') }}" class="block text-sm py-2 text-amber-400 font-semibold">Biz haqimizda</a>
            <a href="{{ route('faq') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">FAQ</a>
            <a href="{{ route('contact') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Aloqa</a>
        </div>
    </header>

    <!-- ── Hero Section (Chiqib keluvchi Editorial Missiya) ── -->
    <section class="py-20 md:py-28 noise-bg">
        <div class="max-w-5xl mx-auto px-6 text-center space-y-6">
            <div class="about-hero-item inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-400/10 border border-amber-400/25 text-amber-400 font-mono text-xs tracking-wider shadow-sm">
                <span>✦ BIZNING MISSIYAMIZ</span>
            </div>
            
            <h1 class="about-hero-item text-4xl sm:text-6xl lg:text-7xl font-extrabold text-white tracking-tight leading-[1.08]">
                Har bir inson uchun <br class="hidden sm:block">
                <span class="text-amber-400 italic font-serif">intellektual o'sish</span> madaniyatini yaratish.
            </h1>
            
            <p class="about-hero-item text-base sm:text-xl text-slate-300 max-w-2xl mx-auto leading-relaxed">
                Kitobxon — shunchaki kitob o'qish ilovasi emas, balki qisqa vaqt ichida chuqur bilim olish, kunlik odat shakllantirish va fikrdoshlar bilan uchrashish maskani.
            </p>

            <div class="about-hero-item pt-4 flex flex-wrap justify-center gap-4">
                <a href="{{ route('register') }}" class="px-7 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider transition-all duration-200 active:scale-95 shadow-lg shadow-amber-500/25">
                    Hamjamiyatga qo'shilish →
                </a>
                <a href="{{ route('books.public') }}" class="px-6 py-3.5 rounded-xl bg-ink-800/80 hover:bg-ink-700 text-slate-200 font-semibold text-xs border border-white/10 transition-all duration-200 active:scale-95">
                    Kitoblar ro'yxati
                </a>
            </div>
        </div>
    </section>

    <!-- ── Key Stats Row (ScrollTrigger Rolling Counters) ── -->
    <section class="py-14 border-y border-white/[0.08] bg-ink-900/50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                
                <div class="about-stat-card p-6 sm:p-8 rounded-3xl bg-ink-950/70 border border-white/10 hover:border-amber-400/30 transition-all spotlight-card">
                    <p class="text-4xl sm:text-5xl font-black text-amber-400 font-mono tracking-tight">
                        <span class="counter-element" data-target="52">0</span>+
                    </p>
                    <p class="text-xs text-slate-400 mt-2 uppercase tracking-wider font-mono">Yillik sara asarlar</p>
                </div>

                <div class="about-stat-card p-6 sm:p-8 rounded-3xl bg-ink-950/70 border border-white/10 hover:border-amber-400/30 transition-all spotlight-card">
                    <p class="text-4xl sm:text-5xl font-black text-white font-mono tracking-tight">
                        <span class="counter-element" data-target="1200">0</span>+
                    </p>
                    <p class="text-xs text-slate-400 mt-2 uppercase tracking-wider font-mono">Faol kitobxonlar</p>
                </div>

                <div class="about-stat-card p-6 sm:p-8 rounded-3xl bg-ink-950/70 border border-white/10 hover:border-amber-400/30 transition-all spotlight-card">
                    <p class="text-4xl sm:text-5xl font-black text-amber-400 font-mono tracking-tight">
                        <span class="counter-element" data-target="3">0</span> xil
                    </p>
                    <p class="text-xs text-slate-400 mt-2 uppercase tracking-wider font-mono">Matn, Audio, Video</p>
                </div>

                <div class="about-stat-card p-6 sm:p-8 rounded-3xl bg-ink-950/70 border border-white/10 hover:border-amber-400/30 transition-all spotlight-card">
                    <p class="text-4xl sm:text-5xl font-black text-white font-mono tracking-tight">
                        <span class="counter-element" data-target="98">0</span>%
                    </p>
                    <p class="text-xs text-slate-400 mt-2 uppercase tracking-wider font-mono">Odat shakllanishi</p>
                </div>

            </div>
        </div>
    </section>

    <!-- ── Core Values (ScrollTrigger Staggered Bento) ── -->
    <section class="py-24 md:py-32">
        <div class="max-w-7xl mx-auto px-6 space-y-12">
            <div class="values-header space-y-2">
                <span class="text-xs font-mono text-amber-400 uppercase tracking-widest block">✦ ASOSIY USTUNLAR</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">Bizning Qadriyatlarimiz</h2>
                <p class="text-sm text-slate-400">Har bir qadamimiz va qarorimiz ortida turgan tamoyillar</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="value-card spotlight-card rounded-3xl p-8 sm:p-10 space-y-5">
                    <div class="w-14 h-14 rounded-2xl bg-amber-400/10 border border-amber-400/25 text-amber-400 flex items-center justify-center text-3xl">
                        💎
                    </div>
                    <h3 class="text-2xl font-bold text-white tracking-tight">Faqat sara asarlar</h3>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        Vaqtingizni behuda sarflamaymiz. Faqat jahon miqyosida o'z isbotini topgan, real amaliy natija va dunyoqarashni kengaytiruvchi asarlar saralab olinadi.
                    </p>
                    <div class="pt-2 text-xs font-mono text-amber-400">✦ Natijadorlik me'yori</div>
                </div>

                <div class="value-card spotlight-card rounded-3xl p-8 sm:p-10 space-y-5">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 border border-indigo-500/25 text-indigo-400 flex items-center justify-center text-3xl">
                        ⚡️
                    </div>
                    <h3 class="text-2xl font-bold text-white tracking-tight">Doimiylik va Streak</h3>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        Haftasiga bir marta 100 bet o'qib charchagandan ko'ra, har kuni 15 daqiqa mutolaa qilish miyada yangi mustahkam neyron aloqalarini barpo etadi.
                    </p>
                    <div class="pt-2 text-xs font-mono text-indigo-400">✦ 1% har kungi o'sish</div>
                </div>

                <div class="value-card spotlight-card rounded-3xl p-8 sm:p-10 space-y-5">
                    <div class="w-14 h-14 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-rose-400 flex items-center justify-center text-3xl">
                        🤝
                    </div>
                    <h3 class="text-2xl font-bold text-white tracking-tight">Kuchli hamjamiyat</h3>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        Yolg'iz o'qishdan ko'ra birgalikda o'rganish 5 barobar samaraliroq. Har hafta ustozlar va minglab fikrdoshlar bilan jonli fikr almashing.
                    </p>
                    <div class="pt-2 text-xs font-mono text-rose-400">✦ Birgalikda yuksalish</div>
                </div>

            </div>
        </div>
    </section>

    <!-- ── Team Section (Interactive Expert Cards) ── -->
    <section class="py-24 border-t border-white/[0.07] bg-ink-900/40">
        <div class="max-w-7xl mx-auto px-6 space-y-12">
            <div class="team-header space-y-2">
                <span class="text-xs font-mono text-amber-400 uppercase tracking-widest block">✦ EKSPERTLAR VA USTOZLAR</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">Platforma Jamoasi</h2>
                <p class="text-sm text-slate-400">Har haftalik tahlil, audio va darslarni tayyorlaydigan mutaxassislar</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <div class="team-card spotlight-card rounded-3xl p-6 flex items-center gap-5">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-ink-950 font-black text-2xl shrink-0 shadow-lg shadow-amber-500/20">
                        XS
                    </div>
                    <div>
                        <h4 class="font-bold text-white text-base">Xabibullayev Shamsiddin</h4>
                        <p class="text-xs text-amber-400 font-mono mt-0.5">Bosh Arxitektor & Muallif</p>
                        <p class="text-xs text-slate-400 mt-1">Platforma asoschisi va rahbar dasturchi</p>
                    </div>
                </div>

                <div class="team-card spotlight-card rounded-3xl p-6 flex items-center gap-5">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-700 flex items-center justify-center text-white font-bold text-2xl shrink-0 shadow-lg shadow-indigo-600/20">
                        DK
                    </div>
                    <div>
                        <h4 class="font-bold text-white text-base">Dilshod Karimov</h4>
                        <p class="text-xs text-indigo-300 font-mono mt-0.5">Adabiy Muharrir</p>
                        <p class="text-xs text-slate-400 mt-1">Kitoblar tahlilchisi va tarjimon</p>
                    </div>
                </div>

                <div class="team-card spotlight-card rounded-3xl p-6 flex items-center gap-5">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-700 flex items-center justify-center text-white font-bold text-2xl shrink-0 shadow-lg shadow-emerald-600/20">
                        NR
                    </div>
                    <div>
                        <h4 class="font-bold text-white text-base">Nodira Rahimova</h4>
                        <p class="text-xs text-emerald-300 font-mono mt-0.5">Audio & Diktant koordinatori</p>
                        <p class="text-xs text-slate-400 mt-1">Professional audio kitoblar ijrochisi</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ── Footer ── -->
    <footer class="border-t border-white/[0.07] bg-ink-950 py-10 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <span class="font-bold text-white text-sm">Kitobxon</span>
                <span>•</span>
                <span>© {{ date('Y') }} Barcha huquqlar himoyalangan</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}" class="hover:text-slate-300 transition-colors">Bosh sahifa</a>
                <a href="{{ route('books.public') }}" class="hover:text-slate-300 transition-colors">Kitoblar</a>
                <a href="{{ route('faq') }}" class="hover:text-slate-300 transition-colors">FAQ</a>
                <a href="{{ route('contact') }}" class="hover:text-slate-300 transition-colors">Aloqa</a>
            </div>
        </div>
    </footer>

    <!-- ── Advanced Motion & Physics Script ── -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            gsap.registerPlugin(ScrollTrigger);

            // 1. Header Entrance
            gsap.fromTo('#site-header',
                { y: -30, opacity: 0 },
                { y: 0, opacity: 1, duration: 0.8, ease: "power3.out" }
            );

            // 2. Hero Staggered Entrance Reveal
            gsap.fromTo('.about-hero-item', 
                { opacity: 0, y: 40, filter: 'blur(8px)', scale: 0.96 },
                { 
                    opacity: 1, 
                    y: 0, 
                    filter: 'blur(0px)',
                    scale: 1,
                    duration: 0.9, 
                    stagger: 0.12, 
                    ease: "power4.out",
                    clearProps: "transform,scale,filter"
                }
            );

            // 3. Stats Cards Stagger Reveal
            gsap.fromTo('.about-stat-card',
                { opacity: 0, y: 35, scale: 0.94 },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.8,
                    stagger: 0.12,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: '.about-stat-card',
                        start: "top 85%",
                    },
                    clearProps: "transform,scale"
                }
            );

            // 4. Values Section Reveal
            gsap.fromTo('.values-header',
                { opacity: 0, y: 30 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.8,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: '.values-header',
                        start: "top 85%",
                    }
                }
            );

            gsap.fromTo('.value-card',
                { opacity: 0, y: 50, scale: 0.95 },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.85,
                    stagger: 0.15,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: '.value-card',
                        start: "top 80%",
                    },
                    clearProps: "transform,scale"
                }
            );

            // 5. Team Cards Stagger Reveal
            gsap.fromTo('.team-header',
                { opacity: 0, y: 30 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.8,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: '.team-header',
                        start: "top 85%",
                    }
                }
            );

            gsap.fromTo('.team-card',
                { opacity: 0, y: 40, scale: 0.95 },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.8,
                    stagger: 0.15,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: '.team-card',
                        start: "top 85%",
                    },
                    clearProps: "transform,scale"
                }
            );

            // 6. Dynamic Rolling Number Counters
            document.querySelectorAll('.counter-element').forEach(el => {
                const target = parseInt(el.getAttribute('data-target') || 0, 10);
                ScrollTrigger.create({
                    trigger: el,
                    start: "top 90%",
                    once: true,
                    onEnter: () => {
                        const obj = { count: 0 };
                        gsap.to(obj, {
                            count: target,
                            duration: 1.8,
                            ease: "power2.out",
                            onUpdate: () => {
                                el.textContent = Math.floor(obj.count).toLocaleString('en-US');
                            }
                        });
                    }
                });
            });

            // 7. Dynamic Spotlight Cursor Glow
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
