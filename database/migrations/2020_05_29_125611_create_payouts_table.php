<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('payout_type_id');
            $table->boolean('is_run_by_system')->default(1);
            $table->date('sales_start_date');
            $table->date('sales_end_date');
            $table->decimal('sales_bv', 10, 2)->default(0);
            $table->decimal('sales_amount', 10, 2)->default(0);
            $table->decimal('total_matched_bv', 10, 2)->default(0);
            $table->decimal('total_carry_forward_bv', 10, 2)->default(0);
            $table->decimal('total_payout', 10, 2)->default(0);
            $table->decimal('tds', 10, 2)->default(0);
            $table->decimal('admin_fee', 10, 2)->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
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
        Schema::dropIfExists('payouts');
    }
}
