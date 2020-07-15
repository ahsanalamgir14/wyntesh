<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberMonthlyLegPvsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_monthly_leg_pvs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_id');
            $table->integer('position');
            $table->decimal('pv', 10, 2)->default(0);
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
        Schema::dropIfExists('member_monthly_leg_pvs');
    }
}
