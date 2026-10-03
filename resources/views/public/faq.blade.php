<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ __('site.faq.sub') }}">
    <title>{{ __('site.faq.badge') }} — Kitobxon</title>

    @include('partials.design-system')


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
            background: rgba(13, 17, 23, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .faq-item:hover {
            border-color: rgba(245, 158, 11, 0.35);
        }
        .faq-item.active {
            border-color: rgba(245, 158, 11, 0.5);
            background: rgba(18, 24, 38, 0.95);
            box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.6), 0 0 20px -2px rgba(245, 158, 11, 0.12);
        }
    </style>
</head>
<body class="bg-ink-950 text-paper-muted font-sans selection:bg-amber-500 selection:text-ink-950 antialiased min-h-screen relative overflow-x-hidden">

    <!-- ── Page Transition & Loader ── -->
    @include('components.page-loader')

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
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-pill bg-gold/10 border border-gold/25 text-gold font-mono text-xs tracking-wider">
                    {{ __('site.faq.badge') }}
                </span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-serif font-bold text-paper tracking-tight leading-tight">
                    {{ __('site.faq.title_1') }}<span class="text-gold italic font-serif">{{ __('site.faq.title_2') }}</span>{{ __('site.faq.title_3') }}
                </h1>
                <p class="text-mist text-xs sm:text-sm max-w-xl mx-auto leading-relaxed">
                    {{ __('site.faq.sub') }}
                </p>
            </div>

            <!-- Qidiruv maydoni -->
            <div class="mb-6 relative max-w-xl mx-auto">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-mist">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="{{ __('site.faq.search_ph') }}"
                           class="w-full pl-11 pr-10 py-3 bg-ink-900 border border-ink-border rounded-btn text-xs sm:text-sm text-paper placeholder-mist focus:outline-none focus:border-gold transition-all shadow-soft font-sans">
                    <button x-show="searchQuery.length > 0" 
                            @click="searchQuery = ''"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-mist hover:text-paper cursor-pointer"
                            style="display: none;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Kategoriya Filterlari -->
            <div class="flex items-center justify-center flex-wrap gap-2 mb-10 text-xs">
                <button @click="activeCat = 'all'"
                        :class="activeCat === 'all' ? 'bg-gold text-ink-950 font-bold shadow-md shadow-gold/20' : 'bg-ink-900 text-mist hover:text-paper hover:bg-ink-800 border border-ink-border'"
                        class="px-4 py-2 rounded-btn transition-all cursor-pointer">
                    {{ __('site.faq.cat_all') }}
                </button>
                <button @click="activeCat = 'books'"
                        :class="activeCat === 'books' ? 'bg-gold text-ink-950 font-bold shadow-md shadow-gold/20' : 'bg-ink-900 text-mist hover:text-paper hover:bg-ink-800 border border-ink-border'"
                        class="px-4 py-2 rounded-btn transition-all cursor-pointer">
                    {{ __('site.faq.cat_books') }}
                </button>
                <button @click="activeCat = 'points'"
                        :class="activeCat === 'points' ? 'bg-gold text-ink-950 font-bold shadow-md shadow-gold/20' : 'bg-ink-900 text-mist hover:text-paper hover:bg-ink-800 border border-ink-border'"
                        class="px-4 py-2 rounded-btn transition-all cursor-pointer">
                    {{ __('site.faq.cat_points') }}
                </button>
                <button @click="activeCat = 'groups'"
                        :class="activeCat === 'groups' ? 'bg-gold text-ink-950 font-bold shadow-md shadow-gold/20' : 'bg-ink-900 text-mist hover:text-paper hover:bg-ink-800 border border-ink-border'"
                        class="px-4 py-2 rounded-btn transition-all cursor-pointer">
                    {{ __('site.faq.cat_groups') }}
                </button>
                <button @click="activeCat = 'ai'"
                        :class="activeCat === 'ai' ? 'bg-gold text-ink-950 font-bold shadow-md shadow-gold/20' : 'bg-ink-900 text-mist hover:text-paper hover:bg-ink-800 border border-ink-border'"
                        class="px-4 py-2 rounded-btn transition-all cursor-pointer">
                    {{ __('site.faq.cat_ai') }}
                </button>
            </div>

            <!-- Accordion Ro'yxati -->
            <div class="space-y-4">
                
                <!-- 1. Mutolaa va Kitoblar -->
                <div x-show="matches(@js(__('site.faq.q1')), 'books', @js(__('site.faq.q1a_catalog_d') . ' ' . __('site.faq.q1a_chapters_d') . ' ' . __('site.faq.q1a_quiz_d')))" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 1 }">
                    <button @click="toggle(1)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-paper flex items-center gap-3">
                            <span class="w-8 h-8 rounded-btn bg-gold/10 border border-gold/20 flex items-center justify-center text-gold shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </span>
                            {{ __('site.faq.q1') }}
                        </span>
                        <div class="w-8 h-8 rounded-btn bg-white/5 flex items-center justify-center text-gold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-gold/20 text-gold': openItem === 1 }">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="openItem === 1" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-paper-muted leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p>{{ __('site.faq.q1a_intro') }}</p>
                        <ul class="list-disc list-inside space-y-1 text-mist pl-1">
                            <li><strong class="text-paper">{{ __('site.faq.q1a_catalog') }}</strong> {{ __('site.faq.q1a_catalog_d') }}</li>
                            <li><strong class="text-paper">{{ __('site.faq.q1a_chapters') }}</strong> {{ __('site.faq.q1a_chapters_d') }}</li>
                            <li><strong class="text-paper">{{ __('site.faq.q1a_quiz') }}</strong> {{ __('site.faq.q1a_quiz_d') }}</li>
                        </ul>
                    </div>
                </div>

                <!-- 2. Bepul foydalanish -->
                <div x-show="matches(@js(__('site.faq.q2')), 'books', @js(__('site.faq.q2a')))" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 2 }">
                    <button @click="toggle(2)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-paper flex items-center gap-3">
                            <span class="w-8 h-8 rounded-btn bg-gold/10 border border-gold/20 flex items-center justify-center text-gold shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </span>
                            {{ __('site.faq.q2') }}
                        </span>
                        <div class="w-8 h-8 rounded-btn bg-white/5 flex items-center justify-center text-gold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-gold/20 text-gold': openItem === 2 }">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="openItem === 2" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-paper-muted leading-relaxed border-t border-white/5 pt-4">
                        {{ __('site.faq.q2a') }}
                    </div>
                </div>

                <!-- 3. Audio kitoblar -->
                <div x-show="matches(@js(__('site.faq.q3')), 'books', @js(__('site.faq.q3a')))" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 3 }">
                    <button @click="toggle(3)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-paper flex items-center gap-3">
                            <span class="w-8 h-8 rounded-btn bg-gold/10 border border-gold/20 flex items-center justify-center text-gold shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 18v-6a9 9 0 0118 0v6M3 18a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3v5zm18 0a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3v5z"/></svg>
                            </span>
                            {{ __('site.faq.q3') }}
                        </span>
                        <div class="w-8 h-8 rounded-btn bg-white/5 flex items-center justify-center text-gold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-gold/20 text-gold': openItem === 3 }">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="openItem === 3" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-paper-muted leading-relaxed border-t border-white/5 pt-4">
                        {{ __('site.faq.q3a') }}
                    </div>
                </div>

                <!-- 4. Ballar va Tangalar -->
                <div x-show="matches(@js(__('site.faq.q4')), 'points', @js(__('site.faq.q4a_points') . ' ' . __('site.faq.q4a_how')))" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 4 }">
                    <button @click="toggle(4)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-paper flex items-center gap-3">
                            <span class="w-8 h-8 rounded-btn bg-gold/10 border border-gold/20 flex items-center justify-center text-gold shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="9" r="6" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.5 9A6 6 0 0121 15a6 6 0 01-6.5 6M9 15h6"/></svg>
                            </span>
                            {{ __('site.faq.q4') }}
                        </span>
                        <div class="w-8 h-8 rounded-btn bg-white/5 flex items-center justify-center text-gold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-gold/20 text-gold': openItem === 4 }">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="openItem === 4" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-paper-muted leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p><strong class="text-paper">{{ __('site.faq.q4a_points') }}</strong></p>
                        <p><strong class="text-paper">{{ __('site.faq.q4a_how') }}</strong></p>
                        <ul class="list-disc list-inside space-y-1 text-mist pl-1">
                            <li>{{ __('site.faq.q4a_r1') }} <span class="text-gold font-mono font-semibold">+10 ball</span></li>
                            <li>{{ __('site.faq.q4a_r2') }}</li>
                            <li>{{ __('site.faq.q4a_r3') }}</li>
                        </ul>
                        <p><strong class="text-paper">{{ __('site.faq.q4a_coins') }}</strong></p>
                    </div>
                </div>

                <!-- 5. Streak nima -->
                <div x-show="matches(@js(__('site.faq.q5')), 'points', @js(__('site.faq.q5a_p1') . ' ' . __('site.faq.q5a_p2')))" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 5 }">
                    <button @click="toggle(5)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-paper flex items-center gap-3">
                            <span class="w-8 h-8 rounded-btn bg-gold/10 border border-gold/20 flex items-center justify-center text-gold shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </span>
                            {{ __('site.faq.q5') }}
                        </span>
                        <div class="w-8 h-8 rounded-btn bg-white/5 flex items-center justify-center text-gold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-gold/20 text-gold': openItem === 5 }">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="openItem === 5" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-paper-muted leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p>{{ __('site.faq.q5a_p1') }}</p>
                        <p>{{ __('site.faq.q5a_p2') }}</p>
                    </div>
                </div>

                <!-- 6. Reyting (Leaderboard) -->
                <div x-show="matches(@js(__('site.faq.q6')), 'points', @js(__('site.faq.q6a_p1') . ' ' . __('site.faq.q6a_p2')))" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 6 }">
                    <button @click="toggle(6)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-paper flex items-center gap-3">
                            <span class="w-8 h-8 rounded-btn bg-gold/10 border border-gold/20 flex items-center justify-center text-gold shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </span>
                            {{ __('site.faq.q6') }}
                        </span>
                        <div class="w-8 h-8 rounded-btn bg-white/5 flex items-center justify-center text-gold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-gold/20 text-gold': openItem === 6 }">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="openItem === 6" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-paper-muted leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p>{{ __('site.faq.q6a_p1') }}</p>
                        <ul class="list-disc list-inside space-y-1 text-mist pl-1">
                            <li><strong class="text-paper">{{ __('site.faq.q6a_d1') }}</strong> {{ __('site.faq.q6a_d1_d') }}</li>
                            <li><strong class="text-paper">{{ __('site.faq.q6a_d2') }}</strong> {{ __('site.faq.q6a_d2_d') }}</li>
                            <li><strong class="text-paper">{{ __('site.faq.q6a_d3') }}</strong> {{ __('site.faq.q6a_d3_d') }}</li>
                            <li><strong class="text-paper">{{ __('site.faq.q6a_d4') }}</strong> {{ __('site.faq.q6a_d4_d') }}</li>
                        </ul>
                        <p class="pt-1">{{ __('site.faq.q6a_p2') }}</p>
                    </div>
                </div>

                <!-- 7. Guruhlar va Yopiq guruhlar -->
                <div x-show="matches(@js(__('site.faq.q7')), 'groups', @js(__('site.faq.q7a_open_d') . ' ' . __('site.faq.q7a_closed_d')))" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 7 }">
                    <button @click="toggle(7)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-paper flex items-center gap-3">
                            <span class="w-8 h-8 rounded-btn bg-gold/10 border border-gold/20 flex items-center justify-center text-gold shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </span>
                            {{ __('site.faq.q7') }}
                        </span>
                        <div class="w-8 h-8 rounded-btn bg-white/5 flex items-center justify-center text-gold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-gold/20 text-gold': openItem === 7 }">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="openItem === 7" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-paper-muted leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p><strong class="text-paper">{{ __('site.faq.q7a_open') }}</strong> {{ __('site.faq.q7a_open_d') }}</p>
                        <p><strong class="text-paper">{{ __('site.faq.q7a_closed') }}</strong> {{ __('site.faq.q7a_closed_d') }}</p>
                    </div>
                </div>

                <!-- 8. Guruh ochish limitlari -->
                <div x-show="matches(@js(__('site.faq.q8')), 'groups', @js(__('site.faq.q8a_p1') . ' ' . __('site.faq.q8a_p2')))" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 8 }">
                    <button @click="toggle(8)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-paper flex items-center gap-3">
                            <span class="w-8 h-8 rounded-btn bg-gold/10 border border-gold/20 flex items-center justify-center text-gold shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                            </span>
                            {{ __('site.faq.q8') }}
                        </span>
                        <div class="w-8 h-8 rounded-btn bg-white/5 flex items-center justify-center text-gold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-gold/20 text-gold': openItem === 8 }">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="openItem === 8" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-paper-muted leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p>{{ __('site.faq.q8a_p1') }}</p>
                        <ul class="list-disc list-inside space-y-1 text-mist pl-1">
                            <li><strong class="text-paper">{{ __('site.faq.q8a_r1') }}</strong> {{ __('site.faq.q8a_max') }} <span class="text-gold font-mono font-bold">2</span> {{ __('site.faq.q8a_group') }}</li>
                            <li><strong class="text-paper">{{ __('site.faq.q8a_r2') }}</strong> {{ __('site.faq.q8a_max') }} <span class="text-gold font-mono font-bold">5</span> {{ __('site.faq.q8a_group') }}</li>
                            <li><strong class="text-paper">{{ __('site.faq.q8a_r3') }}</strong> <span class="text-gold font-mono font-bold">{{ __('site.faq.q8a_unlimited') }}</span></li>
                        </ul>
                        <p class="pt-1">{{ __('site.faq.q8a_p2') }}</p>
                    </div>
                </div>

                <!-- 9. Guruhni o'chirish huquqi -->
                <div x-show="matches(@js(__('site.faq.q9')), 'groups', @js(__('site.faq.q9a_p1') . ' ' . __('site.faq.q9a_p2')))" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 9 }">
                    <button @click="toggle(9)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-paper flex items-center gap-3">
                            <span class="w-8 h-8 rounded-btn bg-gold/10 border border-gold/20 flex items-center justify-center text-gold shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </span>
                            {{ __('site.faq.q9') }}
                        </span>
                        <div class="w-8 h-8 rounded-btn bg-white/5 flex items-center justify-center text-gold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-gold/20 text-gold': openItem === 9 }">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="openItem === 9" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-paper-muted leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p>{{ __('site.faq.q9a_p1') }}</p>
                        <p>{{ __('site.faq.q9a_p2') }}</p>
                    </div>
                </div>

                <!-- 10. Umumiy Chat qoidalari -->
                <div x-show="matches(@js(__('site.faq.q10')), 'groups', @js(__('site.faq.q10a_p1') . ' ' . __('site.faq.q10a_delete_d')))" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 10 }">
                    <button @click="toggle(10)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-paper flex items-center gap-3">
                            <span class="w-8 h-8 rounded-btn bg-gold/10 border border-gold/20 flex items-center justify-center text-gold shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            </span>
                            {{ __('site.faq.q10') }}
                        </span>
                        <div class="w-8 h-8 rounded-btn bg-white/5 flex items-center justify-center text-gold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-gold/20 text-gold': openItem === 10 }">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="openItem === 10" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-paper-muted leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p>{{ __('site.faq.q10a_p1') }}</p>
                        <p><strong class="text-paper">{{ __('site.faq.q10a_delete') }}</strong> {{ __('site.faq.q10a_delete_d') }}</p>
                    </div>
                </div>

                <!-- 11. AI Kitob Maslahatchisi -->
                <div x-show="matches(@js(__('site.faq.q11')), 'ai', @js(__('site.faq.q11a_p1') . ' ' . __('site.faq.q11a_p2')))" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 11 }">
                    <button @click="toggle(11)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-paper flex items-center gap-3">
                            <span class="w-8 h-8 rounded-btn bg-gold/10 border border-gold/20 flex items-center justify-center text-gold shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                            {{ __('site.faq.q11') }}
                        </span>
                        <div class="w-8 h-8 rounded-btn bg-white/5 flex items-center justify-center text-gold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-gold/20 text-gold': openItem === 11 }">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="openItem === 11" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-paper-muted leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p>{{ __('site.faq.q11a_p1') }}</p>
                        <p>{{ __('site.faq.q11a_p2') }}</p>
                    </div>
                </div>

                <!-- 12. Jonli Efirlar -->
                <div x-show="matches(@js(__('site.faq.q12')), 'ai', @js(__('site.faq.q12a_p1') . ' ' . __('site.faq.q12a_p2')))" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 12 }">
                    <button @click="toggle(12)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-paper flex items-center gap-3">
                            <span class="w-8 h-8 rounded-btn bg-gold/10 border border-gold/20 flex items-center justify-center text-gold shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </span>
                            {{ __('site.faq.q12') }}
                        </span>
                        <div class="w-8 h-8 rounded-btn bg-white/5 flex items-center justify-center text-gold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-gold/20 text-gold': openItem === 12 }">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="openItem === 12" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-paper-muted leading-relaxed border-t border-white/5 pt-4 space-y-2">
                        <p>{{ __('site.faq.q12a_p1') }}</p>
                        <p>{{ __('site.faq.q12a_p2') }}</p>
                    </div>
                </div>

                <!-- 13. Parol va Profil Sozlamalari -->
                <div x-show="matches(@js(__('site.faq.q13')), 'books', @js(__('site.faq.q13a')))" 
                     class="faq-card faq-item" :class="{ 'active': openItem === 13 }">
                    <button @click="toggle(13)" 
                            class="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4 focus:outline-none cursor-pointer">
                        <span class="text-sm sm:text-base font-bold text-paper flex items-center gap-3">
                            <span class="w-8 h-8 rounded-btn bg-gold/10 border border-gold/20 flex items-center justify-center text-gold shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="1.8"/></svg>
                            </span>
                            {{ __('site.faq.q13') }}
                        </span>
                        <div class="w-8 h-8 rounded-btn bg-white/5 flex items-center justify-center text-gold text-xs shrink-0 transition-transform duration-300"
                             :class="{ 'rotate-180 bg-gold/20 text-gold': openItem === 13 }">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </button>
                    <div x-show="openItem === 13" x-cloak class="px-5 sm:px-6 pb-6 text-xs sm:text-sm text-paper-muted leading-relaxed border-t border-white/5 pt-4">
                        {{ __('site.faq.q13a') }}
                    </div>
                </div>

            </div>

            <!-- Qidiruv natijasi topilmagan holat -->
            <div x-show="searchQuery.trim().length > 0 && !document.querySelectorAll('.faq-item:not([style*=\'display: none\'])').length"
                 x-cloak
                 class="text-center py-12 p-8 rounded-panel bg-ink-900/60 border border-ink-border space-y-3">
                <span class="w-12 h-12 rounded-btn bg-white/5 border border-white/10 flex items-center justify-center text-mist mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <h4 class="text-base font-bold text-paper">{{ __('site.faq.no_results_t') }}</h4>
                <p class="text-xs text-mist max-w-sm mx-auto">
                    {{ __('site.faq.no_results_s') }}
                </p>
                <button @click="searchQuery = ''; activeCat = 'all'"
                        class="px-4 py-2 bg-gold/10 text-gold hover:bg-gold/20 text-xs font-bold rounded-btn transition-colors cursor-pointer border border-gold/20">
                    {{ __('site.faq.clear_filter') }}
                </button>
            </div>

            <!-- Still Have Questions Banner -->
            <div class="faq-cta-card mt-16 p-8 sm:p-10 rounded-panel bg-ink-900 border border-ink-border text-center space-y-4 shadow-card-depth">
                <div class="w-12 h-12 rounded-btn bg-gold/10 border border-gold/20 text-gold flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <h3 class="text-xl sm:text-2xl font-serif font-bold text-paper">{{ __('site.faq.cta_title') }}</h3>
                <p class="text-xs sm:text-sm text-mist max-w-md mx-auto leading-relaxed">
                    {{ __('site.faq.cta_sub') }}
                </p>
                <div class="pt-2">
                    <a href="{{ route('contact') }}" class="ks-btn-gold inline-flex items-center gap-2 px-7 py-3">
                        <span>{{ __('site.faq.cta_btn') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

        </div>
    </main>

    <!-- ── Universal Footer ── -->
    <x-nav.main-footer />
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

    <!-- ── Universal Toast Notification Container ── -->
    <x-toast-container />
</body>
</html>
