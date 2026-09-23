<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_audios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')
                  ->constrained('books')
                  ->onDelete('cascade');
            $table->foreignId('chapter_id')
                  ->nullable()
                  ->constrained('book_chapters')
                  ->onDelete('set null');
            $table->string('file_path');
            $table->unsignedInteger('duration')->nullable()->comment('Duration in seconds');
            $table->string('title')->nullable();
            $table->timestamps();

            $table->index('book_id');
            $table->index('chapter_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_audios');
    }
};
