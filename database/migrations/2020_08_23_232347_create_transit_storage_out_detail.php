<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransitStorageOutDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transit_storage_out_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('trandit_storage_out_id')->comment('出库');
            $table->string('spec')->comment('尺码');
            $table->enum('type',['left','right','couple'])->default('left')->comment('规格');
            $table->enum('is_print',['1','0'])->default('0')->comment('是否打印');
            $table->enum('status',['1','0'])->default('1')->comment('状态');
            $table->decimal('num',10,2)->comment('数量')->nullable()->default(0);
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
        Schema::dropIfExists('transit_storage_out_detail');
    }
}
