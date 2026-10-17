<?php

namespace App\Services\Analytics;

use App\Models\DailyActivity;
use App\Models\ReadingSession;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserStreak;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RetentionMetrics
{
    protected string $timezone;

    public function __construct()
    {
        $this->timezone = config('analytics.timezone', DateRange::TIMEZONE);
    }

    /**
     * Kunlik kohortalar bo'yicha D1 / D7 / D30 retention.
     * Hali n kun o'tmagan kohortalar uchun qiymat NULL (N/A) qaytariladi, 0 emas!
     */
    public function getDailyCohorts(DateRange $range, ?Carbon $referenceToday = null): array
    {
        $today = ($referenceToday ? $referenceToday->copy() : Carbon::now($this->timezone))
            ->setTimezone($this->timezone)
            ->startOfDay();

        $startStr = $range->start->format('Y-m-d H:i:s');
        $endStr = $range->end->format('Y-m-d H:i:s');

        $users = User::select(['id', 'created_at'])
            ->whereBetween('created_at', [$startStr, $endStr])
            ->orderBy('created_at')
            ->get();

        // Foydalanuvchilarni Toshkent vaqti bo'yicha ro'yxatdan o'tgan sanasiga guruhlash
        $cohortsByDate = [];
        $allUserIds = [];
        foreach ($users as $user) {
            $regDate = Carbon::parse($user->created_at)->setTimezone($this->timezone)->toDateString();
            $cohortsByDate[$regDate][] = (int) $user->id;
            $allUserIds[] = (int) $user->id;
        }

        // Barcha tegishli foydalanuvchilarning faol kunlarini bitta so'rovda olish
        $activeMap = [];
        if (!empty($allUserIds)) {
            $maxTargetDate = $range->end->copy()->addDays(30)->toDateString();
            $activities = DailyActivity::select(['user_id', 'activity_date'])
                ->whereIn('user_id', $allUserIds)
                ->whereBetween('activity_date', [$range->startDate(), $maxTargetDate])
                ->where(function ($q) {
                    $q->where('logged_in', true)
                      ->orWhere('minutes_read', '>', 0);
                })
                ->get();

            foreach ($activities as $act) {
                $dStr = $act->activity_date instanceof Carbon
                    ? $act->activity_date->toDateString()
                    : substr((string) $act->activity_date, 0, 10);
                $activeMap[(int) $act->user_id][$dStr] = true;
            }
        }

        $rows = [];
        foreach ($range->dateStrings() as $cohortDateStr) {
            $cohortUserIds = $cohortsByDate[$cohortDateStr] ?? [];
            $cohortSize = count($cohortUserIds);
            $cohortCarbon = Carbon::createFromFormat('Y-m-d', $cohortDateStr, $this->timezone)->startOfDay();

            $row = [
                'cohort_date' => $cohortDateStr,
                'label'       => $cohortCarbon->format('d.m.Y'),
                'cohort_size' => $cohortSize,
                'd1'          => null,
                'd7'          => null,
                'd30'         => null,
                'd1_count'    => null,
                'd7_count'    => null,
                'd30_count'   => null,
            ];

            foreach ([1 => 'd1', 7 => 'd7', 30 => 'd30'] as $offsetDays => $key) {
                $targetCarbon = $cohortCarbon->copy()->addDays($offsetDays);

                // Hali n kun o'tmagan bo'lsa -> null (N/A), 0 EMAS!
                if ($targetCarbon->gt($today)) {
                    $row[$key] = null;
                    $row[$key . '_count'] = null;
                    continue;
                }

                if ($cohortSize === 0) {
                    $row[$key] = null;
                    $row[$key . '_count'] = 0;
                    continue;
                }

                $targetDateStr = $targetCarbon->toDateString();
                $retained = 0;
                foreach ($cohortUserIds as $uid) {
                    if (!empty($activeMap[$uid][$targetDateStr])) {
                        $retained++;
                    }
                }

                $row[$key . '_count'] = $retained;
                $row[$key] = round(($retained / $cohortSize) * 100, 1);
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Haftalik kohortalar bo'yicha D1 / D7 / D30 retention (Foydalanuvchilar tabidagi issiqlik xaritasi uchun).
     */
    public function getWeeklyCohorts(DateRange $range, ?Carbon $referenceToday = null, bool $forceRefresh = false): array
    {
        $cacheKey = $range->cacheKey('retention_weekly');
        $ttl = (int) config('analytics.cache_ttl', 300);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($range, $referenceToday) {
            $daily = $this->getDailyCohorts($range, $referenceToday);

            $weeks = [];
            foreach ($daily as $dayRow) {
                $date = Carbon::createFromFormat('Y-m-d', $dayRow['cohort_date'], $this->timezone);
                $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
                $weekEnd = $date->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();

                if (!isset($weeks[$weekStart])) {
                    $weeks[$weekStart] = [
                        'week_start'  => $weekStart,
                        'week_end'    => $weekEnd,
                        'label'       => Carbon::parse($weekStart)->format('d.m') . ' – ' . Carbon::parse($weekEnd)->format('d.m'),
                        'cohort_size' => 0,
                        'd1_eligible' => 0,
                        'd1_retained' => 0,
                        'd7_eligible' => 0,
                        'd7_retained' => 0,
                        'd30_eligible'=> 0,
                        'd30_retained'=> 0,
                    ];
                }

                $weeks[$weekStart]['cohort_size'] += $dayRow['cohort_size'];

                foreach (['d1', 'd7', 'd30'] as $k) {
                    if ($dayRow[$k . '_count'] !== null && $dayRow['cohort_size'] > 0) {
                        $weeks[$weekStart][$k . '_eligible'] += $dayRow['cohort_size'];
                        $weeks[$weekStart][$k . '_retained'] += $dayRow[$k . '_count'];
                    }
                }
            }

            $result = [];
            foreach ($weeks as $w) {
                $result[] = [
                    'week_start'  => $w['week_start'],
                    'week_end'    => $w['week_end'],
                    'label'       => $w['label'],
                    'cohort_size' => $w['cohort_size'],
                    'd1'          => $w['d1_eligible'] > 0 ? round(($w['d1_retained'] / $w['d1_eligible']) * 100, 1) : null,
                    'd7'          => $w['d7_eligible'] > 0 ? round(($w['d7_retained'] / $w['d7_eligible']) * 100, 1) : null,
                    'd30'         => $w['d30_eligible'] > 0 ? round(($w['d30_retained'] / $w['d30_eligible']) * 100, 1) : null,
                    'd1_count'    => $w['d1_eligible'] > 0 ? $w['d1_retained'] : null,
                    'd7_count'    => $w['d7_eligible'] > 0 ? $w['d7_retained'] : null,
                    'd30_count'   => $w['d30_eligible'] > 0 ? $w['d30_retained'] : null,
                ];
            }

            return $result;
        });
    }

    /**
     * Voronka (Funnel):
     * 1. Ro'yxatdan o'tdi
     * 2. Onboarding tugadi (EnsureOnboardingIsComplete bilan bir xil: user_profiles.reading_place != '')
     * 3. Birinchi o'qish (minutes_read > 0)
     * 4. 2-kuni qaytdi (ro'yxatdan o'tgan kundan keyingi kunda faol)
     * 5. 7 kunlik streak (longest_streak >= 7 yoki current_streak >= 7)
     */
    public function getFunnel(DateRange $range, bool $forceRefresh = false): array
    {
        $cacheKey = $range->cacheKey('retention_funnel');
        $ttl = (int) config('analytics.cache_ttl', 300);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($range) {
            $startStr = $range->start->format('Y-m-d H:i:s');
            $endStr = $range->end->format('Y-m-d H:i:s');

            $users = User::select(['id', 'created_at'])
                ->whereBetween('created_at', [$startStr, $endStr])
                ->get();

            $userIds = $users->pluck('id')->map(fn ($id) => (int) $id)->all();
            $registeredCount = count($userIds);

            if ($registeredCount === 0) {
                return [
                    'steps' => [
                        ['key' => 'registered',    'label' => "Ro'yxatdan o'tdi",  'count' => 0, 'percent_of_total' => 0.0, 'percent_of_prev' => 100.0],
                        ['key' => 'onboarded',     'label' => 'Onboarding tugadi', 'count' => 0, 'percent_of_total' => 0.0, 'percent_of_prev' => 0.0],
                        ['key' => 'first_reading', 'label' => "Birinchi o'qish",   'count' => 0, 'percent_of_total' => 0.0, 'percent_of_prev' => 0.0],
                        ['key' => 'returned_d1',   'label' => '2-kuni qaytdi',     'count' => 0, 'percent_of_total' => 0.0, 'percent_of_prev' => 0.0],
                        ['key' => 'streak_7',      'label' => '7 kunlik streak',   'count' => 0, 'percent_of_total' => 0.0, 'percent_of_prev' => 0.0],
                    ],
                ];
            }

            // 2. Onboarding tugaganlar (EnsureOnboardingIsComplete: $user->profile && !empty($user->profile->reading_place))
            $onboardedIds = UserProfile::whereIn('user_id', $userIds)
                ->whereNotNull('reading_place')
                ->where('reading_place', '!=', '')
                ->distinct('user_id')
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->all();
            $onboardedCount = count($onboardedIds);

            // 3. Birinchi o'qish (onboarding tugatganlar orasidan minutes_read > 0 bo'lganlar)
            $firstReadingIds = [];
            if (!empty($onboardedIds)) {
                $readersFromDaily = DailyActivity::whereIn('user_id', $onboardedIds)
                    ->where('minutes_read', '>', 0)
                    ->distinct('user_id')
                    ->pluck('user_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
                $readersFromSessions = ReadingSession::whereIn('user_id', $onboardedIds)
                    ->where('minutes_read', '>', 0)
                    ->distinct('user_id')
                    ->pluck('user_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
                $firstReadingIds = array_values(array_unique(array_merge($readersFromDaily, $readersFromSessions)));
            }
            $firstReadingCount = count($firstReadingIds);

            // 4. 2-kuni qaytdi (birinchi o'qishni amalga oshirganlar orasidan ro'yxatdan o'tgan sanadan +1 kunda faol bo'lganlar)
            $returnedD1Ids = [];
            if (!empty($firstReadingIds)) {
                $regDateByUser = [];
                foreach ($users as $u) {
                    $regDateByUser[(int) $u->id] = Carbon::parse($u->created_at)
                        ->setTimezone($this->timezone)
                        ->addDay()
                        ->toDateString();
                }

                $activities = DailyActivity::select(['user_id', 'activity_date'])
                    ->whereIn('user_id', $firstReadingIds)
                    ->where(function ($q) {
                        $q->where('logged_in', true)
                          ->orWhere('minutes_read', '>', 0);
                    })
                    ->get();

                $returnedD1Users = [];
                foreach ($activities as $act) {
                    $uid = (int) $act->user_id;
                    $actDate = $act->activity_date instanceof Carbon
                        ? $act->activity_date->toDateString()
                        : substr((string) $act->activity_date, 0, 10);

                    if (isset($regDateByUser[$uid]) && $regDateByUser[$uid] === $actDate) {
                        $returnedD1Users[$uid] = true;
                    }
                }
                $returnedD1Ids = array_keys($returnedD1Users);
            }
            $returnedD1Count = count($returnedD1Ids);

            // 5. 7 kunlik streak (2-kuni qaytganlar orasidan 7 kunlik streakka yetganlar)
            $streak7Count = 0;
            if (!empty($returnedD1Ids)) {
                $streak7Count = (int) UserStreak::whereIn('user_id', $returnedD1Ids)
                    ->where(function ($q) {
                        $q->where('longest_streak', '>=', 7)
                          ->orWhere('current_streak', '>=', 7);
                    })
                    ->distinct('user_id')
                    ->count('user_id');
            }

            $rawSteps = [
                ['key' => 'registered',    'label' => "Ro'yxatdan o'tdi",  'count' => $registeredCount],
                ['key' => 'onboarded',     'label' => 'Onboarding tugadi', 'count' => $onboardedCount],
                ['key' => 'first_reading', 'label' => "Birinchi o'qish",   'count' => $firstReadingCount],
                ['key' => 'returned_d1',   'label' => '2-kuni qaytdi',     'count' => $returnedD1Count],
                ['key' => 'streak_7',      'label' => '7 kunlik streak',   'count' => $streak7Count],
            ];

            $steps = [];
            $prevCount = $registeredCount;
            foreach ($rawSteps as $idx => $step) {
                $pctTotal = $registeredCount > 0 ? round(($step['count'] / $registeredCount) * 100, 1) : 0.0;
                $pctPrev = $idx === 0
                    ? 100.0
                    : ($prevCount > 0 ? round(($step['count'] / $prevCount) * 100, 1) : 0.0);

                $steps[] = [
                    'key'              => $step['key'],
                    'label'            => $step['label'],
                    'count'            => $step['count'],
                    'percent_of_total' => $pctTotal,
                    'percent_of_prev'  => $pctPrev,
                ];

                $prevCount = max($step['count'], 1);
            }

            return ['steps' => $steps];
        });
    }

    /**
     * Segmentlar: reading_goal va reading_place bo'yicha.
     * Guruhda 5 tadan kam foydalanuvchi bo'lsa ko'rsatilmaydi ("kam ma'lumot").
     */
    public function getSegments(bool $forceRefresh = false): array
    {
        $cacheKey = 'analytics:retention:segments';
        $ttl = (int) config('analytics.cache_ttl', 300);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () {
            $minUsers = (int) config('analytics.segment_min_users', 5);

            return [
                'min_users_threshold' => $minUsers,
                'by_reading_goal'     => $this->buildSegmentBreakdown('reading_goal', $minUsers),
                'by_reading_place'    => $this->buildSegmentBreakdown('reading_place', $minUsers),
            ];
        });
    }

    protected function buildSegmentBreakdown(string $column, int $minUsers): array
    {
        $labelsMap = [
            'home'         => 'Uyda',
            'office'       => 'Ofisda / ishda',
            'university'   => 'O\'quv yurtida',
            'library'      => 'Kutubxonada',
            'travel'       => 'Yo\'lda / transportda',
            'knowledge'    => 'Bilim oshirish',
            'personal_dev' => 'Shaxsiy rivojlanish',
            'exam_prep'    => 'Imtihonga tayyorgarlik',
            'language'     => 'Til o\'rganish',
            'career'       => 'Kasbiy o\'sish',
            'spiritual'    => 'Ma\'naviy ozuqa',
            'other'        => 'Boshqa',
        ];

        $rows = UserProfile::select([$column, DB::raw('COUNT(*) as total')])
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupBy($column)
            ->orderByDesc('total')
            ->get();

        $totalUsers = (int) $rows->sum('total');
        $visible = [];
        $hiddenGroupsCount = 0;
        $hiddenUsersCount = 0;

        foreach ($rows as $row) {
            $count = (int) $row->total;
            $val = (string) $row->{$column};

            if ($count < $minUsers) {
                $hiddenGroupsCount++;
                $hiddenUsersCount += $count;
                continue;
            }

            $visible[] = [
                'segment' => $val,
                'label'   => $labelsMap[$val] ?? $val,
                'count'   => $count,
                'percent' => $totalUsers > 0 ? round(($count / $totalUsers) * 100, 1) : 0.0,
            ];
        }

        return [
            'total_users'  => $totalUsers,
            'items'        => $visible,
            'insufficient' => [
                'has_hidden'          => $hiddenGroupsCount > 0,
                'label'               => 'kam ma\'lumot',
                'hidden_groups_count' => $hiddenGroupsCount,
                'hidden_users_count'  => $hiddenUsersCount,
            ],
        ];
    }

    /**
     * ★ "Yo'qolish xavfi": streak >= 7 bo'lib, oxirgi 2+ kun faol bo'lmaganlar.
     * Maxfiylik qoidasi (6-bo'lim): faqat username + admin foydalanuvchi sahifasiga havola (email/ism YO'Q).
     */
    public function getChurnRiskUsers(int $limit = 50, ?Carbon $referenceToday = null): array
    {
        $minStreak = (int) config('analytics.churn_min_streak', 7);
        $inactiveDays = (int) config('analytics.churn_inactive_days', 2);

        $today = ($referenceToday ? $referenceToday->copy() : Carbon::now($this->timezone))
            ->setTimezone($this->timezone)
            ->startOfDay();

        // Oxirgi 2 kun ichida faol bo'lmagan = oxirgi faollik sanasi <= (today - 2 kun)
        $cutoffDate = $today->copy()->subDays($inactiveDays)->toDateString();
        $recentWindowStart = $today->copy()->subDays($inactiveDays - 1)->toDateString();
        $todayStr = $today->toDateString();

        // Oxirgi 2 kun ichida faol bo'lgan foydalanuvchilar ID'lari
        $recentlyActiveUserIds = DailyActivity::whereBetween('activity_date', [$recentWindowStart, $todayStr])
            ->where(function ($q) {
                $q->where('logged_in', true)
                  ->orWhere('minutes_read', '>', 0);
            })
            ->distinct('user_id')
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $query = UserStreak::with(['user:id,username,role'])
            ->where(function ($q) use ($minStreak) {
                $q->where('current_streak', '>=', $minStreak)
                  ->orWhere('longest_streak', '>=', $minStreak);
            })
            ->where(function ($q) use ($cutoffDate) {
                $q->whereNull('last_active_date')
                  ->orWhereDate('last_active_date', '<=', $cutoffDate);
            });

        if (!empty($recentlyActiveUserIds)) {
            $query->whereNotIn('user_id', $recentlyActiveUserIds);
        }

        $totalCount = (clone $query)->count();

        $streaks = $query->orderByDesc('current_streak')
            ->orderByDesc('longest_streak')
            ->limit($limit)
            ->get();

        $items = [];
        foreach ($streaks as $st) {
            if (!$st->user) {
                continue;
            }

            $lastActive = $st->last_active_date
                ? ($st->last_active_date instanceof Carbon ? $st->last_active_date->toDateString() : (string) $st->last_active_date)
                : null;

            $daysInactive = $lastActive
                ? Carbon::createFromFormat('Y-m-d', substr($lastActive, 0, 10), $this->timezone)->startOfDay()->diffInDays($today)
                : null;

            $items[] = [
                'user_id'          => (int) $st->user->id,
                'username'         => $st->user->username,
                'current_streak'   => (int) $st->current_streak,
                'longest_streak'   => (int) $st->longest_streak,
                'last_active_date' => $lastActive,
                'days_inactive'    => $daysInactive,
                'admin_url'        => route('admin.users.edit', $st->user->id),
            ];
        }

        return [
            'count'         => $totalCount,
            'min_streak'    => $minStreak,
            'inactive_days' => $inactiveDays,
            'items'         => $items,
        ];
    }

    /**
     * ★ Soat × hafta kuni faollik issiqlik xaritasi (Asia/Tashkent).
     * Jonli efir va xabarnoma uchun eng qulay vaqtni aniqlaydi.
     */
    public function getActivityHeatmap(DateRange $range, bool $forceRefresh = false): array
    {
        $cacheKey = $range->cacheKey('retention_heatmap');
        $ttl = (int) config('analytics.cache_ttl', 300);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($range) {
            $dayNames = [
                1 => 'Dushanba',
                2 => 'Seshanba',
                3 => 'Chorshanba',
                4 => 'Payshanba',
                5 => 'Juma',
                6 => 'Shanba',
                7 => 'Yakshanba',
            ];

            // 7 kun (1..7 ISO) x 24 soat (0..23) matritsa
            $matrix = [];
            foreach ($dayNames as $isoDay => $name) {
                $matrix[$isoDay] = [
                    'day_iso'  => $isoDay,
                    'day_name' => $name,
                    'hours'    => array_fill(0, 24, 0),
                ];
            }

            $startStr = $range->start->format('Y-m-d H:i:s');
            $endStr = $range->end->format('Y-m-d H:i:s');

            $sessions = ReadingSession::select(['created_at', 'minutes_read'])
                ->whereBetween('created_at', [$startStr, $endStr])
                ->get();

            $maxCell = 0;
            $bestSlot = [
                'day_iso'  => 1,
                'day_name' => 'Dushanba',
                'hour'     => 20,
                'label'    => 'Dushanba, 20:00',
                'count'    => 0,
            ];

            foreach ($sessions as $session) {
                if (!$session->created_at) {
                    continue;
                }
                $dt = Carbon::parse($session->created_at)->setTimezone($this->timezone);
                $isoDay = (int) $dt->dayOfWeekIso; // 1 (Du) .. 7 (Ya)
                $hour = (int) $dt->hour;           // 0 .. 23

                $matrix[$isoDay]['hours'][$hour]++;
                $val = $matrix[$isoDay]['hours'][$hour];

                if ($val > $maxCell) {
                    $maxCell = $val;
                    $bestSlot = [
                        'day_iso'  => $isoDay,
                        'day_name' => $dayNames[$isoDay],
                        'hour'     => $hour,
                        'label'    => sprintf('%s, %02d:00', $dayNames[$isoDay], $hour),
                        'count'    => $val,
                    ];
                }
            }

            return [
                'days'      => array_values($matrix),
                'max_value' => $maxCell,
                'best_slot' => $bestSlot,
                'timezone'  => $this->timezone,
            ];
        });
    }
}
