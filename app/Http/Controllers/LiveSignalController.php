<?php

namespace App\Http\Controllers;

use App\Models\LiveEvent;
use App\Models\LiveSignal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveSignalController extends Controller
{
    /** Faqat broadcaster yuboradigan signal turlari */
    private const HOST_ONLY_TYPES = ['offer', 'stream-status', 'viewer-count'];

    /**
     * Send WebRTC signal (join, offer, answer, ice-candidate, stream-status)
     */
    public function send(Request $request, LiveEvent $event): JsonResponse
    {
        // 5 soatlik maksimal vaqt tekshiruvi — 5 soat to'lishi bilan darhol yakunlash
        if ($event->status === LiveEvent::STATUS_LIVE && $event->hasExceededMaxDuration()) {
            $event->endAndCleanup('Maksimal efir vaqti (5 soat) tugadi.');
            return response()->json([
                'error'        => 'Jonli efir 5 soatlik maksimal vaqtga yetdi va avtomatik yakunlandi.',
                'stream_ended' => true,
            ], 410);
        }

        if ($denied = $this->denyAccess($request, $event)) {
            return $denied;
        }

        $validated = $request->validate([
            'sender_id'   => 'required|string|max:80',
            'receiver_id' => 'required|string|max:80',
            'type'        => 'required|string|max:40',
            'payload'     => 'present',
        ]);

        // Host nomidan signal (offer, stream-status, viewer-count) faqat haqiqiy host yubora oladi —
        // aks holda istalgan tomoshabin o'z videosini boshqalarga "host" sifatida uzatishi mumkin edi.
        $claimsHost = $this->isHostPeerId($validated['sender_id'])
            || in_array($validated['type'], self::HOST_ONLY_TYPES, true);

        if ($claimsHost && !$event->isBroadcaster($request->user())) {
            return response()->json(['error' => 'Bu signalni faqat efir egasi yubora oladi.'], 403);
        }

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
                $count = max(0, (int) $decodedPayload['count']);
                \Illuminate\Support\Facades\Cache::put("live_event_{$event->id}_viewers", $count, 30);
                if (\Illuminate\Support\Facades\Schema::hasColumn('live_events', 'viewers_count')) {
                    $event->updateQuietly(['viewers_count' => $count]);
                }
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
        // 5 soatlik maksimal vaqt tekshiruvi — 5 soat to'lishi bilan darhol yakunlash
        if ($event->status === LiveEvent::STATUS_LIVE && $event->hasExceededMaxDuration()) {
            $event->endAndCleanup('Maksimal efir vaqti (5 soat) tugadi.');
            return response()->json([
                'signals' => [
                    [
                        'id'          => 999999999,
                        'sender_id'   => 'server_system',
                        'receiver_id' => 'all',
                        'type'        => 'stream-status',
                        'payload'     => [
                            'active' => false,
                            'ended'  => true,
                            'reason' => 'Jonli efir 5 soatlik maksimal vaqtga yetdi va avtomatik yakunlandi.'
                        ],
                    ]
                ],
                'stream_ended' => true,
                'server_time'  => now()->timestamp,
            ]);
        }

        if ($denied = $this->denyAccess($request, $event)) {
            return $denied;
        }

        $peerId = (string) $request->query('peer_id', '');
        $sinceId = (int) $request->query('since_id', 0);

        if (empty($peerId)) {
            return response()->json(['signals' => [], 'server_time' => now()->timestamp]);
        }

        // 'host:*' ga yuborilgan signallar barcha host peer'lariga yetib boradi —
        // tomoshabinlarning join/answer (SDP, IP manzillar) signallarini faqat haqiqiy host o'qiy oladi
        $isHostPeer = $this->isHostPeerId($peerId);
        if ($isHostPeer && !$event->isBroadcaster($request->user())) {
            return response()->json(['error' => 'Bu signallarni faqat efir egasi o\'qiy oladi.'], 403);
        }

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

    /** Guruh efiriga faqat a'zolar, admin va host ulanadi. */
    protected function denyAccess(Request $request, LiveEvent $event): ?JsonResponse
    {
        if ($event->isViewableBy($request->user())) {
            return null;
        }

        return $request->user()
            ? response()->json(['error' => 'Bu efir faqat guruh a\'zolari uchun.'], 403)
            : response()->json(['error' => 'Guruh efiriga ulanish uchun avval tizimga kiring.'], 401);
    }

    /** Frontend host peer'lari "host", "host:*", "host_{userId}_{rand}", "server_{userId}" va h.k. */
    protected function isHostPeerId(string $peerId): bool
    {
        $normalized = strtolower(trim($peerId));

        return $normalized === 'host'
            || $normalized === 'host:*'
            || str_starts_with($normalized, 'host:')
            || str_starts_with($normalized, 'host_')
            || str_starts_with($normalized, 'host-')
            || str_starts_with($normalized, 'server_')
            || str_starts_with($normalized, 'server:')
            || str_starts_with($normalized, 'server-');
    }
}
