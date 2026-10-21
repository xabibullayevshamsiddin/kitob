<?php

namespace Tests\Feature\Telegram;

use App\Models\Book;
use App\Models\User;
use App\Services\Telegram\TelegramAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class TelegramMiniAppTest extends TestCase
{
    use RefreshDatabase;

    protected string $botToken = '123456789:ABCdefGHIjklMNOpqrsTUVwxyz';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('telegram.bot_token', $this->botToken);
        Config::set('telegram.bot_username', 'ziyonur_test_bot');
        Config::set('telegram.mini_app_url', 'https://ziyonur.uz/tg-app');
    }

    /**
     * Telegram Mini App (WebAppData) HMAC-SHA256 formatidagi initData satrini hosil qilish.
     */
    protected function generateInitData(array $userData = [], ?int $authDate = null): string
    {
        $authDate = $authDate ?? time();

        $userJson = json_encode(array_merge([
            'id'         => 99887766,
            'first_name' => 'Farrux',
            'last_name'  => 'Karimov',
            'username'   => 'farrux_dev',
        ], $userData));

        $params = [
            'auth_date' => (string) $authDate,
            'query_id'  => 'AAHdF6IQAAAAAN0XohD97a6s',
            'user'      => $userJson,
        ];

        $dataCheckArr = [];
        foreach ($params as $k => $v) {
            $dataCheckArr[] = $k . '=' . $v;
        }
        sort($dataCheckArr);
        $dataCheckString = implode("\n", $dataCheckArr);

        // WebAppData HMAC
        $secretKey = hash_hmac('sha256', $this->botToken, 'WebAppData', true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

        $params['hash'] = $hash;

        return http_build_query($params);
    }

    public function test_verify_init_data_accepts_valid_signature(): void
    {
        $service = new TelegramAuthService();
        $initData = $this->generateInitData(['id' => 12345678]);

        $result = $service->verifyInitData($initData);

        $this->assertIsArray($result);
        $this->assertEquals(12345678, $result['user_data']['id']);
        $this->assertEquals('Farrux', $result['user_data']['first_name']);
    }

    public function test_verify_init_data_rejects_tampered_signature(): void
    {
        $service = new TelegramAuthService();
        $initData = $this->generateInitData(['id' => 12345678]);

        // Maliciously change user id in query string without changing hash
        $tampered = str_replace('12345678', '87654321', $initData);

        $result = $service->verifyInitData($tampered);

        $this->assertFalse($result);
    }

    public function test_verify_init_data_rejects_expired_auth_date(): void
    {
        $service = new TelegramAuthService();
        // 25 hours ago
        $initData = $this->generateInitData(['id' => 12345678], time() - (25 * 3600));

        $result = $service->verifyInitData($initData);

        $this->assertFalse($result);
    }

    public function test_tg_app_page_renders_successfully(): void
    {
        Book::create([
            'title'       => 'Sariq devni minib',
            'slug'        => 'sariq-devni-minib-' . time(),
            'author'      => 'Xudoyberdi To\'xtaboyev',
            'description' => 'Bolalar adabiyoti durdonasi',
            'genre'       => 'Badiiy',
            'week_number' => 1,
            'is_active'   => true,
        ]);

        $response = $this->get(route('tg-app'));

        $response->assertStatus(200);
        $response->assertSee('Kitobxon — Telegram Mini App');
        $response->assertSee('telegram-web-app.js');
        $response->assertSee('Sariq devni minib');
    }

    public function test_tg_app_auth_logs_in_user_with_valid_init_data(): void
    {
        $initData = $this->generateInitData([
            'id'         => 88776655,
            'first_name' => 'Ziyoda',
            'last_name'  => 'Alimova',
            'username'   => 'ziyoda_read',
        ]);

        $response = $this->postJson(route('tg-app.auth'), [
            'init_data' => $initData,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'user'    => [
                'name' => 'Ziyoda Alimova',
            ],
        ]);

        $this->assertTrue(Auth::check());
        $this->assertEquals(88776655, Auth::user()->telegram_id);
    }

    public function test_tg_app_auth_rejects_invalid_init_data(): void
    {
        $response = $this->postJson(route('tg-app.auth'), [
            'init_data' => 'invalid_init_data_string',
        ]);

        $response->assertStatus(401);
        $this->assertFalse(Auth::check());
    }

    public function test_tg_app_auth_blocks_banned_user(): void
    {
        $user = User::factory()->create([
            'telegram_id' => 99911122,
            'is_banned'   => true,
            'ban_reason'  => 'Qoidabuzarlik',
        ]);

        $initData = $this->generateInitData(['id' => 99911122]);

        $response = $this->postJson(route('tg-app.auth'), [
            'init_data' => $initData,
        ]);

        $response->assertStatus(403);
        $this->assertFalse(Auth::check());
    }
}
