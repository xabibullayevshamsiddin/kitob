<?php

namespace Tests\Feature\Telegram;

use App\Models\TelegramUpdate;
use App\Models\User;
use App\Services\Telegram\Exceptions\TelegramApiException;
use App\Services\Telegram\Exceptions\TelegramBlockedException;
use App\Services\Telegram\Exceptions\TelegramInvalidTokenException;
use App\Services\Telegram\Exceptions\TelegramRateLimitException;
use App\Services\Telegram\TelegramClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramCoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('telegram.bot_token', '123456789:ABCdefGHIjklMNOpqrsTUVwxyz');
        Config::set('telegram.webhook_secret', 'test-webhook-secret-token-12345');
        Config::set('telegram.api_url', 'https://api.telegram.org');
    }

    public function test_telegram_client_throws_exception_if_token_empty(): void
    {
        Config::set('telegram.bot_token', '');

        $this->expectException(TelegramInvalidTokenException::class);
        new TelegramClient();
    }

    public function test_telegram_client_sends_message_successfully(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response([
                'ok' => true,
                'result' => [
                    'message_id' => 999,
                    'chat' => ['id' => 12345],
                    'text' => 'Salom dunyo',
                ],
            ], 200),
        ]);

        $client = new TelegramClient();
        $response = $client->sendMessage(12345, 'Salom dunyo');

        $this->assertTrue($response['ok']);
        $this->assertEquals(999, $response['result']['message_id']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.telegram.org/bot123456789:ABCdefGHIjklMNOpqrsTUVwxyz/sendMessage'
                && $request['chat_id'] === 12345
                && $request['text'] === 'Salom dunyo'
                && $request['parse_mode'] === 'HTML';
        });
    }

    public function test_telegram_client_handles_user_blocked_403_and_flags_database(): void
    {
        $user = User::factory()->create([
            'telegram_id' => 777888999,
            'telegram_blocked_at' => null,
        ]);

        Http::fake([
            'https://api.telegram.org/bot*' => Http::response([
                'ok' => false,
                'error_code' => 403,
                'description' => 'Forbidden: bot was blocked by the user',
            ], 403),
        ]);

        $client = new TelegramClient();

        try {
            $client->sendMessage(777888999, 'Test xabar');
            $this->fail('TelegramBlockedException kutilgan edi');
        } catch (TelegramBlockedException $e) {
            $this->assertEquals(777888999, $e->getChatId());
        }

        $user->refresh();
        $this->assertNotNull($user->telegram_blocked_at);
        $this->assertTrue($user->isTelegramBlocked());
    }

    public function test_telegram_client_handles_rate_limit_429(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response([
                'ok' => false,
                'error_code' => 429,
                'description' => 'Too Many Requests: retry after 8',
                'parameters' => [
                    'retry_after' => 8,
                ],
            ], 429),
        ]);

        $client = new TelegramClient();

        try {
            $client->sendMessage(12345, 'Test xabar');
            $this->fail('TelegramRateLimitException kutilgan edi');
        } catch (TelegramRateLimitException $e) {
            $this->assertEquals(8, $e->getRetryAfter());
        }
    }

    public function test_telegram_client_masks_token_in_logs(): void
    {
        $client = new TelegramClient();
        $masked = $client->maskToken('https://api.telegram.org/bot123456789:ABCdefGHIjklMNOpqrsTUVwxyz/sendMessage');

        $this->assertStringNotContainsString('123456789:ABCdefGHIjklMNOpqrsTUVwxyz', $masked);
        $this->assertStringContainsString('***MASKED_TOKEN***', $masked);
    }

    public function test_telegram_client_stars_payment_helpers(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*/createInvoiceLink' => Http::response([
                'ok' => true,
                'result' => 'https://t.me/$invoice_test_link',
            ], 200),
            'https://api.telegram.org/bot*/answerPreCheckoutQuery' => Http::response([
                'ok' => true,
                'result' => true,
            ], 200),
            'https://api.telegram.org/bot*/refundStarPayment' => Http::response([
                'ok' => true,
                'result' => true,
            ], 200),
        ]);

        $client = new TelegramClient();

        $link = $client->createInvoiceLink(
            '100 tanga paketi',
            'Platformada sarflash uchun 100 tanga',
            'order_123',
            'XTR',
            [['label' => '100 tanga', 'amount' => 50]]
        );
        $this->assertEquals('https://t.me/$invoice_test_link', $link);

        $preCheckout = $client->answerPreCheckoutQuery('pre_checkout_123', true);
        $this->assertTrue($preCheckout['ok']);

        $refund = $client->refundStarPayment(12345, 'charge_123');
        $this->assertTrue($refund['ok']);
    }

    public function test_webhook_rejects_requests_with_invalid_secret_token(): void
    {
        $response = $this->postJson('/telegram/webhook', [
            'update_id' => 1001,
            'message' => ['chat' => ['id' => 123]],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'not-matching-secret',
        ]);

        $response->assertStatus(403);
    }

    public function test_webhook_accepts_valid_secret_and_records_to_deduplication(): void
    {
        $response = $this->postJson('/telegram/webhook', [
            'update_id' => 2001,
            'message' => [
                'message_id' => 1,
                'chat' => ['id' => 555666],
                'text' => 'Salom bot',
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'test-webhook-secret-token-12345',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);

        $this->assertDatabaseHas('telegram_updates', [
            'update_id' => 2001,
            'chat_id' => 555666,
            'update_type' => 'message',
        ]);
    }

    public function test_webhook_deduplicates_repeated_updates(): void
    {
        // Birinchi so'rov
        $first = $this->postJson('/telegram/webhook', [
            'update_id' => 3001,
            'message' => [
                'chat' => ['id' => 111],
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'test-webhook-secret-token-12345',
        ]);
        $first->assertStatus(200);
        $this->assertNull($first->json('duplicate'));

        // Ikkinchi (takroriy) so'rov
        $second = $this->postJson('/telegram/webhook', [
            'update_id' => 3001,
            'message' => [
                'chat' => ['id' => 111],
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'test-webhook-secret-token-12345',
        ]);
        $second->assertStatus(200);
        $this->assertTrue($second->json('duplicate'));

        // Jadvalda faqat 1 dona yozuv qolishi shart
        $this->assertEquals(1, TelegramUpdate::where('update_id', 3001)->count());
    }

    public function test_artisan_set_webhook_command(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*/setWebhook' => Http::response([
                'ok' => true,
                'result' => true,
                'description' => 'Webhook was set',
            ], 200),
        ]);

        $this->artisan('telegram:set-webhook', [
            '--url' => 'https://ziyonur.uz/telegram/webhook',
        ])
            ->expectsOutputToContain('Muvaffaqiyatli! Webhook o\'rnatildi.')
            ->assertExitCode(0);
    }

    public function test_artisan_webhook_info_command(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*/getWebhookInfo' => Http::response([
                'ok' => true,
                'result' => [
                    'url' => 'https://ziyonur.uz/telegram/webhook',
                    'has_custom_certificate' => false,
                    'pending_update_count' => 0,
                    'max_connections' => 40,
                    'ip_address' => '1.2.3.4',
                ],
            ], 200),
        ]);

        $this->artisan('telegram:webhook-info')
            ->expectsOutputToContain('https://ziyonur.uz/telegram/webhook')
            ->assertExitCode(0);
    }

    public function test_email_registration_can_be_disabled_via_config(): void
    {
        Config::set('telegram.auth_email_registration_enabled', false);

        $action = new \App\Actions\Fortify\CreateNewUser();

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $action->create([
            'name' => 'Test User',
            'username' => 'testuser123',
            'email' => 'test@example.com',
            'password' => 'secret123456',
            'password_confirmation' => 'secret123456',
        ]);
    }

    public function test_user_without_password_can_set_password_without_current_password(): void
    {
        $user = User::factory()->create([
            'password' => null,
            'telegram_id' => 999111,
        ]);

        $this->assertFalse($user->hasPassword());

        $action = new \App\Actions\Fortify\UpdateUserPassword();
        $action->update($user, [
            'password' => 'newSecretPassword123',
            'password_confirmation' => 'newSecretPassword123',
        ]);

        $user->refresh();
        $this->assertTrue($user->hasPassword());
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newSecretPassword123', $user->password));
    }
}
