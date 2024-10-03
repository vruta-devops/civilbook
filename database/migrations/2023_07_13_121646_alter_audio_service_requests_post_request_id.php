<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAudioServiceRequestsPostRequestId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('audio_service_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('post_request_id')->default(0)->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('audio_service_requests', function (Blueprint $table) {
            //
        });
    }
}
