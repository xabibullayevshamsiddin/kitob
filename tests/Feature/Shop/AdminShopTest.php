<?php

namespace Tests\Feature\Shop;

use App\Models\CoinTransaction;
use App\Models\Setting;
use App\Models\ShopItem;
use App\Models\User;
use App\Models\UserEquipment;
use App\Models\UserItem;
use App\Models\UserProfile;
use App\Services\Shop\ShopService;
use Database\Seeders\ShopItemsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminShopTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;
    protected ShopService $shopService;

    protected function setUp(): void
    {
        parent::setUp();
        (new ShopItemsSeeder())->run();
        $this->shopService = app(ShopService::class);

        // Admin role & user
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'coin_balance' => 1000,
        ]);
        $this->admin->assignRole('admin');

        // Regular user
        $this->regularUser = User::factory()->create([
            'role' => 'reader',
            'coin_balance' => 500,
        ]);
        UserProfile::create(['user_id' => $this->regularUser->id]);
    }

    public function test_guest_is_redirected_to_login_from_admin_shop(): void
    {
        $response = $this->get(route('admin.shop.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_admin_shop(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.shop.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_admin_shop_tabs(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.shop.index'));
        $response->assertStatus(200);
        $response->assertSee('Do‘kon va Tangalar Boshqaruvi');
        $response->assertSee('Buyumlar katalogi');
        $response->assertSee('O‘yin iqtisodiyoti');
        $response->assertSee('Almashtirish sozlamalari');
    }

    public function test_admin_can_create_new_shop_item(): void
    {
        $data = [
            'key'              => 'accent_crimson_vip',
            'category'         => 'colors',
            'slot'             => 'accent',
            'name_uz'          => 'Qirmizi Qizil Urg‘u',
            'name_ru'          => 'Багровый акцент',
            'name_en'          => 'Crimson Accent',
            'description_uz'   => 'VIP kitobxonlar uchun maxsus qizil rang.',
            'price_coins'      => 150,
            'rarity'           => 'epic',
            'is_consumable'    => 0,
            'max_quantity'     => 1,
            'is_active'        => 1,
            'sort_order'       => 99,
            'color'            => '#DC2626',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.shop.items.store'), $data);
        $response->assertRedirect(route('admin.shop.index', ['tab' => 'items']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shop_items', [
            'key'         => 'accent_crimson_vip',
            'price_coins' => 150,
            'category'    => 'colors',
            'slot'        => 'accent',
            'rarity'      => 'epic',
        ]);
    }

    public function test_admin_can_edit_existing_shop_item(): void
    {
        $item = ShopItem::where('key', 'aurora')->firstOrFail();

        $data = [
            'category'         => 'bundle',
            'slot'             => 'profile_theme',
            'name_uz'          => 'Yangi Qutb Yog‘dusi',
            'name_ru'          => 'Новое Северное Сияние',
            'name_en'          => 'New Aurora Borealis',
            'description_uz'   => 'Yangilangan sehrli yashil nur.',
            'price_coins'      => 199,
            'rarity'           => 'legendary',
            'max_quantity'     => 1,
            'is_active'        => 1,
            'sort_order'       => 5,
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.shop.items.update', $item), $data);
        $response->assertRedirect(route('admin.shop.index', ['tab' => 'items']));

        $item->refresh();
        $this->assertEquals(199, $item->price_coins);
        $this->assertEquals('legendary', $item->rarity);
        $this->assertEquals('Yangi Qutb Yog‘dusi', $item->getTranslation('name', 'uz'));
    }

    public function test_admin_can_toggle_shop_item_status(): void
    {
        $item = ShopItem::where('key', 'gold')->firstOrFail();
        $this->assertTrue($item->is_active);

        $response = $this->actingAs($this->admin)->post(route('admin.shop.items.toggle', $item));
        $response->assertSessionHas('success');

        $item->refresh();
        $this->assertFalse($item->is_active);
    }

    public function test_admin_can_update_exchange_settings(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.shop.settings.update'), [
            'rate_points_per_coin' => 4,
            'min_points'           => 40,
            'max_points_per_day'   => 400,
            'exchange_enabled'     => 1,
        ]);

        $response->assertRedirect(route('admin.shop.index', ['tab' => 'settings']));
        $response->assertSessionHas('success');

        $this->assertEquals(4, Setting::get('shop_exchange_rate'));
        $this->assertEquals(40, Setting::get('shop_exchange_min_points'));
        $this->assertEquals(400, Setting::get('shop_exchange_daily_limit'));
        $this->assertTrue((bool) Setting::get('shop_exchange_enabled'));
    }

    public function test_admin_can_refund_user_purchased_item(): void
    {
        $item = ShopItem::where('key', 'aurora')->firstOrFail();

        // Foydalanuvchi buyumni xarid qiladi va kiyadi
        $this->shopService->purchase($this->regularUser, $item, 1, 'refund-test-token');
        $this->shopService->equip($this->regularUser, $item);

        $this->regularUser->refresh();
        $this->assertEquals(380, $this->regularUser->coin_balance); // 500 - 120 = 380
        $this->assertDatabaseHas('user_items', ['user_id' => $this->regularUser->id, 'shop_item_id' => $item->id]);
        $this->assertDatabaseHas('user_equipment', ['user_id' => $this->regularUser->id, 'shop_item_id' => $item->id]);

        // Admin buyumni qaytarib oladi (Refund)
        $response = $this->actingAs($this->admin)->post(route('admin.shop.refund'), [
            'user_id'      => $this->regularUser->id,
            'shop_item_id' => $item->id,
            'quantity'     => 1,
            'reason'       => 'Mijoz shikoyati bo‘yicha qaytarildi',
        ]);

        $response->assertRedirect(route('admin.shop.index', ['tab' => 'users']));
        $response->assertSessionHas('success');

        $this->regularUser->refresh();
        $this->assertEquals(500, $this->regularUser->coin_balance); // 380 + 120 = 500

        // Inventar va jihozdan o'chgan
        $this->assertDatabaseMissing('user_items', ['user_id' => $this->regularUser->id, 'shop_item_id' => $item->id]);
        $this->assertDatabaseMissing('user_equipment', ['user_id' => $this->regularUser->id, 'shop_item_id' => $item->id]);

        // Refund tranzaksiyasi yozilgan
        $this->assertDatabaseHas('coin_transactions', [
            'user_id'        => $this->regularUser->id,
            'coins'          => 120,
            'source'         => 'refund',
            'reference_type' => ShopItem::class,
            'reference_id'   => $item->id,
        ]);
    }
}
