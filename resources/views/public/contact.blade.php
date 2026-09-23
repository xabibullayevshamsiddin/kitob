<!DOCTYPE html>
<html lang="uz" class="scroll-smooth dark" x-data="{ darkMode: true, mobileMenu: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kitobxon bilan bog'laning. Savollar, takliflar va hamkorlik uchun aloqa formasi.">
    <title>Aloqa — Kitobxon</title>

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

    <div id="smooth-page-wrapper">
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
                <a href="{{ route('about') }}" class="hover:text-amber-400 transition-colors">Biz haqimizda</a>
                <a href="{{ route('faq') }}" class="hover:text-amber-400 transition-colors">FAQ</a>
                <a href="{{ route('contact') }}" class="text-amber-400 font-semibold">Aloqa</a>
            </nav>

            <div class="hidden sm:flex items-center gap-3">
                @auth
                    @if(auth()->user()->hasRole('admin'))
                        <a href="{{ route('admin.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-amber-500 text-ink-950 font-bold text-xs uppercase tracking-wider hover:bg-amber-400 active:scale-95 transition-all shadow-md shadow-amber-500/20">
                            Boshqaruv paneli →
                        </a>
                    @elseif(auth()->user()->hasRole('teacher'))
                        <a href="{{ route('teacher.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-amber-500 text-ink-950 font-bold text-xs uppercase tracking-wider hover:bg-amber-400 active:scale-95 transition-all shadow-md shadow-amber-500/20">
                            O'qituvchi paneli →
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-amber-500 text-ink-950 font-bold text-xs uppercase tracking-wider hover:bg-amber-400 active:scale-95 transition-all shadow-md shadow-amber-500/20">
                            Dashboard →
                        </a>
                    @endif
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
            <a href="{{ route('about') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">Biz haqimizda</a>
            <a href="{{ route('faq') }}" class="block text-sm py-2 text-slate-200 hover:text-amber-400">FAQ</a>
            <a href="{{ route('contact') }}" class="block text-sm py-2 text-amber-400 font-semibold">Aloqa</a>
        </div>
    </header>

    <!-- ── Main Content (Chiqib keluvchi Aloqa Formasi & Kartalar) ── -->
    <main class="py-16 md:py-24 noise-bg">
        <div class="max-w-7xl mx-auto px-6">
            
            <div class="contact-header max-w-2xl mb-12 space-y-3">
                <span class="text-xs font-mono text-amber-400 uppercase tracking-widest block">✦ BIZ BILAN BOG'LANING</span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-tight">
                    Savol yoki taklifingiz bormi? <br>
                    <span class="text-amber-400 italic font-serif">Biz doim aloqadamiz.</span>
                </h1>
                <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
                    Mutolaa jarayoni, kitoblar tanlovi, guruhlar yoki hamkorlik masalalarida xabar qoldiring. Mutaxassislarimiz 24 soat ichida javob berishadi.
                </p>
            </div>

            <!-- Form + Info Split Grid (50/50 Staggered Entrance) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Contact Form (Col 7) -->
                <div class="contact-form-col lg:col-span-7 spotlight-card rounded-3xl p-8 sm:p-10 shadow-card-depth">
                    
                    @if(session('success'))
                        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-3">
                            <span class="text-lg font-bold">✓</span>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    <form action="{{ route('contact.send') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 font-mono mb-2">Ismingiz *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required 
                                       placeholder="Ali Valiyev"
                                       class="w-full px-4 py-3.5 rounded-xl bg-ink-950/80 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition-colors">
                                @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 font-mono mb-2">Elektron pochta *</label>
                                <input type="email" name="email" value="{{ old('email') }}" required 
                                       placeholder="ali@misol.uz"
                                       class="w-full px-4 py-3.5 rounded-xl bg-ink-950/80 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition-colors">
                                @error('email') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 font-mono mb-2">Mavzu *</label>
                            <input type="text" name="subject" value="{{ old('subject') }}" required 
                                   placeholder="Qaysi mavzuda murojaat qilmoqchisiz?"
                                   class="w-full px-4 py-3.5 rounded-xl bg-ink-950/80 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition-colors">
                            @error('subject') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 font-mono mb-2">Xabaringiz *</label>
                            <textarea name="message" rows="5" required 
                                      placeholder="Fikringiz, taklifingiz yoki savolingizni batafsil bayon eting..."
                                      class="w-full px-4 py-3.5 rounded-xl bg-ink-950/80 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition-colors resize-none">{{ old('message') }}</textarea>
                            @error('message') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" 
                                class="w-full py-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider transition-all duration-200 active:scale-95 shadow-lg shadow-amber-500/25 hover:shadow-glow-amber flex items-center justify-center gap-2 group">
                            <span>Xabarni yuborish</span>
                            <svg class="w-4 h-4 group-hover:translate-x-1.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>
                </div>

                <!-- Info Cards (Col 5) -->
                <div class="lg:col-span-5 space-y-6">
                    
                    <div class="contact-info-card spotlight-card rounded-3xl p-6 sm:p-7 flex items-start gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-amber-400/10 border border-amber-400/25 text-amber-400 flex items-center justify-center text-2xl shrink-0">
                            💬
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Telegram orqali tezkor aloqa</h3>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">Savollaringizga bot va qo'llab-quvvatlash guruhi orqali bir necha daqiqa ichida javob oling.</p>
                            <a href="https://t.me/" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-amber-400 font-semibold mt-3 hover:text-amber-300 transition-colors">
                                <span>@kitobxon_support</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>

                    <div class="contact-info-card spotlight-card rounded-3xl p-6 sm:p-7 flex items-start gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 border border-indigo-500/25 text-indigo-400 flex items-center justify-center text-2xl shrink-0">
                            ✉️
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Elektron pochta</h3>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">Rasmiy takliflar, media murojaatlari va hamkorlik loyihalari uchun.</p>
                            <p class="text-xs font-mono text-slate-200 mt-2 font-semibold">info@kitobxon.uz</p>
                        </div>
                    </div>

                    <div class="contact-info-card spotlight-card rounded-3xl p-6 sm:p-7 flex items-start gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 flex items-center justify-center text-2xl shrink-0">
                            📍
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Bosh ofis</h3>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">O'zbekiston, Toshkent shahri, IT Park hududi.</p>
                            <p class="text-[11px] text-slate-500 mt-2 font-mono">Dush — Juma: 09:00 — 18:00</p>
                        </div>
                    </div>

                    <!-- Instant Community Help Callout -->
                    <div class="contact-info-card p-6 rounded-3xl bg-gradient-to-br from-amber-500/10 via-ink-900 to-indigo-950/40 border border-amber-400/20 text-xs text-slate-300 flex items-center gap-4">
                        <span class="w-3 h-3 rounded-full bg-emerald-400 animate-ping shrink-0"></span>
                        <p>Qo'llab-quvvatlash xizmati haftaning har kuni 24 soat faol ishlamoqda.</p>
                    </div>

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
                <span>© {{ date('Y') }} Barcha huquqlar himoyalangan</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}" class="hover:text-slate-300 transition-colors">Bosh sahifa</a>
                <a href="{{ route('books.public') }}" class="hover:text-slate-300 transition-colors">Kitoblar</a>
                <a href="{{ route('about') }}" class="hover:text-slate-300 transition-colors">Biz haqimizda</a>
                <a href="{{ route('faq') }}" class="hover:text-slate-300 transition-colors">FAQ</a>
            </div>
        </div>
    </footer>
    </div>

    <!-- ── Advanced Motion & Physics Script ── -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            gsap.registerPlugin(ScrollTrigger);

            // 1. Header Entrance
            gsap.fromTo('#site-header',
                { y: -30, opacity: 0 },
                { y: 0, opacity: 1, duration: 0.8, ease: "power3.out" }
            );

            // 2. Header Title & Subtitle Entrance
            gsap.fromTo('.contact-header',
                { opacity: 0, y: 35, filter: 'blur(8px)' },
                { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.9, ease: "power4.out" }
            );

            // 3. Form Container Stagger (Slide-in from left)
            gsap.fromTo('.contact-form-col',
                { opacity: 0, x: -40, scale: 0.97 },
                { opacity: 1, x: 0, scale: 1, duration: 0.85, ease: "power3.out", clearProps: "transform,scale" }
            );

            // 4. Info Cards Stagger (Slide-in from right)
            gsap.fromTo('.contact-info-card',
                { opacity: 0, x: 40, scale: 0.97 },
                { opacity: 1, x: 0, scale: 1, duration: 0.8, stagger: 0.12, ease: "power3.out", clearProps: "transform,scale" }
            );

            // 5. Dynamic Spotlight Cursor Glow
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
