<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberPayoutIncomesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_payout_incomes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_id');
            $table->bigInteger('payout_id');
            $table->integer('income_id');
            $table->decimal('payout_amount', 14, 6)->default(0)->nullable();
            $table->string('income_payout_parameter_1_name',128)->nullable();
            $table->decimal('income_payout_parameter_1_value', 14, 6)->default(0)->nullable();
            $table->string('income_payout_parameter_2_name',128)->nullable();
            $table->decimal('income_payout_parameter_2_value', 14, 6)->default(0)->nullable();
            $table->string('income_payout_parameter_3_name',128)->nullable();
            $table->decimal('income_payout_parameter_3_value', 14, 6)->default(0)->nullable();
            $table->string('income_payout_parameter_4_name',128)->nullable();
            $table->decimal('income_payout_parameter_4_value', 14, 6)->default(0)->nullable();
            $table->string('income_payout_parameter_5_name',128)->nullable();
            $table->decimal('income_payout_parameter_5_value', 14, 6)->default(0)->nullable();
            $table->decimal('tds', 14, 6)->default(0);
            $table->decimal('admin_fee', 14, 6)->default(0);
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
        Schema::dropIfExists('member_payout_incomes');
    }
}
