<?php

namespace App\Services\Analytics;

use App\Models\CoinTransaction;
use App\Models\PointTransaction;
use App\Models\ShopItem;
use App\Models\ShopPurchase;
use App\Models\User;
use App\Models\UserStreak;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EconomyMetrics
{
    protected string $timezone;

    public function __construct()
    {
        $this->timezone = config('analytics.timezone', DateRange::TIMEZONE);
    }

    /**
     * Ball manbalari (point_transactions.source bo'yicha taqsimot)
     */
    public function getPointSources(DateRange $range, bool $forceRefresh = false): array
    {
        $cacheKey = $range->cacheKey('economy_point_sources');
        $ttl = (int) config('analytics.cache_ttl', 300);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($range) {
            $startStr = $range->start->format('Y-m-d H:i:s');
            $endStr = $range->end->format('Y-m-d H:i:s');

            $rows = PointTransaction::select([
                'source',
                DB::raw('SUM(points) as total_points'),
                DB::raw('COUNT(*) as transactions_count'),
            ])
                ->whereBetween('created_at', [$startStr, $endStr])
                ->where('points', '>', 0)
                ->groupBy('source')
                ->orderByDesc('total_points')
                ->get();

            $grandTotal = (int) $rows->sum('total_points');

            $sourceLabels = [
                'reading' => 'Mutolaa',
                'quiz'    => 'Quiz testlar',
                'bonus'   => 'Kunlik bonuslar',
                'badge'   => 'Yutuq nishonlari',
                'streak'  => 'Streak bonusi',
                'admin'   => 'Admin amali',
            ];

            $items = [];
            foreach ($rows as $row) {
                $pts = (int) $row->total_points;
                $src = (string) $row->source;
                $items[] = [
                    'source'             => $src,
                    'label'              => $sourceLabels[$src] ?? ucfirst($src),
                    'points'             => $pts,
                    'transactions_count' => (int) $row->transactions_count,
                    'percent'            => $grandTotal > 0 ? round(($pts / $grandTotal) * 100, 1) : 0.0,
                ];
            }

            return [
                'total_points' => $grandTotal,
                'sources'      => $items,
            ];
        });
    }

    /**
     * Kunlik chiqarilgan ball va tangalar dinamikasi
     */
    public function getDailyIssuance(DateRange $range, bool $forceRefresh = false): array
    {
        $cacheKey = $range->cacheKey('economy_daily_issuance');
        $ttl = (int) config('analytics.cache_ttl', 300);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($range) {
            $startStr = $range->start->format('Y-m-d H:i:s');
            $endStr = $range->end->format('Y-m-d H:i:s');

            $pointsRows = PointTransaction::select(['created_at', 'points'])
                ->whereBetween('created_at', [$startStr, $endStr])
                ->where('points', '>', 0)
                ->get();

            $coinsRows = CoinTransaction::select(['created_at', 'coins'])
                ->whereBetween('created_at', [$startStr, $endStr])
                ->get();

            $pointsByDay = [];
            foreach ($pointsRows as $pr) {
                $d = Carbon::parse($pr->created_at)->setTimezone($this->timezone)->toDateString();
                $pointsByDay[$d] = ($pointsByDay[$d] ?? 0) + (int) $pr->points;
            }

            $coinsIssuedByDay = [];
            $coinsSpentByDay = [];
            foreach ($coinsRows as $cr) {
                $d = Carbon::parse($cr->created_at)->setTimezone($this->timezone)->toDateString();
                $val = (int) $cr->coins;
                if ($val > 0) {
                    $coinsIssuedByDay[$d] = ($coinsIssuedByDay[$d] ?? 0) + $val;
                } elseif ($val < 0) {
                    $coinsSpentByDay[$d] = ($coinsSpentByDay[$d] ?? 0) + abs($val);
                }
            }

            $dates = [];
            $labels = [];
            $pointsIssued = [];
            $coinsIssued = [];
            $coinsSpent = [];

            foreach ($range->dateStrings() as $dateStr) {
                $dates[] = $dateStr;
                $labels[] = Carbon::createFromFormat('Y-m-d', $dateStr, $this->timezone)->format('d.m');
                $pointsIssued[] = (int) ($pointsByDay[$dateStr] ?? 0);
                $coinsIssued[] = (int) ($coinsIssuedByDay[$dateStr] ?? 0);
                $coinsSpent[] = (int) ($coinsSpentByDay[$dateStr] ?? 0);
            }

            return [
                'dates'              => $dates,
                'labels'             => $labels,
                'points_issued'      => $pointsIssued,
                'coins_issued'       => $coinsIssued,
                'coins_spent'        => $coinsSpent,
                'total_points_issued'=> array_sum($pointsIssued),
                'total_coins_issued' => array_sum($coinsIssued),
                'total_coins_spent'  => array_sum($coinsSpent),
            ];
        });
    }

    /**
     * Streak taqsimoti: 1–2 / 3–6 / 7–29 / 30+ kun
     */
    public function getStreakDistribution(bool $forceRefresh = false): array
    {
        $cacheKey = 'analytics:economy:streak_distribution';
        $ttl = (int) config('analytics.cache_ttl', 300);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () {
            $b1_2 = (int) UserStreak::whereBetween('current_streak', [1, 2])->count();
            $b3_6 = (int) UserStreak::whereBetween('current_streak', [3, 6])->count();
            $b7_29 = (int) UserStreak::whereBetween('current_streak', [7, 29])->count();
            $b30Plus = (int) UserStreak::where('current_streak', '>=', 30)->count();

            $totalActiveStreaks = $b1_2 + $b3_6 + $b7_29 + $b30Plus;

            $buckets = [
                ['key' => '1-2',   'label' => '1–2 kun',   'count' => $b1_2],
                ['key' => '3-6',   'label' => '3–6 kun',   'count' => $b3_6],
                ['key' => '7-29',  'label' => '7–29 kun',  'count' => $b7_29],
                ['key' => '30+',   'label' => '30+ kun',   'count' => $b30Plus],
            ];

            foreach ($buckets as &$b) {
                $b['percent'] = $totalActiveStreaks > 0
                    ? round(($b['count'] / $totalActiveStreaks) * 100, 1)
                    : 0.0;
            }
            unset($b);

            return [
                'total_active_streaks' => $totalActiveStreaks,
                'buckets'              => $buckets,
            ];
        });
    }

    /**
     * ★ Suiiste'mol belgilari (Abuse signals):
     * Kunlik ball bo'yicha me'yordan keskin chetga chiqqanlar (z-score >= 3).
     * Faqat "tekshirilsin" ro'yxati, avtomatik jazo YO'Q!
     * Maxfiylik qoidasi (6-bo'lim): faqat username + admin foydalanuvchi sahifasiga havola.
     */
    public function getAbuseSignals(DateRange $range, bool $forceRefresh = false): array
    {
        $cacheKey = $range->cacheKey('economy_abuse_signals');
        $ttl = (int) config('analytics.cache_ttl', 300);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($range) {
            $zThreshold = (float) config('analytics.abuse_z_score_threshold', 3.0);
            $startStr = $range->start->format('Y-m-d H:i:s');
            $endStr = $range->end->format('Y-m-d H:i:s');

            $txs = PointTransaction::select(['user_id', 'points', 'created_at'])
                ->whereBetween('created_at', [$startStr, $endStr])
                ->where('points', '>', 0)
                ->get();

            // (user_id, date) bo'yicha kunlik yig'indilar
            $dailyUserPoints = [];
            foreach ($txs as $tx) {
                $d = Carbon::parse($tx->created_at)->setTimezone($this->timezone)->toDateString();
                $key = $tx->user_id . '|' . $d;
                if (!isset($dailyUserPoints[$key])) {
                    $dailyUserPoints[$key] = [
                        'user_id' => (int) $tx->user_id,
                        'date'    => $d,
                        'points'  => 0,
                    ];
                }
                $dailyUserPoints[$key]['points'] += (int) $tx->points;
            }

            $n = count($dailyUserPoints);
            if ($n < 3) {
                return [
                    'z_threshold' => $zThreshold,
                    'mean'        => 0.0,
                    'std_dev'     => 0.0,
                    'count'       => 0,
                    'items'       => [],
                ];
            }

            $values = array_column($dailyUserPoints, 'points');
            $mean = array_sum($values) / $n;

            $varianceSum = 0.0;
            foreach ($values as $v) {
                $varianceSum += ($v - $mean) ** 2;
            }
            $stdDev = sqrt($varianceSum / $n);

            if ($stdDev <= 0.0) {
                return [
                    'z_threshold' => $zThreshold,
                    'mean'        => round($mean, 1),
                    'std_dev'     => 0.0,
                    'count'       => 0,
                    'items'       => [],
                ];
            }

            $flagged = [];
            $flaggedUserIds = [];
            foreach ($dailyUserPoints as $entry) {
                $z = ($entry['points'] - $mean) / $stdDev;
                if ($z >= $zThreshold) {
                    $entry['z_score'] = round($z, 2);
                    $flagged[] = $entry;
                    $flaggedUserIds[] = $entry['user_id'];
                }
            }

            usort($flagged, fn ($a, $b) => $b['z_score'] <=> $a['z_score']);

            $usersById = !empty($flaggedUserIds)
                ? User::select(['id', 'username', 'is_banned'])->whereIn('id', array_unique($flaggedUserIds))->get()->keyBy('id')
                : collect();

            $items = [];
            foreach ($flagged as $f) {
                $user = $usersById->get($f['user_id']);
                if (!$user) {
                    continue;
                }

                $items[] = [
                    'user_id'   => (int) $user->id,
                    'username'  => $user->username,
                    'date'      => $f['date'],
                    'points'    => $f['points'],
                    'z_score'   => $f['z_score'],
                    'is_banned' => (bool) $user->is_banned,
                    'status'    => 'tekshirilsin',
                    'admin_url' => route('admin.users.edit', $user->id),
                ];
            }

            return [
                'z_threshold' => $zThreshold,
                'mean'        => round($mean, 1),
                'std_dev'     => round($stdDev, 1),
                'count'       => count($items),
                'items'       => $items,
            ];
        });
    }

    /**
     * Do'kon va o'yin iqtisodiyoti ko'rsatkichlari:
     * - aylanmadagi tanga
     * - chiqarilgan va sarflangan tanga
     * - almashtirish hajmi
     * - eng ko'p sotilgan buyumlar
     */
    public function getShopEconomyMetrics(bool $forceRefresh = false): array
    {
        $cacheKey = 'analytics:economy:shop_metrics';
        $ttl = (int) config('analytics.cache_ttl', 300);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () {
            $circulation = (int) User::sum('coin_balance');
            $issuedTotal = (int) CoinTransaction::where('coins', '>', 0)->sum('coins');
            $spentTotal = abs((int) CoinTransaction::where('coins', '<', 0)->sum('coins'));
            $exchangeCoins = (int) CoinTransaction::where('source', 'exchange')->sum('coins');
            $purchasesCount = (int) ShopPurchase::count();
            $refundsCount = (int) CoinTransaction::where('source', 'refund')->count();
            $refundedCoins = (int) CoinTransaction::where('source', 'refund')->sum('coins');

            $topItems = ShopItem::select(['id', 'key', 'category', 'slot', 'name', 'price_coins', 'rarity', 'is_active'])
                ->withCount(['purchases as units_sold' => function ($q) {
                    $q->select(DB::raw('COALESCE(SUM(quantity), 0)'));
                }])
                ->withSum('purchases as total_revenue', 'price_paid')
                ->orderByDesc('units_sold')
                ->take(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'id'            => $item->id,
                        'key'           => $item->key,
                        'name'          => $item->name_text,
                        'category'      => $item->category,
                        'price_coins'   => (int) $item->price_coins,
                        'rarity'        => $item->rarity,
                        'is_active'     => (bool) $item->is_active,
                        'units_sold'    => (int) ($item->units_sold ?? 0),
                        'total_revenue' => (int) ($item->total_revenue ?? 0),
                    ];
                })
                ->toArray();

            return [
                'circulation'     => $circulation,
                'issued_total'    => $issuedTotal,
                'spent_total'     => $spentTotal,
                'exchange_coins'  => $exchangeCoins,
                'purchases_count' => $purchasesCount,
                'refunds_count'   => $refundsCount,
                'refunded_coins'  => $refundedCoins,
                'top_items'       => $topItems,
            ];
        });
    }
}
