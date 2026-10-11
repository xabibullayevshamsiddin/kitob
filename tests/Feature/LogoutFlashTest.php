<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutFlashTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_flashes_confirmation_after_invalidating_the_old_session(): void
    {
        $user = User::factory()->create([
            'username' => 'logout_' . uniqid(),
        ]);

        $response = $this->withSession(['before_logout' => 'discard-me'])
            ->actingAs($user)
            ->post('/logout');

        $response->assertRedirect('/');
        $response->assertSessionHas('info', 'Tizimdan muvaffaqiyatli chiqdingiz.');
        $response->assertSessionMissing('before_logout');
        $this->assertGuest();
    }
}
