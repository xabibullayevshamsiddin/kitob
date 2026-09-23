<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')
                  ->constrained('books')
                  ->onDelete('cascade');
            $table->unsignedInteger('chapter_number')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->unsignedInteger('time_limit_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('book_id');
            $table->index(['book_id', 'chapter_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
