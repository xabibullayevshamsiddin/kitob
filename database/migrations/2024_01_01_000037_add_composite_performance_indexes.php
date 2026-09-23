<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Composite performance indexes migration.
 *
 * These indexes are added separately (after all tables exist) to avoid
 * circular dependency issues and to document performance tuning intent.
 *
 * Targets:
 *  - reading_sessions      : user_id + session_date + book_id
 *  - book_reading_progress : user_id + book_id  (already covered by unique, kept for clarity)
 *  - point_transactions    : user_id + created_at
 *  - leaderboard_snapshots : period + snapshot_date + rank
 */
return new class extends Migration
{
    public function up(): void
    {
        // reading_sessions: composite for daily aggregation queries
        Schema::table('reading_sessions', function (Blueprint $table) {
            $table->index(
                ['user_id', 'session_date', 'book_id'],
                'rs_user_date_book_idx'
            );
        });

        // book_reading_progress: composite for progress lookup
        Schema::table('book_reading_progress', function (Blueprint $table) {
            // The unique(['user_id','book_id','chapter_id']) already covers this,
            // but we add an explicit non-unique index for partial queries (user+book without chapter).
            // MySQL uses the leftmost prefix of the unique index, so this is a no-op on most engines;
            // kept explicit for documentation and cross-DB compatibility.
            if (DB::getDriverName() !== 'sqlite') {
                $table->index(
                    ['user_id', 'book_id'],
                    'brp_user_book_idx'
                );
            }
        });

        // point_transactions: composite for timeline queries
        Schema::table('point_transactions', function (Blueprint $table) {
            $table->index(
                ['user_id', 'created_at'],
                'pt_user_created_idx'
            );
        });

        // leaderboard_snapshots: composite for ranked period queries
        Schema::table('leaderboard_snapshots', function (Blueprint $table) {
            $table->index(
                ['period', 'snapshot_date', 'rank'],
                'ls_period_date_rank_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('reading_sessions', function (Blueprint $table) {
            $table->dropIndex('rs_user_date_book_idx');
        });

        Schema::table('book_reading_progress', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropIndex('brp_user_book_idx');
            }
        });

        Schema::table('point_transactions', function (Blueprint $table) {
            $table->dropIndex('pt_user_created_idx');
        });

        Schema::table('leaderboard_snapshots', function (Blueprint $table) {
            $table->dropIndex('ls_period_date_rank_idx');
        });
    }
};
