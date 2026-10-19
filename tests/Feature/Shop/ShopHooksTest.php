<?php

namespace Tests\Feature\Shop;

use App\Http\Livewire\Groups\GroupList;
use App\Models\Book;
use App\Models\ShopItem;
use App\Models\User;
use App\Models\UserItem;
use App\Models\UserProfile;
use App\Services\Shop\Exceptions\ShopException;
use App\Services\Shop\ShopService;
use Database\Seeders\ShopItemsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopHooksTest extends TestCase
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
            'coin_balance' => 1000,
            'total_points' => 500,
            'points_spent' => 0,
        ]);
        UserProfile::create(['user_id' => $this->user->id]);
    }

    public function test_ai_questions_pack_consumption_and_silent_cleanup(): void
    {
        $pack = ShopItem::where('key', 'ai_questions_pack')->firstOrFail();

        // 1 paket xarid qilinadi (payload['amount'] = 10 ta savol beradi)
        $this->shopService->purchase($this->user, $pack, 1);

        $userItem = UserItem::where('user_id', $this->user->id)
            ->where('shop_item_id', $pack->id)
            ->firstOrFail();

        $this->assertEquals(10, $userItem->quantity);

        // 1 ta savol sarflanadi
        $consumed = $this->shopService->consumeAiQuestion($this->user);
        $this->assertTrue($consumed);

        $userItem->refresh();
        $this->assertEquals(9, $userItem->quantity);

        // Qolgan 8 tasini sarflaymiz (1 ta qoladi)
        for ($i = 0; $i < 8; $i++) {
            $this->shopService->consumeAiQuestion($this->user);
        }

        $userItem->refresh();
        $this->assertEquals(1, $userItem->quantity);

        // Oxirgi 1 ta sarflanganda — jim o'chadi (delete)
        $consumedLast = $this->shopService->consumeAiQuestion($this->user);
        $this->assertTrue($consumedLast);

        $this->assertDatabaseMissing('user_items', [
            'user_id'      => $this->user->id,
            'shop_item_id' => $pack->id,
        ]);

        // Paket tugagach yana sarflashga urinsa — false qaytadi
        $this->assertFalse($this->shopService->consumeAiQuestion($this->user));
    }

    public function test_extra_group_slot_hook_increases_membership_limit(): void
    {
        $groupSlotItem = ShopItem::where('key', 'extra_group_slot')->firstOrFail();

        $groupList = new GroupList();
        $baseLimit = $groupList->getMembershipLimit($this->user);

        // 1 ta qo'shimcha guruh o'rni xarid qilamiz
        $this->shopService->purchase($this->user, $groupSlotItem, 1);

        $newLimit = $groupList->getMembershipLimit($this->user);
        $this->assertEquals($baseLimit + 1, $newLimit);

        // Yana 1 ta xarid qilamiz
        $this->shopService->purchase($this->user, $groupSlotItem, 1);
        $this->assertEquals($baseLimit + 2, $groupList->getMembershipLimit($this->user));
    }

    public function test_coin_booster_multiplies_reading_coins_and_prevents_duplicate_purchase(): void
    {
        $booster = ShopItem::whereIn('key', ['coin_booster_60', 'coin_booster'])->firstOrFail();

        // 1. Kuchaytirgichsiz: standart multiplier 1.0
        $this->assertEquals(1.0, $this->shopService->getActiveCoinBoosterMultiplier($this->user));
        $this->assertFalse($this->shopService->hasActiveBooster($this->user));

        // 2. Kuchaytirgichni xarid qilamiz (60 daqiqa)
        $this->shopService->purchase($this->user, $booster, 1);

        $this->assertTrue($this->shopService->hasActiveBooster($this->user));
        $this->assertEquals(2.0, $this->shopService->getActiveCoinBoosterMultiplier($this->user));

        // 3. Kuchaytirgich faol paytida qayta sotib olishga urinsa — xato beradi (bir turdagi ustma-ust qo'yilmasin)
        $this->expectException(ShopException::class);
        $this->shopService->purchase($this->user, $booster, 1);
    }

    public function test_reading_heartbeat_applies_booster_multiplier(): void
    {
        $booster = ShopItem::whereIn('key', ['coin_booster_60', 'coin_booster'])->firstOrFail();
        $this->shopService->purchase($this->user, $booster, 1);

        $initialBalance = $this->user->fresh()->coin_balance;

        $book = Book::create([
            'title'       => 'Test Kitob',
            'slug'        => 'test-kitob-' . uniqid(),
            'author'      => 'Test Muallif',
            'description' => 'Test tavsifi',
            'genre'       => 'badiiy',
            'week_number' => 1,
            'is_active'   => true,
        ]);

        // 5 daqiqalik mutolaa sessiyasi yuboramiz
        $response = $this->actingAs($this->user)->postJson(route('reading.heartbeat'), [
            'book_id'   => $book->id,
            'minutes'   => 5,
            'page_type' => 'book',
        ]);

        $response->assertStatus(200);

        // Standart: 1 daqiqa = 1 tanga. 2x kuchaytirgich bilan: 5 daqiqa * 1 * 2 = 10 tanga berilishi lozim
        $this->assertEquals($initialBalance + 10, $this->user->fresh()->coin_balance);
    }
}
