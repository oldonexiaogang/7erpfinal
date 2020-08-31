<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransitStorageOut extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transit_storage_out', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dispatch_id')->comment('派工单');
            $table->unsignedBigInteger('dispatch_detail_id')->comment('派工单');
            $table->string('dispatch_no')->comment('派工单号')->nullable();
            $table->unsignedBigInteger('plan_list_id')->comment('计划单');
            $table->string('plan_list_no')->comment('计划单')->nullable();

            $table->enum('type',['inject_mold_inner','inject_mold_outer','electroplating_outer','storage_count'])
                ->comment('出库类型')
                ->default('inject_mold_inner');
            $table->enum('style',['in','out'])
                ->comment('出库方式')
                ->default('in');
            $table->enum('storage_type',['blank','silver_plating','electroplating_gun','sole',
                'electroplating_gold','crystal_heel','waterproof_platform','come_forward'])
                ->comment('库存类型')
                ->default('sole');
            $table->enum('count_type',['inject_mold','outer','blank_electroplating_outer'])
                ->comment('计价类型')
                ->default('inject_mold');

            $table->unsignedBigInteger('log_user_id')->comment('记录人员');
            $table->string('log_user_name')->comment('记录人员')->nullable();
            $table->unsignedBigInteger('company_model_id')->comment('雷力型号');
            $table->string('company_model')->comment('雷力型号')->nullable();
            $table->unsignedBigInteger('department_id')->comment('领用部门');
            $table->string('department_name')->comment('领用部门')->nullable();
            $table->unsignedBigInteger('personnel_id')->comment('领用员工');
            $table->string('personnel_name')->comment('领用员工')->nullable();
            $table->text('remark')->comment('描述')->nullable();
            $table->timestamp('out_date')->comment('出库日期');
            $table->decimal('num',10,2)->comment('入库数量')->nullable()->default(0);
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
        Schema::dropIfExists('transit_storage_out');
    }
}
