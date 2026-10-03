<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark" x-data="{ mobileMenu: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ __('site.contact.meta') }}">
    <title>{{ __('site.contact.title') }} — Kitobxon</title>

    @include('partials.design-system')


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
    <x-nav.main-header />

    <!-- ── Main Content (Chiqib keluvchi Aloqa Formasi & Kartalar) ── -->
    <main class="py-16 md:py-24 noise-bg">
        <div class="max-w-7xl mx-auto px-6">
            
            <div class="contact-header max-w-2xl mb-12 space-y-3">
                <span class="text-xs font-mono text-amber-400 uppercase tracking-widest block">{{ __('site.contact.badge') }}</span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-tight">
                    {{ __('site.contact.hero_1') }} <br>
                    <span class="text-amber-400 italic font-serif">{{ __('site.contact.hero_b') }}</span>
                </h1>
                <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
                    {{ __('site.contact.hero_sub') }}
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

                    @php
                        // "Shikoyat rejimi" faqat chatdagi 🚩 tugmasidan kelgan haqiqiy ma'lumotlar
                        // (reported_user_id) bilan, bo'sh footer havolasi bilan emas, faollashadi.
                        $isReport = request('report') == 1 && request('reported_user_id');
                        $defaultSubject = $isReport ? "🚩 Qoidabuzarlik / Haqorat bo'yicha shikoyat (#" . request('reported_user_id') . ")" : old('subject');
                        
                        $defaultMessage = old('message');
                        if ($isReport && empty($defaultMessage)) {
                            $defaultMessage = "[QOIDABUZARLIK BO'YICHA SHIKOYAT / REPORT]\n"
                                . "👤 Qoidabuzar foydalanuvchi: " . request('reported_name', 'Noma\'lum') . " (@" . request('reported_username', '') . ", ID: #" . request('reported_user_id', '') . ")\n"
                                . "📍 Bo'lim: " . request('source', 'Chat') . "\n"
                                . "🔗 Sahifa havolasi: " . request('url', '') . "\n"
                                . "⏰ Xabar yozilgan vaqt: " . request('time', '') . "\n"
                                . "💬 Qoidabuzar yozgan xabar matni:\n\"" . request('message_text', '') . "\"\n\n"
                                . "⚠️ Shikoyat sababi: Chatda haqoratli/nojo'ya so'zlar ishlatildi. Iltimos, ushbu foydalanuvchiga nisbatan chora ko'rishingizni (ban berishingizni) so'rayman!";
                        }
                    @endphp

                    @if($isReport)
                        <div class="p-4 rounded-panel bg-amber-500/10 border border-amber-500/30 text-amber-300 space-y-1.5 animate-fade-in">
                            <div class="flex items-center gap-2 font-bold text-xs font-mono text-amber-400">
                                <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                                <span>Qoidabuzarlik bo'yicha tezkor shikoyat tayyorlandi!</span>
                            </div>
                            <p class="text-xs text-mist font-sans leading-relaxed">
                                Qoidabuzar foydalanuvchi ma'lumotlari va u yozgan nojo'ya xabar shaklga avtomatik to'ldirildi. Ma'lumotlarni ko'rib chiqing va ma'muriyatga jo'natish uchun pastdagi tugmani bosing.
                            </p>
                        </div>
                    @endif

                    <form action="{{ route('contact.send') }}" method="POST" class="space-y-6">
                        @csrf

                        <input type="hidden" name="is_report" value="{{ $isReport ? '1' : '0' }}">
                        <input type="hidden" name="reported_user_id" value="{{ request('reported_user_id') }}">
                        <input type="hidden" name="source" value="{{ request('source') }}">
                        <input type="hidden" name="link" value="{{ request('url') }}">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 font-mono mb-2">{{ __('site.contact.form_name') }}</label>
                                <input type="text" name="name" value="{{ old('name', auth()->user()?->name) }}" required 
                                       placeholder="Ali Valiyev"
                                       class="w-full px-4 py-3.5 rounded-xl bg-ink-950/80 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition-colors">
                                @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 font-mono mb-2">{{ __('site.contact.form_email') }}</label>
                                <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" required 
                                       placeholder="ali@misol.uz"
                                       class="w-full px-4 py-3.5 rounded-xl bg-ink-950/80 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition-colors">
                                @error('email') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 font-mono mb-2">{{ __('site.contact.form_subject') }}</label>
                            <input type="text" name="subject" value="{{ old('subject', $defaultSubject) }}" required 
                                   placeholder="{{ __('site.contact.form_subject_ph') }}"
                                   class="w-full px-4 py-3.5 rounded-xl bg-ink-950/80 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition-colors">
                            @error('subject') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 font-mono mb-2">{{ __('site.contact.form_message') }}</label>
                            <textarea name="message" rows="8" required 
                                      placeholder="{{ __('site.contact.form_message_ph') }}"
                                      class="w-full px-4 py-3.5 rounded-xl bg-ink-950/80 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition-colors resize-none font-sans leading-relaxed">{{ $defaultMessage }}</textarea>
                            @error('message') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" 
                                class="w-full py-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider transition-all duration-200 active:scale-95 shadow-lg shadow-amber-500/25 hover:shadow-glow-amber flex items-center justify-center gap-2 group">
                            <span>{{ __('site.contact.form_send') }}</span>
                            <svg class="w-4 h-4 group-hover:translate-x-1.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>
                </div>

                <!-- Info Cards (Col 5) -->
                <div class="lg:col-span-5 space-y-6">
                    
                    <div class="contact-info-card spotlight-card rounded-panel p-5 sm:p-6 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-btn bg-amber-400/10 border border-amber-400/25 text-amber-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold font-serif text-paper">{{ __('site.contact.tg_title') }}</h3>
                            <p class="text-xs text-mist mt-1 leading-relaxed font-sans">{{ __('site.contact.tg_sub') }}</p>
                            <a href="https://t.me/" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-amber-400 font-mono mt-2.5 hover:text-amber-300 transition-colors">
                                <span>@kitobxon_support</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>

                    <div class="contact-info-card spotlight-card rounded-panel p-5 sm:p-6 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-btn bg-amber-500/10 border border-amber-500/25 text-amber-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold font-serif text-paper">{{ __('site.contact.email_title') }}</h3>
                            <p class="text-xs text-mist mt-1 leading-relaxed font-sans">{{ __('site.contact.email_sub') }}</p>
                            <p class="text-xs font-mono text-paper mt-2 font-medium">info@kitobxon.uz</p>
                        </div>
                    </div>

                    <div class="contact-info-card spotlight-card rounded-panel p-5 sm:p-6 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-btn bg-amber-500/10 border border-amber-500/25 text-amber-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold font-serif text-paper">{{ __('site.contact.office_title') }}</h3>
                            <p class="text-xs text-mist mt-1 leading-relaxed font-sans">{{ __('site.contact.office_sub') }}</p>
                            <p class="text-[11px] text-mist mt-2 font-mono">{{ __('site.contact.office_hours') }}</p>
                        </div>
                    </div>

                    <!-- Instant Community Help Callout -->
                    <div class="contact-info-card p-6 rounded-3xl bg-gradient-to-br from-amber-500/10 via-ink-900 to-indigo-950/40 border border-amber-400/20 text-xs text-slate-300 flex items-center gap-4">
                        <span class="w-3 h-3 rounded-full bg-emerald-400 animate-ping shrink-0"></span>
                        <p>{{ __('site.contact.support_note') }}</p>
                    </div>

                </div>

            </div>

        </div>
    </main>

    <!-- ── Universal Footer ── -->
    <x-nav.main-footer />
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
    
        // ── Fail-safe: GSAP yuklanmasa yoki xato bo'lsa kontent ko'rinadi ──
        const __kitobxonSeen = new WeakMap();
        function __kitobxonVisibilityFailsafe() {
            document.querySelectorAll('.hero-anim-item, .bento-header, .bento-card, .step-card, .cta-box, .legal-content, .about-stat-card, .value-card, .team-card, .contact-form-col, .contact-info-card, .faq-card, .book-card, .error-anim-item').forEach(el => {
                const r = el.getBoundingClientRect();
                if (r.height === 0) return;
                const s = getComputedStyle(el);
                if (parseFloat(s.opacity) >= 0.05) { __kitobxonSeen.delete(el); return; }

                // GSAP umuman yuklanmagan bo'lsa — darhol ochamiz
                if (typeof gsap === 'undefined') {
                    el.style.opacity = '1'; el.style.filter = 'none'; el.style.transform = 'none';
                    return;
                }

                const reached = r.top < window.innerHeight + 100; // ekranda yoki undan yuqorida
                const first = __kitobxonSeen.get(el);
                // 4+ soniya opacity:0 da qotgan bo'lsa — animatsiya ishlamagan: ochamiz
                if (reached || (first && Date.now() - first > 4000)) {
                    el.style.opacity = '1'; el.style.filter = 'none'; el.style.transform = 'none';
                    __kitobxonSeen.delete(el);
                } else if (!first) {
                    __kitobxonSeen.set(el, Date.now());
                }
            });
        }
        // Darhol tekshiruv
        __kitobxonVisibilityFailsafe();
        // Scroll paytida darhol sinxron tekshiruv (taymer throttling ta'sir qilmaydi)
        window.addEventListener('scroll', __kitobxonVisibilityFailsafe, { passive: true });
        // Zaxira interval (PJAX navigatsiyadan keyin ham ishlashi uchun)
        setInterval(__kitobxonVisibilityFailsafe, 2000);
    </script>

    <!-- ── Universal Toast Notification Container ── -->
    <x-toast-container />
</body>
</html>
