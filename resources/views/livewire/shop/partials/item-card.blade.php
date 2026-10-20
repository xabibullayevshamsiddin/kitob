@php
    $isOwned = $ownedItemsMap->has($item->id);
    $isEquipped = false;
    if ($item->category === 'bundle') {
        $isEquipped = in_array($item->id, $equippedMap, true);
    } elseif ($item->slot) {
        $isEquipped = isset($equippedMap[$item->slot]) && $equippedMap[$item->slot] == $item->id;
    }

    $payload = $item->payload ?? [];
    $accentColor = $payload['accent_color'] ?? ($payload['color'] ?? ($payload['badge_color'] ?? '#F59E0B'));
    $bannerGradient = $payload['banner_gradient'] ?? 'bg-gradient-to-r from-ink-900 to-ink-950';
    $canAfford = ($user->coin_balance >= $item->price_coins);
    $isConsumable = (bool) $item->is_consumable;
    $ownedQty = $isOwned ? $ownedItemsMap->get($item->id)->quantity : 0;
    if ($item->key === 'streak_freeze') {
        $ownedQty = $freezeCount;
    }
@endphp

<div class="relative flex flex-col justify-between p-5 rounded-card border bg-ink-900/90 transition-all duration-base group hover:border-amber-500/40 hover:shadow-xl hover:shadow-black/20 {{ $isEquipped ? 'border-emerald-500/50 shadow-md shadow-emerald-950/20' : 'border-ink-border' }}">

    {{-- Top Section --}}
    <div class="space-y-3.5">
        {{-- Header row: Rarity badge & Status --}}
        <div class="flex items-center justify-between gap-2">
            <span class="px-2 py-0.5 rounded-badge font-mono text-[10px] uppercase font-bold tracking-wider {{ $item->rarity === 'legendary' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : ($item->rarity === 'epic' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/40' : ($item->rarity === 'rare' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/40' : 'bg-slate-500/20 text-slate-300 border border-slate-500/30')) }}">
                {{ trans('shop.rarity_' . $item->rarity) }}
            </span>

            @if($isEquipped)
                <span class="px-2 py-0.5 rounded-badge bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 font-mono text-[10px] font-bold">
                    Kiyilgan
                </span>
            @elseif($isOwned && !$isConsumable)
                <span class="px-2 py-0.5 rounded-badge bg-ink-800 border border-ink-border text-mist font-mono text-[10px]">
                    Sizda bor
                </span>
            @elseif($isConsumable && $ownedQty > 0)
                <span class="px-2 py-0.5 rounded-badge bg-amber-500/15 border border-amber-500/30 text-amber-300 font-mono text-[10px] font-bold">
                    {{ $ownedQty }} ta bor
                </span>
            @endif
        </div>

        {{-- Visual Preview Thumbnail --}}
        <div class="relative h-24 rounded-btn overflow-hidden border border-ink-border/80 flex items-center justify-center p-3 select-none {{ $bannerGradient }}"
             style="background-image: {{ ($payload['banner_ambient'] ?? '') !== 'none' ? ($payload['banner_ambient'] ?? '') : '' }}">
            @if($item->category === 'bundle' || $item->slot === 'profile_theme')
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-ink-950/70 border border-ink-border/50 backdrop-blur-sm shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $accentColor }}"></span>
                    <span class="font-mono text-xs text-paper font-semibold">{{ $item->name_text }}</span>
                </div>
            @elseif($item->slot === 'name_color')
                <span class="font-serif font-bold text-lg" style="color: {{ $accentColor }}">
                    {{ $user->name }}
                </span>
            @elseif($item->slot === 'title')
                <span class="px-3 py-1 rounded-badge font-mono text-xs font-bold"
                      style="background-color: {{ $accentColor }}25; color: {{ $accentColor }}; border: 1px solid {{ $accentColor }}50;">
                    {{ $item->name_text }}
                </span>
            @elseif($item->slot === 'accent')
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full shadow-lg" style="background-color: {{ $accentColor }}"></span>
                    <span class="font-mono text-xs text-mist">{{ $accentColor }}</span>
                </div>
            @elseif($item->slot === 'reader_palette')
                <div class="flex items-center gap-2 px-3 py-2 rounded-btn text-xs font-serif"
                     style="background-color: {{ $payload['bg_light'] ?? '#fff' }}; color: {{ $payload['text_light'] ?? '#000' }}; border: 1px solid rgba(0,0,0,0.1);">
                    Aa Mutolaa foni
                </div>
            @elseif($item->slot === 'highlight_color')
                <div class="px-2 py-0.5 rounded text-ink-950 font-bold text-xs" style="background-color: {{ $accentColor }}">
                    Iqtibos matni
                </div>
            @elseif($item->is_consumable)
                <div class="w-12 h-12 rounded-full bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400">
                    @if($item->key === 'streak_freeze')
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="2" y1="12" x2="22" y2="12"/><line x1="12" y1="2" x2="12" y2="22"/><path d="m20 16-4-4 4-4"/><path d="m4 8 4 4-4 4"/><path d="m16 4-4 4-4-4"/><path d="m8 20 4-4 4 4"/></svg>
                    @elseif($item->key === 'coin_booster_60')
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    @else
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                    @endif
                </div>
            @endif
        </div>

        {{-- Name and Description --}}
        <div>
            <h4 class="font-serif font-bold text-base text-paper group-hover:text-amber-400 transition-colors">
                {{ $item->name_text }}
            </h4>
            <p class="text-xs text-mist font-sans line-clamp-2 mt-1 leading-relaxed">
                {{ $item->description_text }}
            </p>

            @if($item->slot === 'title')
                <div class="text-[10px] font-mono text-amber-400/90 flex items-center gap-1.5 pt-1.5">
                    <svg class="w-2.5 h-2.5 text-amber-400 shrink-0 fill-current" viewBox="0 0 24 24"><polygon points="12 2 14.5 9.5 22 12 14.5 14.5 12 22 9.5 14.5 2 12 9.5 9.5 12 2"/></svg>
                    <span>Ismingiz yonida, profil va chatda faxriy nishon</span>
                </div>
            @elseif($item->slot === 'name_color')
                <div class="text-[10px] font-mono text-amber-400/90 flex items-center gap-1.5 pt-1.5">
                    <svg class="w-2.5 h-2.5 text-amber-400 shrink-0 fill-current" viewBox="0 0 24 24"><polygon points="12 2 14.5 9.5 22 12 14.5 14.5 12 22 9.5 14.5 2 12 9.5 9.5 12 2"/></svg>
                    <span>Ismingiz butun saytda shu rangda porlaydi</span>
                </div>
            @elseif($item->slot === 'accent')
                <div class="text-[10px] font-mono text-amber-400/90 flex items-center gap-1.5 pt-1.5">
                    <svg class="w-2.5 h-2.5 text-amber-400 shrink-0 fill-current" viewBox="0 0 24 24"><polygon points="12 2 14.5 9.5 22 12 14.5 14.5 12 22 9.5 14.5 2 12 9.5 9.5 12 2"/></svg>
                    <span>Butun platforma tugmalari va aksent rangi</span>
                </div>
            @elseif($item->slot === 'reader_palette')
                <div class="text-[10px] font-mono text-amber-400/90 flex items-center gap-1.5 pt-1.5">
                    <svg class="w-2.5 h-2.5 text-amber-400 shrink-0 fill-current" viewBox="0 0 24 24"><polygon points="12 2 14.5 9.5 22 12 14.5 14.5 12 22 9.5 14.5 2 12 9.5 9.5 12 2"/></svg>
                    <span>Kitob mutolaa sahifasi foni</span>
                </div>
            @elseif($item->slot === 'highlight_color')
                <div class="text-[10px] font-mono text-amber-400/90 flex items-center gap-1.5 pt-1.5">
                    <svg class="w-2.5 h-2.5 text-amber-400 shrink-0 fill-current" viewBox="0 0 24 24"><polygon points="12 2 14.5 9.5 22 12 14.5 14.5 12 22 9.5 14.5 2 12 9.5 9.5 12 2"/></svg>
                    <span>Kitobda matn belgilash va iqtibos rangi</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Bottom Section: Price & Action Buttons --}}
    <div class="pt-4 mt-4 border-t border-ink-border/80 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-1.5 font-mono text-sm font-bold text-amber-400">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v10M15 9.5a3 3 0 0 0-6 0c0 3 6 2 6 5a3 3 0 0 1-6 0"/></svg>
                <span>{{ $item->price_coins }} tanga</span>
            </div>

            {{-- Preview button (for visual items) --}}
            @if(in_array($item->category, ['bundle', 'profile', 'colors']))
                <button wire:click="openPreview({{ $item->id }})"
                        type="button"
                        class="p-1.5 rounded-btn text-mist hover:text-paper hover:bg-ink-800 transition-colors"
                        title="Ko‘rinishini sinab ko‘rish"
                        aria-label="Sinab ko‘rish">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            @endif
        </div>

        {{-- Primary Action Button --}}
        <div>
            @if($isEquipped)
                <button wire:click="unequipSlot('{{ $item->category === 'bundle' ? 'all' : $item->slot }}')"
                        wire:loading.attr="disabled"
                        type="button"
                        class="w-full py-2 rounded-btn bg-ink-800 border border-ink-border text-mist hover:text-paper text-xs font-medium transition-colors">
                    Yechish
                </button>
            @elseif($isOwned && !$isConsumable)
                <button wire:click="equipItem({{ $item->id }})"
                        wire:loading.attr="disabled"
                        type="button"
                        class="w-full py-2 rounded-btn bg-emerald-600 hover:bg-emerald-500 text-ink-950 font-bold text-xs transition-colors shadow-sm">
                    Jihozlash
                </button>
            @else
                <button wire:click="openPurchaseModal({{ $item->id }})"
                        @if(!$canAfford) disabled @endif
                        type="button"
                        class="w-full py-2 rounded-btn text-xs font-bold transition-all inline-flex items-center justify-center gap-1.5 {{ $canAfford ? 'ks-btn-primary' : 'bg-ink-800 border border-ink-border text-mist opacity-50 cursor-not-allowed' }}">
                    <span>Xarid qilish</span>
                </button>
            @endif
        </div>
    </div>
</div>
