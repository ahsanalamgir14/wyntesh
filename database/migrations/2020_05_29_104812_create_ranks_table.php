<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name',64);
            $table->decimal('capping', 10, 2)->default(0);
            $table->decimal('bv_from', 8, 2)->nullable();
            $table->decimal('bv_to', 8, 2)->nullable();
            $table->integer('leg_rank')->nullable();
            $table->integer('leg_rank_count')->nullable();
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
        Schema::dropIfExists('ranks');
    }
}
