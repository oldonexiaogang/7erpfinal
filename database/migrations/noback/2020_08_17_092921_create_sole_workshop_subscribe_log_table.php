<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoleWorkshopSubscribeLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sole_workshop_subscribe_log', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('sole_workshop_subscribe_detail_id')->comment('鞋底车间申购详情id');
            $table->unsignedBigInteger('sole_workshop_subscribe_id')->comment('鞋底车间申购id');
            $table->unsignedBigInteger('check_user_id')->comment('审核人');
            $table->string('check_user_name')->comment('审核人')->nullable();
            $table->text('reason')->comment('原因')->nullable();
            $table->decimal('approval_num',10,2)->comment('批准数量')->default(0);
            $table->softDeletes();
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
        Schema::dropIfExists('sole_workshop_subscribe_log');
    }
}
