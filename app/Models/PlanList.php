<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PlanList extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'plan_list';
    protected $fillable = [
'client_sole_information_id','plan_list_no','delivery_date',
'client_order_no','product_time','carft_skill_id','carft_skill_name','personnel_id',
        'personnel_name','client_id','client_name','company_model_id','company_model','client_model_id',
        'client_model','craft_color_id','craft_color_name','product_category_id',
        'product_category_name','plan_category_id','plan_category_name',
        'spec_num','plan_describe','knife_mold','leather_piece','welt','out','inject_mold_ask',
        'craft_ask','plan_remark','image','status','process','sole','sole_status','inject_mold_status',
        'box_label_status','from',
        'delivery_num','storage_out_status','storage_out_num','storage_in_status',
        'storage_in_num','delivery_status','is_void'
    ];
    /**
     * dec:计划单对应的详情
     * author : happybean
     * date: 2020-05-09
     */
    public function spec(){
        return $this->hasMany(PlanListDetail::class,'plan_list_id','id');
    }
    public function getSoleMaterialName($client_sole_information_id){
        $info = ClientSoleInformation::where('id',$client_sole_information_id)->first();
        if($info){
            return $info->sole_material_name;
        }else{
            return '暂无信息';
        }
    }

    /**
     * 详情
     * @param $plan_order_id
     * @param $code
     * @return array
     */
    public function getDetailNum($plan_list_id,$code){
        $all =  PlanListDetail::where('plan_list_id',$plan_list_id)
            ->where('spec',$code)
            ->sum('num');
        $left = PlanListDetail::where('plan_list_id',$plan_list_id)
            ->where('spec',$code)
            ->where('type','left')
            ->sum('num');
        $right = PlanListDetail::where('plan_list_id',$plan_list_id)
            ->where('type','right')
            ->where('spec',$code)
            ->sum('num');
        return ['all'=>is_float_number($all),'left'=>is_float_number($left),'right'=>is_float_number($right)];
    }

    /**
     * 获取未派工数量
     * @param $plan_list_id
     * @param $code
     * @param $type
     * @return array
     */
    public function getWaitDispatchDetailNum($plan_list_id,$code,$type){
        $all =  PlanListDetail::where('plan_list_id',$plan_list_id)
            ->where('spec',$code)->where('status','1')->sum('num');
        $sum_column = $type.'_dispatch_num';

        $sole_dispatch_all =  PlanListDetail::where('plan_list_id',$plan_list_id)
            ->where('spec',$code)->where('status','1')->sum($sum_column);
        $sole_dispatch_left_all = PlanListDetail::where('plan_list_id',$plan_list_id)
            ->where('spec',$code)
            ->where('type','left')
            ->sum('num');
        $sole_dispatch_right_all = PlanListDetail::where('plan_list_id',$plan_list_id)
            ->where('spec',$code)
            ->where('type','right')
            ->sum('num');
        $sole_dispatch_left = PlanListDetail::where('plan_list_id',$plan_list_id)
            ->where('spec',$code)
            ->where('type','left')
            ->sum($sum_column);
        $sole_dispatch_right = PlanListDetail::where('plan_list_id',$plan_list_id)
            ->where('spec',$code)
            ->where('type','right')
            ->sum($sum_column);
        $show_all = $all-$sole_dispatch_all;
        $left = $sole_dispatch_left_all-$sole_dispatch_left;
        $right = $sole_dispatch_right_all-$sole_dispatch_right;

        return ['all'=>$show_all,'left'=>$left,'right'=>$right];
    }
    public function getWaitInjectMoldDispatchDetailNum($plan_list_id,$code){
        $inject_mold_dispatch_all =  DispatchDetail::whereHas('dispatch_info',function ($q){
            $q->where('is_void','0');
        })->where('plan_list_id',$plan_list_id)
            ->where('spec','like',$code)
            ->sum('num');
        $has_inject_mold_dispatch_all =  DispatchDetail::whereHas('dispatch_info',function ($q){
            $q->where('is_void','0');
        })->where('plan_list_id',$plan_list_id)
            ->where('status','2')
            ->where('spec','like',$code)
            ->sum('num');
        $show_all = $inject_mold_dispatch_all;
        $wait= $show_all-$has_inject_mold_dispatch_all;

        return ['all'=>is_float_number($show_all),'wait' =>is_float_number($wait)];
    }
    public function getDispatchAllNum($plan_list_id,$type){
        $all =  PlanListDetail::where('plan_list_id',$plan_list_id)->sum('num');
        $sole_dispatch_num_all =  PlanListDetail::where('plan_list_id',$plan_list_id)->sum('sole_dispatch_num');
        $box_label_dispatch_num_all =  PlanListDetail::where('plan_list_id',$plan_list_id)->sum('box_label_dispatch_num');
        $inject_mold_dispatch_num_all =  PlanListDetail::where('plan_list_id',$plan_list_id)->sum('inject_mold_dispatch_num');
        $diapatch_num=$wait_num=0;
        if($type=='sole'){
             $wait_num= $all-$sole_dispatch_num_all;
            $diapatch_num = $sole_dispatch_num_all;
        }elseif($type=='box_label'){
            $wait_num = $all-$box_label_dispatch_num_all;
            $diapatch_num = $box_label_dispatch_num_all;

        }elseif($type=='inject_mold'){
            $wait_num = $all-$inject_mold_dispatch_num_all;
            $diapatch_num = $inject_mold_dispatch_num_all;

        }
        return ['all'=>$all,'dispatch_num'=>$diapatch_num,'wait_num'=>$wait_num];
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    public function getNoDelivery($plan_list_id,$code){
        $all =  PlanListDetail::where('plan_list_id',$plan_list_id)
            ->where('spec',$code)
            ->sum('num');

        $delivery_all =  PlanListDetail::where('plan_list_id',$plan_list_id)
            ->where('spec',$code)->sum('delivery_num');
        $left_all = PlanListDetail::where('plan_list_id',$plan_list_id)
            ->where('spec',$code)
            ->where('type','left')
            ->sum('num');
        $right_all = PlanListDetail::where('plan_list_id',$plan_list_id)
            ->where('spec',$code)
            ->where('type','right')
            ->sum('num');
        $left_delivery = PlanListDetail::where('plan_list_id',$plan_list_id)
            ->where('spec',$code)
            ->where('type','left')->sum('delivery_num');
        $right_delivery  = PlanListDetail::where('plan_list_id',$plan_list_id)
            ->where('spec',$code)
            ->where('type','right')
            ->sum('delivery_num');

        $show_all = $all-$delivery_all;
        $left = $left_all-$left_delivery;
        $right = $right_all-$right_delivery;
        return ['all'=>$show_all,'left'=>$left,'right'=>$right];
    }

    /**
     * dec: 客户订单数汇总
     * @param $id
     * author : happybean
     * date: 2020-05-09
     */
    public function planListClientNum($id=0,$time_start,$time_end){
        $month_today = date('Y-m-d',time());
        if($id){

            $num = PlanListDetail::whereHas('plan_list',function ($q) use($id){
                $q->where('client_id',$id);
            })->where('created_at', '>=', $time_start)
                ->where('created_at', '<=',$time_end)
                ->whereDate('created_at', '<',$month_today)
                ->sum('num');
        }else{
            $num = PlanListDetail::where('created_at', '>=', $time_start)
                ->where('created_at', '<=',$time_end)
                ->whereDate('created_at', '<',$month_today)
                ->sum('num');
        }
        return $num;
    }
    /**
     * dec:当日报表汇总TPU
     * author : happybean
     * date: 2020-05-18
     */
    public function planListTpu($id=0,$time_start,$time_end){
        $month_today = date('Y-m-d',time());
        if($id){
            $num = PlanListDetail::whereHas('plan_list',function ($q) use($id){
                $q->where('client_id',$id)
                    ->where('product_category_name','like','%TPU%');
            })->where('created_at', '>=', $time_start)
                ->where('created_at', '<=',$time_end)
                ->whereDate('created_at', '<',$month_today)
                ->sum('num');
        }else{
            $num = PlanListDetail::whereHas('plan_list',function ($q){
                $q->where('product_category_name','like','%TPU%');
            })->where('created_at', '>=', $time_start)
                ->where('created_at', '<=',$time_end)
                ->whereDate('created_at', '<',$month_today)
                ->sum('num');
        }
        return $num;
    }
    /**
     * dec:当日报表汇总橡胶
     * author : happybean
     * date: 2020-05-18
     */
    public function planListRubber($id=0,$time_start,$time_end){
        $month_today = date('Y-m-d',time());
        if($id){
            $num = PlanListDetail::whereHas('plan_list',function ($q) use($id){
                $q->where('client_id',$id)->where('product_category_name','like','%橡胶%');
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('num');
        }else{
            $num = PlanListDetail::whereHas('plan_list',function ($q){
                $q->where('product_category_name','like','%橡胶%');
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('num');
        }
        return $num;
    }
    /**
     * dec:当日报表汇总沿条
     * author : happybean
     * date: 2020-05-18
     */
    public function planListWelt($id=0,$time_start,$time_end){
        $month_today = date('Y-m-d',time());
        if($id){
            $num = PlanListDetail::whereHas('plan_list',function ($q) use($id){
                $q->where('client_id',$id)->whereHas('client_sole_information_info',function ($qq){
                    $qq->where('is_welt','1');
                });
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('num');
        }else{
            $num = PlanListDetail::whereHas('plan_list',function ($q){
                $q->whereHas('client_sole_information_info',function ($qq){
                    $qq->where('is_welt','1');
                });
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('num');
        }
        return $num;
    }
    public function client_sole_information_info(){
        return $this->belongsTo(ClientSoleInformation::class,
            'client_sole_information_id','id');
    }
    /**
     * dec:当日报表汇总改色
     * author : happybean
     * date: 2020-05-18
     */
    public function planListColor($id=0,$time_start,$time_end){
        $month_today = date('Y-m-d',time());
        if($id){
            $num = PlanListDetail::whereHas('plan_list',function ($q) use($id){
                $q->where('kehu_id',$id)->whereHas('client_sole_information_info',function ($qq){
                    $qq->where('is_color','1');
                });
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('num');
        }else{
            $num = PlanListDetail::whereHas('plan_list',function ($q){
                $q->whereHas('client_sole_information_info',function ($qq){
                    $qq->where('is_color','1');
                });
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('num');
        }
        return $num;
    }
    /**
     * dec: 客户订单未完成数量汇总
     * @param $id
     * author : happybean
     * date: 2020-05-09
     */
    public function planListNoCompleteClientNum($id=0,$time_start,$time_end){
        $month_today = date('Y-m-d',time());
        if($id){
            $allnum = PlanListDetail::whereHas('plan_list',function ($q) use($id){
                $q->where('client_id',$id);
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)
                ->sum('num');
            $delivery_num = PlanListDetail::whereHas('plan_list',function ($q) use($id){
                $q->where('client_id',$id);
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)
                ->sum('delivery_num');
        }else{
            $allnum = PlanListDetail::where('created_at', '>=', $time_start)
                ->where('created_at', '<=',$time_end)
                ->whereDate('created_at', '<',$month_today)
                ->sum('num');
            $delivery_num = PlanListDetail::where('created_at', '>=', $time_start)
                ->where('created_at', '<=',$time_end)
                ->whereDate('created_at', '<',$month_today)
                ->sum('delivery_num');
        }
        $num = $allnum-$delivery_num;
        return $num;
    }
    //TPU
    public function planListNoCompleteTpu($id=0,$time_start,$time_end){
        $month_today = date('Y-m-d',time());
        if($id){
            $allnum = PlanListDetail::whereHas('plan_list',function ($q) use($id){
                $q->where('client_id',$id)->where('product_category_name','like','%TPU%');
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('num');
            $fanum = PlanListDetail::whereHas('plan_list',function ($q) use($id){
                $q->where('client_id',$id)->where('product_category_name','like','%TPU%');
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('delivery_num');
        }else{
            $allnum = PlanListDetail::whereHas('plan_list',function ($q){
                $q->where('product_category_name','like','%TPU%');
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('num');
            $fanum = PlanListDetail::whereHas('plan_list',function ($q){
                $q->where('product_category_name','like','%TPU%');
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('delivery_num');
        }
        return $allnum-$fanum;
    }
    //橡胶
    public function planListNoCompleteRubber($id=0,$time_start,$time_end){
        $month_today = date('Y-m-d',time());
        if($id){
            $allnum = PlanListDetail::whereHas('plan_list',function ($q) use($id){
                $q->where('client_id',$id)->where('product_category_name','like','%橡胶%');
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('num');
            $fanum = PlanListDetail::whereHas('plan_list',function ($q) use($id){
                $q->where('client_id',$id)->where('product_category_name','like','%橡胶%');
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('delivery_num');
        }else{
            $allnum = PlanListDetail::whereHas('plan_list',function ($q){
                $q->where('product_category_name','like','%橡胶%');
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('num');
            $fanum = PlanListDetail::whereHas('plan_list',function ($q){
                $q->where('product_category_name','like','%橡胶%');
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('delivery_num');
        }
        return $allnum-$fanum;
    }
    //沿条
    public function planListNoCompleteWelt($id=0,$time_start,$time_end){
        $month_today = date('Y-m-d',time());
        if($id){
            $allnum = PlanListDetail::whereHas('plan_list',function ($q) use($id){
                $q->where('client_id',$id)->whereHas('client_sole_information_info',function ($qq){
                    $qq->where('is_welt','1');
                });
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('num');
            $fanum = PlanListDetail::whereHas('plan_list',function ($q) use($id){
                $q->where('client_id',$id)->whereHas('client_sole_information_info',function ($qq){
                    $qq->where('is_welt','1');
                });
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('delivery_num');
        }else{
            $allnum = PlanListDetail::whereHas('plan_list',function ($q){
                $q->whereHas('client_sole_information_info',function ($qq){
                    $qq->where('is_welt','1');
                });
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<=',$time_end)
                ->where('created_at', '<',$month_today)
                ->sum('num');
            $fanum = PlanListDetail::whereHas('plan_list',function ($q){
                $q->whereHas('client_sole_information_info',function ($qq){
                    $qq->where('is_welt','1');
                });
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('delivery_num');
        }
        return $allnum-$fanum;
    }
    //颜色
    public function planListNoCompleteColor($id=0,$time_start,$time_end){
        $month_today = date('Y-m-d',time());
        if($id){
            $allnum = PlanListDetail::whereHas('plan_list',function ($q) use($id){
                $q->where('client_id',$id)->whereHas('client_sole_information_info',function ($qq){
                    $qq->where('is_color','1');
                });
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('num');
            $fanum = PlanListDetail::whereHas('plan_list',function ($q) use($id){
                $q->where('client_id',$id)->whereHas('client_sole_information_info',function ($qq){
                    $qq->where('is_color','1');
                });
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('delivery_num');
        }else{
            $allnum = PlanListDetail::whereHas('plan_list',function ($q){
                $q->whereHas('client_sole_information_info',function ($qq){
                    $qq->where('is_color','1');
                });
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('num');
            $fanum = PlanListDetail::whereHas('plan_list',function ($q){
                $q->whereHas('client_sole_information_info',function ($qq){
                    $qq->where('is_color','1');
                });
            })->where('created_at', '>=', $time_start)
                ->whereDate('created_at', '<',$month_today)
                ->where('created_at', '<=',$time_end)->sum('delivery_num');
        }
        return $allnum-$fanum;
    }
}
