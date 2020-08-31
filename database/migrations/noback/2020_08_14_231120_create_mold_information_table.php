<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoldInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mold_information', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('date_at')->comment('日期');
            $table->timestamp('check_at')->comment('验收日期');
            $table->integer('client_id')->comment('客户');
            $table->string('client_name')->comment('客户');
            $table->string('mold_information_no')->comment('单号');
            $table->enum('properties',['all','first','second','addsize','fix','change'])->default('all');
            $table->integer('company_model_id')->comment('雷力型号');
            $table->string('company_model')->comment('雷力型号');
            $table->integer('personnel_id')->comment('业务员');
            $table->string('personnel_name')->comment('业务员');
            $table->string('mold_maker_name')->comment('模具生产商');
            $table->integer('mold_maker_id')->comment('模具生产商');
            $table->integer('mold_category_child_id')->comment('模具产品类型');
            $table->integer('mold_category_parent_id')->comment('模具类型');
            $table->text('mold_make_detail_standard')->comment('模具生产详情规格');
            $table->string('money_from')->comment('金额来源');
            $table->string('sole_count')->comment('鞋底合计');
            $table->string('actual_size')->comment('实际码数');
            $table->string('settle_size')->comment('结算码数');
            $table->string('price')->comment('单价/双');
            $table->string('total_price')->comment('金额');
            $table->text('remark')->comment('备注');
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
        Schema::dropIfExists('mold_information');
    }
}
