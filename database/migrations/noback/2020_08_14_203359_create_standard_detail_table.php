<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStandardDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('standard_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_model_id')->comment('雷力型号');
            $table->string('company_model')->comment('雷力型号');
            $table->string('standard_detail_name')->nullable();
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
        Schema::dropIfExists('standard_detail');
    }
}
