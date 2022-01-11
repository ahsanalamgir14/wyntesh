<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComboCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('combo_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('combo_id');
            $table->integer('category_id');
            $table->integer('quantity')->default(0);
            $table->decimal('dp_base', 8, 2)->default(0);
            $table->decimal('dp_gst_rate', 12, 2)->default(0);
            $table->decimal('dp_gst_amount', 12, 2)->default(0);
            $table->decimal('dp_cgst_rate', 12, 2)->default(0);
            $table->decimal('dp_cgst_amount', 12, 2)->default(0);
            $table->decimal('dp_sgst_rate', 12, 2)->default(0);
            $table->decimal('dp_sgst_amount', 12, 2)->default(0);
            $table->decimal('dp_utgst_rate', 12, 2)->default(0);
            $table->decimal('dp_utgst_amount', 12, 2)->default(0);
            $table->decimal('dp_amount', 12, 2)->default(0);
            $table->decimal('pv', 12, 2)->default(0)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['combo_id','category_id','created_at'],'combo_categories_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('combo_categories');
    }
}
