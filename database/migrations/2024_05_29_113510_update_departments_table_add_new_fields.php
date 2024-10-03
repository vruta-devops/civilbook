<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDepartmentsTableAddNewFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->boolean('is_transport_option')->default(false);
            $table->boolean('is_experience')->default(false);
            $table->boolean('is_expected_salary')->default(false);
            $table->boolean('is_relocate')->default(false);
            $table->boolean('is_used_travelling')->default(false);
            $table->boolean('is_notice_joining')->default(false);
            $table->boolean('is_business_name')->default(false);
            $table->boolean('is_designation')->default(false);
            $table->boolean('is_preferred')->default(false);
            $table->boolean('is_qualification')->default(false);
            $table->boolean('is_plot_area')->default(false);
            $table->boolean('is_advance_payment')->default(false);
            $table->boolean('is_tax')->default(false);
            $table->boolean('is_interest_rates')->default(false);
            $table->boolean('is_loan_process')->default(false);
            $table->boolean('is_multiple_location')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn([
                'is_transport_option',
                'is_experience',
                'is_expected_salary',
                'is_relocate',
                'is_used_travelling',
                'is_notice_joining',
                'is_business_name',
                'is_designation',
                'is_preferred',
                'is_qualification',
                'is_plot_area',
                'is_advance_payment',
                'is_tax',
                'is_interest_rates',
                'is_loan_process',
                'is_multiple_location',
            ]);
        });
    }
}
