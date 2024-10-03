<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletWithdrawsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_withdraws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('action_by')->nullable()->default(null)->constrained("users")->onDelete('cascade');
            $table->string('account_holder_name');
            $table->string('bank_name');
            $table->string('branch_name');
            $table->string('account_number');
            $table->string('account_type');
            $table->string('ifsc_code');
            $table->double('amount');
            $table->enum('status', ['pending', 'paid', 'rejected']);
            $table->text('reject_reason')->nullable()->default(null);
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
        Schema::dropIfExists('wallet_withdraws');
    }
}
