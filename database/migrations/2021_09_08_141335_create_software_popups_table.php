<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoftwarePopupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('software_popups', function (Blueprint $table) {

            $table->increments('id');
			$table->string('title', 2048);
			$table->string('subtitle', 2048)->nullable();
			$table->string('image', 2048)->nullable();
			$table->text('description')->nullable();
			$table->string('cta_text', 32)->nullable();
            $table->string('cta_link', 2048)->nullable();
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
        Schema::dropIfExists('login_popups');
    }
}
