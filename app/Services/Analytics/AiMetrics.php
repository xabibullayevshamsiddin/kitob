<?php

namespace App\Services\Analytics;

use App\Models\AiChatHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AiMetrics
{
    protected string $timezone;

    public function __construct()
    {
        $this->timezone = config('analytics.timezone', DateRange::TIMEZONE);
    }

    /**
     * AI foydalanish tahlili:
     * - Xabarlar soni, sarflangan tokenlar, taxminiy narx (config('analytics.ai_cost_per_1k'))
     * - Provayder va model bo'yicha taqsimot
     * - Kitoblar bo'yicha berilgan savollar
     * - Kunlik token byudjeti holati
     */
    public function getSummary(DateRange $range, bool $forceRefresh = false): array
    {
        $cacheKey = $range->cacheKey('ai_summary');
        $ttl = (int) config('analytics.cache_ttl', 300);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($range) {
            $costPer1k = (float) config('analytics.ai_cost_per_1k', 0.002);
            $dailyBudget = (int) config('analytics.ai_daily_token_budget', 100000);

            $startStr = $range->start->format('Y-m-d H:i:s');
            $endStr = $range->end->format('Y-m-d H:i:s');
            $prevRange = $range->previousPeriod();
            $prevStartStr = $prevRange->start->format('Y-m-d H:i:s');
            $prevEndStr = $prevRange->end->format('Y-m-d H:i:s');

            $totalMessages = (int) AiChatHistory::whereBetween('created_at', [$startStr, $endStr])->count();
            $totalTokens = (int) AiChatHistory::whereBetween('created_at', [$startStr, $endStr])->sum('tokens_used');
            $estimatedCostUsd = round(($totalTokens / 1000.0) * $costPer1k, 4);

            $prevMessages = (int) AiChatHistory::whereBetween('created_at', [$prevStartStr, $prevEndStr])->count();
            $prevTokens = (int) AiChatHistory::whereBetween('created_at', [$prevStartStr, $prevEndStr])->sum('tokens_used');

            // Bugungi token sarfi va byudjet
            $todayStart = Carbon::now($this->timezone)->startOfDay()->format('Y-m-d H:i:s');
            $todayEnd = Carbon::now($this->timezone)->endOfDay()->format('Y-m-d H:i:s');
            $todayTokens = (int) AiChatHistory::whereBetween('created_at', [$todayStart, $todayEnd])->sum('tokens_used');
            $budgetPercent = $dailyBudget > 0 ? round(($todayTokens / $dailyBudget) * 100, 1) : 0.0;

            // Provayder va model bo'yicha taqsimot
            $byModelRows = AiChatHistory::select([
                'provider',
                'model',
                DB::raw('COUNT(*) as messages_count'),
                DB::raw('SUM(tokens_used) as tokens_sum'),
            ])
                ->whereBetween('created_at', [$startStr, $endStr])
                ->groupBy('provider', 'model')
                ->orderByDesc('tokens_sum')
                ->get();

            $byModel = [];
            foreach ($byModelRows as $r) {
                $toks = (int) $r->tokens_sum;
                $byModel[] = [
                    'provider'       => $r->provider ?: 'unknown',
                    'model'          => $r->model ?: 'default',
                    'messages_count' => (int) $r->messages_count,
                    'tokens_used'    => $toks,
                    'cost_usd'       => round(($toks / 1000.0) * $costPer1k, 4),
                ];
            }

            // Kitoblar bo'yicha AI savollar
            $byBookRows = AiChatHistory::join('books', 'ai_chat_histories.book_id', '=', 'books.id')
                ->whereBetween('ai_chat_histories.created_at', [$startStr, $endStr])
                ->select([
                    'books.id as book_id',
                    'books.title as book_title',
                    DB::raw('COUNT(ai_chat_histories.id) as questions_count'),
                    DB::raw('COUNT(DISTINCT ai_chat_histories.user_id) as unique_users'),
                    DB::raw('SUM(ai_chat_histories.tokens_used) as tokens_sum'),
                ])
                ->groupBy('books.id', 'books.title')
                ->orderByDesc('questions_count')
                ->limit(10)
                ->get();

            $byBook = [];
            foreach ($byBookRows as $br) {
                $byBook[] = [
                    'book_id'         => (int) $br->book_id,
                    'book_title'      => $br->book_title,
                    'questions_count' => (int) $br->questions_count,
                    'unique_users'    => (int) $br->unique_users,
                    'tokens_used'     => (int) $br->tokens_sum,
                ];
            }

            return [
                'messages_count'     => $totalMessages,
                'messages_delta'     => DateRange::formatDelta($totalMessages, $prevMessages),
                'tokens_used'        => $totalTokens,
                'tokens_delta'       => DateRange::formatDelta($totalTokens, $prevTokens),
                'cost_per_1k_usd'    => $costPer1k,
                'estimated_cost_usd' => $estimatedCostUsd,
                'daily_budget'       => [
                    'today_tokens'   => $todayTokens,
                    'budget_tokens'  => $dailyBudget,
                    'usage_percent'  => $budgetPercent,
                    'is_exceeded'    => $dailyBudget > 0 && $todayTokens > $dailyBudget,
                ],
                'by_model'           => $byModel,
                'by_book'            => $byBook,
            ];
        });
    }
}
