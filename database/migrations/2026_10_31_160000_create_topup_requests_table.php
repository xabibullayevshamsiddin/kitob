<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Karta orqali (P2P) tanga/ball sotib olish so'rovlari.
 * Foydalanuvchi admin kartasiga aynan "expected_amount" so'm o'tkazadi, chek yuklaydi,
 * tizim chekni avtomatik tekshiradi va admin tasdiqlagach hisobga tushadi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topup_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 16)->unique();

            // Paketning so'rov yaratilgan paytdagi nusxasi (keyin narx o'zgarsa ham shu beriladi)
            $table->string('package_key', 40);
            $table->string('package_title', 120);
            $table->unsignedInteger('coins')->default(0);
            $table->unsignedInteger('points')->default(0);
            $table->unsignedInteger('base_amount');
            $table->unsignedInteger('expected_amount')->index();
            $table->string('card_last4', 4)->nullable();

            $table->string('status', 20)->default('awaiting_payment')->index();
            $table->timestamp('expires_at')->nullable();

            // Chek (yopiq diskda saqlanadi)
            $table->string('receipt_path')->nullable();
            $table->string('receipt_mime', 50)->nullable();
            $table->unsignedInteger('receipt_size')->nullable();
            $table->string('receipt_sha256', 64)->nullable()->index();
            $table->string('receipt_dhash', 16)->nullable()->index();
            $table->string('payer_card_last4', 4)->nullable();
            $table->string('user_note', 500)->nullable();
            $table->timestamp('submitted_at')->nullable();

            // Avtomatik tekshiruv natijalari
            $table->string('ai_status', 20)->default('none');
            $table->json('ai_result')->nullable();
            $table->string('extracted_transaction_id', 100)->nullable()->index();
            $table->unsignedInteger('extracted_amount')->nullable();
            $table->json('checks')->nullable();
            $table->unsignedTinyInteger('risk_score')->default(0);

            // Admin qarori
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('reject_reason', 500)->nullable();
            $table->text('admin_note')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topup_requests');
    }
};
