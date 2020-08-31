<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeNumToPlanListDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plan_list_detail', function (Blueprint $table) {
            $table->decimal('sole_dispatch_num',10,0)->comment('鞋底派工数量')->nullable()->default(0);
            $table->decimal('inject_mold_dispatch_num',10,2)->comment('注塑派工数量')->nullable()->default(0);
            $table->decimal('box_label_dispatch_num',10,2)->comment('箱标派工数量')->nullable()->default(0);
            $table->decimal('delivery_num',10,2)->comment('成品发货数量')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plan_list_detail', function (Blueprint $table) {
            $table->dropColumn('sole_dispatch_num');
            $table->dropColumn('inject_mold_dispatch_num');
            $table->dropColumn('box_label_dispatch_num');
            $table->dropColumn('delivery_num');
        });
    }
}
