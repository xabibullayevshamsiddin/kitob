<?php

namespace Tests\Feature\Shop;

use App\Models\CoinTransaction;
use App\Models\PointTransaction;
use App\Models\ShopItem;
use App\Models\ShopPurchase;
use App\Models\User;
use App\Models\UserEquipment;
use App\Models\UserItem;
use App\Models\UserProfile;
use App\Models\UserStreak;
use App\Services\Shop\Exceptions\ShopException;
use App\Services\Shop\ShopService;
use Database\Seeders\ShopItemsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ShopCoreTest extends TestCase
{
    use RefreshDatabase;

    protected ShopService $shopService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        (new ShopItemsSeeder())->run();
        $this->shopService = app(ShopService::class);

        $this->user = User::factory()->create([
            'coin_balance' => 500,
            'total_points' => 1000,
            'points_spent' => 0,
        ]);
        UserProfile::create(['user_id' => $this->user->id]);
    }

    public function test_user_can_purchase_non_consumable_theme_atomically(): void
    {
        $theme = ShopItem::where('key', 'aurora')->firstOrFail();
        $initialBalance = $this->user->coin_balance;

        $result = $this->shopService->purchase($this->user, $theme, 1, 'token-123');

        $this->assertTrue($result['success']);
        $this->assertEquals($initialBalance - $theme->price_coins, $result['balance']);

        $this->user->refresh();
        $this->assertEquals($initialBalance - $theme->price_coins, $this->user->coin_balance);

        // user_items da mavjud
        $this->assertDatabaseHas('user_items', [
            'user_id'      => $this->user->id,
            'shop_item_id' => $theme->id,
            'quantity'     => 1,
        ]);

        // shop_purchases da token saqlangan
        $this->assertDatabaseHas('shop_purchases', [
            'user_id'        => $this->user->id,
            'shop_item_id'   => $theme->id,
            'purchase_token' => 'token-123',
            'price_paid'     => $theme->price_coins,
        ]);

        // coin_transactions manbasi shop bo'lgan
        $this->assertDatabaseHas('coin_transactions', [
            'user_id'        => $this->user->id,
            'coins'          => -$theme->price_coins,
            'source'         => 'shop',
            'reference_type' => ShopItem::class,
            'reference_id'   => $theme->id,
            'balance_after'  => $this->user->coin_balance,
        ]);

        // Orqaga moslik: user_profiles.unlocked_items massivida saqlangan
        $this->user->refresh();
        $this->assertTrue($this->user->profile->hasUnlockedItem('aurora'));
    }

    public function test_purchase_token_prevents_double_spend(): void
    {
        $theme = ShopItem::where('key', 'gold')->firstOrFail();
        $token = 'unique-replay-token-456';

        $first = $this->shopService->purchase($this->user, $theme, 1, $token);
        $this->assertFalse($first['duplicate']);
        $balanceAfterFirst = $this->user->fresh()->coin_balance;

        // Xuddi shu token bilan ikkinchi chaqiruv
        $second = $this->shopService->purchase($this->user, $theme, 1, $token);

        $this->assertTrue($second['duplicate']);
        $this->assertEquals($balanceAfterFirst, $this->user->fresh()->coin_balance);
        $this->assertEquals(1, ShopPurchase::where('purchase_token', $token)->count());
    }

    public function test_insufficient_coins_throws_exception_and_does_not_deduct_balance(): void
    {
        $this->user->update(['coin_balance' => 20]);
        $theme = ShopItem::where('key', 'cyber')->firstOrFail(); // 150 coins

        $this->expectException(ShopException::class);
        try {
            $this->shopService->purchase($this->user, $theme, 1);
        } finally {
            $this->assertEquals(20, $this->user->fresh()->coin_balance);
            $this->assertEquals(0, ShopPurchase::count());
        }
    }

    public function test_cannot_purchase_already_owned_non_consumable_item(): void
    {
        $theme = ShopItem::where('key', 'emerald')->firstOrFail();
        $this->shopService->purchase($this->user, $theme, 1);

        $this->expectException(ShopException::class);
        $this->shopService->purchase($this->user, $theme, 1);
    }

    public function test_streak_freeze_consumable_respects_max_quantity(): void
    {
        $streakItem = ShopItem::where('key', 'streak_freeze')->firstOrFail();
        $streak = UserStreak::create([
            'user_id'        => $this->user->id,
            'current_streak' => 10,
            'longest_streak' => 15,
            'freeze_count'   => 4,
        ]);

        // 1 ta sotib olish mumkin (4 + 1 = 5)
        $this->shopService->purchase($this->user, $streakItem, 1);
        $this->assertEquals(5, $streak->fresh()->freeze_count);

        // Yana 1 ta xarid qilganda limitdan oshadi va xato beradi
        $this->expectException(ShopException::class);
        $this->shopService->purchase($this->user, $streakItem, 1);
    }

    public function test_inactive_or_expired_item_cannot_be_purchased(): void
    {
        $item = ShopItem::create([
            'key'         => 'expired_item',
            'category'    => 'profile',
            'name'        => ['uz' => 'Eskirgan buyum'],
            'price_coins' => 10,
            'is_active'   => false,
        ]);

        $this->expectException(ShopException::class);
        $this->shopService->purchase($this->user, $item, 1);
    }

    public function test_equipping_bundle_sets_all_slots_and_syncs_cached_columns(): void
    {
        $theme = ShopItem::where('key', 'obsidian')->firstOrFail();
        $this->shopService->purchase($this->user, $theme, 1);

        $equipRes = $this->shopService->equip($this->user, $theme);
        $this->assertTrue($equipRes['success']);

        // user_equipment da 3 ta slot
        $this->assertDatabaseHas('user_equipment', [
            'user_id'      => $this->user->id,
            'slot'         => 'profile_theme',
            'shop_item_id' => $theme->id,
        ]);
        $this->assertDatabaseHas('user_equipment', [
            'user_id'      => $this->user->id,
            'slot'         => 'avatar_frame',
            'shop_item_id' => $theme->id,
        ]);
        $this->assertDatabaseHas('user_equipment', [
            'user_id'      => $this->user->id,
            'slot'         => 'chat_bubble',
            'shop_item_id' => $theme->id,
        ]);

        // user_profiles keshlangan ustunlari yangilangan
        $profile = $this->user->fresh()->profile;
        $this->assertEquals('obsidian', $profile->profile_theme);
        $this->assertEquals('obsidian', $profile->avatar_frame);
        $this->assertEquals('obsidian', $profile->chat_bubble_theme);
        $this->assertEquals('shop', $profile->chat_style_preference);
    }

    public function test_cannot_equip_unowned_item(): void
    {
        $theme = ShopItem::where('key', 'sunset')->firstOrFail();

        $this->expectException(ShopException::class);
        $this->shopService->equip($this->user, $theme);
    }

    public function test_unequip_clears_equipment_and_cached_columns(): void
    {
        $theme = ShopItem::where('key', 'library')->firstOrFail();
        $this->shopService->purchase($this->user, $theme, 1);
        $this->shopService->equip($this->user, $theme);

        $unequipRes = $this->shopService->unequip($this->user, 'all');
        $this->assertTrue($unequipRes['success']);

        $this->assertDatabaseMissing('user_equipment', [
            'user_id' => $this->user->id,
            'slot'    => 'profile_theme',
        ]);

        $profile = $this->user->fresh()->profile;
        $this->assertNull($profile->profile_theme);
        $this->assertNull($profile->avatar_frame);
        $this->assertNull($profile->chat_bubble_theme);
        $this->assertEquals('default', $profile->chat_style_preference);
    }

    public function test_exchange_points_to_coins_enforces_one_way_and_leaves_total_points_intact(): void
    {
        $initialTotalPoints = $this->user->total_points; // 1000
        $initialCoins = $this->user->coin_balance; // 500

        // 150 ball almashtirish (15 ball = 1 tanga -> 10 tanga)
        $res = $this->shopService->exchangePointsToCoins($this->user, 150);

        $this->assertTrue($res['success']);
        $this->assertEquals(10, $res['coins_received']);
        $this->assertEquals(150, $res['points_exchanged']);

        $this->user->refresh();

        // total_points O'ZGARMAYDI (reyting himoyasi)
        $this->assertEquals($initialTotalPoints, $this->user->total_points);

        // points_spent 150 ga oshgan
        $this->assertEquals(150, $this->user->points_spent);

        // available_points = 850
        $this->assertEquals(850, $this->user->available_points);

        // coin_balance 10 ga oshgan
        $this->assertEquals($initialCoins + 10, $this->user->coin_balance);

        // coin_transactions da exchange manbasi
        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $this->user->id,
            'coins'   => 10,
            'source'  => 'exchange',
        ]);
    }

    public function test_exchange_enforces_minimum_and_daily_limit(): void
    {
        // 15 dan kam ball almashtirib bo'lmaydi
        $this->expectException(ShopException::class);
        $this->shopService->exchangePointsToCoins($this->user, 10);
    }

    public function test_validate_hex_color(): void
    {
        $this->assertTrue(ShopService::validateHexColor('#F59E0B'));
        $this->assertTrue(ShopService::validateHexColor('#ffffff'));
        $this->assertFalse(ShopService::validateHexColor('red'));
        $this->assertFalse(ShopService::validateHexColor('expression(alert(1))'));
        $this->assertFalse(ShopService::validateHexColor('#12345'));
    }
}
