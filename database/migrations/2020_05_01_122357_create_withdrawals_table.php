<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('member_id');
            $table->decimal('amount', 8, 2)->default(0);
            $table->decimal('tds_percentage', 8, 2)->default(0);
            $table->decimal('tds_amount', 8, 2)->default(0);
            $table->decimal('net_amount', 8, 2)->default(0);
            $table->integer('withdrawal_request_id');            
            $table->timestamp('payment_made_at')->nullable();
            $table->string('payment_status',32)->nullable();
            $table->integer('transaction_by')->nullable();
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
        Schema::dropIfExists('withdrawals');
    }
}
