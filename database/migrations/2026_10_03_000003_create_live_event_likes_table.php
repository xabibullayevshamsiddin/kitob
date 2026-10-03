<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('live_event_likes')) {
            Schema::create('live_event_likes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('live_event_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['live_event_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('live_event_likes');
    }
};
