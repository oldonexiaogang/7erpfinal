<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoldCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mold_category', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mold_category_name');
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
        Schema::dropIfExists('mold_category');
    }
}
