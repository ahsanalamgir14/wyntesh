<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWallOfWyntashTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wall_of_wyntash', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 256)->nullable();
            $table->string('username', 256)->nullable();
            $table->string('age', 2048)->nullable();
            $table->string('city', 2048)->nullable();
            $table->string('total_amount', 2048)->nullable();
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
        Schema::dropIfExists('wall_of_wyntash');
    }
}
