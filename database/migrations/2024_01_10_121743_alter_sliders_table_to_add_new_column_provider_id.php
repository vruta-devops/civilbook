<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSlidersTableToAddNewColumnProviderId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->unsignedInteger('provider_id')->nullable()->after('type_id');
            $table->unsignedInteger('provider_address_id')->nullable()->after('provider_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->dropColumn('provider_id');
            $table->dropColumn('provider_address_id');
        });
    }
}
