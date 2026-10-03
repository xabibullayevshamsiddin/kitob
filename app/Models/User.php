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
        'total_points',
        'coin_balance',
        'bio',
        'is_banned',
        'banned_until',
        'ban_reason',
        'banned_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'total_points'      => 'integer',
        'coin_balance'      => 'integer',
        'is_banned'         => 'boolean',
        'banned_until'      => 'datetime',
        'banned_at'         => 'datetime',
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

    public function groupMessages(): HasMany
    {
        return $this->hasMany(GroupMessage::class);
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class);
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
        return $this->hasRole('admin');
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
        return $this->profile?->reading_place !== null;
    }

    // -------------------------------------------------------------------------
    // Ban System
    // -------------------------------------------------------------------------

    public function isBanned(): bool
    {
        if (!$this->is_banned) {
            return false;
        }

        // If time-limited ban and duration has passed, auto-unban
        if ($this->banned_until && $this->banned_until->isPast()) {
            $this->update([
                'is_banned'    => false,
                'banned_until' => null,
                'ban_reason'   => null,
            ]);
            return false;
        }

        return true;
    }

    public function ban(string $duration, ?string $reason = null): void
    {
        $until = match ($duration) {
            '1_hour'  => now()->addHour(),
            '1_day'   => now()->addDay(),
            '1_week'  => now()->addWeek(),
            '1_month' => now()->addMonth(),
            default   => null, // permanent
        };

        $this->update([
            'is_banned'    => true,
            'banned_until' => $until,
            'ban_reason'   => $reason ? trim($reason) : 'Qoidabuzarlik / Nojo\'ya so\'zlar ishlatilganligi sababli',
            'banned_at'    => now(),
        ]);
    }

    public function unban(): void
    {
        $this->update([
            'is_banned'    => false,
            'banned_until' => null,
            'ban_reason'   => null,
        ]);
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
}
