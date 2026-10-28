<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('buddy_reads', function (Blueprint $table) {
            $table->boolean('is_private')->default(false)->after('max_members');
            $table->string('password')->nullable()->after('is_private');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('buddy_reads', function (Blueprint $table) {
            $table->dropColumn(['is_private', 'password']);
        });
    }
};
