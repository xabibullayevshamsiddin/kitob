<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Make book_id optional/nullable for book_videos, book_audios, and quizzes.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE book_videos MODIFY COLUMN book_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE book_audios MODIFY COLUMN book_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE quizzes MODIFY COLUMN book_id BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE book_videos MODIFY COLUMN book_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE book_audios MODIFY COLUMN book_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE quizzes MODIFY COLUMN book_id BIGINT UNSIGNED NOT NULL');
    }
};
