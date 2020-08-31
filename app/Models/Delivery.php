<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'delivery';

    protected $fillable=[
        'plan_list_id','plan_list_no','delivery_no','client_order_no','client_id','client_name',
        'company_model_id','company_model','client_model_id','client_model','craft_color_id','craft_color_name',
        'content','status','all_num','delivery_price_id','delivery_price','log_user_id','log_user_name',
        'delivery_at',
        'delivery_user_id','delivery_user_name','delivery_type','is_print','is_void'
    ];
    public function plan_list_info (){
        return $this->belongsTo(PlanList::class,'plan_list_id','id');
    }
    /**
     * 详情
     * @param $plan_order_id
     * @param $code
     * @return array
     */
    public function getDetailNum($delivery_id,$code){
        $all =  DeliveryDetail::where('delivery_id',$delivery_id)
            ->where('spec',$code)
            ->sum('num');
        $left = DeliveryDetail::where('delivery_id',$delivery_id)
            ->where('spec',$code)
            ->where('type','left')
            ->sum('num');
        $right = DeliveryDetail::where('delivery_id',$delivery_id)
            ->where('type','right')
            ->where('spec',$code)
            ->sum('num');
        return ['all'=>is_float_number($all),'left'=>is_float_number($left),'right'=>is_float_number($right)];
    }
}
