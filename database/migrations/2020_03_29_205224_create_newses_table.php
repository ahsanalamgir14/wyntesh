<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNewsesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('newses', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('title', 2048);
			$table->string('subtitle', 2048)->nullable();
			$table->string('image', 2048)->nullable();
			$table->text('description')->nullable();
			$table->date('date');
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
		Schema::drop('newses');
	}

}
