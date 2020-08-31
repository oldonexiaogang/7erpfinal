<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDispatchDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatch_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('plan_list_id')->comment('计划单');
            $table->unsignedBigInteger('plan_list_detail_id')->comment('计划单详情');
            $table->unsignedBigInteger('dispatch_id')->comment('派工单');
            $table->string('spec')->comment('尺码');
            $table->enum('type',['left','right','couple'])->default('left')->comment('规格');
            $table->unsignedBigInteger('num')->default(1)->comment('数量');
            $table->enum('is_print',['1','0'])->default('0')->comment('是否打印');
            $table->decimal('storage_in',10,2)->comment('中转入库数量')->nullable()->default(0);
            $table->decimal('storage_out',10,2)->comment('中转出库数量')->nullable()->default(0);
            $table->enum('status',['1','0','couple'])->default('1')->comment('状态');
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
        Schema::dropIfExists('dispatch_details');
    }
}
