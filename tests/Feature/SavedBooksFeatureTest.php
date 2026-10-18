<?php

namespace Tests\Feature;

use App\Http\Livewire\Catalog\CatalogPage;
use App\Http\Livewire\Profile\ProfilePage;
use App\Models\Book;
use App\Models\User;
use App\Models\UserSavedBook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SavedBooksFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function makeUserAndBooks(): array
    {
        $user = User::create([
            'name' => 'Alisher Navoiy',
            'username' => 'alisher_' . uniqid(),
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('password'),
        ]);

        $book1 = Book::create([
            'title' => 'Xamsa',
            'slug' => 'xamsa-' . uniqid(),
            'author' => 'Alisher Navoiy',
            'description' => 'Dostonlar to\'plami',
            'genre' => 'Mumtoz',
            'week_number' => 1,
            'is_active' => true,
        ]);

        $book2 = Book::create([
            'title' => 'Boburnoma',
            'slug' => 'boburnoma-' . uniqid(),
            'author' => 'Zahiriddin Muhammad Bobur',
            'description' => 'Tarixiy memuar',
            'genre' => 'Tarixiy',
            'week_number' => 2,
            'is_active' => true,
        ]);

        return [$user, $book1, $book2];
    }

    public function test_profile_page_displays_saved_books_tab_and_list(): void
    {
        [$user, $book1, $book2] = $this->makeUserAndBooks();

        // Save book1
        UserSavedBook::create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
        ]);

        Livewire::actingAs($user)
            ->test(ProfilePage::class, ['username' => $user->username])
            ->assertSee('Saqlanganlar')
            ->call('setTab', 'saved')
            ->assertSet('activeTab', 'saved')
            ->assertSee($book1->title)
            ->assertDontSee($book2->title);
    }

    public function test_profile_owner_can_unsave_book(): void
    {
        [$user, $book1] = $this->makeUserAndBooks();

        UserSavedBook::create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
        ]);

        $this->assertDatabaseHas('user_saved_books', [
            'user_id' => $user->id,
            'book_id' => $book1->id,
        ]);

        Livewire::actingAs($user)
            ->test(ProfilePage::class, ['username' => $user->username])
            ->call('unsaveBook', $book1->id)
            ->assertSet('activeTab', 'overview');

        $this->assertDatabaseMissing('user_saved_books', [
            'user_id' => $user->id,
            'book_id' => $book1->id,
        ]);
    }

    public function test_catalog_page_filters_by_saved_books(): void
    {
        [$user, $book1, $book2] = $this->makeUserAndBooks();

        // User saves only book1
        UserSavedBook::create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
        ]);

        Livewire::actingAs($user)
            ->test(CatalogPage::class)
            ->assertSee('Saqlanganlar')
            ->set('readingStatus', 'saved')
            ->assertSee($book1->title)
            ->assertDontSee($book2->title);
    }

    public function test_catalog_page_shows_saved_count_and_empty_state_when_none_saved(): void
    {
        [$user, $book1, $book2] = $this->makeUserAndBooks();

        Livewire::actingAs($user)
            ->test(CatalogPage::class)
            ->set('readingStatus', 'saved')
            ->assertSee('Hozircha saqlangan kitoblar yo‘q')
            ->assertDontSee($book1->title)
            ->assertDontSee($book2->title);
    }
}
