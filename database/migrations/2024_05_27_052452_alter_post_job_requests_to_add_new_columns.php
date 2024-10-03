<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPostJobRequestsToAddNewColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('post_job_requests', function (Blueprint $table) {
            $table->string('address')->nullable()->default(null)->after('customer_id');
            $table->double('latitude')->nullable()->default(null)->after('address');
            $table->double('longitude')->nullable()->default(null)->after('latitude');
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
            $table->dropColumn('address');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });
    }
}
