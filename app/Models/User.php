<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Services\Shop\ShopService;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    use HasRoles {
        HasRoles::hasRole as spatieHasRole;
        HasRoles::getRoleNames as spatieGetRoleNames;
        HasRoles::assignRole as spatieAssignRole;
    }

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'email_verified_at',
        'avatar',
        'telegram_id',
        'telegram_username',
        'phone',
        'phone_verified_at',
        'telegram_linked_at',
        'telegram_blocked_at',
        'telegram_locale',
        'notification_prefs',
        'total_points',
        'points_spent',
        'coin_balance',
        'bio',
        'is_banned',
        'banned_until',
        'ban_reason',
        'banned_at',
        'league',
        'weekly_points',
        'league_updated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'phone',
    ];

    protected $casts = [
        'email_verified_at'   => 'datetime',
        'telegram_id'         => 'integer',
        'phone_verified_at'   => 'datetime',
        'telegram_linked_at'  => 'datetime',
        'telegram_blocked_at' => 'datetime',
        'notification_prefs'  => 'array',
        'total_points'        => 'integer',
        'points_spent'        => 'integer',
        'coin_balance'        => 'integer',
        'is_banned'           => 'boolean',
        'banned_until'        => 'datetime',
        'banned_at'           => 'datetime',
    ];

    protected $appends = [
        'avatar_url',
        'total_reading_minutes',
        'current_streak',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function streak(): HasOne
    {
        return $this->hasOne(UserStreak::class);
    }

    // -------------------------------------------------------------------------
    // Level System
    // -------------------------------------------------------------------------

    /**
     * Hozirgi daraja raqamini qaytaradi (1–7)
     */
    public function getLevelAttribute(): int
    {
        // Agar model qisman yuklangan bo'lsa va total_points bo'lmasa, bazadan olamiz
        if (!array_key_exists('total_points', $this->attributes) && $this->exists) {
            $points = (int) static::where('id', $this->id)->value('total_points');
            $this->attributes['total_points'] = $points;
        }

        return \App\Services\Gamification\LevelService::getLevelForPoints(
            (int) ($this->attributes['total_points'] ?? 0)
        );
    }

    /**
     * Hozirgi daraja ma'lumotlarini qaytaradi (title, icon, coin_bonus, threshold)
     */
    public function getLevelInfoAttribute(): array
    {
        return \App\Services\Gamification\LevelService::getLevelInfo($this->level);
    }

    /**
     * Daraja nomi (masalan "Faol O'quvchi")
     */
    public function getLevelTitleAttribute(): string
    {
        return $this->level_info['title'] ?? "Boshlang'ich Kitobxon";
    }

    /**
     * Daraja emojisi (masalan "📖")
     */
    public function getLevelIconAttribute(): string
    {
        return $this->level_info['icon'] ?? '🌱';
    }

    /**
     * Daraja progressi (percent, next_level, points_needed, va h.k.)
     */
    public function getLevelProgressAttribute(): array
    {
        if (!array_key_exists('total_points', $this->attributes) && $this->exists) {
            $points = (int) static::where('id', $this->id)->value('total_points');
            $this->attributes['total_points'] = $points;
        }

        return \App\Services\Gamification\LevelService::getLevelProgress(
            (int) ($this->attributes['total_points'] ?? 0)
        );
    }

    // -------------------------------------------------------------------------
    // League (existing)
    // -------------------------------------------------------------------------

    public function getLeagueAttribute(): string
    {
        $val = $this->attributes['league'] ?? null;
        if (!empty($val) && in_array($val, ['bronze', 'silver', 'gold'])) {
            return $val;
        }

        $points = (int) ($this->attributes['total_points'] ?? 0);
        if ($points >= 2000) return 'gold';
        if ($points >= 500)  return 'silver';
        return 'bronze';
    }

    public function getLeagueTitleAttribute(): string
    {
        return match ($this->league) {
            'gold'   => __('site.league.gold'),
            'silver' => __('site.league.silver'),
            default  => __('site.league.bronze'),
        };
    }

    public function getLeagueIconAttribute(): string
    {
        return match ($this->league) {
            'gold'   => '🥇',
            'silver' => '🥈',
            default  => '🥉',
        };
    }

    public function readingSessions(): HasMany
    {
        return $this->hasMany(ReadingSession::class);
    }

    public function readingProgress(): HasMany
    {
        return $this->hasMany(BookReadingProgress::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function savedBookEntries(): HasMany
    {
        return $this->hasMany(UserSavedBook::class);
    }

    public function savedBooks(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'user_saved_books')
                    ->withTimestamps();
    }

    public function isBookSaved(int|Book $book): bool
    {
        $bookId = $book instanceof Book ? $book->id : $book;
        return $this->savedBooks()->where('books.id', $bookId)->exists();
    }

    public function pdfAnnotations(): HasMany
    {
        return $this->hasMany(PdfAnnotation::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(UserNote::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function coinTransactions(): HasMany
    {
        return $this->hasMany(CoinTransaction::class);
    }

    public function dailyActivities(): HasMany
    {
        return $this->hasMany(DailyActivity::class);
    }

    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
                    ->withPivot('earned_at');
    }

    public function globalChatMessages(): HasMany
    {
        return $this->hasMany(GlobalChatMessage::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_members')
                    ->withPivot('role', 'joined_at');
    }

    public function createdGroups(): HasMany
    {
        return $this->hasMany(Group::class, 'created_by');
    }

    public function groupMessages(): HasMany
    {
        return $this->hasMany(GroupMessage::class);
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class);
    }

    public function bookComments(): HasMany
    {
        return $this->hasMany(BookComment::class);
    }

    public function likedBookComments(): BelongsToMany
    {
        return $this->belongsToMany(BookComment::class, 'book_comment_likes');
    }

    public function aiChatHistories(): HasMany
    {
        return $this->hasMany(AiChatHistory::class);
    }

    public function leaderboardSnapshots(): HasMany
    {
        return $this->hasMany(LeaderboardSnapshot::class);
    }

    public function userQuotes(): HasMany
    {
        return $this->hasMany(UserQuote::class);
    }

    public function liveQuestions(): HasMany
    {
        return $this->hasMany(LiveQuestion::class);
    }

    /** Users who follow this user */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'following_id',
            'follower_id'
        );
    }

    /** Users that this user follows */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'follower_id',
            'following_id'
        );
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Returns avatar URL or a generated avatar from ui-avatars.com.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name)
             . '&background=4f46e5&color=fff&bold=true';
    }

    /**
     * Returns total reading minutes across all daily activities and reading sessions.
     */
    public function getTotalReadingMinutesAttribute(): int
    {
        return max(
            (int) $this->dailyActivities()->sum('minutes_read'),
            (int) $this->readingSessions()->sum('minutes_read')
        );
    }

    /**
     * Returns current streak count from related streak record.
     *
     * Self-healing: agar foydalanuvchi kecha ham, bugun ham o'qimagan bo'lsa
     * (yoki umuman faol bo'lmagan bo'lsa), streakni 0 ko'rsatamiz — hatto
     * kunlik `streaks:calculate` rejimlangan vazifasi ishlamagan bo'lsa ham
     * (masalan, lokal OSPanel muhitida cron yo'q).
     */
    public function getCurrentStreakAttribute(): int
    {
        $streak = $this->streak;

        if (!$streak || (int) $streak->current_streak < 1 || !$streak->last_active_date) {
            return 0;
        }

        $today     = now('Asia/Tashkent')->toDateString();
        $yesterday = now('Asia/Tashkent')->subDay()->toDateString();
        $last      = $streak->last_active_date->toDateString();

        // Streak faqat shu ikki holatda jonli: bugun yoki kecha faol bo'lsa.
        return in_array($last, [$today, $yesterday], true)
            ? (int) $streak->current_streak
            : 0;
    }

    // -------------------------------------------------------------------------
    // Role Helpers (zaxira manba: users.role ustuni)
    // -------------------------------------------------------------------------

    /**
     * Rol tekshiruvi: avval Spatie jadvali, bo'lmasa users.role ustuni.
     * Shu tufayli Spatie jadvalidan rol o'chib ketsa ham, ustundagi rol ishlaydi.
     */
    public function hasRole($roles, string $guard = null): bool
    {
        if ($this->spatieHasRole($roles, $guard)) {
            return true;
        }

        $columnRole = $this->attributes['role'] ?? null;

        if (!$columnRole) {
            return false;
        }

        if (is_string($roles)) {
            $roles = str_contains($roles, '|') ? explode('|', $roles) : [$roles];
        }

        if (is_array($roles)) {
            return in_array($columnRole, $roles, true);
        }

        return false;
    }

    /**
     * Rol nomlari: Spatie rollari + users.role ustuni (agar mavjud bo'lsa).
     */
    public function getRoleNames(): Collection
    {
        $names = $this->spatieGetRoleNames();

        $columnRole = $this->attributes['role'] ?? null;

        if ($columnRole && !$names->contains($columnRole)) {
            $names = $names->push($columnRole);
        }

        return $names;
    }

    /**
     * Rol berilganda users.role ustunini ham sinxron tutamiz.
     */
    public function assignRole(...$roles)
    {
        $result = $this->spatieAssignRole(...$roles);

        $names = collect($roles)
            ->map(fn ($role) => $role instanceof \Spatie\Permission\Contracts\Role ? $role->name : $role)
            ->flatten()
            ->filter(fn ($name) => is_string($name) && $name !== '')
            ->unique()
            ->values();

        if ($names->isNotEmpty()) {
            $this->forceFill(['role' => $this->pickPrimaryRoleName($names)])->saveQuietly();
        }

        return $result;
    }

    /**
     * Bir nechta rol berilsa, asosiy (ustuvor) rolni tanlaydi.
     */
    protected function pickPrimaryRoleName(Collection $names): string
    {
        foreach (['admin', 'teacher', 'author', 'student', 'reader'] as $priority) {
            if ($names->contains($priority)) {
                return $priority;
            }
        }

        return (string) $names->first();
    }

    public function isAdmin(): bool
    {
        return ($this->role ?? null) === 'admin' || $this->hasRole('admin');
    }

    public function isTeacher(): bool
    {
        return $this->hasRole('teacher');
    }

    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    public function isAuthor(): bool
    {
        return $this->hasRole('author');
    }

    public function isReader(): bool
    {
        return $this->hasRole('reader');
    }

    public function isAdminOrTeacher(): bool
    {
        return $this->hasRole(['admin', 'teacher', 'author']) || in_array($this->role ?? null, ['admin', 'teacher', 'author'], true);
    }

    // -------------------------------------------------------------------------
    // Onboarding
    // -------------------------------------------------------------------------

    /**
     * Checks whether the user has completed the onboarding flow.
     */
    public function hasCompletedOnboarding(): bool
    {
        if ($this->isAdminOrTeacher()) {
            return true;
        }

        return !empty($this->profile?->reading_place);
    }

    // -------------------------------------------------------------------------
    // Ban System
    // -------------------------------------------------------------------------

    public function isBanned(): bool
    {
        // Adminlar daxlsizdir — ular hech qachon bloklanmaydi
        if ($this->isAdmin()) {
            return false;
        }

        if (!$this->is_banned) {
            return false;
        }

        // If time-limited ban and duration has passed, auto-unban and notify
        if ($this->banned_until && $this->banned_until->isPast()) {
            $this->unban();
            return false;
        }

        return true;
    }

    public function ban(string $duration, ?string $reason = null): void
    {
        // Adminlar daxlsizdir — ularni hech qanday holatda (boshqa admin ham) bloklay olmaydi
        if ($this->isAdmin()) {
            return;
        }
        $until = match ($duration) {
            '1_hour'  => now()->addHour(),
            '1_day'   => now()->addDay(),
            '1_week'  => now()->addWeek(),
            '1_month' => now()->addMonth(),
            default   => null, // permanent
        };

        $finalReason = $reason ? trim($reason) : 'Qoidabuzarlik / Nojo\'ya so\'zlar ishlatilganligi sababli';

        $this->update([
            'is_banned'    => true,
            'banned_until' => $until,
            'ban_reason'   => $finalReason,
            'banned_at'    => now(),
        ]);

        $durationLabel = match ($duration) {
            '1_hour'  => '1 soat',
            '1_day'   => '1 kun',
            '1_week'  => '1 hafta',
            '1_month' => '1 oy',
            default   => 'Doimiy (butun umrga)',
        };

        $appealUrl = url('/contact?appeal=1&user_id=' . $this->id);

        try {
            \App\Services\NotifyUser::send(
                $this,
                'system',
                '🚫 Hisobingizga cheklov o\'rnatildi (Ban)',
                "Muddat: {$durationLabel}. Sabab: {$finalReason}. Noto'g'ri ban berilgan bo'lsa, Aloqa bo'limiga yozib qoldiring.",
                '🚫',
                '/contact?appeal=1&user_id=' . $this->id
            );
        } catch (\Throwable $e) {
            report($e);
        }

        if ($this->isTelegramLinked()) {
            try {
                $safeName = htmlspecialchars((string) $this->name);
                $safeReason = htmlspecialchars($finalReason);
                $untilText = $until
                    ? "• <b>Tugash vaqti:</b> " . $until->copy()->setTimezone('Asia/Tashkent')->format('d.m.Y H:i') . "\n"
                    : '';

                $tgMessage = "🚫 <b>Hisobingizga cheklov o'rnatildi (Ban)</b>\n\n"
                    . "Hurmatli <b>{$safeName}</b>, sizning «Kitobxon» platformasidagi hisobingiz vaqtincha yoki doimiy cheklandi:\n\n"
                    . "• <b>Muddat:</b> {$durationLabel}\n"
                    . $untilText
                    . "• <b>Sabab:</b> {$safeReason}\n\n"
                    . "⚖️ <b>Noto'g'ri ban berildimi?</b>\n"
                    . "Agar sizga xatolik bilan yoki nohaq ban berilgan deb hisoblasangiz, mana bu joyga yozib qoldiring:\n"
                    . "👉 <a href=\"{$appealUrl}\">📩 Aloqa bo'limiga yozish (Apellyatsiya)</a>\n\n"
                    . "<i>Cheklov muddati tugashi yoki ma'muriyat tomonidan olib tashlanishi bilan sizga darhol xabar yuboriladi.</i>";

                $inlineKeyboard = [];
                if (!str_contains($appealUrl, 'localhost') && !str_contains($appealUrl, '127.0.0.1')) {
                    $inlineKeyboard[] = [
                        ['text' => '📩 Noto\'g\'ri ban berildimi? (Aloqa)', 'url' => $appealUrl],
                    ];
                }
                $inlineKeyboard[] = [
                    ['text' => '⚙️ Bildirishnoma sozlamalari', 'callback_data' => 'cmd_settings'],
                ];

                \App\Jobs\SendTelegramNotificationJob::dispatch($this->id, $tgMessage, [
                    'reply_markup' => [
                        'inline_keyboard' => $inlineKeyboard,
                    ],
                ], 'ban_status');
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    public function unban(): void
    {
        $wasBanned = (bool) $this->is_banned;

        $this->update([
            'is_banned'    => false,
            'banned_until' => null,
            'ban_reason'   => null,
        ]);

        if (!$wasBanned) {
            return;
        }

        try {
            \App\Services\NotifyUser::send(
                $this,
                'system',
                '🎉 Hisobingiz blokdan chiqarildi!',
                'Hisobingizdagi cheklov (ban) yechildi. Endi barcha chatlar va imkoniyatlardan yana to\'liq foydalanishingiz mumkin!',
                '✅',
                '/dashboard'
            );
        } catch (\Throwable $e) {
            report($e);
        }

        if ($this->isTelegramLinked()) {
            try {
                $safeName = htmlspecialchars((string) $this->name);
                $appUrl = rtrim((string) config('app.url', 'https://kitobxon.elitehost.uz'), '/');

                $tgMessage = "🎉 <b>Xushxabar! Hisobingiz blokdan chiqarildi (Ban yechildi)</b>\n\n"
                    . "Assalomu alaykum, <b>{$safeName}</b>! 👋\n"
                    . "Sizning «Kitobxon» platformasidagi hisobingizdan cheklov (ban) muvaffaqiyatli olib tashlandi! ✅\n\n"
                    . "Endi platformaning barcha imkoniyatlaridan — mutolaa, global chat, guruhlar va jonli efirlardan yana to'liq foydalanishingiz mumkin! 📚✨";

                \App\Jobs\SendTelegramNotificationJob::dispatch($this->id, $tgMessage, [
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                ['text' => '🚀 Platformaga qaytish', 'url' => $appUrl],
                            ],
                            [
                                ['text' => '⚙️ Bildirishnoma sozlamalari', 'callback_data' => 'cmd_settings'],
                            ],
                        ],
                    ],
                ], 'ban_status');
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    /**
     * Muddati tugagan vaqtinchalik banlarni avtomatik yechish va foydalanuvchiga Telegram xabar yuborish.
     */
    public static function processExpiredBans(): int
    {
        $expiredUsers = static::where('is_banned', true)
            ->whereNotNull('banned_until')
            ->where('banned_until', '<=', now())
            ->get();

        $count = 0;
        foreach ($expiredUsers as $user) {
            $user->unban();
            $count++;
        }

        return $count;
    }

    public function getBanRemainingAttribute(): string
    {
        if (!$this->is_banned) {
            return '';
        }

        if (!$this->banned_until) {
            return 'Doimiy (butun umrga)';
        }

        if ($this->banned_until->isPast()) {
            return 'Muddati tugagan';
        }

        $now = now();
        $diffHours = (int) $now->diffInHours($this->banned_until, false);
        $diffDays  = (int) $now->diffInDays($this->banned_until, false);

        if ($diffDays > 0) {
            return $diffDays . ' kun qoldi';
        }

        if ($diffHours > 0) {
            return $diffHours . ' soat qoldi';
        }

        $diffMinutes = max(1, (int) $now->diffInMinutes($this->banned_until, false));
        return $diffMinutes . ' daqiqa qoldi';
    }

    public function reportsReceived()
    {
        return $this->hasMany(Report::class, 'reported_user_id');
    }

    public function reportsSent()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    // -------------------------------------------------------------------------
    // Shop & Points Relationships
    // -------------------------------------------------------------------------

    public function userItems(): HasMany
    {
        return $this->hasMany(UserItem::class);
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(UserEquipment::class);
    }

    public function shopPurchases(): HasMany
    {
        return $this->hasMany(ShopPurchase::class);
    }

    /**
     * Tanga almashtirish uchun mavjud ball (total_points - points_spent).
     */
    public function getAvailablePointsAttribute(): int
    {
        return max(0, (int) $this->total_points - (int) ($this->points_spent ?? 0));
    }

    /**
     * Foydalanuvchining ma'lum slotdagi kiyilgan buyumini olish.
     */
    public function getEquippedItem(string $slot): ?ShopItem
    {
        $eq = $this->relationLoaded('equipment')
            ? $this->equipment->firstWhere('slot', $slot)
            : $this->equipment()->with('shopItem')->where('slot', $slot)->first();

        return $eq?->shopItem;
    }

    /**
     * Foydalanuvchining kiyilgan unvoni (Title).
     */
    public function getEquippedTitleAttribute(): ?ShopItem
    {
        return $this->getEquippedItem('title');
    }

    /**
     * Foydalanuvchining kiyilgan ism rangi (Name color hex).
     */
    public function getEquippedNameColorAttribute(): ?string
    {
        $item = $this->getEquippedItem('name_color');
        if (!$item) return null;

        $color = $item->payload['hex'] ?? ($item->payload['color'] ?? null);
        return ShopService::validateHexColor($color) ? $color : null;
    }

    // -------------------------------------------------------------------------
    // Telegram & Auth Helpers
    // -------------------------------------------------------------------------

    public function hasPassword(): bool
    {
        return !empty($this->password);
    }

    public function hasEmail(): bool
    {
        return !empty($this->email);
    }

    public function isTelegramLinked(): bool
    {
        return !empty($this->telegram_id);
    }

    public function hasVerifiedPhone(): bool
    {
        return !empty($this->phone) && !empty($this->phone_verified_at);
    }

    public function isTelegramBlocked(): bool
    {
        return !empty($this->telegram_blocked_at);
    }

    public function markTelegramBlocked(): void
    {
        $this->update(['telegram_blocked_at' => now()]);
    }

    public function markTelegramUnblocked(): void
    {
        $this->update(['telegram_blocked_at' => null]);
    }

    public function isTelegramNotificationEnabled(string $type): bool
    {
        $prefs = is_array($this->notification_prefs) ? $this->notification_prefs : [];

        if ($type === 'quiet_hours') {
            return (bool) ($prefs['quiet_hours'] ?? false);
        }

        if ($type !== 'security') {
            if (array_key_exists('enabled', $prefs) && !$prefs['enabled']) {
                return false;
            }
            if (array_key_exists('telegram_enabled', $prefs) && !$prefs['telegram_enabled']) {
                return false;
            }
        }

        return (bool) ($prefs[$type] ?? true);
    }

    public function prefersTelegramNotification(?string $type = null): bool
    {
        if (!$this->isTelegramLinked() || $this->isTelegramBlocked()) {
            return false;
        }

        if ($type === null || $type === '') {
            $prefs = is_array($this->notification_prefs) ? $this->notification_prefs : [];
            if (array_key_exists('enabled', $prefs) && !$prefs['enabled']) {
                return false;
            }
            if (array_key_exists('telegram_enabled', $prefs) && !$prefs['telegram_enabled']) {
                return false;
            }
            return true;
        }

        return $this->isTelegramNotificationEnabled($type);
    }

    /**
     * Foydalanuvchining bitta Telegram bildirishnoma turini yoqish/o'chirish (toggle).
     */
    public function toggleTelegramPreference(string $key, ?bool $forceValue = null): bool
    {
        $prefs = is_array($this->notification_prefs) ? $this->notification_prefs : [];
        $allowedKeys = [
            'live_stream',
            'ban_status',
            'new_follower',
            'streak_reminder',
            'daily_quote',
            'coin_reward',
            'quiet_hours',
        ];

        if ($key === 'all_on' || $key === 'all_off') {
            $val = $key === 'all_on';
            foreach ($allowedKeys as $k) {
                if ($k !== 'quiet_hours') {
                    $prefs[$k] = $val;
                }
            }
            $prefs['enabled'] = true;
            $prefs['telegram_enabled'] = true;
            $this->update(['notification_prefs' => $prefs]);
            return $val;
        }

        if (!in_array($key, $allowedKeys, true)) {
            return false;
        }

        $current = $this->isTelegramNotificationEnabled($key);
        $next = $forceValue !== null ? $forceValue : !$current;

        $prefs[$key] = $next;
        $prefs['enabled'] = true;
        $prefs['telegram_enabled'] = true;

        $this->update(['notification_prefs' => $prefs]);

        return $next;
    }

    /**
     * Foydalanuvchi uchun "Jim soatlar" (Quiet Hours, masalan 23:00 - 07:00) faol ekanligini tekshirish.
     */
    public function isInTelegramQuietHours(?\Carbon\Carbon $now = null): bool
    {
        $prefs = is_array($this->notification_prefs) ? $this->notification_prefs : [];
        $quietEnabled = (bool) ($prefs['quiet_hours'] ?? config('telegram.quiet_hours_enabled', false));

        if (!$quietEnabled) {
            return false;
        }

        $now = ($now ?? now())->copy()->setTimezone('Asia/Tashkent');
        $startHour = (int) ($prefs['quiet_hours_start'] ?? config('telegram.quiet_hours_start', 23));
        $endHour = (int) ($prefs['quiet_hours_end'] ?? config('telegram.quiet_hours_end', 7));
        $hour = (int) $now->format('G');

        if ($startHour > $endHour) {
            return $hour >= $startHour || $hour < $endHour;
        }

        return $hour >= $startHour && $hour < $endHour;
    }
}
