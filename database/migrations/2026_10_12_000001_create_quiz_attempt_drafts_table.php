<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempt_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('current_question')->default(0);
            $table->json('answers')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'quiz_id']);
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_drafts');
    }
};
