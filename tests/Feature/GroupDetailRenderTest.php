<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupDetailRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_detail_page_renders_for_member(): void
    {
        $user = User::factory()->create(['username' => 'gmember_' . uniqid()]);

        // GroupFactory mavjud emas — kerak bo'lsa to'g'ridan-to'g'ri yaratamiz
        $group = Group::first();
        $createdGroup = false;
        if (!$group) {
            $group = Group::create([
                'name'       => 'Test Guruhi ' . uniqid(),
                'slug'       => 'test-guruh-' . uniqid(),
                'created_by' => $user->id,
            ]);
            $createdGroup = true;
        }

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/groups/' . $group->id);

        $response->assertStatus(200);
        $response->assertSee('Guruh suhbati', false);

        // Tozalash (faqat o'zimiz yaratgan narsalarni)
        GroupMember::where('user_id', $user->id)->delete();
        if ($createdGroup) {
            $group->forceDelete();
        }
        $user->delete();
    }
}
