<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUserProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $admin2;
    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $this->admin = User::create([
            'name'              => 'First Admin',
            'username'          => 'first_admin',
            'email'             => 'first_admin@kitobxon.uz',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'admin',
        ]);
        $this->admin->syncRoles(['admin']);

        $this->admin2 = User::create([
            'name'              => 'Second Admin',
            'username'          => 'second_admin',
            'email'             => 'second_admin@kitobxon.uz',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'admin',
        ]);
        $this->admin2->syncRoles(['admin']);

        $this->student = User::create([
            'name'              => 'Student User',
            'username'          => 'student_user',
            'email'             => 'protect_student@kitobxon.uz',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'student',
        ]);
        $this->student->syncRoles(['student']);
    }

    /** @test */
    public function admin_can_view_edit_page_of_other_admin(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $this->admin2));

        $response->assertOk();
    }

    /** @test */
    public function admin_can_update_other_admin_balance_but_cannot_change_role(): void
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $this->admin2), [
                'role'   => 'student', // Admin rolini o'zgartirishga urinish
                'points' => 999,
                'coins'  => 999,
            ]);

        $response->assertRedirect(route('admin.users.edit', $this->admin2));
        $response->assertSessionHas('success');

        $this->admin2->refresh();
        // Rol daxlsiz saqlanishi kerak (admin)
        $this->assertEquals('admin', $this->admin2->roles->first()?->name);
        // Balans esa muvaffaqiyatli yangilangan bo'lishi kerak
        $this->assertEquals(999, $this->admin2->total_points);
        $this->assertEquals(999, $this->admin2->coin_balance);
    }

    /** @test */
    public function admin_can_topup_other_admin_balance(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.balance.topup'), [
                'target_user_id' => $this->admin2->id,
                'points'         => 250,
                'coins'          => 100,
                'reason'         => 'Test admin topup',
            ]);

        $response->assertSessionHas('success');

        $this->admin2->refresh();
        $this->assertEquals(250, $this->admin2->total_points);
        $this->assertEquals(100, $this->admin2->coin_balance);
    }

    /** @test */
    public function admin_cannot_delete_other_admin(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->admin2));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin2->id, 'deleted_at' => null]);
    }

    /** @test */
    public function admin_cannot_ban_other_admin(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.ban', $this->admin2), [
                'duration' => '1_day',
                'reason'   => 'test',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->admin2->refresh();
        $this->assertFalse($this->admin2->isBanned());
    }

    /** @test */
    public function admin_still_can_edit_ban_and_delete_student(): void
    {
        // Edit sahifasi ochiladi
        $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $this->student))
            ->assertOk();

        // Update ishlaydi
        $this->actingAs($this->admin)
            ->put(route('admin.users.update', $this->student), [
                'role'   => 'student',
                'points' => 50,
                'coins'  => 5,
            ])
            ->assertSessionHas('success');

        $this->student->refresh();
        $this->assertEquals(50, $this->student->total_points);

        // Ban ishlaydi
        $this->actingAs($this->admin)
            ->post(route('admin.users.ban', $this->student), [
                'duration' => '1_day',
                'reason'   => 'test',
            ])
            ->assertSessionHas('success');

        $this->student->refresh();
        $this->assertTrue($this->student->isBanned());

        // Unban
        $this->actingAs($this->admin)
            ->post(route('admin.users.unban', $this->student))
            ->assertSessionHas('success');

        $this->student->refresh();
        $this->assertFalse($this->student->isBanned());

        // Delete ishlaydi
        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->student))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('users', ['id' => $this->student->id]);
    }
}
