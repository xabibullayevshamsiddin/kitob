<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookComment;
use App\Models\ShopItem;
use App\Models\User;
use App\Models\UserItem;
use App\Services\Shop\ShopService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SystemSecurityAndRefinementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Book $book1;
    protected Book $book2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name'              => 'Test User',
            'username'          => 'testuser',
            'email'             => 'test@kitobxon.uz',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'student',
        ]);

        $this->book1 = Book::create([
            'title'        => 'Kitob 1',
            'slug'         => 'kitob-1',
            'author'       => 'Muallif 1',
            'description'  => 'Tavsif 1',
            'genre'        => 'Badiiy',
            'week_number'  => 1,
            'is_active'    => true,
            'published_at' => now(),
        ]);

        $this->book2 = Book::create([
            'title'        => 'Kitob 2',
            'slug'         => 'kitob-2',
            'author'       => 'Muallif 2',
            'description'  => 'Tavsif 2',
            'genre'        => 'Ilmiy',
            'week_number'  => 2,
            'is_active'    => true,
            'published_at' => now(),
        ]);
    }

    /** @test */
    public function cross_book_comment_replies_are_rejected(): void
    {
        // 1. Kitob 2 ga tegishli izoh
        $commentOnBook2 = BookComment::create([
            'book_id' => $this->book2->id,
            'user_id' => $this->user->id,
            'comment' => 'Kitob 2 ga tegishli izoh',
        ]);

        // 2. Kitob 1 ga izoh yozishda Kitob 2 ning izohini parent_id qilib yuborish
        $response = $this->actingAs($this->user)
            ->post(route('books.comments.store', $this->book1), [
                'comment'   => 'Kitob 1 sahifasidan javob',
                'parent_id' => $commentOnBook2->id,
            ]);

        // Validatsiya xatolik berishi kerak (parent_id boshqa kitobga tegishli bo'lgani uchun)
        $response->assertSessionHasErrors('parent_id');
        $this->assertDatabaseMissing('book_comments', [
            'book_id'   => $this->book1->id,
            'parent_id' => $commentOnBook2->id,
        ]);
    }

    /** @test */
    public function same_book_comment_reply_is_allowed(): void
    {
        $commentOnBook1 = BookComment::create([
            'book_id' => $this->book1->id,
            'user_id' => $this->user->id,
            'comment' => 'Kitob 1 ga tegishli izoh',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('books.comments.store', $this->book1), [
                'comment'   => 'Kitob 1 izohiga to\'g\'ri javob',
                'parent_id' => $commentOnBook1->id,
            ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('book_comments', [
            'book_id'   => $this->book1->id,
            'parent_id' => $commentOnBook1->id,
            'comment'   => 'Kitob 1 izohiga to\'g\'ri javob',
        ]);
    }

    /** @test */
    public function book_discussion_chat_prevents_cross_book_reply(): void
    {
        $commentOnBook2 = BookComment::create([
            'book_id' => $this->book2->id,
            'user_id' => $this->user->id,
            'comment' => 'Kitob 2 izohi',
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(\App\Http\Livewire\Book\BookDiscussionChat::class, ['book' => $this->book1]);

        // Boshqa kitobning izohiga javob belgilash
        $component->call('setReplyTo', $commentOnBook2->id);

        // replyToId o'rnatilmagan (null) bo'lishi kerak
        $this->assertNull($component->get('replyToId'));

        // To'g'ridan-to'g'ri soxtalashtirilgan replyToId bilan yuborish
        $component->set('replyToId', $commentOnBook2->id);
        $component->set('message', 'Soxtalashtirilgan javob');
        $component->call('sendMessage');

        // Bazada yaratilgan izoh parent_id = null bo'lishi kerak
        $this->assertDatabaseHas('book_comments', [
            'book_id'   => $this->book1->id,
            'comment'   => 'Soxtalashtirilgan javob',
            'parent_id' => null,
        ]);
    }

    /** @test */
    public function consume_ai_question_respects_expiry_and_decrements_atomically(): void
    {
        /** @var ShopService $shopService */
        $shopService = app(ShopService::class);

        $aiItem = ShopItem::create([
            'key'           => 'ai_questions_pack',
            'category'      => 'perk',
            'name'          => ['uz' => 'AI Savollar', 'ru' => 'AI Вопросы', 'en' => 'AI Questions'],
            'price_coins'   => 10,
            'rarity'        => 'common',
            'is_consumable' => true,
            'max_quantity'  => 50,
            'is_active'     => true,
        ]);

        // 1. Muddati tugagan chipta rad etilishi kerak
        $expiredItem = UserItem::create([
            'user_id'      => $this->user->id,
            'shop_item_id' => $aiItem->id,
            'quantity'     => 5,
            'acquired_at'  => now()->subDays(2),
            'expires_at'   => now()->subDay(),
        ]);

        $this->assertFalse($shopService->consumeAiQuestion($this->user));
        $this->assertDatabaseMissing('user_items', ['id' => $expiredItem->id]);

        // 2. Amal qiluvchi chipta to'g'ri kamayishi kerak
        $validItem = UserItem::create([
            'user_id'      => $this->user->id,
            'shop_item_id' => $aiItem->id,
            'quantity'     => 2,
            'acquired_at'  => now(),
            'expires_at'   => now()->addDays(7),
        ]);

        $this->assertTrue($shopService->consumeAiQuestion($this->user));
        $this->assertEquals(1, $validItem->fresh()->quantity);

        $this->assertTrue($shopService->consumeAiQuestion($this->user));
        $this->assertNull($validItem->fresh());
    }

    /** @test */
    public function consume_quiz_retry_ticket_respects_expiry_and_decrements_atomically(): void
    {
        /** @var ShopService $shopService */
        $shopService = app(ShopService::class);

        $quizTicket = ShopItem::create([
            'key'           => 'quiz_retry_ticket',
            'category'      => 'perk',
            'name'          => ['uz' => 'Qayta topshirish bileti', 'ru' => 'Билет', 'en' => 'Retry Ticket'],
            'price_coins'   => 20,
            'rarity'        => 'rare',
            'is_consumable' => true,
            'max_quantity'  => 10,
            'is_active'     => true,
        ]);

        // Muddati tugagan chipta sarflanmaydi
        UserItem::create([
            'user_id'      => $this->user->id,
            'shop_item_id' => $quizTicket->id,
            'quantity'     => 1,
            'acquired_at'  => now()->subDays(2),
            'expires_at'   => now()->subHour(),
        ]);

        $this->assertFalse($shopService->consumeQuizRetryTicket($this->user));

        // Faol chipta muvaffaqiyatli sarflanadi
        $activeTicket = UserItem::create([
            'user_id'      => $this->user->id,
            'shop_item_id' => $quizTicket->id,
            'quantity'     => 1,
            'acquired_at'  => now(),
            'expires_at'   => now()->addHour(),
        ]);

        $this->assertTrue($shopService->consumeQuizRetryTicket($this->user));
        $this->assertNull($activeTicket->fresh());
    }

    /** @test */
    public function book_detail_page_loads_without_redundant_discussions(): void
    {
        $response = $this->actingAs($this->user)->get(route('books.show', $this->book1->slug));

        $response->assertStatus(200);
        $response->assertViewIs('pages.book-detail');
        $response->assertViewMissing('discussions');
    }
}
