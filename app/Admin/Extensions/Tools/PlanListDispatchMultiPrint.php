<?php

namespace App\Admin\Extensions\Tools;

use Dcat\Admin\Grid\BatchAction;
use Illuminate\Http\Request;
use Dcat\Admin\Form;
use App\Models\Dispatch;
use App\Models\DispatchDetail;
use App\Models\PlanListDetail;
use App\Models\PlanList;

class PlanListDispatchMultiPrint extends CommonTools
{
    protected $action;

    public function __construct($title = null, $action = 1)
    {
        $this->title = '<button class="btn btn-primary btn-sm btn-mini  " id="plpg">'.$title.'</button>';
        $this->action = $action;
    }

    // 确认弹窗信息
    public function confirm()
    {
        return [
            // 确认弹窗 title
            "您确定要批量鞋底派工并打印所选数据？",
            // 确认弹窗 content
            '',
        ];
    }

    // 处理请求
    public function handle(Request $request)
    {
        $url = urlencode(url()->previous());
        // 获取选中的文章ID数组
        $keys = $this->getKey();
        $firstPlanList  = PlanList::where('id',$keys[0])->first();
        //查询是否有左右脚之类的,33码，使用但规格打印
        $usesimple  = PlanListDetail::whereIn('plan_list_id',$keys)
            ->where(function ($qq){
                $qq ->where('type','left')
                    ->orWhere('type','right')
                    ->orWhere('spec','33');
            })
            ->count();
        if($usesimple>0){
            return $this->response()->error('存在左右脚或33码，请使用订制派工');
        }

        $plan_list_num  = PlanList::whereIn('id',$keys)
            ->where('client_id',$firstPlanList->client_id)
            ->where('client_model',$firstPlanList->client_model)
            ->where('company_model',$firstPlanList->company_model)
            ->where('craft_color_name',$firstPlanList->craft_color_name)
            ->where('status','0')
            ->count();

        if($plan_list_num!=count($keys)){
            return $this->response()->error('所选数据必要条件不一致或部分计划单已派工，请重新选择');
        }

        $ids = implode(',',$keys);
        return $this->response()->script("window.open('".admin_url('plan-list-to-dispatch/preview?id='.$ids)."');
         setTimeout(function(){ window.location.reload(); }, 1500);");
//        $url  = admin_url('plan-list-to-dispatch/preview?id='.$ids);
//        return $this->response()->script("layer.open({type: 2,title: '批量派工打印预览',shadeClose: true,shade: false,maxmin: true, area: [".config('plan.dialog.width').", ".config('plan.dialog.height')."],content:'".$url."'});Dcat.reload();");
    }


    // 设置请求参数
    public function parameters()
    {
        return [
            'action' => $this->action,
        ];
    }
}
