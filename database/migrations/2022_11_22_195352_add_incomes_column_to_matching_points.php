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
            $table->decimal('total_matched_bv',12, 2)->default(0)->nullable();
            $table->decimal('income_1_payout_amount', 12, 2)->default(0)->nullable();
            $table->decimal('income_2_payout_amount', 12, 2)->default(0)->nullable();
            $table->decimal('income_3_factor_value', 12, 2)->default(0)->nullable();
            $table->decimal('income_3_total_points', 12, 2)->default(0)->nullable();
            $table->decimal('income_3_point_value', 12, 2)->default(0)->nullable();
            $table->decimal('income_3_payout_amount', 12, 2)->default(0)->nullable();
            $table->decimal('income_4_factor_value', 12, 2)->default(0)->nullable();
            $table->decimal('income_4_total_points', 12, 2)->default(0)->nullable();
            $table->decimal('income_4_point_value', 12, 2)->default(0)->nullable();
            $table->decimal('income_4_payout_amount', 12, 2)->default(0)->nullable();
            $table->decimal('income_5_factor_value', 12, 2)->default(0)->nullable();
            $table->decimal('income_5_total_points', 12, 2)->default(0)->nullable();
            $table->decimal('income_5_point_value', 12, 2)->default(0)->nullable();
            $table->decimal('income_5_payout_amount', 12, 2)->default(0)->nullable();
            $table->decimal('income_6_factor_value', 12, 2)->default(0)->nullable();
            $table->decimal('income_6_total_points', 12, 2)->default(0)->nullable();
            $table->decimal('income_6_point_value', 12, 2)->default(0)->nullable();
            $table->decimal('income_6_payout_amount', 12, 2)->default(0)->nullable();
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
            $table->dropColumn('total_matched_bv'); 
            $table->dropColumn('income_1_payout_amount');
            $table->dropColumn('income_2_payout_amount');
            $table->dropColumn('income_3_factor_value');
            $table->dropColumn('income_3_total_points');
            $table->dropColumn('income_3_point_value');
            $table->dropColumn('income_3_payout_amount');
            $table->dropColumn('income_4_factor_value');
            $table->dropColumn('income_4_total_points');
            $table->dropColumn('income_4_point_value');
            $table->dropColumn('income_4_payout_amount');
            $table->dropColumn('income_5_factor_value');
            $table->dropColumn('income_5_total_points');
            $table->dropColumn('income_5_point_value');
            $table->dropColumn('income_5_payout_amount');
            $table->dropColumn('income_6_factor_value');
            $table->dropColumn('income_6_total_points');
            $table->dropColumn('income_6_point_value');
            $table->dropColumn('income_6_payout_amount');
        });
    }
}
