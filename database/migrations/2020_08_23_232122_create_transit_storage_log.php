<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransitStorageLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transit_storage_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('transit_storage_id')->comment('仓库');
            $table->unsignedBigInteger('log_user_id')->comment('记录人员');
            $table->string('log_user_name')->comment('记录人员');
            $table->unsignedBigInteger('company_model_id')->comment('雷力型号');
            $table->string('company_model')->comment('雷力型号');
            $table->unsignedBigInteger('spec_id')->comment('规格明细');
            $table->string('spec')->comment('规格明细')->unique();
            $table->enum('from',['1','2','3'])
                ->comment('来源')
                ->default('1')->nullable();
            $table->enum('type',['blank','silver_plating','electroplating_gun','sole',
                'electroplating_gold','crystal_heel','waterproof_platform','come_forward'])
                ->comment('派工类型')
                ->default('sole')->nullable();
            $table->decimal('in_num',10,2)->comment('入库数量')->nullable()->default(0);
            $table->decimal('out_num',10,2)->comment('出库数量')->nullable()->default(0);
            $table->decimal('storage',10,2)->comment('库存')->nullable()->default(0);
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
        Schema::dropIfExists('transit_storage_log');
    }
}
