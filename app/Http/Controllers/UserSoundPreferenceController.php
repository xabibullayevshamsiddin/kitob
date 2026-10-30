<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSoundPreferenceController extends Controller
{
    /**
     * Foydalanuvchining interfeys ovozlari sozlamasini (yoqish/o'chirish va balandlik) yangilash.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user() ?: Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'volume'  => 'required|integer|min:0|max:100',
        ]);

        $prefs = is_array($user->notification_prefs) ? $user->notification_prefs : [];
        $prefs['sound_enabled'] = (bool) $validated['enabled'];
        $prefs['sound_volume']  = (int) $validated['volume'];

        $user->update(['notification_prefs' => $prefs]);

        return response()->json([
            'success' => true,
            'enabled' => $prefs['sound_enabled'],
            'volume'  => $prefs['sound_volume'],
        ]);
    }
}
