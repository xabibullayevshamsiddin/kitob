@extends('admin.layouts.app')

@section('title', 'Do‘kon boshqaruvi')
@section('breadcrumb', 'Do‘kon')

@section('content')
<div class="space-y-6">

    {{-- Sarlavha va Harakatlar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold font-serif text-paper">Do‘kon va Tangalar Boshqaruvi</h1>
            <p class="text-xs text-mist font-mono mt-0.5">Katalog buyumlari, narxlar, o‘yin iqtisodiyoti va almashtirish sozlamalari</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.shop.items.create') }}"
               class="ks-btn-primary py-2 px-4 text-xs font-mono inline-flex items-center gap-2 rounded-btn shadow-sm hover:opacity-90 transition-opacity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Yangi buyum qo‘shish</span>
            </a>
        </div>
    </div>

    {{-- Alert xabarlari --}}
    @if(session('success'))
        <div class="bg-emerald-950/60 border border-emerald-500/40 text-emerald-300 px-4 py-3 rounded-panel text-xs font-mono flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-950/60 border border-rose-500/40 text-rose-300 px-4 py-3 rounded-panel text-xs font-mono flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-200">&times;</button>
        </div>
    @endif

    {{-- Tizim ogohlantirishlari (Katalog bo'sh yoki migratsiya qilinmagan) --}}
    @if(!empty($shopWarnings))
        <div class="space-y-3">
            @foreach($shopWarnings as $warning)
                <div class="p-4 rounded-panel border {{ $warning['type'] === 'danger' ? 'bg-rose-950/70 border-rose-500/50 text-rose-200' : 'bg-amber-950/70 border-amber-500/50 text-amber-200' }} shadow-lg shadow-black/20">
                    <div class="flex items-start gap-3">
                        <div class="p-1.5 rounded-full {{ $warning['type'] === 'danger' ? 'bg-rose-500/20 text-rose-400' : 'bg-amber-500/20 text-amber-400' }} shrink-0 mt-0.5">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                        </div>
                        <div class="space-y-1 flex-1">
                            <h4 class="text-sm font-bold font-serif">{{ $warning['title'] }}</h4>
                            <p class="text-xs leading-relaxed opacity-90">{{ $warning['message'] }}</p>
                            <div class="pt-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-black/40 border border-white/10 font-mono text-[11px] select-all text-amber-300">
                                    <code>php artisan shop:install</code>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Tab Navigatsiyasi --}}
    <div class="border-b border-ink-border flex items-center gap-2 overflow-x-auto">
        <a href="{{ route('admin.shop.index', ['tab' => 'items']) }}"
           class="px-4 py-2.5 text-xs font-mono font-medium border-b-2 whitespace-nowrap transition-colors flex items-center gap-2 {{ $activeTab === 'items' ? 'border-amber-400 text-amber-400' : 'border-transparent text-mist hover:text-paper' }}">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            <span>Buyumlar katalogi</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $activeTab === 'items' ? 'bg-amber-400/20 text-amber-300' : 'bg-ink-900 text-mist' }}">{{ $items->total() }}</span>
        </a>

        <a href="{{ route('admin.shop.index', ['tab' => 'economy']) }}"
           class="px-4 py-2.5 text-xs font-mono font-medium border-b-2 whitespace-nowrap transition-colors flex items-center gap-2 {{ $activeTab === 'economy' ? 'border-amber-400 text-amber-400' : 'border-transparent text-mist hover:text-paper' }}">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="9"/><path d="M12 6v12M15 9.5a2.5 2.5 0 0 0-5 0c0 3 5 2 5 5a2.5 2.5 0 0 1-5 0"/></svg>
            <span>O‘yin iqtisodiyoti</span>
        </a>

        <a href="{{ route('admin.shop.index', ['tab' => 'settings']) }}"
           class="px-4 py-2.5 text-xs font-mono font-medium border-b-2 whitespace-nowrap transition-colors flex items-center gap-2 {{ $activeTab === 'settings' ? 'border-amber-400 text-amber-400' : 'border-transparent text-mist hover:text-paper' }}">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <span>Almashtirish sozlamalari</span>
        </a>

        <a href="{{ route('admin.shop.index', ['tab' => 'users']) }}"
           class="px-4 py-2.5 text-xs font-mono font-medium border-b-2 whitespace-nowrap transition-colors flex items-center gap-2 {{ $activeTab === 'users' ? 'border-amber-400 text-amber-400' : 'border-transparent text-mist hover:text-paper' }}">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Foydalanuvchi buyumlari & Refund</span>
        </a>
    </div>

    {{-- TAB 1: BUYUMLAR KATALOGI --}}
    @if($activeTab === 'items')
        <div class="space-y-4">
            {{-- Filtrlar paneli --}}
            <form method="GET" action="{{ route('admin.shop.index') }}" class="bg-ink-900 border border-ink-border rounded-panel p-3.5 flex flex-wrap items-center gap-3">
                <input type="hidden" name="tab" value="items">

                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Buyum nomi yoki kaliti bo‘yicha qidiruv..."
                           class="w-full bg-ink-950 border border-ink-border text-paper placeholder-mist/60 text-xs font-mono rounded-btn px-3 py-2 focus:outline-none focus:border-amber-400">
                </div>

                <div class="w-36">
                    <select name="category" onchange="this.form.submit()"
                            class="w-full bg-ink-950 border border-ink-border text-paper text-xs font-mono rounded-btn px-2.5 py-2 focus:outline-none focus:border-amber-400">
                        <option value="all">Barcha bo‘limlar</option>
                        <option value="profile" {{ request('category') === 'profile' ? 'selected' : '' }}>Profil</option>
                        <option value="colors" {{ request('category') === 'colors' ? 'selected' : '' }}>Ranglar</option>
                        <option value="consumable" {{ request('category') === 'consumable' ? 'selected' : '' }}>Foydali</option>
                        <option value="perk" {{ request('category') === 'perk' ? 'selected' : '' }}>Imtiyoz</option>
                        <option value="bundle" {{ request('category') === 'bundle' ? 'selected' : '' }}>To‘plam</option>
                    </select>
                </div>

                <div class="w-36">
                    <select name="rarity" onchange="this.form.submit()"
                            class="w-full bg-ink-950 border border-ink-border text-paper text-xs font-mono rounded-btn px-2.5 py-2 focus:outline-none focus:border-amber-400">
                        <option value="all">Barcha nodirliklar</option>
                        <option value="common" {{ request('rarity') === 'common' ? 'selected' : '' }}>Oddiy</option>
                        <option value="rare" {{ request('rarity') === 'rare' ? 'selected' : '' }}>Noyob</option>
                        <option value="epic" {{ request('rarity') === 'epic' ? 'selected' : '' }}>Epik</option>
                        <option value="legendary" {{ request('rarity') === 'legendary' ? 'selected' : '' }}>Afsonaviy</option>
                    </select>
                </div>

                <div class="w-32">
                    <select name="status" onchange="this.form.submit()"
                            class="w-full bg-ink-950 border border-ink-border text-paper text-xs font-mono rounded-btn px-2.5 py-2 focus:outline-none focus:border-amber-400">
                        <option value="all">Barcha holat</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Faol</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Yashirilgan</option>
                    </select>
                </div>

                <button type="submit" class="px-3.5 py-2 bg-ink-950 border border-ink-border hover:border-amber-400/60 text-paper text-xs font-mono rounded-btn transition-colors">
                    Filtrlash
                </button>

                @if(request()->hasAny(['search', 'category', 'rarity', 'status']))
                    <a href="{{ route('admin.shop.index', ['tab' => 'items']) }}" class="text-xs font-mono text-mist hover:text-amber-400">
                        Tozalash
                    </a>
                @endif
            </form>

            {{-- Buyumlar jadvali --}}
            <div class="bg-ink-900 border border-ink-border rounded-panel overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs font-mono">
                        <thead class="bg-ink-950 text-mist uppercase text-[10px] tracking-wider border-b border-ink-border">
                            <tr>
                                <th class="px-4 py-3">ID / Vizual</th>
                                <th class="px-4 py-3">Nomi & Kaliti</th>
                                <th class="px-4 py-3">Bo‘lim / Slot</th>
                                <th class="px-4 py-3">Narx</th>
                                <th class="px-4 py-3">Nodirlik</th>
                                <th class="px-4 py-3">Turi</th>
                                <th class="px-4 py-3">Holat</th>
                                <th class="px-4 py-3 text-right">Amallar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-border">
                            @forelse($items as $item)
                                <tr class="hover:bg-ink-950/40 transition-colors">
                                    {{-- Vizual eskiz --}}
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-mist/70 text-[11px]">#{{ $item->id }}</span>
                                            <div class="w-8 h-8 rounded-btn border border-ink-border bg-ink-950 flex items-center justify-center overflow-hidden">
                                                @if(isset($item->payload['color']))
                                                    <span class="w-4 h-4 rounded-full border border-white/20" style="background-color: {{ $item->safeColor('color') }}"></span>
                                                @elseif(isset($item->payload['bg_light']))
                                                    <span class="w-4 h-4 rounded-btn border border-white/20" style="background-color: {{ $item->safeColor('bg_light') }}"></span>
                                                @else
                                                    <svg class="w-4 h-4 text-amber-400/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3z"/></svg>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Nomi va kaliti --}}
                                    <td class="px-4 py-3">
                                        <p class="font-serif font-semibold text-paper text-sm">{{ $item->name_text }}</p>
                                        <p class="text-[11px] text-mist/80 font-mono">{{ $item->key }}</p>
                                    </td>

                                    {{-- Bo'lim & Slot --}}
                                    <td class="px-4 py-3 whitespace-nowrap text-mist">
                                        <span class="inline-block px-2 py-0.5 rounded-badge bg-ink-950 border border-ink-border text-[10px] text-paper/90">
                                            {{ ucfirst($item->category) }}
                                        </span>
                                        @if($item->slot)
                                            <span class="block text-[10px] text-mist/70 mt-0.5">{{ $item->slot }}</span>
                                        @endif
                                    </td>

                                    {{-- Narx --}}
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5 text-amber-400 font-bold">
                                            <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9"/>
                                                <path d="M12 6v12M15 9.5a2.5 2.5 0 0 0-5 0c0 3 5 2 5 5a2.5 2.5 0 0 1-5 0"/>
                                            </svg>
                                            <span>{{ number_format($item->price_coins) }}</span>
                                        </div>
                                    </td>

                                    {{-- Nodirlik --}}
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-block px-2 py-0.5 rounded-badge text-[10px] font-bold uppercase tracking-wider
                                            @if($item->rarity === 'common') bg-slate-500/10 text-slate-300 border border-slate-500/30
                                            @elseif($item->rarity === 'rare') bg-blue-500/10 text-blue-300 border border-blue-500/30
                                            @elseif($item->rarity === 'epic') bg-purple-500/10 text-purple-300 border border-purple-500/30
                                            @elseif($item->rarity === 'legendary') bg-amber-500/10 text-amber-300 border border-amber-500/30
                                            @endif">
                                            {{ $item->rarity }}
                                        </span>
                                    </td>

                                    {{-- Turi --}}
                                    <td class="px-4 py-3 whitespace-nowrap text-mist text-[11px]">
                                        @if($item->is_consumable)
                                            <span class="text-emerald-400">Iste’mol (max {{ $item->max_quantity }})</span>
                                        @else
                                            <span>Doimiy</span>
                                        @endif
                                    </td>

                                    {{-- Holat --}}
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($item->is_active)
                                            <span class="inline-flex items-center gap-1.5 text-[11px] text-emerald-400">
                                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                                Faol
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 text-[11px] text-rose-400">
                                                <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                                                Yashirilgan
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Amallar --}}
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <div class="inline-flex items-center gap-2">
                                            {{-- Toggle Active --}}
                                            <form method="POST" action="{{ route('admin.shop.items.toggle', $item) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        title="{{ $item->is_active ? 'Do‘kondan yashirish' : 'Do‘konda ko‘rsatish' }}"
                                                        class="p-1.5 rounded-btn bg-ink-950 border border-ink-border text-mist hover:text-paper hover:border-amber-400/60 transition-colors">
                                                    @if($item->is_active)
                                                        <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                                    @else
                                                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    @endif
                                                </button>
                                            </form>

                                            {{-- Edit --}}
                                            <a href="{{ route('admin.shop.items.edit', $item) }}"
                                               class="p-1.5 rounded-btn bg-ink-950 border border-ink-border text-mist hover:text-amber-400 hover:border-amber-400/60 transition-colors"
                                               title="Tahrirlash">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-mist">
                                        Buyumlar topilmadi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($items->hasPages())
                    <div class="p-3 border-t border-ink-border">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- TAB 2: O'YIN IQTISODIYOTI --}}
    @if($activeTab === 'economy')
        <div class="space-y-5">
            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 font-mono">
                {{-- Aylanmadagi tanga --}}
                <div class="bg-ink-900 border border-ink-border rounded-panel p-4 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase text-mist tracking-wider">Aylanmadagi Tangalar</p>
                        <p class="text-xl font-bold text-amber-400 mt-1">{{ number_format($economyData['circulation']) }}</p>
                        <p class="text-[10px] text-mist/80 mt-0.5">Barcha foydalanuvchilar balansida</p>
                    </div>
                    <div class="w-10 h-10 rounded-btn bg-amber-400/10 border border-amber-400/30 text-amber-400 flex items-center justify-center">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="9"/><path d="M12 6v12M15 9.5a2.5 2.5 0 0 0-5 0c0 3 5 2 5 5a2.5 2.5 0 0 1-5 0"/></svg>
                    </div>
                </div>

                {{-- Chiqarilgan tangalar --}}
                <div class="bg-ink-900 border border-ink-border rounded-panel p-4 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase text-mist tracking-wider">Jami Chiqarilgan</p>
                        <p class="text-xl font-bold text-emerald-400 mt-1">{{ number_format($economyData['issued_total']) }}</p>
                        <p class="text-[10px] text-mist/80 mt-0.5">Mutolaa, testlar va bonuslar orqali</p>
                    </div>
                    <div class="w-10 h-10 rounded-btn bg-emerald-400/10 border border-emerald-400/30 text-emerald-400 flex items-center justify-center">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                </div>

                {{-- Do'konda sarflangan --}}
                <div class="bg-ink-900 border border-ink-border rounded-panel p-4 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase text-mist tracking-wider">Do‘konda Sarflangan</p>
                        <p class="text-xl font-bold text-rose-400 mt-1">{{ number_format($economyData['spent_total']) }}</p>
                        <p class="text-[10px] text-mist/80 mt-0.5">{{ number_format($economyData['purchases_count']) }} ta xarid orqali</p>
                    </div>
                    <div class="w-10 h-10 rounded-btn bg-rose-400/10 border border-rose-400/30 text-rose-400 flex items-center justify-center">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    </div>
                </div>

                {{-- Ballardan almashtirilgan --}}
                <div class="bg-ink-900 border border-ink-border rounded-panel p-4 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] uppercase text-mist tracking-wider">Ball → Tanga Hajmi</p>
                        <p class="text-xl font-bold text-cyan-400 mt-1">{{ number_format($economyData['exchange_coins']) }}</p>
                        <p class="text-[10px] text-mist/80 mt-0.5">O‘girilgan tangalar miqdori</p>
                    </div>
                    <div class="w-10 h-10 rounded-btn bg-cyan-400/10 border border-cyan-400/30 text-cyan-400 flex items-center justify-center">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="m16 3 4 4-4 4"/><path d="M20 7H4"/><path d="m8 21-4-4 4-4"/><path d="M4 17h16"/></svg>
                    </div>
                </div>
            </div>

            {{-- 2 ustunli tahlil: Eng ko'p sotilganlar & So'nggi xaridlar --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                {{-- Eng ko'p sotilgan buyumlar --}}
                <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
                    <h3 class="text-sm font-bold font-serif text-paper mb-3 flex items-center justify-between">
                        <span>Eng Ommabop Buyumlar (Top 10)</span>
                        <span class="text-xs font-mono text-mist">Sotilgan soni bo‘yicha</span>
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs font-mono">
                            <thead class="text-[10px] text-mist uppercase border-b border-ink-border">
                                <tr>
                                    <th class="py-2">#</th>
                                    <th class="py-2">Buyum</th>
                                    <th class="py-2 text-right">Sotildi</th>
                                    <th class="py-2 text-right">Daromad</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-border/60">
                                @forelse($economyData['top_items'] as $idx => $tItem)
                                    <tr class="hover:bg-ink-950/30">
                                        <td class="py-2.5 text-mist/70">{{ $idx + 1 }}</td>
                                        <td class="py-2.5">
                                            <p class="font-medium text-paper">{{ $tItem['name'] }}</p>
                                            <span class="text-[10px] text-mist">{{ $tItem['key'] }}</span>
                                        </td>
                                        <td class="py-2.5 text-right font-bold text-paper">{{ number_format($tItem['units_sold']) }} ta</td>
                                        <td class="py-2.5 text-right text-amber-400 font-bold">{{ number_format($tItem['total_revenue']) }} 🪙</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-mist">Hozircha xaridlar qayd etilmagan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- So'nggi xaridlar oqimi --}}
                <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
                    <h3 class="text-sm font-bold font-serif text-paper mb-3 flex items-center justify-between">
                        <span>So‘nggi Xaridlar</span>
                        <span class="text-xs font-mono text-mist">Jonli oqim</span>
                    </h3>
                    <div class="space-y-2.5 max-h-[360px] overflow-y-auto pr-1">
                        @forelse($recentPurchases as $purchase)
                            <div class="flex items-center justify-between p-2.5 rounded-btn bg-ink-950 border border-ink-border text-xs font-mono">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-full bg-amber-400/10 text-amber-400 border border-amber-400/30 flex items-center justify-center flex-shrink-0 text-[11px] font-bold">
                                        🪙
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-paper truncate">{{ $purchase->user->name ?? 'Foydalanuvchi' }}</p>
                                        <p class="text-[10px] text-mist truncate">
                                            {{ $purchase->shopItem->name_text ?? 'Buyum' }} &bull; {{ $purchase->quantity }} dona
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="font-bold text-rose-400">-{{ number_format($purchase->price_paid) }} 🪙</p>
                                    <p class="text-[10px] text-mist/60">{{ $purchase->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-xs font-mono text-mist py-8">Xaridlar mavjud emas.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- TAB 3: ALMASHTIRISH SOZLAMALARI --}}
    @if($activeTab === 'settings')
        <div class="max-w-2xl bg-ink-900 border border-ink-border rounded-panel p-6">
            <h2 class="text-base font-bold font-serif text-paper mb-1">Ball → Tanga Almashtirish Parametrlari</h2>
            <p class="text-xs text-mist font-mono mb-5">Reyting tizimiga ta’sir qilmagan holda ballarni tangaga aylantirish qoidalarini boshqaring.</p>

            <form method="POST" action="{{ route('admin.shop.settings.update') }}" class="space-y-5 font-mono text-xs">
                @csrf

                {{-- Holatni yoqish / o'chirish --}}
                <div class="flex items-center justify-between p-3.5 bg-ink-950 border border-ink-border rounded-btn">
                    <div>
                        <label for="exchange_enabled" class="font-bold text-paper block cursor-pointer">Almashtirish xizmatini yoqish</label>
                        <p class="text-[11px] text-mist mt-0.5">O‘chirilganda foydalanuvchilar almashtirish sahifasidan foydalana olmaydi</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="exchange_enabled" id="exchange_enabled" value="1"
                               {{ $exchangeSettings['enabled'] ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-ink-border peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-400"></div>
                    </label>
                </div>

                {{-- Kurs: nechta ball = 1 tanga --}}
                <div class="space-y-1.5">
                    <label class="block text-paper font-semibold">Almashtirish kursi (Nechta ball = 1 tanga)</label>
                    <div class="relative">
                        <input type="number" name="rate_points_per_coin" value="{{ $exchangeSettings['rate_points_per_coin'] }}"
                               min="1" max="100" required
                               class="w-full bg-ink-950 border border-ink-border text-paper rounded-btn px-3 py-2.5 focus:outline-none focus:border-amber-400">
                    </div>
                    <p class="text-[11px] text-mist">Tavsiya: 15 (15 ball evaziga 1 tanga beriladi. Leaderboard reytingiga ta’sir qilmaydi).</p>
                </div>

                {{-- Minimal ball --}}
                <div class="space-y-1.5">
                    <label class="block text-paper font-semibold">Bir martalik minimal ball miqdori</label>
                    <input type="number" name="min_points" value="{{ $exchangeSettings['min_points'] }}"
                           min="1" max="10000" required
                           class="w-full bg-ink-950 border border-ink-border text-paper rounded-btn px-3 py-2.5 focus:outline-none focus:border-amber-400">
                    <p class="text-[11px] text-mist">Standart: 15 ball (Foydalanuvchi kamida 15 ball almashtirishi lozim).</p>
                </div>

                {{-- Kunlik maksimal ball --}}
                <div class="space-y-1.5">
                    <label class="block text-paper font-semibold">Kunlik maksimal chegara (Maksimal sarflanadigan ball)</label>
                    <input type="number" name="max_points_per_day" value="{{ $exchangeSettings['max_points_per_day'] }}"
                           min="1" max="100000" required
                           class="w-full bg-ink-950 border border-ink-border text-paper rounded-btn px-3 py-2.5 focus:outline-none focus:border-amber-400">
                    <p class="text-[11px] text-mist">Standart: 300 ball (Kuniga eng ko‘pi bilan 20 tanga almashtirib olinadi).</p>
                </div>

                <div class="pt-3">
                    <button type="submit" class="ks-btn-primary py-2.5 px-5 text-xs font-mono rounded-btn">
                        Sozlamalarni saqlash
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- TAB 4: FOYDALANUVCHI BUYUMLARI & REFUND --}}
    @if($activeTab === 'users')
        <div class="space-y-5">
            {{-- Qidiruv --}}
            <form method="GET" action="{{ route('admin.shop.index') }}" class="bg-ink-900 border border-ink-border rounded-panel p-3.5 flex items-center gap-3">
                <input type="hidden" name="tab" value="users">
                <div class="flex-1">
                    <input type="text" name="user_search" value="{{ request('user_search') }}"
                           placeholder="Foydalanuvchi ismi, emaili yoki buyum nomi bo‘yicha qidiruv..."
                           class="w-full bg-ink-950 border border-ink-border text-paper placeholder-mist/60 text-xs font-mono rounded-btn px-3 py-2 focus:outline-none focus:border-amber-400">
                </div>
                <button type="submit" class="px-4 py-2 bg-ink-950 border border-ink-border hover:border-amber-400/60 text-paper text-xs font-mono rounded-btn transition-colors">
                    Qidirish
                </button>
                @if(request('user_search'))
                    <a href="{{ route('admin.shop.index', ['tab' => 'users']) }}" class="text-xs font-mono text-mist hover:text-amber-400">
                        Tozalash
                    </a>
                @endif
            </form>

            {{-- Foydalanuvchi buyumlari jadvali --}}
            <div class="bg-ink-900 border border-ink-border rounded-panel overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs font-mono">
                        <thead class="bg-ink-950 text-mist uppercase text-[10px] tracking-wider border-b border-ink-border">
                            <tr>
                                <th class="px-4 py-3">Foydalanuvchi</th>
                                <th class="px-4 py-3">Buyum</th>
                                <th class="px-4 py-3">Miqdor</th>
                                <th class="px-4 py-3">Olingan sana</th>
                                <th class="px-4 py-3">Muddati</th>
                                <th class="px-4 py-3 text-right">Qaytarib olish (Refund)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-border">
                            @forelse($userItems as $uItem)
                                <tr class="hover:bg-ink-950/40 transition-colors">
                                    <td class="px-4 py-3">
                                        <p class="font-bold text-paper">{{ $uItem->user->name ?? 'Mavjud emas' }}</p>
                                        <p class="text-[10px] text-mist">{{ $uItem->user->email ?? '' }}</p>
                                        <span class="text-[10px] text-amber-400">Balans: {{ number_format($uItem->user->coin_balance ?? 0) }} 🪙</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-paper">{{ $uItem->shopItem->name_text ?? 'Buyum' }}</p>
                                        <span class="text-[10px] text-mist">{{ $uItem->shopItem->key ?? '' }} &bull; {{ number_format($uItem->shopItem->price_coins ?? 0) }} 🪙</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="font-bold text-paper">{{ $uItem->quantity }}</span> dona
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-mist">
                                        {{ $uItem->acquired_at ? $uItem->acquired_at->format('d.m.Y H:i') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-mist">
                                        @if($uItem->expires_at)
                                            <span class="{{ $uItem->isExpired() ? 'text-rose-400' : 'text-emerald-400' }}">
                                                {{ $uItem->expires_at->format('d.m.Y H:i') }}
                                            </span>
                                        @else
                                            <span>Muddatsiz</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <form method="POST" action="{{ route('admin.shop.refund') }}"
                                              onsubmit="return confirm('Haqiqatan ham «{{ $uItem->user->name ?? 'Foydalanuvchi' }}»dan «{{ $uItem->shopItem->name_text ?? 'bu buyum' }}»ni qaytarib olib, tangalarini qaytarmoqchimisiz?');"
                                              class="inline-flex items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $uItem->user_id }}">
                                            <input type="hidden" name="shop_item_id" value="{{ $uItem->shop_item_id }}">
                                            <input type="hidden" name="quantity" value="{{ $uItem->quantity }}">
                                            <button type="submit"
                                                    class="px-2.5 py-1 rounded-btn bg-rose-500/10 border border-rose-500/30 text-rose-300 hover:bg-rose-500/20 text-[11px] transition-colors">
                                                Qaytarib olish
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-mist">
                                        Foydalanuvchi buyumlari topilmadi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($userItems->hasPages())
                    <div class="p-3 border-t border-ink-border">
                        {{ $userItems->links() }}
                    </div>
                @endif
            </div>

            {{-- So'nggi Refundlar tarixi --}}
            <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
                <h3 class="text-sm font-bold font-serif text-paper mb-3">So‘nggi Qaytarishlar Tarixi (Refund Audit)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs font-mono">
                        <thead class="text-[10px] text-mist uppercase border-b border-ink-border">
                            <tr>
                                <th class="py-2">Foydalanuvchi</th>
                                <th class="py-2">Qaytarilgan miqdor</th>
                                <th class="py-2">Izoh</th>
                                <th class="py-2">Sana</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-border/60">
                            @forelse($recentRefunds as $refund)
                                <tr class="hover:bg-ink-950/30">
                                    <td class="py-2 font-medium text-paper">{{ $refund->user->name ?? 'Foydalanuvchi' }}</td>
                                    <td class="py-2 text-emerald-400 font-bold">+{{ number_format($refund->coins) }} 🪙</td>
                                    <td class="py-2 text-mist">{{ $refund->description }}</td>
                                    <td class="py-2 text-mist/70 text-[10px]">{{ $refund->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-mist">Qaytarishlar qayd etilmagan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
