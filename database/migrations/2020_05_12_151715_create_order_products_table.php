<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('order_id');
            $table->integer('product_id');
            $table->integer('qty');
            $table->decimal('amount', 8, 2)->default(0);
            $table->decimal('discount', 8, 2)->default(0)->nullable();
            $table->decimal('shipping_fee', 8, 2)->default(0)->nullable();
            $table->decimal('admin_fee', 8, 2)->default(0)->nullable();
            $table->decimal('gst', 8, 2)->default(0);
            $table->decimal('gst_rate', 8, 2)->default(0);
            $table->decimal('final_amount', 8, 2)->default(0);
            $table->decimal('pv', 8, 2)->default(0)->nullable();
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
        Schema::dropIfExists('order_products');
    }
}
