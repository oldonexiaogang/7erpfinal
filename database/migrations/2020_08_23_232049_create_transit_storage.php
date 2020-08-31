<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransitStorage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transit_storage', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_model_id')->comment('雷力型号');
            $table->string('company_model')->comment('雷力型号');
            $table->unsignedBigInteger('spec_id')->comment('规格明细');
            $table->string('spec')->comment('规格明细')->unique();

            $table->enum('type',['blank','silver_plating','electroplating_gun','sole',
                'electroplating_gold','crystal_heel','waterproof_platform','come_forward'])
                ->comment('派工类型')
                ->default('sole')->nullable();
            $table->timestamp('check_at')->comment('验收日期');
            $table->decimal('price',10,2)->comment('单价')->nullable()->default(0);
            $table->decimal('in_num',10,2)->comment('入库数量')->nullable()->default(0);
            $table->decimal('out_num',10,2)->comment('出库数量')->nullable()->default(0);
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
        Schema::dropIfExists('transit_storage');
    }
}
