<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDispatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('plan_list_id')->comment('计划单');
            $table->string('plan_list_no')->comment('计划单编号');
            $table->string('dispatch_no')->comment('派工编号')->unique();
            $table->string('client_order_no')->comment('客户订单号');

            $table->unsignedBigInteger('carft_skill_id')->comment('工艺类型');
            $table->string('craft_skill_name')->comment('工艺类型')->nullable();

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

            $table->enum('type',['sole','inject_mold','box_label'])->comment('派工类型')
                ->default('sole')->nullable();
            $table->enum('process_workshop',['sole','inject_mold','box_label'])->comment('加工车间')
                ->default('sole')->nullable();
            $table->enum('process_department',['sole','inject_mold','box_label'])->comment('加工部门')
                ->default('sole')->nullable();

            $table->string('inject_mold_ask')->comment('注塑要求')->nullable();
            $table->string('plan_remark')->comment('计划说明')->nullable();
            $table->enum('status',['0','1'])->comment('派工状态')->nullable()->default(0);
            $table->decimal('all_num',10,2)->comment('派工总数量')->nullable()->default(0);

            $table->unsignedBigInteger('dispatch_user_id')->comment('派工员');
            $table->string('dispatch_user_name')->comment('派工员')->nullable();
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
        Schema::dropIfExists('dispatches');
    }
}
