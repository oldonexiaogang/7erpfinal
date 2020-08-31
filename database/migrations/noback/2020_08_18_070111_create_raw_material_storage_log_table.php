<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRawMaterialStorageLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raw_material_storage_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('raw_material_storage_id')->comment('仓库');
            $table->unsignedBigInteger('raw_material_product_information_id')->comment('原材料');
            $table->unsignedBigInteger('check_user_id')->comment('审核人');
            $table->string('raw_material_product_information_name')->comment('原材料')->nullable();
            $table->string('raw_material_product_information_no')->comment('原材料')->nullable();
            $table->string('check_user_name')->comment('审核人')->nullable();
            $table->string('from')->comment('来源')->nullable();
            $table->decimal('num',10,2)->comment('数量')->default(0);
            $table->decimal('after_storage_num',10,2)->comment('剩余数量')->default(0);
            $table->enum('type',['in','out'])->comment('类型')->default('in');
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
        Schema::dropIfExists('raw_material_storage_log');
    }
}
