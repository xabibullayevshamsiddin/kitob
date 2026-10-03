<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark" x-data="{ mobileMenu: false, activeTab: 'all' }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ __('site.terms.meta_desc') }}">
    <title>{{ __('site.terms.page_title') }}</title>

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
                    <span>{{ __('site.terms.badge') }}</span>
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.1] max-w-4xl">
                    {{ __('site.terms.h1_a') }} <br class="hidden sm:inline">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-amber-200 to-amber-500">{{ __('site.terms.h1_b') }}</span>
                </h1>

                <p class="text-sm sm:text-base text-slate-400 max-w-2xl leading-relaxed">
                    {{ __('site.terms.intro') }}
                </p>

                <!-- Info Badges -->
                <div class="pt-4 flex flex-wrap items-center justify-center gap-3 text-xs">
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-ink-900/80 border border-white/10 text-slate-300">
                        <span class="text-amber-400 font-bold">📅 {{ __('site.terms.effective') }}</span>
                        <span class="font-mono text-slate-400">{{ __('site.terms.effective_value') }}</span>
                    </div>
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-ink-900/80 border border-white/10 text-slate-300">
                        <span class="text-emerald-400 font-bold">👥 {{ __('site.terms.scope') }}</span>
                        <span class="text-slate-400">{{ __('site.terms.scope_value') }}</span>
                    </div>
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-ink-900/80 border border-white/10 text-slate-300">
                        <span class="text-indigo-400 font-bold">⚖️ {{ __('site.terms.control') }}</span>
                        <span class="text-slate-400">{{ __('site.terms.control_value') }}</span>
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
                        <h3 class="text-sm font-bold text-white mb-1">{{ __('site.terms.card1_title') }}</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            {{ __('site.terms.card1_text') }}
                        </p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="p-5 rounded-2xl bg-ink-900/90 border border-white/10 flex flex-col justify-between spotlight-card">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl mb-3">
                        💬
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1">{{ __('site.terms.card2_title') }}</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            {{ __('site.terms.card2_text') }}
                        </p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="p-5 rounded-2xl bg-ink-900/90 border border-white/10 flex flex-col justify-between spotlight-card">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center text-xl mb-3">
                        🚫
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1">{{ __('site.terms.card3_title') }}</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            {{ __('site.terms.card3_text') }}
                        </p>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="p-5 rounded-2xl bg-ink-900/90 border border-white/10 flex flex-col justify-between spotlight-card">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-xl mb-3">
                        🚩
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1">{{ __('site.terms.card4_title') }}</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
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
                        <h2 class="text-xl sm:text-2xl font-black text-white">{{ __('site.terms.a5_title') }}</h2>
                        <p class="text-xs text-amber-400/80 mt-0.5 font-mono">{{ __('site.terms.a5_sub') }}</p>
                    </div>
                </div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-bold">
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
            <div class="mt-6 p-4 rounded-xl bg-slate-900/90 border border-slate-800 text-xs text-slate-400 leading-relaxed flex items-start gap-3">
                <span class="text-base text-amber-400">💡</span>
                <div>
                    <strong class="text-slate-200">{{ __('site.terms.a5_note_title') }}</strong> {{ __('site.terms.a5_note_text') }}
                </div>
            </div>
        </article>

        <!-- 6-Modda -->
        <article class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/25 flex items-center justify-center font-mono font-bold text-sm">
                    06
                </span>
                <h2 class="text-xl font-bold text-white">{{ __('site.terms.a6_title') }}</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>{!! __('site.terms.a6_p1') !!}</p>
                <p>{{ __('site.terms.a6_p2') }}</p>
                <p>
                    {{ __('site.terms.a6_p3_pre') }}
                    <a href="{{ route('contact') }}" class="text-amber-400 underline hover:text-amber-300 font-semibold">{{ __('site.terms.a6_link') }}</a>
                    {{ __('site.terms.a6_p3_post') }}
                </p>
            </div>
        </article>

        <!-- 7-Modda -->
        <article class="p-8 sm:p-10 rounded-3xl bg-ink-900/70 border border-white/10 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/25 flex items-center justify-center font-mono font-bold text-sm">
                    07
                </span>
                <h2 class="text-xl font-bold text-white">{{ __('site.terms.a7_title') }}</h2>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed space-y-3 pl-0 sm:pl-11">
                <p>{{ __('site.terms.a7_p1') }}</p>
                <p>{{ __('site.terms.a7_p2') }}</p>
            </div>
        </article>

        <!-- CTA Contact Block -->
        <div class="p-8 sm:p-10 rounded-3xl bg-gradient-to-r from-ink-900 via-slate-900 to-ink-900 border border-white/10 text-center space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-2xl mx-auto">
                📬
            </div>
            <h3 class="text-xl font-black text-white">{{ __('site.terms.cta_title') }}</h3>
            <p class="text-xs sm:text-sm text-slate-400 max-w-xl mx-auto leading-relaxed">
                {{ __('site.terms.cta_text') }}
            </p>
            <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('contact') }}" class="px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-ink-950 font-bold text-xs uppercase tracking-wider shadow-lg shadow-amber-500/20 transition-all active:scale-95">
                    {{ __('site.terms.cta_contact') }}
                </a>
                <a href="{{ route('home') }}" class="px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition-colors border border-slate-700">
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
</body>
</html>
