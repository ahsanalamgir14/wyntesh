<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberPayoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_payouts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('member_id');
            $table->bigInteger('payout_id');            
            $table->decimal('sales_pv', 10, 2)->default(0);
            $table->decimal('sales_amount', 10, 2)->default(0);
            $table->decimal('total_payout', 10, 2)->default(0);
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
        Schema::dropIfExists('member_payouts');
    }
}
