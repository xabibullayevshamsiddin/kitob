<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboard_snapshots', function (Blueprint $table) {
            $table->id();
            $table->enum('period', ['daily', 'weekly', 'monthly', 'all_time']);
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->unsignedInteger('rank');
            $table->unsignedBigInteger('points');
            $table->integer('reading_minutes')->default(0);
            $table->integer('quiz_score')->default(0);
            $table->integer('streak_days')->default(0);
            $table->date('snapshot_date');
            $table->timestamps();

            $table->index(['period', 'snapshot_date']);
            $table->index('user_id');
            $table->index(['period', 'snapshot_date', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboard_snapshots');
    }
};
