<?php

namespace Tests\Feature\Shop;

use App\Models\ShopItem;
use App\Models\User;
use App\Models\UserItem;
use App\Models\UserProfile;
use App\Services\Shop\Exceptions\ShopException;
use App\Services\Shop\ShopService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ShopRateLimiterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private ShopService $shopService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'coin_balance' => 1000,
            'total_points' => 2000,
            'points_spent' => 0,
        ]);
        UserProfile::create(['user_id' => $this->user->id]);

        $this->shopService = app(ShopService::class);
        RateLimiter::clear('shop:purchase:' . $this->user->id);
        RateLimiter::clear('shop:exchange:' . $this->user->id);
        RateLimiter::clear('shop:equip:' . $this->user->id);
    }

    /**
     * Xarid tezligi cheklovi: daqiqasiga belgilangan sondan oshganda ShopException tashlanadi.
     */
    public function test_purchase_rate_limiter_blocks_excessive_attempts(): void
    {
        config(['shop.throttle.purchases_per_minute' => 3]);

        $item = ShopItem::create([
            'key'           => 'rate_test_potion',
            'name'          => 'Tezlik testi eliksiri',
            'category'      => 'consumable',
            'slot'          => null,
            'price_coins'   => 10,
            'is_consumable' => true,
            'max_quantity'  => 100,
            'is_active'     => true,
            'payload'       => [],
        ]);

        // 3 ta xarid muvaffaqiyatli o'tadi
        $this->shopService->purchase($this->user, $item);
        $this->shopService->purchase($this->user, $item);
        $this->shopService->purchase($this->user, $item);

        // 4-xarid limit sababli rad etiladi
        $this->expectException(ShopException::class);
        $this->expectExceptionMessage(trans('shop.throttle_purchase', ['seconds' => 60]));

        $this->shopService->purchase($this->user, $item);
    }

    /**
     * Ball almashtirish tezligi cheklovi tekshiriladi.
     */
    public function test_exchange_rate_limiter_blocks_excessive_attempts(): void
    {
        config([
            'shop.throttle.exchange_per_minute' => 2,
            'shop.exchange.rate_points_per_coin' => 5,
            'shop.exchange.min_points' => 5,
            'shop.exchange.max_points_per_day' => 1000,
        ]);

        // 2 ta almashtirish muvaffaqiyatli
        $this->shopService->exchangePointsToCoins($this->user, 5);
        $this->shopService->exchangePointsToCoins($this->user, 5);

        // 3-almashtirish limit sababli rad etiladi
        $this->expectException(ShopException::class);
        $this->expectExceptionMessage(trans('shop.throttle_exchange', ['seconds' => 60]));

        $this->shopService->exchangePointsToCoins($this->user, 5);
    }

    /**
     * Jihozlash (equip) tezligi cheklovi tekshiriladi.
     */
    public function test_equip_rate_limiter_blocks_excessive_attempts(): void
    {
        config(['shop.throttle.equip_per_minute' => 2]);

        $item = ShopItem::create([
            'key'           => 'rate_test_theme',
            'name'          => 'Tezlik mavzusi',
            'category'      => 'profile',
            'slot'          => 'profile_theme',
            'price_coins'   => 10,
            'is_consumable' => false,
            'max_quantity'  => 1,
            'is_active'     => true,
            'payload'       => ['color' => '#10B981'],
        ]);

        UserItem::create([
            'user_id'      => $this->user->id,
            'shop_item_id' => $item->id,
            'quantity'     => 1,
            'acquired_at'  => now(),
        ]);

        // 2 ta kiyish muvaffaqiyatli
        $this->shopService->equip($this->user, $item);
        $this->shopService->equip($this->user, $item);

        // 3-kiyish limit sababli rad etiladi
        $this->expectException(ShopException::class);
        $this->expectExceptionMessage(trans('shop.throttle_equip', ['seconds' => 60]));

        $this->shopService->equip($this->user, $item);
    }
}
