<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookChapter;
use App\Models\User;
use App\Services\Gamification\StreakService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReadingAndStreakTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
    }

    protected function getStudentUser(): User
    {
        $user = User::create(
            [
                'email' => 'reader_test_' . uniqid() . '@kitobxon.uz',
                'name' => 'Reader Test',
                'username' => 'reader_test_' . uniqid(),
                'password' => bcrypt('password'),
            ]
        );
        $user->syncRoles(['student']);
        \App\Models\UserProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['reading_place' => 'home']
        );
        return $user;
    }

    /** @test */
    public function catalog_page_is_accessible()
    {
        $user = $this->getStudentUser();
        // /books /catalog sahifasiga redirect qiladi
        $response = $this->actingAs($user)->get('/catalog');
        $response->assertStatus(200);
    }

    /** @test */
    public function book_detail_page_is_accessible()
    {
        $user = $this->getStudentUser();
        $book = Book::create([
            'title' => 'Test Kitob ' . uniqid(),
            'slug' => 'test-kitob-' . uniqid(),
            'author' => 'Test Muallif',
            'description' => 'Test uchun yaratilgan kitob tavsifi.',
            'genre' => 'Test',
            'week_number' => 1,
            'is_active' => true,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($user)->get("/books/{$book->slug}");
        $response->assertStatus(200);
    }

    /** @test */
    public function reader_page_is_accessible()
    {
        $user = $this->getStudentUser();
        $book = Book::create([
            'title' => 'Oqish Kitob ' . uniqid(),
            'slug' => 'oqish-kitob-' . uniqid(),
            'author' => 'Test Muallif',
            'description' => 'Test uchun yaratilgan kitob tavsifi.',
            'genre' => 'Test',
            'week_number' => 1,
            'is_active' => true,
            'published_at' => now(),
        ]);
        $chapter = BookChapter::create([
            'book_id' => $book->id,
            'chapter_number' => 1,
            'title' => '1-bob',
            'content' => 'Test matn.',
            'is_published' => true,
        ]);

        $response = $this->actingAs($user)->get("/books/{$book->id}/read/{$chapter->id}");
        $response->assertStatus(200);
    }

    /** @test */
    public function streak_service_records_activity_and_increments_streak()
    {
        $user = $this->getStudentUser();
        $streakService = new StreakService();

        $streak = $streakService->recordActivity($user);

        $this->assertNotNull($streak);
        $this->assertGreaterThanOrEqual(1, $streak->current_streak);
    }

    /** @test */
    public function no_reading_result_is_cached_for_one_minute_per_user_and_day()
    {
        $user = $this->getStudentUser();
        $readingQueries = 0;
        DB::listen(function ($query) use (&$readingQueries) {
            $sql = strtolower($query->sql);
            if (
                (str_contains($sql, 'reading_sessions') && str_contains($sql, 'session_date'))
                || (str_contains($sql, 'daily_activities') && str_contains($sql, 'activity_date'))
            ) {
                $readingQueries++;
            }
        });

        $this->actingAs($user)->get('/')->assertOk();
        $this->assertSame(2, $readingQueries);

        $this->get('/')->assertOk();
        $this->assertSame(2, $readingQueries);

        \Carbon\Carbon::setTestNow(now()->addSeconds(61));
        try {
            $this->get('/')->assertOk();
            $this->assertSame(4, $readingQueries);
        } finally {
            \Carbon\Carbon::setTestNow();
        }
    }
}
