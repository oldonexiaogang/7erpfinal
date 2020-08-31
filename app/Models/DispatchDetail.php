<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispatchDetail extends Model
{

    protected $table = 'dispatch_details';
    public function dispatch_info(){
        return $this->belongsTo(Dispatch::class,'dispatch_id','id');
    }
    public function plan_list(){
        return $this->belongsTo(PlanList::class,'plan_list_id','id');
    }

//    public function injectMoldStorageInAllNum($plan_list_id,$company_model_id){
//        $num = ZhongzhuanRuku::where('paigong_detail_id',$id)
//            ->whereNull('deleted_at')->sum('num');
//        return $num;
//    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
