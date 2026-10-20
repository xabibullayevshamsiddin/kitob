<?php

namespace Tests\Feature\Shop;

use App\Http\Livewire\Live\LiveChatPanel;
use App\Http\Livewire\Live\LiveIndex;
use App\Models\LiveEvent;
use App\Models\LiveQuestion;
use App\Models\ShopItem;
use App\Models\User;
use App\Models\UserItem;
use App\Services\Shop\Exceptions\ShopException;
use App\Services\Shop\ShopService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivePriorityQuestionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private LiveEvent $event;
    private ShopItem $ticketItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'coin_balance' => 100,
        ]);
        \App\Models\UserProfile::create(['user_id' => $this->user->id]);

        $this->event = LiveEvent::create([
            'title'           => 'Jonli muloqot efiri',
            'host_user_id'    => User::factory()->create()->id,
            'permission_mode' => 'open_chat',
            'status'          => LiveEvent::STATUS_LIVE,
            'scheduled_at'    => now(),
            'started_at'      => now(),
        ]);

        $this->ticketItem = ShopItem::create([
            'key'         => 'live_priority_question',
            'name'        => 'Jonli efirda ustuvor savol',
            'category'    => 'perk',
            'slot'        => null,
            'price_coins' => 25,
            'is_active'   => true,
            'payload'     => ['effect' => 'live_priority'],
        ]);
    }

    /**
     * Chiptasiz foydalanuvchi ustuvor savol yubora olmaydi (oddiy savol sifatida ham o'tmaydi).
     */
    public function test_user_without_ticket_cannot_submit_priority_question(): void
    {
        $shopService = app(ShopService::class);

        $this->expectException(ShopException::class);
        $this->expectExceptionMessage("Sizda ustuvor savol chiptasi mavjud emas");

        $shopService->createLiveQuestion($this->user, $this->event->id, 'Mening savolim', true);

        $this->assertDatabaseMissing('live_questions', [
            'live_event_id' => $this->event->id,
            'user_id'       => $this->user->id,
        ]);
    }

    /**
     * Chipta faqat savol muvaffaqiyatli yaratilgandagina sarflanadi (atomik).
     */
    public function test_ticket_is_only_consumed_on_successful_creation(): void
    {
        config(['shop.max_priority_questions_per_live' => 5]);
        $shopService = app(ShopService::class);

        // Foydalanuvchiga 2 ta chipta beramiz
        UserItem::create([
            'user_id'      => $this->user->id,
            'shop_item_id' => $this->ticketItem->id,
            'quantity'     => 2,
            'acquired_at'  => now(),
        ]);

        $this->assertEquals(2, $shopService->getLivePriorityTicketCount($this->user));

        // 1-savol muvaffaqiyatli yaratiladi
        $q = $shopService->createLiveQuestion($this->user, $this->event->id, 'Birinchi ustuvor savol', true);

        $this->assertTrue($q->is_priority);
        $this->assertEquals(1, $shopService->getLivePriorityTicketCount($this->user));

        // 2-savol muvaffaqiyatli yaratiladi (oxirgi chipta ketadi)
        $q2 = $shopService->createLiveQuestion($this->user, $this->event->id, 'Ikkinchi ustuvor savol', true);

        $this->assertTrue($q2->is_priority);
        $this->assertEquals(0, $shopService->getLivePriorityTicketCount($this->user));

        // 3-savol urinishi chipta yetmagani sababli bekor qilinadi
        try {
            $shopService->createLiveQuestion($this->user, $this->event->id, 'Uchinchi savol', true);
            $this->fail('Chiptasiz ustuvor savol berilmasligi kerak edi');
        } catch (ShopException $e) {
            $this->assertStringContainsString("mavjud emas", $e->getMessage());
        }

        // DB da jami 2 ta savol bo'lishi kerak
        $this->assertEquals(2, LiveQuestion::where('live_event_id', $this->event->id)->count());
    }

    /**
     * Savollar navbatida ustuvor savollar yuqorida, keyin likes_count, keyin vaqt bo'yicha saralanadi.
     */
    public function test_priority_queue_order(): void
    {
        Carbon::setTestNow('2026-10-20 12:00:00');
        $q1 = LiveQuestion::create([
            'live_event_id' => $this->event->id,
            'user_id'       => $this->user->id,
            'question'      => 'Oddiy savol kam like',
            'is_priority'   => false,
            'likes_count'   => 2,
        ]);

        Carbon::setTestNow('2026-10-20 12:01:00');
        $q2 = LiveQuestion::create([
            'live_event_id' => $this->event->id,
            'user_id'       => $this->user->id,
            'question'      => 'Oddiy savol ko\'p like',
            'is_priority'   => false,
            'likes_count'   => 15,
        ]);

        Carbon::setTestNow('2026-10-20 12:02:00');
        $q3 = LiveQuestion::create([
            'live_event_id' => $this->event->id,
            'user_id'       => $this->user->id,
            'question'      => 'Ustuvor savol 0 like',
            'is_priority'   => true,
            'likes_count'   => 0,
        ]);

        Carbon::setTestNow('2026-10-20 12:03:00');
        $q4 = LiveQuestion::create([
            'live_event_id' => $this->event->id,
            'user_id'       => $this->user->id,
            'question'      => 'Ustuvor savol 5 like',
            'is_priority'   => true,
            'likes_count'   => 5,
        ]);

        $orderedIds = LiveQuestion::where('live_event_id', $this->event->id)
            ->inPriorityOrder()
            ->pluck('id')
            ->all();

        // Kutilgan tartib:
        // 1. Ustuvor 5 like ($q4)
        // 2. Ustuvor 0 like ($q3)
        // 3. Oddiy 15 like ($q2)
        // 4. Oddiy 2 like ($q1)
        $this->assertEquals([$q4->id, $q3->id, $q2->id, $q1->id], $orderedIds);
    }

    /**
     * Bitta efirda beriladigan ustuvor savollar limiti tekshiriladi (standart 2).
     */
    public function test_per_live_limit_is_enforced(): void
    {
        config(['shop.max_priority_questions_per_live' => 2]);

        $shopService = app(ShopService::class);

        // 10 ta chipta beramiz
        UserItem::create([
            'user_id'      => $this->user->id,
            'shop_item_id' => $this->ticketItem->id,
            'quantity'     => 10,
            'acquired_at'  => now(),
        ]);

        // 1-savol muvaffaqiyatli
        $shopService->createLiveQuestion($this->user, $this->event->id, '1-ustuvor savol', true);
        // 2-savol muvaffaqiyatli
        $shopService->createLiveQuestion($this->user, $this->event->id, '2-ustuvor savol', true);

        // 3-savol limitdan oshgani uchun ShopException tashlashi kerak
        $this->expectException(ShopException::class);
        $this->expectExceptionMessage("ko'pi bilan 2 ta ustuvor savol");

        $shopService->createLiveQuestion($this->user, $this->event->id, '3-ustuvor savol', true);
    }

    /**
     * LiveChatPanel orqali ustuvor savol yuborish.
     */
    public function test_live_chat_panel_submits_priority_question(): void
    {
        $this->actingAs($this->user);

        UserItem::create([
            'user_id'      => $this->user->id,
            'shop_item_id' => $this->ticketItem->id,
            'quantity'     => 1,
            'acquired_at'  => now(),
        ]);

        Livewire::test(LiveChatPanel::class, ['event' => $this->event, 'isHost' => false])
            ->set('isPriority', true)
            ->set('message', 'Chatdagi ustuvor savol')
            ->call('sendMessage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('live_questions', [
            'live_event_id' => $this->event->id,
            'user_id'       => $this->user->id,
            'question'      => 'Chatdagi ustuvor savol',
            'is_priority'   => true,
        ]);

        // Chipta sarflangan bo'lishi kerak
        $this->assertDatabaseMissing('user_items', [
            'user_id'      => $this->user->id,
            'shop_item_id' => $this->ticketItem->id,
        ]);
    }
}
