<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('member_id');
            $table->decimal('amount', 8, 2)->default(0);
            $table->decimal('balance', 8, 2)->default(0);
            $table->integer('transfered_from');
            $table->integer('transfered_to');
            $table->integer('transaction_type_id');
            $table->integer('transaction_by');            
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
        Schema::dropIfExists('wallet_transactions');
    }
}