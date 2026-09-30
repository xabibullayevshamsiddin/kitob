<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Make book_id nullable in reading_sessions for independent videos / general sessions.
     *
     * Note: On SQLite the base create migration already defines book_id as
     * nullable (SQLite cannot MODIFY COLUMN), so this is a no-op there.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE reading_sessions MODIFY COLUMN book_id BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE reading_sessions MODIFY COLUMN book_id BIGINT UNSIGNED NOT NULL');
    }
};
