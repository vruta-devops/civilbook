<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPostJobRequestsTableToAddNewColumnExpireAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('post_job_requests', function (Blueprint $table) {
            $table->date('expired_at')->nullable()->after('date')->default(null);
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
            $table->dropColumn('expired_at');
        });
    }
}
