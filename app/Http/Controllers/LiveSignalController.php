<?php

namespace App\Http\Controllers;

use App\Models\LiveEvent;
use App\Models\LiveSignal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveSignalController extends Controller
{
    /**
     * Send WebRTC signal (join, offer, answer, ice-candidate, stream-status)
     */
    public function send(Request $request, LiveEvent $event): JsonResponse
    {
        $validated = $request->validate([
            'sender_id'   => 'required|string|max:80',
            'receiver_id' => 'required|string|max:80',
            'type'        => 'required|string|max:40',
            'payload'     => 'present',
        ]);

        $payload = is_string($validated['payload'])
            ? $validated['payload']
            : json_encode($validated['payload']);

        $signal = LiveSignal::create([
            'live_event_id' => $event->id,
            'sender_id'     => $validated['sender_id'],
            'receiver_id'   => $validated['receiver_id'],
            'type'          => $validated['type'],
            'payload'       => $payload,
            'created_at'    => now(),
        ]);

        // Cleanup old signals older than 2 minutes to keep table lightweight
        if (random_int(1, 20) === 1) {
            LiveSignal::where('created_at', '<', now()->subMinutes(2))->delete();
        }

        return response()->json([
            'ok' => true,
            'id' => $signal->id,
        ]);
    }

    /**
     * Retrieve WebRTC signals directed to this peer or broadcast to 'all'
     */
    public function poll(Request $request, LiveEvent $event): JsonResponse
    {
        $peerId = (string) $request->query('peer_id', '');
        $sinceId = (int) $request->query('since_id', 0);

        if (empty($peerId)) {
            return response()->json(['signals' => [], 'server_time' => now()->timestamp]);
        }

        $signals = LiveSignal::where('live_event_id', $event->id)
            ->where('id', '>', $sinceId)
            ->where('sender_id', '!=', $peerId)
            ->where(function ($q) use ($peerId) {
                $q->where('receiver_id', $peerId)
                  ->orWhere('receiver_id', 'all');
            })
            ->orderBy('id', 'asc')
            ->limit(40)
            ->get();

        return response()->json([
            'signals' => $signals->map(function ($s) {
                $decoded = json_decode($s->payload, true);
                return [
                    'id'          => $s->id,
                    'sender_id'   => $s->sender_id,
                    'receiver_id' => $s->receiver_id,
                    'type'        => $s->type,
                    'payload'     => $decoded !== null ? $decoded : $s->payload,
                ];
            }),
            'server_time' => now()->timestamp,
        ]);
    }
}
