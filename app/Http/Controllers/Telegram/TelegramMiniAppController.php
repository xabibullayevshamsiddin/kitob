<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\DailyQuote;
use App\Models\ShopItem;
use App\Services\Telegram\TelegramAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TelegramMiniAppController extends Controller
{
    /**
     * Telegram Mini App asosiy oynasi.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Faol kitoblar va yangiliklar
        $featuredBook = Book::where('is_active', true)
            ->latest('published_at')
            ->first();

        $recentBooks = Book::where('is_active', true)
            ->latest('id')
            ->take(6)
            ->get();

        $dailyQuote = DailyQuote::with('book')
            ->whereDate('send_date', '<=', today())
            ->latest('send_date')
            ->first();

        $shopItemsCount = ShopItem::where('is_active', true)->count();

        return view('telegram.mini-app', compact(
            'user',
            'featuredBook',
            'recentBooks',
            'dailyQuote',
            'shopItemsCount'
        ));
    }

    /**
     * Telegram WebApp JS SDK'dan kelgan initData orqali avtomatik kirish.
     */
    public function authenticate(Request $request, TelegramAuthService $authService): JsonResponse
    {
        $initData = (string) $request->input('init_data', '');

        if (empty($initData)) {
            return response()->json([
                'success' => false,
                'message' => 'initData ma\'lumoti yuborilmadi.',
            ], 422);
        }

        $verified = $authService->verifyInitData($initData);

        if ($verified === false) {
            return response()->json([
                'success' => false,
                'message' => 'Yaroqsiz yoki eskirgan Telegram imzosi (Hash signature invalid).',
            ], 401);
        }

        $tgUser = $verified['user_data'] ?? null;

        if (!$tgUser || empty($tgUser['id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Foydalanuvchi ma\'lumotlari (user_data) topilmadi.',
            ], 422);
        }

        $result = $authService->findOrCreateUser($tgUser);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 403);
        }

        $user = $result['user'];

        // Tizimga kiritish va sessiyani yangilash
        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'is_new'  => $result['is_new'] ?? false,
            'user'    => [
                'id'           => $user->id,
                'name'         => $user->name,
                'username'     => $user->username,
                'avatar_url'   => $user->avatar_url,
                'coin_balance' => $user->coin_balance,
                'total_points' => $user->total_points,
                'streak'       => $user->streak?->current_streak ?? 0,
            ],
        ]);
    }
}
