<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDelivery extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('plan_list_id')->comment('计划单');
            $table->string('plan_list_no')->comment('计划单编号');
            $table->string('delivery_no')->comment('发货单号');
            $table->string('client_order_no')->comment('客户订单号');

            $table->unsignedBigInteger('client_id');
            $table->string('client_name')->comment('客户名称')->nullable();
            $table->unsignedBigInteger('company_model_id')->comment('雷力型号');
            $table->string('company_model')->comment('雷力型号')->nullable();
            $table->unsignedBigInteger('client_model_id')->comment('客户型号');
            $table->string('client_model')->comment('客户型号')->nullable();

            $table->unsignedBigInteger('craft_color_id')->comment('工艺颜色');
            $table->string('craft_color_name')->comment('工艺颜色')->nullable();
            $table->string('inject_mold_ask')->comment('注塑要求')->nullable();
            $table->string('plan_remark')->comment('计划说明')->nullable();

            $table->enum('status',['0','1','2'])->comment('发货状态')->nullable()->default(0);
            $table->decimal('all_num',10,2)->comment('派工总数量')->nullable()->default(0);

            $table->unsignedBigInteger('delivery_price_id')->comment('出库单价');
            $table->decimal('delivery_price',10,2)->comment('出库单价')->default(0);
            $table->unsignedBigInteger('log_user_id')->comment('记录人员');
            $table->string('log_user_name')->comment('记录人员')->nullable();
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
        Schema::dropIfExists('delivery');
    }
}
