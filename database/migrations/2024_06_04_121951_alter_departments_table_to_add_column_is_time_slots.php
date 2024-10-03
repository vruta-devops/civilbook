<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDepartmentsTableToAddColumnIsTimeSlots extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->tinyInteger('is_time_slots')->nullable()->default(0);
            $table->tinyInteger('is_discount_enabled')->nullable()->default(0);
            $table->tinyInteger('is_price_enabled')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('is_time_slots');
            $table->dropColumn('is_discount_enabled');
            $table->dropColumn('is_price_enabled');
        });
    }
}
