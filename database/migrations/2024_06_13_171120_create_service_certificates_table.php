<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained("users")->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('certificate_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('is_approved')->nullable()->default(0);
            $table->tinyInteger('is_required')->nullable()->default(0);
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
        Schema::dropIfExists('service_certificates');
    }
}
