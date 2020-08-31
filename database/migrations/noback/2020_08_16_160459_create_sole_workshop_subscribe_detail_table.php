<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoleWorkshopSubscribeDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sole_workshop_subscribe_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sole_workshop_subscribe_id')->comment('计划单');
            $table->unsignedBigInteger('purcahse_standard_id')->comment('规格');
            $table->string('purcahse_standard_name')->comment('规格');
            $table->decimal('total_price',10,2)->comment('总金额')->default(0);
            $table->decimal('change_coefficient',10,0)->comment('公斤转换系数')->default(0);
            $table->unsignedBigInteger('unit_id')->comment('计量单位');
            $table->string('unit_name')->comment('计量单位');
            $table->decimal('approval_num',10,0)->comment('批准数量')->default(0);
            $table->decimal('apply_num',10,0)->comment('申购数量')->default(0);
            $table->decimal('storage_in_num',10,0)->comment('入库数量')->default(0);
            $table->enum('is_void',['1','0'])->comment('是否作废')->default(0);
            $table->enum('check_status',['part','overrule','verify','unreviewed'])->comment('是否作废')->default('unreviewed');
            $table->unsignedBigInteger('check_user_id')->comment('审核人');
            $table->string('check_user_name')->comment('审核人');
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
        Schema::dropIfExists('sole_workshop_subscribe_detail');
    }
}
