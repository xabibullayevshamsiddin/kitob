@if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->hasRole('admin')))
<div x-data="{
    open: false,
    loading: false,
    coins: 10000,
    points: 2000,
    targetUserId: {{ auth()->id() }},
    targetUserName: 'O\'zimga ({{ addslashes(auth()->user()->name) }})',
    currentCoins: {{ (int) auth()->user()->coin_balance }},
    currentPoints: {{ (int) auth()->user()->total_points }},
    reason: 'Admin tomonidan hisob to\'ldirildi (Tekin)',
    activeTab: 'both', // 'coins', 'points', 'both'

    openModal(userId = null, userName = '') {
        if (userId) {
            this.targetUserId = userId;
            this.targetUserName = userName || ('Foydalanuvchi #' + userId);
        } else {
            this.targetUserId = {{ auth()->id() }};
            this.targetUserName = 'O\'zimga ({{ addslashes(auth()->user()->name) }})';
        }
        this.open = true;
    },

    setPreset(type, val) {
        if (type === 'coins') {
            this.coins = val;
            if (this.activeTab === 'points') this.activeTab = 'both';
        } else if (type === 'points') {
            this.points = val;
            if (this.activeTab === 'coins') this.activeTab = 'both';
        }
    },

    async submitTopup() {
        const c = (this.activeTab === 'coins' || this.activeTab === 'both') ? parseInt(this.coins || 0) : 0;
        const p = (this.activeTab === 'points' || this.activeTab === 'both') ? parseInt(this.points || 0) : 0;

        if (c <= 0 && p <= 0) {
            if (window.showToast) window.showToast('Iltimos, tanga yoki ball miqdorini kiriting.', 'error');
            return;
        }

        this.loading = true;

        try {
            const res = await fetch('{{ route('admin.balance.topup') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    target_user_id: this.targetUserId,
                    coins: c,
                    points: p,
                    reason: this.reason
                })
            });

            const data = await res.json();

            if (data.success) {
                if (window.showToast) window.showToast(data.message, 'success');
                if (window.KitobSound) window.KitobSound.play('achievement');

                if (this.targetUserId === {{ auth()->id() }}) {
                    this.currentCoins = data.new_coins;
                    this.currentPoints = data.new_points;

                    // Update global headers and live components
                    window.dispatchEvent(new CustomEvent('coins-awarded', {
                        detail: { coins: data.coins_added, newTotal: data.new_coins }
                    }));
                    window.dispatchEvent(new CustomEvent('points-awarded', {
                        detail: { points: data.points_added, newTotal: data.new_points }
                    }));
                    window.dispatchEvent(new CustomEvent('shop-balance-updated', {
                        detail: { newBalance: data.new_coins, newPoints: data.new_points }
                    }));
                }

                this.open = false;
            } else {
                if (window.showToast) window.showToast(data.message || 'Xatolik yuz berdi.', 'error');
            }
        } catch (err) {
            if (window.showToast) window.showToast('Server bilan bog\'lanishda xatolik yuz berdi.', 'error');
        } finally {
            this.loading = false;
        }
    }
}"
x-init="(() => {
    window.openAdminTopupModal = (userId, userName) => openModal(userId, userName);
})()"
@keydown.escape.window="open = false"
class="relative z-[9999]"
x-cloak>

    <!-- Modal Backdrop -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-ink-950/80 backdrop-blur-sm flex items-center justify-center p-4">

        <!-- Modal Content Card -->
        <div x-show="open"
             @click.outside="open = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="bg-ink-900 border-2 border-amber-500/40 rounded-2xl w-full max-w-lg shadow-[0_25px_60px_rgba(0,0,0,0.8)] overflow-hidden text-paper font-sans">

            <!-- Header -->
            <div class="px-5 py-4 border-b border-ink-border bg-gradient-to-r from-amber-500/15 via-amber-400/5 to-transparent flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-xl shadow-inner">
                        🪙
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-display font-bold text-base sm:text-lg text-paper">Admin Balansini To'ldirish</h3>
                            <span class="px-2 py-0.5 rounded-full bg-amber-500/20 border border-amber-500/30 text-[10px] font-mono font-bold text-amber-400">
                                TEKIN / CHEKSIZ
                            </span>
                        </div>
                        <p class="text-xs text-mist font-mono mt-0.5">
                            Qabul qiluvchi: <span class="text-paper font-semibold" x-text="targetUserName"></span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="open = false" class="w-8 h-8 rounded-lg text-mist hover:text-paper hover:bg-ink-800 flex items-center justify-center text-lg font-mono">
                    ✕
                </button>
            </div>

            <!-- Current Balances Display -->
            <div class="px-5 py-3 bg-ink-950/70 border-b border-ink-border/70 flex items-center justify-around text-xs font-mono">
                <div class="flex items-center gap-2">
                    <span class="text-mist">Hozirgi tangalar:</span>
                    <span class="text-amber-400 font-bold text-sm" x-text="currentCoins.toLocaleString() + ' 🪙'"></span>
                </div>
                <div class="w-px h-4 bg-ink-border"></div>
                <div class="flex items-center gap-2">
                    <span class="text-mist">Hozirgi ballar:</span>
                    <span class="text-cyan-400 font-bold text-sm" x-text="currentPoints.toLocaleString() + ' 🌟'"></span>
                </div>
            </div>

            <!-- Body -->
            <div class="p-5 space-y-4 text-xs sm:text-sm">

                <!-- Tabs: Coins / Points / Both -->
                <div class="grid grid-cols-3 gap-1.5 p-1 bg-ink-950 rounded-xl border border-ink-border">
                    <button type="button"
                            @click="activeTab = 'both'"
                            :class="activeTab === 'both' ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-mist hover:text-paper'"
                            class="py-1.5 rounded-lg text-xs font-mono transition-all">
                        Tanga + Ball
                    </button>
                    <button type="button"
                            @click="activeTab = 'coins'"
                            :class="activeTab === 'coins' ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-mist hover:text-paper'"
                            class="py-1.5 rounded-lg text-xs font-mono transition-all">
                        Faqat Tanga 🪙
                    </button>
                    <button type="button"
                            @click="activeTab = 'points'"
                            :class="activeTab === 'points' ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-mist hover:text-paper'"
                            class="py-1.5 rounded-lg text-xs font-mono transition-all">
                        Faqat Ball 🌟
                    </button>
                </div>

                <!-- Coins Block -->
                <div x-show="activeTab === 'coins' || activeTab === 'both'" class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-amber-400 text-xs flex items-center gap-1.5">
                            <span>🪙</span>
                            <span>Qo'shiladigan Tangalar (Do'kon va xaridlar uchun):</span>
                        </label>
                    </div>

                    <!-- Presets for coins -->
                    <div class="grid grid-cols-5 gap-1.5">
                        <button type="button" @click="setPreset('coins', 1000)" class="py-1 px-1 rounded-badge text-[11px] font-mono font-bold bg-ink-950 border border-ink-border hover:border-amber-400/50 hover:text-amber-400 transition-colors">+1K</button>
                        <button type="button" @click="setPreset('coins', 5000)" class="py-1 px-1 rounded-badge text-[11px] font-mono font-bold bg-ink-950 border border-ink-border hover:border-amber-400/50 hover:text-amber-400 transition-colors">+5K</button>
                        <button type="button" @click="setPreset('coins', 10000)" class="py-1 px-1 rounded-badge text-[11px] font-mono font-bold bg-ink-950 border border-ink-border hover:border-amber-400/50 hover:text-amber-400 transition-colors">+10K</button>
                        <button type="button" @click="setPreset('coins', 50000)" class="py-1 px-1 rounded-badge text-[11px] font-mono font-bold bg-ink-950 border border-ink-border hover:border-amber-400/50 hover:text-amber-400 transition-colors">+50K</button>
                        <button type="button" @click="setPreset('coins', 100000)" class="py-1 px-1 rounded-badge text-[11px] font-mono font-bold bg-ink-950 border border-ink-border hover:border-amber-400/50 hover:text-amber-400 transition-colors">+100K</button>
                    </div>

                    <div class="relative">
                        <input type="number"
                               x-model.number="coins"
                               min="0"
                               step="500"
                               class="ks-input font-mono font-bold text-amber-400 pl-8 text-sm"
                               placeholder="Tanga miqdori">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-sm">🪙</span>
                    </div>
                </div>

                <!-- Points Block -->
                <div x-show="activeTab === 'points' || activeTab === 'both'" class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-cyan-400 text-xs flex items-center gap-1.5">
                            <span>🌟</span>
                            <span>Qo'shiladigan Ballar (Daraja va reyting uchun):</span>
                        </label>
                    </div>

                    <!-- Presets for points -->
                    <div class="grid grid-cols-5 gap-1.5">
                        <button type="button" @click="setPreset('points', 500)" class="py-1 px-1 rounded-badge text-[11px] font-mono font-bold bg-ink-950 border border-ink-border hover:border-cyan-400/50 hover:text-cyan-400 transition-colors">+500</button>
                        <button type="button" @click="setPreset('points', 2000)" class="py-1 px-1 rounded-badge text-[11px] font-mono font-bold bg-ink-950 border border-ink-border hover:border-cyan-400/50 hover:text-cyan-400 transition-colors">+2K</button>
                        <button type="button" @click="setPreset('points', 5000)" class="py-1 px-1 rounded-badge text-[11px] font-mono font-bold bg-ink-950 border border-ink-border hover:border-cyan-400/50 hover:text-cyan-400 transition-colors">+5K</button>
                        <button type="button" @click="setPreset('points', 10000)" class="py-1 px-1 rounded-badge text-[11px] font-mono font-bold bg-ink-950 border border-ink-border hover:border-cyan-400/50 hover:text-cyan-400 transition-colors">+10K</button>
                        <button type="button" @click="setPreset('points', 50000)" class="py-1 px-1 rounded-badge text-[11px] font-mono font-bold bg-ink-950 border border-ink-border hover:border-cyan-400/50 hover:text-cyan-400 transition-colors">+50K</button>
                    </div>

                    <div class="relative">
                        <input type="number"
                               x-model.number="points"
                               min="0"
                               step="100"
                               class="ks-input font-mono font-bold text-cyan-400 pl-8 text-sm"
                               placeholder="Ball miqdori">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-sm">🌟</span>
                    </div>
                </div>

                <!-- Reason / Note -->
                <div class="space-y-1">
                    <label class="text-[11px] text-mist font-mono">Izoh / Sabab (ixtiyoriy):</label>
                    <input type="text"
                           x-model="reason"
                           class="ks-input text-xs font-mono"
                           placeholder="Admin tomonidan to'ldirildi">
                </div>
            </div>

            <!-- Footer -->
            <div class="px-5 py-4 border-t border-ink-border bg-ink-950/70 flex items-center justify-between">
                <button type="button" @click="open = false" class="ks-btn-ghost text-xs">
                    Bekor qilish
                </button>

                <button type="button"
                        @click="submitTopup()"
                        :disabled="loading"
                        class="px-5 py-2.5 rounded-btn bg-gradient-to-r from-amber-500 via-amber-400 to-amber-500 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-bold font-sans text-xs inline-flex items-center gap-2 shadow-lg shadow-amber-500/25 transition-all active:scale-95 disabled:opacity-50">
                    <span x-show="!loading">⚡ Hisobni bir zumda to'ldirish</span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="4" class="opacity-25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" class="opacity-75"></path></svg>
                        <span>To'ldirilmoqda...</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif
