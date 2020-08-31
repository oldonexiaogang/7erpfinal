<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempPlanListDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_plan_list_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('plan_list_id')->comment('临时计划单');
            $table->string('spec')->comment('尺码');
            $table->enum('type',['left','right','couple'])->default('left')->comment('规格');
            $table->unsignedBigInteger('num')->default(1)->comment('订单数');
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
        Schema::dropIfExists('temp_plan_list_detail');
    }
}
