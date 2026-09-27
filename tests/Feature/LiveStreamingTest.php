<?php

namespace Tests\Feature;

use App\Http\Livewire\Live\LiveDetail;
use App\Models\LiveEvent;
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
}
