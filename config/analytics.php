<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Analytics Timezone
    |--------------------------------------------------------------------------
    | Barcha kunlik chegaralar, kohortalar va grafiklar shu vaqt zonasida
    | hisoblanadi.
    */
    'timezone' => env('ANALYTICS_TIMEZONE', 'Asia/Tashkent'),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    | Jonli analitika bloklari uchun kesh vaqti (soniyada). Standart: 300s (5 daq).
    */
    'cache_ttl' => (int) env('ANALYTICS_CACHE_TTL', 300),

    /*
    |--------------------------------------------------------------------------
    | Metric Thresholds
    |--------------------------------------------------------------------------
    */
    'active_reader_min_minutes' => (int) env('ANALYTICS_ACTIVE_READER_MINUTES', 5),
    'book_finished_percent'     => (float) env('ANALYTICS_BOOK_FINISHED_PERCENT', 90.0),
    'segment_min_users'         => (int) env('ANALYTICS_SEGMENT_MIN_USERS', 5),
    'churn_min_streak'          => (int) env('ANALYTICS_CHURN_MIN_STREAK', 7),
    'churn_inactive_days'       => (int) env('ANALYTICS_CHURN_INACTIVE_DAYS', 2),
    'abuse_z_score_threshold'   => (float) env('ANALYTICS_ABUSE_Z_SCORE', 3.0),

    /*
    |--------------------------------------------------------------------------
    | AI Cost & Budget
    |--------------------------------------------------------------------------
    | 1 000 token uchun taxminiy narx (USD) va kunlik token byudjeti.
    */
    'ai_cost_per_1k'        => (float) env('ANALYTICS_AI_COST_PER_1K', 0.002),
    'ai_daily_token_budget' => (int) env('ANALYTICS_AI_DAILY_TOKEN_BUDGET', 100000),

    /*
    |--------------------------------------------------------------------------
    | Alerts ("Diqqat talab qiladi") Thresholds
    |--------------------------------------------------------------------------
    */
    'alerts' => [
        'dau_drop_percent'        => (int) env('ANALYTICS_ALERT_DAU_DROP_PCT', 30),
        'report_stale_hours'      => (int) env('ANALYTICS_ALERT_REPORT_HOURS', 24),
        'scheduler_stale_minutes' => (int) env('ANALYTICS_ALERT_SCHEDULER_MINUTES', 5),
    ],
];
