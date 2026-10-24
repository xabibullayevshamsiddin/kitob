<?php

namespace Tests\Feature\Telegram;

use App\Models\DailyQuote;
use App\Models\User;
use App\Services\Telegram\TelegramBotService;
use App\Services\Telegram\TelegramClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramBotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('telegram.bot_token', '123456789:ABCdefGHIjklMNOpqrsTUVwxyz');
        Config::set('telegram.bot_username', 'ziyonur_test_bot');
    }

    public function test_start_command_sends_welcome_message(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]], 200),
        ]);

        $service = new TelegramBotService();
        $service->handleUpdate([
            'update_id' => 5001,
            'message' => [
                'message_id' => 10,
                'chat' => ['id' => 12345],
                'from' => ['id' => 12345, 'first_name' => 'Dilshod'],
                'text' => '/start',
            ],
        ]);

        Http::assertSent(function ($request) {
            return $request['chat_id'] === 12345
                && str_contains($request['text'], 'Dilshod')
                && str_contains($request['text'], 'Kitobxon');
        });
    }

    public function test_profile_command_returns_user_data_if_linked(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 2]], 200),
        ]);

        $user = User::factory()->create([
            'name'         => 'Nodir',
            'username'     => 'nodir_reader',
            'telegram_id'  => 888123,
            'coin_balance' => 120,
            'total_points' => 350,
        ]);

        $service = new TelegramBotService();
        $service->handleUpdate([
            'update_id' => 5002,
            'message' => [
                'chat' => ['id' => 888123],
                'from' => ['id' => 888123, 'first_name' => 'Nodir'],
                'text' => '/profil',
            ],
        ]);

        Http::assertSent(function ($request) {
            return $request['chat_id'] === 888123
                && str_contains($request['text'], 'Nodir')
                && str_contains($request['text'], '120 🪙');
        });
    }

    public function test_hikmat_command_sends_quote(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 3]], 200),
        ]);

        $book = \App\Models\Book::create([
            'title'       => 'Test Kitob',
            'slug'        => 'test-kitob-' . time(),
            'author'      => 'Muallif',
            'description' => 'Tavsif',
            'genre'       => 'Badiiy',
            'week_number' => 999,
        ]);

        DailyQuote::create([
            'book_id'    => $book->id,
            'quote_text' => 'Kitob — aql chirog\'i.',
            'author'     => 'Xalq maqoli',
            'is_active'  => true,
            'send_date'  => now()->toDateString(),
        ]);

        $service = new TelegramBotService();
        $service->handleUpdate([
            'update_id' => 5003,
            'message' => [
                'chat' => ['id' => 777],
                'from' => ['id' => 777, 'first_name' => 'Ali'],
                'text' => '/hikmat',
            ],
        ]);

        Http::assertSent(function ($request) {
            return $request['chat_id'] === 777
                && str_contains($request['text'], 'Kun hikmati');
        });
    }

    public function test_contact_sharing_verifies_phone_and_links_account(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 4]], 200),
        ]);

        $user = User::factory()->create([
            'telegram_id'       => 999333,
            'phone'             => null,
            'phone_verified_at' => null,
        ]);

        $service = new TelegramBotService();
        $service->handleUpdate([
            'update_id' => 5004,
            'message' => [
                'chat' => ['id' => 999333],
                'from' => ['id' => 999333, 'first_name' => 'Jasur'],
                'contact' => [
                    'user_id'      => 999333, // O'zining kontakti
                    'phone_number' => '+998901234567',
                ],
            ],
        ]);

        $user->refresh();
        $this->assertEquals('+998901234567', $user->phone);
        $this->assertNotNull($user->phone_verified_at);
        $this->assertTrue($user->hasVerifiedPhone());

        Http::assertSent(function ($request) {
            return $request['chat_id'] === 999333
                && str_contains($request['text'], 'muvaffaqiyatli tasdiqlandi');
        });
    }

    public function test_contact_sharing_rejects_other_people_contacts(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 5]], 200),
        ]);

        $service = new TelegramBotService();
        $service->handleUpdate([
            'update_id' => 5005,
            'message' => [
                'chat' => ['id' => 999333],
                'from' => ['id' => 999333, 'first_name' => 'Jasur'],
                'contact' => [
                    'user_id'      => 111222, // Begona odamning kontakti!
                    'phone_number' => '+998909998877',
                ],
            ],
        ]);

        Http::assertSent(function ($request) {
            return $request['chat_id'] === 999333
                && str_contains($request['text'], 'faqat o\'zingizning');
        });
    }

    public function test_reply_keyboard_menu_button_text_triggers_profile_command(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 6]], 200),
        ]);

        $user = User::factory()->create([
            'name'         => 'Sardor',
            'username'     => 'sardor_reader',
            'telegram_id'  => 555123,
            'coin_balance' => 250,
            'total_points' => 1500,
        ]);

        $service = new TelegramBotService();
        // Foydalanuvchi klaviaturadagi "👤 Profilim" tugmasini bosganda
        $service->handleUpdate([
            'update_id' => 5006,
            'message' => [
                'chat' => ['id' => 555123],
                'from' => ['id' => 555123, 'first_name' => 'Sardor'],
                'text' => '👤 Profilim',
            ],
        ]);

        Http::assertSent(function ($request) {
            return $request['chat_id'] === 555123
                && str_contains($request['text'], 'Sardor')
                && str_contains($request['text'], '250 🪙')
                && str_contains($request['text'], '1,500 ⭐️');
        });
    }

    public function test_menu_command_and_reply_buttons_routing(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 7]], 200),
        ]);

        $service = new TelegramBotService();

        // 1. /menu buyrug'i
        $service->handleUpdate([
            'update_id' => 5007,
            'message' => [
                'chat' => ['id' => 112233],
                'from' => ['id' => 112233, 'first_name' => 'Anvar'],
                'text' => '/menu',
            ],
        ]);

        Http::assertSent(function ($request) {
            return $request['chat_id'] === 112233
                && str_contains($request['text'], 'Asosiy buyruqlar menyusi')
                && isset($request['reply_markup']['keyboard']);
        });

        // 2. "🏆 Reyting" tugmasi
        $service->handleUpdate([
            'update_id' => 5008,
            'message' => [
                'chat' => ['id' => 112233],
                'from' => ['id' => 112233, 'first_name' => 'Anvar'],
                'text' => '🏆 Reyting',
            ],
        ]);

        Http::assertSent(function ($request) {
            return $request['chat_id'] === 112233
                && str_contains($request['text'], 'kitobxonlar reytingi');
        });

        // 3. "📚 Kitoblar" tugmasi
        $service->handleUpdate([
            'update_id' => 5009,
            'message' => [
                'chat' => ['id' => 112233],
                'from' => ['id' => 112233, 'first_name' => 'Anvar'],
                'text' => '📚 Kitoblar',
            ],
        ]);

        Http::assertSent(function ($request) {
            return $request['chat_id'] === 112233
                && str_contains($request['text'], 'kitoblar');
        });
    }

    public function test_start_reg_for_already_verified_user_shows_menu_instead_of_contact(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 8]], 200),
        ]);

        $existingUser = User::factory()->create([
            'phone'             => '+998901234567',
            'phone_verified_at' => now(),
            'telegram_id'       => 999888,
        ]);

        $token = 'reg_token_already_verified';
        \Illuminate\Support\Facades\Cache::put("pending_reg_{$token}", [
            'name'     => 'Existing Person',
            'username' => 'existing_person',
            'phone'    => '+998901234567',
            'password' => 'secret',
        ], now()->addMinutes(15));

        $service = new TelegramBotService();
        $service->handleUpdate([
            'update_id' => 5010,
            'message' => [
                'chat' => ['id' => 999888],
                'from' => ['id' => 999888, 'first_name' => 'Existing Person'],
                'text' => "/start reg_{$token}",
            ],
        ]);

        Http::assertSent(function ($request) {
            return $request['chat_id'] === 999888
                && str_contains($request['text'], 'allaqachon')
                && isset($request['reply_markup']['keyboard']);
        });
    }

    public function test_main_menu_keyboard_has_close_button_and_not_persistent(): void
    {
        $service = new TelegramBotService();
        $keyboard = $service->getMainMenuKeyboard();

        $this->assertArrayHasKey('keyboard', $keyboard);
        $this->assertTrue($keyboard['resize_keyboard']);
        $this->assertArrayNotHasKey('is_persistent', $keyboard);

        $flatButtons = [];
        foreach ($keyboard['keyboard'] as $row) {
            foreach ($row as $btn) {
                $flatButtons[] = $btn['text'];
            }
        }

        $this->assertContains('❌ Menyuni yopish', $flatButtons);
        $this->assertContains('🔥 Streak holati', $flatButtons);
        $this->assertContains('🪙 Tanga balansi', $flatButtons);
    }

    public function test_close_menu_removes_keyboard(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 9]], 200),
        ]);

        $service = new TelegramBotService();
        $service->handleUpdate([
            'update_id' => 5011,
            'message' => [
                'chat' => ['id' => 555444],
                'from' => ['id' => 555444, 'first_name' => 'Alisher'],
                'text' => '❌ Menyuni yopish',
            ],
        ]);

        Http::assertSent(function ($request) {
            return $request['chat_id'] === 555444
                && str_contains($request['text'], 'Menyu yopildi')
                && isset($request['reply_markup']['remove_keyboard'])
                && $request['reply_markup']['remove_keyboard'] === true;
        });
    }

    public function test_slash_close_command_removes_keyboard(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 10]], 200),
        ]);

        $service = new TelegramBotService();
        $service->handleUpdate([
            'update_id' => 5012,
            'message' => [
                'chat' => ['id' => 555444],
                'from' => ['id' => 555444, 'first_name' => 'Alisher'],
                'text' => '/close',
            ],
        ]);

        Http::assertSent(function ($request) {
            return $request['chat_id'] === 555444
                && str_contains($request['text'], 'Menyu yopildi')
                && isset($request['reply_markup']['remove_keyboard'])
                && $request['reply_markup']['remove_keyboard'] === true;
        });
    }

    public function test_start_reg_followed_by_plain_start_preserves_contact_request_keyboard(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 11]], 200),
        ]);

        $token = 'reg_token_new_user_123';
        \Illuminate\Support\Facades\Cache::put("pending_reg_{$token}", [
            'name'     => 'Ozoda',
            'username' => 'ozoda_reader',
            'phone'    => '+998935931523',
            'password' => 'secret123',
        ], now()->addMinutes(15));

        $service = new TelegramBotService();

        // 1. Deep-link /start reg_{token}
        $service->handleUpdate([
            'update_id' => 5020,
            'message' => [
                'chat' => ['id' => 777888],
                'from' => ['id' => 777888, 'first_name' => 'Озода'],
                'text' => "/start reg_{$token}",
            ],
        ]);

        // 2. Ketidan darhol oddiy /start kelsa (Telegram auto-launch + Start bosilganda)
        $service->handleUpdate([
            'update_id' => 5021,
            'message' => [
                'chat' => ['id' => 777888],
                'from' => ['id' => 777888, 'first_name' => 'Озода'],
                'text' => '/start',
            ],
        ]);

        // Faqat bitta xabar ketgan bo'lishi va unda request_contact tugmasi bo'lishi shart!
        Http::assertSentCount(1);
        Http::assertSent(function ($request) {
            return $request['chat_id'] === 777888
                && str_contains($request['text'], 'Raqamni ulashish (Tasdiqlash)')
                && ($request['reply_markup']['keyboard'][0][0]['request_contact'] ?? false) === true;
        });
    }
}
