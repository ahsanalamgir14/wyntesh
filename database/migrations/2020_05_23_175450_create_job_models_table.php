<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_models', function (Blueprint $table) {
            $table->id();
            $table->string('type',32);
            $table->boolean('is_queued')->default(0);
            $table->string('models',655360)->nullable();
            $table->boolean('is_failed')->default(0);
            $table->string('failed_models',65536)->nullable();
            $table->integer('is_finished')->default(0);
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
        Schema::dropIfExists('job_models');
    }
}
