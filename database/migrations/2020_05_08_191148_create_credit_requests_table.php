<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('amount', 8, 2)->default(0);
            $table->bigInteger('requested_by');
            $table->integer('payment_mode');
            $table->string('reference',512)->nullable();
            $table->integer('bank_id')->nullable();
            $table->string('status',32)->nullable();
            $table->bigInteger('approved_by');
            $table->timestamp('approved_at')->nullable();
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
        Schema::dropIfExists('credit_requests');
    }
}
