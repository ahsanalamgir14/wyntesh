<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateBonusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_bonus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_id');
            $table->bigInteger('income_id');
            $table->bigInteger('order_id');
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('tds_percent', 10, 2)->default(0);
            $table->decimal('tds_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->default(0);
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
        Schema::dropIfExists('affiliate_bonus');
    }
}
