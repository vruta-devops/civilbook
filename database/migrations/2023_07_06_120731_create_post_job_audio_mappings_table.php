<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostJobAudioMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_job_audio_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_request_id');
            $table->text('post_job_attachment_audio')->nullable();
            $table->foreign('post_request_id')->references('id')->on('post_job_requests')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post_job_audio_mappings');
    }
}
