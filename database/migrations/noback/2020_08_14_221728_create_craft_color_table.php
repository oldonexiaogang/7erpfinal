<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCraftColorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('craft_color', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('client_model_id')->comment('客户型号');
            $table->string('client_model')->comment('客户型号');
            $table->string('craft_color_name');
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
        Schema::dropIfExists('craft_color');
    }
}
