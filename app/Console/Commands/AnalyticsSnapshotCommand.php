<?php

namespace App\Console\Commands;

use App\Models\DailyActivity;
use App\Models\DailyMetric;
use App\Models\ReadingSession;
use App\Models\User;
use App\Services\Analytics\DateRange;
use App\Services\Analytics\OverviewMetrics;
use App\Services\Analytics\SystemHealthMetrics;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AnalyticsSnapshotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:snapshot
                            {--from= : Boshlanish sanasi (YYYY-MM-DD)}
                            {--to= : Tugash sanasi (YYYY-MM-DD)}
                            {--backfill : Mavjud jadvallardagi eng eski sanadan kechagacha barcha kunlarni to\'ldirish}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kunlik analitika ko\'rsatkichlarini daily_metrics jadvaliga idempotent tarzda (upsert) yozish';

    public function handle(OverviewMetrics $overview): int
    {
        $tz = config('analytics.timezone', DateRange::TIMEZONE);
        $now = Carbon::now($tz);
        $yesterday = $now->copy()->subDay()->startOfDay();

        $fromOpt = $this->option('from');
        $toOpt = $this->option('to');
        $isBackfill = (bool) $this->option('backfill');

        if ($isBackfill && !$fromOpt) {
            $earliestDates = array_filter([
                User::min('created_at'),
                DailyActivity::min('activity_date'),
                ReadingSession::min('session_date'),
            ]);

            if (!empty($earliestDates)) {
                $minRaw = min($earliestDates);
                $start = Carbon::parse($minRaw, $tz)->setTimezone($tz)->startOfDay();
                // Backfill maksimal 365 kun bilan chegaralanadi
                if ($start->diffInDays($yesterday) > 364) {
                    $start = $yesterday->copy()->subDays(364)->startOfDay();
                }
            } else {
                $start = $yesterday->copy()->subDays(29)->startOfDay();
            }
            $end = $toOpt
                ? Carbon::createFromFormat('Y-m-d', (string) $toOpt, $tz)->startOfDay()
                : $yesterday->copy();
        } else {
            $start = $fromOpt
                ? Carbon::createFromFormat('Y-m-d', (string) $fromOpt, $tz)->startOfDay()
                : $yesterday->copy();

            $end = $toOpt
                ? Carbon::createFromFormat('Y-m-d', (string) $toOpt, $tz)->startOfDay()
                : ($fromOpt ? $start->copy() : $yesterday->copy());
        }

        if ($end->lt($start)) {
            $this->error("Tugash sanasi ({$end->toDateString()}) boshlanish sanasidan ({$start->toDateString()}) oldin bo'lishi mumkin emas.");
            return self::FAILURE;
        }

        $dates = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dates[] = $cursor->toDateString();
            $cursor->addDay();
        }

        $totalDays = count($dates);
        $this->info("Analitika snapshot boshlandi: {$start->toDateString()} -> {$end->toDateString()} ({$totalDays} kun, {$tz})");

        $nowTimestamp = Carbon::now($tz)->format('Y-m-d H:i:s');
        $chunks = array_chunk($dates, 15);
        $processed = 0;

        foreach ($chunks as $dateChunk) {
            $rows = [];
            foreach ($dateChunk as $dateStr) {
                $row = $overview->computeDailyRow($dateStr);
                $row['created_at'] = $nowTimestamp;
                $row['updated_at'] = $nowTimestamp;
                $rows[] = $row;
                $processed++;
            }

            DailyMetric::upsert(
                $rows,
                ['date'],
                [
                    'dau',
                    'logged_in_users',
                    'reading_users',
                    'active_readers',
                    'new_users',
                    'onboarded',
                    'minutes_read',
                    'books_finished',
                    'quiz_attempts',
                    'points_issued',
                    'coins_issued',
                    'chat_messages',
                    'ai_messages',
                    'ai_tokens',
                    'updated_at',
                ]
            );
        }

        // Scheduler heartbeat yangilash
        Cache::put(
            SystemHealthMetrics::HEARTBEAT_CACHE_KEY,
            Carbon::now($tz)->toIso8601String(),
            86400 * 7
        );

        $this->info("Tayyor: {$processed} kunlik snapshot idempotent (upsert) saqlandi.");

        return self::SUCCESS;
    }
}
