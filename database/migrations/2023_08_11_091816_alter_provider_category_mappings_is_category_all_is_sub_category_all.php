<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProviderCategoryMappingsIsCategoryAllIsSubCategoryAll extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_category_mappings', function (Blueprint $table) {
            $table->tinyInteger('is_category_all')->default(0)->comment('0 = no, 1 = yes');
            $table->tinyInteger('is_sub_category_all')->default(0)->comment('0 = no, 1 = yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_category_mappings', function (Blueprint $table) {
            //
        });
    }
}
