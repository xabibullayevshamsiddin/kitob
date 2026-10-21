<?php

namespace Tests\Feature\Telegram;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use App\Services\Telegram\TelegramAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TelegramAuthTest extends TestCase
{
    use RefreshDatabase;

    protected string $botToken = '123456789:ABCdefGHIjklMNOpqrsTUVwxyz';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('telegram.bot_token', $this->botToken);
        Config::set('telegram.bot_username', 'ziyonur_test_bot');
    }

    /**
     * Test uchun haqiqiy Telegram HMAC-SHA256 imzoli ma'lumotlar to'plamini hosil qilish.
     */
    protected function generateWidgetPayload(array $attributes = []): array
    {
        $data = array_merge([
            'id'         => 987654321,
            'first_name' => 'Ali',
            'last_name'  => 'Valiyev',
            'username'   => 'ali_valiyev',
            'photo_url'  => 'https://t.me/i/userpic/320/avatar.jpg',
            'auth_date'  => time(),
        ], $attributes);

        $checkArr = [];
        foreach ($data as $k => $v) {
            if ($v !== null && $v !== '') {
                $checkArr[] = $k . '=' . $v;
            }
        }
        sort($checkArr);
        $checkString = implode("\n", $checkArr);

        $secretKey = hash('sha256', $this->botToken, true);
        $data['hash'] = hash_hmac('sha256', $checkString, $secretKey);

        return $data;
    }

    public function test_telegram_auth_service_verifies_valid_widget_signature(): void
    {
        $service = new TelegramAuthService();
        $payload = $this->generateWidgetPayload();

        $this->assertTrue($service->verifyWidgetData($payload));
    }

    public function test_telegram_auth_service_rejects_tampered_signature(): void
    {
        $service = new TelegramAuthService();
        $payload = $this->generateWidgetPayload(['first_name' => 'Boshqa Ism']);

        // Imzoni buzamiz
        $payload['hash'] = 'invalid_hash_string_12345';

        $this->assertFalse($service->verifyWidgetData($payload));
    }

    public function test_telegram_auth_service_rejects_expired_auth_date(): void
    {
        $service = new TelegramAuthService();
        // 25 soat oldingi auth_date (86400 soniyadan ortiq)
        $payload = $this->generateWidgetPayload(['auth_date' => time() - 90000]);

        $this->assertFalse($service->verifyWidgetData($payload));
    }

    public function test_telegram_callback_logs_in_existing_user(): void
    {
        $user = User::factory()->create([
            'telegram_id' => 987654321,
            'telegram_username' => 'ali_valiyev',
        ]);

        $payload = $this->generateWidgetPayload(['id' => 987654321]);

        $response = $this->get(route('auth.telegram.callback', $payload));

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_telegram_callback_creates_and_logs_in_new_user(): void
    {
        $payload = $this->generateWidgetPayload([
            'id'         => 888999111,
            'first_name' => 'Sardor',
            'last_name'  => 'Rahimov',
            'username'   => 'sardor_r',
        ]);

        $response = $this->get(route('auth.telegram.callback', $payload));

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $user = User::where('telegram_id', 888999111)->first();
        $this->assertNotNull($user);
        $this->assertEquals('Sardor Rahimov', $user->name);
        $this->assertEquals('sardor_r', $user->username);
        $this->assertNull($user->email);
        $this->assertNull($user->password);
        $this->assertEquals((int) setting('welcome_bonus_coins', 50), $user->coin_balance);
    }

    public function test_telegram_callback_rejects_banned_user(): void
    {
        User::factory()->create([
            'telegram_id' => 777666555,
            'is_banned'   => true,
            'ban_reason'  => 'Qoidalarni buzganligi sababli',
        ]);

        $payload = $this->generateWidgetPayload(['id' => 777666555]);

        $response = $this->get(route('auth.telegram.callback', $payload));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_authenticated_user_can_link_telegram_account(): void
    {
        $user = User::factory()->create([
            'telegram_id' => null,
        ]);

        $this->actingAs($user);

        $payload = $this->generateWidgetPayload([
            'id'       => 555444333,
            'username' => 'new_telegram_user',
        ]);

        $response = $this->get(route('auth.telegram.callback', $payload));

        $response->assertRedirect(route('settings'));

        $user->refresh();
        $this->assertEquals(555444333, $user->telegram_id);
        $this->assertEquals('new_telegram_user', $user->telegram_username);
    }

    public function test_user_with_password_can_unlink_telegram_account(): void
    {
        $user = User::factory()->create([
            'password'    => Hash::make('secret123'),
            'telegram_id' => 1234567,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('auth.telegram.unlink'));

        $response->assertRedirect(route('settings'));

        $user->refresh();
        $this->assertNull($user->telegram_id);
    }

    public function test_user_without_password_cannot_unlink_telegram_account(): void
    {
        $user = User::factory()->create([
            'password'    => null,
            'telegram_id' => 1234567,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('auth.telegram.unlink'));

        $response->assertRedirect(route('settings'));
        $response->assertSessionHasErrors(['telegram']);

        $user->refresh();
        $this->assertEquals(1234567, $user->telegram_id); // Uzilmasligi shart
    }

    public function test_user_can_log_in_using_phone_number_in_standard_login_form(): void
    {
        $user = User::factory()->create([
            'phone'    => '+998901234567',
            'password' => Hash::make('my-password-123'),
        ]);

        // Standart E.164 formati bilan kirish
        $response = $this->post(route('login'), [
            'email'    => '+998901234567',
            'password' => 'my-password-123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);

        Auth::logout();

        // Bo'shliqli mahalliy format bilan kirish (90 123 45 67)
        $response2 = $this->post(route('login'), [
            'email'    => '90 123 45 67',
            'password' => 'my-password-123',
        ]);

        $response2->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_register_with_phone_number(): void
    {
        $action = new CreateNewUser();

        $user = $action->create([
            'name'                  => 'Jasur Olimov',
            'username'              => 'jasur_olimov',
            'phone'                 => '+998 90 987-65-43',
            'password'              => 'password12345',
            'password_confirmation' => 'password12345',
        ]);

        $this->assertNotNull($user);
        $this->assertEquals('+998909876543', $user->phone);
        $this->assertNull($user->email);
        $this->assertTrue($user->hasPassword());
    }

    public function test_registration_with_duplicate_phone_number_fails(): void
    {
        User::factory()->create([
            'phone' => '+998901112233',
        ]);

        $action = new CreateNewUser();

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $action->create([
            'name'                  => 'Ikkinchi Foydalanuvchi',
            'username'              => 'ikkinchi_user',
            'phone'                 => '+998901112233',
            'password'              => 'password12345',
            'password_confirmation' => 'password12345',
        ]);
    }

    public function test_forgot_password_screen_renders_successfully(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
        $response->assertSee('Parolni tiklash');
        $response->assertSee('Telegram orqali parolsiz kirish');
    }

    public function test_password_reset_request_sends_telegram_message_for_telegram_user(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://api.telegram.org/bot*' => \Illuminate\Support\Facades\Http::response([
                'ok' => true,
                'result' => ['message_id' => 1234],
            ], 200),
        ]);

        $user = User::factory()->create([
            'name'        => 'Azizbek',
            'phone'       => '+998905554433',
            'telegram_id' => 77712345,
            'email'       => null,
        ]);

        $response = $this->post(route('password.email'), [
            'email' => '+998 90 555-44-33',
        ]);

        $response->assertSessionHas('status');

        \Illuminate\Support\Facades\Http::assertSent(function ($request) {
            return $request['chat_id'] === 77712345
                && str_contains($request['text'], 'Parolni yangilash so\'rovi');
        });
    }

    public function test_password_reset_fails_for_nonexistent_phone(): void
    {
        $response = $this->post(route('password.email'), [
            'email' => '+998909998877',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_redirect_route_redirects_to_waiting_screen(): void
    {
        $response = $this->get(route('auth.telegram.redirect'));
        $response->assertStatus(302);
        $this->assertStringContainsString('/auth/telegram/waiting/', $response->headers->get('Location'));
    }

    public function test_waiting_screen_renders_successfully(): void
    {
        $token = 'test_token_waiting_123';
        \Illuminate\Support\Facades\Cache::put("tg_auth_login_{$token}", ['created_at' => now()], now()->addMinutes(15));

        $response = $this->get(route('auth.telegram.waiting', ['token' => $token]));
        $response->assertStatus(200);
        $response->assertSee('Telegram orqali kirish');
        $response->assertSee('Bot javobi kutilmoqda');
    }

    public function test_check_endpoint_returns_confirmed_and_logs_in(): void
    {
        $token = 'test_token_confirm_456';
        $user = User::factory()->create(['name' => 'Tasdiqlangan User']);

        \Illuminate\Support\Facades\Cache::put("tg_auth_login_{$token}", ['created_at' => now()], now()->addMinutes(15));
        \Illuminate\Support\Facades\Cache::put("tg_auth_confirmed_{$token}", $user->id, now()->addMinutes(10));

        $response = $this->getJson(route('auth.telegram.check', ['token' => $token]));
        $response->assertStatus(200);
        $response->assertJson([
            'confirmed' => true,
        ]);

        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_complete_endpoint_logs_in_user_and_redirects_to_home(): void
    {
        $token = 'test_token_direct_789';
        $user = User::factory()->create(['name' => 'Direct User']);

        \Illuminate\Support\Facades\Cache::put("tg_auth_confirmed_{$token}", $user->id, now()->addMinutes(10));

        $response = $this->get(route('auth.telegram.complete', ['token' => $token]));
        $response->assertStatus(302);
        $response->assertRedirect(route('home'));

        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }
}
