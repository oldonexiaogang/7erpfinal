<?php

namespace App\Admin\Extensions\Tools;

use App\Models\TempPlanList;
use Carbon\Carbon;
use Dcat\Admin\Admin;
use Dcat\Admin\Grid\BatchAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\TempPlanListDetail;
use App\Models\PlanListDetail;
use App\Models\PlanList;

class TempPlanListMultiCheck extends CommonTools
{
    protected $action;

    // 注意action的构造方法参数一定要给默认值
    public function __construct($title = null, $action = 1)
    {
        $this->title = '<button class="btn btn-primary btn-sm btn-mini  ">'.$title.'</button>';
        $this->action = $action;
    }

    // 确认弹窗信息
    public function confirm()
    {
        return '您确定要将选中的临时单数据转入计划单中？';
    }

    // 处理请求
    public function handle(Request $request)
    {
        // 获取选中的文章ID数组
        $keys = $this->getKey();
        $checknum= TempPlanList::whereIn('id',$keys)
            ->Where('is_check','1')
            ->count();
        if($checknum>0){
            return $this->response()->error('请确认所选数据未验收')->refresh();
        }
        DB::beginTransaction(); //开启事务
        $now = Carbon::now();
        try {
            $TempPlanorders= TempPlanList::with('spec')->whereIn('id',$keys)->get();
            foreach ($TempPlanorders as $k=>$v) {
                $no = getOrderNo('plan_list', '',8,'plan_list_no');
                $specs = $v->spec->toArray();
                $temp_order = $v->toArray();

                $temp_order['plan_list_no'] = $no;
                //计划单添加
                $plan_list_info = PlanList::create([
                    'client_sole_information_id'=>$temp_order['client_sole_information_id'],
                    'delivery_date'=>$temp_order['delivery_date'],
                    'client_order_no'=>$temp_order['client_order_no'],
                    'product_time'=>$temp_order['product_time'],
                    'carft_skill_id'=>$temp_order['carft_skill_id'],
                    'carft_skill_name'=>$temp_order['carft_skill_name'],
                    'personnel_id'=>$temp_order['personnel_id'],
                    'personnel_name'=>$temp_order['personnel_name'],
                    'client_id'=>$temp_order['client_id'],
                    'client_name'=>$temp_order['client_name'],
                    'company_model_id'=>$temp_order['company_model_id'],
                    'company_model'=>$temp_order['company_model'],
                    'client_model_id'=>$temp_order['client_model_id'],
                    'client_model'=>$temp_order['client_model'],
                    'craft_color_id'=>$temp_order['craft_color_id'],
                    'craft_color_name'=>$temp_order['craft_color_name'],
                    'product_category_id'=>$temp_order['product_category_id'],
                    'product_category_name'=>$temp_order['product_category_name'],
                    'plan_category_id'=>$temp_order['plan_category_id'],
                    'plan_category_name'=>$temp_order['plan_category_name'],
                    'spec_num'=>$temp_order['spec_num'],
                    'plan_describe'=>$temp_order['plan_describe'],
                    'knife_mold'=>$temp_order['knife_mold'],
                    'leather_piece'=>$temp_order['leather_piece'],
                    'welt'=>$temp_order['welt'],
                    'out'=>$temp_order['out'],
                    'inject_mold_ask'=>$temp_order['inject_mold_ask'],
                    'craft_ask'=>$temp_order['craft_ask'],
                    'plan_remark'=>$temp_order['plan_remark'],
                    'image'=>$temp_order['image'],
                    'sole'=>$temp_order['sole'],
                    'from'=>'temp_plan_list',
                    'status'=>'0',
                    'process'=>'none',
                    'plan_list_no'=>$no,
                ]);
                foreach ($specs as $kk=>$vv){
                    unset($specs[$kk]['id']);
                    $specs[$kk]['plan_list_id']=$plan_list_info->id;
                    $specs[$kk]['sole_dispatch_num']='1';
                    $specs[$kk]['inject_mold_dispatch_num']='1';
                    $specs[$kk]['box_label_dispatch_num']='1';
                    $specs[$kk]['delivery_num']='1';
                    $specs[$kk]['sole_dispatch_complete']='1';
                    $specs[$kk]['status']='1';
                    $specs[$kk]['box_label_dispatch_complete']='1';
                    $specs[$kk]['delivery_complete']='1';
                }
                //计划单详情数据
                DB::table('plan_list_detail')->insert($specs);

                //临时单修改

                $v->plan_list_id = $plan_list_info->id;
                $v->plan_list_no = $plan_list_info->plan_list_no;
                $v->is_check = '1';
                $v->check_at = $now;
                $v->check_user_id = Admin::user()->id;
                $v->check_user_name = Admin::user()->name;
                $v->save();
            }


            DB::commit();
            return $this->response()->success('批量验收成功')->refresh();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->response()->error($e);
            return $this->response()->error('批量验收失败');
        }
    }

    // 设置请求参数
    public function parameters()
    {
        return [
            'action' => $this->action,
        ];
    }
}
