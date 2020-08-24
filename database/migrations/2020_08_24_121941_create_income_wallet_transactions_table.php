<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomeWalletTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('income_wallet_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_id');
            $table->integer('transaction_type_id')->nullable();
            $table->decimal('amount', 8, 2)->default(0);
            $table->decimal('balance', 8, 2)->default(0);
            $table->bigInteger('transfered_from')->nullable();
            $table->bigInteger('transfered_to')->nullable();            
            $table->bigInteger('transaction_by')->nullable();            
            $table->string('note',2048)->nullable();
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
        Schema::dropIfExists('income_wallet_transactions');
    }
}
