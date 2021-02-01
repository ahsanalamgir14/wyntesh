<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLuxuryWalletTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('luxury_wallet_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_id');
            $table->integer('transaction_type_id')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);       
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
        Schema::dropIfExists('luxury_wallet_transactions');
    }
}
