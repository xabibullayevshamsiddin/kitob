<?php

namespace Tests\Feature\Shop;

use App\Http\Livewire\Profile\ProfilePage;
use App\Models\ShopItem;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserStreak;
use Database\Seeders\ShopItemsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileShopBridgeTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        (new ShopItemsSeeder())->run();

        $this->user = User::factory()->create([
            'username'     => 'testkitobxon',
            'coin_balance' => 300,
            'total_points' => 500,
            'points_spent' => 0,
        ]);
        UserProfile::create(['user_id' => $this->user->id]);
    }

    public function test_profile_page_can_buy_streak_freeze(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ProfilePage::class, ['username' => $this->user->username])
            ->call('buyStreakFreeze')
            ->assertDispatchedBrowserEvent('toast');

        $this->assertEquals(250, $this->user->fresh()->coin_balance);
        $this->assertEquals(1, UserStreak::where('user_id', $this->user->id)->first()->freeze_count);
    }

    public function test_profile_page_can_buy_and_equip_theme(): void
    {
        $this->actingAs($this->user);
        $theme = ShopItem::where('key', 'emerald')->firstOrFail(); // 90 coins

        Livewire::test(ProfilePage::class, ['username' => $this->user->username])
            ->call('setProfileTheme', 'emerald')
            ->assertDispatchedBrowserEvent('toast');

        $this->assertEquals(210, $this->user->fresh()->coin_balance);
        $profile = $this->user->fresh()->profile;

        $this->assertEquals('emerald', $profile->profile_theme);
        $this->assertEquals('emerald', $profile->avatar_frame);
        $this->assertEquals('emerald', $profile->chat_bubble_theme);
        $this->assertTrue($profile->hasUnlockedItem('emerald'));

        // user_equipment da ham saqlangan
        $this->assertDatabaseHas('user_equipment', [
            'user_id' => $this->user->id,
            'slot'    => 'profile_theme',
        ]);
    }

    public function test_profile_page_can_unequip_theme(): void
    {
        $this->actingAs($this->user);
        $this->user->profile->update([
            'profile_theme'     => 'gold',
            'avatar_frame'      => 'gold',
            'chat_bubble_theme' => 'gold',
        ]);

        Livewire::test(ProfilePage::class, ['username' => $this->user->username])
            ->call('unequipShopTheme')
            ->assertDispatchedBrowserEvent('toast');

        $profile = $this->user->fresh()->profile;
        $this->assertNull($profile->profile_theme);
        $this->assertNull($profile->avatar_frame);
        $this->assertNull($profile->chat_bubble_theme);
        $this->assertEquals('default', $profile->chat_style_preference);
    }
}
