<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coin_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->integer('coins')->comment('Positive for earn, negative for spend');
            $table->enum('source', [
                'reading', 'quiz', 'streak', 'badge', 'bonus', 'admin', 'spend'
            ]);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coin_transactions');
    }
};
