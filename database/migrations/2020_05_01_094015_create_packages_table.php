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
			$table->string('package_code',32);			
			$table->text('description', 65535)->nullable();
			$table->string('image', 2048)->nullable();
			$table->decimal('base_amount', 8, 2)->default(0);
			$table->decimal('gst_rate', 8, 2)->default(0);
			$table->decimal('gst_amount', 8, 2)->default(0);
			$table->decimal('net_amount', 8, 2)->default(0);
			$table->decimal('capping_amount', 8, 2)->default(0);
			$table->integer('pv')->nullable();
			$table->integer('validity')->nullable();
			$table->boolean('is_active')->default(1);
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
		Schema::drop('packages');
	}

}
