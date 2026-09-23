<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookChapter;
use App\Models\User;
use App\Services\Gamification\StreakService;
use Tests\TestCase;

class ReadingAndStreakTest extends TestCase
{
    /** @test */
    public function catalog_page_is_accessible()
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/books');
        $response->assertStatus(200);
    }

    /** @test */
    public function book_detail_page_is_accessible()
    {
        $user = User::first();
        $book = Book::first();

        $response = $this->actingAs($user)->get("/books/{$book->slug}");
        $response->assertStatus(200);
    }

    /** @test */
    public function reader_page_is_accessible()
    {
        $user = User::first();
        $book = Book::first();
        $chapter = BookChapter::where('book_id', $book->id)->first();

        $response = $this->actingAs($user)->get("/books/{$book->id}/read/{$chapter->id}");
        $response->assertStatus(200);
    }

    /** @test */
    public function streak_service_records_activity_and_increments_streak()
    {
        $user = User::first();
        $streakService = new StreakService();

        $streak = $streakService->recordActivity($user);

        $this->assertNotNull($streak);
        $this->assertGreaterThanOrEqual(1, $streak->current_streak);
    }
}
