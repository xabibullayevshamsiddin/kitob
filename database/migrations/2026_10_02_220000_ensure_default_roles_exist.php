<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roleNames = ['admin', 'teacher', 'student', 'author', 'reader'];
        foreach ($roleNames as $r) {
            try {
                Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
            } catch (\Throwable $e) {
                // Ignore if tables are not ready
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
