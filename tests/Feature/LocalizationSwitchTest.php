<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LocalizationSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    }

    /** @test */
    public function it_switches_locale_via_query_param_and_persists_in_session()
    {
        // Default is uz
        $res = $this->get('/?lang=uz');
        $res->assertOk();
        $res->assertSee('lang="uz"', false);
        $res->assertSee('Bosh sahifa');

        // Switch to ru
        $resRu = $this->get('/?lang=ru');
        $resRu->assertOk();
        $resRu->assertSee('lang="ru"', false);
        $resRu->assertSee('Главная');
        $resRu->assertSee('Начать чтение');
        $resRu->assertSee('Все сервисы работают');

        // Subsequent page retains ru
        $resAbout = $this->get('/about');
        $resAbout->assertOk();
        $resAbout->assertSee('lang="ru"', false);
        $resAbout->assertSee('О нас');

        // Switch to en
        $resEn = $this->get('/?lang=en');
        $resEn->assertOk();
        $resEn->assertSee('lang="en"', false);
        $resEn->assertSee('Home');
        $resCatalog = $this->get('/catalog');
        $resCatalog->assertOk();
        $resCatalog->assertSee('lang="en"', false);
        $resCatalog->assertSee('Books library');
    }

    /** @test */
    public function it_renders_home_page_scrollytelling_and_footer_in_all_languages()
    {
        // 1. Russian
        $resRu = $this->get('/?lang=ru');
        $resRu->assertOk();
        $resRu->assertSee('Начать чтение');
        $resRu->assertSee('Все сервисы работают');
        $resRu->assertSee('SSL 256-бит защита');
        $resRu->assertSee('Политика конфиденциальности');
        $resRu->assertSee('Подать жалобу');
        $resRu->assertSee('Правила и санкции');
        // Ensure no untranslated Uzbek residue in Scrolly/Footer
        $resRu->assertDontSee('Barcha xizmatlar faol');
        $resRu->assertDontSee('Scrollytelling Tajribasi');
        $resRu->assertDontSee('SHARAF ZALI');

        // 2. English
        $resEn = $this->get('/?lang=en');
        $resEn->assertOk();
        $resEn->assertSee('Start reading');
        $resEn->assertSee('All systems operational');
        $resEn->assertSee('SSL 256-bit secure');
        $resEn->assertSee('Privacy Policy');
        $resEn->assertSee('Report Issue');
        $resEn->assertSee('Rules &amp; Penalties', false);
        $resEn->assertDontSee('Barcha xizmatlar faol');
        $resEn->assertDontSee('Scrollytelling Tajribasi');
        $resEn->assertDontSee('SHARAF ZALI');
    }

    /** @test */
    public function book_detail_page_renders_localized_strings_cleanly()
    {
        $user = User::create([
            'name' => 'Reader User',
            'email' => 'reader@example.com',
            'username' => 'reader_user',
            'password' => Hash::make('secret123'),
            'email_verified_at' => now(),
        ]);
        $user->syncRoles(['student']);
        UserProfile::create(['user_id' => $user->id, 'reading_place' => 'home']);

        $book = Book::create([
            'title' => 'Alkimyogar',
            'author' => 'Paulo Koelo',
            'slug' => 'alkimyogar',
            'week_number' => 1,
            'is_active' => true,
            'genre' => 'Badiiy',
            'description' => 'Ajoyib asar',
            'pdf_path' => 'books/test.pdf',
        ]);

        // In UZ
        $resUz = $this->actingAs($user)->get(route('books.show', $book->slug) . '?lang=uz');
        $resUz->assertOk();
        $resUz->assertSee('Keyinroq o&#039;qiyman', false);

        // In RU
        $resRu = $this->actingAs($user)->get(route('books.show', $book->slug) . '?lang=ru');
        $resRu->assertOk();
        $resRu->assertSee('Читать');
        $resRu->assertSee('Прочитаю позже');

        // In EN
        $resEn = $this->actingAs($user)->get(route('books.show', $book->slug) . '?lang=en');
        $resEn->assertOk();
        $resEn->assertSee('Read');
        $resEn->assertSee('Read later');
    }

    /** @test */
    public function profile_page_renders_localized_strings_cleanly()
    {
        $user = User::create([
            'name' => 'Profile User',
            'email' => 'profile@example.com',
            'username' => 'profile_user',
            'password' => Hash::make('secret123'),
            'email_verified_at' => now(),
        ]);
        $user->syncRoles(['student']);
        UserProfile::create(['user_id' => $user->id, 'reading_place' => 'home']);

        // In UZ
        $resUz = $this->actingAs($user)->get('/profile/' . $user->username . '?lang=uz');
        $resUz->assertOk();
        $resUz->assertSee('Tanga do&#039;koni', false);
        $resUz->assertSee('Umumiy ko&#039;rinish', false);

        // In RU
        $resRu = $this->actingAs($user)->get('/profile/' . $user->username . '?lang=ru');
        $resRu->assertOk();
        $resRu->assertSee('Магазин монет');
        $resRu->assertSee('Обзор');

        // In EN
        $resEn = $this->actingAs($user)->get('/profile/' . $user->username . '?lang=en');
        $resEn->assertOk();
        $resEn->assertSee('Coin Shop');
        $resEn->assertSee('Overview');
    }
}
