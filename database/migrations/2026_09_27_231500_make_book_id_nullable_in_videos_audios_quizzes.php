<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Make book_id optional/nullable for book_videos, book_audios, and quizzes.
     *
     * Note: On SQLite the base create migrations already define book_id as
     * nullable (SQLite cannot MODIFY COLUMN), so this is a no-op there.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE book_videos MODIFY COLUMN book_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE book_audios MODIFY COLUMN book_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE quizzes MODIFY COLUMN book_id BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE book_videos MODIFY COLUMN book_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE book_audios MODIFY COLUMN book_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE quizzes MODIFY COLUMN book_id BIGINT UNSIGNED NOT NULL');
    }
};
