<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    protected $table = 'dispatches';
    protected $fillable=[
        'plan_list_id','plan_list_no','dispatch_no',
        'client_order_no','carft_skill_id','carft_skill_name',
        'sole_material_id','sole_material_name','client_id','client_name',
        'company_model_id','company_model','client_model_id','client_model',
        'craft_color_id','craft_color_name','product_category_id','product_category_name',
        'type','process_workshop','process_department','inject_mold_ask','plan_remark','all_num',
        'dispatch_user_id','dispatch_user_name','status','is_void'
    ];
    /**
     * 派工中是否有鞋底派工
     * @param $plan_list_id
     * @return bool
     */
    public function hasSoleDispatch($plan_list_id){
        $num = Dispatch::where('plan_list_id',$plan_list_id)
            ->where('type','sole')
            ->count();
        return  $num>0?true:false;
    }
    //根据派工id 获取详情数量
    public function getDetailNum($dispatch_id,$code){
        $all =  DispatchDetail::where('dispatch_id',$dispatch_id)
            ->where('spec',$code)
            ->sum('num');
        $left = DispatchDetail::where('dispatch_id',$dispatch_id)
            ->where('spec',$code)
            ->where('type','left')
            ->sum('num');
        $right = DispatchDetail::where('dispatch_id',$dispatch_id)
            ->where('type','right')
            ->where('spec',$code)
            ->sum('num');
        return ['all'=>is_float_number($all),'left'=>is_float_number($left),'right'=>is_float_number($right)];
    }
    /**
     * dec:派工对应详情
     * author : happybean
     * date: 2020-05-22
     */
    public function detail(){
        return $this->hasMany(DispatchDetail::class,'dispatch_id','id');
    }
    /**
     * 所属的计划单
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan_list(){
        return $this->belongsTo(PlanList::class,'plan_list_id','id');
    }

    public function sole_material(){
        return $this->belongsTo(SoleMaterial::class,'sole_material_id','id');
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
    public function getStorageOutNum($dispatch_id){
        $all =  DispatchDetail::where('dispatch_id',$dispatch_id)
            ->sum('storage_out');
        return $all;
    }
    public function getWaitStorageOutNum($dispatch_id,$code){
        $all =  DispatchDetail::where('dispatch_id',$dispatch_id)
            ->where('spec',$code)
            ->sum('num');
        $storage_out_all =  DispatchDetail::where('dispatch_id',$dispatch_id)
            ->where('spec',$code)
            ->sum('storage_out');
        $left_all = DispatchDetail::where('dispatch_id',$dispatch_id)
            ->where('spec',$code)
            ->where('type','left')
            ->sum('num');
        $left_storage_out = DispatchDetail::where('dispatch_id',$dispatch_id)
            ->where('spec',$code)
            ->where('type','left')
            ->sum('storage_out');

        $right_all = DispatchDetail::where('dispatch_id',$dispatch_id)
            ->where('spec',$code)
            ->where('type','right')
            ->sum('num');
        $right_storage_out = DispatchDetail::where('dispatch_id',$dispatch_id)
            ->where('spec',$code)
            ->where('type','right')
            ->sum('storage_out');

        return ['all'=>is_float_number($all-$storage_out_all),
                'left'=>is_float_number($left_all-$left_storage_out),
                'right'=>is_float_number($right_all-$right_storage_out)];
    }
}
