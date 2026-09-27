<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserStreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAndPublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
    }

    /** @test */
    public function public_pages_are_accessible_without_auth()
    {
        $res = $this->get('/');
        $res->assertStatus(200);
        $res->assertSee('faol kitobxon safimizda');
        $res->assertSee('Platformadagi kitoblar');

        $this->get('/about')->assertStatus(200)->assertSee('Platformadagi kitoblar');
        $this->get('/contact')->assertStatus(200);
        $this->get('/faq')->assertStatus(200);
        $this->get('/books')->assertStatus(200);
    }

    /** @test */
    public function student_cannot_access_admin_panel()
    {
        $student = User::firstOrCreate(
            ['email' => 'test_student@kitobxon.uz'],
            [
                'name' => 'Test Student',
                'username' => 'test_student',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $student->syncRoles(['student']);
        UserProfile::firstOrCreate(['user_id' => $student->id], ['reading_place' => 'home']);

        $response = $this->actingAs($student)->get('/admin');
        $response->assertStatus(403);
    }

    /** @test */
    public function student_cannot_access_teacher_panel()
    {
        // Har bir test o'z userini yaratadi (RefreshDatabase tranzaksiyalari testlarni izolyatsiya qiladi)
        $student = User::firstOrCreate(
            ['email' => 'test_student2@kitobxon.uz'],
            [
                'name' => 'Test Student 2',
                'username' => 'test_student2',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $student->syncRoles(['student']);
        UserProfile::firstOrCreate(['user_id' => $student->id], ['reading_place' => 'home']);

        $response = $this->actingAs($student)->get('/teacher');
        $response->assertStatus(403);
    }

    /** @test */
    public function teacher_can_access_teacher_panel()
    {
        $teacher = User::firstOrCreate(
            ['email' => 'test_teacher@kitobxon.uz'],
            [
                'name' => 'Test Teacher',
                'username' => 'test_teacher',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $teacher->syncRoles(['teacher']);
        UserProfile::firstOrCreate(['user_id' => $teacher->id], ['reading_place' => 'other']);

        $response = $this->actingAs($teacher)->get('/teacher');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_admin_panel()
    {
        $admin = User::firstOrCreate(
            ['email' => 'test_admin@kitobxon.uz'],
            [
                'name' => 'Test Admin',
                'username' => 'test_admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);
        UserProfile::firstOrCreate(['user_id' => $admin->id], ['reading_place' => 'home']);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_is_redirected_from_general_dashboard_to_admin_panel()
    {
        $admin = User::firstOrCreate(
            ['email' => 'test_admin2@kitobxon.uz'],
            [
                'name' => 'Test Admin 2',
                'username' => 'test_admin2',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);
        UserProfile::firstOrCreate(['user_id' => $admin->id], ['reading_place' => 'home']);

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertRedirect(route('admin.dashboard'));
    }

    /** @test */
    public function teacher_is_redirected_from_general_dashboard_to_teacher_panel()
    {
        $teacher = User::firstOrCreate(
            ['email' => 'test_teacher2@kitobxon.uz'],
            [
                'name' => 'Test Teacher 2',
                'username' => 'test_teacher2',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $teacher->syncRoles(['teacher']);
        UserProfile::firstOrCreate(['user_id' => $teacher->id], ['reading_place' => 'other']);

        $response = $this->actingAs($teacher)->get('/dashboard');
        $response->assertRedirect(route('teacher.dashboard'));
    }
}
