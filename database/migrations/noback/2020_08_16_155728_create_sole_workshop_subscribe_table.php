<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoleWorkshopSubscribeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sole_workshop_subscribe', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sole_workshop_subscribe_no')->comment('申购编号');
            $table->string('raw_material_product_information_no')->comment('原材料编号');
            $table->unsignedBigInteger('raw_material_product_information_id')->comment('原材料名称');
            $table->string('raw_material_product_information_name')->comment('原材料名称')->nullable();

            $table->unsignedBigInteger('raw_material_category_id')->comment('原材料类型');
            $table->string('raw_material_category_name')->comment('原材料类型')->nullable();

            $table->unsignedBigInteger('supplier_id')->comment('供应商');
            $table->string('supplier_name')->comment('供应商')->nullable();

            $table->decimal('price',10,2)->comment('单价');

            $table->unsignedBigInteger('color_id')->comment('颜色');
            $table->string('color')->comment('颜色')->nullable();

            $table->decimal('total_num',10,0)->comment('申购总数量')->default(0);



            $table->text('subscribe_remark')->comment('申购说明')->nullable();
            $table->text('subscribe_content')->comment('申购备注')->nullable();

            $table->timestamp('date_at')->comment('申购时间');

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
        Schema::dropIfExists('sole_workshop_subscribe');
    }
}
