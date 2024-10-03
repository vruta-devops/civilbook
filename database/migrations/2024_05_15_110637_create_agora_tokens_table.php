<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgoraTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agora_tokens', function (Blueprint $table) {
            $table->id();
            $table->integer('sender_id');
            $table->integer('receiver_id');
            $table->string('channel_name');
            $table->text('common_token');
            $table->text('audio_token');
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
        Schema::dropIfExists('agora_tokens');
    }
}
