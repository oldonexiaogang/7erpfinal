<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan_category', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_id')->comment('上级');
            $table->string('plan_category_name')->comment('名称');
            $table->integer('order')->comment('等级');
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
        Schema::dropIfExists('plan_category');
    }
}
