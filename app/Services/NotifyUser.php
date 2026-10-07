<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Illuminate\Notifications\Messages\DatabaseMessage;

/**
 * Saytdagi barcha bildirishnomalarning yagona nuqtasi.
 *
 * Laravel'ning DatabaseNotification (notifications jadvali) ishlatadi —
 * /notifications sahifasi va header qo'ng'irog'i o'sha jadvaldan o'qiydi.
 *
 * Ishlatish:  NotifyUser::send($user, 'badge', 'Nishon olindi! 🏆', '...', '🏅');
 *             NotifyUser::sendToRole('admin', 'live', 'Yangi efir boshlandi!', ...);
 */
class NotifyUser
{
    /**
     * Bitta foydalanuvchida saqlanadigan maksimal bildirishnomalar soni.
     * 20 tadan oshganda eskilari (21-chisi va undan oldingilari) avtomatik o'chiriladi.
     */
    public const MAX_NOTIFICATIONS_PER_USER = 20;

    /**
     * Bitta foydalanuvchiga database bildirishnoma yuborish.
     */
    public static function send(User $user, string $type, string $title, string $body, string $icon = '🔔', string $link = ''): void
    {
        if (!$user) {
            return;
        }

        try {
            $user->notify(new class($type, $title, $body, $icon, $link) extends \Illuminate\Notifications\Notification {
                public function __construct(
                    protected string $type,
                    protected string $title,
                    protected string $body,
                    protected string $icon,
                    protected string $link,
                ) {}

                public function via($notifiable): array
                {
                    return ['database'];
                }

                public function toArray($notifiable): array
                {
                    return [
                        'type' => $this->type,
                        'title' => $this->title,
                        'body' => $this->body,
                        'icon' => $this->icon,
                        'link' => $this->link,
                    ];
                }
            });

            // 20 tadan oshgan eski bildirishnomalarni avtomatik tozalash (21-chisi avtomatik o'chadi)
            self::pruneOldNotifications($user, self::MAX_NOTIFICATIONS_PER_USER);
        } catch (\Throwable $e) {
            // Bildirishnoma muvaffaqiyatsiz bo'lsa asosiy jarayon buzilmasin
            report($e);
        }
    }

    /**
     * Foydalanuvchining 20 tadan oshgan eski bildirishnomalarini avtomatik tozalash.
     * Eng so'nggi $keep (standart 20) ta bildirishnoma saqlanib,
     * undan oldingi eskilari avtomatik bazadan o'chiriladi.
     */
    public static function pruneOldNotifications(User $user, int $keep = self::MAX_NOTIFICATIONS_PER_USER): int
    {
        try {
            $total = $user->notifications()->count();
            if ($total <= $keep) {
                return 0;
            }

            // Eng so'nggi $keep ta bildirishnomaning ID larini olish
            $keepIds = $user->notifications()
                ->latest()
                ->limit($keep)
                ->pluck('id');

            // Qolgan barcha eski bildirishnomalarni o'chirish
            return $user->notifications()
                ->whereNotIn('id', $keepIds)
                ->delete();
        } catch (\Throwable $e) {
            report($e);
            return 0;
        }
    }

    /**
     * Tizimdagi barcha foydalanuvchilarning 20 tadan ortiq bildirishnomalarini tozalash (cron / artisan uchun).
     */
    public static function pruneAllUsers(int $keep = self::MAX_NOTIFICATIONS_PER_USER): int
    {
        $deletedTotal = 0;
        try {
            // 20 tadan ortiq bildirishnomasi bor foydalanuvchilarni aniqlash
            $userIds = \Illuminate\Support\Facades\DB::table('notifications')
                ->where('notifiable_type', User::class)
                ->select('notifiable_id')
                ->groupBy('notifiable_id')
                ->havingRaw('COUNT(*) > ?', [$keep])
                ->pluck('notifiable_id');

            foreach ($userIds as $id) {
                $u = User::find($id);
                if ($u) {
                    $deletedTotal += self::pruneOldNotifications($u, $keep);
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return $deletedTotal;
    }

    /**
     * Ma'lum rol'dagi barcha foydalanuvchilarga yuborish (masalan, barcha adminlar).
     */
    public static function sendToRole(string $role, string $type, string $title, string $body, string $icon = '🔔', string $link = ''): void
    {
        // 1) users.role kolonkasi orqali (bosh rol)
        User::where('role', $role)
            ->whereNull('deleted_at')
            ->get()
            ->each(fn (User $u) => self::send($u, $type, $title, $body, $icon, $link));

        // 2) Spatie rollari orqali ham (spatie Rolga bog'langan, users.role'dan boshqacha bo'lishi mumkin)
        $spatieUsers = User::whereHas('roles', fn ($q) => $q->where('name', $role))
            ->whereNull('deleted_at')
            ->pluck('id');

        User::whereIn('id', $spatieUsers)
            ->get()
            ->each(fn (User $u) => self::send($u, $type, $title, $body, $icon, $link));
    }
}
