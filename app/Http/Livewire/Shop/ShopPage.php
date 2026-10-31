<?php

namespace App\Http\Livewire\Shop;

use App\Http\Livewire\Concerns\WithToast;
use App\Models\CoinTransaction;
use App\Models\ShopItem;
use App\Models\UserEquipment;
use App\Models\UserItem;
use App\Services\Gamification\MysteryChestService;
use App\Services\Shop\Exceptions\ShopException;
use App\Services\Shop\ShopService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class ShopPage extends Component
{
    use WithToast;

    public string $activeTab = 'showcase'; // showcase, profile, colors, consumable, perks, exchange, chest, inventory, wallet

    // Mystery Chest holati
    public bool $chestOpening = false;
    public ?array $chestReward = null;
    public bool $showChestResult = false;
    public string $search = '';
    public string $rarityFilter = 'all'; // all, common, rare, epic, legendary
    public string $walletFilter = 'all'; // all, earned, spent

    // Preview modal holati
    public ?int $previewItemId = null;

    // Xarid oynasi holati
    public ?int $confirmingPurchaseItemId = null;
    public int $purchaseQuantity = 1;
    public string $purchaseToken = '';

    // Ball -> Tanga almashtirish holati
    public int $exchangePoints = 15;
    public bool $confirmingExchange = false;

    // So'nggi xarid qilingan buyum (tezkor jihozlash taklifi uchun)
    public ?int $lastPurchasedItemId = null;

    protected $queryString = [
        'activeTab'    => ['except' => 'showcase', 'as' => 'tab'],
        'rarityFilter' => ['except' => 'all', 'as' => 'rarity'],
        'search'       => ['except' => '', 'as' => 'q'],
    ];

    public function mount(): void
    {
        if (!Auth::check()) {
            redirect()->guest(route('login'));
            return;
        }

        $validTabs = ['showcase', 'profile', 'colors', 'consumable', 'perks', 'exchange', 'chest', 'inventory', 'wallet'];
        if (!in_array($this->activeTab, $validTabs, true)) {
            $this->activeTab = 'showcase';
        }

        /** @var ShopService $shopService */
        $shopService = app(ShopService::class);
        $this->exchangePoints = $shopService->getExchangeMinPoints();
    }

    public function setTab(string $tab): void
    {
        $validTabs = ['showcase', 'profile', 'colors', 'consumable', 'perks', 'exchange', 'chest', 'inventory', 'wallet'];
        if (in_array($tab, $validTabs, true)) {
            $this->activeTab = $tab;
            $this->search = '';
            $this->rarityFilter = 'all';
        }
    }

    /**
     * Mijoz tomonida sinab ko'rish (Preview) oynasini ochish.
     */
    public function openPreview($item): void
    {
        if (is_string($item) && !is_numeric($item)) {
            $found = ShopItem::where('key', $item)->first();
            $this->previewItemId = $found?->id;
            return;
        }
        $this->previewItemId = (int) $item;
    }

    public function closePreview(): void
    {
        $this->previewItemId = null;
    }

    /**
     * Xarid tasdiqlash oynasini ochish (yangi purchase_token bilan).
     */
    public function openPurchaseModal($item, int $quantity = 1): void
    {
        if (is_string($item) && !is_numeric($item)) {
            $found = ShopItem::where('key', $item)->first();
            $this->confirmingPurchaseItemId = $found?->id;
        } else {
            $this->confirmingPurchaseItemId = (int) $item;
        }
        $this->purchaseQuantity = max(1, $quantity);
        $this->purchaseToken = Str::random(40);
        $this->lastPurchasedItemId = null;
    }

    public function promptPurchase($item, int $quantity = 1): void
    {
        $this->openPurchaseModal($item, $quantity);
    }

    public function closePurchaseModal(): void
    {
        $this->confirmingPurchaseItemId = null;
        $this->purchaseQuantity = 1;
        $this->purchaseToken = '';
    }

    /**
     * Xaridni tasdiqlash va atomar amalga oshirish.
     */
    public function confirmPurchase(): void
    {
        if (!Auth::check() || !$this->confirmingPurchaseItemId) {
            return;
        }

        /** @var ShopService $shopService */
        $shopService = app(ShopService::class);
        $user = Auth::user();

        try {
            $result = $shopService->purchase(
                $user,
                $this->confirmingPurchaseItemId,
                $this->purchaseQuantity,
                $this->purchaseToken
            );

            $user->refresh();
            Auth::setUser($user);
            $this->lastPurchasedItemId = $this->confirmingPurchaseItemId;
            $purchasedItem = ShopItem::find($this->confirmingPurchaseItemId);

            $this->toastSuccess($result['message']);

            // Brauzerga balans sanash va audio/vizual effekt hodisasi yuborish
            $this->dispatchBrowserEvent('shop-balance-updated', [
                'newBalance' => (int) $user->coin_balance,
                'newPoints'  => (int) $user->total_points,
            ]);

            $this->dispatchBrowserEvent('coins-awarded', [
                'coins'    => 0,
                'newTotal' => (int) $user->coin_balance,
            ]);

            $this->dispatchBrowserEvent('shop-item-bought', [
                'itemId'   => $this->confirmingPurchaseItemId,
                'itemName' => $purchasedItem ? $purchasedItem->name_text : '',
            ]);

            $this->closePurchaseModal();
        } catch (ShopException $e) {
            $this->toastWarning($e->getMessage());
        } catch (\Throwable $e) {
            $this->toastError('Xarid jarayonida kutilmagan xatolik yuz berdi.');
        }
    }

    /**
     * Ball -> Tanga almashtirish miqdorini o'rnatish.
     */
    public function setExchangePoints(int $points): void
    {
        $this->exchangePoints = max(1, $points);
    }

    public function openExchangeModal(): void
    {
        $this->confirmingExchange = true;
    }

    public function closeExchangeModal(): void
    {
        $this->confirmingExchange = false;
    }

    /**
     * Ball -> Tanga almashtirishni tasdiqlash va atomar amalga oshirish.
     */
    public function confirmExchange(): void
    {
        if (!Auth::check()) {
            return;
        }

        /** @var ShopService $shopService */
        $shopService = app(ShopService::class);
        $user = Auth::user();

        try {
            $result = $shopService->exchangePointsToCoins($user, $this->exchangePoints);
            $user->refresh();
            Auth::setUser($user);

            $this->toastSuccess($result['message']);

            $this->dispatchBrowserEvent('shop-balance-updated', [
                'newBalance' => (int) $user->coin_balance,
                'newPoints'  => (int) $user->total_points,
            ]);

            $this->dispatchBrowserEvent('coins-awarded', [
                'coins'    => $result['coins_received'],
                'newTotal' => (int) $user->coin_balance,
            ]);

            $this->dispatchBrowserEvent('points-awarded', [
                'points'   => 0,
                'newTotal' => (int) $user->total_points,
            ]);

            $this->confirmingExchange = false;
        } catch (ShopException $e) {
            $this->toastWarning($e->getMessage());
        } catch (\Throwable $e) {
            $this->toastError('Almashtirishda xatolik yuz berdi.');
        }
    }

    /**
     * Mystery Chest ochish (450 ball sarflab tasodifiy mukofot olish, kuniga max 3 marta).
     */
    public function openMysteryChest(): void
    {
        if (!Auth::check()) {
            $this->toastError('Iltimos, avval tizimga kiring.');
            return;
        }

        $user = Auth::user();

        try {
            $chestService = app(MysteryChestService::class);
            $reward = $chestService->open($user);

            $user->refresh();
            Auth::setUser($user);
            $this->chestReward = $reward;
            $this->showChestResult = true;

            $this->toastSuccess("🎁 Omad Sandig'i ochildi: {$reward['icon']} {$reward['label']}!");

            $this->dispatchBrowserEvent('chest-opened', [
                'reward' => $reward,
            ]);

            // Header va sahifadagi balanslarni darhol jonli yangilash
            $this->dispatchBrowserEvent('shop-balance-updated', [
                'newBalance' => (int) $user->coin_balance,
                'newPoints'  => (int) $user->total_points,
            ]);

            $this->dispatchBrowserEvent('points-awarded', [
                'points'   => 0,
                'newTotal' => (int) $user->total_points,
            ]);

            $this->dispatchBrowserEvent('coins-awarded', [
                'coins'    => ($reward['type'] === 'coins' ? (int) $reward['value'] : 0),
                'newTotal' => (int) $user->coin_balance,
            ]);

            if ($reward['type'] === 'item' && $reward['value'] === 'streak_freeze') {
                $this->dispatchBrowserEvent('streak-updated', [
                    'streak' => (int) ($user->streak?->current_streak ?? 0),
                ]);
            }
        } catch (\RuntimeException $e) {
            $this->toastWarning($e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            $this->toastError("Sandiq ochishda xatolik yuz berdi.");
        }
    }

    public function closeChestResult(): void
    {
        $this->showChestResult = false;
        $this->chestReward = null;
    }

    /**
     * Buyumni jihozlash (Equip).
     */
    public function equipItem($item): void
    {
        if (!Auth::check()) {
            return;
        }

        /** @var ShopService $shopService */
        $shopService = app(ShopService::class);
        $user = Auth::user();

        try {
            $res = $shopService->equip($user, $item);
            $user->unsetRelation('profile');
            $user->load('profile');

            $this->toastSuccess($res['message']);
        } catch (ShopException $e) {
            $this->toastWarning($e->getMessage());
        } catch (\Throwable $e) {
            $this->toastError('Jihozlashda xatolik yuz berdi.');
        }
    }

    /**
     * Slotdan buyumni yechish (Unequip).
     */
    public function unequipSlot(string $slot): void
    {
        if (!Auth::check()) {
            return;
        }

        /** @var ShopService $shopService */
        $shopService = app(ShopService::class);
        $user = Auth::user();

        try {
            $res = $shopService->unequip($user, $slot);
            $user->unsetRelation('profile');
            $user->load('profile');

            $this->toastInfo($res['message']);
        } catch (ShopException $e) {
            $this->toastWarning($e->getMessage());
        } catch (\Throwable $e) {
            $this->toastError('Buyumni yechishda xatolik yuz berdi.');
        }
    }

    public function unequipItem(string $slot): void
    {
        $this->unequipSlot($slot);
    }

    public function render(): View
    {
        $user = Auth::user();

        // 1. Foydalanuvchi ega bo'lgan buyumlar xaritasi (itemId => UserItem)
        $userInventory = UserItem::with('shopItem')
            ->where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get();

        $ownedItemsMap = $userInventory->keyBy('shop_item_id');

        // 2. Foydalanuvchi kiyib turgan buyumlar (slot => shop_item_id)
        $equippedMap = UserEquipment::where('user_id', $user->id)
            ->pluck('shop_item_id', 'slot')
            ->toArray();

        // 3. Tab bo'yicha buyumlar ro'yxati
        $itemsQuery = ShopItem::active()->orderBy('sort_order')->orderBy('price_coins');

        if ($this->search !== '') {
            $s = trim($this->search);
            $itemsQuery->where(function ($q) use ($s) {
                $q->where('key', 'like', "%{$s}%")
                    ->orWhere('name', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%");
            });
        }

        if ($this->rarityFilter !== 'all') {
            $itemsQuery->where('rarity', $this->rarityFilter);
        }

        $items = collect();
        $dailyRecommendation = null;
        $featuredItems = collect();
        $transactions = collect();
        $monthEarned = 0;
        $monthSpent = 0;

        if ($this->activeTab === 'showcase') {
            // Kunlik tavsiya: bugungi kunga bog'langan deterministik tavsiya
            $allActive = ShopItem::active()->get();
            if ($allActive->isNotEmpty()) {
                $dayOfYear = (int) now()->format('z');
                $dailyRecommendation = $allActive->get($dayOfYear % $allActive->count());
            }

            // Ommabop va yangi buyumlar
            $featuredItems = ShopItem::active()
                ->where('category', 'bundle')
                ->take(4)
                ->get();

            $consumableHighlights = ShopItem::active()
                ->where('category', 'consumable')
                ->take(3)
                ->get();

            $newArrivals = ShopItem::active()
                ->whereIn('category', ['profile', 'colors'])
                ->latest()
                ->take(4)
                ->get();
        } elseif ($this->activeTab === 'profile') {
            $items = $itemsQuery->whereIn('category', ['bundle', 'profile'])->get();
        } elseif ($this->activeTab === 'colors') {
            $items = $itemsQuery->where('category', 'colors')->get();
        } elseif ($this->activeTab === 'consumable') {
            $items = $itemsQuery->where('category', 'consumable')->get();
        } elseif ($this->activeTab === 'perks') {
            $items = $itemsQuery->where('category', 'perk')->get();
        } elseif ($this->activeTab === 'inventory') {
            // Inventarda foydalanuvchi egalik qiladigan buyumlar
            $items = $userInventory;
        } elseif ($this->activeTab === 'wallet') {
            $txQuery = CoinTransaction::where('user_id', $user->id)->latest('id');

            if ($this->walletFilter === 'earned') {
                $txQuery->where('coins', '>', 0);
            } elseif ($this->walletFilter === 'spent') {
                $txQuery->where('coins', '<', 0);
            }

            $transactions = $txQuery->take(40)->get();

            $startOfMonth = Carbon::now('Asia/Tashkent')->startOfMonth()->utc();
            $monthEarned = (int) CoinTransaction::where('user_id', $user->id)
                ->where('coins', '>', 0)
                ->where('created_at', '>=', $startOfMonth)
                ->sum('coins');

            $monthSpent = abs((int) CoinTransaction::where('user_id', $user->id)
                ->where('coins', '<', 0)
                ->where('created_at', '>=', $startOfMonth)
                ->sum('coins'));
        }

        // Preview qilinayotgan buyum
        $previewItem = $this->previewItemId ? ShopItem::find($this->previewItemId) : null;

        // Xarid qilinayotgan buyum
        $purchaseItem = $this->confirmingPurchaseItemId ? ShopItem::find($this->confirmingPurchaseItemId) : null;

        // Ball -> Tanga hisob-kitoblari
        /** @var \App\Services\Shop\ShopService $shopService */
        $shopService = app(\App\Services\Shop\ShopService::class);

        /** @var \App\Services\Telegram\TelegramPaymentService $paymentService */
        $paymentService = app(\App\Services\Telegram\TelegramPaymentService::class);
        $starsPaymentsEnabled = $paymentService->isPaymentsEnabled();
        $starPacks = $paymentService->getCoinPacks();

        $exchangeRate = $shopService->getExchangeRate();
        $exchangeMinPoints = $shopService->getExchangeMinPoints();
        $exchangeDailyLimit = $shopService->getExchangeDailyLimit();
        $todayStart = Carbon::now('Asia/Tashkent')->startOfDay()->utc();
        $coinsExchangedToday = (int) CoinTransaction::where('user_id', $user->id)
            ->where('source', 'exchange')
            ->where('created_at', '>=', $todayStart)
            ->sum('coins');
        $pointsSpentToday = $coinsExchangedToday * $exchangeRate;
        $dailyRemainingPoints = max(0, $exchangeDailyLimit - $pointsSpentToday);
        $availablePoints = $user->available_points;
        $coinsToReceive = (int) floor($this->exchangePoints / $exchangeRate);

        return view('livewire.shop.shop-page', [
            'user'                 => $user,
            'ownedItemsMap'        => $ownedItemsMap,
            'equippedMap'          => $equippedMap,
            'items'                => $items,
            'dailyRecommendation'  => $dailyRecommendation,
            'featuredItems'        => $featuredItems ?? collect(),
            'consumableHighlights' => $consumableHighlights ?? collect(),
            'newArrivals'          => $newArrivals ?? collect(),
            'transactions'         => $transactions,
            'monthEarned'          => $monthEarned,
            'monthSpent'           => $monthSpent,
            'previewItem'          => $previewItem,
            'purchaseItem'         => $purchaseItem,
            'freezeCount'          => optional($user->streak)->freeze_count ?? 0,
            'exchangeRate'         => $exchangeRate,
            'exchangeMinPoints'    => $exchangeMinPoints,
            'exchangeDailyLimit'   => $exchangeDailyLimit,
            'dailyRemainingPoints' => $dailyRemainingPoints,
            'availablePoints'      => $availablePoints,
            'coinsToReceive'       => $coinsToReceive,
            'starsPaymentsEnabled' => $starsPaymentsEnabled,
            'starPacks'            => $starPacks,
            'cardTopupEnabled'     => $cardTopupEnabled = app(\App\Services\Topup\TopupService::class)->isEnabled(),
            // O'chiq paytda tugma faqat adminga ko'rinadi (sozlab sinash uchun)
            'cardTopupVisible'     => $cardTopupEnabled || $user->isAdmin(),
            'chestCost'            => MysteryChestService::getCost(),
            'dailyRemainingChests' => MysteryChestService::getDailyRemaining($user->id),
            'dailyMaxChests'       => MysteryChestService::DAILY_LIMIT,
            'chestReward'          => $this->chestReward,
            'showChestResult'      => $this->showChestResult,
        ])->layout('layouts.app', ['title' => trans('shop.title') . ' — Kitobxon']);
    }

    /**
     * Telegram Stars orqali tanga paketi xarid qilish (Invoice).
     */
    public function buyCoinPackWithStars(string $packId): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->toastError('Iltimos, avval tizimga kiring.');
            return;
        }

        /** @var \App\Services\Telegram\TelegramPaymentService $paymentService */
        $paymentService = app(\App\Services\Telegram\TelegramPaymentService::class);

        $result = $paymentService->createCoinPackInvoice($user, $packId);

        if (!$result['success']) {
            $this->toastError($result['message']);
            return;
        }

        $this->toastSuccess('Telegram Stars to\'lov havolasi yaratildi!');
        $this->emit('openTelegramInvoice', $result['invoice_link']);
        $this->dispatchBrowserEvent('open-url', ['url' => $result['invoice_link']]);
    }
}
