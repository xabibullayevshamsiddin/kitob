<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->foreignId('book_id')
                  ->nullable()
                  ->constrained('books')
                  ->onDelete('set null');
            $table->text('message');
            $table->text('response');
            $table->string('provider')->comment('e.g. openai, gemini, claude');
            $table->string('model')->comment('e.g. gpt-4o, gemini-1.5-pro');
            $table->unsignedInteger('tokens_used')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_histories');
    }
};
