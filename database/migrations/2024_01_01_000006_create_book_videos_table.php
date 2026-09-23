<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')
                  ->constrained('books')
                  ->onDelete('cascade');
            $table->enum('type', ['overview', 'chapter']);
            $table->unsignedInteger('chapter_number')->nullable();
            $table->string('title');
            $table->string('video_path');
            $table->string('thumbnail')->nullable();
            $table->string('hls_path')->nullable();
            $table->unsignedInteger('duration')->nullable()->comment('Duration in seconds');
            $table->boolean('is_processed')->default(false);
            $table->timestamps();

            $table->index('book_id');
            $table->index('type');
            $table->index(['book_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_videos');
    }
};
