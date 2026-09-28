<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Make book_id nullable in reading_sessions for independent videos / general sessions.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE reading_sessions MODIFY COLUMN book_id BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE reading_sessions MODIFY COLUMN book_id BIGINT UNSIGNED NOT NULL');
    }
};
