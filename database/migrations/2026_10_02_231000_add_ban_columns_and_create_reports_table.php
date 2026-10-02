<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'is_banned')) {
                    $table->boolean('is_banned')->default(false);
                }
                if (!Schema::hasColumn('users', 'banned_until')) {
                    $table->timestamp('banned_until')->nullable();
                }
                if (!Schema::hasColumn('users', 'ban_reason')) {
                    $table->string('ban_reason', 500)->nullable();
                }
                if (!Schema::hasColumn('users', 'banned_at')) {
                    $table->timestamp('banned_at')->nullable();
                }
            });
        }

        if (!Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('reported_user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->string('source', 50)->default('global_chat');
                $table->text('message_content');
                $table->string('link', 500)->nullable();
                $table->string('reason', 255)->nullable();
                $table->string('status', 30)->default('pending');
                $table->text('admin_notes')->nullable();
                $table->timestamps();

                $table->index('reported_user_id');
                $table->index('status');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reports')) {
            Schema::dropIfExists('reports');
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'is_banned')) {
                    $table->dropColumn('is_banned');
                }
                if (Schema::hasColumn('users', 'banned_until')) {
                    $table->dropColumn('banned_until');
                }
                if (Schema::hasColumn('users', 'ban_reason')) {
                    $table->dropColumn('ban_reason');
                }
                if (Schema::hasColumn('users', 'banned_at')) {
                    $table->dropColumn('banned_at');
                }
            });
        }
    }
};
