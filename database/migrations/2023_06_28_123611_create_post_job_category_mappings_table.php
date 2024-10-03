<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostJobCategoryMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_job_category_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_request_id');
            $table->unsignedBigInteger('category_id');
            $table->bigInteger('sub_category_id')->default('0')->comment('0=all');
            $table->foreign('post_request_id')->references('id')->on('post_job_requests')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post_job_category_mappings');
    }
}
