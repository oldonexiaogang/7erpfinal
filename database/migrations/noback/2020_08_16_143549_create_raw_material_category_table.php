<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRawMaterialCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raw_material_category', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('raw_material_category_name');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('parent_id')->default(0)->comment('父级id');
            $table->unsignedBigInteger('order')->default(0)->nullable();
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
        Schema::dropIfExists('raw_material_category');
    }
}
