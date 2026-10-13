<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use App\Services\Gamification\ChatStyleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatStyleAndShopTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_style_service_resolves_default_style_for_guest()
    {
        $style = ChatStyleService::resolveStyle(null);
        $this->assertEquals('default', $style['mode']);
        $this->assertNull($style['badge_text']);
    }

    public function test_chat_style_service_resolves_shop_theme_when_preference_is_shop()
    {
        $user = User::factory()->create(['username' => 'test_' . uniqid()]);
        $profile = UserProfile::create([
            'user_id' => $user->id,
            'avatar_frame' => 'cyber',
            'chat_bubble_theme' => 'cyber',
            'chat_style_preference' => 'shop',
            'unlocked_items' => ['cyber'],
        ]);
        $user->setRelation('profile', $profile);

        $style = ChatStyleService::resolveStyle($user);
        $this->assertEquals('shop', $style['mode']);
        $this->assertEquals('🔥 Cyberpunk', $style['badge_text']);
        $this->assertStringContainsString('border-rose-500', $style['bubble_class']);
    }

    public function test_chat_style_service_resolves_rank_style_when_preference_is_rank()
    {
        $user = User::factory()->create(['username' => 'test_' . uniqid(), 'total_points' => 500]);
        $profile = UserProfile::create([
            'user_id' => $user->id,
            'avatar_frame' => 'cyber',
            'chat_bubble_theme' => 'cyber',
            'chat_style_preference' => 'rank',
            'unlocked_items' => ['cyber'],
        ]);
        $user->setRelation('profile', $profile);

        // 1st place rank
        $style = ChatStyleService::resolveStyle($user, 1);
        $this->assertEquals('rank', $style['mode']);
        $this->assertEquals('🥇 Chempion', $style['badge_text']);
        $this->assertStringContainsString('border-amber-400', $style['bubble_class']);
    }

    public function test_user_can_unlock_and_switch_items()
    {
        $user = User::factory()->create(['username' => 'test_' . uniqid()]);
        $profile = UserProfile::create([
            'user_id' => $user->id,
            'chat_style_preference' => 'shop',
        ]);

        $this->assertFalse($profile->hasUnlockedItem('aurora'));

        $profile->unlockItem('aurora');
        $profile->save();

        $profile->refresh();
        $this->assertTrue($profile->hasUnlockedItem('aurora'));
    }

    public function test_chat_style_service_resolves_clean_default_when_preference_is_default()
    {
        $user = User::factory()->create(['username' => 'test_' . uniqid(), 'total_points' => 500]);
        $profile = UserProfile::create([
            'user_id' => $user->id,
            'avatar_frame' => 'cyber',
            'chat_bubble_theme' => 'cyber',
            'chat_style_preference' => 'default',
            'unlocked_items' => ['cyber'],
        ]);
        $user->setRelation('profile', $profile);

        // Even if user is Rank 1 and owns Cyber theme, 'default' MUST return clean default style
        $style = ChatStyleService::resolveStyle($user, 1);
        $this->assertEquals('default', $style['mode']);
        $this->assertNull($style['badge_text']);
        $this->assertStringContainsString('border-ink-border', $style['bubble_class']);
    }
}
