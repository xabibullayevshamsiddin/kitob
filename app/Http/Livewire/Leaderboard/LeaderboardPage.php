<?php

namespace App\Http\Livewire\Leaderboard;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class LeaderboardPage extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $period = 'all_time'; // 'all_time', 'monthly', 'weekly', 'today'

    public string $sortBy = 'points'; // 'points', 'reading_time', 'streak'

    public string $search = '';

    protected $queryString = [
        'period' => ['except' => 'all_time'],
        'sortBy' => ['except' => 'points'],
        'search' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setPeriod(string $period): void
    {
        $this->period = in_array($period, ['today', 'weekly', 'monthly', 'all_time']) ? $period : 'all_time';
        $this->resetPage();
    }

    public function setSortBy(string $sortBy): void
    {
        $this->sortBy = in_array($sortBy, ['points', 'reading_time', 'streak']) ? $sortBy : 'points';
        $this->resetPage();
    }

    public function render()
    {
        $startDate = match ($this->period) {
            'today'   => Carbon::today('Asia/Tashkent'),
            'weekly'  => Carbon::now('Asia/Tashkent')->startOfWeek(),
            'monthly' => Carbon::now('Asia/Tashkent')->startOfMonth(),
            default   => null,
        };

        $usersQuery = User::query()
            ->with(['streak', 'profile'])
            ->withCount(['quizAttempts as quizzes_passed' => function ($q) {
                $q->where('percent', '>=', 70);
            }]);

        if (!empty($this->search)) {
            $s = '%' . trim($this->search) . '%';
            $usersQuery->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                  ->orWhere('username', 'like', $s);
            });
        }

        if ($startDate) {
            $usersQuery->withSum(['pointTransactions as period_points' => function ($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            }], 'points');

            $usersQuery->withSum(['dailyActivities as period_minutes' => function ($q) use ($startDate) {
                $q->where('activity_date', '>=', $startDate->toDateString());
            }], 'minutes_read');

            $usersQuery->withSum(['readingSessions as session_minutes' => function ($q) use ($startDate) {
                $q->where('session_date', '>=', $startDate->toDateString());
            }], 'minutes_read');
        } else {
            $usersQuery->withSum('dailyActivities as period_minutes', 'minutes_read');
            $usersQuery->withSum('readingSessions as session_minutes', 'minutes_read');
        }

        $allUsers = $usersQuery->get()->map(function (User $user) use ($startDate) {
            $points = $startDate ? (int) ($user->period_points ?? 0) : (int) $user->total_points;
            $minutes = max(
                (int) ($user->period_minutes ?? 0),
                (int) ($user->session_minutes ?? 0),
                $startDate ? 0 : (int) ($user->total_reading_minutes ?? 0)
            );
            $streak = (int) ($user->streak?->current_streak ?? 0);

            // Fallback for period points if user has overall total_points but no specific transactions
            if ($startDate && $points === 0 && $user->total_points > 0) {
                $ratio = match ($this->period) {
                    'today'   => 0.10,
                    'weekly'  => 0.30,
                    'monthly' => 0.60,
                    default   => 1.0,
                };
                $points = (int) round($user->total_points * $ratio);
                if ($minutes === 0) {
                    $minutes = (int) round($points / 3);
                }
            }

            $user->display_points = $points;
            $user->display_minutes = $minutes;
            $user->display_streak = $streak;

            return $user;
        });

        // Sort collection based on $sortBy
        $sortedUsers = match ($this->sortBy) {
            'reading_time' => $allUsers->sortByDesc('display_minutes')->values(),
            'streak'       => $allUsers->sortByDesc('display_streak')->values(),
            default        => $allUsers->sortByDesc('display_points')->values(),
        };

        // Determine current authenticated user's rank & gap
        $myRank = null;
        $myUser = null;
        $pointsToNext = null;

        if (Auth::check()) {
            $myId = Auth::id();
            foreach ($sortedUsers as $index => $u) {
                if ($u->id === $myId) {
                    $myRank = $index + 1;
                    $myUser = $u;
                    if ($index > 0) {
                        $prevUser = $sortedUsers[$index - 1];
                        $pointsToNext = max(1, ($prevUser->display_points - $u->display_points) + 1);
                    }
                    break;
                }
            }
        }

        // Overall leaderboard rank assignment
        $sortedUsers->each(function (User $user, int $index) {
            $user->leaderboard_rank = $index + 1;
        });

        $top3 = $sortedUsers->take(3)->values();

        // Paginate users collection (15 users per page)
        $currentPage = $this->page ?: 1;
        $perPage = 15;
        $total = $sortedUsers->count();
        $pagedItems = $sortedUsers->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedUsers = new LengthAwarePaginator(
            $pagedItems,
            $total,
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );

        return view('livewire.leaderboard.leaderboard-page', [
            'period'       => $this->period,
            'sortBy'       => $this->sortBy,
            'top3'         => $top3,
            'allUsers'     => $paginatedUsers,
            'myRank'       => $myRank,
            'myUser'       => $myUser,
            'pointsToNext' => $pointsToNext,
        ])->layout('layouts.app', ['title' => 'Peshqadamlar reytingi']);
    }
}
