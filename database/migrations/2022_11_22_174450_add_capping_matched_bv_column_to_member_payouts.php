<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCappingMatchedBvColumnToMemberPayouts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_payouts', function (Blueprint $table) {
            $table->decimal('capping_matched_bv', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member_payouts', function (Blueprint $table) {
            $table->dropColumn('capping_matched_bv');
        });
    }
}
