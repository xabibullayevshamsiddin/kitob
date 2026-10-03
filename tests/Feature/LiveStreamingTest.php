<?php

namespace Tests\Feature;

use App\Http\Livewire\Live\LiveChatPanel;
use App\Http\Livewire\Live\LiveDetail;
use App\Models\LiveEvent;
use App\Models\LiveQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

class LiveStreamingTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_stream_signal_can_be_sent_and_polled(): void
    {
        $unique = uniqid();
        $host = User::create([
            'email'    => "stream_host_{$unique}@kitobxon.uz",
            'name'     => 'Host Teacher',
            'username' => 'stream_host_' . $unique,
            'password' => bcrypt('password'),
        ]);
        $event = LiveEvent::create([
            'title'           => 'Test WebRTC Live Room',
            'host_user_id'    => $host->id,
            'status'          => LiveEvent::STATUS_LIVE,
            'permission_mode' => 'both',
            'scheduled_at'    => now(),
            'started_at'      => now(),
        ]);

        // Host sends offer to viewer_123
        $sendResponse = $this->postJson(route('live.signal.send', $event), [
            'sender_id'   => 'host',
            'receiver_id' => 'viewer_123',
            'type'        => 'offer',
            'payload'     => ['sdp' => 'test-sdp-offer', 'type' => 'offer'],
        ]);

        $sendResponse->assertOk();
        $sendResponse->assertJson(['ok' => true]);

        // Viewer polls signals
        $pollResponse = $this->getJson(route('live.signal.poll', [
            'event'   => $event,
            'peer_id' => 'viewer_123',
        ]));

        $pollResponse->assertOk();
        $pollResponse->assertJsonStructure([
            'signals' => [
                '*' => ['id', 'sender_id', 'receiver_id', 'type', 'payload'],
            ],
            'server_time',
        ]);

        $signals = $pollResponse->json('signals');
        $this->assertCount(1, $signals);
        $this->assertEquals('host', $signals[0]['sender_id']);
        $this->assertEquals('viewer_123', $signals[0]['receiver_id']);
        $this->assertEquals('offer', $signals[0]['type']);
    }

    public function test_live_stream_detail_page_loads_with_started_at(): void
    {
        $unique = uniqid();
        $host = User::create([
            'email'    => "stream_host2_{$unique}@kitobxon.uz",
            'name'     => 'Host Teacher 2',
            'username' => 'stream_host2_' . $unique,
            'password' => bcrypt('password'),
        ]);
        $event = LiveEvent::create([
            'title'           => 'Fizika Jonli Efiri',
            'host_user_id'    => $host->id,
            'status'          => LiveEvent::STATUS_LIVE,
            'permission_mode' => 'both',
            'scheduled_at'    => now(),
            'started_at'      => now(),
        ]);

        $response = $this->get(route('live.show', $event));
        $response->assertOk();
        $response->assertSee('liveVideoPlayer');
        $response->assertSee('Fizika Jonli Efiri');
    }

    public function test_permission_mode_settings_visible_only_to_host(): void
    {
        $unique = uniqid();
        $host = User::create([
            'email'    => "perm_host_{$unique}@kitobxon.uz",
            'name'     => 'Perm Host',
            'username' => 'perm_host_' . $unique,
            'password' => bcrypt('password'),
        ]);
        $viewer = User::create([
            'email'    => "perm_viewer_{$unique}@kitobxon.uz",
            'name'     => 'Perm Viewer',
            'username' => 'perm_viewer_' . $unique,
            'password' => bcrypt('password'),
        ]);
        $event = LiveEvent::create([
            'title'           => 'Ruxsat Rejimi Efiri',
            'host_user_id'    => $host->id,
            'status'          => LiveEvent::STATUS_LIVE,
            'permission_mode' => 'chat_only',
            'scheduled_at'    => now(),
            'started_at'      => now(),
        ]);

        // HOST: sozlama paneli va 4 ta tugma ko'rinadi
        $this->actingAs($host);
        Auth::setUser($host);
        $hostPage = $this->get(route('live.show', $event));
        $hostPage->assertOk();
        $hostPage->assertSee('Tashrif buyuruvchilar huquqi');
        $hostPage->assertSee("updatePermissionMode('view_only')", false);

        // VIEWER: sozlama paneli umuman ko'rinmaydi
        $this->actingAs($viewer);
        Auth::setUser($viewer);
        $viewerPage = $this->get(route('live.show', $event));
        $viewerPage->assertOk();
        $viewerPage->assertDontSee('Tashrif buyuruvchilar huquqi');
        // Lekin joriy rejim haqida xabar va chat kiritish maydoni ko'rinadi
        $viewerPage->assertSee('Faqat yozma chat faol');
        $viewerPage->assertSee('Fikringiz yoki savolingiz...');
    }

    public function test_only_host_can_change_permission_mode(): void
    {
        $unique = uniqid();
        $host = User::create([
            'email'    => "mode_host_{$unique}@kitobxon.uz",
            'name'     => 'Mode Host',
            'username' => 'mode_host_' . $unique,
            'password' => bcrypt('password'),
        ]);
        $viewer = User::create([
            'email'    => "mode_viewer_{$unique}@kitobxon.uz",
            'name'     => 'Mode Viewer',
            'username' => 'mode_viewer_' . $unique,
            'password' => bcrypt('password'),
        ]);
        $event = LiveEvent::create([
            'title'           => 'Mode Change Efiri',
            'host_user_id'    => $host->id,
            'status'          => LiveEvent::STATUS_LIVE,
            'permission_mode' => 'both',
            'scheduled_at'    => now(),
            'started_at'      => now(),
        ]);

        // Viewer (hatto admin/teacher ham bo'lmasa) rejimni o'zgartira olmaydi
        $this->actingAs($viewer);
        Auth::setUser($viewer);
        Livewire::test(LiveDetail::class, ['event' => $event])
            ->call('updatePermissionMode', 'view_only');
        $this->assertEquals('both', $event->fresh()->permission_mode);

        // Host rejimni o'zgartira oladi
        $this->actingAs($host);
        Auth::setUser($host);
        Livewire::test(LiveDetail::class, ['event' => $event])
            ->call('updatePermissionMode', 'view_only');
        $this->assertEquals('view_only', $event->fresh()->permission_mode);
    }

    private function makeUser(string $prefix): User
    {
        $unique = uniqid();

        return User::create([
            'email'    => "{$prefix}_{$unique}@kitobxon.uz",
            'name'     => ucfirst($prefix) . ' User',
            'username' => $prefix . '_' . $unique,
            'password' => bcrypt('password'),
        ]);
    }

    private function makeLiveEvent(User $host, string $title): LiveEvent
    {
        return LiveEvent::create([
            'title'           => $title,
            'host_user_id'    => $host->id,
            'status'          => LiveEvent::STATUS_LIVE,
            'permission_mode' => 'both',
            'scheduled_at'    => now(),
            'started_at'      => now(),
        ]);
    }

    public function test_like_toggle_increments_and_decrements(): void
    {
        $host    = $this->makeUser('likehost');
        $liker   = $this->makeUser('liker');
        $event   = $this->makeLiveEvent($host, 'Like Toggle Efiri');
        $message = LiveQuestion::create([
            'live_event_id' => $event->id,
            'user_id'       => $host->id,
            'question'      => 'Salom hammaga!',
        ]);

        $this->actingAs($liker);
        Auth::setUser($liker);

        $component = Livewire::test(LiveChatPanel::class, ['event' => $event, 'isHost' => false]);

        // 1-bosish: like +1
        $component->call('toggleLike', $message->id);
        $this->assertEquals(1, $message->fresh()->likes_count);
        $this->assertContains($message->id, $component->instance()->myLikedIds);

        // 2-bosish: like olib tashlanadi (toggle)
        $component->call('toggleLike', $message->id);
        $this->assertEquals(0, $message->fresh()->likes_count);
        $this->assertNotContains($message->id, $component->instance()->myLikedIds);
    }

    public function test_multiple_users_likes_accumulate_for_everyone(): void
    {
        $host  = $this->makeUser('likehost2');
        $userA = $this->makeUser('likea');
        $userB = $this->makeUser('likeb');
        $event = $this->makeLiveEvent($host, 'Umumiy Like Efiri');
        $message = LiveQuestion::create([
            'live_event_id' => $event->id,
            'user_id'       => $host->id,
            'question'      => 'Bu xabarga hamma like bosadi',
        ]);

        // A foydalanuvchi like bosadi
        $this->actingAs($userA);
        Auth::setUser($userA);
        $compA = Livewire::test(LiveChatPanel::class, ['event' => $event, 'isHost' => false]);
        $compA->call('toggleLike', $message->id);

        // B foydalanuvchi ham like bosadi — hisob DB'da kopyiladi (2 bo'ladi)
        $this->actingAs($userB);
        Auth::setUser($userB);
        $compB = Livewire::test(LiveChatPanel::class, ['event' => $event, 'isHost' => false]);
        $compB->call('toggleLike', $message->id);

        $this->assertEquals(2, $message->fresh()->likes_count);

        // B ning sahifasida (render) yangi hisob ko'rinadi — obnova shart emas (wire:poll)
        $compB->call('$refresh');
        $compB->assertSee('2', false);

        // A sahifasi ham keyingi poll'da 2 ni ko'radi (o'zi + boshqa odam)
        $this->actingAs($userA);
        Auth::setUser($userA);
        $compA2 = Livewire::test(LiveChatPanel::class, ['event' => $event, 'isHost' => false]);
        $compA2->assertSee('2', false);
    }

    public function test_guest_cannot_like_but_sees_counts(): void
    {
        $host  = $this->makeUser('likehost3');
        $event = $this->makeLiveEvent($host, 'Guest Like Efiri');
        $message = LiveQuestion::create([
            'live_event_id' => $event->id,
            'user_id'       => $host->id,
            'question'      => 'Mehmon like bosa olmaydi',
            'likes_count'   => 5,
        ]);

        // Guest: toggleLike hech narsa o'zgartirmaydi
        Livewire::test(LiveChatPanel::class, ['event' => $event, 'isHost' => false])
            ->call('toggleLike', $message->id);
        $this->assertEquals(5, $message->fresh()->likes_count);

        // Lekin mehmon like sonini ko'radi
        Livewire::test(LiveChatPanel::class, ['event' => $event, 'isHost' => false])
            ->assertSee('5', false);
    }

    public function test_total_likes_shown_to_host(): void
    {
        $host  = $this->makeUser('likehost4');
        $fan   = $this->makeUser('likefan');
        $event = $this->makeLiveEvent($host, 'Host Total Like Efiri');
        $m1 = LiveQuestion::create([
            'live_event_id' => $event->id,
            'user_id'       => $fan->id,
            'question'      => 'Birinchi xabar',
            'likes_count'   => 3,
        ]);
        $m2 = LiveQuestion::create([
            'live_event_id' => $event->id,
            'user_id'       => $host->id,
            'question'      => 'Ikkinchi xabar',
            'likes_count'   => 4,
        ]);

        // HOST (efir boshlagan odam) ham jami like sonini ko'radi: 3 + 4 = 7
        $this->actingAs($host);
        Auth::setUser($host);
        Livewire::test(LiveChatPanel::class, ['event' => $event, 'isHost' => true])
            ->assertSee('7', false);

        $this->assertEquals(7, $m1->likes_count + $m2->likes_count);
    }

    public function test_stream_likes_can_be_sent_and_broadcast_via_signal(): void
    {
        $host = $this->makeUser('stream_like_host');
        $event = $this->makeLiveEvent($host, 'Stream Like Room');
        $this->assertEquals(0, $event->likes_count);

        $viewer = $this->makeUser('stream_like_viewer');
        $this->actingAs($viewer);

        // 1st like: increments to 1
        Livewire::test(LiveDetail::class, ['event' => $event])
            ->call('toggleStreamLike');

        $this->assertEquals(1, $event->fresh()->likes_count);
        $this->assertDatabaseHas('live_event_likes', [
            'live_event_id' => $event->id,
            'user_id'       => $viewer->id,
        ]);

        // Check LiveSignal record
        $signal = \App\Models\LiveSignal::where('live_event_id', $event->id)
            ->where('type', 'like')
            ->first();

        $this->assertNotNull($signal);
        $this->assertEquals('all', $signal->receiver_id);
        $payload = json_decode($signal->payload, true);
        $this->assertEquals(1, $payload['total']);

        // Poll signal as another viewer
        $pollResponse = $this->getJson(route('live.signal.poll', [
            'event'   => $event,
            'peer_id' => 'viewer_other_456',
        ]));

        $pollResponse->assertOk();
        $signals = $pollResponse->json('signals');
        $likeSignal = collect($signals)->firstWhere('type', 'like');
        $this->assertNotNull($likeSignal);
        $this->assertEquals(1, $likeSignal['payload']['total']);

        // 2nd like by same user: toggles off to 0
        Livewire::test(LiveDetail::class, ['event' => $event])
            ->call('toggleStreamLike');
        $this->assertEquals(0, $event->fresh()->likes_count);
        $this->assertDatabaseMissing('live_event_likes', [
            'live_event_id' => $event->id,
            'user_id'       => $viewer->id,
        ]);
    }

    public function test_viewer_count_signal_can_be_broadcast_and_polled(): void
    {
        $host = $this->makeUser('viewer_cnt_host');
        $event = $this->makeLiveEvent($host, 'Viewer Count Room');

        // Host broadcasts viewer-count signal to 'all'
        $sendResponse = $this->postJson(route('live.signal.send', $event), [
            'sender_id'   => 'host_123',
            'receiver_id' => 'all',
            'type'        => 'viewer-count',
            'payload'     => ['count' => 14],
        ]);
        $sendResponse->assertOk();

        // Viewer polls signals and receives viewer-count
        $pollResponse = $this->getJson(route('live.signal.poll', [
            'event'   => $event,
            'peer_id' => 'viewer_999',
        ]));

        $pollResponse->assertOk();
        $signals = $pollResponse->json('signals');
        $vcSignal = collect($signals)->firstWhere('type', 'viewer-count');
        $this->assertNotNull($vcSignal);
        $this->assertEquals(14, $vcSignal['payload']['count']);
    }

    public function test_live_index_displays_stream_likes_and_viewers_count(): void
    {
        $host = $this->makeUser('index_display_host');
        $event = $this->makeLiveEvent($host, 'Efir Kartasi Sinovi');
        $event->update([
            'status'      => LiveEvent::STATUS_LIVE,
            'likes_count' => 25,
        ]);

        // Broadcast viewer count
        $this->postJson(route('live.signal.send', $event), [
            'sender_id'   => 'host_card_test',
            'receiver_id' => 'all',
            'type'        => 'viewer-count',
            'payload'     => ['count' => 9],
        ]);

        $response = $this->get(route('live.index'));
        $response->assertOk();
        $response->assertSee('Efir Kartasi Sinovi');
        $response->assertSee('25'); // Stream likes count
        $response->assertSee('9');  // Live viewers count
    }
}
