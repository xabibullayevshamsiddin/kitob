@extends('admin.layouts.app')

@section('title', 'Buyumni tahrirlash')
@section('breadcrumb')
    <a href="{{ route('admin.shop.index') }}" class="text-mist hover:text-paper">Do‘kon</a>
    <span class="text-mist/60 mx-1">/</span>
    <span>Tahrirlash: {{ $item->key }}</span>
@endsection

@section('content')
<div class="max-w-3xl space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold font-serif text-paper">Buyumni Tahrirlash: {{ $item->name_text }}</h1>
            <p class="text-xs text-mist font-mono mt-0.5">Kalit: <span class="text-amber-400">{{ $item->key }}</span> &bull; ID: #{{ $item->id }}</p>
        </div>
        <a href="{{ route('admin.shop.index') }}" class="px-3 py-1.5 rounded-btn bg-ink-950 border border-ink-border text-xs font-mono text-mist hover:text-paper">
            &larr; Orqaga
        </a>
    </div>

    @if($errors->any())
        <div class="bg-rose-950/60 border border-rose-500/40 text-rose-300 p-4 rounded-panel text-xs font-mono">
            <p class="font-bold mb-1">Xatoliklarni to‘g‘rilang:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.shop.items.update', $item) }}" class="bg-ink-900 border border-ink-border rounded-panel p-6 space-y-5 font-mono text-xs">
        @csrf
        @method('PUT')

        {{-- Kalit (faqat o'qish uchun) va Bo'lim --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label class="block text-paper font-semibold">Unikal kalit (Key)</label>
                <input type="text" value="{{ $item->key }}" disabled
                       class="w-full bg-ink-950/60 border border-ink-border text-mist rounded-btn px-3 py-2 cursor-not-allowed">
                <p class="text-[10px] text-mist">Kalit xavfsizlik va mavjud xaridlar sababli o‘zgartirilmaydi</p>
            </div>

            <div class="space-y-1.5">
                <label class="block text-paper font-semibold">Bo‘lim (Category) *</label>
                <select name="category" required class="w-full bg-ink-950 border border-ink-border text-paper rounded-btn px-3 py-2 focus:outline-none focus:border-amber-400">
                    <option value="profile" {{ old('category', $item->category) === 'profile' ? 'selected' : '' }}>Profil bezagi (profile)</option>
                    <option value="colors" {{ old('category', $item->category) === 'colors' ? 'selected' : '' }}>Ranglar va mavzular (colors)</option>
                    <option value="consumable" {{ old('category', $item->category) === 'consumable' ? 'selected' : '' }}>Foydali buyum (consumable)</option>
                    <option value="perk" {{ old('category', $item->category) === 'perk' ? 'selected' : '' }}>Imtiyoz (perk)</option>
                    <option value="bundle" {{ old('category', $item->category) === 'bundle' ? 'selected' : '' }}>To‘liq mavzu to‘plami (bundle)</option>
                </select>
            </div>
        </div>

        {{-- Slot va Nodirlik --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label class="block text-paper font-semibold">Jihozlash joyi (Slot)</label>
                <select name="slot" class="w-full bg-ink-950 border border-ink-border text-paper rounded-btn px-3 py-2 focus:outline-none focus:border-amber-400">
                    <option value="">Slot talab qilinmaydi (masalan consumable/perk)</option>
                    @foreach($slots as $slot)
                        <option value="{{ $slot }}" {{ old('slot', $item->slot) === $slot ? 'selected' : '' }}>{{ $slot }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="block text-paper font-semibold">Nodirlik (Rarity) *</label>
                <select name="rarity" required class="w-full bg-ink-950 border border-ink-border text-paper rounded-btn px-3 py-2 focus:outline-none focus:border-amber-400">
                    <option value="common" {{ old('rarity', $item->rarity) === 'common' ? 'selected' : '' }}>Oddiy (common)</option>
                    <option value="rare" {{ old('rarity', $item->rarity) === 'rare' ? 'selected' : '' }}>Noyob (rare)</option>
                    <option value="epic" {{ old('rarity', $item->rarity) === 'epic' ? 'selected' : '' }}>Epik (epic)</option>
                    <option value="legendary" {{ old('rarity', $item->rarity) === 'legendary' ? 'selected' : '' }}>Afsonaviy (legendary)</option>
                </select>
            </div>
        </div>

        {{-- Narx, Maksimal miqdor va Muddat --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="space-y-1.5">
                <label class="block text-paper font-semibold">Narxi (Tanga) *</label>
                <input type="number" name="price_coins" value="{{ old('price_coins', $item->price_coins) }}" min="0" required
                       class="w-full bg-ink-950 border border-ink-border text-paper rounded-btn px-3 py-2 focus:outline-none focus:border-amber-400">
            </div>

            <div class="space-y-1.5">
                <label class="block text-paper font-semibold">Maksimal miqdor</label>
                <input type="number" name="max_quantity" value="{{ old('max_quantity', $item->max_quantity) }}" min="1" max="999" required
                       class="w-full bg-ink-950 border border-ink-border text-paper rounded-btn px-3 py-2 focus:outline-none focus:border-amber-400">
            </div>

            <div class="space-y-1.5">
                <label class="block text-paper font-semibold">Amal qilish muddati (daqiqa)</label>
                <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $item->duration_minutes) }}" min="1" placeholder="Muddatsiz"
                       class="w-full bg-ink-950 border border-ink-border text-paper rounded-btn px-3 py-2 focus:outline-none focus:border-amber-400">
            </div>
        </div>

        {{-- Nomlar 3 tilda --}}
        <div class="space-y-3 pt-2 border-t border-ink-border">
            <h3 class="text-xs uppercase text-mist tracking-wider font-bold">Buyum Nomi (3 tilda)</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="space-y-1">
                    <label class="text-[11px] text-paper">O‘zbekcha *</label>
                    <input type="text" name="name_uz" value="{{ old('name_uz', $item->getTranslation('name', 'uz')) }}" required
                           class="w-full bg-ink-950 border border-ink-border text-paper rounded-btn px-2.5 py-1.5 focus:outline-none focus:border-amber-400">
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] text-paper">Ruscha</label>
                    <input type="text" name="name_ru" value="{{ old('name_ru', $item->getTranslation('name', 'ru')) }}"
                           class="w-full bg-ink-950 border border-ink-border text-paper rounded-btn px-2.5 py-1.5 focus:outline-none focus:border-amber-400">
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] text-paper">Inglizcha</label>
                    <input type="text" name="name_en" value="{{ old('name_en', $item->getTranslation('name', 'en')) }}"
                           class="w-full bg-ink-950 border border-ink-border text-paper rounded-btn px-2.5 py-1.5 focus:outline-none focus:border-amber-400">
                </div>
            </div>
        </div>

        {{-- Tavsiflar 3 tilda --}}
        <div class="space-y-3 pt-2 border-t border-ink-border">
            <h3 class="text-xs uppercase text-mist tracking-wider font-bold">Tavsif (3 tilda)</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="space-y-1">
                    <label class="text-[11px] text-paper">O‘zbekcha</label>
                    <textarea name="description_uz" rows="2" class="w-full bg-ink-950 border border-ink-border text-paper rounded-btn px-2.5 py-1.5 focus:outline-none focus:border-amber-400">{{ old('description_uz', $item->getTranslation('description', 'uz')) }}</textarea>
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] text-paper">Ruscha</label>
                    <textarea name="description_ru" rows="2" class="w-full bg-ink-950 border border-ink-border text-paper rounded-btn px-2.5 py-1.5 focus:outline-none focus:border-amber-400">{{ old('description_ru', $item->getTranslation('description', 'ru')) }}</textarea>
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] text-paper">Inglizcha</label>
                    <textarea name="description_en" rows="2" class="w-full bg-ink-950 border border-ink-border text-paper rounded-btn px-2.5 py-1.5 focus:outline-none focus:border-amber-400">{{ old('description_en', $item->getTranslation('description', 'en')) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Vizual Parametrlar --}}
        <div class="space-y-3 pt-2 border-t border-ink-border">
            <h3 class="text-xs uppercase text-mist tracking-wider font-bold">Vizual Parametrlar (Rang & Payload)</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-paper font-semibold">Asosiy rang (HEX)</label>
                    <input type="text" name="color" value="{{ old('color', $item->payload['color'] ?? '') }}" placeholder="#3B82F6"
                           class="w-full bg-ink-950 border border-ink-border text-paper rounded-btn px-3 py-2 focus:outline-none focus:border-amber-400">
                    <p class="text-[10px] text-mist">Format: #RRGGBB</p>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-paper font-semibold">Tartib raqami (Sort order)</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $item->sort_order) }}" min="0"
                           class="w-full bg-ink-950 border border-ink-border text-paper rounded-btn px-3 py-2 focus:outline-none focus:border-amber-400">
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-paper font-semibold">Joriy Payload JSON</label>
                <textarea name="payload_json" rows="3"
                          class="w-full bg-ink-950 border border-ink-border text-paper rounded-btn px-3 py-2 focus:outline-none focus:border-amber-400 font-mono text-xs">{{ old('payload_json', json_encode($item->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
                <p class="text-[10px] text-mist">
                    Qo‘llab-quvvatlanadigan effektlar: <code class="text-amber-400">streak_freeze</code>, <code class="text-amber-400">coin_booster</code>, <code class="text-amber-400">ai_questions</code>, <code class="text-amber-400">group_slot</code>, <code class="text-amber-400">quiz_retry</code>, <code class="text-amber-400">live_priority</code>.
                </p>
                @error('effect') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Flaglar --}}
        <div class="flex flex-wrap items-center gap-6 pt-3 border-t border-ink-border">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_consumable" value="1" {{ old('is_consumable', $item->is_consumable) ? 'checked' : '' }}
                       class="rounded border-ink-border bg-ink-950 text-amber-400 focus:ring-0">
                <span class="text-paper">Iste’mol qilinadigan buyum (Consumable)</span>
            </label>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active) ? 'checked' : '' }}
                       class="rounded border-ink-border bg-ink-950 text-amber-400 focus:ring-0">
                <span class="text-paper">Do‘konda faol ko‘rsatish</span>
            </label>
        </div>

        <div class="pt-4 flex items-center gap-3">
            <button type="submit" class="ks-btn-primary py-2.5 px-6 text-xs font-mono rounded-btn">
                O‘zgarishlarni saqlash
            </button>
            <a href="{{ route('admin.shop.index') }}" class="px-4 py-2 text-xs font-mono text-mist hover:text-paper">
                Bekor qilish
            </a>
        </div>
    </form>
</div>
@endsection
