<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('book_comments')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('comment');
            $table->unsignedInteger('likes_count')->default(0);
            $table->timestamps();

            $table->index(['book_id', 'created_at']);
            $table->index('user_id');
            $table->index('parent_id');
        });

        Schema::create('book_comment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_comment_id')->constrained('book_comments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['book_comment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_comment_likes');
        Schema::dropIfExists('book_comments');
    }
};
