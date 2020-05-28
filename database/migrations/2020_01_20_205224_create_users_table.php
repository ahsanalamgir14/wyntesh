<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->string('username');
			$table->string('name')->nullable();
			$table->string('email')->nullable();
			$table->string('password')->nullable();
			$table->string('contact', 20)->nullable();
			$table->string('gender', 1)->nullable();
			$table->integer('parent')->nullable();
			$table->date('dob')->nullable();
			$table->string('otp',6)->nullable();
			$table->dateTime('otp_valid_till')->nullable();
			$table->dateTime('last_login')->nullable();
			$table->dateTime('verified_at')->nullable();
			$table->boolean('is_active')->default(1);
			$table->boolean('is_blocked')->default(0);
			$table->string('google')->nullable();
			$table->text('verification_code', 65535)->nullable();
			$table->string('remember_token', 100)->nullable();
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
		Schema::drop('users');
	}

}
