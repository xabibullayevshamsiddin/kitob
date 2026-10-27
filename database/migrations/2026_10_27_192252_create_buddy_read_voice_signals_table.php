<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buddy_read_voice_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buddy_read_id')->constrained('buddy_reads')->onDelete('cascade');
            $table->foreignId('from_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('to_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('type', 32); // 'offer', 'answer', 'candidate', 'join', 'leave'
            $table->longText('payload');
            $table->boolean('is_consumed')->default(false);
            $table->timestamps();

            $table->index(['buddy_read_id', 'to_user_id', 'is_consumed'], 'br_signals_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buddy_read_voice_signals');
    }
};
