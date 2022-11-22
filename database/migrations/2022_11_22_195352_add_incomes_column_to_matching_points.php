<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIncomesColumnToMatchingPoints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('matching_points', function (Blueprint $table) {
            $table->integer('income_1_id')->nullable(); 
            $table->decimal('income_1_total_points',12, 2)->default(0)->nullable();
            $table->decimal('income_1_point_value', 12, 2)->default(0)->nullable();
            $table->integer('income_2_id')->nullable(); 
            $table->decimal('income_2_total_points',12, 2)->default(0)->nullable();
            $table->decimal('income_2_point_value', 12, 2)->default(0)->nullable();
            $table->integer('income_3_id')->nullable(); 
            $table->decimal('income_3_total_points',12, 2)->default(0)->nullable();
            $table->decimal('income_3_point_value', 12, 2)->default(0)->nullable();
            $table->integer('income_4_id')->nullable(); 
            $table->decimal('income_4_total_points',12, 2)->default(0)->nullable();
            $table->decimal('income_4_point_value', 12, 2)->default(0)->nullable();
            $table->integer('income_5_id')->nullable(); 
            $table->decimal('income_5_total_points',12, 2)->default(0)->nullable();
            $table->decimal('income_5_point_value', 12, 2)->default(0)->nullable();
            $table->integer('income_6_id')->nullable(); 
            $table->decimal('income_6_total_points',12, 2)->default(0)->nullable();
            $table->decimal('income_6_point_value', 12, 2)->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('matching_points', function (Blueprint $table) {
            $table->dropColumn('income_1_id'); 
            $table->dropColumn('income_1_total_points');
            $table->dropColumn('income_1_point_value');
            $table->dropColumn('income_2_id'); 
            $table->dropColumn('income_2_total_points');
            $table->dropColumn('income_2_point_value');
            $table->dropColumn('income_3_id'); 
            $table->dropColumn('income_3_total_points');
            $table->dropColumn('income_3_point_value');
            $table->dropColumn('income_4_id'); 
            $table->dropColumn('income_4_total_points');
            $table->dropColumn('income_4_point_value');
            $table->dropColumn('income_5_id'); 
            $table->dropColumn('income_5_total_points');
            $table->dropColumn('income_5_point_value');
            $table->dropColumn('income_6_id'); 
            $table->dropColumn('income_6_total_points');
            $table->dropColumn('income_6_point_value');
        });
    }
}
