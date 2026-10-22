<?php

namespace Tests\Feature\Telegram;

use App\Models\User;
use App\Services\Telegram\TelegramBotService;
use App\Services\Telegram\TelegramClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramPhoneAuthVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('telegram.bot_token', '123456789:ABCdefGHIjklMNOpqrsTUVwxyz');
        Config::set('telegram.bot_username', 'ziyonur_test_bot');
    }

    public function test_registration_redirects_to_verification_screen_without_creating_user(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Shavkat Mirzayev',
            'username'              => 'shavkat_reader',
            'phone'                 => '+998 90 123 45 67',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(302);
        $this->assertStringContainsString('/register/verify/', $response->headers->get('Location'));

        // DB'da foydalanuvchi hali yaratilmagan (tasdiqlanmaguncha)
        $this->assertDatabaseMissing('users', [
            'username' => 'shavkat_reader',
        ]);
    }

    public function test_telegram_contact_sharing_verifies_phone_and_creates_user(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]], 200),
        ]);

        $token = 'test_reg_token_abc123';
        Cache::put("pending_reg_{$token}", [
            'name'       => 'Akmal Xoliqov',
            'username'   => 'akmal_books',
            'phone'      => '+998901112233',
            'email'      => null,
            'password'   => Hash::make('secret123'),
        ], now()->addMinutes(15));

        // 1. Foydalanuvchi botda /start reg_... yuboradi
        $botService = new TelegramBotService(new TelegramClient());
        $botService->handleUpdate([
            'update_id' => 9001,
            'message'   => [
                'chat' => ['id' => 777666],
                'from' => ['id' => 777666, 'first_name' => 'Akmal'],
                'text' => "/start reg_{$token}",
            ],
        ]);

        // 2. Foydalanuvchi kontaktini ulashadi (o'z raqami)
        $botService->handleUpdate([
            'update_id' => 9002,
            'message'   => [
                'chat'    => ['id' => 777666],
                'from'    => ['id' => 777666, 'first_name' => 'Akmal'],
                'contact' => [
                    'user_id'      => 777666,
                    'phone_number' => '+998901112233',
                    'first_name'   => 'Akmal',
                ],
            ],
        ]);

        // 3. DB'da tasdiqlangan foydalanuvchi yaratildi
        $this->assertDatabaseHas('users', [
            'username'    => 'akmal_books',
            'phone'       => '+998901112233',
            'telegram_id' => 777666,
        ]);

        $user = User::where('username', 'akmal_books')->first();
        $this->assertNotNull($user->phone_verified_at);

        // 4. Saytdagi polling tekshiradi va tizimga kiritadi
        $checkResponse = $this->getJson(route('register.check', ['token' => $token]));
        $checkResponse->assertStatus(200);
        $checkResponse->assertJson(['confirmed' => true]);

        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_telegram_contact_sharing_rejects_mismatched_phone(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true], 200),
        ]);

        $token = 'test_reg_token_diff_phone';
        Cache::put("pending_reg_{$token}", [
            'name'       => 'Bekzod',
            'username'   => 'bekzod_reader',
            'phone'      => '+998909990011',
            'password'   => Hash::make('secret123'),
        ], now()->addMinutes(15));

        $botService = new TelegramBotService(new TelegramClient());
        $botService->handleUpdate([
            'update_id' => 9003,
            'message'   => [
                'chat' => ['id' => 555444],
                'from' => ['id' => 555444, 'first_name' => 'Bekzod'],
                'text' => "/start reg_{$token}",
            ],
        ]);

        // Foydalanuvchi boshqa raqam ulashadi
        $botService->handleUpdate([
            'update_id' => 9004,
            'message'   => [
                'chat'    => ['id' => 555444],
                'from'    => ['id' => 555444, 'first_name' => 'Bekzod'],
                'contact' => [
                    'user_id'      => 555444,
                    'phone_number' => '+998901234567', // mos kelmaydigan raqam
                    'first_name'   => 'Bekzod',
                ],
            ],
        ]);

        // Foydalanuvchi yaratilmaydi
        $this->assertDatabaseMissing('users', [
            'username' => 'bekzod_reader',
        ]);
    }

    public function test_login_sends_6_digit_telegram_code_when_credentials_valid(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 55]], 200),
        ]);

        $user = User::factory()->create([
            'phone'             => '+998907778899',
            'phone_verified_at' => now(),
            'telegram_id'       => 123456789,
            'password'          => Hash::make('my-password-123'),
        ]);

        $response = $this->post('/login', [
            'email'    => '+998 90 777 88 99',
            'password' => 'my-password-123',
        ]);

        $response->assertStatus(302);
        $this->assertStringContainsString('/login/verify-code/', $response->headers->get('Location'));

        // To'g'ridan-to'g'ri tizimga kiritilmagan (2FA kodi kutilmoqda)
        $this->assertFalse(Auth::check());

        // Telegram bot orqali 6 xonali kod yuborilgan
        Http::assertSent(function ($request) {
            return $request['chat_id'] === 123456789
                && str_contains($request['text'], 'bir martalik 6 xonali tasdiqlash kodingiz');
        });
    }

    public function test_verify_code_logs_in_user_with_correct_code(): void
    {
        $user = User::factory()->create([
            'name'  => 'Jamshid',
            'phone' => '+998903332211',
        ]);

        $token = 'test_token_2fa_xyz';
        Cache::put("login_2fa_{$token}", [
            'user_id'    => $user->id,
            'code'       => '582491',
            'remember'   => false,
            'attempts'   => 0,
            'created_at' => now(),
        ], now()->addMinutes(5));

        $response = $this->post(route('login.verify-code.submit', ['token' => $token]), [
            'code' => '582491',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('home'));

        // Tizimga muvaffaqiyatli kiritildi
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_verify_code_rejects_incorrect_code(): void
    {
        $user = User::factory()->create(['phone' => '+998904445566']);

        $token = 'test_token_2fa_wrong';
        Cache::put("login_2fa_{$token}", [
            'user_id'    => $user->id,
            'code'       => '112233',
            'remember'   => false,
            'attempts'   => 0,
            'created_at' => now(),
        ], now()->addMinutes(5));

        $response = $this->post(route('login.verify-code.submit', ['token' => $token]), [
            'code' => '999999', // xato kod
        ]);

        $response->assertSessionHasErrors(['code']);
        $this->assertFalse(Auth::check());
    }
}
