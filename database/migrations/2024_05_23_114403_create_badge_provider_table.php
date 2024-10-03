<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBadgeProviderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('badge_provider', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('badge_id'); // Foreign key for badge
            $table->unsignedBigInteger('provider_id'); // Foreign key for provider
            // Define foreign key constraints
            $table->foreign('badge_id')->references('id')->on('badges')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('users')->onDelete('cascade');
            $table->softDeletes();
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
        Schema::dropIfExists('badge_provider');
    }
}
