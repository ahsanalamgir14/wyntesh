<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberCarryForwardPvTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_carry_forward_pv', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('member_id');
            $table->integer('position');
            $table->bigInteger('last_payout_id');
            $table->decimal('last_payout_carry_forward_pv', 10, 2)->default(0); 
            $table->decimal('carry_forward_pv', 10, 2)->default(0);
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
        Schema::dropIfExists('member_carry_forward_pv');
    }
}
