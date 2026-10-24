<?php

namespace Tests\Feature\Telegram;

use App\Models\CoinTransaction;
use App\Models\TelegramStarPayment;
use App\Models\User;
use App\Services\Telegram\TelegramClient;
use App\Services\Telegram\TelegramPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('telegram.bot_token', '123456789:ABCdefGHIjklMNOpqrsTUVwxyz');
        Config::set('telegram.bot_username', 'ziyonur_test_bot');
        Config::set('telegram.payments_enabled', false); // default false
    }

    public function test_payments_disabled_by_default(): void
    {
        $service = new TelegramPaymentService();
        $this->assertFalse($service->isPaymentsEnabled());

        $user = User::factory()->create(['telegram_id' => 112233]);
        $result = $service->createCoinPackInvoice($user, 'pack_50');

        $this->assertFalse($result['success']);
        $this->assertEquals('payments_disabled', $result['error']);
    }

    public function test_create_coin_pack_invoice_succeeds_when_enabled(): void
    {
        Config::set('telegram.payments_enabled', true);

        Http::fake([
            'https://api.telegram.org/bot*' => Http::response([
                'ok'     => true,
                'result' => 'https://t.me/$invoice_test_123',
            ], 200),
        ]);

        $user = User::factory()->create([
            'telegram_id'  => 55443322,
            'coin_balance' => 10,
        ]);

        $service = new TelegramPaymentService(new TelegramClient());
        $result = $service->createCoinPackInvoice($user, 'pack_50');

        $this->assertTrue($result['success']);
        $this->assertEquals('https://t.me/$invoice_test_123', $result['invoice_link']);

        $this->assertDatabaseHas('telegram_star_payments', [
            'user_id'       => $user->id,
            'stars_amount'  => 25,
            'coins_awarded' => 50,
            'status'        => 'pending',
        ]);
    }

    public function test_pre_checkout_query_approved_for_valid_pending_payment(): void
    {
        Config::set('telegram.payments_enabled', true);

        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => true], 200),
        ]);

        $user = User::factory()->create(['telegram_id' => 778899]);
        $payment = TelegramStarPayment::create([
            'user_id'       => $user->id,
            'payload'       => 'test_payload_order_1',
            'title'         => '50 ta tanga',
            'stars_amount'  => 25,
            'coins_awarded' => 50,
            'status'        => 'pending',
        ]);

        $service = new TelegramPaymentService(new TelegramClient());
        $approved = $service->handlePreCheckoutQuery([
            'id'              => 'pre_query_999',
            'invoice_payload' => 'test_payload_order_1',
            'total_amount'    => 25,
        ]);

        $this->assertTrue($approved);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'answerPreCheckoutQuery')
                && $request['pre_checkout_query_id'] === 'pre_query_999'
                && $request['ok'] === true;
        });
    }

    public function test_pre_checkout_query_rejected_when_payments_disabled(): void
    {
        Config::set('telegram.payments_enabled', false);

        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true], 200),
        ]);

        $service = new TelegramPaymentService(new TelegramClient());
        $approved = $service->handlePreCheckoutQuery([
            'id'              => 'pre_query_888',
            'invoice_payload' => 'some_payload',
            'total_amount'    => 25,
        ]);

        $this->assertFalse($approved);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'answerPreCheckoutQuery')
                && $request['ok'] === false;
        });
    }

    public function test_successful_payment_awards_coins_and_creates_transaction(): void
    {
        Config::set('telegram.payments_enabled', true);

        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 101]], 200),
        ]);

        $user = User::factory()->create([
            'telegram_id'  => 998811,
            'coin_balance' => 20,
        ]);

        $payment = TelegramStarPayment::create([
            'user_id'       => $user->id,
            'payload'       => 'payload_succ_100',
            'title'         => '150 ta tanga',
            'stars_amount'  => 65,
            'coins_awarded' => 150,
            'status'        => 'pending',
        ]);

        $service = new TelegramPaymentService(new TelegramClient());

        $handled = $service->handleSuccessfulPayment([
            'successful_payment' => [
                'currency'                   => 'XTR',
                'total_amount'               => 65,
                'invoice_payload'            => 'payload_succ_100',
                'telegram_payment_charge_id' => 'tg_charge_xyz_777',
            ]
        ]);

        $this->assertTrue($handled);

        $user->refresh();
        $this->assertEquals(170, $user->coin_balance); // 20 + 150

        $payment->refresh();
        $this->assertEquals('completed', $payment->status);
        $this->assertEquals('tg_charge_xyz_777', $payment->telegram_payment_charge_id);

        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $user->id,
            'coins'   => 150,
            'source'  => 'telegram_stars',
        ]);
    }

    public function test_successful_payment_idempotency_prevents_double_crediting(): void
    {
        Config::set('telegram.payments_enabled', true);

        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true], 200),
        ]);

        $user = User::factory()->create([
            'telegram_id'  => 443322,
            'coin_balance' => 50,
        ]);

        $payment = TelegramStarPayment::create([
            'user_id'                    => $user->id,
            'payload'                    => 'payload_double_check',
            'title'                      => '50 ta tanga',
            'stars_amount'               => 25,
            'coins_awarded'              => 50,
            'telegram_payment_charge_id' => 'charge_already_done_555',
            'status'                     => 'completed',
        ]);

        $service = new TelegramPaymentService(new TelegramClient());

        // Attempting to process the same charge again
        $handled = $service->handleSuccessfulPayment([
            'successful_payment' => [
                'currency'                   => 'XTR',
                'total_amount'               => 25,
                'invoice_payload'            => 'payload_double_check',
                'telegram_payment_charge_id' => 'charge_already_done_555',
            ]
        ]);

        $this->assertTrue($handled);

        $user->refresh();
        // Balance remains unchanged
        $this->assertEquals(50, $user->coin_balance);
    }

    public function test_refund_star_payment_deducts_coins_and_updates_status(): void
    {
        Config::set('telegram.payments_enabled', true);

        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => true], 200),
        ]);

        $user = User::factory()->create([
            'telegram_id'  => 887711,
            'coin_balance' => 150,
        ]);

        $payment = TelegramStarPayment::create([
            'user_id'                    => $user->id,
            'payload'                    => 'payload_refund_test',
            'title'                      => '50 ta tanga',
            'stars_amount'               => 25,
            'coins_awarded'              => 50,
            'telegram_payment_charge_id' => 'charge_refund_target',
            'status'                     => 'completed',
        ]);

        $service = new TelegramPaymentService(new TelegramClient());
        $result = $service->refundPayment($payment, 'Mijoz so\'rovi bo\'yicha');

        $this->assertTrue($result['success']);

        $user->refresh();
        $this->assertEquals(100, $user->coin_balance); // 150 - 50

        $payment->refresh();
        $this->assertEquals('refunded', $payment->status);
        $this->assertNotNull($payment->refunded_at);

        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $user->id,
            'coins'   => -50,
            'source'  => 'telegram_stars_refund',
        ]);
    }

    public function test_reconcile_star_transactions_fulfills_pending_paid_orders(): void
    {
        Config::set('telegram.payments_enabled', true);

        $user = User::factory()->create([
            'telegram_id'  => 334455,
            'coin_balance' => 10,
        ]);

        $payment = TelegramStarPayment::create([
            'user_id'       => $user->id,
            'payload'       => 'star_coin_pack_50_missed_webhook',
            'title'         => '50 ta Oltin tanga',
            'stars_amount'  => 25,
            'coins_awarded' => 50,
            'status'        => 'pending',
        ]);

        Http::fake([
            'https://api.telegram.org/bot*/getStarTransactions' => Http::response([
                'ok'     => true,
                'result' => [
                    'transactions' => [
                        [
                            'id'     => 'reconciled_charge_999',
                            'amount' => 25,
                            'date'   => time() - 120,
                            'source' => [
                                'type'            => 'user',
                                'invoice_payload' => 'star_coin_pack_50_missed_webhook',
                            ],
                        ],
                    ],
                ],
            ], 200),
            'https://api.telegram.org/bot*/sendMessage' => Http::response(['ok' => true], 200),
        ]);

        $this->artisan('telegram:reconcile-stars')
            ->assertExitCode(0);

        $payment->refresh();
        $user->refresh();

        $this->assertEquals('completed', $payment->status);
        $this->assertEquals('reconciled_charge_999', $payment->telegram_payment_charge_id);
        $this->assertEquals(60, $user->coin_balance);
    }

    public function test_shop_page_works_without_telegram_bot_token(): void
    {
        Config::set('telegram.bot_token', null);

        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('shop.index'));

        $response->assertStatus(200);
    }
}
