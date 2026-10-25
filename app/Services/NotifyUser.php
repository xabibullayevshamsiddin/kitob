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

    /**
     * Jonli efir (strim) boshlanganda obunachilarga Telegram va sayt ichida xabar yuborish.
     */
    public static function notifyLiveStreamStarted(\App\Models\LiveEvent $event, User $host): void
    {
        $followerIds = \App\Models\Follow::where('following_id', $host->id)
            ->pluck('follower_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $followerMap = array_flip($followerIds);
        $link = '/live/' . $event->id;
        $fullUrl = rtrim((string) config('app.url', 'https://kitobxon.elitehost.uz'), '/') . $link;

        $safeHostName = htmlspecialchars((string) $host->name);
        $safeHostUsername = htmlspecialchars((string) ($host->username ?? ''));
        $safeTitle = htmlspecialchars((string) $event->title);
        $bookTitle = $event->book?->title ? htmlspecialchars((string) $event->book->title) : null;
        $safeDesc = !empty($event->description) ? htmlspecialchars(\Illuminate\Support\Str::limit((string) $event->description, 160)) : null;

        // 1. Sayt ichidagi bildirishnoma (barcha faol foydalanuvchilarga, obunachilar uchun maxsus sarlavha bilan)
        User::where('id', '!=', $host->id)
            ->whereNull('deleted_at')
            ->get()
            ->each(function (User $u) use ($event, $host, $followerMap, $link) {
                $isFollower = isset($followerMap[(int) $u->id]);
                $title = $isFollower
                    ? "🔴 Siz obuna bo'lgan {$host->name} strim boshladi!"
                    : '🔴 Jonli efir boshlandi!';
                $body = '"' . $event->title . '" — ' . $host->name . ' jonli efirda. Hoziroq qo\'shiling!';

                self::send($u, 'live', $title, $body, '📺', $link);
            });

        // 2. Obunachilarning Telegramiga darhol xabar yuborish (agar live_stream yoqilgan bo'lsa)
        if (!empty($followerIds)) {
            $followers = User::whereIn('id', $followerIds)
                ->whereNull('deleted_at')
                ->whereNotNull('telegram_id')
                ->get();

            foreach ($followers as $follower) {
                if (!$follower->prefersTelegramNotification('live_stream')) {
                    continue;
                }

                $safeFollowerName = htmlspecialchars((string) $follower->name);
                $bookLine = $bookTitle ? "• <b>Kitob:</b> 📖 «{$bookTitle}»\n" : '';
                $descLine = $safeDesc ? "• <b>Tavsif:</b> <i>{$safeDesc}</i>\n" : '';
                $usernameTag = $safeHostUsername !== '' ? " (@{$safeHostUsername})" : '';

                $tgText = "🔴 <b>Siz obuna bo'lgan muallif jonli efir (strim) boshladi!</b>\n\n"
                    . "Salom, <b>{$safeFollowerName}</b>! 👋\n"
                    . "Siz obuna bo'lgan <b>{$safeHostName}</b>{$usernameTag} hozirgina yangi jonli efirni boshladi:\n\n"
                    . "• <b>Mavzu:</b> 📺 «{$safeTitle}»\n"
                    . $bookLine
                    . $descLine . "\n"
                    . "Hoziroq efirga qo'shiling va jonli suhbatda qatnashing! 👇";

                try {
                    \App\Jobs\SendTelegramNotificationJob::dispatch($follower->id, $tgText, [
                        'reply_markup' => [
                            'inline_keyboard' => [
                                [
                                    ['text' => '🔴 Jonli efirga qo\'shilish', 'url' => $fullUrl],
                                ],
                                [
                                    ['text' => '🔕 Strim xabarlarini o\'chirish', 'callback_data' => 'toggle_pref:live_stream'],
                                ],
                            ],
                        ],
                    ], 'live_stream');
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }
    }

    /**
     * Guruh ichida jonli efir (strim) boshlanganda guruh a'zolariga (va obunachilarga) xabar yuborish.
     */
    public static function notifyGroupLiveStreamStarted(\App\Models\LiveEvent $event, User $host, \App\Models\Group $group): void
    {
        $members = \App\Models\GroupMember::where('group_id', $group->id)
            ->where('user_id', '!=', $host->id)
            ->with('user')
            ->get();

        $followerIds = \App\Models\Follow::where('following_id', $host->id)
            ->pluck('follower_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $followerMap = array_flip($followerIds);

        $link = '/live/' . $event->id;
        $fullUrl = rtrim((string) config('app.url', 'https://kitobxon.elitehost.uz'), '/') . $link;
        $safeGroupName = htmlspecialchars((string) $group->name);
        $safeHostName = htmlspecialchars((string) $host->name);
        $safeTitle = htmlspecialchars((string) $event->title);

        foreach ($members as $gm) {
            $memberUser = $gm->user;
            if (!$memberUser) {
                continue;
            }

            self::send(
                $memberUser,
                'live',
                '🔴 Guruhda jonli efir boshlandi!',
                '«' . $group->name . '» guruhida "' . $event->title . '" jonli efiri boshlandi. Qatnashish uchun kiring!',
                '📺',
                $link
            );

            // Agar guruh a'zosi strim muallifiga obuna bo'lgan bo'lsa yoki live_stream bildirishnomasi yoqilgan bo'lsa
            if ($memberUser->prefersTelegramNotification('live_stream')) {
                $isFollower = isset($followerMap[(int) $memberUser->id]);
                $header = $isFollower
                    ? "🔴 <b>Siz obuna bo'lgan {$safeHostName} «{$safeGroupName}» guruhida strim boshladi!</b>"
                    : "🔴 <b>«{$safeGroupName}» guruhida jonli efir boshlandi!</b>";

                $tgText = "{$header}\n\n"
                    . "• <b>Boshlovchi:</b> {$safeHostName}\n"
                    . "• <b>Mavzu:</b> 📺 «{$safeTitle}»\n\n"
                    . "Guruh jonli efiriga qo'shilish uchun pastdagi tugmani bosing 👇";

                try {
                    \App\Jobs\SendTelegramNotificationJob::dispatch($memberUser->id, $tgText, [
                        'reply_markup' => [
                            'inline_keyboard' => [
                                [
                                    ['text' => '🔴 Jonli efirga kirish', 'url' => $fullUrl],
                                ],
                                [
                                    ['text' => '🔕 Strim xabarlarini o\'chirish', 'callback_data' => 'toggle_pref:live_stream'],
                                ],
                            ],
                        ],
                    ], 'live_stream');
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }
    }

    /**
     * Foydalanuvchiga yangi obunachi qo'shilganda sayt va Telegram orqali xabar yuborish.
     */
    public static function notifyNewFollower(User $targetUser, User $follower): void
    {
        if ($targetUser->id === $follower->id) {
            return;
        }

        $profileLink = '/profile/' . $follower->username;
        $fullUrl = rtrim((string) config('app.url', 'https://kitobxon.elitehost.uz'), '/') . $profileLink;

        self::send(
            $targetUser,
            'social',
            '👤 Yangi obunachi!',
            "{$follower->name} (@{$follower->username}) sizning sahifangizga obuna bo'ldi.",
            '✨',
            $profileLink
        );

        if ($targetUser->prefersTelegramNotification('new_follower')) {
            $safeTarget = htmlspecialchars((string) $targetUser->name);
            $safeFollower = htmlspecialchars((string) $follower->name);
            $safeUsername = htmlspecialchars((string) $follower->username);

            $tgText = "👤 <b>Sizda yangi obunachi bor!</b>\n\n"
                . "Salom, <b>{$safeTarget}</b>! 👋\n"
                . "<b>{$safeFollower}</b> (@{$safeUsername}) sizning «Kitobxon» sahifangizga obuna bo'ldi. ✨\n\n"
                . "<i>Endi siz jonli efir (strim) boshlaganingizda obunachingizga darhol xabar yuboriladi!</i>";

            try {
                \App\Jobs\SendTelegramNotificationJob::dispatch($targetUser->id, $tgText, [
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                ['text' => '👤 Obunachi profilini ko\'rish', 'url' => $fullUrl],
                            ],
                            [
                                ['text' => '🔕 Obuna xabarlarini o\'chirish', 'callback_data' => 'toggle_pref:new_follower'],
                            ],
                        ],
                    ],
                ], 'new_follower');
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
