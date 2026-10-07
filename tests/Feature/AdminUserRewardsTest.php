<?php

namespace Tests\Feature;

use App\Models\CoinTransaction;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUserRewardsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $this->admin = User::create([
            'name'              => 'Admin User',
            'username'          => 'admin_user',
            'email'             => 'admin_rewards@kitobxon.uz',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'admin',
        ]);
        $this->admin->syncRoles(['admin']);

        $this->student = User::create([
            'name'              => 'Student User',
            'username'          => 'student_user',
            'email'             => 'student_rewards@kitobxon.uz',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'student',
            'total_points'      => 100,
            'coin_balance'      => 10,
        ]);
        $this->student->syncRoles(['student']);
    }

    /** @test */
    public function admin_can_add_points_and_coins_to_user(): void
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $this->student), [
                'role'   => 'student',
                'points' => 150,
                'coins'  => 40,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->student->refresh();
        $this->assertEquals(150, $this->student->total_points);
        $this->assertEquals(40, $this->student->coin_balance);

        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $this->student->id,
            'points'  => 50,
            'source'  => 'admin',
        ]);
        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $this->student->id,
            'coins'   => 30,
            'source'  => 'admin',
        ]);
    }

    /** @test */
    public function admin_can_subtract_points_and_coins_from_user(): void
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $this->student), [
                'role'   => 'student',
                'points' => 60,
                'coins'  => 5,
            ]);

        $response->assertRedirect();

        $this->student->refresh();
        $this->assertEquals(60, $this->student->total_points);
        $this->assertEquals(5, $this->student->coin_balance);

        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $this->student->id,
            'points'  => -40,
            'source'  => 'admin',
        ]);
        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $this->student->id,
            'coins'   => -5,
            'source'  => 'admin',
        ]);
    }

    /** @test */
    public function unchanged_balances_create_no_transactions(): void
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $this->student), [
                'role'   => 'student',
                'points' => 100,
                'coins'  => 10,
            ]);

        $response->assertRedirect();

        $this->student->refresh();
        $this->assertEquals(100, $this->student->total_points);
        $this->assertEquals(10, $this->student->coin_balance);

        $this->assertEquals(0, PointTransaction::where('user_id', $this->student->id)->count());
        $this->assertEquals(0, CoinTransaction::where('user_id', $this->student->id)->count());
    }

    /** @test */
    public function negative_values_are_rejected(): void
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $this->student), [
                'role'   => 'student',
                'points' => -5,
                'coins'  => 0,
            ]);

        $response->assertSessionHasErrors(['points']);

        $this->student->refresh();
        $this->assertEquals(100, $this->student->total_points);
    }
}
