<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Models\Setting;
use App\Models\ShopItem;
use App\Models\ShopPurchase;
use App\Models\User;
use App\Models\UserItem;
use App\Models\UserProfile;
use App\Services\Analytics\EconomyMetrics;
use App\Services\Shop\Exceptions\ShopException;
use App\Services\Shop\ShopService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function __construct(
        protected ShopService $shopService
    ) {
    }

    /**
     * Do'kon boshqaruv paneli (Tabs: buyumlar, iqtisodiyot, sozlamalar, inventar/refund).
     */
    public function index(Request $request, EconomyMetrics $economy): View
    {
        $activeTab = $request->get('tab', 'items');
        $validTabs = ['items', 'economy', 'settings', 'users'];
        if (!in_array($activeTab, $validTabs, true)) {
            $activeTab = 'items';
        }

        // 1. Buyumlar (Items) tab ma'lumotlari
        $query = ShopItem::query();

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($cat = $request->get('category')) {
            if ($cat !== 'all') {
                $query->where('category', $cat);
            }
        }

        if ($rarity = $request->get('rarity')) {
            if ($rarity !== 'all') {
                $query->where('rarity', $rarity);
            }
        }

        if ($request->has('status') && $request->get('status') !== 'all') {
            $query->where('is_active', $request->boolean('status'));
        }

        $items = $query->orderBy('sort_order')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        // 2. O'yin iqtisodiyoti (Economy) tab ma'lumotlari
        $economyData = $economy->getShopEconomyMetrics($request->boolean('refresh'));
        $recentPurchases = ShopPurchase::with(['user:id,name,email', 'shopItem:id,key,name,category,price_coins,rarity'])
            ->latest('id')
            ->take(15)
            ->get();

        // 3. Sozlamalar (Settings) tab ma'lumotlari
        $exchangeSettings = [
            'rate_points_per_coin' => $this->shopService->getExchangeRate(),
            'min_points'           => $this->shopService->getExchangeMinPoints(),
            'max_points_per_day'   => $this->shopService->getExchangeDailyLimit(),
            'enabled'              => $this->shopService->isExchangeEnabled(),
        ];

        // 4. Foydalanuvchi buyumlari va Qaytarish (User Items & Refunds) tab
        $userItemsQuery = UserItem::with(['user:id,name,email,coin_balance', 'shopItem:id,key,name,category,price_coins,rarity'])
            ->latest('id');

        if ($userSearch = trim((string) $request->get('user_search'))) {
            $userItemsQuery->whereHas('user', function ($q) use ($userSearch) {
                $q->where('name', 'like', "%{$userSearch}%")
                  ->orWhere('email', 'like', "%{$userSearch}%");
            })->orWhereHas('shopItem', function ($q) use ($userSearch) {
                $q->where('key', 'like', "%{$userSearch}%")
                  ->orWhere('name', 'like', "%{$userSearch}%");
            });
        }

        $userItems = $userItemsQuery->paginate(20, ['*'], 'users_page')->withQueryString();

        $recentRefunds = CoinTransaction::with('user:id,name,email')
            ->where('source', 'refund')
            ->latest('id')
            ->take(10)
            ->get();

        // 5. Katalog va migratsiya holatini tekshirish (Ogohlantirishlar)
        $shopWarnings = [];
        $itemsTotalCount = ShopItem::count();
        if ($itemsTotalCount === 0) {
            $shopWarnings[] = [
                'type'    => 'danger',
                'title'   => 'Do‘kon katalogi yuklanmagan!',
                'message' => 'Hozirda shop_items jadvalida birorta ham buyum mavjud emas. Foydalanuvchilarga do‘kon bo‘sh ko‘rinadi. Katalog va standart buyumlarni yuklash uchun ushbu buyruqni ishga tushiring: php artisan shop:install',
            ];
        }

        $unmigratedCount = UserProfile::query()
            ->whereNotNull('unlocked_items')
            ->where('unlocked_items', '!=', '[]')
            ->where('unlocked_items', '!=', '')
            ->whereDoesntHave('user.userItems')
            ->count();

        if ($unmigratedCount > 0) {
            $shopWarnings[] = [
                'type'    => 'warning',
                'title'   => 'Eski xaridlar yangi do‘konga ko‘chirilmagan!',
                'message' => "Eski tizimda mavzular sotib olgan {$unmigratedCount} ta foydalanuvchining inventari hali yangi Shop jadvaliga ko‘chirilmagan. Ularning buyumlari ko‘rinishi uchun ushbu buyruqni ishga tushiring: php artisan shop:install",
            ];
        }

        return view('admin.shop.index', [
            'activeTab'        => $activeTab,
            'items'            => $items,
            'economyData'      => $economyData,
            'recentPurchases'  => $recentPurchases,
            'exchangeSettings' => $exchangeSettings,
            'userItems'        => $userItems,
            'recentRefunds'    => $recentRefunds,
            'shopWarnings'     => $shopWarnings,
            'slots'            => config('shop.slots', []),
            'rarities'         => config('shop.rarities', []),
        ]);
    }

    /**
     * Yangi buyum yaratish formasi.
     */
    public function create(): View
    {
        return view('admin.shop.create', [
            'slots'    => config('shop.slots', []),
            'rarities' => config('shop.rarities', []),
        ]);
    }

    /**
     * Yangi buyumni bazaga saqlash.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key'              => 'required|string|max:64|unique:shop_items,key|regex:/^[a-z0-9_]+$/',
            'category'         => 'required|in:profile,colors,consumable,perk,bundle',
            'slot'             => 'nullable|string|in:avatar_frame,profile_theme,chat_bubble,name_color,title,accent,reader_palette,highlight_color',
            'name_uz'          => 'required|string|max:120',
            'name_ru'          => 'nullable|string|max:120',
            'name_en'          => 'nullable|string|max:120',
            'description_uz'   => 'nullable|string|max:500',
            'description_ru'   => 'nullable|string|max:500',
            'description_en'   => 'nullable|string|max:500',
            'price_coins'      => 'required|integer|min:0|max:100000',
            'rarity'           => 'required|in:common,rare,epic,legendary',
            'is_consumable'    => 'boolean',
            'max_quantity'     => 'required|integer|min:1|max:999',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_active'        => 'boolean',
            'sort_order'       => 'nullable|integer|min:0',
            'color'            => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'payload_json'     => 'nullable|string',
        ]);

        $payload = $this->buildPayload($request);

        ShopItem::create([
            'key'              => trim($validated['key']),
            'category'         => $validated['category'],
            'slot'             => $validated['slot'] ?: null,
            'name'             => [
                'uz' => $validated['name_uz'],
                'ru' => !empty($validated['name_ru']) ? $validated['name_ru'] : $validated['name_uz'],
                'en' => !empty($validated['name_en']) ? $validated['name_en'] : $validated['name_uz'],
            ],
            'description'      => [
                'uz' => $validated['description_uz'] ?? '',
                'ru' => $validated['description_ru'] ?? '',
                'en' => $validated['description_en'] ?? '',
            ],
            'price_coins'      => (int) $validated['price_coins'],
            'rarity'           => $validated['rarity'],
            'payload'          => $payload,
            'is_consumable'    => $request->boolean('is_consumable'),
            'max_quantity'     => (int) ($validated['max_quantity'] ?? 1),
            'duration_minutes' => !empty($validated['duration_minutes']) ? (int) $validated['duration_minutes'] : null,
            'is_active'        => $request->boolean('is_active', true),
            'sort_order'       => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()->route('admin.shop.index', ['tab' => 'items'])
            ->with('success', "«{$validated['name_uz']}» buyumi muvaffaqiyatli yaratildi!");
    }

    /**
     * Buyumni tahrirlash formasi.
     */
    public function edit(ShopItem $item): View
    {
        return view('admin.shop.edit', [
            'item'     => $item,
            'slots'    => config('shop.slots', []),
            'rarities' => config('shop.rarities', []),
        ]);
    }

    /**
     * Buyum ma'lumotlarini yangilash.
     */
    public function update(Request $request, ShopItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'category'         => 'required|in:profile,colors,consumable,perk,bundle',
            'slot'             => 'nullable|string|in:avatar_frame,profile_theme,chat_bubble,name_color,title,accent,reader_palette,highlight_color',
            'name_uz'          => 'required|string|max:120',
            'name_ru'          => 'nullable|string|max:120',
            'name_en'          => 'nullable|string|max:120',
            'description_uz'   => 'nullable|string|max:500',
            'description_ru'   => 'nullable|string|max:500',
            'description_en'   => 'nullable|string|max:500',
            'price_coins'      => 'required|integer|min:0|max:100000',
            'rarity'           => 'required|in:common,rare,epic,legendary',
            'is_consumable'    => 'boolean',
            'max_quantity'     => 'required|integer|min:1|max:999',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_active'        => 'boolean',
            'sort_order'       => 'nullable|integer|min:0',
            'color'            => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'payload_json'     => 'nullable|string',
        ]);

        $payload = $this->buildPayload($request, $item->payload);

        $item->update([
            'category'         => $validated['category'],
            'slot'             => $validated['slot'] ?: null,
            'name'             => [
                'uz' => $validated['name_uz'],
                'ru' => !empty($validated['name_ru']) ? $validated['name_ru'] : $validated['name_uz'],
                'en' => !empty($validated['name_en']) ? $validated['name_en'] : $validated['name_uz'],
            ],
            'description'      => [
                'uz' => $validated['description_uz'] ?? '',
                'ru' => $validated['description_ru'] ?? '',
                'en' => $validated['description_en'] ?? '',
            ],
            'price_coins'      => (int) $validated['price_coins'],
            'rarity'           => $validated['rarity'],
            'payload'          => $payload,
            'is_consumable'    => $request->boolean('is_consumable'),
            'max_quantity'     => (int) ($validated['max_quantity'] ?? 1),
            'duration_minutes' => !empty($validated['duration_minutes']) ? (int) $validated['duration_minutes'] : null,
            'is_active'        => $request->boolean('is_active'),
            'sort_order'       => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()->route('admin.shop.index', ['tab' => 'items'])
            ->with('success', "«{$item->name_text}» ma'lumotlari yangilandi!");
    }

    /**
     * Buyumning faol/nofaolligini bir zumda almashtirish (Toggle active/hidden).
     */
    public function toggleActive(ShopItem $item): RedirectResponse
    {
        $item->is_active = !$item->is_active;
        $item->save();

        $statusLabel = $item->is_active ? 'faollashtirildi' : 'yashirildi';
        return back()->with('success', "«{$item->name_text}» do'konda {$statusLabel}.");
    }

    /**
     * Ball -> Tanga almashtirish sozlamalarini yangilash.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rate_points_per_coin' => 'required|integer|min:1|max:100',
            'min_points'           => 'required|integer|min:1|max:10000',
            'max_points_per_day'   => 'required|integer|min:1|max:100000',
            'exchange_enabled'     => 'boolean',
        ]);

        Setting::set('shop_exchange_rate', (int) $validated['rate_points_per_coin'], 'shop', 'integer');
        Setting::set('shop_exchange_min_points', (int) $validated['min_points'], 'shop', 'integer');
        Setting::set('shop_exchange_daily_limit', (int) $validated['max_points_per_day'], 'shop', 'integer');
        Setting::set('shop_exchange_enabled', (bool) $request->boolean('exchange_enabled'), 'shop', 'boolean');

        return redirect()->route('admin.shop.index', ['tab' => 'settings'])
            ->with('success', "Almashtirish sozlamalari muvaffaqiyatli saqlandi!");
    }

    /**
     * Foydalanuvchidan buyumni qaytarib olish (Refund amali).
     */
    public function refundUserItem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id'      => 'required|integer|exists:users,id',
            'shop_item_id' => 'required|integer|exists:shop_items,id',
            'quantity'     => 'nullable|integer|min:1',
            'reason'       => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $item = ShopItem::findOrFail($validated['shop_item_id']);

        try {
            $result = $this->shopService->refund(
                $user,
                $item,
                $validated['quantity'] ?? null,
                $validated['reason'] ?? 'Admin tomonidan qaytarib olindi'
            );

            return redirect()->route('admin.shop.index', ['tab' => 'users'])
                ->with('success', $result['message']);
        } catch (ShopException $e) {
            return redirect()->route('admin.shop.index', ['tab' => 'users'])
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Payload JSON / rang parametrlarini shakllantirish va CSS inyeksiyasidan tozalash.
     */
    protected function buildPayload(Request $request, ?array $current = null): array
    {
        $payload = $current ?? [];

        // Agar to'g'ridan-to'g'ri JSON kiritilgan bo'lsa
        if ($jsonStr = trim((string) $request->get('payload_json'))) {
            $decoded = json_decode($jsonStr, true);
            if (is_array($decoded)) {
                $payload = array_merge($payload, $decoded);
            }
        }

        // Rang parametri kiritilgan bo'lsa
        if ($color = $request->get('color')) {
            $cleaned = trim($color);
            if (ShopService::validateHexColor($cleaned)) {
                $payload['color'] = $cleaned;
            }
        }

        // Reader fon ranglari
        if ($request->filled('bg_light') && ShopService::validateHexColor($request->bg_light)) {
            $payload['bg_light'] = trim($request->bg_light);
        }
        if ($request->filled('bg_dark') && ShopService::validateHexColor($request->bg_dark)) {
            $payload['bg_dark'] = trim($request->bg_dark);
        }
        if ($request->filled('text_light') && ShopService::validateHexColor($request->text_light)) {
            $payload['text_light'] = trim($request->text_light);
        }
        if ($request->filled('text_dark') && ShopService::validateHexColor($request->text_dark)) {
            $payload['text_dark'] = trim($request->text_dark);
        }

        // Effekt parametrlari
        if ($effect = trim((string) $request->get('effect'))) {
            $payload['effect'] = $effect;
        }
        if ($request->filled('multiplier')) {
            $payload['multiplier'] = (float) $request->multiplier;
        }
        if ($request->filled('amount')) {
            $payload['amount'] = (int) $request->amount;
        }

        return $payload;
    }
}
