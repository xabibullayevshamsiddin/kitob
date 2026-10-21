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
}
