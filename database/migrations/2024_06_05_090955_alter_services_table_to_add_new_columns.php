<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterServicesTableToAddNewColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('type_id')->nullable()->default(null)->constrained()->onDelete('cascade');
            $table->foreignId('price_type_id')->nullable()->default(null)->constrained()->onDelete('cascade');
            $table->foreignId('shift_type_id')->nullable()->default(null)->constrained()->onDelete('cascade');
            $table->foreignId('shift_hour_id')->nullable()->default(null)->constrained()->onDelete('cascade');
            $table->foreignId('material_unit_id')->nullable()->default(null)->constrained()->onDelete('cascade');
            $table->string('business_name')->nullable()->default(null);
            $table->string('designation')->nullable()->default(null);
            $table->string('preferred_distance')->nullable()->default(null);
            $table->string('tax')->nullable()->default(null);
            $table->string('site_visit')->nullable()->default(null);
            $table->string('charged_price')->nullable()->default(null);
            $table->string('experience')->nullable()->default(null);
            $table->string('expected_salary')->nullable()->default(null);
            $table->string('willing_to_relocate')->nullable()->default(null);
            $table->string('user_for_travel')->nullable()->default(null);
            $table->string('notice_period')->nullable()->default(null);
            $table->string('plot_area')->nullable()->default(null);
            $table->string('interest_rate')->nullable()->default(null);
            $table->string('loan_process')->nullable()->default(null);
            $table->boolean('with_transport')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['material_unit_id']); // Pass the column as an array
            $table->dropForeign(['type_id']);
            $table->dropForeign(['price_type_id']);
            $table->dropForeign(['shift_type_id']);
            $table->dropForeign(['shift_hour_id']);

            // Drop the columns
            $table->dropColumn([
                'type_id',
                'price_type_id',
                'shift_type_id',
                'shift_hour_id',
                'material_unit_id',
                'business_name',
                'designation',
                'preferred_distance',
                'tax',
                'site_visit',
                'charged_price',
                'experience',
                'expected_salary',
                'willing_to_relocate',
                'user_for_travel',
                'notice_period',
                'plot_area',
                'interest_rate',
                'loan_process',
                'with_transport',
            ]);
        });
    }
}
