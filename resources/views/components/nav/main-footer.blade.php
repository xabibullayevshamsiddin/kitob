{{-- 
    Universal Main Footer Component — Kitobxon
    Editorial Dark Modern aesthetic with Spectral typography and Lucide SVG icons.
--}}
<footer class="border-t border-ink-border bg-ink-950 text-mist relative overflow-hidden z-10 selection:bg-amber-500 selection:text-ink-950">
    <!-- Main Footer Content -->
    <div class="max-w-7xl mx-auto px-6 pt-14 pb-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 lg:gap-8 pb-12 border-b border-ink-border">

            <!-- Col 1: Brand Info & Editorial Quote (Spans 2 cols on lg) -->
            <div class="lg:col-span-2 space-y-4">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 text-paper font-serif font-bold text-xl tracking-tight group">
                    <span class="w-8 h-8 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    </span>
                    <span class="text-paper group-hover:text-amber-400 transition-colors font-serif">Kitobxon</span>
                </a>

                <p class="text-xs sm:text-sm text-mist leading-relaxed max-w-sm font-sans">
                    Har hafta bitta sara kitob mutolaa qilib, intellektual salohiyatni oshirish va hamfikr kitobxonlar bilan chuqur tahlil maydoni.
                </p>

                <!-- Platform Status Badge -->
                <div class="pt-2 flex flex-wrap items-center gap-3 text-xs">
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-badge bg-ink-900 border border-ink-border text-emerald-400 font-mono text-[11px]">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span>Barcha xizmatlar faol</span>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-badge bg-ink-900 border border-ink-border text-mist text-[11px] font-mono">
                        <svg class="w-3.5 h-3.5 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span>SSL 256-bit xavfsiz</span>
                    </div>
                </div>
            </div>

            <!-- Col 2: Platforma (Asosiy xizmatlar) -->
            <div class="space-y-3.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-paper font-mono flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    Platforma
                </h4>
                <ul class="space-y-2.5 text-xs text-mist font-sans">
                    <li>
                        <a href="{{ route('home') }}" class="hover:text-paper transition-colors flex items-center gap-1.5 group">
                            <span class="text-ink-600 group-hover:text-amber-400 transition-colors">›</span>
                            <span>Bosh sahifa</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('books.public') }}" class="hover:text-paper transition-colors flex items-center gap-1.5 group">
                            <span class="text-ink-600 group-hover:text-amber-400 transition-colors">›</span>
                            <span>Kitoblar katalogi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('leaderboard') }}" class="hover:text-paper transition-colors flex items-center gap-1.5 group">
                            <span class="text-ink-600 group-hover:text-amber-400 transition-colors">›</span>
                            <span>Peshqadamlar reytingi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('chat') }}" class="hover:text-paper transition-colors flex items-center gap-1.5 group">
                            <span class="text-ink-600 group-hover:text-amber-400 transition-colors">›</span>
                            <span>Global Kitobxon Chati</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('groups.index') }}" class="hover:text-paper transition-colors flex items-center gap-1.5 group">
                            <span class="text-ink-600 group-hover:text-amber-400 transition-colors">›</span>
                            <span>Kitobxonlar guruhlari</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('live.index') }}" class="hover:text-paper transition-colors flex items-center gap-1.5 group">
                            <span class="text-ink-600 group-hover:text-amber-400 transition-colors">›</span>
                            <span>Jonli efirlar & Darslar</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Col 3: Ma'lumot & Yordam -->
            <div class="space-y-3.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-paper font-mono flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    Ma'lumot & Yordam
                </h4>
                <ul class="space-y-2.5 text-xs text-mist font-sans">
                    <li>
                        <a href="{{ route('about') }}" class="hover:text-paper transition-colors flex items-center gap-1.5 group">
                            <span class="text-ink-600 group-hover:text-amber-400 transition-colors">›</span>
                            <span>Biz haqimizda</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('faq') }}" class="hover:text-paper transition-colors flex items-center gap-1.5 group">
                            <span class="text-ink-600 group-hover:text-amber-400 transition-colors">›</span>
                            <span>Ko'p so'raladigan savollar</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('contact') }}" class="hover:text-paper transition-colors flex items-center gap-1.5 group">
                            <span class="text-ink-600 group-hover:text-amber-400 transition-colors">›</span>
                            <span>Bog'lanish & Aloqa</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('contact', ['report' => 1]) }}" class="hover:text-rose-300 transition-colors flex items-center gap-1.5 group">
                            <span class="text-rose-400 font-mono text-[10px]">🚩</span>
                            <span>Qoidabuzarlik ustidan shikoyat</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Col 4: Qoidalar & Maxfiylik -->
            <div class="space-y-3.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-paper font-mono flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    Huquqiy Me'yorlar
                </h4>
                <ul class="space-y-2.5 text-xs text-mist font-sans">
                    <li>
                        <a href="{{ route('terms') }}" class="hover:text-paper transition-colors flex items-center gap-1.5 group">
                            <span class="text-ink-600 group-hover:text-amber-400 transition-colors">›</span>
                            <span>Foydalanish shartlari</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('privacy') }}" class="hover:text-paper transition-colors flex items-center gap-1.5 group">
                            <span class="text-ink-600 group-hover:text-amber-400 transition-colors">›</span>
                            <span>Maxfiylik siyosati</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('terms') }}" class="hover:text-amber-400 transition-colors flex items-center gap-1.5 group">
                            <span class="text-ink-600 group-hover:text-amber-400 transition-colors">›</span>
                            <span>Ban me'yorlari jadvali</span>
                        </a>
                    </li>
                    <li>
                        <span class="text-[11px] text-ink-500 block pt-1 leading-relaxed">
                            Barcha intellektual mulk va mualliflik huquqlari himoyalangan.
                        </span>
                    </li>
                </ul>
            </div>

        </div>

        <!-- Bottom Copyright Bar -->
        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-mist font-mono">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-paper font-serif">Kitobxon</span>
                <span>•</span>
                <span>© {{ date('Y') }} {{ __('site.common.all_rights') ?? 'Barcha huquqlar himoyalangan.' }}</span>
            </div>

            <div class="flex items-center gap-2 text-mist text-[11px]">
                <span>O'zbekiston bo'ylab kitobsevarlar hamjamiyati</span>
            </div>

            <!-- Scroll to top button -->
            <div>
                <button type="button" 
                        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
                        class="ks-btn-ghost py-1 px-3 text-xs inline-flex items-center gap-1.5">
                    <span>Yuqoriga</span>
                    <span>↑</span>
                </button>
            </div>
        </div>
    </div>
</footer>
