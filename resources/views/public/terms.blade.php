<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth" x-data="{ mobileMenu: false, activeTab: 'all' }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ __('site.terms.meta_desc') }}">
    <title>{{ __('site.terms.page_title') }}</title>

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
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>{{ __('site.terms.badge') }}</span>
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-serif font-bold text-paper tracking-tight leading-[1.1] max-w-4xl">
                    {{ __('site.terms.h1_a') }} <br class="hidden sm:inline">
                    <span class="text-gold italic">{{ __('site.terms.h1_b') }}</span>
                </h1>

                <p class="text-sm sm:text-base text-mist max-w-2xl leading-relaxed">
                    {{ __('site.terms.intro') }}
                </p>

                <!-- Info Badges -->
                <div class="pt-4 flex flex-wrap items-center justify-center gap-3 text-xs">
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-btn bg-ink-900 border border-ink-border text-paper-muted">
                        <svg class="w-4 h-4 text-gold shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="text-gold font-bold">{{ __('site.terms.effective') }}</span>
                        <span class="font-mono text-mist">{{ __('site.terms.effective_value') }}</span>
                    </div>
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-btn bg-ink-900 border border-ink-border text-paper-muted">
                        <svg class="w-4 h-4 text-gold shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span class="text-gold font-bold">{{ __('site.terms.scope') }}</span>
                        <span class="text-mist">{{ __('site.terms.scope_value') }}</span>
                    </div>
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-btn bg-ink-900 border border-ink-border text-paper-muted">
                        <svg class="w-4 h-4 text-gold shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                        <span class="text-gold font-bold">{{ __('site.terms.control') }}</span>
                        <span class="text-mist">{{ __('site.terms.control_value') }}</span>
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
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-paper mb-1">{{ __('site.terms.card1_title') }}</h3>
                        <p class="text-xs text-mist leading-relaxed">
                            {{ __('site.terms.card1_text') }}
                        </p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="p-5 rounded-panel bg-ink-900 border border-ink-border flex flex-col justify-between spotlight-card">
                    <div class="w-10 h-10 rounded-btn bg-gold/10 border border-gold/20 text-gold flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-paper mb-1">{{ __('site.terms.card2_title') }}</h3>
                        <p class="text-xs text-mist leading-relaxed">
                            {{ __('site.terms.card2_text') }}
                        </p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="p-5 rounded-panel bg-ink-900 border border-ink-border flex flex-col justify-between spotlight-card">
                    <div class="w-10 h-10 rounded-btn bg-gold/10 border border-gold/20 text-gold flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-paper mb-1">{{ __('site.terms.card3_title') }}</h3>
                        <p class="text-xs text-mist leading-relaxed">
                            {{ __('site.terms.card3_text') }}
                        </p>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="p-5 rounded-panel bg-ink-900 border border-ink-border flex flex-col justify-between spotlight-card">
                    <div class="w-10 h-10 rounded-btn bg-gold/10 border border-gold/20 text-gold flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-paper mb-1">{{ __('site.terms.card4_title') }}</h3>
                        <p class="text-xs text-mist leading-relaxed">
                            {{ __('site.terms.card4_text') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Main Terms & Guidelines Content ── -->
    <main class="py-16 max-w-5xl mx-auto px-6 space-y-12">

        <!-- 1-Modda -->
        <article class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/25 flex items-center justify-center font-mono font-bold text-sm">
                    01
                </span>
                <h2 class="text-xl font-bold text-white">{{ __('site.terms.a1_title') }}</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>{!! __('site.terms.a1_p1') !!}</p>
                <p>{{ __('site.terms.a1_p2') }}</p>
                <p>{{ __('site.terms.a1_p3') }}</p>
            </div>
        </article>

        <!-- 2-Modda -->
        <article class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/25 flex items-center justify-center font-mono font-bold text-sm">
                    02
                </span>
                <h2 class="text-xl font-bold text-white">{{ __('site.terms.a2_title') }}</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>{{ __('site.terms.a2_p1') }}</p>
                <p>{{ __('site.terms.a2_p2') }}</p>
                <p>{{ __('site.terms.a2_p3') }}</p>
            </div>
        </article>

        <!-- 3-Modda -->
        <article class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/25 flex items-center justify-center font-mono font-bold text-sm">
                    03
                </span>
                <h2 class="text-xl font-bold text-white">{{ __('site.terms.a3_title') }}</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>{{ __('site.terms.a3_p1') }}</p>
                <p>{{ __('site.terms.a3_p2') }}</p>
                <p>{{ __('site.terms.a3_p3') }}</p>
            </div>
        </article>

        <!-- 4-Modda -->
        <article class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/25 flex items-center justify-center font-mono font-bold text-sm">
                    04
                </span>
                <h2 class="text-xl font-bold text-white">{{ __('site.terms.a4_title') }}</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>{!! __('site.terms.a4_p1') !!}</p>
                <p>{{ __('site.terms.a4_p2') }}</p>
                <p>{{ __('site.terms.a4_p3') }}</p>
            </div>
        </article>

        <!-- ── 5-MODDA: ASOSIY BAN ME'YORLARI JADVALI ── -->
        <article class="p-8 sm:p-10 rounded-panel bg-ink-900 border border-gold/30 shadow-card-depth relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-btn bg-gold/10 text-gold border border-gold/25 flex items-center justify-center font-bold text-lg">
                        <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                    </span>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-serif font-bold text-paper">{{ __('site.terms.a5_title') }}</h2>
                        <p class="text-xs text-gold/80 mt-0.5 font-mono">{{ __('site.terms.a5_sub') }}</p>
                    </div>
                </div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-btn bg-vermilion/10 border border-vermilion/20 text-rose-300 text-xs font-bold font-mono">
                    <span>{{ __('site.terms.a5_tiers') }}</span>
                </div>
            </div>

            <div class="mt-6 text-sm text-slate-300 leading-relaxed mb-6">
                {{ __('site.terms.a5_intro') }}
            </div>

            <!-- Ban Tiers Grid -->
            <div class="space-y-4">

                <!-- Tier 1 -->
                <div class="p-5 sm:p-6 rounded-2xl bg-ink-950/80 border border-amber-500/30 hover:border-amber-500/60 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2.5 py-1 rounded-lg bg-amber-500/20 text-amber-300 font-mono font-black text-xs border border-amber-500/30">
                                {{ __('site.terms.t1_badge') }}
                            </span>
                            <h3 class="text-sm sm:text-base font-bold text-white">{{ __('site.terms.t1_title') }}</h3>
                        </div>
                        <span class="text-[11px] font-mono text-slate-400 bg-slate-800/80 px-2 py-0.5 rounded border border-slate-700">
                            {{ __('site.terms.t1_tag') }}
                        </span>
                    </div>
                    <ul class="text-xs sm:text-sm text-slate-300 space-y-2 list-disc list-inside leading-relaxed">
                        <li>{!! __('site.terms.t1_i1') !!}</li>
                        <li>{!! __('site.terms.t1_i2') !!}</li>
                        <li>{!! __('site.terms.t1_i3') !!}</li>
                        <li>{!! __('site.terms.t1_i4') !!}</li>
                    </ul>
                    <div class="mt-3 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-amber-400/90 font-mono">
                        <span>{{ __('site.terms.t1_goal') }}</span>
                        <span class="text-slate-400">{{ __('site.terms.t1_note') }}</span>
                    </div>
                </div>

                <!-- Tier 2 -->
                <div class="p-5 sm:p-6 rounded-2xl bg-ink-950/80 border border-orange-500/30 hover:border-orange-500/60 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2.5 py-1 rounded-lg bg-orange-500/20 text-orange-300 font-mono font-black text-xs border border-orange-500/30">
                                {{ __('site.terms.t2_badge') }}
                            </span>
                            <h3 class="text-sm sm:text-base font-bold text-white">{{ __('site.terms.t2_title') }}</h3>
                        </div>
                        <span class="text-[11px] font-mono text-slate-400 bg-slate-800/80 px-2 py-0.5 rounded border border-slate-700">
                            {{ __('site.terms.t2_tag') }}
                        </span>
                    </div>
                    <ul class="text-xs sm:text-sm text-slate-300 space-y-2 list-disc list-inside leading-relaxed">
                        <li>{!! __('site.terms.t2_i1') !!}</li>
                        <li>{!! __('site.terms.t2_i2') !!}</li>
                        <li>{!! __('site.terms.t2_i3') !!}</li>
                        <li>{!! __('site.terms.t2_i4') !!}</li>
                    </ul>
                    <div class="mt-3 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-orange-400/90 font-mono">
                        <span>{{ __('site.terms.t2_goal') }}</span>
                        <span class="text-slate-400">{{ __('site.terms.t2_note') }}</span>
                    </div>
                </div>

                <!-- Tier 3 -->
                <div class="p-5 sm:p-6 rounded-2xl bg-ink-950/80 border border-rose-500/40 hover:border-rose-500/70 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2.5 py-1 rounded-lg bg-rose-500/20 text-rose-300 font-mono font-black text-xs border border-rose-500/30">
                                {{ __('site.terms.t3_badge') }}
                            </span>
                            <h3 class="text-sm sm:text-base font-bold text-white">{{ __('site.terms.t3_title') }}</h3>
                        </div>
                        <span class="text-[11px] font-mono text-slate-400 bg-slate-800/80 px-2 py-0.5 rounded border border-slate-700">
                            {{ __('site.terms.t3_tag') }}
                        </span>
                    </div>
                    <ul class="text-xs sm:text-sm text-slate-300 space-y-2 list-disc list-inside leading-relaxed">
                        <li>{!! __('site.terms.t3_i1') !!}</li>
                        <li>{!! __('site.terms.t3_i2') !!}</li>
                        <li>{!! __('site.terms.t3_i3') !!}</li>
                        <li>{!! __('site.terms.t3_i4') !!}</li>
                    </ul>
                    <div class="mt-3 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-rose-400/90 font-mono">
                        <span>{{ __('site.terms.t3_goal') }}</span>
                        <span class="text-slate-400">{{ __('site.terms.t3_note') }}</span>
                    </div>
                </div>

                <!-- Tier 4 -->
                <div class="p-5 sm:p-6 rounded-2xl bg-ink-950/80 border border-purple-500/40 hover:border-purple-500/70 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2.5 py-1 rounded-lg bg-purple-500/20 text-purple-300 font-mono font-black text-xs border border-purple-500/30">
                                {{ __('site.terms.t4_badge') }}
                            </span>
                            <h3 class="text-sm sm:text-base font-bold text-white">{{ __('site.terms.t4_title') }}</h3>
                        </div>
                        <span class="text-[11px] font-mono text-slate-400 bg-slate-800/80 px-2 py-0.5 rounded border border-slate-700">
                            {{ __('site.terms.t4_tag') }}
                        </span>
                    </div>
                    <ul class="text-xs sm:text-sm text-slate-300 space-y-2 list-disc list-inside leading-relaxed">
                        <li>{!! __('site.terms.t4_i1') !!}</li>
                        <li>{!! __('site.terms.t4_i2') !!}</li>
                        <li>{!! __('site.terms.t4_i3') !!}</li>
                        <li>{!! __('site.terms.t4_i4') !!}</li>
                    </ul>
                    <div class="mt-3 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs text-purple-400/90 font-mono">
                        <span>{{ __('site.terms.t4_goal') }}</span>
                        <span class="text-slate-400">{{ __('site.terms.t4_note') }}</span>
                    </div>
                </div>

                <!-- Tier 5 -->
                <div class="p-5 sm:p-6 rounded-2xl bg-gradient-to-r from-red-950/80 to-ink-950 border border-red-500/50 hover:border-red-500 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2.5 py-1 rounded-lg bg-red-600 text-white font-mono font-black text-xs shadow-lg shadow-red-600/30">
                                {{ __('site.terms.t5_badge') }}
                            </span>
                            <h3 class="text-sm sm:text-base font-black text-red-300">{{ __('site.terms.t5_title') }}</h3>
                        </div>
                        <span class="text-[11px] font-mono text-red-400 bg-red-500/10 px-2 py-0.5 rounded border border-red-500/25">
                            {{ __('site.terms.t5_tag') }}
                        </span>
                    </div>
                    <ul class="text-xs sm:text-sm text-red-100/90 space-y-2 list-disc list-inside leading-relaxed">
                        <li>{!! __('site.terms.t5_i1') !!}</li>
                        <li>{!! __('site.terms.t5_i2') !!}</li>
                        <li>{!! __('site.terms.t5_i3') !!}</li>
                        <li>{!! __('site.terms.t5_i4') !!}</li>
                    </ul>
                    <div class="mt-3 pt-3 border-t border-red-500/20 flex flex-col sm:flex-row items-start sm:items-center justify-between text-xs text-red-300 font-mono gap-1">
                        <span>{{ __('site.terms.t5_goal') }}</span>
                        <span class="text-red-400 font-bold">{{ __('site.terms.t5_note') }}</span>
                    </div>
                </div>

            </div>

            <!-- Note on Ban Execution -->
            <div class="mt-6 p-4 rounded-btn bg-ink-950/80 border border-ink-border text-xs text-mist leading-relaxed flex items-start gap-3">
                <span class="w-5 h-5 text-gold shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </span>
                <div>
                    <strong class="text-paper">{{ __('site.terms.a5_note_title') }}</strong> {{ __('site.terms.a5_note_text') }}
                </div>
            </div>
        </article>

        <!-- 6-Modda -->
        <article class="p-8 sm:p-10 rounded-panel bg-ink-900 border border-ink-border space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-btn bg-gold/10 text-gold border border-gold/25 flex items-center justify-center font-mono font-bold text-sm">
                    06
                </span>
                <h2 class="text-xl font-serif font-bold text-paper">{{ __('site.terms.a6_title') }}</h2>
            </div>
            <div class="text-sm text-paper-muted leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>{!! __('site.terms.a6_p1') !!}</p>
                <p>{{ __('site.terms.a6_p2') }}</p>
                <p>
                    {{ __('site.terms.a6_p3_pre') }}
                    <a href="{{ route('contact') }}" class="text-gold underline hover:text-gold-light font-semibold">{{ __('site.terms.a6_link') }}</a>
                    {{ __('site.terms.a6_p3_post') }}
                </p>
            </div>
        </article>

        <!-- 7-Modda -->
        <article class="p-8 sm:p-10 rounded-panel bg-ink-900 border border-ink-border space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-btn bg-gold/10 text-gold border border-gold/25 flex items-center justify-center font-mono font-bold text-sm">
                    07
                </span>
                <h2 class="text-xl font-serif font-bold text-paper">{{ __('site.terms.a7_title') }}</h2>
            </div>
            <div class="text-sm text-paper-muted leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>{{ __('site.terms.a7_p1') }}</p>
                <p>{{ __('site.terms.a7_p2') }}</p>
            </div>
        </article>

        <!-- CTA Contact Block -->
        <div class="p-8 sm:p-10 rounded-panel bg-ink-900 border border-gold/25 text-center space-y-4 shadow-card-depth">
            <div class="w-12 h-12 rounded-btn bg-gold/10 border border-gold/20 text-gold flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-xl sm:text-2xl font-serif font-bold text-paper">{{ __('site.terms.cta_title') }}</h3>
            <p class="text-xs sm:text-sm text-mist max-w-xl mx-auto leading-relaxed">
                {{ __('site.terms.cta_text') }}
            </p>
            <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('contact') }}" class="ks-btn-gold px-6 py-3">
                    {{ __('site.terms.cta_contact') }}
                </a>
                <a href="{{ route('home') }}" class="ks-btn-ghost px-6 py-3">
                    {{ __('site.terms.cta_home') }}
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

    <!-- ── Universal Book Share Modal ── -->
    <x-book-share-modal />

    <!-- ── Persistent Global Audio Player (Mutolaa Dock) ── -->
    <x-global-audio-player />

    <!-- ── Mobile Bottom Navigation Bar ── -->
    <x-nav.mobile-bottom-bar />
</body>
</html>
