<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_event_id')
                  ->constrained('live_events')
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->text('question');
            $table->boolean('is_selected')->default(false);
            $table->boolean('is_answered')->default(false);
            $table->timestamps();

            $table->index('live_event_id');
            $table->index(['live_event_id', 'is_selected']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_questions');
    }
};
