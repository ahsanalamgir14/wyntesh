<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_number',32);
            $table->string('name',64);
            $table->string('brand_name',32)->nullable();
            $table->integer('qty');
            $table->string('qty_unit',32);
            $table->text('description',4096)->nullable();
            $table->text('benefits',4096)->nullable();
            $table->decimal('gst_rate', 8, 2)->default(0)->nullable();
            $table->decimal('cost_base', 8, 2)->default(0)->nullable();
            $table->decimal('cost_gst', 8, 2)->default(0)->nullable();
            $table->decimal('cost_amount', 8, 2)->default(0)->nullable();
            $table->decimal('dp_gst_rate', 8, 2)->default(0)->nullable();
            $table->decimal('dp_base', 8, 2)->default(0)->nullable();
            $table->decimal('dp_gst', 8, 2)->default(0)->nullable();
            $table->decimal('dp_amount', 8, 2)->default(0)->nullable();
            $table->decimal('retail_base', 8, 2)->default(0)->nullable();
            $table->decimal('retail_gst_rate', 8, 2)->default(0)->nullable();
            $table->decimal('retail_gst', 8, 2)->default(0)->nullable();
            $table->decimal('retail_amount', 8, 2)->default(0)->nullable();
            $table->decimal('discount_rate', 8, 2)->default(0)->nullable();
            $table->decimal('discount_amount', 8, 2)->default(0)->nullable();
            $table->decimal('admin_charge', 8, 2)->default(0)->nullable();
            $table->decimal('shipping_fee', 8, 2)->default(0)->nullable();
            $table->decimal('pv', 8, 2)->default(0)->nullable();
            $table->integer('stock')->default(0);  
            $table->string('cover_image',2048)->nullable();
            $table->string('cover_image_thumbnail',2048)->nullable();
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
        Schema::dropIfExists('products');
    }
}
