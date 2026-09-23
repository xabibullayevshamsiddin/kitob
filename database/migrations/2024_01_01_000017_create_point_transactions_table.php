<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->integer('points')->comment('Positive for earn, negative for spend');
            $table->enum('source', [
                'reading', 'quiz', 'streak', 'badge', 'bonus', 'referral', 'admin'
            ]);
            $table->string('description')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable()->comment('Polymorphic model class');
            $table->timestamps();

            $table->index('user_id');
            $table->index('source');
            $table->index(['user_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
