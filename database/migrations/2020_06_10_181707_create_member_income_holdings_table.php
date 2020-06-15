<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberIncomeHoldingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_income_holdings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('member_id');
            $table->bigInteger('payout_id');
            $table->bigInteger('income_id');
            $table->integer('rank_id');
            $table->decimal('rank_bv_criteria', 10, 2)->default(0);
            $table->decimal('current_bv', 10, 2)->default(0);
            $table->decimal('required_bv', 10, 2)->default(0);
            $table->decimal('amount', 10, 2)->default(0);
            $table->boolean('is_paid')->default(0);
            $table->timestamp('paid_at')->nullable();
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
        Schema::dropIfExists('member_income_holdings');
    }
}
