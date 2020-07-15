<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembersLegPvTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members_leg_pv', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_id');
            $table->bigInteger('payout_id')->nullable();
            $table->integer('position');
            $table->decimal('pv', 10, 2)->default(0); //0 on payout + carry forwarded
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
        Schema::dropIfExists('members_leg_pv');
    }
}
