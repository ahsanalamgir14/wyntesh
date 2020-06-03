<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('pin_number',16);
            $table->integer('package_id');
            $table->decimal('base_amount', 8, 2)->default(0);
            $table->decimal('tax_percentage', 8, 2)->default(0);
            $table->decimal('tax_amount', 8, 2)->default(0);
            $table->decimal('total_amount', 8, 2)->default(0);
            $table->bigInteger('owned_by')->nullable();
            $table->bigInteger('used_by')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->bigInteger('request_id')->nullable();
            $table->timestamp('allocated_at')->nullable();
            $table->string('status',32)->nullable();
            $table->string('note',2048)->nullable();
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
        Schema::dropIfExists('pins');
    }
}
