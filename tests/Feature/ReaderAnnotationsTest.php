<?php

namespace Tests\Feature;

use App\Http\Livewire\Profile\ProfilePage;
use App\Models\Book;
use App\Models\BookChapter;
use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReaderAnnotationsTest extends TestCase
{
    use RefreshDatabase;

    private function makeReaderFixture(): array
    {
        $user = User::create([
            'name' => 'Reader',
            'username' => 'reader_' . uniqid(),
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('password'),
        ]);
        $book = Book::create([
            'title' => 'Qaydli kitob',
            'slug' => 'qaydli-kitob-' . uniqid(),
            'author' => 'Muallif',
            'description' => 'Test kitobi',
            'genre' => 'Adabiyot',
            'week_number' => 1,
            'is_active' => true,
        ]);
        $chapter = BookChapter::create([
            'book_id' => $book->id,
            'chapter_number' => 1,
            'title' => 'Birinchi bob',
            'content' => 'Bob matni',
            'is_published' => true,
        ]);

        return [$user, $book, $chapter];
    }

    public function test_reader_can_save_a_private_bookmark_and_note(): void
    {
        [$user, $book, $chapter] = $this->makeReaderFixture();

        $this->actingAs($user)
            ->postJson(route('reader.bookmarks.store'), [
                'book_id' => $book->id,
                'chapter_id' => $chapter->id,
                'position' => 420,
            ])
            ->assertCreated()
            ->assertJsonPath('bookmark.position', 420);

        $this->actingAs($user)
            ->postJson(route('reader.notes.store'), [
                'book_id' => $book->id,
                'chapter_id' => $chapter->id,
                'position' => 420,
                'selected_text' => 'Muhim parcha',
                'note_text' => 'Bu fikrni keyin qayta ko‘raman.',
            ])
            ->assertCreated()
            ->assertJsonPath('note.position', 420)
            ->assertJsonPath('note.selected_text', 'Muhim parcha');

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'chapter_id' => $chapter->id,
            'position' => 420,
        ]);
        $this->assertDatabaseHas('user_notes', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'chapter_id' => $chapter->id,
            'position' => 420,
            'note_text' => 'Bu fikrni keyin qayta ko‘raman.',
        ]);

        $this->get(route('reader.show', ['book' => $book->id, 'chapter' => $chapter->id]))
            ->assertOk()
            ->assertSee('Xatcho‘p va qaydlar')
            ->assertSee('Muhim parcha')
            ->assertSee('Qayd qoldirilgan joy');
    }

    public function test_reader_cannot_attach_annotation_to_another_books_chapter_or_delete_another_users_bookmark(): void
    {
        [$owner, $book, $chapter] = $this->makeReaderFixture();
        $otherUser = User::create([
            'name' => 'Other Reader',
            'username' => 'other_' . uniqid(),
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('password'),
        ]);
        $otherBook = Book::create([
            'title' => 'Boshqa kitob',
            'slug' => 'boshqa-kitob-' . uniqid(),
            'author' => 'Muallif',
            'description' => 'Test kitobi',
            'genre' => 'Adabiyot',
            'week_number' => 2,
            'is_active' => true,
        ]);
        $foreignChapter = BookChapter::create([
            'book_id' => $otherBook->id,
            'chapter_number' => 1,
            'title' => 'Boshqa bob',
            'content' => 'Boshqa matn',
            'is_published' => true,
        ]);
        $bookmark = Bookmark::create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
            'chapter_id' => $chapter->id,
            'position' => 10,
        ]);

        $this->actingAs($otherUser)
            ->postJson(route('reader.bookmarks.store'), [
                'book_id' => $book->id,
                'chapter_id' => $foreignChapter->id,
                'position' => 5,
            ])
            ->assertNotFound();

        $this->deleteJson(route('reader.bookmarks.destroy', $bookmark))->assertNotFound();
        $this->assertDatabaseHas('bookmarks', ['id' => $bookmark->id]);
    }

    public function test_notes_remain_private_on_public_profiles(): void
    {
        [$owner, $book, $chapter] = $this->makeReaderFixture();
        $otherUser = User::create([
            'name' => 'Profile Visitor',
            'username' => 'visitor_' . uniqid(),
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('password'),
        ]);
        $owner->notes()->create([
            'book_id' => $book->id,
            'chapter_id' => $chapter->id,
            'selected_text' => 'Private passage',
            'note_text' => 'This note belongs only to the reader.',
        ]);

        $this->actingAs($otherUser);
        Livewire::test(ProfilePage::class, ['username' => $owner->username])
            ->call('setTab', 'notes')
            ->assertSet('activeTab', 'overview')
            ->assertDontSee('This note belongs only to the reader.')
            ->assertDontSee('Private passage');
    }
}
