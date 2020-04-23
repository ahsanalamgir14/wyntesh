<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->integer('sponsor_id')->nullable();
            $table->integer('parent_id')->nullable();
            $table->string('path', 2048)->nullable();
            $table->string('sponsor_path', 2048)->nullable();
            $table->integer('level');
            $table->decimal('wallet_balace', 8, 2)->nullable();
            $table->boolean('is_active')->default(1);

            $table->integer('parent_id')->nullable();
            $table->integer('parent_id')->nullable();
            $table->integer('parent_id')->nullable();
            $table->integer('parent_id')->nullable();
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
        Schema::dropIfExists('members');
    }
}
