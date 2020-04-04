<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePackagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('packages', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->text('description', 65535)->nullable();
			$table->string('image', 128)->nullable();			
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
		Schema::drop('packages');
	}

}
