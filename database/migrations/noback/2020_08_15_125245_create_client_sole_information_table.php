<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientSoleInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_sole_information', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('client_id')->comment('客户');
            $table->string('client_name')->comment('客户');
            $table->unsignedBigInteger('company_model_id')->comment('雷力型号');
            $table->string('company_model')->comment('雷力型号');
            $table->unsignedBigInteger('client_model_id')->comment('客户型号');
            $table->string('client_model')->comment('客户型号');
            $table->unsignedBigInteger('product_category_id')->comment('产品类型');
            $table->string('product_category_name')->comment('产品类型');
            $table->unsignedBigInteger('sole_material_id')->comment('鞋底用料');
            $table->string('sole_material_name')->comment('鞋底用料');
            $table->unsignedBigInteger('craft_color_id')->comment('工艺颜色');
            $table->string('craft_color_name')->comment('工艺颜色');
            $table->unsignedBigInteger('personnel_id')->comment('业务员');
            $table->string('personnel_name')->comment('业务员');
            $table->timestamp('date_at')->comment('创建日期');
            $table->enum('is_use',['1','0'])->comment('使用状态')->default(1);
            $table->enum('is_color',['1','0'])->comment('是否改色')->default(0);
            $table->enum('is_welt',['1','0'])->comment('是否沿条底')->default(0);
            $table->enum('is_copy',['1','0'])->comment('是否复制')->default(0);
            $table->string('knife_mold')->comment('刀模');
            $table->string('leather_piece')->comment('革片');
            $table->string('welt')->comment('沿条');
            $table->string('sole')->comment('鞋底');
            $table->string('start_code')->comment('开始码');
            $table->string('end_code')->comment('开始码');
            $table->string('out')->comment('出面');
            $table->string('inject_mold_ask')->comment('注塑要求');
            $table->string('craft_ask')->comment('工艺要求');
            $table->text('remark')->comment('备注');

            $table->decimal('price',10,2)->comment('单价');
            $table->enum('is_information_delete',['1','0'])->comment('是否信息删除')->default(0);
            $table->enum('is_price_delete',['1','0'])->comment('是否价格删除')->default(0);
            $table->enum('price_status',['1','0'])->comment('价格状态')->default(1);
            $table->enum('is_check',['1','0'])->comment('是否验收')->default(0);
            $table->timestamp('check_at')->comment('创建日期');
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
        Schema::dropIfExists('client_sole_information');
    }
}
