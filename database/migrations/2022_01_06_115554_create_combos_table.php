<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCombosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('combos', function (Blueprint $table) {
            $table->increments('id');
			$table->string('name');			
			$table->string('combo_code',32);			
			$table->text('description', 65535)->nullable();
			$table->string('image', 2048)->nullable();
			$table->decimal('base_amount', 8, 2)->default(0);
			$table->decimal('gst_rate', 8, 2)->default(0)->nullable();
			$table->decimal('gst_amount', 8, 2)->default(0);
			$table->decimal('cgst_rate', 8, 2)->default(0)->nullable();
			$table->decimal('cgst_amount', 8, 2)->default(0);
			$table->decimal('sgst_rate', 8, 2)->default(0)->nullable();
			$table->decimal('sgst_amount', 8, 2)->default(0);
            $table->decimal('utgst_rate', 8, 2)->default(0)->nullable();
            $table->decimal('utgst_amount', 8, 2)->default(0);
			$table->decimal('net_amount', 8, 2)->default(0);
			$table->decimal('mrp', 8, 2)->default(0);
			$table->integer('pv')->default(0)->nullable();
			$table->boolean('is_active')->default(1);
			$table->timestamps();
			$table->softDeletes();
			$table->index(['name', 'combo_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('combos');
    }
}
