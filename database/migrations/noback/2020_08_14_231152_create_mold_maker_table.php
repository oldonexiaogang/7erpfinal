<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoldMakerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mold_maker', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mold_maker_no')->comment('模具生产商代号');
            $table->string('mold_maker_name')->comment('模具生产商名称');
            $table->string('pinyin')->comment('客户名称');
            $table->string('tel')->comment('电话号码');
            $table->string('email')->comment('邮箱');
            $table->string('fax')->comment('传真');
            $table->string('address')->comment('地址');
            $table->string('bank')->comment('所在银行');
            $table->string('bank_account')->comment('银行卡号');
            $table->text('remark')->comment('备注');
            $table->timestamp('add_at')->comment('添加时间');
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
        Schema::dropIfExists('mold_maker');
    }
}
