<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKycTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kyc', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->string('address', 2048)->nullable();
            $table->string('pincode', 6)->nullable();            
            $table->string('adhar', 16)->nullable();
            $table->string('adhar_image', 1024)->nullable();
            $table->string('pan', 10)->nullable();
            $table->string('pan_image', 1024)->nullable();
            $table->string('city', 64)->nullable();
            $table->string('state', 64)->nullable();
            $table->string('bank_ac_name', 1024)->nullable();
            $table->string('bank_name', 1024)->nullable();
            $table->string('bank_ac_no', 32)->nullable();
            $table->string('ifsc', 32)->nullable();
            $table->boolean('is_verified')->default(0);
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
        Schema::dropIfExists('kyc');
    }
}
