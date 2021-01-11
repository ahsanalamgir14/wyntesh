<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSizeVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('size_variants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',64);
            $table->string('brand_size',64);
            $table->string('url', 2048)->nullable()->default(0);
            $table->decimal('length_mm', 10,2)->nullable()->default(0);
            $table->decimal('width_mm', 10,2)->nullable()->default(0);
            $table->decimal('height_mm', 10,2)->nullable()->default(0);
            $table->decimal('chest_cm', 10,2)->nullable()->default(0);
            $table->decimal('bust_cm', 10,2)->nullable()->default(0);
            $table->decimal('to_fit_bust_cm', 10,2)->nullable()->default(0);
            $table->decimal('front_length_cm', 10,2)->nullable()->default(0);
            $table->decimal('waist_cm', 10,2)->nullable()->default(0);
            $table->decimal('to_fit_waist', 10,2)->nullable()->default(0);
            $table->decimal('across_shoulder_cm', 10,2)->nullable()->default(0);
            $table->decimal('hips_cm', 10,2)->nullable()->default(0);
            $table->decimal('inseam_length_cm', 10,2)->nullable()->default(0);
            $table->decimal('top_length_cm', 10,2)->nullable()->default(0);
            $table->decimal('bottom_length_cm', 10,2)->nullable()->default(0);
            $table->decimal('sleeve_length_cm', 10,2)->nullable()->default(0);
            $table->decimal('neck_cm', 10,2)->nullable()->default(0);
            $table->text('note')->nullable();
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
        Schema::dropIfExists('size_variants');
    }
}
