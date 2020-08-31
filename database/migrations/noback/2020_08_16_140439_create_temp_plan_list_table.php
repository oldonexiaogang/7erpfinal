<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempPlanListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_plan_list', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('client_sole_information_id')->comment('客户鞋底资料');
            $table->string('plan_list_no')->comment('计划编号');

            $table->timestamp('delivery_date')->comment('交货日期');
            $table->string('client_order_no')->comment('客户订单号');
            $table->string('product_time')->comment('生产周期');

            $table->unsignedBigInteger('carft_skill_id')->comment('工艺类型');
            $table->string('craft_skill_name')->comment('工艺类型')->nullable();

            $table->unsignedBigInteger('personnel_id');
            $table->string('personnel_name')->comment('业务员')->nullable();

            $table->unsignedBigInteger('client_id');
            $table->string('client_name')->comment('客户名称')->nullable();
            $table->unsignedBigInteger('company_model_id')->comment('雷力型号');
            $table->string('company_model')->comment('雷力型号')->nullable();
            $table->unsignedBigInteger('client_model_id')->comment('客户型号');
            $table->string('client_model')->comment('客户型号')->nullable();

            $table->unsignedBigInteger('craft_color_id')->comment('工艺颜色');
            $table->string('craft_color_name')->comment('工艺颜色')->nullable();

            $table->unsignedBigInteger('product_category_id')->comment('产品类型');
            $table->string('product_category_name')->comment('产品类型')->nullable();

            $table->unsignedBigInteger('plan_category_id')->comment('计划类型');
            $table->string('plan_category_name')->comment('计划类型')->nullable();

            $table->unsignedBigInteger('spec_num')->comment('规格数量')->default(1);

            $table->string('plan_describe')->comment('计划描述')->nullable();
            $table->string('knife_mold')->comment('刀模')->nullable();
            $table->string('leather_piece')->comment('革片')->nullable();
            $table->string('welt')->comment('沿条')->nullable();
            $table->string('out')->comment('出面')->nullable();
            $table->string('inject_mold_ask')->comment('注塑要求')->nullable();
            $table->string('craft_ask')->comment('工艺要求')->nullable();
            $table->string('plan_remark')->comment('计划说明')->nullable();
            $table->text('image')->nullable();
            $table->enum('status',['0','1'])->comment('计划单状态')->default(0);
            $table->enum('process',['none','sole','inject_mold','box','put_in_storage','out_of_storage','delivery'])
                ->comment('计划单进程')->default('none');
            $table->unsignedBigInteger('logger_id')->comment('记录人员');
            $table->string('logger_name')->comment('记录人员')->nullable();
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
        Schema::dropIfExists('temp_plan_list');
    }
}
