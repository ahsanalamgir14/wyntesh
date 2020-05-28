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
            $table->integer('position');
            $table->integer('sponsor_id')->nullable();
            $table->integer('parent_id')->nullable();
            $table->string('path', 2048)->nullable();
            $table->integer('level');
            $table->decimal('wallet_balance', 10, 2)->nullable();
            $table->decimal('current_personal_pv', 10, 2)->default(0)->nullable();
            $table->decimal('total_personal_pv', 10, 2)->default(0)->nullable();
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
