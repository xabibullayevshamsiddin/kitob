<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AiMetrics;
use App\Services\Analytics\CommunityMetrics;
use App\Services\Analytics\ContentMetrics;
use App\Services\Analytics\DateRange;
use App\Services\Analytics\EconomyMetrics;
use App\Services\Analytics\OverviewMetrics;
use App\Services\Analytics\RetentionMetrics;
use App\Services\Analytics\SystemHealthMetrics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    public const VALID_TABS = [
        'overview',
        'users',
        'content',
        'economy',
        'community',
        'ai-system',
    ];

    /**
     * Mavjud /admin (admin.dashboard) sahifasi — closure'dan controllerga ko'chirildi.
     * Ko'rinish o'zgarmaydi, online_today xatosi tuzatilgan va natija 5 daqiqaga keshlanadi.
     */
    public function dashboard(Request $request, OverviewMetrics $overview): View
    {
        $forceRefresh = $request->boolean('refresh');
        $data = $overview->getAdminDashboardData($forceRefresh);

        return view('admin.dashboard', $data);
    }

    /**
     * Mavjud /admin/stats (admin.stats) sahifasi.
     */
    public function stats(Request $request, OverviewMetrics $overview): View
    {
        $forceRefresh = $request->boolean('refresh');
        $data = $overview->getAdminDashboardData($forceRefresh);

        return view('admin.stats', ['stats' => $data['stats']]);
    }

    /**
     * GET /admin/analytics/{tab?}
     */
    public function index(
        Request $request,
        OverviewMetrics $overview,
        RetentionMetrics $retention,
        ContentMetrics $content,
        EconomyMetrics $economy,
        CommunityMetrics $community,
        AiMetrics $ai,
        SystemHealthMetrics $health,
        string $tab = 'overview'
    ): View {
        if (!in_array($tab, self::VALID_TABS, true)) {
            $tab = 'overview';
        }

        $range = DateRange::fromRequest($request);
        $forceRefresh = $request->boolean('refresh');

        if ($forceRefresh && $request->hasSession()) {
            $request->session()->now('success', "Analitika kesh ma'lumotlari muvaffaqiyatli yangilandi.");
        }

        $tabData = $this->resolveMetricData(
            $tab,
            $range,
            $forceRefresh,
            $request,
            $overview,
            $retention,
            $content,
            $economy,
            $community,
            $ai,
            $health
        );

        return view('admin.analytics.index', [
            'activeTab'    => $tab,
            'range'        => $range->toArray(),
            'data'         => $tabData,
            'alerts'       => $health->getAlerts(),
            'wasRefreshed' => $forceRefresh,
        ]);
    }

    /**
     * JSON GET /admin/analytics/data/{metric}
     */
    public function data(
        Request $request,
        OverviewMetrics $overview,
        RetentionMetrics $retention,
        ContentMetrics $content,
        EconomyMetrics $economy,
        CommunityMetrics $community,
        AiMetrics $ai,
        SystemHealthMetrics $health,
        string $metric
    ): JsonResponse {
        $allowed = array_merge(self::VALID_TABS, ['alerts', 'chapter-dropoff']);
        abort_unless(in_array($metric, $allowed, true), 404);

        $range = DateRange::fromRequest($request);
        $forceRefresh = $request->boolean('refresh');

        $payload = $this->resolveMetricData(
            $metric,
            $range,
            $forceRefresh,
            $request,
            $overview,
            $retention,
            $content,
            $economy,
            $community,
            $ai,
            $health
        );

        return response()->json([
            'metric' => $metric,
            'range'  => $range->toArray(),
            'data'   => $payload,
        ]);
    }

    /**
     * Eksport GET /admin/analytics/export/{metric} (CSV, faqat agregat, shaxsiy ma'lumotlarsiz)
     */
    public function export(
        Request $request,
        OverviewMetrics $overview,
        RetentionMetrics $retention,
        ContentMetrics $content,
        EconomyMetrics $economy,
        CommunityMetrics $community,
        AiMetrics $ai,
        string $metric
    ): StreamedResponse {
        $allowedExports = ['overview', 'trends', 'retention', 'funnel', 'content', 'economy', 'community', 'ai'];
        abort_unless(in_array($metric, $allowedExports, true), 404);

        $range = DateRange::fromRequest($request);

        // Audit logga eksport amalini yozib qo'yish (6-bo'lim talabi)
        Log::info('Admin analytics CSV export executed', [
            'admin_id' => auth()->id(),
            'metric'   => $metric,
            'period'   => $range->toArray(),
            'ip'       => $request->ip(),
        ]);

        [$headers, $rows] = $this->buildAggregateExportRows(
            $metric,
            $range,
            $overview,
            $retention,
            $content,
            $economy,
            $community,
            $ai
        );

        $filename = sprintf('kitobxon_analytics_%s_%s_%s.csv', $metric, $range->startDate(), $range->endDate());

        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM (Excel'da o'zbek harflari to'g'ri ochilishi uchun)
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function resolveMetricData(
        string $metric,
        DateRange $range,
        bool $forceRefresh,
        Request $request,
        OverviewMetrics $overview,
        RetentionMetrics $retention,
        ContentMetrics $content,
        EconomyMetrics $economy,
        CommunityMetrics $community,
        AiMetrics $ai,
        SystemHealthMetrics $health
    ): array {
        switch ($metric) {
            case 'overview':
                return [
                    'kpis'   => $overview->getKpis($range, $forceRefresh),
                    'trends' => $overview->getTrends($range, $forceRefresh),
                    'alerts' => $health->getAlerts(),
                ];

            case 'users':
                return [
                    'funnel'         => $retention->getFunnel($range, $forceRefresh),
                    'weekly_cohorts' => $retention->getWeeklyCohorts($range, null, $forceRefresh),
                    'daily_cohorts'  => $retention->getDailyCohorts($range),
                    'segments'       => $retention->getSegments($forceRefresh),
                    'churn_risk'     => $retention->getChurnRiskUsers(),
                    'heatmap'        => $retention->getActivityHeatmap($range, $forceRefresh),
                ];

            case 'content':
                $bookId = $request->query('book_id') ? (int) $request->query('book_id') : null;
                return [
                    'top_books'       => $content->getTopBooks($range, 15, $forceRefresh),
                    'book_health'     => $content->getBookHealth($range, $forceRefresh),
                    'chapter_dropoff' => $content->getChapterDropoff($bookId),
                    'quizzes'         => $content->getQuizAnalytics($range, $forceRefresh),
                ];

            case 'chapter-dropoff':
                $bookId = $request->query('book_id') ? (int) $request->query('book_id') : null;
                return $content->getChapterDropoff($bookId);

            case 'economy':
                return [
                    'point_sources'       => $economy->getPointSources($range, $forceRefresh),
                    'daily_issuance'      => $economy->getDailyIssuance($range, $forceRefresh),
                    'streak_distribution' => $economy->getStreakDistribution($forceRefresh),
                    'abuse_signals'       => $economy->getAbuseSignals($range, $forceRefresh),
                    'shop_metrics'        => $economy->getShopEconomyMetrics($forceRefresh),
                ];

            case 'community':
                return $community->getSummary($range, $forceRefresh);

            case 'ai-system':
                return [
                    'ai'     => $ai->getSummary($range, $forceRefresh),
                    'system' => $health->getHealth($forceRefresh),
                    'alerts' => $health->getAlerts(),
                ];

            case 'alerts':
                return [
                    'alerts' => $health->getAlerts(),
                ];

            default:
                return [];
        }
    }

    /**
     * Faqat agregat CSV qatorlarini hosil qilish (email/ism/shaxsiy identifikatorlar YO'Q).
     *
     * @return array{0: array<int, string>, 1: array<int, array<int, scalar|null>>}
     */
    protected function buildAggregateExportRows(
        string $metric,
        DateRange $range,
        OverviewMetrics $overview,
        RetentionMetrics $retention,
        ContentMetrics $content,
        EconomyMetrics $economy,
        CommunityMetrics $community,
        AiMetrics $ai
    ): array {
        switch ($metric) {
            case 'overview':
            case 'trends':
                $trends = $overview->getTrends($range);
                $headers = [
                    'Sana',
                    'DAU',
                    'Kirganlar (logged_in)',
                    "O'qiganlar (minutes_read>0)",
                    "Faol o'quvchilar (>=5 daq)",
                    'Yangi foydalanuvchilar',
                    'Onboarding tugatganlar',
                    "O'qilgan daqiqalar",
                    'Tugatilgan kitoblar',
                    'Quiz urinishlari',
                    'Chiqarilgan ballar',
                    'Chiqarilgan tangalar',
                    'Chat xabarlari',
                    'AI xabarlari',
                    'AI tokenlar',
                ];
                $rows = [];
                foreach ($trends['dates'] as $i => $d) {
                    $rows[] = [
                        $d,
                        $trends['dau'][$i] ?? 0,
                        $trends['logged_in_users'][$i] ?? 0,
                        $trends['reading_users'][$i] ?? 0,
                        $trends['active_readers'][$i] ?? 0,
                        $trends['new_users'][$i] ?? 0,
                        $trends['onboarded'][$i] ?? 0,
                        $trends['minutes_read'][$i] ?? 0,
                        $trends['books_finished'][$i] ?? 0,
                        $trends['quiz_attempts'][$i] ?? 0,
                        $trends['points_issued'][$i] ?? 0,
                        $trends['coins_issued'][$i] ?? 0,
                        $trends['chat_messages'][$i] ?? 0,
                        $trends['ai_messages'][$i] ?? 0,
                        $trends['ai_tokens'][$i] ?? 0,
                    ];
                }
                return [$headers, $rows];

            case 'retention':
                $cohorts = $retention->getWeeklyCohorts($range);
                $headers = ['Hafta boshlanishi', 'Hafta tugashi', 'Kohorta hajmi', 'D1 (%)', 'D7 (%)', 'D30 (%)'];
                $rows = [];
                foreach ($cohorts as $c) {
                    $rows[] = [
                        $c['week_start'],
                        $c['week_end'],
                        $c['cohort_size'],
                        $c['d1'] !== null ? $c['d1'] : 'N/A',
                        $c['d7'] !== null ? $c['d7'] : 'N/A',
                        $c['d30'] !== null ? $c['d30'] : 'N/A',
                    ];
                }
                return [$headers, $rows];

            case 'funnel':
                $funnel = $retention->getFunnel($range);
                $headers = ['Bosqich', 'Foydalanuvchilar soni', 'Jami ulushi (%)', 'Oldingi bosqichdan konversiya (%)'];
                $rows = [];
                foreach ($funnel['steps'] as $s) {
                    $rows[] = [$s['label'], $s['count'], $s['percent_of_total'], $s['percent_of_prev']];
                }
                return [$headers, $rows];

            case 'content':
                $books = $content->getTopBooks($range, 100);
                $headers = ['Kitob sarlavhasi', 'Muallif', 'Janr', "O'quvchilar soni", "O'qilgan daqiqa", 'Tugatish (%)', "O'rtacha baho"];
                $rows = [];
                foreach ($books as $b) {
                    $rows[] = [
                        $b['title'],
                        $b['author'],
                        $b['genre'],
                        $b['readers_count'],
                        $b['minutes_read'],
                        $b['completion_pct'],
                        $b['avg_rating'] ?? 'N/A',
                    ];
                }
                return [$headers, $rows];

            case 'economy':
                $issuance = $economy->getDailyIssuance($range);
                $headers = ['Sana', 'Chiqarilgan ballar', 'Chiqarilgan tangalar', 'Sarflangan tangalar'];
                $rows = [];
                foreach ($issuance['dates'] as $i => $d) {
                    $rows[] = [
                        $d,
                        $issuance['points_issued'][$i] ?? 0,
                        $issuance['coins_issued'][$i] ?? 0,
                        $issuance['coins_spent'][$i] ?? 0,
                    ];
                }
                return [$headers, $rows];

            case 'community':
                $comm = $community->getSummary($range);
                $headers = ['Guruh nomi', "A'zolar soni", 'Xabarlar soni', 'Faol yozuvchilar'];
                $rows = [];
                foreach ($comm['top_groups'] as $g) {
                    $rows[] = [$g['name'], $g['members_count'], $g['messages_count'], $g['active_posters']];
                }
                return [$headers, $rows];

            case 'ai':
                $aiData = $ai->getSummary($range);
                $headers = ['Provayder', 'Model', 'Xabarlar soni', 'Tokenlar', 'Taxminiy narx (USD)'];
                $rows = [];
                foreach ($aiData['by_model'] as $m) {
                    $rows[] = [$m['provider'], $m['model'], $m['messages_count'], $m['tokens_used'], $m['cost_usd']];
                }
                return [$headers, $rows];

            default:
                return [['Sana'], []];
        }
    }
}
