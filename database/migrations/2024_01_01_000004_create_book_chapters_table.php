<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')
                  ->constrained('books')
                  ->onDelete('cascade');
            $table->unsignedInteger('chapter_number');
            $table->string('title');
            $table->longText('content');
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index('book_id');
            $table->index('chapter_number');
            $table->unique(['book_id', 'chapter_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_chapters');
    }
};
