<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePinTransferLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pin_transfer_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('pin_id');
            $table->integer('transfered_from');
            $table->integer('transfered_to');
            $table->integer('transfered_by');
            $table->string('note',2048)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pin_transfer_logs');
    }
}
