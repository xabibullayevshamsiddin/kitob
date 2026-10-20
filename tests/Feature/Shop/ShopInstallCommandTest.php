<?php

namespace Tests\Feature\Shop;

use App\Models\ShopItem;
use App\Models\User;
use App\Models\UserProfile;
use Database\Seeders\ShopItemsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ShopInstallCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_install_runs_successfully_and_seeds_items(): void
    {
        $this->artisan('shop:install')
            ->expectsOutputToContain('DO\'KON YAKUNIY STATISTIKASI')
            ->assertExitCode(0);

        $this->assertGreaterThan(0, ShopItem::count());
        $this->assertDatabaseHas('shop_items', ['key' => 'aurora']);
        $this->assertDatabaseHas('shop_items', ['key' => 'streak_freeze']);
    }

    public function test_shop_install_is_idempotent_and_preserves_admin_edits(): void
    {
        // 1. Initial install
        $this->artisan('shop:install')->assertExitCode(0);

        // 2. Admin edits a price
        $aurora = ShopItem::where('key', 'aurora')->firstOrFail();
        $aurora->price_coins = 999;
        $aurora->save();

        // 3. Second install without flags
        $this->artisan('shop:install')->assertExitCode(0);

        // Price should still be 999
        $aurora->refresh();
        $this->assertEquals(999, $aurora->price_coins);
    }

    public function test_shop_install_with_refresh_defaults_overwrites_edits(): void
    {
        // 1. Initial install
        $this->artisan('shop:install')->assertExitCode(0);

        // 2. Admin edits a price
        $aurora = ShopItem::where('key', 'aurora')->firstOrFail();
        $aurora->price_coins = 999;
        $aurora->save();

        // 3. Install with --refresh-defaults
        $this->artisan('shop:install', ['--refresh-defaults' => true])->assertExitCode(0);

        // Price should be reset to default 120
        $aurora->refresh();
        $this->assertEquals(120, $aurora->price_coins);
    }

    public function test_shop_install_migrates_legacy_user_unlocked_items(): void
    {
        $user = User::factory()->create();
        UserProfile::create([
            'user_id' => $user->id,
            'unlocked_items' => ['aurora', 'cyber'],
            'profile_theme' => 'aurora',
        ]);

        $this->artisan('shop:install')->assertExitCode(0);

        $auroraItem = ShopItem::where('key', 'aurora')->firstOrFail();
        $cyberItem = ShopItem::where('key', 'cyber')->firstOrFail();

        $this->assertDatabaseHas('user_items', [
            'user_id' => $user->id,
            'shop_item_id' => $auroraItem->id,
        ]);

        $this->assertDatabaseHas('user_items', [
            'user_id' => $user->id,
            'shop_item_id' => $cyberItem->id,
        ]);

        $this->assertDatabaseHas('user_equipment', [
            'user_id' => $user->id,
            'slot' => 'profile_theme',
            'shop_item_id' => $auroraItem->id,
        ]);
    }
}
