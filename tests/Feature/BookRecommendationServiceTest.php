<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookReadingProgress;
use App\Models\BookChapter;
use App\Models\User;
use App\Services\BookRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookRecommendationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeBook(string $title, string $genre): Book
    {
        return Book::create([
            'title' => $title,
            'slug' => strtolower(str_replace(' ', '-', $title)) . '-' . uniqid(),
            'author' => 'Muallif',
            'description' => 'Tavsif',
            'genre' => $genre,
            'week_number' => random_int(1, 1000),
            'is_active' => true,
        ]);
    }

    public function test_recommendations_prioritize_read_genres_and_exclude_books_already_started(): void
    {
        $user = User::create([
            'name' => 'Kitobxon',
            'username' => 'booklover_' . uniqid(),
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('password'),
        ]);
        $startedBook = $this->makeBook('Boshlangan asar', 'Tarixiy');
        $chapter = BookChapter::create([
            'book_id' => $startedBook->id,
            'chapter_number' => 1,
            'title' => 'Birinchi bob',
            'content' => 'Matn',
            'is_published' => true,
        ]);
        BookReadingProgress::create([
            'user_id' => $user->id,
            'book_id' => $startedBook->id,
            'chapter_id' => $chapter->id,
            'last_position' => 120,
            'percent_complete' => 40,
        ]);

        $preferred = $this->makeBook('Yangi tarixiy kitob', 'Tarixiy');
        $this->makeBook('Boshqa janr', 'Fantastika');

        $recommendations = app(BookRecommendationService::class)->forUser($user, 4);

        $this->assertTrue($recommendations->contains('id', $preferred->id));
        $this->assertFalse($recommendations->contains('id', $startedBook->id));
        $this->assertSame('Tarixiy', $recommendations->first()->genre);
    }

    public function test_catalog_renders_personalized_recommendations_for_authenticated_readers(): void
    {
        $user = User::create([
            'name' => 'Reader',
            'username' => 'reader_' . uniqid(),
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('password'),
        ]);
        $readBook = $this->makeBook('O‘qilgan tarix', 'Tarixiy');
        $chapter = BookChapter::create([
            'book_id' => $readBook->id,
            'chapter_number' => 1,
            'title' => 'Bob',
            'content' => 'Matn',
            'is_published' => true,
        ]);
        BookReadingProgress::create([
            'user_id' => $user->id,
            'book_id' => $readBook->id,
            'chapter_id' => $chapter->id,
            'last_position' => 20,
            'percent_complete' => 10,
        ]);
        $recommended = $this->makeBook('Tavsiya tarix', 'Tarixiy');

        $this->actingAs($user)
            ->get('/catalog')
            ->assertOk()
            ->assertSee('Sizga mos kitoblar')
            ->assertSee($recommended->title);
    }

    public function test_catalog_does_not_show_empty_message_when_books_exist_for_fresh_user(): void
    {
        $user = User::create([
            'name' => 'Fresh User',
            'username' => 'fresh_' . uniqid(),
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('password'),
        ]);

        $book1 = $this->makeBook('Algebra 8-sinf', 'Darslik');
        $book2 = $this->makeBook('Geometriya 9-sinf', 'Darslik');

        $this->actingAs($user)
            ->get('/catalog')
            ->assertOk()
            ->assertDontSee('Hozircha kitoblar mavjud emas')
            ->assertSee($book1->title)
            ->assertSee($book2->title);
    }

    public function test_catalog_does_not_hide_books_from_main_list_when_recommendations_exist(): void
    {
        $user = User::create([
            'name' => 'Reader Two',
            'username' => 'reader2_' . uniqid(),
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('password'),
        ]);

        $readBook = $this->makeBook('O‘qilgan kitob', 'Ilmiy');
        $chapter = BookChapter::create([
            'book_id' => $readBook->id,
            'chapter_number' => 1,
            'title' => 'Bob 1',
            'content' => 'Matn',
            'is_published' => true,
        ]);
        BookReadingProgress::create([
            'user_id' => $user->id,
            'book_id' => $readBook->id,
            'chapter_id' => $chapter->id,
            'last_position' => 10,
            'percent_complete' => 20,
        ]);

        $recommended = $this->makeBook('Tavsiya ilmiy', 'Ilmiy');

        $this->actingAs($user)
            ->get('/catalog')
            ->assertOk()
            ->assertSee('Sizga mos kitoblar')
            ->assertSee('Barcha kitoblar')
            ->assertDontSee('Hozircha kitoblar mavjud emas')
            ->assertSee($recommended->title)
            ->assertSee($readBook->title);
    }

    public function test_catalog_does_not_duplicate_books_when_all_books_would_be_in_recommendations(): void
    {
        $user = User::create([
            'name' => 'History User',
            'username' => 'hist_' . uniqid(),
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('password'),
        ]);

        \Illuminate\Support\Facades\DB::table('reading_sessions')->insert([
            'user_id' => $user->id,
            'book_id' => null,
            'minutes_read' => 15,
            'session_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $book1 = $this->makeBook('Algebra Kitob', 'Darslik');
        $book2 = $this->makeBook('Geometriya Kitob', 'Darslik');

        $response = $this->actingAs($user)->get('/catalog');

        $response->assertOk();
        // Since all available books (2) are recommendations candidates,
        // it must not split into duplicate sections
        $response->assertDontSee('Sizga mos kitoblar');
        $response->assertDontSee('Hozircha kitoblar mavjud emas');

        // Verify each book card is rendered exactly once (not in recommendations and main list)
        $this->assertSame(1, substr_count($response->getContent(), 'wire:key="book-' . $book1->id . '"'));
        $this->assertSame(0, substr_count($response->getContent(), 'wire:key="recommended-book-' . $book1->id . '"'));
        $this->assertSame(1, substr_count($response->getContent(), 'wire:key="book-' . $book2->id . '"'));
        $this->assertSame(0, substr_count($response->getContent(), 'wire:key="recommended-book-' . $book2->id . '"'));
    }
}
