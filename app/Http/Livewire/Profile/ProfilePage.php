<?php

namespace App\Http\Livewire\Profile;

use App\Http\Livewire\Concerns\WithToast;
use App\Models\Badge;
use App\Models\BookReadingProgress;
use App\Models\DailyActivity;
use App\Models\Follow;
use App\Models\GlobalChatMessage;
use App\Models\GroupMember;
use App\Models\QuizAttempt;
use App\Models\ReadingSession;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProfilePage extends Component
{
    use WithToast;

    public User $user;
    public string $activeTab = 'overview';
    public bool $isFollowing = false;

    public function mount(string $username)
    {
        $this->user = User::with(['profile', 'streak', 'badges'])
            ->where('username', $username)
            ->firstOrFail();

        if (Auth::check()) {
            $this->isFollowing = Follow::where('follower_id', Auth::id())
                ->where('following_id', $this->user->id)
                ->exists();
        }

        // Profil egasi yoki admin kirganda statistika va yutuqlarni qayta sinxronlash
        $this->syncUserMetrics();
    }

    public function syncUserMetrics(): void
    {
        // 1. Ballarni sinxronlash (agar tranzaksiyalar yig'indisi ko'p bo'lsa)
        $txPoints = (int) $this->user->pointTransactions()->sum('points');
        if ($txPoints > (int) $this->user->total_points) {
            $this->user->total_points = $txPoints;
            $this->user->save();
        }

        // 2. Yutuqlarni (Badges) tekshirish va biriktirish
        // Streak endi global \App\Http\Middleware\TrackDailyStreak middlewareida,
        // har qanday sahifa yuklanganda kuniga bir marta tekshiriladi.
        $this->checkAndAwardBadges();
    }

    protected function checkAndAwardBadges(): void
    {
        $existingBadgeIds = $this->user->badges()->pluck('badges.id')->toArray();

        // Badge 1: Birinchi qadam (kamida 1 ta bob o'qilganda)
        $chaptersCount = $this->user->readingSessions()->distinct('chapter_id')->count('chapter_id');
        if ($chaptersCount >= 1 && !in_array(1, $existingBadgeIds)) {
            $b = Badge::find(1);
            if ($b) $this->user->badges()->attach(1, ['earned_at' => now()]);
        }

        // Badge 2: 3 kunlik olov (streak >= 3)
        $currentStreak = (int) ($this->user->streak?->current_streak ?? 0);
        if ($currentStreak >= 3 && !in_array(2, $existingBadgeIds)) {
            $b = Badge::find(2);
            if ($b) $this->user->badges()->attach(2, ['earned_at' => now()]);
        }

        // Badge 4: Bilimdon (100% test topshirilganda)
        $perfectQuiz = $this->user->quizAttempts()->where('percent', '>=', 100)->exists();
        if ($perfectQuiz && !in_array(4, $existingBadgeIds)) {
            $b = Badge::find(4);
            if ($b) $this->user->badges()->attach(4, ['earned_at' => now()]);
        }

        // Qayta yuklash
        $this->user->load('badges');
    }

    public function toggleFollow()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $authId = Auth::id();
        if ($authId === $this->user->id) {
            $this->toastWarning("O'zingizga obuna bo'la olmaysiz.");
            return;
        }

        $follow = Follow::where('follower_id', $authId)
            ->where('following_id', $this->user->id)
            ->first();

        if ($follow) {
            $follow->delete();
            $this->isFollowing = false;
            $this->toastInfo("{$this->user->name} obunasi bekor qilindi.");
        } else {
            Follow::create([
                'follower_id' => $authId,
                'following_id' => $this->user->id,
            ]);
            $this->isFollowing = true;
            $this->toastSuccess("{$this->user->name} ga muvaffaqiyatli obuna bo'ldingiz! ✨");
        }
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $readingProgresses = BookReadingProgress::with(['book', 'chapter'])
            ->where('user_id', $this->user->id)
            ->latest('updated_at')
            ->take(12)
            ->get();

        // 60 kunlik Heatmap faolligi (DailyActivity + ReadingSession birlashmasi)
        $dailyMap = DailyActivity::where('user_id', $this->user->id)
            ->where('activity_date', '>=', now()->subDays(60)->toDateString())
            ->pluck('minutes_read', 'activity_date')
            ->toArray();

        $sessionMap = ReadingSession::where('user_id', $this->user->id)
            ->where('session_date', '>=', now()->subDays(60)->toDateString())
            ->groupBy('session_date')
            ->selectRaw('DATE(session_date) as s_date, SUM(minutes_read) as total_mins')
            ->pluck('total_mins', 's_date')
            ->toArray();

        $activities = [];
        for ($i = 59; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $m1 = (int) ($dailyMap[$d] ?? 0);
            $m2 = (int) ($sessionMap[$d] ?? 0);
            $activities[$d] = max($m1, $m2);
        }

        $badges = $this->user->badges;
        $notes = $this->user->notes()->with(['book', 'chapter'])->latest()->take(12)->get();

        // Dinamik hisob-kitoblar
        $totalReadingMinutes = max(
            (int) $this->user->readingSessions()->sum('minutes_read'),
            (int) $this->user->dailyActivities()->sum('minutes_read')
        );

        $stats = [
            'total_minutes'   => $totalReadingMinutes,
            'total_points'    => (int) $this->user->total_points,
            'coin_balance'    => (int) $this->user->coin_balance,
            'current_streak'  => (int) ($this->user->streak?->current_streak ?? 0),
            'books_finished'  => BookReadingProgress::where('user_id', $this->user->id)->where('percent_complete', '>=', 90)->count(),
            'books_reading'   => BookReadingProgress::where('user_id', $this->user->id)->where('percent_complete', '<', 90)->count(),
            'quizzes_passed'  => QuizAttempt::where('user_id', $this->user->id)->where('percent', '>=', (int) setting('quiz_passing_percent', 70))->count(),
            'groups_count'    => GroupMember::where('user_id', $this->user->id)->count(),
            'messages_count'  => GlobalChatMessage::where('user_id', $this->user->id)->notDeleted()->count(),
            'notes_count'     => $this->user->notes()->count(),
            'followers_count' => $this->user->followers()->count(),
            'following_count' => $this->user->following()->count(),
        ];

        return view('livewire.profile.profile-page', [
            'user'              => $this->user,
            'userRank'          => app(\App\Services\LeaderboardService::class)->getUserRank($this->user),
            'activeTab'         => $this->activeTab,
            'isFollowing'       => $this->isFollowing,
            'readingProgresses' => $readingProgresses,
            'activities'        => $activities,
            'badges'            => $badges,
            'notes'             => $notes,
            'stats'             => $stats,
            'isOwner'           => Auth::id() === $this->user->id,
        ])->layout('layouts.app', ['title' => $this->user->name . ' (@' . $this->user->username . ')']);
    }
}
