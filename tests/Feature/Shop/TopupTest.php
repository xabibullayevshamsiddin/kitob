<?php

namespace Tests\Feature\Shop;

use App\Http\Livewire\Shop\TopupPage;
use App\Models\CoinTransaction;
use App\Models\Setting;
use App\Models\TopupRequest;
use App\Models\User;
use App\Services\Shop\Exceptions\ShopException;
use App\Services\Topup\ReceiptVisionAnalyzer;
use App\Services\Topup\TopupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TopupTest extends TestCase
{
    use RefreshDatabase;

    /** Soxta AI natijasi — har testda o'zgartiriladi */
    protected ?array $fakeAi = null;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        // Haqiqiy Telegram/Gemini'ga hech qanday so'rov ketmasin (.env da haqiqiy tokenlar bor)
        Http::fake();
        Http::preventStrayRequests();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        Setting::set('topup_enabled', true, 'topup', 'boolean');
        Setting::set('topup_card_number', '8600123412345678', 'topup', 'string');

        $test = $this;
        $this->app->instance(ReceiptVisionAnalyzer::class, new class($test) extends ReceiptVisionAnalyzer {
            public function __construct(private $test) {}
            public function isAvailable(): bool { return true; }
            public function analyze(string $absolutePath, string $mime, array $context): ?array { return $this->test->fakeAiResult(); }
        });
    }

    public function fakeAiResult(): ?array
    {
        return $this->fakeAi;
    }

    private function makeUser(string $prefix = 'buyer', bool $admin = false): User
    {
        $user = User::create([
            'name'     => ucfirst($prefix),
            'username' => $prefix . '_' . uniqid(),
            'email'    => $prefix . '_' . uniqid() . '@test.uz',
            'password' => bcrypt('password'),
            'role'     => $admin ? 'admin' : 'student',
        ]);
        if ($admin) {
            $user->syncRoles(['admin']);
        }

        return $user;
    }

    /** Har chaqiruvda boshqacha (tasodifiy naqshli) haqiqiy PNG */
    private function receiptImage(string $name = 'chek.png'): UploadedFile
    {
        $img = imagecreatetruecolor(400, 700);
        for ($i = 0; $i < 25; $i++) {
            $color = imagecolorallocate($img, random_int(0, 255), random_int(0, 255), random_int(0, 255));
            imagefilledrectangle($img, random_int(0, 380), random_int(0, 680), random_int(0, 400), random_int(0, 700), $color);
        }
        $path = tempnam(sys_get_temp_dir(), 'rcpt') . '.png';
        imagepng($img, $path);
        imagedestroy($img);

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    private function aiFor(TopupRequest $topup, array $overrides = []): array
    {
        return array_merge([
            'is_payment_receipt'     => true,
            'transfer_successful'    => true,
            'amount'                 => $topup->expected_amount,
            'currency'               => 'UZS',
            'datetime'               => now('Asia/Tashkent')->format('Y-m-d\TH:i:s'),
            'recipient_card_last4'   => '5678',
            'recipient_name'         => 'TEST',
            'sender_card_last4'      => null,
            'transaction_id'         => 'TX' . uniqid(),
            'app_name'               => 'Click',
            'edit_suspected'         => false,
            'ai_generated_suspected' => false,
            'suspicion_reasons'      => [],
            'confidence'             => 0.9,
            'model'                  => 'fake',
        ], $overrides);
    }

    private function service(): TopupService
    {
        return app(TopupService::class);
    }

    private function submitted(User $user, string $package = 'coins_250', array $aiOverrides = []): TopupRequest
    {
        $topup = $this->service()->createRequest($user, $package);
        $this->travel(1)->minutes();
        $this->fakeAi = $this->aiFor($topup, $aiOverrides);

        return $this->service()->submitReceipt($topup, $user, $this->receiptImage());
    }

    public function test_request_gets_unique_amount_and_only_one_open_request_is_allowed(): void
    {
        $a = $this->makeUser('a');
        $b = $this->makeUser('b');

        $first = $this->service()->createRequest($a, 'coins_250');
        $second = $this->service()->createRequest($b, 'coins_250');

        $this->assertSame(10000, $first->base_amount);
        $this->assertGreaterThan(10000, $first->expected_amount);
        $this->assertLessThanOrEqual(10099, $first->expected_amount);
        $this->assertNotSame($first->expected_amount, $second->expected_amount);
        $this->assertSame('5678', $first->card_last4);

        $this->expectException(ShopException::class);
        $this->service()->createRequest($a, 'coins_100');
    }

    public function test_daily_limit_is_enforced(): void
    {
        Setting::set('topup_daily_limit', 15000, 'topup', 'integer');
        $user = $this->makeUser();

        $this->submitted($user, 'coins_250');

        $this->expectException(ShopException::class);
        $this->service()->createRequest($user, 'coins_250');
    }

    public function test_disabled_topup_cannot_be_used(): void
    {
        Setting::set('topup_enabled', false, 'topup', 'boolean');
        $user = $this->makeUser();

        $this->actingAs($user)->get(route('topup.index'))->assertOk()->assertSee('hozircha yopiq');

        $this->expectException(ShopException::class);
        $this->service()->createRequest($user, 'coins_250');
    }

    public function test_matching_receipt_goes_to_review_with_low_risk_on_private_disk(): void
    {
        $topup = $this->submitted($this->makeUser());

        $this->assertSame(TopupRequest::STATUS_PENDING_REVIEW, $topup->status);
        $this->assertSame('done', $topup->ai_status);
        $this->assertSame(0, $topup->risk_score);
        $this->assertContains('ai_match', array_column($topup->checks, 'code'));
        Storage::disk('local')->assertExists($topup->receipt_path);
        $this->assertStringStartsWith('topup-receipts/', $topup->receipt_path);
    }

    public function test_wrong_amount_card_and_old_date_are_flagged_as_danger(): void
    {
        $topup = $this->submitted($this->makeUser(), 'coins_250', [
            'amount'               => 9000,
            'recipient_card_last4' => '1111',
            'datetime'             => now('Asia/Tashkent')->subDays(3)->format('Y-m-d\TH:i:s'),
        ]);

        $codes = array_column($topup->checks, 'code');
        $this->assertContains('amount_mismatch', $codes);
        $this->assertContains('card_mismatch', $codes);
        $this->assertContains('receipt_too_old', $codes);
        $this->assertSame('high', $topup->risk_level);
    }

    public function test_same_receipt_file_cannot_be_used_twice(): void
    {
        $first = $this->makeUser('first');
        $second = $this->makeUser('second');
        $file = $this->receiptImage();
        $copy = tempnam(sys_get_temp_dir(), 'dup') . '.png';
        copy($file->getRealPath(), $copy);

        $one = $this->service()->createRequest($first, 'coins_250');
        $this->fakeAi = $this->aiFor($one);
        $this->service()->submitReceipt($one, $first, $file);

        $two = $this->service()->createRequest($second, 'coins_250');

        $this->expectException(ShopException::class);
        $this->expectExceptionMessage('avval yuborilgan');
        $this->service()->submitReceipt($two, $second, new UploadedFile($copy, 'chek.png', 'image/png', null, true));
    }

    public function test_reused_transaction_id_is_flagged(): void
    {
        $this->submitted($this->makeUser('one'), 'coins_250', ['transaction_id' => 'TX-777']);
        $second = $this->submitted($this->makeUser('two'), 'coins_250', ['transaction_id' => 'TX-777']);

        $this->assertContains('duplicate_transaction', array_column($second->checks, 'code'));
    }

    public function test_ai_failure_still_queues_receipt_for_manual_review(): void
    {
        $user = $this->makeUser();
        $topup = $this->service()->createRequest($user, 'coins_250');
        $this->fakeAi = null;

        $topup = $this->service()->submitReceipt($topup, $user, $this->receiptImage());

        $this->assertSame(TopupRequest::STATUS_PENDING_REVIEW, $topup->status);
        $this->assertSame('failed', $topup->ai_status);
        $this->assertContains('ai_unavailable', array_column($topup->checks, 'code'));
    }

    public function test_admin_approval_credits_coins_and_points_exactly_once(): void
    {
        $admin = $this->makeUser('admin', true);
        $buyer = $this->makeUser();
        $coinsTopup = $this->submitted($buyer, 'coins_250');

        // Pul tushganini tasdiqlamasdan tasdiqlab bo'lmaydi
        $this->actingAs($admin)->post(route('admin.topups.approve', $coinsTopup))->assertSessionHasErrors('money_confirmed');
        $this->assertSame(0, (int) $buyer->fresh()->coin_balance);

        $this->actingAs($admin)->post(route('admin.topups.approve', $coinsTopup), ['money_confirmed' => '1'])
            ->assertRedirect(route('admin.topups.index'));

        $this->assertSame(250, (int) $buyer->fresh()->coin_balance);
        $this->assertSame(TopupRequest::STATUS_APPROVED, $coinsTopup->fresh()->status);
        $this->assertSame($admin->id, $coinsTopup->fresh()->reviewed_by);
        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $buyer->id, 'coins' => 250, 'source' => 'purchase',
            'reference_type' => TopupRequest::class, 'reference_id' => $coinsTopup->id,
        ]);

        // Ikkinchi marta bosish (yoki ikkinchi admin) — qayta berilmaydi
        $this->actingAs($admin)->post(route('admin.topups.approve', $coinsTopup), ['money_confirmed' => '1'])
            ->assertSessionHas('error');
        $this->assertSame(250, (int) $buyer->fresh()->coin_balance);
        $this->assertSame(1, CoinTransaction::where('source', 'purchase')->count());

        // Ball paketi
        $this->travel(1)->minutes();
        $pointsTopup = $this->submitted($buyer, 'points_500');
        $this->actingAs($admin)->post(route('admin.topups.approve', $pointsTopup), ['money_confirmed' => '1']);
        $this->assertSame(500, (int) $buyer->fresh()->total_points);
    }

    public function test_admin_can_reject_with_reason_and_nothing_is_credited(): void
    {
        $admin = $this->makeUser('admin', true);
        $buyer = $this->makeUser();
        $topup = $this->submitted($buyer);

        $this->actingAs($admin)->post(route('admin.topups.reject', $topup), [])->assertSessionHasErrors('reject_reason');

        $this->actingAs($admin)->post(route('admin.topups.reject', $topup), ['reject_reason' => 'Pul kartaga tushmadi'])
            ->assertRedirect(route('admin.topups.index'));

        $topup->refresh();
        $this->assertSame(TopupRequest::STATUS_REJECTED, $topup->status);
        $this->assertSame('Pul kartaga tushmadi', $topup->reject_reason);
        $this->assertSame(0, (int) $buyer->fresh()->coin_balance);
        $this->assertSame(1, $buyer->notifications()->count());

        $this->expectException(ShopException::class);
        $this->service()->approve($topup, $admin);
    }

    public function test_admin_pages_and_receipt_are_admin_only(): void
    {
        $admin = $this->makeUser('admin', true);
        $buyer = $this->makeUser();
        $topup = $this->submitted($buyer);

        $this->actingAs($buyer)->get(route('admin.topups.receipt', $topup))->assertForbidden();
        $this->actingAs($buyer)->get(route('admin.topups.show', $topup))->assertForbidden();

        $this->actingAs($admin)->get(route('admin.topups.index'))->assertOk()->assertSee($topup->reference);
        $this->actingAs($admin)->get(route('admin.topups.show', $topup))->assertOk()->assertSee('haqiqatan tushganini');
        $this->actingAs($admin)->get(route('admin.topups.receipt', $topup))->assertOk();
        $this->actingAs($admin)->get(route('admin.topups.settings'))->assertOk()->assertSee('8600 1234 1234 5678');
    }

    public function test_admin_can_update_card_and_packages(): void
    {
        $admin = $this->makeUser('admin', true);

        $this->actingAs($admin)->post(route('admin.topups.settings.update'), [
            'enabled'     => '1',
            'card_number' => '9860 1111 2222 3333',
            'card_holder' => 'ALI VALIYEV',
            'daily_limit' => 50000,
            'packages'    => [
                ['title' => '300 tanga', 'price' => 12000, 'coins' => 300, 'points' => 0, 'active' => '1'],
                ['title' => '', 'price' => '', 'coins' => '', 'points' => ''],
            ],
        ])->assertRedirect(route('admin.topups.settings'));

        $service = $this->service();
        $this->assertSame('9860111122223333', $service->cardDigits());
        $this->assertCount(1, $service->activePackages());
        $this->assertSame(12000, $service->activePackages()[0]['price']);
    }

    public function test_settings_warn_when_points_package_undercuts_coin_price(): void
    {
        $admin = $this->makeUser('admin', true);
        Setting::set('shop_exchange_rate', 1, 'shop', 'integer');

        // 1 ball = 1 tanga bo'lsa, 10 000 so'mga 500 ball → 500 tanga (tanga paketida esa 250)
        $this->actingAs($admin)->get(route('admin.topups.settings'))
            ->assertOk()
            ->assertSee('arzonroq tanga olish mumkin');

        Setting::set('shop_exchange_rate', 15, 'shop', 'integer');
        $this->actingAs($admin)->get(route('admin.topups.settings'))
            ->assertDontSee('arzonroq tanga olish mumkin');
    }

    public function test_user_page_full_flow_through_livewire(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $component = Livewire::test(TopupPage::class)
            ->assertSee('250 tanga')
            ->call('choosePackage', 'coins_250')
            ->assertSee('8600 1234 1234 5678');

        $topup = TopupRequest::where('user_id', $user->id)->firstOrFail();
        $component->assertSee(number_format($topup->expected_amount, 0, '.', ' '));

        $this->travel(1)->minutes();
        $this->fakeAi = $this->aiFor($topup);

        $png = $this->receiptImage();
        $component->set('receipt', UploadedFile::fake()->createWithContent('chek.png', file_get_contents($png->getRealPath())))
            ->set('payerCardLast4', '4321')
            ->call('submitReceipt')
            ->assertHasNoErrors()
            ->assertSee('tekshirilmoqda');

        $this->assertSame(TopupRequest::STATUS_PENDING_REVIEW, $topup->fresh()->status);
        $this->assertSame('4321', $topup->fresh()->payer_card_last4);
    }

    public function test_shop_buy_button_visibility(): void
    {
        $student = $this->makeUser();
        $admin = $this->makeUser('admin', true);

        // Yoqilgan: hamma ko'radi
        $this->actingAs($student)->get(route('shop.index'))->assertOk()->assertSee(route('topup.index'), false);

        // O'chiq: faqat admin ko'radi (sozlash uchun)
        Setting::set('topup_enabled', false, 'topup', 'boolean');
        $this->actingAs($student)->get(route('shop.index'))->assertOk()->assertDontSee(route('topup.index'), false);
        $this->actingAs($admin)->get(route('shop.index'))->assertOk()->assertSee(route('topup.index'), false);
    }

    public function test_user_can_cancel_unpaid_request(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);
        $this->service()->createRequest($user, 'coins_100');

        Livewire::test(TopupPage::class)->call('cancelRequest');

        $this->assertSame(TopupRequest::STATUS_CANCELLED, TopupRequest::first()->status);
    }
}
