{{-- 
    Universal Main Footer Component — Kitobxon
    All pages use this unified footer for complete consistency across the entire platform.
--}}
<footer class="border-t border-white/[0.08] bg-[#07090e] text-slate-400 relative overflow-hidden z-10 selection:bg-amber-500 selection:text-ink-950">
    <!-- Ambient Bottom Glow -->
    <div class="absolute bottom-0 right-1/4 w-[500px] h-[250px] bg-gradient-to-t from-amber-500/5 via-indigo-600/5 to-transparent rounded-full blur-[120px] pointer-events-none -z-10"></div>
    <div class="absolute bottom-0 left-10 w-[350px] h-[200px] bg-indigo-900/10 rounded-full blur-[100px] pointer-events-none -z-10"></div>

    <!-- Main Footer Content -->
    <div class="max-w-7xl mx-auto px-6 pt-14 pb-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 lg:gap-8 pb-12 border-b border-white/[0.06]">

            <!-- Col 1: Brand Info & Mission (Spans 2 cols on lg) -->
            <div class="lg:col-span-2 space-y-4">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 text-white font-black text-xl tracking-tight group">
                    <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-lg shadow-lg shadow-amber-500/20 group-hover:scale-105 transition-transform">
                        📚
                    </span>
                    <span class="text-white group-hover:text-amber-400 transition-colors">Kitobxon</span>
                </a>

                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed max-w-sm">
                    Har hafta bitta kitob mutolaa qilib, intellektual salohiyatni oshirish va hamfikr kitobxonlar bilan do'stona fikr almashish maydoni.
                </p>

                <!-- Platform Status Badge -->
                <div class="pt-2 flex flex-wrap items-center gap-3 text-xs">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 font-mono text-[11px]">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span>Barcha xizmatlar faol</span>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 border border-white/10 text-slate-400 text-[11px] font-mono">
                        <span>🛡️</span>
                        <span>SSL 256-bit xavfsiz</span>
                    </div>
                </div>
            </div>

            <!-- Col 2: Platforma (Asosiy xizmatlar) -->
            <div class="space-y-3.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-white flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    Platforma
                </h4>
                <ul class="space-y-2.5 text-xs text-slate-400">
                    <li>
                        <a href="{{ route('home') }}" class="hover:text-white transition-colors flex items-center gap-1.5 group">
                            <span class="text-slate-600 group-hover:text-amber-400 transition-colors">›</span>
                            <span>Bosh sahifa</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('books.public') }}" class="hover:text-white transition-colors flex items-center gap-1.5 group">
                            <span class="text-slate-600 group-hover:text-amber-400 transition-colors">›</span>
                            <span>Kitoblar katalogi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('leaderboard') }}" class="hover:text-white transition-colors flex items-center gap-1.5 group">
                            <span class="text-slate-600 group-hover:text-amber-400 transition-colors">›</span>
                            <span>Peshqadamlar reytingi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('chat') }}" class="hover:text-white transition-colors flex items-center gap-1.5 group">
                            <span class="text-slate-600 group-hover:text-amber-400 transition-colors">›</span>
                            <span>Global Kitobxon Chati</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('groups.index') }}" class="hover:text-white transition-colors flex items-center gap-1.5 group">
                            <span class="text-slate-600 group-hover:text-amber-400 transition-colors">›</span>
                            <span>Kitobxonlar guruhlari</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('live.index') }}" class="hover:text-white transition-colors flex items-center gap-1.5 group">
                            <span class="text-slate-600 group-hover:text-amber-400 transition-colors">›</span>
                            <span>Jonli efirlar & Darslar</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Col 3: Ma'lumot & Yordam -->
            <div class="space-y-3.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-white flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                    Ma'lumot & Yordam
                </h4>
                <ul class="space-y-2.5 text-xs text-slate-400">
                    <li>
                        <a href="{{ route('about') }}" class="hover:text-white transition-colors flex items-center gap-1.5 group">
                            <span class="text-slate-600 group-hover:text-indigo-400 transition-colors">›</span>
                            <span>Biz haqimizda</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('faq') }}" class="hover:text-white transition-colors flex items-center gap-1.5 group">
                            <span class="text-slate-600 group-hover:text-indigo-400 transition-colors">›</span>
                            <span>Ko'p so'raladigan savollar</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('contact') }}" class="hover:text-white transition-colors flex items-center gap-1.5 group">
                            <span class="text-slate-600 group-hover:text-indigo-400 transition-colors">›</span>
                            <span>Bog'lanish & Aloqa</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('contact', ['report' => 1]) }}" class="hover:text-rose-400 transition-colors flex items-center gap-1.5 group">
                            <span class="text-rose-500">🚩</span>
                            <span>Qoidabuzarlik ustidan shikoyat</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Col 4: Qoidalar & Maxfiylik -->
            <div class="space-y-3.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-white flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    Huquqiy Me'yorlar
                </h4>
                <ul class="space-y-2.5 text-xs text-slate-400">
                    <li>
                        <a href="{{ route('terms') }}" class="hover:text-white transition-colors flex items-center gap-1.5 group">
                            <span class="text-slate-600 group-hover:text-emerald-400 transition-colors">›</span>
                            <span>Foydalanish shartlari</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('privacy') }}" class="hover:text-white transition-colors flex items-center gap-1.5 group">
                            <span class="text-slate-600 group-hover:text-emerald-400 transition-colors">›</span>
                            <span>Maxfiylik siyosati</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('terms') }}" class="hover:text-amber-400 transition-colors flex items-center gap-1.5 group">
                            <span class="text-amber-500">⚖️</span>
                            <span>Ban me'yorlari jadvali</span>
                        </a>
                    </li>
                    <li>
                        <span class="text-[11px] text-slate-500 block pt-1 leading-relaxed">
                            Barcha intellektual mulk va mualliflik huquqlari himoyalangan.
                        </span>
                    </li>
                </ul>
            </div>

        </div>

        <!-- Bottom Copyright Bar -->
        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-slate-300">Kitobxon</span>
                <span>•</span>
                <span>© {{ date('Y') }} {{ __('site.common.all_rights') ?? 'Barcha huquqlar himoyalangan.' }}</span>
            </div>

            <div class="flex items-center gap-1.5 text-slate-400">
                <span>O'zbekiston bo'ylab kitobsevarlar hamjamiyati</span>
                <span>🇺🇿</span>
            </div>

            <!-- Scroll to top button -->
            <div>
                <button type="button" 
                        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 border border-white/10 text-slate-400 hover:text-white transition-colors text-xs group">
                    <span>Yuqoriga</span>
                    <span class="group-hover:-translate-y-0.5 transition-transform">↑</span>
                </button>
            </div>
        </div>
    </div>
</footer>
