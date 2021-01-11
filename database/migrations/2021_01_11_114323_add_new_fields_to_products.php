<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('hsn',16)->nullable();
            $table->dropColumn('admin_fee');
            $table->dropColumn('shipping_fee');
            $table->dropColumn('stock');
            
            
            $table->decimal('cost_cgst_rate', 12, 2)->default(0);
            $table->decimal('cost_cgst_amount', 12, 2)->default(0);
            $table->decimal('cost_sgst_rate', 12, 2)->default(0);
            $table->decimal('cost_sgst_amount', 12, 2)->default(0);
            $table->decimal('cost_utgst_rate', 12, 2)->default(0);
            $table->decimal('cost_utgst_amount', 12, 2)->default(0);

           
            $table->decimal('dp_cgst_rate', 12, 2)->default(0);
            $table->decimal('dp_cgst_amount', 12, 2)->default(0);
            $table->decimal('dp_sgst_rate', 12, 2)->default(0);
            $table->decimal('dp_sgst_amount', 12, 2)->default(0);
            $table->decimal('dp_utgst_rate', 12, 2)->default(0);
            $table->decimal('dp_utgst_amount', 12, 2)->default(0);

            
            $table->decimal('retail_cgst_rate', 12, 2)->default(0);
            $table->decimal('retail_cgst_amount', 12, 2)->default(0);
            $table->decimal('retail_sgst_rate', 12, 2)->default(0);
            $table->decimal('retail_sgst_amount', 12, 2)->default(0);
            $table->decimal('retail_utgst_rate', 12, 2)->default(0);
            $table->decimal('retail_utgst_amount', 12, 2)->default(0);

            $table->boolean('is_color_variant')->default(0)->nullable();
            $table->boolean('is_size_variant')->default(0)->nullable();

            //$table->boolean('is_active')->default(1);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
}
