<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SettingsEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'reader', 'guard_name' => 'web']);

        $this->admin = User::create([
            'name'              => 'Admin User',
            'username'          => 'admin_user',
            'email'             => 'admin_test@kitobxon.uz',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'admin',
        ]);
        $this->admin->syncRoles(['admin']);
        UserProfile::create(['user_id' => $this->admin->id]);

        $this->student = User::create([
            'name'              => 'Student User',
            'username'          => 'student_user',
            'email'             => 'student_test@kitobxon.uz',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'student',
        ]);
        $this->student->syncRoles(['student']);
        UserProfile::create(['user_id' => $this->student->id]);
    }

    /** @test */
    public function global_chat_disabled_setting_blocks_regular_users(): void
    {
        // 1. Disable chat in settings
        Setting::set('global_chat_enabled', false, 'community', 'boolean');

        // 2. Regular student attempts to send message
        Livewire::actingAs($this->student)
            ->test(\App\Http\Livewire\Chat\GlobalChat::class)
            ->set('message', 'Salom hammaga')
            ->call('sendMessage')
            ->assertHasErrors(['message']);

        $this->assertDatabaseMissing('global_chat_messages', [
            'user_id' => $this->student->id,
            'message' => 'Salom hammaga',
        ]);

        // 3. Enable chat in settings
        Setting::set('global_chat_enabled', true, 'community', 'boolean');

        Livewire::actingAs($this->student)
            ->test(\App\Http\Livewire\Chat\GlobalChat::class)
            ->set('message', 'Salom hammaga')
            ->call('sendMessage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('global_chat_messages', [
            'user_id' => $this->student->id,
            'message' => 'Salom hammaga',
        ]);
    }

    /** @test */
    public function group_membership_limit_setting_is_enforced(): void
    {
        // 1. Set limit to 2 groups
        Setting::set('user_group_membership_limit', 2, 'community', 'integer');

        $group1 = Group::create(['name' => 'Group 1', 'slug' => 'group-1', 'created_by' => $this->admin->id, 'is_private' => false]);
        $group2 = Group::create(['name' => 'Group 2', 'slug' => 'group-2', 'created_by' => $this->admin->id, 'is_private' => false]);
        $group3 = Group::create(['name' => 'Group 3', 'slug' => 'group-3', 'created_by' => $this->admin->id, 'is_private' => false]);

        // Join group 1
        Livewire::actingAs($this->student)
            ->test(\App\Http\Livewire\Groups\GroupList::class)
            ->call('join', $group1->id);

        $this->assertDatabaseHas('group_members', ['group_id' => $group1->id, 'user_id' => $this->student->id]);

        // Join group 2
        Livewire::actingAs($this->student)
            ->test(\App\Http\Livewire\Groups\GroupList::class)
            ->call('join', $group2->id);

        $this->assertDatabaseHas('group_members', ['group_id' => $group2->id, 'user_id' => $this->student->id]);

        // Join group 3 — should be BLOCKED because limit is 2
        Livewire::actingAs($this->student)
            ->test(\App\Http\Livewire\Groups\GroupList::class)
            ->call('join', $group3->id);

        $this->assertDatabaseMissing('group_members', ['group_id' => $group3->id, 'user_id' => $this->student->id]);
    }

    /** @test */
    public function registration_open_setting_is_enforced_and_welcome_coins_awarded(): void
    {
        // 1. Disable registration
        Setting::set('registration_open', false, 'community', 'boolean');

        $response = $this->post(route('register'), [
            'name'                  => 'New Guy',
            'username'              => 'newguy1',
            'email'                 => 'newguy1@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseMissing('users', ['email' => 'newguy1@example.com']);

        // 2. Enable registration with 75 welcome coins
        Setting::set('registration_open', true, 'community', 'boolean');
        Setting::set('welcome_bonus_coins', 75, 'gamification', 'integer');

        $response = $this->post(route('register'), [
            'name'                  => 'New Guy',
            'username'              => 'newguy2',
            'email'                 => 'newguy2@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $newUser = User::where('email', 'newguy2@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals(75, $newUser->coin_balance);
        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $newUser->id,
            'coins'   => 75,
            'source'  => 'bonus',
        ]);
    }

    /** @test */
    public function reading_heartbeat_respects_points_and_coins_settings(): void
    {
        Setting::set('reading_points_per_minute', 7, 'gamification', 'integer');
        Setting::set('reading_coins_per_minute', 3, 'gamification', 'integer');

        $response = $this->actingAs($this->student)
            ->postJson(route('reading.heartbeat'), [
                'minutes'   => 2,
                'page_type' => 'book',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'      => true,
            'points_added' => 14, // 2 minutes * 7 points
            'coins_added'  => 6,  // 2 minutes * 3 coins
        ]);

        $this->student->refresh();
        $this->assertEquals(14, $this->student->total_points);
        $this->assertEquals(6, $this->student->coin_balance);
    }
}
