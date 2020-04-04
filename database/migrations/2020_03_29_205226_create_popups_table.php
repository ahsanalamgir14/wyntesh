<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePopupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('popups', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('title', 2048);
			$table->string('subtitle', 2048)->nullable();
			$table->string('image', 2048)->nullable();
			$table->text('description')->nullable();
			$table->timestamp('from_time')->nullable();
			$table->timestamp('to_time')->nullable();
			$table->boolean('is_visible')->default(1);
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
		Schema::drop('popups');
	}

}
