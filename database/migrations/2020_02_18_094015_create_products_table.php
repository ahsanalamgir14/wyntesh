<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('products', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('alias');
			$table->text('description', 65535)->nullable();
			$table->string('image', 1024);
			$table->decimal('price', 8, 2)->nullable();
			$table->decimal('gst', 8, 2)->default(0);
			$table->decimal('gst_amount', 8, 2)->default(0);
			$table->decimal('final_price', 8, 2)->nullable();
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
		Schema::drop('products');
	}

}
