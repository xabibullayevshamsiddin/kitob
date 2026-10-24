<?php

namespace Tests\Feature\Telegram;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use App\Services\Telegram\TelegramAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
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
        // 1 soatdan ortiq (3600 soniyadan eski) auth_date rad etilishi kerak
        $payload = $this->generateWidgetPayload(['auth_date' => time() - 3700]);

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

    public function test_telegram_callback_creates_and_logs_in_new_user_without_giving_bonus_until_phone_verified(): void
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
        $this->assertNull($user->phone);
        // Noyob telefon tasdiqlanmaguncha 50 tanga bonus berilmaydi
        $this->assertEquals(0, $user->coin_balance);

        // Endi foydalanuvchi botda o'z noyob telefon raqamini tasdiqlaganda 50 tanga bonus beriladi
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]], 200),
        ]);
        $botService = new \App\Services\Telegram\TelegramBotService();
        $botService->handleUpdate([
            'update_id' => 9001,
            'message'   => [
                'chat'    => ['id' => 888999111],
                'from'    => ['id' => 888999111, 'first_name' => 'Sardor'],
                'contact' => [
                    'user_id'      => 888999111,
                    'phone_number' => '+998901239988',
                ],
            ],
        ]);

        $user->refresh();
        $this->assertEquals('+998901239988', $user->phone);
        $this->assertNotNull($user->phone_verified_at);
        $this->assertEquals((int) setting('welcome_bonus_coins', 50), $user->coin_balance);
    }

    public function test_oidc_redirect_and_callback_with_pkce_state_and_nonce(): void
    {
        Config::set('telegram.login_client_id', 'tg_oidc_client_123');
        Config::set('telegram.login_client_secret', 'tg_oidc_secret_456');

        // 1. Redirect OIDC parametrlarini (state, nonce, code_challenge) yaratishi kerak
        $redirectRes = $this->get(route('auth.telegram.redirect'));
        $redirectRes->assertStatus(302);
        $location = (string) $redirectRes->headers->get('Location');
        $this->assertStringContainsString('https://oauth.telegram.org/auth', $location);
        $this->assertStringContainsString('code_challenge_method=S256', $location);

        $state = session('oauth_telegram_state');
        $nonce = session('oauth_telegram_nonce');
        $this->assertNotEmpty($state);
        $this->assertNotEmpty($nonce);

        // 2. Noto'g'ri state bilan callback rad etilishi kerak
        $badCallback = $this->get(route('auth.telegram.callback', [
            'code'  => 'auth_code_xyz',
            'state' => 'wrong_state_value',
        ]));
        $badCallback->assertRedirect(route('login'));
        $this->assertGuest();

        // 3. To'g'ri state, PKCE va ID token bilan kirish muvaffaqiyatli bo'lishi kerak
        $this->withSession([
            'oauth_telegram_state'         => 'valid_state_abc',
            'oauth_telegram_nonce'         => 'valid_nonce_xyz',
            'oauth_telegram_code_verifier' => 'verifier_1234567890',
        ]);

        $headerB64 = rtrim(strtr(base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])), '+/', '-_'), '=');
        $payloadB64 = rtrim(strtr(base64_encode(json_encode([
            'iss'                => 'https://oauth.telegram.org',
            'aud'                => 'tg_oidc_client_123',
            'sub'                => '777888999',
            'id'                 => 777888999,
            'name'               => 'Otabek Umarov',
            'preferred_username' => 'otabek_u',
            'phone_number'       => '+998907778899',
            'nonce'              => 'valid_nonce_xyz',
            'exp'                => time() + 3600,
        ])), '+/', '-_'), '=');
        $fakeIdToken = "{$headerB64}.{$payloadB64}.signature";

        Http::fake([
            'https://oauth.telegram.org/token' => Http::response([
                'access_token' => 'access_tok_1',
                'id_token'     => $fakeIdToken,
                'token_type'   => 'Bearer',
            ], 200),
        ]);

        $okCallback = $this->get(route('auth.telegram.callback', [
            'code'  => 'valid_auth_code_123',
            'state' => 'valid_state_abc',
        ]));

        $okCallback->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $oidcUser = User::where('telegram_id', 777888999)->first();
        $this->assertNotNull($oidcUser);
        $this->assertEquals('+998907778899', $oidcUser->phone);
        $this->assertEquals((int) setting('welcome_bonus_coins', 50), $oidcUser->coin_balance);
    }

    public function test_bot_deeplink_login_requires_confirmation_and_prevents_session_fixation(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]], 200),
        ]);

        $token = 'secure_token_test_123';
        $browserSecret = 'browser_bound_secret_xyz';

        Cache::put("tg_auth_login_{$token}", [
            'created_at'     => now()->toIso8601String(),
            'ip'             => '127.0.0.1',
            'device'         => 'Chrome (Windows)',
            'code'           => '4829',
            'browser_secret' => $browserSecret,
        ], now()->addMinutes(15));

        $botService = new \App\Services\Telegram\TelegramBotService();

        // 1. /start auth_{token} bosilganda darhol tasdiqlanmasligi kerak — "Tasdiqlaysizmi?" so'rovi chiqadi
        $botService->handleUpdate([
            'update_id' => 9101,
            'message'   => [
                'chat' => ['id' => 444555666],
                'from' => ['id' => 444555666, 'first_name' => 'Rustam'],
                'text' => "/start auth_{$token}",
            ],
        ]);

        $this->assertNull(Cache::get("tg_auth_confirmed_{$token}"));

        // 2. Foydalanuvchi "✅ Ha, tasdiqlayman" inline tugmasini bosgandagina tasdiqlanadi
        $botService->handleUpdate([
            'update_id'      => 9102,
            'callback_query' => [
                'id'      => 'cb_1',
                'from'    => ['id' => 444555666, 'first_name' => 'Rustam'],
                'message' => ['chat' => ['id' => 444555666]],
                'data'    => "auth_confirm:{$token}",
            ],
        ]);

        $this->assertNotNull(Cache::get("tg_auth_confirmed_{$token}"));

        // 3. Begona brauzer (browser_secret mos kelmasa) check endpoint orqali kira olmaydi (403)
        $hijackAttempt = $this->withSession(['tg_auth_secret_' . $token => 'wrong_attacker_secret'])
            ->getJson(route('auth.telegram.check', ['token' => $token]));
        $hijackAttempt->assertStatus(403);
        $this->assertGuest();

        // 4. Haqiqiy brauzer (browser_secret mos kelsa) muvaffaqiyatli kiradi
        $legitAttempt = $this->withSession(['tg_auth_secret_' . $token => $browserSecret])
            ->getJson(route('auth.telegram.check', ['token' => $token]));
        $legitAttempt->assertStatus(200)->assertJson(['confirmed' => true]);
        $this->assertAuthenticated();
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
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]], 200),
        ]);

        $user = User::factory()->create([
            'phone'       => '+998901234567',
            'telegram_id' => 123456789,
            'password'    => Hash::make('my-password-123'),
        ]);

        // Standart E.164 formati bilan kirish (2FA kod sahifasiga yo'naltiriladi)
        $response = $this->post(route('login'), [
            'email'    => '+998901234567',
            'password' => 'my-password-123',
        ]);

        $response->assertStatus(302);
        $this->assertStringContainsString('/login/verify-code/', (string) $response->headers->get('Location'));

        // Telegram orqali yuborilgan 6 xonali kod bilan tasdiqlash
        $location = (string) $response->headers->get('Location');
        $token = basename(parse_url($location, PHP_URL_PATH));
        $cached = Cache::get("login_2fa_{$token}");
        $this->assertNotNull($cached);

        $verifyRes = $this->post(route('login.verify-code', ['token' => $token]), [
            'code' => $cached['code'],
        ]);
        $verifyRes->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);

        Auth::logout();

        // Bo'shliqli mahalliy format bilan kirish (90 123 45 67)
        $response2 = $this->post(route('login'), [
            'email'    => '90 123 45 67',
            'password' => 'my-password-123',
        ]);

        $response2->assertStatus(302);
        $this->assertStringContainsString('/login/verify-code/', (string) $response2->headers->get('Location'));
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
        $response->assertSee('Tiklash havolasini olish');
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
