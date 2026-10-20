<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\CoinTransaction;
use App\Models\PointTransaction;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\ReadingSession;
use App\Models\ShopItem;
use App\Models\ShopPurchase;
use App\Models\User;
use App\Models\UserEquipment;
use App\Models\UserItem;
use App\Models\UserStreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUserDetail360Test extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $this->admin = User::create([
            'name'              => 'Admin User',
            'username'          => 'admin_user',
            'email'             => 'admin_360@kitobxon.uz',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'admin',
        ]);
        $this->admin->syncRoles(['admin']);

        $this->student = User::create([
            'name'              => 'Ali Valiyev',
            'username'          => 'alivaliyev',
            'email'             => 'ali@kitobxon.uz',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'student',
            'total_points'      => 450,
            'coin_balance'      => 80,
        ]);
        $this->student->syncRoles(['student']);
    }

    /** @test */
    public function admin_can_view_user_edit_page_with_complete_360_data(): void
    {
        // 1. O'qish ma'lumotlari
        ReadingSession::create([
            'user_id'      => $this->student->id,
            'minutes_read' => 45,
            'session_date' => now()->toDateString(),
        ]);

        UserStreak::create([
            'user_id'          => $this->student->id,
            'current_streak'   => 5,
            'longest_streak'   => 12,
            'freeze_count'     => 2,
            'last_active_date' => now()->toDateString(),
        ]);

        // 2. Do'kon buyumi va xarid
        $item = ShopItem::create([
            'key'           => 'golden_frame',
            'category'      => 'profile',
            'slot'          => 'avatar_frame',
            'name'          => ['uz' => 'Oltin Ramka', 'ru' => 'Золотая рамка', 'en' => 'Golden Frame'],
            'description'   => ['uz' => 'Hashamatli oltin ramka', 'ru' => 'Описание', 'en' => 'Desc'],
            'price_coins'   => 50,
            'rarity'        => 'epic',
            'is_consumable' => false,
            'max_quantity'  => 1,
            'is_active'     => true,
        ]);

        UserItem::create([
            'user_id'      => $this->student->id,
            'shop_item_id' => $item->id,
            'quantity'     => 1,
            'acquired_at'  => now(),
        ]);

        UserEquipment::create([
            'user_id'      => $this->student->id,
            'slot'         => 'avatar_frame',
            'shop_item_id' => $item->id,
        ]);

        ShopPurchase::create([
            'user_id'        => $this->student->id,
            'shop_item_id'   => $item->id,
            'quantity'       => 1,
            'price_paid'     => 50,
            'purchase_token' => 'PURCHASE_TEST_TOKEN_123',
            'created_at'     => now(),
        ]);

        // 3. Tranzaksiyalar
        CoinTransaction::create([
            'user_id'       => $this->student->id,
            'coins'         => -50,
            'source'        => 'shop_purchase',
            'description'   => 'Oltin Ramka xaridi',
            'balance_after' => 80,
        ]);

        PointTransaction::create([
            'user_id'     => $this->student->id,
            'points'      => 100,
            'source'      => 'reading',
            'description' => '45 daqiqa mutolaa uchun',
        ]);

        // 4. Kitob va Test
        $book = Book::create([
            'title'        => 'Atom Odatlar',
            'slug'         => 'atom-odatlar',
            'author'       => 'James Clear',
            'description'  => 'Odatlar haqida kitob',
            'genre'        => 'Shaxsiy rivojlanish',
            'week_number'  => 1,
            'is_active'    => true,
            'published_at' => now(),
        ]);

        $quiz = Quiz::create([
            'book_id'            => $book->id,
            'title'              => 'Atom Odatlar 1-bob testi',
            'chapter_number'     => 1,
            'difficulty'         => 'medium',
            'reward_points'      => 50,
            'time_limit_minutes' => 15,
            'is_active'          => true,
        ]);

        QuizAttempt::create([
            'user_id'      => $this->student->id,
            'quiz_id'      => $quiz->id,
            'score'        => 9,
            'max_score'    => 10,
            'percent'      => 90.0,
            'completed_at' => now(),
        ]);

        // So'rov yuborish
        $response = $this->actingAs($this->admin)->get(route('admin.users.edit', $this->student));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.edit');

        // Barcha ko'rsatkichlar borligini tasdiqlash
        $response->assertViewHas([
            'leaderboardRank',
            'totalUsersCount',
            'totalReadingMinutes',
            'avgDailyMinutes',
            'readingProgress',
            'booksFinishedCount',
            'booksInProgressCount',
            'userItems',
            'shopPurchases',
            'equipment',
            'coinTransactions',
            'pointTransactions',
            'quizAttempts',
        ]);

        // Sahifada foydalanuvchi ma'lumotlari to'g'ri chiqqanini tekshirish
        $response->assertSee('Ali Valiyev');
        $response->assertSee('alivaliyev');
        $response->assertSee('Oltin Ramka');
        $response->assertSee('Atom Odatlar 1-bob testi');
        $response->assertSee('PURCHASE_TEST_TOKEN_123');
        $response->assertSee('Oltin Ramka xaridi');
    }

    /** @test */
    public function admin_cannot_edit_another_admin(): void
    {
        $anotherAdmin = User::create([
            'name'              => 'Super Admin',
            'username'          => 'super_admin',
            'email'             => 'super_admin@kitobxon.uz',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'admin',
        ]);
        $anotherAdmin->syncRoles(['admin']);

        $response = $this->actingAs($this->admin)->get(route('admin.users.edit', $anotherAdmin));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');
    }
}
