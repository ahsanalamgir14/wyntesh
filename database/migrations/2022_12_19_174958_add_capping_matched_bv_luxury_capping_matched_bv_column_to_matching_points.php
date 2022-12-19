<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCappingMatchedBvLuxuryCappingMatchedBvColumnToMatchingPoints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('matching_points', function (Blueprint $table) {
            $table->decimal('capping_matched', 10, 2)->default(0);
            $table->decimal('luxury_capping_matched', 10, 2)->default(0);
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
            $table->dropColumn('capping_matched');
            $table->dropColumn('luxury_capping_matched');
        });
    }
}
