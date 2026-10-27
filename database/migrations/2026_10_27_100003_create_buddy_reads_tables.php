<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buddy_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('status', 20)->default('active'); // active, finished
            $table->unsignedInteger('max_members')->default(5);
            $table->timestamps();

            $table->index(['book_id', 'status']);
        });

        Schema::create('buddy_read_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buddy_read_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('current_page')->default(0);
            $table->timestamps();

            $table->unique(['buddy_read_id', 'user_id']);
            $table->index('buddy_read_id');
        });

        Schema::create('buddy_read_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buddy_read_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->unsignedInteger('page_number')->default(0)
                ->comment('Foydalanuvchi qaysi sahifada yozdi');
            $table->boolean('is_spoiler')->default(false);
            $table->timestamps();

            $table->index(['buddy_read_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buddy_read_messages');
        Schema::dropIfExists('buddy_read_members');
        Schema::dropIfExists('buddy_reads');
    }
};
