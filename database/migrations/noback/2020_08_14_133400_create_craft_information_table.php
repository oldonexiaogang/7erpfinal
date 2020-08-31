<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCraftInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('craft_information', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('date_at')->comment('创建日期');
            $table->unsignedBigInteger('client_id')->comment('客户id');
            $table->string('client_name')->nullable()->comment('客户名称');
            $table->string('company_model')->comment('公司型号');
            $table->string('old_company_model')->comment('公司型号');
            $table->string('old_client_model')->comment('客户型号');
            $table->string('client_model')->nullable()->comment('客户型号');
            $table->string('sole_material_demand')->nullable()->comment('鞋底用料要求');
            $table->string('carft_type_name')->nullable()->comment('工艺类型');
            $table->text('sole_image')->comment('鞋底照片');
            $table->string('remark')->nullable()->comment('说明');
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
        Schema::dropIfExists('craft_information');
    }
}
