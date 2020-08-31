<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRawMaterialStorageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raw_material_storage', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('supplier_id')->comment('供应商');
            $table->string('supplier_name')->comment('供应商')->nullable();

            $table->unsignedBigInteger('raw_material_product_information_id')->comment('原材料');
            $table->string('raw_material_product_information_name')->comment('原材料名称');
            $table->string('raw_material_product_information_no')->comment('原材料编号');

            $table->unsignedBigInteger('raw_material_category_id')->comment('原材料类型');
            $table->string('raw_material_category_name')->comment('原材料类型')->nullable();

            $table->unsignedBigInteger('purchase_standard_id')->comment('规格');
            $table->string('purchase_standard_name')->comment('规格')->nullable();

            $table->unsignedBigInteger('color_id')->comment('颜色');
            $table->string('color')->comment('颜色')->nullable();

            $table->unsignedBigInteger('unit_id')->comment('单位');
            $table->string('unit')->comment('单位')->nullable();
            $table->decimal('price',10,2)->comment('单价')->default(0);
            $table->decimal('change_coefficient',10,2)->comment('转换公斤系数')->default(0);

            $table->decimal('num',10,0)->comment('库存数量')->default(0);
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
        Schema::dropIfExists('raw_material_storage');
    }
}
