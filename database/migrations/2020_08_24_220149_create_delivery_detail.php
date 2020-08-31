<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('plan_list_id')->comment('计划单');
            $table->unsignedBigInteger('delivery_id')->comment('发货单');
            $table->unsignedBigInteger('plan_list_detail_id')->comment('计划单详情');
            $table->string('spec')->comment('尺码');
            $table->enum('type',['left','right','couple'])->default('left')->comment('规格');
            $table->unsignedBigInteger('num')->default(1)->comment('数量');
            $table->enum('is_print',['1','0'])->default('0')->comment('是否打印');
            $table->enum('status',['1','0'])->default('1')->comment('状态');
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
        Schema::dropIfExists('delivery_detail');
    }
}
