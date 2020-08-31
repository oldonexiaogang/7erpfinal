<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonnelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personnel', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('position_id')->nullable();
            $table->string('personnel_no')->unique();
            $table->string('name');
            $table->enum('sex',['boy','girl'])->default('boy');
            $table->timestamp('birthday_at')->nullable();
            $table->string('idcard')->comment('身份证号码')->nullable();
            $table->string('address')->comment('家庭住址')->nullable();
            $table->string('nation')->comment('民族')->nullable()->default('汉');
            $table->timestamp('come_at')->comment('进厂日期')->nullable();
            $table->timestamp('work_at')->comment('上班日期')->nullable();
            $table->timestamp('out_at')->comment('离厂日期')->nullable();
            $table->enum('work_status',['formal','disformal'])->comment('正式员工，试用期员工');
            $table->enum('status',['on','off'])->comment('在职，离职');
            $table->string('remark')->comment('备注')->nullable();
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
        Schema::dropIfExists('personnel');
    }
}
