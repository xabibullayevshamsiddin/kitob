<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')
                  ->nullable()
                  ->constrained('books')
                  ->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('cover')->nullable();
            $table->timestamp('scheduled_at');
            $table->string('stream_url')->nullable();
            $table->enum('status', ['scheduled', 'live', 'ended'])->default('scheduled');
            $table->string('replay_url')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('scheduled_at');
            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_events');
    }
};
