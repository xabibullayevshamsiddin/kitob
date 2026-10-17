<?php

namespace App\Services\Analytics;

use App\Models\AiChatHistory;
use App\Models\Book;
use App\Models\BookAudio;
use App\Models\BookChapter;
use App\Models\BookVideo;
use App\Models\CoinTransaction;
use App\Models\DailyActivity;
use App\Models\DailyMetric;
use App\Models\GlobalChatMessage;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\PointTransaction;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\Gamification\ProfileVisualService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OverviewMetrics
{
    protected string $timezone;

    public function __construct()
    {
        $this->timezone = config('analytics.timezone', DateRange::TIMEZONE);
    }

    /**
     * Bitta kun (Asia/Tashkent) uchun barcha kunlik agregat ko'rsatkichlarni hisoblash.
     * Ham snapshot buyrug'i, ham "bugun" jonli hisob-kitobi shu metoddan foydalanadi.
     */
    public function computeDailyRow(string $date): array
    {
        $dayStart = Carbon::createFromFormat('Y-m-d', $date, $this->timezone)->startOfDay();
        $dayEnd = Carbon::createFromFormat('Y-m-d', $date, $this->timezone)->endOfDay();
        $startStr = $dayStart->format('Y-m-d H:i:s');
        $endStr = $dayEnd->format('Y-m-d H:i:s');

        $minActiveReaderMinutes = (int) config('analytics.active_reader_min_minutes', 5);
        $finishedThreshold = (float) config('analytics.book_finished_percent', 90.0);

        // DailyActivity asosidagi ko'rsatkichlar (bitta agregat so'rovda)
        $daStats = DB::table('daily_activities')
            ->where('activity_date', $date)
            ->selectRaw('
                COUNT(DISTINCT CASE WHEN logged_in = 1 OR minutes_read > 0 THEN user_id END) as dau,
                COUNT(DISTINCT CASE WHEN logged_in = 1 THEN user_id END) as logged_in_users,
                COUNT(DISTINCT CASE WHEN minutes_read > 0 THEN user_id END) as reading_users,
                COUNT(DISTINCT CASE WHEN minutes_read >= ? THEN user_id END) as active_readers,
                COALESCE(SUM(minutes_read), 0) as minutes_read
            ', [$minActiveReaderMinutes])
            ->first();

        $dau = (int) ($daStats->dau ?? 0);
        $loggedInUsers = (int) ($daStats->logged_in_users ?? 0);
        $readingUsers = (int) ($daStats->reading_users ?? 0);
        $activeReaders = (int) ($daStats->active_readers ?? 0);
        $minutesRead = (int) ($daStats->minutes_read ?? 0);

        // Ro'yxatdan o'tganlar va onboarding tugatganlar
        $newUsers = (int) User::whereBetween('created_at', [$startStr, $endStr])->count();

        $onboarded = (int) UserProfile::whereNotNull('reading_place')
            ->where('reading_place', '!=', '')
            ->whereBetween('created_at', [$startStr, $endStr])
            ->count();

        // Tugatilgan kitoblar: noyob (user_id, book_id) juftliklari (percent_complete >= 90)
        $booksFinished = $this->countDistinctFinishedBookPairs($startStr, $endStr, $finishedThreshold);

        // Yakunlangan va baholangan quiz urinishlari
        $quizAttempts = (int) QuizAttempt::whereNotNull('completed_at')
            ->whereBetween('completed_at', [$startStr, $endStr])
            ->where(function ($q) {
                $q->whereNull('review_status')
                  ->orWhere('review_status', '!=', 'pending');
            })
            ->count();

        // Ball va tangalar (faqat musbat — chiqarilgan)
        $pointsIssued = (int) PointTransaction::whereBetween('created_at', [$startStr, $endStr])
            ->where('points', '>', 0)
            ->sum('points');

        $coinsIssued = (int) CoinTransaction::whereBetween('created_at', [$startStr, $endStr])
            ->where('coins', '>', 0)
            ->sum('coins');

        // Chat xabarlari (global + guruh)
        $globalMessages = (int) GlobalChatMessage::whereBetween('created_at', [$startStr, $endStr])->count();
        $groupMessages = (int) GroupMessage::whereBetween('created_at', [$startStr, $endStr])->count();
        $chatMessages = $globalMessages + $groupMessages;

        // AI suhbatlar va tokenlar (bitta agregat so'rovda)
        $aiStats = DB::table('ai_chat_histories')
            ->whereBetween('created_at', [$startStr, $endStr])
            ->selectRaw('COUNT(*) as ai_messages, COALESCE(SUM(tokens_used), 0) as ai_tokens')
            ->first();
        $aiMessages = (int) ($aiStats->ai_messages ?? 0);
        $aiTokens = (int) ($aiStats->ai_tokens ?? 0);

        return [
            'date'            => $date,
            'dau'             => $dau,
            'logged_in_users' => $loggedInUsers,
            'reading_users'   => $readingUsers,
            'active_readers'  => $activeReaders,
            'new_users'       => $newUsers,
            'onboarded'       => $onboarded,
            'minutes_read'    => $minutesRead,
            'books_finished'  => $booksFinished,
            'quiz_attempts'   => $quizAttempts,
            'points_issued'   => $pointsIssued,
            'coins_issued'    => $coinsIssued,
            'chat_messages'   => $chatMessages,
            'ai_messages'     => $aiMessages,
            'ai_tokens'       => $aiTokens,
        ];
    }

    /**
     * Noyob (user_id, book_id) tugatilgan kitob juftliklarini sanash.
     * Bob bo'yicha bir nechta qator bo'lganda ham 1 foydalanuvchi + 1 kitob = 1 marta sanaladi.
     */
    public function countDistinctFinishedBookPairs(?string $startStr = null, ?string $endStr = null, ?float $threshold = null): int
    {
        $threshold = $threshold ?? (float) config('analytics.book_finished_percent', 90.0);

        $sub = DB::table('book_reading_progress')
            ->select('user_id', 'book_id')
            ->where('percent_complete', '>=', $threshold);

        if ($startStr !== null && $endStr !== null) {
            $sub->whereBetween('updated_at', [$startStr, $endStr]);
        }

        $sub->groupBy('user_id', 'book_id');

        return (int) DB::query()->fromSub($sub, 'finished_book_pairs')->count();
    }

    /**
     * Berilgan sana oralig'idagi noyob faol foydalanuvchilar soni (DAU / WAU / MAU uchun).
     * Faol = daily_activities'da logged_in = 1 YOKI minutes_read > 0.
     */
    public function countActiveUsersInWindow(string $fromDate, string $toDate): int
    {
        return (int) DailyActivity::whereBetween('activity_date', [$fromDate, $toDate])
            ->where(function ($q) {
                $q->where('logged_in', true)
                  ->orWhere('minutes_read', '>', 0);
            })
            ->distinct('user_id')
            ->count('user_id');
    }

    /**
     * Umumiy tab uchun 8 ta asosiy KPI kartalar, deltalari va 14 kunlik sparkline'lar.
     */
    public function getKpis(DateRange $range, bool $forceRefresh = false): array
    {
        $cacheKey = $range->cacheKey('overview_kpis');
        $ttl = (int) config('analytics.cache_ttl', 300);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($range) {
            $todayStr = Carbon::now($this->timezone)->toDateString();
            $endDay = $range->end->copy()->startOfDay();
            $endDate = $endDay->toDateString();
            $prevDayDate = $endDay->copy()->subDay()->toDateString();

            // 1. DAU (davr oxirgi kuni / bugun) + Kirgan va O'qigan alohida
            $todaySnap = ($endDate !== $todayStr) ? DailyMetric::find($endDate) : null;
            $todayRow = $todaySnap ? $todaySnap->toArray() : $this->computeDailyRow($endDate);

            $prevSnap = ($prevDayDate !== $todayStr) ? DailyMetric::find($prevDayDate) : null;
            $prevDayRow = $prevSnap ? $prevSnap->toArray() : $this->computeDailyRow($prevDayDate);

            $dau = (int) $todayRow['dau'];
            $prevDau = (int) $prevDayRow['dau'];

            // 2. WAU (oxirgi 7 kun vs undan oldingi 7 kun)
            $wauStart = $endDay->copy()->subDays(6)->toDateString();
            $prevWauEnd = $endDay->copy()->subDays(7)->toDateString();
            $prevWauStart = $endDay->copy()->subDays(13)->toDateString();

            $wau = $this->countActiveUsersInWindow($wauStart, $endDate);
            $prevWau = $this->countActiveUsersInWindow($prevWauStart, $prevWauEnd);

            // 3. MAU (oxirgi 30 kun vs undan oldingi 30 kun)
            $mauStart = $endDay->copy()->subDays(29)->toDateString();
            $prevMauEnd = $endDay->copy()->subDays(30)->toDateString();
            $prevMauStart = $endDay->copy()->subDays(59)->toDateString();

            $mau = $this->countActiveUsersInWindow($mauStart, $endDate);
            $prevMau = $this->countActiveUsersInWindow($prevMauStart, $prevMauEnd);

            // 4. Stickiness (Yopishqoqlik = DAU / MAU * 100)
            $stickiness = $mau > 0 ? round(($dau / $mau) * 100, 1) : 0.0;
            $prevStickiness = $prevMau > 0 ? round(($prevDau / $prevMau) * 100, 1) : 0.0;

            // Tanlangan davr va oldingi teng davr chegaralari
            $prevRange = $range->previousPeriod();
            $curStartStr = $range->start->format('Y-m-d H:i:s');
            $curEndStr = $range->end->format('Y-m-d H:i:s');
            $prevStartStr = $prevRange->start->format('Y-m-d H:i:s');
            $prevEndStr = $prevRange->end->format('Y-m-d H:i:s');

            // 5. Yangi foydalanuvchilar (davr bo'yicha)
            $newUsers = (int) User::whereBetween('created_at', [$curStartStr, $curEndStr])->count();
            $prevNewUsers = (int) User::whereBetween('created_at', [$prevStartStr, $prevEndStr])->count();

            // 6. O'qilgan daqiqa va soatlar (davr bo'yicha)
            $minutesRead = (int) DailyActivity::whereBetween('activity_date', [$range->startDate(), $range->endDate()])
                ->sum('minutes_read');
            $prevMinutesRead = (int) DailyActivity::whereBetween('activity_date', [$prevRange->startDate(), $prevRange->endDate()])
                ->sum('minutes_read');
            $readingHours = round($minutesRead / 60, 1);
            $prevReadingHours = round($prevMinutesRead / 60, 1);

            // 7. Faol o'quvchilar (kuniga >= 5 daqiqa o'qigan noyob foydalanuvchilar)
            $minMinutes = (int) config('analytics.active_reader_min_minutes', 5);
            $activeReaders = (int) DailyActivity::whereBetween('activity_date', [$range->startDate(), $range->endDate()])
                ->where('minutes_read', '>=', $minMinutes)
                ->distinct('user_id')
                ->count('user_id');
            $prevActiveReaders = (int) DailyActivity::whereBetween('activity_date', [$prevRange->startDate(), $prevRange->endDate()])
                ->where('minutes_read', '>=', $minMinutes)
                ->distinct('user_id')
                ->count('user_id');

            // 8. Tugatilgan kitoblar (noyob user_id + book_id juftliklari)
            $booksFinished = $this->countDistinctFinishedBookPairs($curStartStr, $curEndStr);
            $prevBooksFinished = $this->countDistinctFinishedBookPairs($prevStartStr, $prevEndStr);
            $totalBooksFinishedAllTime = $this->countDistinctFinishedBookPairs();

            // 14 kunlik sparkline ma'lumotlari
            $sparklineRange = new DateRange(
                $endDay->copy()->subDays(13)->startOfDay(),
                $endDay->copy()->endOfDay(),
                '14'
            );
            $sparklines = $this->getTrends($sparklineRange);

            return [
                'dau' => [
                    'value'           => $dau,
                    'logged_in_users' => $todayRow['logged_in_users'],
                    'reading_users'   => $todayRow['reading_users'],
                    'delta'           => DateRange::formatDelta($dau, $prevDau),
                    'sparkline'       => $sparklines['dau'],
                ],
                'wau' => [
                    'value'     => $wau,
                    'delta'     => DateRange::formatDelta($wau, $prevWau),
                    'sparkline' => $sparklines['dau'],
                ],
                'mau' => [
                    'value'     => $mau,
                    'delta'     => DateRange::formatDelta($mau, $prevMau),
                    'sparkline' => $sparklines['dau'],
                ],
                'stickiness' => [
                    'value'     => $stickiness,
                    'unit'      => '%',
                    'delta'     => DateRange::formatDelta($stickiness, $prevStickiness),
                    'sparkline' => $sparklines['dau'],
                ],
                'new_users' => [
                    'value'     => $newUsers,
                    'delta'     => DateRange::formatDelta($newUsers, $prevNewUsers),
                    'sparkline' => $sparklines['new_users'],
                ],
                'reading_hours' => [
                    'value'        => $readingHours,
                    'minutes_read' => $minutesRead,
                    'formatted'    => $this->formatMinutesUz($minutesRead),
                    'delta'        => DateRange::formatDelta($minutesRead, $prevMinutesRead),
                    'sparkline'    => $sparklines['minutes_read'],
                ],
                'active_readers' => [
                    'value'       => $activeReaders,
                    'today_value' => $todayRow['active_readers'],
                    'min_minutes' => $minMinutes,
                    'delta'       => DateRange::formatDelta($activeReaders, $prevActiveReaders),
                    'sparkline'   => $sparklines['active_readers'],
                ],
                'books_finished' => [
                    'value'          => $booksFinished,
                    'total_all_time' => $totalBooksFinishedAllTime,
                    'delta'          => DateRange::formatDelta($booksFinished, $prevBooksFinished),
                    'sparkline'      => $sparklines['books_finished'],
                ],
            ];
        });
    }

    /**
     * Tarixiy grafiklar: o'tgan kunlar `daily_metrics` jadvalidan olinadi,
     * bugungi kun (va jadvalda hali snapshot bo'lmagan kunlar) jonli hisoblanadi.
     */
    public function getTrends(DateRange $range, bool $forceRefresh = false): array
    {
        $cacheKey = $range->cacheKey('overview_trends');
        $ttl = (int) config('analytics.cache_ttl', 300);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($range) {
            $todayStr = Carbon::now($this->timezone)->toDateString();

            $snapshots = DailyMetric::whereBetween('date', [$range->startDate(), $range->endDate()])
                ->get()
                ->keyBy(function (DailyMetric $item) {
                    return $item->date instanceof Carbon ? $item->date->toDateString() : (string) $item->date;
                });

            $labels = [];
            $dates = [];
            $dau = [];
            $loggedInUsers = [];
            $readingUsers = [];
            $activeReaders = [];
            $newUsers = [];
            $onboarded = [];
            $minutesRead = [];
            $readingHours = [];
            $booksFinished = [];
            $quizAttempts = [];
            $pointsIssued = [];
            $coinsIssued = [];
            $chatMessages = [];
            $aiMessages = [];
            $aiTokens = [];

            foreach ($range->dateStrings() as $dateStr) {
                $labels[] = Carbon::createFromFormat('Y-m-d', $dateStr, $this->timezone)->format('d.m');
                $dates[] = $dateStr;

                // "Bugun" doim jonli so'rovdan; o'tgan kunlar esa daily_metrics snapshot'dan (yoki fallback jonli)
                if ($dateStr === $todayStr || !$snapshots->has($dateStr)) {
                    $row = $this->computeDailyRow($dateStr);
                } else {
                    $snap = $snapshots->get($dateStr);
                    $row = [
                        'dau'             => (int) $snap->dau,
                        'logged_in_users' => (int) $snap->logged_in_users,
                        'reading_users'   => (int) $snap->reading_users,
                        'active_readers'  => (int) $snap->active_readers,
                        'new_users'       => (int) $snap->new_users,
                        'onboarded'       => (int) $snap->onboarded,
                        'minutes_read'    => (int) $snap->minutes_read,
                        'books_finished'  => (int) $snap->books_finished,
                        'quiz_attempts'   => (int) $snap->quiz_attempts,
                        'points_issued'   => (int) $snap->points_issued,
                        'coins_issued'    => (int) $snap->coins_issued,
                        'chat_messages'   => (int) $snap->chat_messages,
                        'ai_messages'     => (int) $snap->ai_messages,
                        'ai_tokens'       => (int) $snap->ai_tokens,
                    ];
                }

                $dau[] = $row['dau'];
                $loggedInUsers[] = $row['logged_in_users'];
                $readingUsers[] = $row['reading_users'];
                $activeReaders[] = $row['active_readers'];
                $newUsers[] = $row['new_users'];
                $onboarded[] = $row['onboarded'];
                $minutesRead[] = $row['minutes_read'];
                $readingHours[] = round($row['minutes_read'] / 60, 1);
                $booksFinished[] = $row['books_finished'];
                $quizAttempts[] = $row['quiz_attempts'];
                $pointsIssued[] = $row['points_issued'];
                $coinsIssued[] = $row['coins_issued'];
                $chatMessages[] = $row['chat_messages'];
                $aiMessages[] = $row['ai_messages'];
                $aiTokens[] = $row['ai_tokens'];
            }

            return [
                'dates'           => $dates,
                'labels'          => $labels,
                'dau'             => $dau,
                'logged_in_users' => $loggedInUsers,
                'reading_users'   => $readingUsers,
                'active_readers'  => $activeReaders,
                'new_users'       => $newUsers,
                'onboarded'       => $onboarded,
                'minutes_read'    => $minutesRead,
                'reading_hours'   => $readingHours,
                'books_finished'  => $booksFinished,
                'quiz_attempts'   => $quizAttempts,
                'points_issued'   => $pointsIssued,
                'coins_issued'    => $coinsIssued,
                'chat_messages'   => $chatMessages,
                'ai_messages'     => $aiMessages,
                'ai_tokens'       => $aiTokens,
            ];
        });
    }

    /**
     * Mavjud /admin (admin.dashboard) sahifasi uchun keshlangan ma'lumotlar.
     * online_today xatosi tuzatilgan: haqiqiy 0 endi 0 qaytaradi (max(..., 1) emas).
     */
    public function getAdminDashboardData(bool $forceRefresh = false): array
    {
        $todayStr = Carbon::now($this->timezone)->toDateString();
        $cacheKey = 'analytics:admin_dashboard:' . $todayStr;
        $ttl = (int) config('analytics.cache_ttl', 300);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($todayStr) {
            $now = Carbon::now($this->timezone);

            $totalUsers    = User::count();
            $totalStudents = User::where('role', 'student')->count();
            $totalTeachers = User::where('role', 'teacher')->count();
            $totalAdmins   = User::where('role', 'admin')->count();
            $totalBooks    = Book::count();
            $activeBooks   = Book::where('is_active', true)->count();

            $thisMonthStart = $now->copy()->startOfMonth()->format('Y-m-d H:i:s');
            $thisMonthEnd   = $now->copy()->endOfMonth()->format('Y-m-d H:i:s');
            $prevMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth()->format('Y-m-d H:i:s');
            $prevMonthEnd   = $now->copy()->subMonthNoOverflow()->endOfMonth()->format('Y-m-d H:i:s');

            $newUsersThisMonth = User::whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count();
            $newUsersPrevMonth = User::whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])->count();

            if ($newUsersPrevMonth > 0) {
                $userGrowthPct = (int) round((($newUsersThisMonth - $newUsersPrevMonth) / $newUsersPrevMonth) * 100);
            } else {
                $userGrowthPct = $newUsersThisMonth > 0 ? 100 : 0;
            }

            $studentPct     = $totalUsers > 0 ? (int) round(($totalStudents / $totalUsers) * 100) : 0;
            $teacherPct     = $totalUsers > 0 ? (int) round(($totalTeachers / $totalUsers) * 100) : 0;
            $activeBooksPct = $totalBooks > 0 ? (int) round(($activeBooks / $totalBooks) * 100) : 0;

            $totalReadingMinutes = (int) DailyActivity::sum('minutes_read');
            $totalReadingHours   = round($totalReadingMinutes / 60, 1);

            // TUZATILDI: haqiqiy 0 ni 1 deb ko'rsatgan max($todayUsersCount, 1) xatosi olib tashlandi
            $onlineToday = (int) DailyActivity::where('activity_date', $todayStr)
                ->where(function ($q) {
                    $q->where('logged_in', true)
                      ->orWhere('minutes_read', '>', 0);
                })
                ->distinct('user_id')
                ->count('user_id');

            $totalGroups = Group::count();
            $usersWithGroupsCount = Group::distinct('created_by')->count('created_by');

            $usersWithThemesCount = UserProfile::whereNotNull('unlocked_items')
                ->where('unlocked_items', '!=', '[]')
                ->where('unlocked_items', '!=', 'null')
                ->count();
            $activeThemesCount = UserProfile::whereNotNull('profile_theme')->count();

            $coinsSpentOnThemes = abs((int) CoinTransaction::where('source', 'spend')
                ->where(function ($q) {
                    $q->where('description', 'like', '%profil vizual%')
                      ->orWhere('description', 'like', '%uslubi xaridi%')
                      ->orWhere('description', 'like', '%fon%');
                })->sum('coins'));

            $themePurchasesCount = CoinTransaction::where('source', 'spend')
                ->where(function ($q) {
                    $q->where('description', 'like', '%profil vizual%')
                      ->orWhere('description', 'like', '%uslubi xaridi%')
                      ->orWhere('description', 'like', '%fon%');
                })->count();

            $stats = [
                'total_users'             => $totalUsers,
                'total_students'          => $totalStudents,
                'total_teachers'          => $totalTeachers,
                'total_admins'            => $totalAdmins,
                'total_books'             => $totalBooks,
                'active_books'            => $activeBooks,
                'weekly_books'            => Book::where('created_at', '>=', $now->copy()->subDays(7))->count(),
                'monthly_books'           => Book::where('created_at', '>=', $now->copy()->subDays(30))->count(),
                'new_users_this_month'    => $newUsersThisMonth,
                'new_users_prev_month'    => $newUsersPrevMonth,
                'user_growth_pct'         => $userGrowthPct,
                'student_pct'             => $studentPct,
                'teacher_pct'             => $teacherPct,
                'active_books_pct'        => $activeBooksPct,
                'total_reading_minutes'   => $totalReadingMinutes,
                'total_reading_hours'     => $totalReadingHours,
                'total_quizzes'           => Quiz::count(),
                'total_groups'            => $totalGroups,
                'users_with_groups_count' => $usersWithGroupsCount,
                'users_with_themes_count' => $usersWithThemesCount,
                'active_themes_count'     => $activeThemesCount,
                'coins_spent_on_themes'   => $coinsSpentOnThemes,
                'theme_purchases_count'   => $themePurchasesCount,
                'total_points'            => (int) User::sum('total_points'),
                'total_coins'             => (int) User::sum('coin_balance'),
                'online_today'            => $onlineToday,
            ];

            $recentUsers = User::with(['roles', 'profile', 'createdGroups'])->latest()->take(8)->get();

            $recentGroups = Group::with(['creator.profile', 'book'])
                ->withCount('members')
                ->latest()
                ->take(6)
                ->get();

            $recentThemePurchases = CoinTransaction::with(['user.profile'])
                ->where('source', 'spend')
                ->where(function ($q) {
                    $q->where('description', 'like', '%profil vizual%')
                      ->orWhere('description', 'like', '%uslubi xaridi%')
                      ->orWhere('description', 'like', '%fon%');
                })
                ->latest()
                ->take(6)
                ->get();

            $usersWithThemes = UserProfile::with('user')
                ->whereNotNull('unlocked_items')
                ->where('unlocked_items', '!=', '[]')
                ->where('unlocked_items', '!=', 'null')
                ->latest('updated_at')
                ->take(6)
                ->get();

            $themeCatalog = ProfileVisualService::THEMES;

            // 30 kunlik ro'yxatdan o'tish trendi
            $start30 = $now->copy()->subDays(29)->startOfDay();
            $signupsRaw = User::selectRaw('DATE(created_at) as day, COUNT(*) as cnt')
                ->where('created_at', '>=', $start30->format('Y-m-d H:i:s'))
                ->groupBy('day')
                ->pluck('cnt', 'day');

            $signupLabels = [];
            $signupData = [];
            $cumulative = max(0, User::where('created_at', '<', $start30->format('Y-m-d H:i:s'))->count());
            foreach (range(29, 0) as $i) {
                $day = $now->copy()->subDays($i);
                $key = $day->toDateString();
                $signupLabels[] = $day->format('d.m');
                $cumulative += (int) ($signupsRaw[$key] ?? 0);
                $signupData[] = $cumulative;
            }

            // 14 kunlik o'qilgan daqiqalar
            $start14Date = $now->copy()->subDays(13)->toDateString();
            $minutesRaw = DailyActivity::selectRaw('activity_date as day, SUM(minutes_read) as m')
                ->where('activity_date', '>=', $start14Date)
                ->groupBy('day')
                ->pluck('m', 'day');

            $minutesLabels = [];
            $minutesData = [];
            foreach (range(13, 0) as $i) {
                $day = $now->copy()->subDays($i);
                $minutesLabels[] = $day->format('d.m');
                $minutesData[] = (int) ($minutesRaw[$day->toDateString()] ?? 0);
            }

            $formatData = [
                (int) BookChapter::count(),
                (int) BookAudio::count(),
                (int) BookVideo::count(),
                (int) Quiz::count(),
            ];

            return compact(
                'stats',
                'recentUsers',
                'recentGroups',
                'recentThemePurchases',
                'usersWithThemes',
                'themeCatalog',
                'signupLabels',
                'signupData',
                'minutesLabels',
                'minutesData',
                'formatData'
            );
        });
    }

    /**
     * Daqiqani o'zbek formatiga o'girish ("3 s 20 daq")
     */
    public function formatMinutesUz(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes . ' daq';
        }

        $hours = intdiv($minutes, 60);
        $rem = $minutes % 60;

        $hoursFormatted = number_format($hours, 0, '.', ' ');

        return $rem > 0
            ? "{$hoursFormatted} s {$rem} daq"
            : "{$hoursFormatted} s";
    }
}
