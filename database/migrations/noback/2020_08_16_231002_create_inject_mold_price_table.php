<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInjectMoldPriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inject_mold_price', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('company_model_id')->comment('雷力型号');
            $table->string('company_model')->comment('雷力型号')->nullable();

            $table->unsignedBigInteger('product_category_id')->comment('产品类型');
            $table->string('product_category_name')->comment('产品类型')->nullable();

            $table->decimal('price',10,2)->comment('工价')->default(0);


            $table->enum('out_num',['1','2'])->comment('出面数量')->default('1');
            $table->enum('mold_type',['sole','film_ottom','heel','net_heel','chumian','waterproof_platform','welt','rubber'])
                ->comment('模具类型')->default('sole');

            $table->unsignedBigInteger('check_user_id')->comment('审核人');
            $table->string('check_user_name')->comment('审核人');
            $table->text('remark')->comment('说明');
            $table->text('image')->comment('图片');
            $table->string('product_feature')->comment('产品特性');
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
        Schema::dropIfExists('inject_mold_price');
    }
}
