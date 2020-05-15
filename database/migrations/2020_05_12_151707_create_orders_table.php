<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no',32);
            $table->string('user_id',32);
            $table->decimal('amount', 8, 2)->default(0);
            $table->decimal('discount', 8, 2)->default(0)->nullable();
            $table->decimal('gst', 8, 2)->default(0);
            $table->decimal('shipping_fee', 8, 2)->default(0)->nullable();
            $table->decimal('admin_fee', 8, 2)->default(0)->nullable();
            $table->decimal('final_amount', 8, 2)->default(0);
            $table->string('payment_status',32);
            $table->integer('wallet_transaction_id');
            $table->string('payment_mode',32);
            $table->string('delivery_by',128)->nullable();
            $table->string('tracking_no',32)->nullable();
            $table->string('delivery_status',32)->nullable();
            $table->decimal('pv', 8, 2)->default(0)->nullable();
            $table->string('remarks',1024)->nullable();
            $table->integer('billing_address_id')->nullable();
            $table->integer('shipping_address_id');
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
        Schema::dropIfExists('orders');
    }
}