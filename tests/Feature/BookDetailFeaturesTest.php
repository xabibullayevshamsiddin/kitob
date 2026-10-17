<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\Models\UserSavedBook;
use App\Models\Discussion;
use App\Http\Livewire\Book\BookDiscussionChat;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookDetailFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private function makeBookFixture(): array
    {
        $user = User::create([
            'name' => 'Test Kitobxon',
            'username' => 'kitobxon_' . uniqid(),
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('password'),
        ]);

        $book = Book::create([
            'title' => 'Alkimyogar',
            'slug' => 'alkimyogar-' . uniqid(),
            'author' => 'Paulo Koelo',
            'description' => 'Falsafiy roman',
            'genre' => 'Badiiy',
            'week_number' => 2,
            'is_active' => true,
        ]);

        return [$user, $book];
    }

    public function test_user_can_toggle_keyinroq_oqiyman_saved_book(): void
    {
        [$user, $book] = $this->makeBookFixture();

        // 1. Toggle on (Save)
        $res = $this->actingAs($user)
            ->postJson(route('books.toggle-save', $book));

        $res->assertOk()
            ->assertJsonPath('is_saved', true)
            ->assertJsonPath('total_saved', 1);

        $this->assertDatabaseHas('user_saved_books', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->assertTrue($user->isBookSaved($book));

        // 2. Toggle off (Unsave)
        $res2 = $this->actingAs($user)
            ->postJson(route('books.toggle-save', $book));

        $res2->assertOk()
            ->assertJsonPath('is_saved', false)
            ->assertJsonPath('total_saved', 0);

        $this->assertDatabaseMissing('user_saved_books', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_user_can_post_discussion_on_book_page(): void
    {
        [$user, $book] = $this->makeBookFixture();

        $this->actingAs($user)
            ->post(route('books.discussions.store', $book), [
                'title' => 'Ajoyib asar',
                'body' => 'Bu kitob qalbimdan chuqur joy oldi.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('discussions', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'title' => 'Ajoyib asar',
        ]);
    }

    public function test_user_can_post_comment_and_rating_on_book(): void
    {
        [$user, $book] = $this->makeBookFixture();

        $res = $this->actingAs($user)
            ->post(route('books.comments.store', $book), [
                'rating'  => 5,
                'comment' => 'Bu juda ajoyib va ibratli kitob ekan!',
            ]);

        $res->assertRedirect();

        $this->assertDatabaseHas('book_comments', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating'  => 5,
            'comment' => 'Bu juda ajoyib va ibratli kitob ekan!',
        ]);

        $this->assertEquals(5.0, $book->averageRating());
        $this->assertEquals(1, $book->ratingsCount());
    }

    public function test_user_can_reply_to_comment(): void
    {
        [$user, $book] = $this->makeBookFixture();

        $parentComment = \App\Models\BookComment::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating'  => 4,
            'comment' => 'Asosiy qahramonning qarorlari juda qiziq.',
        ]);

        $res = $this->actingAs($user)
            ->post(route('books.comments.store', $book), [
                'parent_id' => $parentComment->id,
                'comment'   => 'Men ham fikringizga to\'liq qo\'shilaman.',
            ]);

        $res->assertRedirect();

        $this->assertDatabaseHas('book_comments', [
            'book_id'   => $book->id,
            'user_id'   => $user->id,
            'parent_id' => $parentComment->id,
            'comment'   => 'Men ham fikringizga to\'liq qo\'shilaman.',
        ]);
    }

    public function test_user_can_toggle_like_on_comment(): void
    {
        [$user, $book] = $this->makeBookFixture();

        $comment = \App\Models\BookComment::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating'  => 5,
            'comment' => 'Ajoyib kitob!',
        ]);

        // Like
        $res = $this->actingAs($user)
            ->postJson(route('books.comments.like', $comment));

        $res->assertOk()
            ->assertJsonPath('liked', true)
            ->assertJsonPath('likes_count', 1);

        $this->assertEquals(1, $comment->fresh()->likes_count);

        // Unlike
        $res2 = $this->actingAs($user)
            ->postJson(route('books.comments.like', $comment));

        $res2->assertOk()
            ->assertJsonPath('liked', false)
            ->assertJsonPath('likes_count', 0);

        $this->assertEquals(0, $comment->fresh()->likes_count);
    }

    public function test_user_can_delete_own_comment(): void
    {
        [$user, $book] = $this->makeBookFixture();

        $comment = \App\Models\BookComment::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating'  => 5,
            'comment' => 'O\'chirilishi kerak bo\'lgan izoh.',
        ]);

        $res = $this->actingAs($user)
            ->delete(route('books.comments.destroy', $comment));

        $res->assertRedirect();

        $this->assertDatabaseMissing('book_comments', [
            'id' => $comment->id,
        ]);
    }

    public function test_livewire_book_discussion_chat_allows_readers_to_talk_and_comment(): void
    {
        [$user, $book] = $this->makeBookFixture();

        Livewire::actingAs($user)
            ->test(BookDiscussionChat::class, ['book' => $book])
            ->assertSee('Kitobxonlar suhbati va izohlari')
            ->set('message', '15-betdagi voqea juda qiziq bo\'ldi, kim o\'qidi?')
            ->set('selectedPage', 15)
            ->call('sendMessage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('book_comments', [
            'book_id'     => $book->id,
            'user_id'     => $user->id,
            'page_number' => 15,
            'comment'     => '15-betdagi voqea juda qiziq bo\'ldi, kim o\'qidi?',
        ]);
    }

    public function test_book_detail_renders_all_controls_and_shortcuts(): void
    {
        [$user, $book] = $this->makeBookFixture();

        $this->actingAs($user)
            ->get(route('books.show', $book->slug))
            ->assertOk()
            ->assertSee('Keyinroq o\'qiyman', false)
            ->assertSee('Tugmalar boshqaruvi')
            ->assertSee('Kitobxonlar suhbati va izohlari')
            ->assertSee('Izoh qoldirish');
    }
}
