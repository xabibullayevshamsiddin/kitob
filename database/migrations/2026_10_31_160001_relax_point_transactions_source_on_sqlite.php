<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MySQL'da point_transactions.source 2026_10_28 migratsiyasida VARCHAR(32) qilingan.
 * SQLite (testlar) ENUM CHECK cheklovini ALTER bilan olib tashlay olmaydi — jadvalni qayta quramiz,
 * shunda test muhiti production bilan bir xil ishlaydi (masalan, 'purchase' manbasi).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        Schema::create('point_transactions_rebuild', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('points');
            $table->string('source', 32);
            $table->string('description')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('source');
            $table->index(['user_id', 'source']);
        });

        DB::statement('INSERT INTO point_transactions_rebuild (id, user_id, points, source, description, reference_id, reference_type, created_at, updated_at)
            SELECT id, user_id, points, source, description, reference_id, reference_type, created_at, updated_at FROM point_transactions');

        Schema::drop('point_transactions');
        Schema::rename('point_transactions_rebuild', 'point_transactions');

        Schema::table('point_transactions', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'pt_user_created_idx');
        });
    }

    public function down(): void
    {
        // Qattiq ENUM'ga qaytarilmaydi (yangi manbali qatorlarni buzmaslik uchun)
    }
};
