<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Gamification\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminBalanceController extends Controller
{
    /**
     * Admin o'ziga yoki boshqa foydalanuvchiga tekinga tanga (coin) yoki ball (points) to'ldirib olishi.
     */
    public function topup(Request $request, PointsService $pointsService): JsonResponse|RedirectResponse
    {
        /** @var User|null $admin */
        $admin = Auth::user();
        if (!$admin || (!$admin->isAdmin() && !$admin->hasRole('admin'))) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Faqat administratorlar hisobni to\'ldirishi mumkin.'], 403);
            }
            abort(403, 'Faqat administratorlar hisobni to\'ldirishi mumkin.');
        }

        $validated = $request->validate([
            'target_user_id' => 'nullable|integer|exists:users,id',
            'coins'          => 'nullable|integer|min:0|max:10000000',
            'points'         => 'nullable|integer|min:0|max:10000000',
            'reason'         => 'nullable|string|max:255',
        ]);

        $targetUser = !empty($validated['target_user_id'])
            ? User::findOrFail($validated['target_user_id'])
            : $admin;

        $coins = (int) ($validated['coins'] ?? 0);
        $points = (int) ($validated['points'] ?? 0);
        $reason = trim($validated['reason'] ?? '') ?: ($targetUser->id === $admin->id
            ? "Admin o'z hisobini to'ldirdi (Tekin)"
            : "Admin tomonidan hisob to'ldirildi");

        if ($coins <= 0 && $points <= 0) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanga yoki ball miqdorini 0 dan katta qilib kiriting.',
                ], 422);
            }
            return back()->with('error', 'Tanga yoki ball miqdorini kiriting.');
        }

        if ($coins > 0) {
            $pointsService->awardCoins($targetUser, $coins, 'admin', $reason);
        }

        if ($points > 0) {
            $pointsService->awardPoints($targetUser, $points, 'admin', $reason);
        }

        $targetUser->refresh();

        $parts = [];
        if ($coins > 0) {
            $parts[] = '+' . number_format($coins) . ' tanga 🪙';
        }
        if ($points > 0) {
            $parts[] = '+' . number_format($points) . ' ball 🌟';
        }

        $targetName = $targetUser->id === $admin->id ? 'Shaxsiy hisobingizga' : "«{$targetUser->name}» hisobiga";
        $msg = "{$targetName} " . implode(' va ', $parts) . " muvaffaqiyatli qo'shildi!";

        if ($request->wantsJson()) {
            return response()->json([
                'success'        => true,
                'message'        => $msg,
                'coins_added'    => $coins,
                'points_added'   => $points,
                'new_coins'      => (int) $targetUser->coin_balance,
                'new_points'     => (int) $targetUser->total_points,
                'level'          => (int) $targetUser->level,
                'level_title'    => (string) $targetUser->level_title,
                'target_user_id' => $targetUser->id,
            ]);
        }

        return back()->with('success', $msg);
    }
}
