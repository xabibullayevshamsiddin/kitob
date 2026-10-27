<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('mood', 50)->nullable()->after('genre')->comment('Kayfiyat: tezkor, chuqur, ilhomlantiruvchi, dam_oluvchi');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('mood');
        });
    }
};
