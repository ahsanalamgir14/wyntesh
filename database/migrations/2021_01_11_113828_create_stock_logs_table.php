<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('product_id');
            $table->integer('variant_id');
            $table->string('sku',20);
            $table->integer('units_inward')->default(0)->nullable();
            $table->integer('units_outward')->default(0)->nullable();
            $table->bigInteger('created_by');
            $table->string('inward_challan_number',50)->nullable();
            $table->string('outward_challan_number',50)->nullable();
            $table->boolean('is_order_placed');
            $table->boolean('is_order_returned');
            $table->bigInteger('order_id')->nullable();
            $table->text('note')->nullable();
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
        Schema::dropIfExists('stock_logs');
    }
}
