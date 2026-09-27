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
        } catch (\Throwable $e) {
            // Bildirishnoma muvaffaqiyatsiz bo'lsa asosiy jarayon buzilmasin
            report($e);
        }
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
