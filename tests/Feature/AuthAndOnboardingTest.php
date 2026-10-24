<?php

namespace Tests\Feature;

use App\Http\Livewire\Auth\OnboardingWizard;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AuthAndOnboardingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_screen_can_be_rendered()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /** @test */
    public function register_screen_can_be_rendered()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_initiate_2fa_login_using_the_login_screen()
    {
        $user = User::updateOrCreate(
            ['email' => 'testuser@kitobxon.uz'],
            [
                'name' => 'Test User',
                'username' => 'test_auth_user',
                'password' => Hash::make('password123'),
                'role' => 'student',
            ]
        );

        $response = $this->post('/login', [
            'email' => 'testuser@kitobxon.uz',
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $this->assertStringContainsString('/login/verify-code/', $response->headers->get('Location'));
    }

    /** @test */
    public function unauthenticated_users_are_redirected_to_login()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function newly_registered_user_without_onboarding_is_redirected_to_onboarding_from_home_and_dashboard()
    {
        $user = User::create([
            'name' => 'New Reader',
            'username' => 'new_reader_1',
            'email' => 'newreader@kitobxon.uz',
            'password' => Hash::make('password123'),
            'role' => 'student',
        ]);

        $this->assertFalse($user->hasCompletedOnboarding());

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(route('onboarding'));

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('onboarding'));
    }

    /** @test */
    public function onboarding_wizard_supports_multilanguage_5_steps_and_awards_welcome_bonus()
    {
        $user = User::create([
            'name' => 'Onboarding Reader',
            'username' => 'onboarding_reader',
            'email' => 'onboard@kitobxon.uz',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'total_points' => 0,
            'coin_balance' => 0,
        ]);

        $this->actingAs($user);

        Livewire::test(OnboardingWizard::class)
            ->assertSet('currentStep', 1)
            ->call('selectLocale', 'ru')
            ->assertSet('preferred_locale', 'ru')
            ->call('toggleReadingLanguage', 'en')
            ->set('reading_place', 'university')
            ->call('nextStep')
            ->assertSet('currentStep', 2)
            ->set('reading_goal', 'career')
            ->call('nextStep')
            ->assertSet('currentStep', 3)
            ->call('toggleGenre', 'psychology')
            ->call('toggleGenre', 'it_science')
            ->set('reading_format', 'audio')
            ->call('nextStep')
            ->assertSet('currentStep', 4)
            ->set('daily_minutes_goal', 60)
            ->call('selectTheme', 'sepia')
            ->assertSet('reader_theme', 'sepia')
            ->call('nextStep')
            ->assertSet('currentStep', 5)
            ->call('selectPresetAvatar', '🦅')
            ->set('favorite_book', 'Atom odatlar')
            ->set('bio', 'Har kuni 60 daqiqa mutolaa qilaman')
            ->call('finish')
            ->assertRedirect(route('home'));

        $user->refresh();
        $this->assertTrue($user->hasCompletedOnboarding());
        $this->assertEquals(50, $user->total_points);
        $this->assertEquals(25, $user->coin_balance);

        $profile = $user->profile;
        $this->assertNotNull($profile);
        $this->assertEquals('university', $profile->reading_place);
        $this->assertEquals('career', $profile->reading_goal);
        $this->assertEquals('Atom odatlar', $profile->favorite_book);
        $this->assertEquals('ru', $profile->privacy_settings['preferred_locale'] ?? null);
        $this->assertEquals(60, $profile->privacy_settings['daily_reading_minutes'] ?? null);
        $this->assertEquals('sepia', $profile->privacy_settings['reader_theme'] ?? null);
        $this->assertEquals('🦅', $profile->privacy_settings['avatar_emblem'] ?? null);
        $this->assertContains('psychology', $profile->privacy_settings['favorite_genres'] ?? []);
    }

    /** @test */
    public function user_can_skip_onboarding_and_access_home()
    {
        $user = User::create([
            'name' => 'Skip Reader',
            'username' => 'skip_reader',
            'email' => 'skip@kitobxon.uz',
            'password' => Hash::make('password123'),
            'role' => 'student',
        ]);

        $this->actingAs($user);

        Livewire::test(OnboardingWizard::class)
            ->call('skipOnboarding')
            ->assertRedirect(route('home'));

        $user->refresh();
        $this->assertTrue($user->hasCompletedOnboarding());

        $this->actingAs($user)
            ->get('/')
            ->assertStatus(200);
    }
}
