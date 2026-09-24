<?php

namespace Tests\Feature;

use App\Models\LiveEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveStreamingTest extends TestCase
{

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
}
