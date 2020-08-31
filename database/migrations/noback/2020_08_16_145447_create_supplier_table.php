<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('supplier_no')->comment('供应商编号');
            $table->string('supplier_name')->comment('供应商名称');
            $table->string('pinyin')->comment('供应商拼音');
            $table->string('contact')->comment('联系人');
            $table->string('tel')->comment('电话号码');
            $table->string('email')->comment('邮箱')->nullable();
            $table->string('fax')->comment('传真')->nullable();
            $table->string('address')->comment('地址')->nullable();
            $table->string('bank')->comment('所在银行')->nullable();
            $table->string('bank_account')->comment('银行卡号')->nullable();
            $table->text('remark')->comment('备注')->nullable();
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
        Schema::dropIfExists('supplier');
    }
}
