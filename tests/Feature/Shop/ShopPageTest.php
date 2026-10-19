<?php

namespace Tests\Feature\Shop;

use App\Http\Livewire\Shop\ShopPage;
use App\Models\ShopItem;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\Shop\ShopService;
use Database\Seeders\ShopItemsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShopPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        (new ShopItemsSeeder())->run();

        $this->user = User::factory()->create([
            'username'     => 'shopuser',
            'coin_balance' => 500,
            'total_points' => 1000,
            'points_spent' => 0,
        ]);
        UserProfile::create(['user_id' => $this->user->id]);
    }

    public function test_guest_is_redirected_to_login_from_shop_route(): void
    {
        $response = $this->get(route('shop.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_shop_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('shop.index'));
        $response->assertOk();
        $response->assertSeeLivewire(ShopPage::class);
    }

    public function test_shop_page_initial_state_and_balance(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ShopPage::class)
            ->assertSet('activeTab', 'showcase')
            ->assertSee('500');
    }

    public function test_user_can_switch_tabs_on_shop_page(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ShopPage::class)
            ->call('setTab', 'profile')
            ->assertSet('activeTab', 'profile')
            ->call('setTab', 'colors')
            ->assertSet('activeTab', 'colors')
            ->call('setTab', 'consumable')
            ->assertSet('activeTab', 'consumable')
            ->call('setTab', 'inventory')
            ->assertSet('activeTab', 'inventory')
            ->call('setTab', 'wallet')
            ->assertSet('activeTab', 'wallet');
    }

    public function test_user_can_open_purchase_modal_and_buy_item_on_shop_page(): void
    {
        $this->actingAs($this->user);
        $item = ShopItem::where('key', 'aurora')->firstOrFail();

        Livewire::test(ShopPage::class)
            ->call('promptPurchase', 'aurora')
            ->assertSet('confirmingPurchaseItemId', $item->id)
            ->call('confirmPurchase')
            ->assertSet('confirmingPurchaseItemId', null)
            ->assertDispatchedBrowserEvent('toast');

        $this->assertEquals(500 - $item->price_coins, $this->user->fresh()->coin_balance);
        $this->assertTrue(app(ShopService::class)->hasPurchased($this->user, 'aurora'));
    }

    public function test_user_can_equip_and_unequip_item_from_shop_page(): void
    {
        $this->actingAs($this->user);
        app(ShopService::class)->purchase($this->user, 'aurora', 1);

        Livewire::test(ShopPage::class)
            ->call('equipItem', 'aurora')
            ->assertDispatchedBrowserEvent('toast');

        $this->assertEquals('aurora', $this->user->fresh()->profile->profile_theme);

        Livewire::test(ShopPage::class)
            ->call('unequipItem', 'profile_theme')
            ->assertDispatchedBrowserEvent('toast');

        $this->assertNull($this->user->fresh()->profile->profile_theme);
    }

    public function test_user_cannot_purchase_item_with_insufficient_balance(): void
    {
        $poorUser = User::factory()->create([
            'username'     => 'pooruser',
            'coin_balance' => 10,
            'total_points' => 100,
            'points_spent' => 0,
        ]);
        UserProfile::create(['user_id' => $poorUser->id]);
        $this->actingAs($poorUser);

        Livewire::test(ShopPage::class)
            ->call('promptPurchase', 'aurora')
            ->call('confirmPurchase')
            ->assertDispatchedBrowserEvent('toast');

        $this->assertEquals(10, $poorUser->fresh()->coin_balance);
    }

    public function test_user_can_preview_and_close_preview_modal(): void
    {
        $this->actingAs($this->user);
        $item = ShopItem::where('key', 'aurora')->firstOrFail();

        Livewire::test(ShopPage::class)
            ->call('openPreview', 'aurora')
            ->assertSet('previewItemId', $item->id)
            ->call('closePreview')
            ->assertSet('previewItemId', null);
    }

    public function test_user_can_switch_to_perks_tab_and_purchase_perk(): void
    {
        $this->actingAs($this->user);
        $perk = ShopItem::where('key', 'ai_questions_pack')->firstOrFail();

        Livewire::test(ShopPage::class)
            ->call('setTab', 'perks')
            ->assertSet('activeTab', 'perks')
            ->call('promptPurchase', 'ai_questions_pack')
            ->call('confirmPurchase')
            ->assertDispatchedBrowserEvent('toast');

        $this->assertEquals(500 - $perk->price_coins, $this->user->fresh()->coin_balance);
        $this->assertTrue(app(ShopService::class)->hasPurchased($this->user, 'ai_questions_pack'));
    }

    public function test_user_can_switch_to_exchange_tab_and_convert_points_to_coins(): void
    {
        $this->actingAs($this->user);
        $initialCoins = $this->user->coin_balance; // 500
        $initialPoints = $this->user->total_points; // 1000

        Livewire::test(ShopPage::class)
            ->call('setTab', 'exchange')
            ->assertSet('activeTab', 'exchange')
            ->call('setExchangePoints', 150)
            ->call('confirmExchange')
            ->assertDispatchedBrowserEvent('toast')
            ->assertDispatchedBrowserEvent('shop-balance-updated')
            ->assertDispatchedBrowserEvent('coins-awarded');

        $user = $this->user->fresh();
        // 150 points / 15 = 10 coins
        $this->assertEquals($initialCoins + 10, $user->coin_balance);
        // points_spent should be 150
        $this->assertEquals(150, $user->points_spent);
        // total_points (leaderboard) MUST NOT BE TOUCHED
        $this->assertEquals($initialPoints, $user->total_points);
        // available points = 1000 - 150 = 850
        $this->assertEquals(850, $user->available_points);
    }

    public function test_exchange_fails_when_points_exceed_available_points(): void
    {
        $poorUser = User::factory()->create([
            'username'     => 'poorexchange',
            'coin_balance' => 10,
            'total_points' => 10, // less than minimum 15
            'points_spent' => 0,
        ]);
        UserProfile::create(['user_id' => $poorUser->id]);
        $this->actingAs($poorUser);

        Livewire::test(ShopPage::class)
            ->call('setTab', 'exchange')
            ->call('setExchangePoints', 15)
            ->call('confirmExchange')
            ->assertDispatchedBrowserEvent('toast');

        // Coins should not change
        $this->assertEquals(10, $poorUser->fresh()->coin_balance);
        $this->assertEquals(0, $poorUser->fresh()->points_spent);
    }
}

