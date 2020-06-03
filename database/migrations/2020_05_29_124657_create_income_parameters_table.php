<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomeParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('income_parameters', function (Blueprint $table) {
            $table->id();
            $table->integer('income_id');
            $table->string('name',64);
            $table->decimal('value_1', 8, 2)->default(0);
            $table->decimal('value_2', 8, 2)->default(0)->nullable();
            $table->decimal('value_3', 8, 2)->default(0)->nullable();
            $table->decimal('value_4', 8, 2)->default(0)->nullable();
            $table->decimal('value_5', 8, 2)->default(0)->nullable();
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
        Schema::dropIfExists('income_parameters');
    }
}
