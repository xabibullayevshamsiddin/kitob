<?php

namespace App\Http\Controllers;

use App\Models\BuddyRead;
use App\Models\BuddyReadMember;
use App\Models\BuddyReadVoiceSignal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuddyReadVoiceController extends Controller
{
    /**
     * Send WebRTC signal (offer, answer, candidate, join, leave)
     */
    public function sendSignal(Request $request, int $id): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $userId = Auth::id();
        $isMember = BuddyReadMember::where('buddy_read_id', $id)
            ->where('user_id', $userId)
            ->exists();

        if (!$isMember) {
            return response()->json(['error' => 'Not a member of this room'], 403);
        }

        $request->validate([
            'type'       => 'required|string|max:32',
            'payload'    => 'required|string',
            'to_user_id' => 'nullable|integer',
        ]);

        BuddyReadVoiceSignal::create([
            'buddy_read_id' => $id,
            'from_user_id'  => $userId,
            'to_user_id'    => $request->input('to_user_id'),
            'type'          => $request->input('type'),
            'payload'       => $request->input('payload'),
            'is_consumed'   => false,
        ]);

        // Cleanup old signals (older than 3 minutes)
        if (rand(1, 10) === 1) {
            BuddyReadVoiceSignal::where('created_at', '<', now()->subMinutes(3))->delete();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Poll WebRTC signals for the current user
     */
    public function pollSignals(Request $request, int $id): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $userId = Auth::id();

        // Get unconsumed signals directed to me, or broadcasted to everyone
        $signals = BuddyReadVoiceSignal::where('buddy_read_id', $id)
            ->where('from_user_id', '!=', $userId)
            ->where(function ($q) use ($userId) {
                $q->where('to_user_id', $userId)
                  ->orWhereNull('to_user_id');
            })
            ->where('is_consumed', false)
            ->where('created_at', '>=', now()->subMinutes(1))
            ->orderBy('id', 'asc')
            ->take(20)
            ->get();

        if ($signals->isNotEmpty()) {
            $ids = $signals->pluck('id')->all();
            BuddyReadVoiceSignal::whereIn('id', $ids)->update(['is_consumed' => true]);
        }

        // Active voice members list
        $activeMembers = BuddyReadMember::where('buddy_read_id', $id)
            ->where('voice_active', true)
            ->where('user_id', '!=', $userId)
            ->pluck('user_id')
            ->all();

        return response()->json([
            'signals'       => $signals,
            'activeMembers' => $activeMembers,
        ]);
    }
}
