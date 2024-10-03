<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPostJobRequestsTableToAddNewColumnIsAllSubCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('post_job_requests', function (Blueprint $table) {
            $table->boolean('is_all_sub_categories')->default(false)->after('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('post_job_requests', function (Blueprint $table) {
            $table->dropColumn('is_all_sub_categories');
        });
    }
}
