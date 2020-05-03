<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePinRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pin_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('package_id');
            $table->integer('quantity');
            $table->decimal('amount', 8, 2)->default(0);
            $table->decimal('tax_percentage', 8, 2)->nullable()->default(0);
            $table->decimal('tax_amount', 8, 2)->nullable()->default(0);
            $table->decimal('total_amount', 8, 2)->nullable()->default(0);
            $table->integer('requested_by');
            $table->integer('payment_mode');
            $table->string('reference',512)->nullable();
            $table->integer('bank_id')->nullable();
            $table->string('payment_status',32)->nullable();
            $table->string('status',32)->nullable();
            $table->integer('approved_by');
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
        Schema::dropIfExists('pin_requests');
    }
}
