<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoldPriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mold_price', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('date_at')->comment('生成日期');
            $table->timestamp('check_at')->comment('验收日期')->nullable();
            $table->integer('mold_maker_id')->comment('模具生产商');
            $table->string('mold_maker_name')->comment('模具生产商')->nullable();
            $table->integer('mold_category_child_id')->comment('模具产品类别');
            $table->integer('mold_category_parent_id')->comment('模具材料类别');
            $table->string('mold_category_child_name')->comment('模具产品类别')->nullable();
            $table->string('mold_category_parent_name')->comment('模具材料类别')->nullable();
            $table->string('price')->comment('单价/双');
            $table->string('log_user_id')->comment('录入员');
            $table->string('log_user_name')->comment('录入员');
            $table->integer('status')->default(1)->nullable();
            $table->integer('check')->default(0)->nullable();
            $table->text('image')->comment('图片')->nullable();
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
        Schema::dropIfExists('mold_price');
    }
}
