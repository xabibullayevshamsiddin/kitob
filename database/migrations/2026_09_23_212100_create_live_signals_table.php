<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_event_id')->constrained('live_events')->cascadeOnDelete();
            $table->string('sender_id', 80);
            $table->string('receiver_id', 80); // 'host', viewerId, or 'all'
            $table->string('type', 40); // join, offer, answer, ice-candidate, stream-status
            $table->longText('payload');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['live_event_id', 'receiver_id', 'id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_signals');
    }
};
