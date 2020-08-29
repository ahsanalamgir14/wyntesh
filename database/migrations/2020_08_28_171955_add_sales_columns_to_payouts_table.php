<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalesColumnsToPayoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->decimal('sales_gst', 12, 2)->default(0);
            $table->decimal('sales_shipping_fee', 12, 2)->default(0);
            $table->decimal('sales_admin_fee', 12, 2)->default(0);
            $table->decimal('sales_total_amount', 12, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropColumn('sales_gst');
            $table->dropColumn('sales_shipping_fee');
            $table->dropColumn('sales_admin_fee');
            $table->dropColumn('sales_total_amount');
        });
    }
}
