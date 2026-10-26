<div class="max-w-6xl mx-auto space-y-8 pb-20 px-3 sm:px-6"
     x-data="{
         previewOpen: false,
         previewData: null,
         coinCount: {{ (int) $user->coin_balance }},
         init() {
             window.addEventListener('shop-balance-updated', (e) => {
                 const target = e.detail.newBalance;
                 const start = this.coinCount;
                 const duration = 600;
                 const startTime = performance.now();
                 const animate = (currentTime) => {
                     const elapsed = currentTime - startTime;
                     const progress = Math.min(elapsed / duration, 1);
                     this.coinCount = Math.floor(start + (target - start) * progress);
                     if (progress < 1) {
                         requestAnimationFrame(animate);
                     } else {
                         this.coinCount = target;
                     }
                 };
                 requestAnimationFrame(animate);
             });
         }
     }">

    {{-- Screen Reader Announcements --}}
    <div class="sr-only" aria-live="polite" id="shop-announcer">
        {{ trans('shop.title') }}. Balansingiz: {{ $user->coin_balance }} tanga.
    </div>

    {{-- ── 1. Header & Balance Banner ── --}}
    <div class="relative overflow-hidden rounded-panel border border-ink-border bg-ink-900/90 p-6 sm:p-8 backdrop-blur-md shadow-2xl">
        {{-- Subtle ambient glow --}}
        <div class="pointer-events-none absolute -right-16 -top-16 w-80 h-80 rounded-full bg-amber-500/10 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -left-16 -bottom-16 w-72 h-72 rounded-full bg-teal-500/10 blur-3xl" aria-hidden="true"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            {{-- Title & Subtitle --}}
            <div class="space-y-2 max-w-xl">
                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-500 dark:text-amber-400 font-mono text-[11px] font-bold uppercase tracking-wider">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                    </svg>
                    <span>{{ trans('shop.title') }}</span>
                </div>
                <h1 class="font-serif text-3xl sm:text-4xl font-bold text-paper tracking-tight">
                    {{ trans('shop.title') }}
                </h1>
                <p class="text-xs sm:text-sm text-mist leading-relaxed font-sans">
                    {{ trans('shop.subtitle') }}
                </p>
            </div>

            {{-- Balance Pills --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:flex items-stretch sm:items-center gap-3 w-full md:w-auto shrink-0">
                {{-- Coin Balance Pill --}}
                <div class="flex items-center gap-3 px-4 py-3 rounded-card bg-ink-950/80 border border-amber-500/30 shadow-lg shadow-black/10">
                    <div class="w-10 h-10 rounded-full bg-amber-500/20 border border-amber-500/40 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 7v10M15 9.5a3 3 0 0 0-6 0c0 3 6 2 6 5a3 3 0 0 1-6 0"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] font-mono uppercase tracking-wider text-stone-600 dark:text-mist">Mening tangalarim</div>
                        <div class="font-mono text-xl sm:text-2xl font-bold text-amber-700 dark:text-amber-400 leading-none mt-0.5 flex items-baseline gap-1 whitespace-nowrap">
                            <span x-text="coinCount.toLocaleString()">{{ number_format($user->coin_balance) }}</span>
                            <span class="text-xs text-amber-700/80 dark:text-amber-500/80 font-sans font-normal">tanga</span>
                        </div>
                    </div>
                </div>

                {{-- Available Points Pill (Clickable -> opens Exchange tab) --}}
                <button wire:click="setTab('exchange')"
                        type="button"
                        class="flex items-center gap-3 px-4 py-3 rounded-card bg-ink-950/80 border border-ink-border hover:border-purple-500/50 shadow-lg shadow-black/10 transition-all text-left group focus:outline-none"
                        title="{{ trans('shop.tabs.exchange') }}">
                    <div class="w-10 h-10 rounded-full bg-purple-500/15 border border-purple-500/30 group-hover:border-purple-500/60 flex items-center justify-center shrink-0 transition-colors">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] font-mono uppercase tracking-wider text-stone-600 dark:text-mist flex items-center gap-1">
                            <span class="truncate">{{ trans('shop.exchange.available_points') }}</span>
                            <span class="text-purple-600 dark:text-purple-400 group-hover:translate-x-0.5 transition-transform shrink-0">&rarr;</span>
                        </div>
                        <div class="font-mono text-xl sm:text-2xl font-bold text-paper leading-none mt-0.5 flex items-baseline gap-1 whitespace-nowrap">
                            <span>{{ number_format($user->available_points) }}</span>
                            <span class="text-xs text-stone-600 dark:text-mist font-sans font-normal">{{ trans('shop.points') }}</span>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </div>

    {{-- ── 2. Navigation Tabs (Horizontal Scrollable on Mobile) ── --}}
    <div class="flex items-center gap-2 border-b border-ink-border pb-2 overflow-x-auto no-scrollbar scroll-smooth">
        @php
            $tabs = [
                ['id' => 'showcase',   'label' => trans('shop.tabs.showcase'),   'icon' => 'sparkles'],
                ['id' => 'profile',    'label' => trans('shop.tabs.profile'),    'icon' => 'user'],
                ['id' => 'colors',     'label' => trans('shop.tabs.colors'),     'icon' => 'palette'],
                ['id' => 'consumable', 'label' => trans('shop.tabs.consumable'), 'icon' => 'zap'],
                ['id' => 'perks',      'label' => trans('shop.tabs.perks'),      'icon' => 'award'],
                ['id' => 'exchange',   'label' => trans('shop.tabs.exchange'),   'icon' => 'refresh'],
                ['id' => 'inventory',  'label' => trans('shop.tabs.inventory') . ' (' . $ownedItemsMap->count() . ')', 'icon' => 'box'],
                ['id' => 'wallet',     'label' => trans('shop.tabs.wallet'),     'icon' => 'history'],
            ];
        @endphp

        @foreach($tabs as $tab)
            @php $isActive = ($activeTab === $tab['id']); @endphp
            <button wire:click="setTab('{{ $tab['id'] }}')"
                    type="button"
                    class="relative inline-flex items-center gap-2 px-4 py-2.5 rounded-btn text-xs sm:text-sm whitespace-nowrap transition-all duration-base select-none shrink-0 {{ $isActive ? 'bg-amber-500 text-slate-950 font-bold shadow-md shadow-amber-500/20' : 'text-stone-700 dark:text-mist hover:text-paper hover:bg-ink-800 font-medium' }}">
                @if($tab['icon'] === 'sparkles')
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                @elseif($tab['icon'] === 'user')
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                @elseif($tab['icon'] === 'palette')
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
                @elseif($tab['icon'] === 'zap')
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                @elseif($tab['icon'] === 'award')
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                @elseif($tab['icon'] === 'refresh')
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
                @elseif($tab['icon'] === 'box')
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                @elseif($tab['icon'] === 'history')
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                @endif
                <span>{{ $tab['label'] }}</span>
            </button>
        @endforeach
    </div>

    {{-- ── 3. Filters (Search & Rarity) ── --}}
    @if(in_array($activeTab, ['profile', 'colors', 'consumable', 'perks']))
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            {{-- Search input --}}
            <div class="relative w-full sm:w-72">
                <input type="text"
                       wire:model.debounce.300ms="search"
                       placeholder="Buyumlarni qidirish..."
                       class="w-full pl-9 pr-4 py-2 text-xs rounded-btn bg-ink-800 border border-ink-border text-paper placeholder-stone-500 dark:placeholder-mist focus:border-amber-500 focus:outline-none transition-colors">
                <svg class="w-4 h-4 text-stone-500 dark:text-mist absolute left-3 top-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
            </div>

            {{-- Rarity Filter Pills --}}
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar w-full sm:w-auto">
                @php
                    $rarities = [
                        ['id' => 'all',       'label' => 'Barchasi'],
                        ['id' => 'common',    'label' => 'Oddiy'],
                        ['id' => 'rare',      'label' => 'Noyob'],
                        ['id' => 'epic',      'label' => 'Epik'],
                        ['id' => 'legendary', 'label' => 'Afsonaviy'],
                    ];
                @endphp
                @foreach($rarities as $r)
                    <button wire:click="$set('rarityFilter', '{{ $r['id'] }}')"
                            type="button"
                            class="px-3 py-1.5 rounded-full text-xs font-mono transition-colors duration-base select-none whitespace-nowrap {{ $rarityFilter === $r['id'] ? 'bg-amber-500/15 dark:bg-ink-700 border border-amber-500/60 text-amber-900 dark:text-paper font-bold shadow-sm' : 'bg-ink-900 border border-ink-border text-stone-700 dark:text-mist hover:text-paper hover:bg-ink-800 font-medium' }}">
                        {{ $r['label'] }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── 4. Tab Contents ── --}}

    {{-- 4.1. VITRINA (Showcase) TAB --}}
    @if($activeTab === 'showcase')
        @if(!$dailyRecommendation && $featuredItems->isEmpty() && $consumableHighlights->isEmpty())
            <div class="p-12 sm:p-16 text-center rounded-panel border border-ink-border bg-ink-900/60 space-y-4">
                <div class="w-16 h-16 rounded-full bg-amber-500/15 border border-amber-500/30 flex items-center justify-center mx-auto text-amber-400">
                    <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-xl font-serif font-bold text-paper">Do‘kon tez orada ochiladi</h3>
                    <p class="text-xs sm:text-sm text-mist max-w-md mx-auto leading-relaxed">
                        Hozirda do‘konimiz yangilanmoqda va tez orada siz uchun ajoyib mavzular, avatar ramkalari va maxsus imtiyozlar taqdim etiladi.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('books.catalog') }}" class="ks-btn-primary px-5 py-2.5 rounded-btn text-xs font-semibold inline-flex items-center gap-2">
                        <span>Kitoblar katalogi</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        @else
            <div class="space-y-10">
                {{-- Daily Recommendation Banner (Kunlik Tavsiya) --}}
                @if($dailyRecommendation)
                    @php
                        $isDailyOwned = $ownedItemsMap->has($dailyRecommendation->id);
                        $dailyPayload = $dailyRecommendation->payload ?? [];
                        $accentColor = $dailyPayload['accent_color'] ?? '#F59E0B';
                    @endphp
                    <div class="relative overflow-hidden rounded-panel border border-amber-500/40 bg-gradient-to-r from-ink-950 via-ink-900 to-amber-500/10 p-6 sm:p-8 shadow-xl">
                        <div class="absolute right-0 top-0 bottom-0 w-1/2 pointer-events-none opacity-20 bg-gradient-to-l from-amber-500/30 to-transparent blur-2xl"></div>

                        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                            <div class="space-y-3 max-w-2xl">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-800 dark:text-amber-400 font-mono text-[11px] font-bold uppercase tracking-wider">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <span>Kunlik maxsus tavsiya</span>
                                </div>
                                <h2 class="text-2xl sm:text-3xl font-serif font-bold text-paper">
                                    {{ $dailyRecommendation->name_text }}
                                </h2>
                                <p class="text-xs sm:text-sm text-stone-600 dark:text-mist leading-relaxed font-sans">
                                    {{ $dailyRecommendation->description_text }}
                                </p>
                                <div class="flex items-center gap-4 pt-1">
                                    <span class="px-2.5 py-0.5 rounded-badge font-mono text-[10px] uppercase font-bold tracking-wider {{ $dailyRecommendation->rarity === 'legendary' ? 'bg-amber-500/15 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 border border-amber-500/30 dark:border-amber-500/40' : ($dailyRecommendation->rarity === 'epic' ? 'bg-purple-500/15 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-500/30 dark:border-purple-500/40' : 'bg-blue-500/15 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 border border-blue-500/30 dark:border-blue-500/40') }}">
                                        {{ trans('shop.rarity_' . $dailyRecommendation->rarity) }}
                                    </span>
                                    <div class="flex items-center gap-1.5 font-mono text-base font-bold text-amber-700 dark:text-amber-400">
                                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v10M15 9.5a3 3 0 0 0-6 0c0 3 6 2 6 5a3 3 0 0 1-6 0"/></svg>
                                        <span>{{ $dailyRecommendation->price_coins }} tanga</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                @if(!$isDailyOwned)
                                    <button wire:click="openPreview({{ $dailyRecommendation->id }})"
                                            type="button"
                                            class="px-4 py-2.5 rounded-btn bg-ink-800 border border-ink-border text-paper hover:bg-ink-700 text-xs font-medium transition-colors inline-flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-stone-500 dark:text-mist" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <span>Sinab ko‘rish</span>
                                    </button>
                                    <button wire:click="openPurchaseModal({{ $dailyRecommendation->id }})"
                                            type="button"
                                            class="ks-btn-primary !text-white px-5 py-2.5 rounded-btn text-xs font-semibold inline-flex items-center gap-1.5">
                                        <span>Xarid qilish</span>
                                    </button>
                                @else
                                    <span class="px-4 py-2 rounded-badge bg-emerald-500/15 dark:bg-emerald-500/20 border border-emerald-500/40 text-emerald-700 dark:text-emerald-300 text-xs font-mono font-bold">
                                        Sizda mavjud
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Ommabop profil mavzulari --}}
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-serif text-xl font-bold text-paper">Mavzular to‘plami</h3>
                            <p class="text-xs text-stone-600 dark:text-mist font-sans">Profil, avatar va chat uslubingizni yaxlit o‘zgartiring</p>
                        </div>
                        <button wire:click="setTab('profile')" class="text-xs font-mono text-amber-700 dark:text-amber-400 hover:underline font-semibold">
                            Barchasini ko‘rish &rarr;
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($featuredItems as $item)
                            @include('livewire.shop.partials.item-card', ['item' => $item])
                        @endforeach
                    </div>
                </div>

                {{-- Foydali buyumlar --}}
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-serif text-xl font-bold text-paper">Foydali buyumlar</h3>
                            <p class="text-xs text-stone-600 dark:text-mist font-sans">Streakni saqlang, tezroq tanga yig‘ing va testlarni qayta topshiring</p>
                        </div>
                        <button wire:click="setTab('consumable')" class="text-xs font-mono text-amber-700 dark:text-amber-400 hover:underline font-semibold">
                            Barchasini ko‘rish &rarr;
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach($consumableHighlights as $item)
                            @include('livewire.shop.partials.item-card', ['item' => $item])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    {{-- 4.2. PROFIL / RANG / FOYDALI BUYUMLAR / IMTIYOZLAR KARTALARI RO'YXATI --}}
    @elseif(in_array($activeTab, ['profile', 'colors', 'consumable', 'perks']))
        @if($items->isEmpty())
            <div class="p-12 text-center rounded-panel border border-ink-border bg-ink-900/60 space-y-3">
                <svg class="w-12 h-12 text-mist mx-auto opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <h4 class="text-base font-serif font-bold text-paper">Buyumlar topilmadi</h4>
                <p class="text-xs text-mist max-w-sm mx-auto">Qidiruv yoki nodirlik filtrini o‘zgartirib ko‘ring.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($items as $item)
                    @include('livewire.shop.partials.item-card', ['item' => $item])
                @endforeach
            </div>
        @endif

    {{-- 4.3. MENING BUYUMLARIM (INVENTORY) --}}
    @elseif($activeTab === 'inventory')
        <div class="space-y-6">
            @if($items->isEmpty())
                <div class="p-12 text-center rounded-panel border border-ink-border bg-ink-900/60 space-y-4">
                    <div class="w-16 h-16 rounded-full bg-ink-800 border border-ink-border flex items-center justify-center mx-auto text-mist">
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-serif font-bold text-paper">Inventaringiz hozircha bo‘sh</h4>
                        <p class="text-xs text-mist max-w-sm mx-auto mt-1">Siz hali do‘kondan buyum sotib olmagansiz. Vitrinadagi mavzular va buyumlarni ko‘rib chiqing.</p>
                    </div>
                    <button wire:click="setTab('showcase')" class="ks-btn-primary px-5 py-2 text-xs font-semibold inline-flex items-center gap-1.5">
                        <span>Do‘konga o‘tish</span>
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($items as $userItem)
                        @php
                            $item = $userItem->shopItem;
                            if (!$item) continue;
                            $isEquipped = false;
                            if ($item->category === 'bundle') {
                                $isEquipped = in_array($item->id, $equippedMap, true);
                            } elseif ($item->slot) {
                                $isEquipped = isset($equippedMap[$item->slot]) && $equippedMap[$item->slot] == $item->id;
                            }
                        @endphp
                        <div class="relative flex flex-col justify-between p-5 rounded-card border bg-ink-900/90 transition-all duration-base {{ $isEquipped ? 'border-emerald-500/50 shadow-lg shadow-emerald-950/20' : 'border-ink-border hover:border-ink-border/80' }}">
                            {{-- Top badge and expiry --}}
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="px-2 py-0.5 rounded-badge font-mono text-[10px] uppercase font-bold tracking-wider {{ $item->rarity === 'legendary' ? 'bg-amber-500/15 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 border border-amber-500/30 dark:border-amber-500/40' : ($item->rarity === 'epic' ? 'bg-purple-500/15 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-500/30 dark:border-purple-500/40' : ($item->rarity === 'rare' ? 'bg-blue-500/15 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 border border-blue-500/30 dark:border-blue-500/40' : 'bg-slate-500/10 dark:bg-slate-500/20 text-slate-700 dark:text-slate-300 border border-slate-500/30')) }}">
                                        {{ trans('shop.rarity_' . $item->rarity) }}
                                    </span>
                                    @if($isEquipped)
                                        <span class="px-2 py-0.5 rounded-badge bg-emerald-500/15 dark:bg-emerald-500/20 border border-emerald-500/40 text-emerald-700 dark:text-emerald-400 font-mono text-[10px] font-bold">
                                            Kiyilgan
                                        </span>
                                    @endif
                                </div>

                                <div>
                                    <h4 class="font-serif font-bold text-base text-paper">{{ $item->name_text }}</h4>
                                    <p class="text-xs text-stone-600 dark:text-mist line-clamp-2 mt-1">{{ $item->description_text }}</p>
                                </div>

                                @if($item->is_consumable)
                                    <div class="text-xs font-mono text-amber-700 dark:text-amber-400">
                                        Mavjud miqdor: <span class="font-bold">{{ $item->key === 'streak_freeze' ? $freezeCount : $userItem->quantity }} ta</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="pt-4 mt-4 border-t border-ink-border flex items-center justify-between gap-2">
                                @if(!$item->is_consumable)
                                    @if($isEquipped)
                                        <button wire:click="unequipSlot('{{ $item->category === 'bundle' ? 'all' : $item->slot }}')"
                                                wire:loading.attr="disabled"
                                                type="button"
                                                class="w-full py-2 rounded-btn bg-ink-800 border border-ink-border text-stone-700 dark:text-mist hover:text-paper text-xs font-medium transition-colors">
                                            Yechish
                                        </button>
                                    @else
                                        <button wire:click="equipItem({{ $item->id }})"
                                                wire:loading.attr="disabled"
                                                type="button"
                                                class="w-full py-2 rounded-btn bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-colors shadow-sm">
                                            Jihozlash
                                        </button>
                                    @endif
                                @else
                                    <span class="text-xs text-stone-600 dark:text-mist font-mono">Avtomatik ishlatiladi</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    {{-- 4.4. BALL -> TANGA ALMASHTIRISH (EXCHANGE) --}}
    @elseif($activeTab === 'exchange')
        <div class="space-y-6 max-w-4xl mx-auto">
            {{-- Top Hero / Notice Card --}}
            <div class="relative overflow-hidden p-6 sm:p-8 rounded-panel bg-ink-900 border border-purple-500/30 shadow-2xl">
                <div class="absolute -right-16 -top-16 w-56 h-56 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-16 -bottom-16 w-56 h-56 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2 max-w-xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-500/15 border border-purple-500/30 text-purple-700 dark:text-purple-300 text-xs font-mono font-semibold">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
                            <span>{{ trans('shop.exchange.rate_label') }}: {{ $exchangeRate }} ball = 1 tanga</span>
                        </div>
                        <h3 class="font-serif text-2xl font-bold text-paper">{{ trans('shop.exchange.title') }}</h3>
                        <p class="text-xs sm:text-sm text-stone-600 dark:text-mist leading-relaxed font-sans">{{ trans('shop.exchange.subtitle') }}</p>
                    </div>

                    {{-- Available & Remaining metrics --}}
                    <div class="grid grid-cols-2 gap-3 shrink-0">
                        <div class="p-3.5 rounded-card bg-ink-950/80 border border-ink-border text-center">
                            <div class="text-[10px] font-mono uppercase text-stone-600 dark:text-mist">{{ trans('shop.exchange.available_points') }}</div>
                            <div class="font-mono text-xl font-bold text-purple-700 dark:text-purple-300 mt-0.5">{{ number_format($availablePoints) }}</div>
                            <div class="text-[9px] font-mono text-stone-500 dark:text-mist/80 mt-0.5">{{ trans('shop.points') }}</div>
                        </div>
                        <div class="p-3.5 rounded-card bg-ink-950/80 border border-ink-border text-center">
                            <div class="text-[10px] font-mono uppercase text-stone-600 dark:text-mist">{{ trans('shop.exchange.daily_limit') }}</div>
                            <div class="font-mono text-xl font-bold text-amber-700 dark:text-amber-400 mt-0.5">{{ number_format($dailyRemainingPoints) }}</div>
                            <div class="text-[9px] font-mono text-stone-500 dark:text-mist/80 mt-0.5">qoldi / {{ $exchangeDailyLimit }}</div>
                        </div>
                    </div>
                </div>

                {{-- Daily limit progress bar --}}
                <div class="relative z-10 mt-6 pt-5 border-t border-ink-border/60">
                    @php
                        $usedPercent = $exchangeDailyLimit > 0 ? min(100, round((($exchangeDailyLimit - $dailyRemainingPoints) / $exchangeDailyLimit) * 100)) : 0;
                    @endphp
                    <div class="flex items-center justify-between text-xs font-mono text-stone-600 dark:text-mist mb-1.5">
                        <span>Bugungi limit sarflanishi:</span>
                        <span class="text-paper font-semibold">{{ $exchangeDailyLimit - $dailyRemainingPoints }} / {{ $exchangeDailyLimit }} ball ({{ $usedPercent }}%)</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-ink-950 overflow-hidden border border-ink-border/50">
                        <div class="h-full bg-gradient-to-r from-purple-500 to-amber-500 rounded-full transition-all duration-500" style="width: {{ $usedPercent }}%;"></div>
                    </div>
                </div>
            </div>

            {{-- Interactive Exchange Card --}}
            <div class="p-6 sm:p-8 rounded-panel bg-ink-900 border border-ink-border space-y-6"
                 x-data="{
                     inputPoints: @entangle('exchangePoints'),
                     rate: {{ $exchangeRate }},
                     available: {{ $availablePoints }},
                     dailyLimitLeft: {{ $dailyRemainingPoints }},
                     minPoints: {{ $exchangeMinPoints }},
                     get coinsOut() {
                         let p = parseInt(this.inputPoints) || 0;
                         return Math.floor(p / this.rate);
                     },
                     get isValid() {
                         let p = parseInt(this.inputPoints) || 0;
                         return p >= this.minPoints && p <= this.available && p <= this.dailyLimitLeft;
                     }
                 }">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
                    {{-- Left Input Card: Points --}}
                    <div class="md:col-span-2 p-5 rounded-card bg-ink-950 border border-ink-border space-y-3">
                        <div class="flex items-center justify-between">
                            <label for="exchange_input" class="text-xs font-mono uppercase tracking-wider text-stone-600 dark:text-mist flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-purple-500 dark:bg-purple-400"></span>
                                <span>{{ trans('shop.exchange.points_to_exchange') }}</span>
                            </label>
                            <span class="text-[11px] font-mono text-stone-500 dark:text-mist">min: {{ $exchangeMinPoints }}</span>
                        </div>
                        <div class="relative">
                            <input id="exchange_input"
                                   type="number"
                                   min="{{ $exchangeMinPoints }}"
                                   max="{{ min($availablePoints, $dailyRemainingPoints) }}"
                                   step="{{ $exchangeRate }}"
                                   wire:model.debounce.250ms="exchangePoints"
                                   x-model="inputPoints"
                                   class="w-full font-mono text-2xl font-bold bg-ink-900 border border-ink-border rounded-btn px-4 py-2.5 text-paper focus:border-purple-500 focus:outline-none transition-colors"
                                   placeholder="{{ $exchangeMinPoints }}">
                            <span class="absolute right-3.5 top-3.5 font-mono text-xs text-stone-500 dark:text-mist font-semibold">{{ trans('shop.points') }}</span>
                        </div>

                        {{-- Quick amount chips --}}
                        <div class="flex items-center gap-1.5 flex-wrap pt-1">
                            <span class="text-[10px] font-mono text-stone-600 dark:text-mist">{{ trans('shop.exchange.quick_buttons') }}:</span>
                            @foreach([$exchangeMinPoints, $exchangeRate * 2, $exchangeRate * 5, $exchangeRate * 10, $exchangeRate * 20] as $preset)
                                <button type="button"
                                        wire:click="setExchangePoints({{ $preset }})"
                                        @click="inputPoints = {{ $preset }}"
                                        class="px-2 py-0.5 rounded text-[11px] font-mono transition-colors {{ $exchangePoints === $preset ? 'bg-purple-500/20 text-purple-800 dark:text-purple-300 border border-purple-500/40 font-bold' : 'bg-ink-800 text-stone-700 dark:text-mist hover:text-paper border border-ink-border' }}">
                                    {{ $preset }}
                                </button>
                            @endforeach
                            @if($dailyRemainingPoints > 0 && $availablePoints > 0)
                                @php $maxPossible = min($availablePoints, $dailyRemainingPoints); @endphp
                                <button type="button"
                                        wire:click="setExchangePoints({{ $maxPossible }})"
                                        @click="inputPoints = {{ $maxPossible }}"
                                        class="px-2 py-0.5 rounded text-[11px] font-mono bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-500/30 hover:bg-amber-500/25">
                                    Maks ({{ $maxPossible }})
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Middle Converter Indicator --}}
                    <div class="md:col-span-1 flex flex-col items-center justify-center text-center py-2 sm:py-0">
                        <div class="w-11 h-11 rounded-full bg-amber-500/15 border-2 border-amber-500/40 flex items-center justify-center text-amber-600 dark:text-amber-400 shadow-lg shadow-amber-500/10">
                            {{-- Desktopda: O'ngga qaratilgan strelka (Ball -> Tanga) --}}
                            <svg class="w-5 h-5 hidden md:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                            {{-- Mobilda: Pastga qaratilgan strelka (Ball v Tanga) --}}
                            <svg class="w-5 h-5 block md:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <polyline points="19 12 12 19 5 12"></polyline>
                            </svg>
                        </div>
                        <span class="inline-block font-mono text-[11px] font-bold text-amber-800 dark:text-amber-300 mt-2 bg-amber-500/10 border border-amber-500/30 px-2.5 py-0.5 rounded-full whitespace-nowrap">
                            {{ $exchangeRate }} ball = 1 tanga
                        </span>
                    </div>

                    {{-- Right Output Card: Coins to receive --}}
                    <div class="md:col-span-2 p-5 rounded-card bg-ink-950 border border-amber-500/30 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono uppercase tracking-wider text-stone-600 dark:text-mist flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500 dark:bg-amber-400"></span>
                                <span>{{ trans('shop.exchange.you_will_receive') }}</span>
                            </span>
                            <span class="text-[10px] font-mono text-amber-700 dark:text-amber-400">100% kafolatlangan</span>
                        </div>
                        <div class="p-3 rounded-btn bg-ink-900 border border-amber-500/20 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-amber-500/20 border border-amber-500/40 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v10M15 9.5a3 3 0 0 0-6 0c0 3 6 2 6 5a3 3 0 0 1-6 0"/></svg>
                                </div>
                                <span class="font-mono text-2xl font-bold text-amber-700 dark:text-amber-400" x-text="coinsOut">{{ $coinsToReceive }}</span>
                            </div>
                            <span class="font-sans text-xs text-amber-700 dark:text-amber-300 font-semibold uppercase tracking-wider">{{ trans('shop.coins') }}</span>
                        </div>
                        <div class="text-[11px] font-mono text-stone-600 dark:text-mist">
                            Balansingiz: <span class="text-paper font-bold">{{ number_format($user->coin_balance) }}</span> &rarr; <span class="text-amber-700 dark:text-amber-400 font-bold" x-text="({{ (int) $user->coin_balance }} + coinsOut).toLocaleString()">{{ number_format($user->coin_balance + $coinsToReceive) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Validation message alerts --}}
                @if($exchangePoints < $exchangeMinPoints)
                    <div class="p-3 rounded-btn bg-amber-500/10 border border-amber-500/30 text-amber-800 dark:text-amber-300 text-xs font-mono flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span>Minimal almashtirish miqdori kamida {{ $exchangeMinPoints }} ball bo‘lishi lozim.</span>
                    </div>
                @elseif($exchangePoints > $availablePoints)
                    <div class="p-3 rounded-btn bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs font-mono flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span>Sizda yetarli ball mavjud emas. Almashtirish uchun mavjud: {{ number_format($availablePoints) }} ball.</span>
                    </div>
                @elseif($exchangePoints > $dailyRemainingPoints)
                    <div class="p-3 rounded-btn bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs font-mono flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span>Bugungi kunlik almashtirish chegarasidan oshib ketdi. Qolgan chegara: {{ number_format($dailyRemainingPoints) }} ball.</span>
                    </div>
                @endif

                {{-- Action Button --}}
                @php
                    $isExchangeDisabled = ($exchangePoints < $exchangeMinPoints) || ($exchangePoints > $availablePoints) || ($exchangePoints > $dailyRemainingPoints);
                @endphp
                <div class="pt-2 flex items-center justify-between gap-4">
                    <div class="text-xs text-stone-600 dark:text-mist font-sans hidden sm:block">
                        «Tasdiqlash» tugmasi bosilganda ballaringiz bir zumda tangaga aylanadi.
                    </div>
                    <button wire:click="confirmExchange"
                            wire:loading.attr="disabled"
                            @if($isExchangeDisabled) disabled @endif
                            type="button"
                            class="w-full sm:w-auto px-8 py-3 rounded-btn font-mono text-xs font-bold uppercase tracking-wider transition-all duration-base flex items-center justify-center gap-2 {{ $isExchangeDisabled ? 'bg-ink-800 text-stone-500 dark:text-mist/60 border border-ink-border cursor-not-allowed' : 'ks-btn-gold !text-slate-950 font-bold shadow-lg shadow-amber-500/20 active:scale-95' }}">
                        <span wire:loading.remove wire:target="confirmExchange">{{ trans('shop.exchange.btn_submit') }} (+{{ $coinsToReceive }} {{ trans('shop.coins') }})</span>
                        <span wire:loading wire:target="confirmExchange" class="inline-flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>
                            <span>Almashtirilmoqda...</span>
                        </span>
                    </button>
                </div>
            </div>

            {{-- Rules / Guarantee Notice Grid --}}
            <div class="p-6 rounded-panel bg-ink-900/60 border border-ink-border space-y-4">
                <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-stone-600 dark:text-mist flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>{{ trans('shop.exchange.notice_title') }}</span>
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-sans text-stone-600 dark:text-mist leading-relaxed">
                    <div class="p-3.5 rounded-card bg-ink-950/60 border border-ink-border/60 space-y-1">
                        <div class="text-paper font-semibold flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Reyting xavfsiz</span>
                        </div>
                        <p>{{ trans('shop.exchange.notice_1') }}</p>
                    </div>
                    <div class="p-3.5 rounded-card bg-ink-950/60 border border-ink-border/60 space-y-1">
                        <div class="text-paper font-semibold flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Bir tomonlama</span>
                        </div>
                        <p>{{ trans('shop.exchange.notice_2') }}</p>
                    </div>
                    <div class="p-3.5 rounded-card bg-ink-950/60 border border-ink-border/60 space-y-1">
                        <div class="text-paper font-semibold flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Kunlik qayta yangilanish</span>
                        </div>
                        <p>{{ trans('shop.exchange.notice_3') }}</p>
                    </div>
                </div>
            </div>
        </div>

    {{-- 4.5. HAMYON VA TRANZAKSIYALAR TARIXI (Wallet) --}}
    @elseif($activeTab === 'wallet')
        <div class="space-y-6">
            {{-- Monthly Summary Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-5 rounded-panel border border-ink-border bg-ink-900/80">
                    <div class="text-xs font-mono uppercase text-stone-600 dark:text-mist">Joriy balans</div>
                    <div class="font-mono text-2xl font-bold text-amber-700 dark:text-amber-400 mt-1 flex items-baseline gap-1">
                        <span>{{ number_format($user->coin_balance) }}</span>
                        <span class="text-xs text-amber-700/80 dark:text-amber-500/80 font-normal">tanga</span>
                    </div>
                </div>
                <div class="p-5 rounded-panel border border-ink-border bg-ink-900/80">
                    <div class="text-xs font-mono uppercase text-stone-600 dark:text-mist">Bu oy ishlangan tangalar</div>
                    <div class="font-mono text-2xl font-bold text-emerald-700 dark:text-emerald-400 mt-1 flex items-baseline gap-1">
                        <span>+{{ number_format($monthEarned) }}</span>
                        <span class="text-xs text-emerald-700/80 dark:text-emerald-500/80 font-normal">tanga</span>
                    </div>
                </div>
                <div class="p-5 rounded-panel border border-ink-border bg-ink-900/80">
                    <div class="text-xs font-mono uppercase text-stone-600 dark:text-mist">Bu oy sarflangan tangalar</div>
                    <div class="font-mono text-2xl font-bold text-rose-700 dark:text-rose-400 mt-1 flex items-baseline gap-1">
                        <span>-{{ number_format($monthSpent) }}</span>
                        <span class="text-xs text-rose-700/80 dark:text-rose-500/80 font-normal">tanga</span>
                    </div>
                </div>
            </div>

            {{-- Telegram Stars Tanga To'plamlari --}}
            <div class="p-5 sm:p-6 rounded-panel border border-ink-border bg-ink-900/80 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-bold">
                            ⭐️
                        </span>
                        <div>
                            <h3 class="font-serif font-bold text-lg text-paper">Telegram Stars orqali tanga xarid qilish</h3>
                            <p class="text-xs text-stone-600 dark:text-mist">Telegram yulduzlari evaziga bir zumda tanga hisobingizni to'ldiring</p>
                        </div>
                    </div>

                    @if($starsPaymentsEnabled)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-mono bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Faol
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-mono bg-slate-500/10 text-slate-700 dark:text-slate-400 border border-slate-500/20">
                            🔒 Tez kunda
                        </span>
                    @endif
                </div>

                @if($starsPaymentsEnabled)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 pt-2">
                        @foreach($starPacks as $pKey => $pData)
                            <div class="p-4 rounded-2xl bg-ink-950 border border-ink-border hover:border-amber-500/30 flex flex-col justify-between transition-all group">
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-800 dark:text-amber-400 border border-amber-500/20 font-bold">
                                            {{ $pData['badge'] }}
                                        </span>
                                        <span class="text-xs font-bold text-paper font-mono flex items-center gap-1">
                                            {{ $pData['stars'] }} ⭐️
                                        </span>
                                    </div>
                                    <h4 class="text-sm font-bold text-paper font-serif pt-1 flex items-center gap-1.5">
                                        <span>🪙 {{ $pData['coins'] }}</span>
                                        <span class="text-xs text-stone-600 dark:text-mist font-normal">tanga</span>
                                    </h4>
                                    <p class="text-[11px] text-stone-600 dark:text-mist leading-relaxed line-clamp-2">
                                        {{ $pData['description'] }}
                                    </p>
                                </div>

                                <div class="pt-3">
                                    <button wire:click="buyCoinPackWithStars('{{ $pKey }}')"
                                            wire:loading.attr="disabled"
                                            class="w-full py-2 px-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center justify-center gap-1.5 shadow-md shadow-amber-500/20 transition-all active:scale-98">
                                        <span>{{ $pData['stars'] }} ⭐️ bilan olish</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 rounded-2xl bg-ink-950 border border-ink-border/60 text-xs text-stone-600 dark:text-mist flex items-start gap-3">
                        <span class="text-base text-amber-600 dark:text-amber-400 mt-0.5">ℹ️</span>
                        <div class="space-y-1">
                            <p class="text-stone-700 dark:text-slate-300 font-medium">To'lov integratsiyasi xavfsiz bayroq ortida tayyorlangan (Master switch: <code>TELEGRAM_PAYMENTS_ENABLED=false</code>).</p>
                            <p class="text-[11px] text-stone-600 dark:text-slate-400">Stars orqali haqiqiy to'lovlarni ochish mijozning soliq, qaytarish siyosati va yosh chegarasi bo'yicha biznes qaroriga binoan <code>.env</code> faylida <code>true</code> qilinishi kifoya.</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Transactions Table with filter --}}
            <div class="p-5 sm:p-6 rounded-panel border border-ink-border bg-ink-900/80 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-serif font-bold text-lg text-paper">Tanga amallari tarixi</h3>
                    <div class="flex items-center gap-1">
                        <button wire:click="$set('walletFilter', 'all')"
                                type="button"
                                class="px-2.5 py-1 text-xs rounded-btn font-mono {{ $walletFilter === 'all' ? 'bg-ink-700 text-paper font-bold' : 'text-stone-600 dark:text-mist hover:text-paper' }}">
                            Barchasi
                        </button>
                        <button wire:click="$set('walletFilter', 'earned')"
                                type="button"
                                class="px-2.5 py-1 text-xs rounded-btn font-mono {{ $walletFilter === 'earned' ? 'bg-ink-700 text-emerald-700 dark:text-emerald-400 font-bold' : 'text-stone-600 dark:text-mist hover:text-paper' }}">
                            Kirim (+)
                        </button>
                        <button wire:click="$set('walletFilter', 'spent')"
                                type="button"
                                class="px-2.5 py-1 text-xs rounded-btn font-mono {{ $walletFilter === 'spent' ? 'bg-ink-700 text-rose-700 dark:text-rose-400 font-bold' : 'text-stone-600 dark:text-mist hover:text-paper' }}">
                            Chiqim (-)
                        </button>
                    </div>
                </div>

                @if($transactions->isEmpty())
                    <p class="py-8 text-center text-xs text-stone-600 dark:text-mist font-sans">Tranzaksiyalar mavjud emas.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-ink-border text-stone-600 dark:text-mist font-mono uppercase text-[10px]">
                                <tr>
                                    <th class="py-2.5 pr-4">Sana</th>
                                    <th class="py-2.5 px-4">Tavsif / Manba</th>
                                    <th class="py-2.5 px-4 text-right">Miqdor</th>
                                    <th class="py-2.5 pl-4 text-right">Balans</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-border/50 font-mono">
                                @foreach($transactions as $tx)
                                    <tr class="hover:bg-ink-800/40 transition-colors">
                                        <td class="py-3 pr-4 text-stone-600 dark:text-mist whitespace-nowrap">
                                            {{ $tx->created_at ? $tx->created_at->format('d.m.Y H:i') : '—' }}
                                        </td>
                                        <td class="py-3 px-4 font-sans text-paper">
                                            <div class="font-medium">{{ $tx->description ?: 'Tanga amali' }}</div>
                                            <div class="text-[10px] font-mono text-stone-600 dark:text-mist uppercase tracking-wider">{{ $tx->source }}</div>
                                        </td>
                                        <td class="py-3 px-4 text-right font-bold whitespace-nowrap {{ $tx->coins > 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                                            {{ $tx->coins > 0 ? '+' . $tx->coins : $tx->coins }}
                                        </td>
                                        <td class="py-3 pl-4 text-right text-stone-600 dark:text-mist whitespace-nowrap">
                                            {{ $tx->balance_after !== null ? number_format($tx->balance_after) : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ── 5. PREVIEW MODAL (Client-side interactive inspection) ── --}}
    @if($previewItem)
        @php
            $visualTheme = \App\Services\Gamification\ProfileVisualService::THEMES[$previewItem->key] ?? null;
            $pPayload = $previewItem->payload ?? [];
            $previewAccent = $previewItem->safeColor('accent_color', $previewItem->safeColor('color', '#F59E0B'));
            $pGradient = $visualTheme['banner_gradient'] ?? ($pPayload['banner_gradient'] ?? 'bg-gradient-to-r from-ink-900 via-ink-950 to-ink-950');
            $pAmbient = $visualTheme['banner_ambient'] ?? ($pPayload['banner_ambient'] ?? 'none');
            $pAvatarRing = $visualTheme['avatar_ring'] ?? ($pPayload['avatar_ring'] ?? 'ring-2 ring-amber-400/80');
        @endphp
        <div x-data="{
                closing: false,
                closeModal() {
                    if (this.closing) return;
                    this.closing = true;
                    setTimeout(() => { $wire.closePreview(); }, 300);
                }
             }"
             @keydown.escape.window="closeModal()"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/75 backdrop-blur-md overflow-y-auto"
             :class="closing ? 'ks-backdrop-fade-out-anim' : 'ks-backdrop-fade-anim'"
             role="dialog" aria-modal="true" aria-labelledby="preview-modal-title">
            <div class="relative w-full max-w-lg rounded-panel border border-slate-200 dark:border-ink-border/90 bg-white dark:bg-ink-900/95 p-6 shadow-2xl backdrop-blur-xl space-y-6 my-auto"
                 :class="closing ? 'ks-blur-out-dialog-anim' : 'ks-blur-dialog-anim'">
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-ink-border pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full shadow-sm" style="background-color: {{ $previewAccent }}"></span>
                        <h3 id="preview-modal-title" class="font-serif font-bold text-lg text-paper">
                            {{ $previewItem->name_text }} (Ko‘rinishini sinash)
                        </h3>
                    </div>
                    <button @click="closeModal()" type="button" class="text-mist hover:text-paper p-1 rounded-btn" aria-label="Yopish">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Interactive Mock Preview Card --}}
                <div class="relative overflow-hidden rounded-card border border-slate-200 dark:border-ink-border p-5 space-y-4 {{ $pGradient }}"
                     style="background-image: {{ $pAmbient !== 'none' ? $pAmbient : '' }}">
                    <div class="flex items-center gap-4">
                        <img src="{{ $user->avatar_url }}"
                             class="w-14 h-14 rounded-full object-cover shrink-0 {{ $pAvatarRing }}"
                             alt="{{ $user->name }}">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-serif font-bold text-base text-paper" style="color: {{ $previewItem->slot === 'name_color' ? $previewAccent : '' }}">
                                    {{ $user->name }}
                                </span>
                                @if($previewItem->slot === 'title')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-badge text-[10px] font-mono font-bold"
                                          style="background-color: {{ $previewAccent }}25; color: {{ $previewAccent }}; border: 1px solid {{ $previewAccent }}50;">
                                        <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        <span>{{ $previewItem->name_text }}</span>
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-mist font-mono">{{ '@' . $user->username }}</p>
                        </div>
                    </div>

                    {{-- Mock Chat Bubble Preview --}}
                    <div class="pt-2">
                        <div class="text-[10px] font-mono uppercase tracking-wider text-mist mb-1.5">Chatdagi xabar ko‘rinishi:</div>
                        <div class="inline-block p-3 rounded-card text-xs font-sans max-w-sm bg-white/90 dark:bg-slate-900/90 border shadow-sm text-paper"
                             style="border-color: {{ $previewAccent }}60;">
                            Salom! Bu yangi profil uslubingiz va vizual ko‘rinishingiz namunasidir.
                        </div>
                    </div>
                </div>

                <div class="text-xs text-mist font-sans">
                    Ushbu ko‘rinish faqat brauzerda sinov tarzida ko‘rsatilmoqda. Serverga hech narsa yozilmadi.
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button wire:click="closePreview" type="button" class="px-4 py-2 rounded-btn bg-ink-800 border border-ink-border text-xs text-stone-700 dark:text-mist hover:text-paper">
                        Yopish
                    </button>
                    @if(!$ownedItemsMap->has($previewItem->id))
                        <button wire:click="openPurchaseModal({{ $previewItem->id }})" type="button" class="ks-btn-gold !text-slate-950 font-bold px-5 py-2 rounded-btn text-xs">
                            Sotib olish ({{ $previewItem->price_coins }} tanga)
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ── 6. PURCHASE CONFIRMATION MODAL ── --}}
    @if($purchaseItem)
        @php
            $itemCost = (int) $purchaseItem->price_coins * $purchaseQuantity;
            $balanceAfter = $user->coin_balance - $itemCost;
            $hasEnough = ($balanceAfter >= 0);
            $missingCoins = max(0, $itemCost - $user->coin_balance);
            $rate = (int) config('shop.exchange_rate', 15);
            $neededPoints = $missingCoins * $rate;
            $canCoverWithPoints = (!$hasEnough && $user->available_points >= $neededPoints);
            $payload = $purchaseItem->payload ?? [];
            $badgeColor = $purchaseItem->safeColor('badge_color', $purchaseItem->safeColor('color', $purchaseItem->safeColor('accent_color', '#FBBF24')));
        @endphp
        <div x-data="{
                closing: false,
                closeModal() {
                    if (this.closing) return;
                    this.closing = true;
                    setTimeout(() => { $wire.closePurchaseModal(); }, 300);
                }
             }"
             @keydown.escape.window="closeModal()"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/75 backdrop-blur-md overflow-y-auto"
             :class="closing ? 'ks-backdrop-fade-out-anim' : 'ks-backdrop-fade-anim'"
             role="dialog" aria-modal="true" aria-labelledby="purchase-modal-title">
            <div class="relative w-full max-w-md rounded-panel border border-ink-border/90 bg-ink-900/95 p-6 shadow-[0_30px_70px_rgba(0,0,0,0.95)] backdrop-blur-xl space-y-5 my-auto"
                 :class="closing ? 'ks-blur-out-dialog-anim' : 'ks-blur-dialog-anim'">
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-ink-border pb-3">
                    <h3 id="purchase-modal-title" class="font-serif font-bold text-lg text-paper flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 7v10M15 9.5a3 3 0 0 0-6 0c0 3 6 2 6 5a3 3 0 0 1-6 0"/>
                        </svg>
                        <span>Xaridni tasdiqlash</span>
                    </h3>
                    <button @click="closeModal()" type="button" class="text-stone-500 dark:text-mist hover:text-paper p-1 rounded-btn" aria-label="Bekor qilish">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Item Details Card --}}
                <div class="p-4 rounded-card bg-ink-950/70 border border-ink-border space-y-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <span class="px-2 py-0.5 rounded-badge font-mono text-[9px] uppercase font-bold {{ $purchaseItem->rarity === 'legendary' ? 'bg-amber-500/15 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 border border-amber-500/30 dark:border-amber-500/40' : ($purchaseItem->rarity === 'epic' ? 'bg-purple-500/15 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-500/30 dark:border-purple-500/40' : 'bg-blue-500/15 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 border border-blue-500/30 dark:border-blue-500/40') }}">
                                {{ trans('shop.rarity_' . $purchaseItem->rarity) }}
                            </span>
                            <h4 class="font-serif font-bold text-base text-paper">{{ $purchaseItem->name_text }}</h4>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="inline-flex items-center gap-1 font-mono font-bold text-amber-700 dark:text-amber-400 text-sm">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="M12 7v10M15 9.5a3 3 0 0 0-6 0c0 3 6 2 6 5a3 3 0 0 1-6 0"/>
                                </svg>
                                <span>{{ $purchaseItem->price_coins }} tanga</span>
                            </div>
                        </div>
                    </div>

                    {{-- Visual Item Preview Banner inside modal --}}
                    @if($purchaseItem->slot === 'title')
                        <div class="pt-1">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-badge text-xs font-mono font-bold shadow-sm"
                                  style="background-color: {{ $badgeColor }}20; color: {{ $badgeColor }}; border: 1px solid {{ $badgeColor }}50;">
                                <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <span>{{ $purchaseItem->name_text }}</span>
                            </span>
                        </div>
                    @elseif($purchaseItem->slot === 'name_color')
                        <div class="pt-1">
                            <span class="font-serif font-bold text-sm tracking-wide" style="color: {{ $badgeColor }}; text-shadow: 0 0 12px {{ $badgeColor }}50;">
                                {{ $user->name }} (Rang namunasi)
                            </span>
                        </div>
                    @elseif($purchaseItem->slot === 'accent')
                        <div class="pt-1 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full border border-white/20 shadow-sm" style="background-color: {{ $badgeColor }}"></span>
                            <span class="text-xs text-stone-600 dark:text-mist font-mono">{{ $purchaseItem->name_text }} urg'u rangi</span>
                        </div>
                    @endif

                    <p class="text-xs text-stone-600 dark:text-mist font-sans leading-relaxed pt-1 border-t border-ink-border/50">
                        {{ $purchaseItem->description_text }}
                    </p>
                </div>

                {{-- Quantity Selector (if consumable) --}}
                @if($purchaseItem->is_consumable && $purchaseItem->max_quantity > 1)
                    <div class="flex items-center justify-between p-3 rounded-card bg-ink-800/60 border border-ink-border">
                        <span class="text-xs font-mono text-stone-600 dark:text-mist">Miqdori:</span>
                        <div class="flex items-center gap-3">
                            <button type="button"
                                    wire:click="$set('purchaseQuantity', {{ max(1, $purchaseQuantity - 1) }})"
                                    class="w-7 h-7 rounded-btn bg-ink-700 text-paper hover:bg-ink-600 flex items-center justify-center font-bold">
                                -
                            </button>
                            <span class="font-mono font-bold text-paper text-sm">{{ $purchaseQuantity }}</span>
                            <button type="button"
                                    wire:click="$set('purchaseQuantity', {{ min($purchaseItem->max_quantity, $purchaseQuantity + 1) }})"
                                    class="w-7 h-7 rounded-btn bg-ink-700 text-paper hover:bg-ink-600 flex items-center justify-center font-bold">
                                +
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Calculation Breakdown --}}
                <div class="space-y-2 p-3.5 rounded-card bg-ink-950/60 border border-ink-border/70 text-xs font-mono">
                    <div class="flex justify-between items-center text-stone-600 dark:text-mist">
                        <span>Joriy balansingiz:</span>
                        <span class="text-paper font-semibold">{{ number_format($user->coin_balance) }} tanga</span>
                    </div>
                    <div class="flex justify-between items-center text-stone-600 dark:text-mist">
                        <span>Xarid narxi:</span>
                        <span class="text-amber-700 dark:text-amber-400 font-bold">{{ number_format($itemCost) }} tanga</span>
                    </div>
                    <div class="pt-2 border-t border-ink-border/70 flex justify-between items-center font-bold">
                        @if($hasEnough)
                            <span class="text-stone-600 dark:text-mist">Xariddan keyin qoladi:</span>
                            <span class="text-emerald-700 dark:text-emerald-400 text-sm font-bold">{{ number_format($balanceAfter) }} tanga</span>
                        @else
                            <span class="text-rose-700 dark:text-rose-400">Yetishmayotgan tanga:</span>
                            <span class="text-rose-700 dark:text-rose-400 text-sm font-bold">+{{ number_format($missingCoins) }} tanga</span>
                        @endif
                    </div>
                </div>

                {{-- Insufficient Balance Helper Box --}}
                @if(!$hasEnough)
                    <div class="p-3.5 rounded-card bg-amber-500/10 border border-amber-500/30 space-y-2 text-xs">
                        <div class="flex items-center gap-2 text-amber-800 dark:text-amber-300 font-medium">
                            <svg class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span>Ushbu xarid uchun tangangiz yetarli emas.</span>
                        </div>
                        @if($canCoverWithPoints)
                            <p class="text-[11px] text-stone-600 dark:text-mist font-sans leading-relaxed">
                                Sizda <strong class="text-purple-700 dark:text-purple-300 font-mono">{{ number_format($user->available_points) }} ball</strong> bor. Yetishmayotgan {{ number_format($missingCoins) }} tangani olish uchun <strong class="text-paper">{{ number_format($neededPoints) }} ball</strong> almashtirish kifoya!
                            </p>
                            <button wire:click="closePurchaseModal(); $wire.setTab('exchange')"
                                    type="button"
                                    class="w-full mt-1 py-2 px-3 rounded-btn bg-purple-500/15 hover:bg-purple-500/25 border border-purple-500/40 text-purple-800 dark:text-purple-200 font-mono text-[11px] font-bold flex items-center justify-center gap-2 transition-colors">
                                <span>Ballarni tangaga almashtirish</span>
                                <span>&rarr;</span>
                            </button>
                        @else
                            <p class="text-[11px] text-stone-600 dark:text-mist font-sans">
                                Kitob mutolaa qilib yoki viktorinalarda qatnashib ball va tangalar yig'ishingiz mumkin.
                            </p>
                        @endif
                    </div>
                @endif

                {{-- Confirmation Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button @click="closeModal()"
                            type="button"
                            class="px-4 py-2 rounded-btn bg-ink-800 border border-ink-border text-xs text-stone-700 dark:text-mist hover:text-paper transition-colors">
                        Bekor qilish
                    </button>
                    @if($hasEnough)
                        <button wire:click="confirmPurchase"
                                wire:loading.attr="disabled"
                                type="button"
                                class="ks-btn-gold !text-slate-950 font-bold px-5 py-2.5 rounded-btn text-xs inline-flex items-center gap-2">
                            <span wire:loading.remove wire:target="confirmPurchase">Tasdiqlash va xarid qilish</span>
                            <span wire:loading wire:target="confirmPurchase" class="inline-flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="12"/></svg>
                                <span>Xarid amalga oshirilmoqda...</span>
                            </span>
                        </button>
                    @elseif($canCoverWithPoints)
                        <button wire:click="closePurchaseModal(); $wire.setTab('exchange')"
                                type="button"
                                class="ks-btn-gold !text-slate-950 font-bold px-5 py-2.5 rounded-btn text-xs inline-flex items-center gap-2">
                            <span>Ballarni almashtirish</span>
                            <span>&rarr;</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
