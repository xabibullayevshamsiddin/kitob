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

        if ($validated['type'] === 'viewer-count') {
            $decodedPayload = is_array($validated['payload'])
                ? $validated['payload']
                : json_decode($validated['payload'], true);
            if (isset($decodedPayload['count'])) {
                \Illuminate\Support\Facades\Cache::put("live_event_{$event->id}_viewers", (int) $decodedPayload['count'], 30);
            }
        }

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

        // 'host:*' ga yuborilgan signallar barcha host peer'lariga yetib boradi
        $isHostPeer = str_starts_with($peerId, 'host_');

        $signals = LiveSignal::where('live_event_id', $event->id)
            ->where('id', '>', $sinceId)
            ->where('sender_id', '!=', $peerId)
            // Faqat yangi signallar (60 soniya) — eski yozuvlar tirbandligi offer kechikishiga sabab bo'lmasin
            ->where('created_at', '>', now()->subSeconds(60))
            ->where(function ($q) use ($peerId, $isHostPeer) {
                $q->where('receiver_id', $peerId)
                  ->when($isHostPeer, fn ($qq) => $qq->orWhere('receiver_id', 'host:*')->orWhere('receiver_id', 'host'))
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
