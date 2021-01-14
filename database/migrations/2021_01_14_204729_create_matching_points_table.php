<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchingPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matching_points', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('member_id');
            $table->decimal('leg_1', 12, 2)->default(0);
            $table->decimal('leg_2', 12, 2)->default(0);
            $table->decimal('leg_3', 12, 2)->default(0);
            $table->decimal('leg_4', 12, 2)->default(0);
            $table->decimal('matched', 12, 2)->default(0);
            $table->decimal('previous_carry_forward_position', 12, 2)->default(0);
            $table->integer('previous_carry_forward')->nullable();
            $table->decimal('carry_forward', 12, 2)->default(0);
            $table->integer('carry_forward_position')->nullable();
            $table->decimal('total_sales', 12, 2)->nullable();
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
        Schema::dropIfExists('matching_points');
    }
}
