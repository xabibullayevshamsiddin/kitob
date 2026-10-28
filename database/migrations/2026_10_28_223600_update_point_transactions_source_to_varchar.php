<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE point_transactions MODIFY COLUMN source VARCHAR(32) NOT NULL");
        }
    }

    public function down(): void
    {
        // No need to revert to strict enum to avoid breaking new rows
    }
};
