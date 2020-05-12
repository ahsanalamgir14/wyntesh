<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('full_name', 128);
            $table->string('mobile_number', 10)->nullable();
            $table->string('pincode', 6)->nullable();
            $table->string('address', 256)->nullable();
            $table->string('landmark', 64)->nullable();
            $table->string('city', 64)->nullable();
            $table->string('state', 64)->nullable();
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
        Schema::dropIfExists('addresses');
    }
}
