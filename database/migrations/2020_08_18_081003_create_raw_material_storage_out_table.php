<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRawMaterialStorageOutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raw_material_storage_out', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('raw_material_storage_out_no')->comment('出库单号')->unique();
            $table->enum('type',['in','out'])->comment('出库类型')->default('in');
            $table->unsignedBigInteger('check_user_id')->comment('记录人');
            $table->string('check_user_name')->comment('记录人')->nullable();

            $table->unsignedBigInteger('raw_material_product_information_id')->comment('原材料');
            $table->string('raw_material_product_information_name')->comment('原材料名称');
            $table->string('raw_material_product_information_no')->comment('原材料编号');

            $table->unsignedBigInteger('raw_material_category_id')->comment('原材料类型');
            $table->string('raw_material_category_name')->comment('原材料类型')->nullable();

            $table->unsignedBigInteger('purchase_standard_id')->comment('规格');
            $table->string('purchase_standard_name')->comment('规格')->nullable();

            $table->unsignedBigInteger('unit_id')->comment('单位');
            $table->string('unit')->comment('单位')->nullable();
            $table->decimal('num',10,0)->comment('数量')->default(0);
            $table->decimal('change_coefficient',10,2)->comment('转换公斤系数')->default(0);

            $table->timestamp('date_at')->comment('出库时间');
            $table->string('apply_user_name')->comment('领用厂家')->nullable();
            $table->text('remark')->comment('描述')->nullable();
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
        Schema::dropIfExists('raw_material_storage_out');
    }
}
