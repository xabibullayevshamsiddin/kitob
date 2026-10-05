<?php

namespace Tests\Feature;

use App\Http\Livewire\Chat\GlobalChat;
use App\Http\Livewire\Groups\GroupList;
use App\Http\Livewire\Profile\ProfilePage;
use App\Models\Book;
use App\Models\GlobalChatMessage;
use App\Models\User;
use App\Services\LeaderboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class LeaderboardPrivilegesTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }

    public function test_leaderboard_service_calculates_correct_ranks_and_tiers(): void
    {
        Cache::flush();
        $service = app(LeaderboardService::class);

        $user0 = User::factory()->create(['username' => 'u0_' . uniqid(), 'total_points' => 0]);
        $user1 = User::factory()->create(['username' => 'u1_' . uniqid(), 'total_points' => 1000]);
        $user2 = User::factory()->create(['username' => 'u2_' . uniqid(), 'total_points' => 800]);
        $user3 = User::factory()->create(['username' => 'u3_' . uniqid(), 'total_points' => 600]);
        $user4 = User::factory()->create(['username' => 'u4_' . uniqid(), 'total_points' => 400]);
        $user5 = User::factory()->create(['username' => 'u5_' . uniqid(), 'total_points' => 200]);
        $user6 = User::factory()->create(['username' => 'u6_' . uniqid(), 'total_points' => 100]);

        // Rank checks
        $this->assertNull($service->getUserRank($user0));
        $this->assertEquals(1, $service->getUserRank($user1));
        $this->assertEquals(2, $service->getUserRank($user2));
        $this->assertEquals(3, $service->getUserRank($user3));
        $this->assertEquals(4, $service->getUserRank($user4));
        $this->assertEquals(5, $service->getUserRank($user5));
        $this->assertEquals(6, $service->getUserRank($user6));

        // Tier checks
        $this->assertEquals('gold', $service->getRankTier(1));
        $this->assertEquals('silver', $service->getRankTier(2));
        $this->assertEquals('bronze', $service->getRankTier(3));
        $this->assertEquals('top5', $service->getRankTier(4));
        $this->assertEquals('top5', $service->getRankTier(5));
        $this->assertNull($service->getRankTier(6));
        $this->assertNull($service->getRankTier(null));

        // getTopFiveIds map
        $topFive = $service->getTopFiveIds();
        $this->assertCount(5, $topFive);
        $this->assertEquals(1, $topFive[$user1->id]);
        $this->assertEquals(2, $topFive[$user2->id]);
        $this->assertEquals(3, $topFive[$user3->id]);
        $this->assertEquals(4, $topFive[$user4->id]);
        $this->assertEquals(5, $topFive[$user5->id]);
        $this->assertArrayNotHasKey($user6->id, $topFive);
    }

    public function test_group_limits_grant_rank_bonus_to_top_five(): void
    {
        Cache::flush();
        $component = new GroupList();

        $userRank1 = User::factory()->create(['username' => 'r1_' . uniqid(), 'total_points' => 500, 'role' => 'student']);
        $userRank4 = User::factory()->create(['username' => 'r4_' . uniqid(), 'total_points' => 200, 'role' => 'student']);
        $userRank6 = User::factory()->create(['username' => 'r6_' . uniqid(), 'total_points' => 10, 'role' => 'student']);
        // Create 2 higher users so userRank4 is 4th
        User::factory()->create(['username' => 'r2_' . uniqid(), 'total_points' => 400]);
        User::factory()->create(['username' => 'r3_' . uniqid(), 'total_points' => 300]);
        // Create 5th user
        User::factory()->create(['username' => 'r5_' . uniqid(), 'total_points' => 50]);

        $admin = User::factory()->create(['username' => 'adm_' . uniqid(), 'role' => 'admin', 'total_points' => 1000]);

        // Base regular limit is 2
        $this->assertEquals(3, $component->getRankBonus($userRank1));
        $this->assertEquals(5, $component->getMembershipLimit($userRank1)); // 2 + 3 = 5

        $this->assertEquals(1, $component->getRankBonus($userRank4));
        $this->assertEquals(3, $component->getMembershipLimit($userRank4)); // 2 + 1 = 3

        $this->assertEquals(0, $component->getRankBonus($userRank6));
        $this->assertEquals(2, $component->getMembershipLimit($userRank6)); // 2 + 0 = 2

        // Admin remains unlimited (null)
        $this->assertNull($component->getMembershipLimit($admin));
    }

    public function test_global_chat_renders_tier_badge_for_top_five_users(): void
    {
        Cache::flush();
        $topUser = User::factory()->create([
            'name'         => 'Peshqadam Kitobxon',
            'username'     => 'peshqadam_' . uniqid(),
            'total_points' => 5000,
        ]);

        GlobalChatMessage::create([
            'user_id' => $topUser->id,
            'message' => 'Salom barchaga, yangi kitob zo\'r ekan!',
        ]);

        Livewire::test(GlobalChat::class)
            ->assertSee('Peshqadam Kitobxon')
            ->assertSee('#1')
            ->assertSee('Salom barchaga');
    }

    public function test_profile_page_displays_rank_indicator_for_top_five(): void
    {
        Cache::flush();
        $topUser = User::factory()->create([
            'name'         => 'Kumush Kitobxon',
            'username'     => 'kumush_' . uniqid(),
            'total_points' => 2000,
        ]);
        // Higher user so topUser is rank 2
        User::factory()->create([
            'username'     => 'first_' . uniqid(),
            'total_points' => 3000,
        ]);

        Livewire::actingAs($topUser)
            ->test(ProfilePage::class, ['username' => $topUser->username])
            ->assertSee('Reytingda #2 o\'rin', false)
            ->assertSee('Kumush sovrindor');
    }

    public function test_home_page_displays_faxriy_beshlik(): void
    {
        Cache::flush();
        $topUser = User::factory()->create([
            'name'         => 'Alisher Navoiy Muhlisi',
            'username'     => 'navoiy_' . uniqid(),
            'total_points' => 1500,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Faxriy Beshlik');
        $response->assertSee('Alisher Navoiy Muhlisi');
    }

    public function test_top_five_early_access_to_future_published_books(): void
    {
        Cache::flush();
        $topUser = User::factory()->create([
            'username'     => 'top_reader_' . uniqid(),
            'total_points' => 5000,
            'role'         => 'student',
        ]);

        // Create 4 more users with high points so topUser is rank 1, and regularUser is rank 6
        User::factory()->create(['username' => 'r2_' . uniqid(), 'total_points' => 4000]);
        User::factory()->create(['username' => 'r3_' . uniqid(), 'total_points' => 3000]);
        User::factory()->create(['username' => 'r4_' . uniqid(), 'total_points' => 2000]);
        User::factory()->create(['username' => 'r5_' . uniqid(), 'total_points' => 1000]);

        $regularUser = User::factory()->create([
            'username'     => 'regular_' . uniqid(),
            'total_points' => 10,
            'role'         => 'student',
        ]);

        $futureBook = Book::create([
            'title'        => 'Yangi Maxfiy Kitob',
            'slug'         => 'yangi-maxfiy-kitob-' . uniqid(),
            'author'       => 'Muallif',
            'genre'        => 'Badiiy',
            'description'  => 'Tavsif',
            'week_number'  => 10,
            'published_at' => now()->addHours(12), // Kelasi 12 soat ichida
            'is_active'    => true,
        ]);

        // Top user can access
        $this->actingAs($topUser)
            ->get(route('books.show', $futureBook->slug))
            ->assertStatus(200)
            ->assertSee('Yangi Maxfiy Kitob');

        // Regular user is denied with 403
        $this->actingAs($regularUser)
            ->get(route('books.show', $futureBook->slug))
            ->assertStatus(403);
    }
}
