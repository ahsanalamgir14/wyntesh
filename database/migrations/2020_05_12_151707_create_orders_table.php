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
            $table->bigIncrements('id');
            $table->string('order_no',32);
            $table->bigInteger('user_id');
            $table->decimal('amount', 8, 2)->default(0);
            $table->decimal('discount', 8, 2)->default(0)->nullable();
            $table->decimal('gst', 8, 2)->default(0);
            $table->decimal('shipping_fee', 8, 2)->default(0)->nullable();
            $table->decimal('admin_fee', 8, 2)->default(0)->nullable();
            $table->decimal('final_amount', 8, 2)->default(0);
            $table->string('payment_status',32);
            $table->decimal('distributor_discount', 8, 2)->default(0)->nullable();
            $table->integer('wallet_transaction_id')->nullable();
            $table->string('payment_mode',32);
            $table->string('delivery_by',128)->nullable();
            $table->string('tracking_no',32)->nullable();
            $table->string('delivery_status',32)->nullable();
            $table->boolean('is_withhold_purchase')->default(0)->nullable();
            $table->bigInteger('payout_id')->nullable();
            $table->decimal('pv', 8, 2)->default(0)->nullable();
            $table->string('remarks',1024)->nullable();
            $table->bigInteger('billing_address_id')->nullable();            
            $table->bigInteger('shipping_address_id')->nullable();
            $table->boolean('is_package')->default(0);
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
