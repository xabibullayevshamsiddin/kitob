<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAndOnboardingTest extends TestCase
{
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
    public function user_can_authenticate_using_the_login_screen()
    {
        $user = User::updateOrCreate(
            ['email' => 'testuser@kitobxon.uz'],
            [
                'name' => 'Test User',
                'username' => 'test_auth_user',
                'password' => Hash::make('password123'),
                'role' => 'reader',
            ]
        );

        $response = $this->post('/login', [
            'email' => 'testuser@kitobxon.uz',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function unauthenticated_users_are_redirected_to_login()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }
}
