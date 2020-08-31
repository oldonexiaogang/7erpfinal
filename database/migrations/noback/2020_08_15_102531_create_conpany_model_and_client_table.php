<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConpanyModelAndClientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_model_and_client', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_model_id')->comment('雷力型号');
            $table->unsignedBigInteger('client_id')->comment('客户');
            $table->unsignedBigInteger('craft_information_id')->comment('工艺单');
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
        Schema::dropIfExists('company_model_and_client');
    }
}
