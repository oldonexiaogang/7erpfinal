<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('client_category_id')->comment('客户分类');
            $table->string('client_no')->comment('客户代号');
            $table->string('client_name')->comment('客户名称');
            $table->string('sales_id')->comment('业务员id');
            $table->string('sales_name')->comment('业务员');
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
        Schema::dropIfExists('client');
    }
}
