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
}
