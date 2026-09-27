<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRememberTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_remember_creates_long_lived_cookie(): void
    {
        $user = User::factory()->create([
            'username' => 'remember_' . uniqid(),
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
            'remember' => 'on',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);

        // Remember cookie yaratildimi
        $cookies = $response->headers->getCookies();
        $rememberCookie = null;
        foreach ($cookies as $cookie) {
            if (str_contains($cookie->getName(), 'remember_web')) {
                $rememberCookie = $cookie;
            }
        }

        $this->assertNotNull($rememberCookie, 'remember_web cookie yaratilmadi');
        // 400 kun = ~34,560,000 soniya (Laravel zamonaviy standarti)
        $this->assertGreaterThan(31536000, $rememberCookie->getExpiresTime() - time(), 'Remember cookie umri kamida 1 yil bolishi kerak');

        $user->delete();
    }

    public function test_login_form_has_remember_checked_by_default(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('name="remember" checked', false);
    }
}
