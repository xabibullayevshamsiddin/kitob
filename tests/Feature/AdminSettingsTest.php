<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
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

        $this->admin = User::create([
            'name'              => 'Super Admin',
            'username'          => 'super_admin',
            'email'             => 'admin_settings@kitobxon.uz',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'admin',
        ]);
        $this->admin->syncRoles(['admin']);
        UserProfile::create(['user_id' => $this->admin->id]);

        $this->student = User::create([
            'name'              => 'Test Student',
            'username'          => 'student_settings',
            'email'             => 'student_settings@kitobxon.uz',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'student',
        ]);
        $this->student->syncRoles(['student']);
        UserProfile::create(['user_id' => $this->student->id]);
    }

    /** @test */
    public function guest_and_student_cannot_access_settings(): void
    {
        $this->get(route('admin.settings'))->assertRedirect(route('login'));

        $this->actingAs($this->student)
            ->get(route('admin.settings'))
            ->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_settings_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.settings'));

        $response->assertStatus(200);
        $response->assertSee('Tizim sozlamalari');
        $response->assertSee('PHP Versiyasi');
        $response->assertSee('Kesh boshqaruvi');
        $response->assertSee("Texnik xizmat ko'rsatish rejimi", false);
    }

    /** @test */
    public function admin_can_update_settings_successfully(): void
    {
        $payload = [
            'app_name'                       => 'Ziyo Nur Platformasi',
            'contact_email'                  => 'ziyo@nur.uz',
            'app_url'                        => 'https://ziyonur.uz',
            'timezone'                       => 'Asia/Tashkent',
            'per_page'                       => 20,
            'welcome_message'                => 'Salom yangi foydalanuvchi!',
            'telegram_channel'               => '@ziyonur',
            'reading_points_per_minute'      => 5,
            'reading_coins_per_minute'       => 2,
            'streak_minimum_minutes'         => 20,
            'quiz_passing_percent'           => 80,
            'welcome_bonus_coins'            => 100,
            'global_chat_enabled'            => '1',
            'registration_open'              => '1',
            'user_group_membership_limit'    => 10,
            'teacher_group_membership_limit' => 30,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.settings.update'), $payload);

        $response->assertRedirect(route('admin.settings'));
        $response->assertSessionHas('success');

        $this->assertEquals('Ziyo Nur Platformasi', Setting::get('app_name'));
        $this->assertEquals('ziyo@nur.uz', Setting::get('contact_email'));
        $this->assertEquals('https://ziyonur.uz', Setting::get('app_url'));
        $this->assertEquals(5, Setting::get('reading_points_per_minute'));
        $this->assertEquals(2, Setting::get('reading_coins_per_minute'));
        $this->assertEquals(20, Setting::get('streak_minimum_minutes'));
        $this->assertEquals(80, Setting::get('quiz_passing_percent'));
        $this->assertEquals(100, Setting::get('welcome_bonus_coins'));
        $this->assertTrue(Setting::get('global_chat_enabled'));
        $this->assertTrue(Setting::get('registration_open'));
        $this->assertEquals(10, Setting::get('user_group_membership_limit'));
        $this->assertEquals(30, Setting::get('teacher_group_membership_limit'));
    }

    /** @test */
    public function admin_can_clear_system_caches(): void
    {
        foreach (['clear', 'config', 'route', 'view'] as $action) {
            $response = $this->actingAs($this->admin)
                ->post(route('admin.settings.cache'), ['action' => $action]);

            $response->assertRedirect();
            $response->assertSessionHas('success');
        }
    }

    /** @test */
    public function admin_can_toggle_maintenance_mode(): void
    {
        // 1. Enable maintenance
        $response = $this->actingAs($this->admin)
            ->post(route('admin.settings.maintenance'), ['maintenance' => '1']);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertTrue(app()->isDownForMaintenance());

        // 2. Disable maintenance
        $response = $this->actingAs($this->admin)
            ->post(route('admin.settings.maintenance'), ['maintenance' => '0']);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertFalse(app()->isDownForMaintenance());
    }

    /** @test */
    public function admin_can_access_any_page_during_maintenance_while_guests_and_students_are_blocked(): void
    {
        // 1. Turn on maintenance
        Artisan::call('down', ['--secret' => 'admin-bypass']);
        $this->assertTrue(app()->isDownForMaintenance());

        // 2. Guest visiting / is blocked with 503
        $this->get('/')->assertStatus(503);

        // 3. Student visiting / is blocked with 503
        $this->actingAs($this->student)->get('/')->assertStatus(503);

        // 4. Admin visiting / can access normally (200 OK)
        $this->actingAs($this->admin)->get('/')->assertStatus(200);

        // 5. Admin visiting /admin/settings can access normally (200 OK)
        $this->actingAs($this->admin)->get(route('admin.settings'))->assertStatus(200);

        // 6. Turn off maintenance
        Artisan::call('up');
        $this->assertFalse(app()->isDownForMaintenance());
    }

    /** @test */
    public function admin_can_run_storage_link(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.settings.storage-link'));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function admin_can_send_test_email(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.settings.test-email'), [
                'test_email' => 'tester@example.com',
            ]);

        $response->assertSessionHas('success');
    }
}
