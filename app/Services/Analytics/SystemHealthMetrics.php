<?php

namespace App\Services\Analytics;

use App\Models\AiChatHistory;
use App\Models\DailyActivity;
use App\Models\DailyMetric;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class SystemHealthMetrics
{
    public const HEARTBEAT_CACHE_KEY = 'analytics:scheduler_heartbeat';

    protected string $timezone;

    public function __construct()
    {
        $this->timezone = config('analytics.timezone', DateRange::TIMEZONE);
    }

    /**
     * Tizim salomatligi ko'rsatkichlari:
     * - Scheduler heartbeat
     * - Queue (jobs / failed_jobs) — faqat database sozlangan bo'lsa, aks holda "not_configured"
     * - Yuklangan fayllar hajmi (storage/app/public)
     * - So'nggi backup — faqat sozlangan bo'lsa, aks holda "not_configured"
     */
    public function getHealth(bool $forceRefresh = false): array
    {
        $cacheKey = 'analytics:system_health';
        $ttl = (int) config('analytics.cache_ttl', 300);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () {
            $now = Carbon::now($this->timezone);

            // 1. Scheduler heartbeat
            $heartbeatIso = Cache::get(self::HEARTBEAT_CACHE_KEY);
            $schedulerStatus = 'not_configured';
            $heartbeatAgeMinutes = null;
            $heartbeatFormatted = null;

            if ($heartbeatIso) {
                $hbTime = Carbon::parse($heartbeatIso)->setTimezone($this->timezone);
                $heartbeatFormatted = $hbTime->format('Y-m-d H:i:s');
                $heartbeatAgeMinutes = (int) $hbTime->diffInMinutes($now);
                $staleMinutes = (int) config('analytics.alerts.scheduler_stale_minutes', 5);
                $schedulerStatus = $heartbeatAgeMinutes <= $staleMinutes ? 'healthy' : 'stale';
            }

            // Kechagi snapshot mavjudligi
            $yesterdayStr = $now->copy()->subDay()->toDateString();
            $hasYesterdaySnapshot = DailyMetric::where('date', $yesterdayStr)->exists();
            $latestSnapshot = DailyMetric::orderByDesc('date')->first(['date', 'updated_at']);

            // 2. Queue (navbat) holati
            $queueConnection = (string) config('queue.default', 'sync');
            $hasJobsTable = Schema::hasTable('jobs');
            $hasFailedJobsTable = Schema::hasTable('failed_jobs');

            $pendingJobs = ($queueConnection === 'database' && $hasJobsTable)
                ? (int) DB::table('jobs')->count()
                : null;
            $failedJobs = $hasFailedJobsTable
                ? (int) DB::table('failed_jobs')->count()
                : null;

            $queueStatus = $queueConnection === 'database' && $hasJobsTable
                ? 'configured'
                : 'not_configured';

            // 3. Yuklangan fayllar hajmi (storage/app/public)
            $storagePath = storage_path('app/public');
            $storageBytes = 0;
            $filesCount = 0;
            if (File::exists($storagePath)) {
                foreach (File::allFiles($storagePath) as $file) {
                    $storageBytes += $file->getSize();
                    $filesCount++;
                }
            }
            $storageSizeMb = round($storageBytes / 1024 / 1024, 2);

            // 4. Backup holati (faqat spatie/laravel-backup yoki storage/app/backups mavjud bo'lsa)
            $backupConfig = config('backup');
            $backupDir = storage_path('app/backups');
            $backupStatus = 'not_configured';
            $lastBackupAt = null;
            $lastBackupSizeMb = null;

            if ($backupConfig !== null || File::isDirectory($backupDir)) {
                $backupFiles = File::isDirectory($backupDir) ? File::allFiles($backupDir) : [];
                if (!empty($backupFiles)) {
                    usort($backupFiles, fn ($a, $b) => $b->getMTime() <=> $a->getMTime());
                    $latestFile = $backupFiles[0];
                    $backupStatus = 'configured';
                    $lastBackupAt = Carbon::createFromTimestamp($latestFile->getMTime(), $this->timezone)->format('Y-m-d H:i');
                    $lastBackupSizeMb = round($latestFile->getSize() / 1024 / 1024, 2);
                } else {
                    $backupStatus = $backupConfig !== null ? 'empty' : 'not_configured';
                }
            }

            return [
                'scheduler' => [
                    'status'                 => $schedulerStatus,
                    'last_heartbeat_at'      => $heartbeatFormatted,
                    'age_minutes'            => $heartbeatAgeMinutes,
                    'has_yesterday_snapshot' => $hasYesterdaySnapshot,
                    'latest_snapshot_date'   => $latestSnapshot
                        ? ($latestSnapshot->date instanceof Carbon ? $latestSnapshot->date->toDateString() : (string) $latestSnapshot->date)
                        : null,
                ],
                'queue' => [
                    'connection'   => $queueConnection,
                    'status'       => $queueStatus,
                    'pending_jobs' => $pendingJobs,
                    'failed_jobs'  => $failedJobs,
                ],
                'storage' => [
                    'size_mb'     => $storageSizeMb,
                    'size_bytes'  => $storageBytes,
                    'files_count' => $filesCount,
                ],
                'backup' => [
                    'status'       => $backupStatus,
                    'last_at'      => $lastBackupAt,
                    'last_size_mb' => $lastBackupSizeMb,
                ],
            ];
        });
    }

    /**
     * ★ "Diqqat talab qiladi" ogohlantirishlar paneli (7-bo'lim):
     * 1. DAU oldingi 7 kunlik o'rtachadan 30%+ tushgan
     * 2. Kechagi snapshot yo'q
     * 3. Scheduler heartbeat 5 daqiqadan eski (heartbeat mavjud bo'lsa)
     * 4. 24 soatdan oshgan ochiq shikoyat
     * 5. AI token kunlik byudjeti oshgan (config)
     * 6. failed_jobs > 0
     */
    public function getAlerts(?Carbon $referenceNow = null): array
    {
        $now = ($referenceNow ? $referenceNow->copy() : Carbon::now($this->timezone))
            ->setTimezone($this->timezone);
        $todayStr = $now->toDateString();
        $yesterdayStr = $now->copy()->subDay()->toDateString();

        $alerts = [];

        // 1. DAU oldingi 7 kunlik o'rtachadan 30%+ tushgan
        $dauDropThreshold = (int) config('analytics.alerts.dau_drop_percent', 30);
        $todayDau = (int) DailyActivity::where('activity_date', $todayStr)
            ->where(function ($q) {
                $q->where('logged_in', true)
                  ->orWhere('minutes_read', '>', 0);
            })
            ->distinct('user_id')
            ->count('user_id');

        $prev7Start = $now->copy()->subDays(7)->toDateString();
        $prev7End = $yesterdayStr;
        $prev7Rows = DailyActivity::select([
            'activity_date',
            DB::raw('COUNT(DISTINCT user_id) as dau'),
        ])
            ->whereBetween('activity_date', [$prev7Start, $prev7End])
            ->where(function ($q) {
                $q->where('logged_in', true)
                  ->orWhere('minutes_read', '>', 0);
            })
            ->groupBy('activity_date')
            ->pluck('dau');

        $prev7Sum = (int) $prev7Rows->sum();
        $prev7Avg = round($prev7Sum / 7.0, 1);

        if ($prev7Avg >= 1.0) {
            $dropPct = round((($prev7Avg - $todayDau) / $prev7Avg) * 100, 1);
            if ($dropPct >= $dauDropThreshold) {
                $alerts[] = [
                    'key'          => 'dau_drop',
                    'severity'     => 'warning',
                    'title'        => "DAU 7 kunlik o'rtachadan {$dropPct}% ga past",
                    'description'  => "Bugungi faol foydalanuvchilar ({$todayDau} ta) so'nggi 7 kunlik o'rtacha ko'rsatkichdan ({$prev7Avg} ta) sezilarli kam. Foydalanuvchilar tabida kohorta va yo'qolish xavfini tekshiring.",
                    'action_label' => 'Foydalanuvchilar tahlili',
                    'action_url'   => route('admin.analytics', ['tab' => 'users']),
                ];
            }
        }

        // 2. Kechagi snapshot yo'q
        $hasYesterdaySnapshot = DailyMetric::where('date', $yesterdayStr)->exists();
        if (!$hasYesterdaySnapshot) {
            $alerts[] = [
                'key'          => 'missing_snapshot',
                'severity'     => 'warning',
                'title'        => "Kechagi ({$yesterdayStr}) analitika snapshot topilmadi",
                'description'  => "`daily_metrics` jadvalida kechagi kun uchun yozuv yo'q. Cron (`schedule:run`) ishlayotganini tekshiring yoki `php artisan analytics:snapshot` buyrug'ini ishga tushiring.",
                'action_label' => 'Tizim salomatligi',
                'action_url'   => route('admin.analytics', ['tab' => 'ai-system']),
            ];
        }

        // 3. Scheduler heartbeat 5 daqiqadan eski (agar heartbeat yozilgan bo'lsa)
        $heartbeatIso = Cache::get(self::HEARTBEAT_CACHE_KEY);
        if ($heartbeatIso) {
            $staleThreshold = (int) config('analytics.alerts.scheduler_stale_minutes', 5);
            $ageMinutes = (int) Carbon::parse($heartbeatIso)->diffInMinutes($now);
            if ($ageMinutes > $staleThreshold) {
                $alerts[] = [
                    'key'          => 'scheduler_stale',
                    'severity'     => 'danger',
                    'title'        => "Scheduler heartbeat {$ageMinutes} daqiqadan beri yangilanmagan",
                    'description'  => "Serverda `* * * * * php artisan schedule:run` cron vazifasi to'xtab qolgan bo'lishi mumkin. Tizim tabini tekshiring.",
                    'action_label' => 'Tizim tabini ochish',
                    'action_url'   => route('admin.analytics', ['tab' => 'ai-system']),
                ];
            }
        }

        // 4. 24 soatdan oshgan ochiq shikoyat
        $staleHours = (int) config('analytics.alerts.report_stale_hours', 24);
        $staleCutoff = $now->copy()->subHours($staleHours)->format('Y-m-d H:i:s');
        $staleReportsCount = (int) Report::where('status', 'pending')
            ->where('created_at', '<=', $staleCutoff)
            ->count();

        if ($staleReportsCount > 0) {
            $alerts[] = [
                'key'          => 'stale_reports',
                'severity'     => 'danger',
                'title'        => "{$staleReportsCount} ta ochiq shikoyat {$staleHours} soatdan oshdi",
                'description'  => "Foydalanuvchilardan kelgan shikoyatlar ko'rib chiqilishini kutmoqda. Shikoyatlar bo'limiga o'tib ularni hal qiling.",
                'action_label' => "Shikoyatlarni ko'rish",
                'action_url'   => route('admin.reports.index'),
            ];
        }

        // 5. AI token kunlik byudjeti oshgan
        $dailyBudget = (int) config('analytics.ai_daily_token_budget', 100000);
        if ($dailyBudget > 0) {
            $todayTokens = (int) AiChatHistory::whereBetween('created_at', [
                $now->copy()->startOfDay()->format('Y-m-d H:i:s'),
                $now->copy()->endOfDay()->format('Y-m-d H:i:s'),
            ])->sum('tokens_used');

            if ($todayTokens > $dailyBudget) {
                $alerts[] = [
                    'key'          => 'ai_budget_exceeded',
                    'severity'     => 'warning',
                    'title'        => 'Kunlik AI token byudjeti oshib ketdi',
                    'description'  => sprintf(
                        "Bugun %s token sarflandi (kunlik me'yor: %s token). AI va Tizim tabida provayder hamda kitoblar kesimini tekshiring.",
                        number_format($todayTokens, 0, '.', ' '),
                        number_format($dailyBudget, 0, '.', ' ')
                    ),
                    'action_label' => 'AI tahlilini ochish',
                    'action_url'   => route('admin.analytics', ['tab' => 'ai-system']),
                ];
            }
        }

        // 6. failed_jobs > 0
        if (Schema::hasTable('failed_jobs')) {
            $failedJobsCount = (int) DB::table('failed_jobs')->count();
            if ($failedJobsCount > 0) {
                $alerts[] = [
                    'key'          => 'failed_jobs',
                    'severity'     => 'danger',
                    'title'        => "Navbatda {$failedJobsCount} ta xatoga uchragan vazifa (failed_jobs) bor",
                    'description'  => "Orqa fon vazifalarida xatolik yuz bergan. `php artisan queue:failed` orqali xatolarni tekshiring.",
                    'action_label' => 'Tizim salomatligi',
                    'action_url'   => route('admin.analytics', ['tab' => 'ai-system']),
                ];
            }
        }

        return $alerts;
    }
}
