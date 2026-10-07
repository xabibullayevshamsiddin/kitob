<?php

namespace App\Http\Livewire\Notifications;

use App\Http\Livewire\Concerns\WithToast;
use App\Models\DailyActivity;
use App\Models\LiveEvent;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationList extends Component
{
    use WithPagination;
    use WithToast;

    protected $paginationTheme = 'tailwind';
    public function markAllRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->toastSuccess('Barcha bildirishnomalar o\'qilgan deb belgilandi.');
    }

    public function markRead(string $id): void
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
            $this->toastInfo('Bildirishnoma o\'qildi.');
        }
    }

    /** Bildirishnomani butunlay o'chirish */
    public function deleteNotification(string $id): void
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->delete();
            $this->toastSuccess('Bildirishnoma o\'chirildi.');
        }
    }

    /** Barcha bildirishnomalarni tozalash */
    public function clearAll(): void
    {
        $deleted = Auth::user()->notifications()->delete();
        $this->toastSuccess('Barcha bildirishnomalar muvaffaqiyatli tozalandi.');
    }

    /** Bildirishnoma havolasiga o'tish (o'qilgan deb belgilab) */
    public function openNotification(string $id, string $link): void
    {
        $this->markRead($id);

        if ($link) {
            // url() — loyiha base path'ini (/Kitob/public) hisobga oladi
            $this->redirect(url($link));
        }
    }

    /**
     * Ertalabki uyg'onish vazifasini qabul qilish (+100 ball + 20 tanga).
     * Faqat Toshkent vaqti bilan soat 05:00–07:00 oralig'ida bajariladi, kuniga bir marta.
     */
    public function claimEarlyBird(): void
    {
        $user = Auth::user();
        if (!$user) return;

        $tashkentNow = now('Asia/Tashkent');
        $hour = (int) $tashkentNow->format('G');

        // Faqat ertalabki soatlar: 05:00 dan 07:00 gacha (05:00 va 06:59 ham kiritiladi)
        if ($hour < 5 || $hour >= 7) {
            $this->toastError("Bu vazifa faqat ertalab 05:00–07:00 oralig'ida (O'zbekiston vaqti) bajariladi. Ertaga erta uyg'oning! 🌅");
            return;
        }

        $today = $tashkentNow->toDateString();
        $activity = DailyActivity::getTodayActivity($user->id, $today);

        if ($activity->early_bird_claimed) {
            $this->toastInfo("Bugungi erta uyg'onish vazifasi allaqachon bajarilgan! Ertaga yana urinib ko'ring. 🌅");
            return;
        }

        $pointsService = app(\App\Services\Gamification\PointsService::class);
        $pointsService->awardPoints(
            $user,
            70,
            'bonus',
            "Ertalabki uyg'onish vazifasi (+70 ball)"
        );

        $pointsService->awardCoins(
            $user,
            10,
            'bonus',
            "Ertalabki uyg'onish vazifasi (+10 tanga 🪙)"
        );

        $activity->update(['early_bird_claimed' => true]);
        $freshUser = $user->fresh();

        $this->dispatchBrowserEvent('points-awarded', [
            'points'   => 70,
            'newTotal' => (int) $freshUser->total_points,
        ]);

        $this->dispatchBrowserEvent('coins-awarded', [
            'coins'    => 10,
            'newTotal' => (int) $freshUser->coin_balance,
        ]);

        $this->dispatchBrowserEvent('bonus-claimed-animation', [
            'points' => 70,
            'coins'  => 10,
        ]);

        $this->toastSuccess("Barakalla, ertaldo turibsiz! 🌅 +70 ball va +10 tanga hisobingizga qo'shildi!");
    }

    /** Kunlik 1 soatlik (60 daqiqa) mutolaa super bonusini olish (200 ball + 40 tanga) */
    public function claimHourlyBonus(): void
    {
        $user = Auth::user();
        if (!$user) return;

        $today = now('Asia/Tashkent')->toDateString();
        $activity = DailyActivity::getTodayActivity($user->id, $today);

        if ((int) $activity->minutes_read < 60) {
            $remaining = 60 - (int) $activity->minutes_read;
            $this->toastError("Kunlik super bonus uchun yana {$remaining} daqiqa mutolaa qilishingiz kerak!");
            return;
        }

        if ($activity->hourly_bonus_claimed) {
            $this->toastInfo("Bugungi 1 soatlik super bonus allaqachon qabul qilingan! Ertaga yangi bonus ochiladi.");
            return;
        }

        $pointsService = app(\App\Services\Gamification\PointsService::class);
        $pointsService->awardPoints(
            $user,
            200,
            'bonus',
            "Kunlik 1 soatlik mutolaa super bonusi (+200 ball)"
        );

        $pointsService->awardCoins(
            $user,
            40,
            'bonus',
            "Kunlik 1 soatlik mutolaa super bonusi (+40 tanga 🪙)"
        );

        $activity->update(['hourly_bonus_claimed' => true]);
        $freshUser = $user->fresh();

        // Header va sahifadagi hisoblagichlar uchun animatsiya hodisalari
        $this->dispatchBrowserEvent('points-awarded', [
            'points'   => 200,
            'newTotal' => (int) $freshUser->total_points,
        ]);

        $this->dispatchBrowserEvent('coins-awarded', [
            'coins'    => 40,
            'newTotal' => (int) $freshUser->coin_balance,
        ]);

        $this->dispatchBrowserEvent('bonus-claimed-animation', [
            'points' => 200,
            'coins'  => 40,
        ]);

        $this->toastSuccess("Tabriklaymiz! +200 ball va +40 tanga hisobingizga muvaffaqiyatli qo'shildi! 🏆🪙");
    }

    public function render()
    {
        $user = Auth::user();

        // 20 tadan ko'p bildirishnomalar bo'lsa, eskilari avtomatik tozalanadi
        \App\Services\NotifyUser::pruneOldNotifications($user);

        $notifications = $user->notifications()
            ->latest()
            ->paginate(10)
            ->through(function ($n) {
                return [
                    'id'        => $n->id,
                    'type'      => data_get($n->data, 'type', 'info'),
                    'title'     => data_get($n->data, 'title', 'Bildirishnoma'),
                    'body'      => data_get($n->data, 'body', ''),
                    'icon'      => data_get($n->data, 'icon', '🔔'),
                    'link'      => data_get($n->data, 'link', ''),
                    'read_at'   => $n->read_at,
                    'time'      => $n->created_at->timezone('Asia/Tashkent')->diffForHumans(),
                ];
            });

        // Bugungi mutolaa faolligi (real ma'lumotlar)
        $today = now('Asia/Tashkent')->toDateString();
        $todayActivity = DailyActivity::where('user_id', $user->id)
            ->whereDate('activity_date', $today)
            ->first();

        $todayMinutes = $todayActivity ? (int) $todayActivity->minutes_read : 0;
        $hourlyBonusClaimed = $todayActivity ? (bool) $todayActivity->hourly_bonus_claimed : false;

        $streak = $user->streak;

        $streakAlert = ($streak && $streak->current_streak > 0 && $todayMinutes < 10)
            ? [
                'title'  => 'Streak xavf ostida! 🔥',
                'body'   => 'Bugun hali ' . max(0, 10 - $todayMinutes) . ' daqiqa o\'qish kerak. ' . $streak->current_streak . ' kunlik ketma-ketlikni saqlab qoling!',
            ]
            : null;

        // Kunlik 1 soatlik (60 daqiqa) super mutolaa vazifasi
        $hourlyGoal = [
            'target_minutes'  => 60,
            'current_minutes' => $todayMinutes,
            'remaining'       => max(0, 60 - $todayMinutes),
            'percentage'      => min(100, round(($todayMinutes / 60) * 100)),
            'is_completed'    => $todayMinutes >= 60,
            'is_claimed'      => $hourlyBonusClaimed,
            'points'          => 200,
            'coins'           => 40,
        ];

        // Ertalabki uyg'onish vazifasi: 05:00–07:00 (O'zbekiston vaqti), kuniga bir marta
        $tashkentHour = (int) now('Asia/Tashkent')->format('G');
        $earlyBird = [
            'is_claimed'  => $todayActivity ? (bool) $todayActivity->early_bird_claimed : false,
            'window_open' => $tashkentHour >= 5 && $tashkentHour < 7,
            'points'      => 70,
            'coins'       => 10,
        ];

        // Upcoming live event (real data) — eng yangisi ustunlik bilan
        $upcomingLive = LiveEvent::whereIn('status', ['scheduled', 'live'])
            ->orderByRaw("CASE WHEN status = 'live' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->first();

        return view('livewire.notifications.notification-list', [
            'notifications' => $notifications,
            'unreadCount'   => $user->unreadNotifications->count(),
            'streakAlert'   => $streakAlert,
            'hourlyGoal'    => $hourlyGoal,
            'earlyBird'     => $earlyBird,
            'upcomingLive'  => $upcomingLive,
        ])->layout('layouts.app', ['title' => 'Bildirishnomalar']);
    }
}
