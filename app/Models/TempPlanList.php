<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TempPlanList extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'temp_plan_list';
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
    /**
     * dec:计划单对应的详情
     * author : happybean
     * date: 2020-05-09
     */
    public function spec(){
        return $this->hasMany(TempPlanListDetail::class,'plan_list_id','id');
    }
    /**
     * 详情
     * @param $plan_order_id
     * @param $code
     * @return array
     */
    public function getDetailNum($plan_list_id,$code){
        $all =  TempPlanListDetail::where('plan_list_id',$plan_list_id)
            ->where('spec',$code)
            ->sum('num');
        $left = TempPlanListDetail::where('plan_list_id',$plan_list_id)
            ->where('spec',$code)
            ->where('type','left')
            ->sum('num');
        $right = TempPlanListDetail::where('plan_list_id',$plan_list_id)
            ->where('type','right')
            ->where('spec',$code)
            ->sum('num');
        return ['all'=>is_float_number($all),'left'=>is_float_number($left),'right'=>is_float_number($right)];
    }
}
